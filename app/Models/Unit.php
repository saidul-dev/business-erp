<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Products that use this unit as their Stock Unit. Products may also
     * reference this unit as a Purchase or Sale unit — see
     * UnitController::destroy() for the full in-use check across all three.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'stock_unit_id');
    }
}
