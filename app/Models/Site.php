<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Site extends Model
{
    /**
     * Site Types available in the current version. New types can be
     * added here without a migration — `type` is a plain string column.
     */
    public const TYPES = [
        'Head Office',
        'Branch',
        'Warehouse',
        'Outlet',
        'Factory',
        'Depot',
        'Distribution Center',
        'Service Center',
        'Showroom',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sites')
            ->withPivot('is_default')
            ->withTimestamps();
    }
}
