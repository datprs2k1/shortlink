<?php

namespace App\Http\Controllers;

use App\Services\Click\ClickService;
use App\Services\Domain\DomainService;
use App\Services\Shortlink\ShortlinkService;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DomainService $domainService,
        private ShortlinkService $shortlinkService,
        private ClickService $clickService,
        private UserService $userService
    ) {}

    /**
     * Display the dashboard with statistics
     */
    public function index(Request $request)
    {
        // Get overall statistics
        $domainStats = $this->domainService->getDomainStats();
        $shortlinkStats = $this->shortlinkService->getShortlinkStats();
        $clickStats = $this->clickService->getClickStats();
        $userStats = $this->userService->getUserStats();

        // Get recent activity
        $recentShortlinks = $this->shortlinkService->getShortlinksWithClickCounts()
            ->sortByDesc('created_at')
            ->take(5);

        $recentClicks = $this->clickService->getRecentClicks(10);

        // Get top performing shortlinks
        $topShortlinks = $this->shortlinkService->getTopPerforming(5);

        // Get click trends for the last 7 days
        $clickTrends = $this->clickService->getDailyClickTrends(7);

        // Get domain statistics with shortlink counts
        $domainData = $this->domainService->getDomainsWithShortlinkCounts()->take(5);

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'domainStats' => $domainStats,
            'shortlinkStats' => $shortlinkStats,
            'clickStats' => $clickStats,
            'userStats' => $userStats,
            'recentShortlinks' => $recentShortlinks,
            'recentClicks' => $recentClicks,
            'topShortlinks' => $topShortlinks,
            'clickTrends' => $clickTrends,
            'domainData' => $domainData,
        ]);
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function stats(Request $request)
    {
        $period = $request->get('period', 7); // Default to 7 days

        $stats = [
            'clicks' => $this->clickService->getClickStats(),
            'shortlinks' => $this->shortlinkService->getShortlinkStats(),
            'domains' => $this->domainService->getDomainStats(),
            'trends' => $this->clickService->getDailyClickTrends($period),
            'topCountries' => $this->clickService->getTopCountries(5),
            'browserStats' => $this->clickService->getBrowserStats(),
        ];

        return response()->json($stats);
    }
}