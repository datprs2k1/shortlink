<?php

namespace App\Services\Analytics;

use App\Models\Click;
use App\Models\Domain;
use App\Models\Shortlink;
use App\Repositories\Click\IClickRepository;
use App\Repositories\Domain\IDomainRepository;
use App\Repositories\Shortlink\IShortlinkRepository;
use App\Services\_Abstract\BaseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService extends BaseService
{
    // Constants
    private const DEFAULT_PERIOD_DAYS = 30;
    private const TOP_ITEMS_LIMIT = 10;
    private const CHART_DATA_POINTS = 30;
    
    private const FIELD_CREATED_AT = 'created_at';
    private const FIELD_SHORTLINK_ID = 'shortlink_id';
    private const FIELD_DOMAIN_ID = 'domain_id';
    private const FIELD_IS_ACTIVE = 'is_active';
    
    private IDomainRepository $domainRepository;
    private IShortlinkRepository $shortlinkRepository;

    public function __construct(
        IClickRepository $clickRepository,
        IDomainRepository $domainRepository,
        IShortlinkRepository $shortlinkRepository
    ) {
        $this->mainRepository = $clickRepository;
        $this->domainRepository = $domainRepository;
        $this->shortlinkRepository = $shortlinkRepository;
    }

    // ========================================
    // Overview Statistics
    // ========================================

    /**
     * Get comprehensive analytics overview
     */
    public function getOverviewStats(array $filters = []): array
    {
        $period = $this->getPeriodFromFilters($filters);
        
        return [
            'total_clicks' => $this->getTotalClicks($filters),
            'unique_visitors' => $this->getUniqueVisitors($filters),
            'total_shortlinks' => $this->getTotalShortlinks($filters),
            'active_domains' => $this->getActiveDomains(),
            'clicks_today' => $this->getClicksToday($filters),
            'clicks_this_week' => $this->getClicksThisWeek($filters),
            'clicks_this_month' => $this->getClicksThisMonth($filters),
            'growth_rate' => $this->getGrowthRate($filters, $period),
            'average_daily_clicks' => $this->getAverageDailyClicks($filters, $period),
            'avg_clicks_per_link' => $this->getAvgClicksPerLink($filters),
            'top_country' => $this->getTopCountry($filters),
        ];
    }

    /**
     * Get chart data for analytics dashboard
     */
    public function getChartData(array $filters = []): array
    {
        $period = $this->getPeriodFromFilters($filters);
        $interval = $this->getIntervalFromPeriod($period);
        
        $clicksOverTime = $this->getClicksOverTime($filters, $period, $interval);
        
        // Transform data for chart.js format
        $dates = [];
        $clicks = [];
        foreach ($clicksOverTime as $dataPoint) {
            $dates[] = $dataPoint['date'];
            $clicks[] = $dataPoint['clicks'];
        }
        
        return [
            'dates' => $dates,
            'clicks' => $clicks,
            'clicks_over_time' => $clicksOverTime,
            'shortlinks_created' => $this->getShortlinksCreatedOverTime($filters, $period, $interval),
            'top_hours' => $this->getTopHours($filters),
            'top_days' => $this->getTopDays($filters),
        ];
    }

    // ========================================
    // Performance Analytics
    // ========================================

    /**
     * Get top performing shortlinks
     */
    public function getTopShortlinks(array $filters = [], int $limit = self::TOP_ITEMS_LIMIT): Collection
    {
        $query = $this->shortlinkRepository->getQuery()
            ->withCount('clicks')
            ->with(['domain'])
            ->orderByDesc('clicks_count');

        $this->applyShortlinkFilters($query, $filters);
        
        return $query->limit($limit)->get();
    }

    /**
     * Get top performing domains
     */
    public function getTopDomains(array $filters = [], int $limit = self::TOP_ITEMS_LIMIT): array
    {
        $query = DB::table('clicks')
            ->join('shortlinks', 'clicks.shortlink_id', '=', 'shortlinks.id')
            ->join('domains', 'shortlinks.domain_id', '=', 'domains.id')
            ->select('domains.name', 'domains.id', DB::raw('COUNT(*) as click_count'))
            ->groupBy('domains.id', 'domains.name');

        $this->applyClickFilters($query, $filters);
        
        return $query->orderByDesc('click_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get shortlink performance metrics
     */
    public function getShortlinkMetrics(int $shortlinkId): array
    {
        $shortlink = $this->shortlinkRepository->findById($shortlinkId);
        
        if (!$shortlink) {
            throw new Exception('Shortlink not found.');
        }

        $totalClicks = $this->mainRepository->getQuery()
            ->where(self::FIELD_SHORTLINK_ID, $shortlinkId)
            ->count();

        $uniqueVisitors = $this->mainRepository->getQuery()
            ->where(self::FIELD_SHORTLINK_ID, $shortlinkId)
            ->distinct('ip_address')
            ->count();

        $clicksToday = $this->mainRepository->getQuery()
            ->where(self::FIELD_SHORTLINK_ID, $shortlinkId)
            ->whereDate(self::FIELD_CREATED_AT, today())
            ->count();

        $clicksThisWeek = $this->mainRepository->getQuery()
            ->where(self::FIELD_SHORTLINK_ID, $shortlinkId)
            ->whereBetween(self::FIELD_CREATED_AT, [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            'shortlink' => $shortlink,
            'total_clicks' => $totalClicks,
            'unique_visitors' => $uniqueVisitors,
            'clicks_today' => $clicksToday,
            'clicks_this_week' => $clicksThisWeek,
            'click_through_rate' => $this->calculateClickThroughRate($shortlinkId),
            'peak_day' => $this->getPeakDay($shortlinkId),
            'recent_activity' => $this->getRecentActivity($shortlinkId),
        ];
    }

    // ========================================
    // Geographic & Device Analytics
    // ========================================

    /**
     * Get geographic data for clicks
     */
    public function getGeographicData(array $filters = []): array
    {
        $query = $this->mainRepository->getQuery()
            ->select('country', 'city', DB::raw('COUNT(*) as click_count'))
            ->whereNotNull('country')
            ->groupBy('country', 'city');

        $this->applyClickFiltersToEloquent($query, $filters);
        
        $results = $query->orderByDesc('click_count')
            ->limit(50)
            ->get();

        return [
            'countries' => $this->groupByCountry($results),
            'cities' => $results->take(self::TOP_ITEMS_LIMIT)->toArray(),
            'total_countries' => $results->pluck('country')->unique()->count(),
        ];
    }

    /**
     * Get device analytics data
     */
    public function getDeviceData(array $filters = []): array
    {
        $query = $this->mainRepository->getQuery();
        $this->applyClickFiltersToEloquent($query, $filters);
        
        // Count devices based on boolean columns
        $mobileCount = (clone $query)->where('is_mobile', 1)->count();
        $tabletCount = (clone $query)->where('is_tablet', 1)->count();
        $desktopCount = (clone $query)->where('is_desktop', 1)->count();
        $robotCount = (clone $query)->where('is_robot', 1)->count();
        
        return [
            'browsers' => [],
            'operating_systems' => [],
            'devices' => [
                ['device_type' => 'Mobile', 'count' => $mobileCount],
                ['device_type' => 'Tablet', 'count' => $tabletCount],
                ['device_type' => 'Desktop', 'count' => $desktopCount],
                ['device_type' => 'Robot', 'count' => $robotCount],
            ],
        ];
    }

    /**
     * Get referrer analytics data
     */
    public function getReferrerData(array $filters = []): array
    {
        $query = $this->mainRepository->getQuery()
            ->select('referer', DB::raw('COUNT(*) as click_count'))
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->groupBy('referer');

        $this->applyClickFiltersToEloquent($query, $filters);
        
        $referrers = $query->orderByDesc('click_count')
            ->limit(self::TOP_ITEMS_LIMIT)
            ->get()
            ->toArray();

        return [
            'top_referrers' => $referrers,
            'referrer_domains' => $this->groupReferrersByDomain($referrers),
        ];
    }

    // ========================================
    // Time-based Analytics
    // ========================================

    /**
     * Get clicks over time data
     */
    public function getClicksOverTime(array $filters = [], int $days = self::DEFAULT_PERIOD_DAYS, string $interval = 'day'): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();
        
        $query = $this->mainRepository->getQuery()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"),
                DB::raw('COUNT(*) as clicks')
            )
            ->whereBetween(self::FIELD_CREATED_AT, [$startDate, $endDate])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
            ->orderBy('date');

        $this->applyClickFiltersToEloquent($query, $filters);
        
        return $query->get()->toArray();
    }

    /**
     * Get real-time analytics data
     */
    public function getRealTimeData(): array
    {
        $lastHour = now()->subHour();
        $last24Hours = now()->subDay();
        
        return [
            'clicks_last_hour' => $this->mainRepository->getQuery()
                ->where(self::FIELD_CREATED_AT, '>=', $lastHour)
                ->count(),
            'clicks_last_24h' => $this->mainRepository->getQuery()
                ->where(self::FIELD_CREATED_AT, '>=', $last24Hours)
                ->count(),
            'active_shortlinks' => $this->getActiveShortlinksCount(),
            'recent_clicks' => $this->getRecentClicks(20),
            'hourly_breakdown' => $this->getHourlyBreakdown(),
        ];
    }

    // ========================================
    // Export & Reporting
    // ========================================

    /**
     * Get data for analytics export
     */
    public function getExportData(array $filters = []): Collection
    {
        $query = $this->mainRepository->getQuery()
            ->with(['shortlink.domain'])
            ->select([
                'clicks.*',
                'shortlinks.short_code',
                'shortlinks.original_url',
                'domains.name as domain_name'
            ])
            ->join('shortlinks', 'clicks.shortlink_id', '=', 'shortlinks.id')
            ->join('domains', 'shortlinks.domain_id', '=', 'domains.id');

        $this->applyClickFilters($query, $filters);
        
        return $query->orderByDesc('clicks.created_at')->get();
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    /**
     * Get period from filters
     */
    private function getPeriodFromFilters(array $filters): int
    {
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $start = Carbon::parse($filters['start_date']);
            $end = Carbon::parse($filters['end_date']);
            return $start->diffInDays($end);
        }
        
        return $filters['period'] ?? self::DEFAULT_PERIOD_DAYS;
    }

    /**
     * Get interval based on period length
     */
    private function getIntervalFromPeriod(int $days): string
    {
        if ($days <= 7) return 'hour';
        if ($days <= 31) return 'day';
        if ($days <= 365) return 'week';
        return 'month';
    }

    /**
     * Apply click filters to query builder
     */
    private function applyClickFilters($query, array $filters): void
    {
        if (!empty($filters['domain_id'])) {
            $query->where('shortlinks.domain_id', $filters['domain_id']);
        }
        
        if (!empty($filters['shortlink_id'])) {
            $query->where('clicks.shortlink_id', $filters['shortlink_id']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('clicks.created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('clicks.created_at', '<=', $filters['end_date']);
        }
        
        if (!empty($filters['country'])) {
            $query->where('clicks.country', $filters['country']);
        }
    }

    /**
     * Apply click filters to eloquent query
     */
    private function applyClickFiltersToEloquent($query, array $filters): void
    {
        if (!empty($filters['start_date'])) {
            $query->where(self::FIELD_CREATED_AT, '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where(self::FIELD_CREATED_AT, '<=', $filters['end_date']);
        }
        
        // Apply device type filter using boolean columns
        if (!empty($filters['device_type'])) {
            $deviceType = strtolower($filters['device_type']);
            switch ($deviceType) {
                case 'mobile':
                    $query->where('is_mobile', 1);
                    break;
                case 'tablet':
                    $query->where('is_tablet', 1);
                    break;
                case 'desktop':
                    $query->where('is_desktop', 1);
                    break;
                case 'robot':
                    $query->where('is_robot', 1);
                    break;
            }
        }
        
        // Apply country filter
        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }
    }

    /**
     * Apply shortlink filters
     */
    private function applyShortlinkFilters($query, array $filters): void
    {
        if (!empty($filters['domain_id'])) {
            $query->where(self::FIELD_DOMAIN_ID, $filters['domain_id']);
        }
    }

    /**
     * Get total clicks count
     */
    private function getTotalClicks(array $filters): int
    {
        $query = $this->mainRepository->getQuery();
        $this->applyClickFiltersToEloquent($query, $filters);
        return $query->count();
    }

    /**
     * Get unique visitors count
     */
    private function getUniqueVisitors(array $filters): int
    {
        $query = $this->mainRepository->getQuery();
        $this->applyClickFiltersToEloquent($query, $filters);
        return $query->distinct('ip_address')->count();
    }

    /**
     * Get total shortlinks count
     */
    private function getTotalShortlinks(array $filters): int
    {
        $query = $this->shortlinkRepository->getQuery();
        $this->applyShortlinkFilters($query, $filters);
        return $query->count();
    }

    /**
     * Get active domains count
     */
    private function getActiveDomains(): int
    {
        return $this->domainRepository->getQuery()
            ->where(self::FIELD_IS_ACTIVE, true)
            ->count();
    }

    /**
     * Get clicks today
     */
    private function getClicksToday(array $filters): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereDate(self::FIELD_CREATED_AT, today());
        $this->applyClickFiltersToEloquent($query, $filters);
        return $query->count();
    }

    /**
     * Get clicks this week
     */
    private function getClicksThisWeek(array $filters): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereBetween(self::FIELD_CREATED_AT, [now()->startOfWeek(), now()->endOfWeek()]);
        $this->applyClickFiltersToEloquent($query, $filters);
        return $query->count();
    }

    /**
     * Get clicks this month
     */
    private function getClicksThisMonth(array $filters): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereBetween(self::FIELD_CREATED_AT, [now()->startOfMonth(), now()->endOfMonth()]);
        $this->applyClickFiltersToEloquent($query, $filters);
        return $query->count();
    }

    /**
     * Calculate growth rate
     */
    private function getGrowthRate(array $filters, int $period): float
    {
        $currentPeriodClicks = $this->getTotalClicks($filters);
        
        $previousPeriodFilters = $filters;
        $previousPeriodFilters['start_date'] = now()->subDays($period * 2)->format('Y-m-d');
        $previousPeriodFilters['end_date'] = now()->subDays($period)->format('Y-m-d');
        
        $previousPeriodClicks = $this->getTotalClicks($previousPeriodFilters);
        
        if ($previousPeriodClicks == 0) return 0;
        
        return (($currentPeriodClicks - $previousPeriodClicks) / $previousPeriodClicks) * 100;
    }

    /**
     * Get average daily clicks
     */
    private function getAverageDailyClicks(array $filters, int $period): float
    {
        $totalClicks = $this->getTotalClicks($filters);
        return $period > 0 ? $totalClicks / $period : 0;
    }


    /**
     * Get average clicks per link
     */
    private function getAvgClicksPerLink(array $filters = []): string
    {
        $totalClicks = $this->getTotalClicks($filters);
        $totalShortlinks = $this->getTotalShortlinks($filters);
        
        if ($totalShortlinks === 0) {
            return '0';
        }
        
        $average = $totalClicks / $totalShortlinks;
        return number_format($average, 1);
    }

    /**
     * Get top country by clicks
     */
    private function getTopCountry(array $filters = []): string
    {
        $query = $this->mainRepository->getQuery()
            ->select('country', DB::raw('COUNT(*) as click_count'))
            ->whereNotNull('country')
            ->groupBy('country');

        $this->applyClickFiltersToEloquent($query, $filters);
        
        $result = $query->orderByDesc('click_count')
            ->first();

        return $result ? $result->country : 'N/A';
    }

    /**
     * Group data by field
     */
    private function getGroupedData($query, string $field): array
    {
        return $query->clone()
            ->select($field, DB::raw('COUNT(*) as count'))
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->groupBy($field)
            ->orderByDesc('count')
            ->limit(self::TOP_ITEMS_LIMIT)
            ->get()
            ->toArray();
    }

    /**
     * Group results by country
     */
    private function groupByCountry(Collection $results): array
    {
        return $results->groupBy('country')
            ->map(function ($group) {
                return $group->sum('click_count');
            })
            ->sortDesc()
            ->take(self::TOP_ITEMS_LIMIT)
            ->toArray();
    }

    /**
     * Group referrers by domain
     */
    private function groupReferrersByDomain(array $referrers): array
    {
        $domains = [];
        
        foreach ($referrers as $referrer) {
            $domain = parse_url($referrer['referer'], PHP_URL_HOST) ?? 'Direct';
            $domains[$domain] = ($domains[$domain] ?? 0) + $referrer['click_count'];
        }
        
        arsort($domains);
        return array_slice($domains, 0, self::TOP_ITEMS_LIMIT, true);
    }

    /**
     * Get shortlinks created over time
     */
    private function getShortlinksCreatedOverTime(array $filters, int $days, string $interval): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();
        
        $query = $this->shortlinkRepository->getQuery()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"),
                DB::raw('COUNT(*) as shortlinks')
            )
            ->whereBetween(self::FIELD_CREATED_AT, [$startDate, $endDate])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
            ->orderBy('date');

        $this->applyShortlinkFilters($query, $filters);
        
        return $query->get()->toArray();
    }

    /**
     * Get top hours for clicks
     */
    private function getTopHours(array $filters): array
    {
        $query = $this->mainRepository->getQuery()
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as clicks')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour');

        $this->applyClickFiltersToEloquent($query, $filters);
        
        return $query->get()->toArray();
    }

    /**
     * Get top days for clicks
     */
    private function getTopDays(array $filters): array
    {
        $query = $this->mainRepository->getQuery()
            ->select(
                DB::raw('DAYOFWEEK(created_at) as day'),
                DB::raw('COUNT(*) as clicks')
            )
            ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
            ->orderBy('day');

        $this->applyClickFiltersToEloquent($query, $filters);
        
        return $query->get()->toArray();
    }

    /**
     * Calculate click through rate
     */
    private function calculateClickThroughRate(int $shortlinkId): float
    {
        // This would need to be implemented based on your tracking requirements
        // For now, return a placeholder
        return 0.0;
    }

    /**
     * Get peak day for shortlink
     */
    private function getPeakDay(int $shortlinkId): ?array
    {
        return $this->mainRepository->getQuery()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as clicks')
            )
            ->where(self::FIELD_SHORTLINK_ID, $shortlinkId)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('clicks')
            ->first()?->toArray();
    }

    /**
     * Get recent activity for shortlink
     */
    private function getRecentActivity(int $shortlinkId, int $limit = 10): Collection
    {
        return $this->mainRepository->getQuery()
            ->where(self::FIELD_SHORTLINK_ID, $shortlinkId)
            ->orderByDesc(self::FIELD_CREATED_AT)
            ->limit($limit)
            ->get();
    }

    /**
     * Get active shortlinks count
     */
    private function getActiveShortlinksCount(): int
    {
        return $this->shortlinkRepository->getQuery()
            ->where(self::FIELD_IS_ACTIVE, true)
            ->count();
    }

    /**
     * Get recent clicks
     */
    private function getRecentClicks(int $limit = 20): Collection
    {
        return $this->mainRepository->getQuery()
            ->with(['shortlink.domain'])
            ->orderByDesc(self::FIELD_CREATED_AT)
            ->limit($limit)
            ->get();
    }

    /**
     * Get hourly breakdown for today
     */
    private function getHourlyBreakdown(): array
    {
        return $this->mainRepository->getQuery()
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as clicks')
            )
            ->whereDate(self::FIELD_CREATED_AT, today())
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->toArray();
    }
}