<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryPartner extends Model
{
    protected $fillable = [
        'name',
        'code',
        'phone',
        'contact_person',
        'api_base_url',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function consignments(): HasMany
    {
        return $this->hasMany(CourierConsignment::class);
    }
}
