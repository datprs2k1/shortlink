<?php

namespace App\Services\Click;

use App\Models\Click;
use App\Repositories\Click\IClickRepository;
use App\Services\_Abstract\BaseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class ClickService extends BaseService
{
    // Constants
    private const FIELD_SHORTLINK_ID = 'shortlink_id';
    private const FIELD_IP_ADDRESS = 'ip_address';
    private const FIELD_USER_AGENT = 'user_agent';
    private const FIELD_REFERER = 'referer';
    private const FIELD_COUNTRY_CODE = 'country_code';
    private const FIELD_CLICKED_AT = 'clicked_at';

    public function __construct(IClickRepository $clickRepository)
    {
        $this->mainRepository = $clickRepository;
    }

    // ========================================
    // Click Tracking Operations
    // ========================================

    /**
     * Record a new click
     */
    public function recordClick(array $clickData): Click
    {
        $clickData[self::FIELD_CLICKED_AT] = $clickData[self::FIELD_CLICKED_AT] ?? now();

        return $this->mainRepository->create($clickData);
    }

    /**
     * Record click for shortlink
     */
    public function recordShortlinkClick(int $shortlinkId, array $trackingData = []): Click
    {
        $clickData = array_merge($trackingData, [
            self::FIELD_SHORTLINK_ID => $shortlinkId,
            self::FIELD_CLICKED_AT => now(),
        ]);

        return $this->recordClick($clickData);
    }

    // ========================================
    // Query Operations
    // ========================================

    /**
     * Get clicks by shortlink
     */
    public function getClicksByShortlink(int $shortlinkId): Collection
    {
        return $this->mainRepository->findWhere([self::FIELD_SHORTLINK_ID => $shortlinkId]);
    }

    /**
     * Get clicks by date range
     */
    public function getClicksByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->mainRepository->getQuery()
            ->whereBetween(self::FIELD_CLICKED_AT, [$startDate, $endDate])
            ->get();
    }

    /**
     * Get clicks by country
     */
    public function getClicksByCountry(string $countryCode): Collection
    {
        return $this->mainRepository->findWhere([self::FIELD_COUNTRY_CODE => $countryCode]);
    }

    /**
     * Get recent clicks
     */
    public function getRecentClicks(int $limit = 100): Collection
    {
        return $this->mainRepository->getQuery()
            ->latest(self::FIELD_CLICKED_AT)
            ->limit($limit)
            ->get();
    }

    // ========================================
    // Analytics & Statistics
    // ========================================

    /**
     * Get click statistics
     */
    public function getClickStats(?int $shortlinkId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = $this->buildStatsQuery($shortlinkId, $startDate, $endDate);

        return [
            'total_clicks' => $query->count(),
            'unique_ips' => $query->distinct(self::FIELD_IP_ADDRESS)->count(),
            'countries_count' => $query->distinct(self::FIELD_COUNTRY_CODE)->count(),
            'today_clicks' => $this->getTodayClicks($shortlinkId),
            'this_week_clicks' => $this->getThisWeekClicks($shortlinkId),
            'this_month_clicks' => $this->getThisMonthClicks($shortlinkId),
        ];
    }

    /**
     * Get top countries by click count
     */
    public function getTopCountries(int $limit = 10, ?int $shortlinkId = null): Collection
    {
        $query = $this->mainRepository->getQuery();

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        return $query->select(self::FIELD_COUNTRY_CODE, DB::raw('COUNT(*) as click_count'))
            ->whereNotNull(self::FIELD_COUNTRY_CODE)
            ->groupBy(self::FIELD_COUNTRY_CODE)
            ->orderByDesc('click_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get browser statistics
     */
    public function getBrowserStats(?int $shortlinkId = null): SupportCollection
    {
        $query = $this->mainRepository->getQuery();

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        return $query->get()->groupBy('browser')->map(function ($clicks, $browser) {
            return [
                'browser' => $browser,
                'count' => $clicks->count(),
                'percentage' => 0, // Will be calculated after getting all data
            ];
        });
    }

    /**
     * Get daily click trends
     */
    public function getDailyClickTrends(int $days = 30, ?int $shortlinkId = null): Collection
    {
        $startDate = now()->subDays($days);
        $query = $this->mainRepository->getQuery()
            ->where(self::FIELD_CLICKED_AT, '>=', $startDate);

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        return $query->select(
                DB::raw('DATE(' . self::FIELD_CLICKED_AT . ') as date'),
                DB::raw('COUNT(*) as clicks')
            )
            ->groupBy(DB::raw('DATE(' . self::FIELD_CLICKED_AT . ')'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Get hourly click distribution
     */
    public function getHourlyClickDistribution(?int $shortlinkId = null): Collection
    {
        $query = $this->mainRepository->getQuery();

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        return $query->select(
                DB::raw('HOUR(' . self::FIELD_CLICKED_AT . ') as hour'),
                DB::raw('COUNT(*) as clicks')
            )
            ->groupBy(DB::raw('HOUR(' . self::FIELD_CLICKED_AT . ')'))
            ->orderBy('hour')
            ->get();
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    /**
     * Build query for statistics
     */
    private function buildStatsQuery(?int $shortlinkId, ?Carbon $startDate, ?Carbon $endDate)
    {
        $query = $this->mainRepository->getQuery();

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween(self::FIELD_CLICKED_AT, [$startDate, $endDate]);
        }

        return $query;
    }

    /**
     * Get today's clicks count
     */
    private function getTodayClicks(?int $shortlinkId): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereDate(self::FIELD_CLICKED_AT, today());

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        return $query->count();
    }

    /**
     * Get this week's clicks count
     */
    private function getThisWeekClicks(?int $shortlinkId): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereBetween(self::FIELD_CLICKED_AT, [now()->startOfWeek(), now()->endOfWeek()]);

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        return $query->count();
    }

    /**
     * Get this month's clicks count
     */
    private function getThisMonthClicks(?int $shortlinkId): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereMonth(self::FIELD_CLICKED_AT, now()->month)
            ->whereYear(self::FIELD_CLICKED_AT, now()->year);

        if ($shortlinkId) {
            $query->where(self::FIELD_SHORTLINK_ID, $shortlinkId);
        }

        return $query->count();
    }
}