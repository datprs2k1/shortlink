<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsService;
use App\Services\Domain\DomainService;
use App\Services\Shortlink\ShortlinkService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Http\Controllers\Traits\HandlesFiltering;

class AnalyticsController extends Controller
{
    use HandlesFiltering;
    private AnalyticsService $analyticsService;
    private DomainService $domainService;
    private ShortlinkService $shortlinkService;

    public function __construct(
        AnalyticsService $analyticsService,
        DomainService $domainService,
        ShortlinkService $shortlinkService
    ) {
        $this->analyticsService = $analyticsService;
        $this->domainService = $domainService;
        $this->shortlinkService = $shortlinkService;
    }

    /**
     * Display the analytics dashboard
     */
    public function index(Request $request)
    {
        try {
            $filters = $this->getCommonFilters($request);
            $filters = array_merge($filters, $this->getDateFilters($request));
            $filters['domain_id'] = $request->input('domain_id');
            
            // Get overview stats
            $stats = $this->analyticsService->getOverviewStats($filters);
            
            // Get chart data
            $chartData = $this->analyticsService->getChartData($filters);
            
            // Get top performing shortlinks
            $topShortlinks = $this->analyticsService->getTopShortlinks($filters);
            
            // Get geographic data
            $geoData = $this->analyticsService->getGeographicData($filters);
            
            // Get device data
            $deviceData = $this->analyticsService->getDeviceData($filters);
            
            // Get referrer data
            $referrerData = $this->analyticsService->getReferrerData($filters);
            
            // Get domains for filter
            $domains = $this->domainService->getAllActive();
            
            return view('analytics.index', [
                'title' => 'Analytics Dashboard',
                'stats' => $stats,
                'chartData' => $chartData,
                'topShortlinks' => $topShortlinks,
                'geoData' => $geoData,
                'deviceData' => $deviceData,
                'referrerData' => $referrerData,
                'domains' => $domains,
                'filters' => $filters
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading analytics');
        }
    }

    /**
     * Display shortlink specific analytics
     */
    public function shortlink(Request $request, int $shortlinkId)
    {
        try {
            $metrics = $this->analyticsService->getShortlinkMetrics($shortlinkId);
            $filters = $this->getCommonFilters($request);
            
            // Get detailed analytics for this shortlink
            $detailedFilters = array_merge($filters, ['shortlink_id' => $shortlinkId]);
            
            $chartData = $this->analyticsService->getChartData($detailedFilters);
            $geoData = $this->analyticsService->getGeographicData($detailedFilters);
            $deviceData = $this->analyticsService->getDeviceData($detailedFilters);
            $referrerData = $this->analyticsService->getReferrerData($detailedFilters);
            
            return view('analytics.shortlink', [
                'title' => 'Shortlink Analytics',
                'metrics' => $metrics,
                'chartData' => $chartData,
                'geoData' => $geoData,
                'deviceData' => $deviceData,
                'referrerData' => $referrerData,
                'filters' => $filters
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading shortlink analytics');
        }
    }

    /**
     * Get real-time analytics data
     */
    public function realtime(Request $request): JsonResponse
    {
        try {
            $data = $this->analyticsService->getRealTimeData();
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Error loading real-time data: ' . $e->getMessage(), true);
        }
    }

    /**
     * Compare multiple shortlinks
     */
    public function comparison(Request $request)
    {
        try {
            $shortlinkIds = $request->input('shortlinks', []);
            
            if (empty($shortlinkIds)) {
                return $this->errorResponse('Please select at least one shortlink to compare.');
            }
            
            $comparisons = [];
            foreach ($shortlinkIds as $shortlinkId) {
                $comparisons[] = $this->analyticsService->getShortlinkMetrics($shortlinkId);
            }
            
            return view('analytics.comparison', [
                'title' => 'Shortlink Comparison',
                'comparisons' => $comparisons
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading comparison data');
        }
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        try {
            $filters = $this->getCommonFilters($request);
            $filters = array_merge($filters, $this->getDateFilters($request));
            $format = $request->input('format', 'csv');
            
            $data = $this->analyticsService->getExportData($filters);
            
            if ($format === 'json') {
                return response()->json($data);
            }
            
            // For CSV export
            $filename = 'analytics_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$filename",
            ];
            
            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Date',
                    'Short Code',
                    'Original URL',
                    'Domain',
                    'IP Address',
                    'Country',
                    'City',
                    'Browser',
                    'OS',
                    'Device',
                    'Referrer'
                ]);
                
                // CSV data
                foreach ($data as $click) {
                    fputcsv($file, [
                        $click->created_at,
                        $click->short_code,
                        $click->original_url,
                        $click->domain_name,
                        $click->ip_address,
                        $click->country,
                        $click->city,
                        $click->browser,
                        $click->os,
                        $click->device,
                        $click->referrer
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (Exception $e) {
            return $this->handleException($e, 'exporting data');
        }
    }
}