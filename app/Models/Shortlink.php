<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shortlink extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'domain_id',
        'short_code',
        'original_url',
        'title',
        'description',
        'password',
        'tags',
        'is_active',
        'expires_at',
        'click_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * Get the domain that owns the shortlink.
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Get the clicks for the shortlink.
     */
    public function clicks()
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Get the clicks count attribute (from database column)
     */
    public function getClicksCountAttribute()
    {
        return $this->click_count ?? 0;
    }
    
    /**
     * Increment the click count
     */
    public function incrementClickCount()
    {
        $this->increment('click_count');
    }

    /**
     * Get the full short URL.
     */
    public function getShortUrlAttribute()
    {
        return $this->domain->name . '/' . $this->short_code;
    }

    /**
     * Scope a query to only include active shortlinks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include non-expired shortlinks.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if the shortlink is expired.
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
