<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryPartner extends Model
{
    /**
     * 'manual' keeps today's behavior — an admin books with the courier
     * outside this system and types the tracking number in by hand. Any
     * other value routes SaleController::deliver() through that provider's
     * Service class instead (see SteadfastService) to book automatically.
     */
    public const PROVIDERS = ['manual', 'steadfast'];

    protected $fillable = [
        'name',
        'code',
        'provider',
        'api_key',
        'secret_key',
        'phone',
        'contact_person',
        'api_base_url',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
        'api_key' => 'encrypted',
        'secret_key' => 'encrypted',
    ];

    public function consignments(): HasMany
    {
        return $this->hasMany(CourierConsignment::class);
    }

    public function bookedViaApi(): bool
    {
        return $this->provider !== 'manual';
    }
}
