<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteHealthSnapshot extends Model
{
    protected $fillable = [
        'overall_score',
        'data_consistency_score',
        'inventory_accuracy_score',
        'financial_integrity_score',
        'pending_backlog_score',
        'details',
        'computed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'computed_at' => 'datetime',
    ];

    /**
     * Most recent computed snapshot, or null if the check has never run yet
     * (e.g. fresh install, before the scheduled/manual check first fires).
     */
    public static function current(): ?self
    {
        return static::latest('computed_at')->first();
    }
}
