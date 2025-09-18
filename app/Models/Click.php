<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shortlink_id',
        'ip_address',
        'user_agent',
        'referer',
        'country_code',
        'country',
        'region',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'timezone',
        'browser',
        'browser_version',
        'operating_system',
        'operating_system_version',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'is_robot',
        'referrer_url',
        'clicked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the shortlink that owns the click.
     */
    public function shortlink()
    {
        return $this->belongsTo(Shortlink::class);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('clicked_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by country.
     */
    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Get the browser name from user agent.
     */
    public function getBrowserAttribute()
    {
        // If browser is already stored, return it
        if (!empty($this->attributes['browser'])) {
            return $this->attributes['browser'];
        }

        // Fallback to parsing user agent if browser not stored
        if (!$this->user_agent) {
            return 'Unknown';
        }

        // Simple browser detection
        if (strpos($this->user_agent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($this->user_agent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($this->user_agent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($this->user_agent, 'Edge') !== false) {
            return 'Edge';
        }

        return 'Other';
    }
}
