<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the shortlinks for the domain.
     */
    public function shortlinks()
    {
        return $this->hasMany(Shortlink::class);
    }

    /**
     * Get active shortlinks for the domain.
     */
    public function activeShortlinks()
    {
        return $this->hasMany(Shortlink::class)->where('is_active', true);
    }

    /**
     * Scope a query to only include active domains.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
