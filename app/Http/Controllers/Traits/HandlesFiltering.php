<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait HandlesFiltering
{
    protected function getCommonFilters(Request $request): array
    {
        return [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'sort' => $request->get('sort', 'created_at'),
            'direction' => $request->get('direction', 'desc'),
            'per_page' => $request->get('per_page', 15),
            // Analytics-specific filters
            'device_type' => $request->get('device_type', ''),
            'country' => $request->get('country', ''),
        ];
    }

    /**
     * Get date range filters
     */
    protected function getDateFilters(Request $request): array
    {
        return [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'date_from' => $request->get('date_from', $request->get('start_date')),
            'date_to' => $request->get('date_to', $request->get('end_date')),
            'period' => $request->get('period', 30),
        ];
    }

    /**
     * Validate sort parameters
     */
    protected function validateSort(string $sort, array $allowedSorts): string
    {
        return in_array($sort, $allowedSorts) ? $sort : $allowedSorts[0] ?? 'created_at';
    }

    /**
     * Validate direction parameter
     */
    protected function validateDirection(string $direction): string
    {
        return in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'desc';
    }

    /**
     * Get pagination parameters
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = $request->get('per_page', 15);
        $perPage = is_numeric($perPage) && $perPage > 0 && $perPage <= 100 ? (int)$perPage : 15;
        
        return [
            'per_page' => $perPage,
            'page' => $request->get('page', 1)
        ];
    }

    /**
     * Build query parameters for pagination links
     */
    protected function buildQueryParams(array $filters): array
    {
        return array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });
    }
}