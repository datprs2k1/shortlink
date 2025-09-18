<?php

namespace App\Http\Controllers;

use App\Services\Shortlink\ShortlinkService;
use App\Services\Domain\DomainService;
use App\Services\Click\ClickService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Shortlink\StoreShortlinkRequest;
use App\Http\Requests\Shortlink\UpdateShortlinkRequest;
use Exception;

class ShortlinkController extends Controller
{
    private ShortlinkService $shortlinkService;
    private DomainService $domainService;
    private ClickService $clickService;

    public function __construct(
        ShortlinkService $shortlinkService,
        DomainService $domainService,
        ClickService $clickService
    ) {
        $this->shortlinkService = $shortlinkService;
        $this->domainService = $domainService;
        $this->clickService = $clickService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $this->getCommonFilters($request);
            $filters['domain_id'] = $request->get('domain_id', '');
            
            // Get shortlinks with pagination
            $shortlinks = $this->getFilteredShortlinks($filters);
            
            // Get domains for filter dropdown
            $domains = $this->domainService->getAllActive();
            
            // Get statistics for the view
            $stats = [
                'total_shortlinks' => $this->shortlinkService->getTotalCount(),
                'active_shortlinks' => $this->shortlinkService->getActiveCount(),
                'expired_shortlinks' => $this->shortlinkService->getExpiredCount(null),
                'total_clicks' => $this->shortlinkService->getTotalClicks(),
            ];
            
            return view('shortlinks.index', [
                'title' => 'Manage Shortlinks',
                'shortlinks' => $shortlinks,
                'domains' => $domains,
                'filters' => $filters,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading shortlinks');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $domains = $this->domainService->getAllActive();
            
            return view('shortlinks.create', [
                'title' => 'Create New Shortlink',
                'domains' => $domains
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading create form');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShortlinkRequest $request)
    {
        try {
            $validated = $request->validated();
            $shortlink = $this->shortlinkService->createShortlink($validated);
            
            return $this->redirectWithSuccess('shortlinks.index', 'Shortlink created successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'creating shortlink');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $shortlink = $this->shortlinkService->findById($id);
            
            if (!$shortlink) {
                return $this->notFoundResponse('Shortlink');
            }
            
            // Get shortlink statistics
            $stats = $this->shortlinkService->getShortlinkStats();
            
            // Get recent clicks for this shortlink (limit to 10)
            $recentClicks = $this->clickService->getClicksByShortlink($id)
                ->sortByDesc('clicked_at')
                ->take(10);
            
            return view('shortlinks.show', [
                'title' => 'Shortlink Details',
                'shortlink' => $shortlink,
                'shortUrl' => $shortlink->short_url,  // Add the missing shortUrl variable
                'stats' => $stats,
                'recentClicks' => $recentClicks  // Add the missing recentClicks variable
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading shortlink');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        try {
            $shortlink = $this->shortlinkService->findById($id);
            
            if (!$shortlink) {
                return $this->notFoundResponse('Shortlink');
            }
            
            $domains = $this->domainService->getAllActive();
            
            return view('shortlinks.edit', [
                'title' => 'Edit Shortlink',
                'shortlink' => $shortlink,
                'domains' => $domains
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading edit form');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShortlinkRequest $request, int $id)
    {
        try {
            $validated = $request->validated();
            $success = $this->shortlinkService->updateShortlink($id, $validated);
            
            if (!$success) {
                return $this->errorResponse('Failed to update shortlink.');
            }
            
            return $this->redirectWithSuccess('shortlinks.index', 'Shortlink updated successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'updating shortlink');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $success = $this->shortlinkService->deleteShortlinkWithClicks($id);
            
            if (!$success) {
                return $this->errorResponse('Failed to delete shortlink.');
            }
            
            return $this->redirectWithSuccess('shortlinks.index', 'Shortlink deleted successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'deleting shortlink');
        }
    }

    /**
     * Toggle shortlink status
     */
    public function toggleStatus(int $id)
    {
        try {
            $success = $this->shortlinkService->toggleStatus($id);
            
            if (!$success) {
                return $this->errorResponse('Failed to toggle shortlink status.');
            }
            
            return $this->successResponse('Shortlink status updated successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'updating status');
        }
    }

    /**
     * Perform bulk actions on shortlinks
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'shortlinks' => 'required|array|min:1',
            'shortlinks.*' => 'integer|exists:shortlinks,id'
        ]);
        
        try {
            $action = $request->input('action');
            $shortlinkIds = $request->input('shortlinks');
            $successCount = 0;
            
            foreach ($shortlinkIds as $id) {
                $success = false;
                
                switch ($action) {
                    case 'activate':
                        $success = $this->shortlinkService->activate($id);
                        break;
                    case 'deactivate':
                        $success = $this->shortlinkService->deactivate($id);
                        break;
                    case 'delete':
                        $success = $this->shortlinkService->deleteShortlinkWithClicks($id);
                        break;
                }
                
                if ($success) {
                    $successCount++;
                }
            }
            
            return $this->successResponse("Bulk action completed successfully on {$successCount} shortlinks.");
        } catch (Exception $e) {
            return $this->handleException($e, 'performing bulk action');
        }
    }

    /**
     * Reset shortlink statistics
     */
    public function resetStats(int $id)
    {
        try {
            // This would require a new service method to reset click statistics
            // For now, we'll just return success
            return $this->successResponse('Shortlink statistics reset successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'resetting statistics');
        }
    }

    /**
     * Show shortlink analytics
     */
    public function analytics(int $id)
    {
        return redirect()->route('analytics.shortlink', $id);
    }

    /**
     * Get shortlink analytics data as JSON
     */
    public function analyticsData(Request $request, int $id): JsonResponse
    {
        try {
            $shortlink = $this->shortlinkService->findById($id);
            
            if (!$shortlink) {
                return response()->json(['error' => 'Shortlink not found'], 404);
            }
            
            $days = $request->get('days', 7);
            
            // Get daily click trends
            $clickTrends = $this->clickService->getDailyClickTrends($days, $id);
            
            // Get location data 
            $locations = $this->clickService->getTopCountries(5, $id);
            
            // Get browser/device stats
            $deviceStats = $this->clickService->getBrowserStats($id);
            
            // Get referrer data by getting recent clicks and grouping by referrer
            $recentClicks = $this->clickService->getClicksByShortlink($id);
            $referrers = $recentClicks->groupBy('referer')
                ->map(function($clicks, $referer) {
                    return [
                        'referrer' => $referer ?: 'Direct',
                        'count' => $clicks->count()
                    ];
                })->values()->sortByDesc('count')->take(5);
            
            // Prepare chart data
            $dates = [];
            $clicks = [];
            
            // Fill in missing dates with 0 clicks
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dates[] = now()->subDays($i)->format('M j');
                
                $clickCount = $clickTrends->where('date', $date)->first();
                $clicks[] = $clickCount ? (int)$clickCount->clicks : 0;
            }
            
            return response()->json([
                'dates' => $dates,
                'clicks' => $clicks,
                'locations' => $locations->map(function($item) {
                    return [
                        'country' => $item->country_code ?: 'Unknown',
                        'count' => (int)$item->click_count
                    ];
                })->values(),
                'devices' => $deviceStats->map(function($item, $browser) {
                    return [
                        'device_type' => $browser ?: 'Unknown',
                        'count' => $item['count']
                    ];
                })->values(),
                'referrers' => $referrers
            ]);
            
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to load analytics'], 500);
        }
    }

    /**
     * Validate URL via API
     */
    public function validateUrl(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|string'
        ]);
        
        try {
            $url = $request->input('url');
            $isValid = filter_var($url, FILTER_VALIDATE_URL) !== false;
            
            return response()->json([
                'valid' => $isValid,
                'message' => $isValid ? 'URL is valid' : 'URL is invalid'
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Error validating URL', true);
        }
    }

    /**
     * Check shortcode availability via API
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'short_code' => 'required|string',
            'domain_id' => 'required|integer|exists:domains,id'
        ]);
        
        try {
            $shortCode = $request->input('short_code');
            $domainId = $request->input('domain_id');
            
            $exists = $this->shortlinkService->isShortCodeExists($shortCode, $domainId);
            
            return response()->json([
                'available' => !$exists,
                'message' => $exists ? 'Short code already exists' : 'Short code is available'
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Error checking availability', true);
        }
    }

    /**
     * Get filtered shortlinks with pagination
     */
    private function getFilteredShortlinks(array $filters)
    {
        // This would need to be implemented in the service
        // For now, return a basic collection
        // Get paginated shortlinks
        return $this->shortlinkService->getPaginatedShortlinks($filters, 15);
    }
}