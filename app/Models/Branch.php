<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    /**
     * Branch Types available in the current version. New types can be
     * added here without a migration — `type` is a plain string column.
     */
    public const TYPES = [
        'Head Office',
        'Outlet',
        'Central Kitchen',
        'Cloud Kitchen',
        'Warehouse',
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
        return $this->belongsToMany(User::class, 'user_branches')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
