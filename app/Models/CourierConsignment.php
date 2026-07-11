<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CourierConsignment extends Model
{
    /**
     * booked -> picked_up -> in_transit -> delivered, with returned/lost as
     * the alternate exits from any of the first three. Never moves
     * backward — mirrors Purchase/Sale's own status convention.
     */
    public const STATUSES = ['booked', 'picked_up', 'in_transit', 'delivered', 'returned', 'lost'];

    public const ACTIVE_STATUSES = ['booked', 'picked_up', 'in_transit'];

    protected $fillable = [
        'sale_delivery_id',
        'delivery_partner_id',
        'tracking_no',
        'cod_amount',
        'courier_fee',
        'status',
        'cod_settled_at',
        'booked_by',
        'note',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'courier_fee' => 'decimal:2',
        'cod_settled_at' => 'date',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(SaleDelivery::class, 'sale_delivery_id');
    }

    public function deliveryPartner(): BelongsTo
    {
        return $this->belongsTo(DeliveryPartner::class);
    }

    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function ledgerTransaction(): MorphOne
    {
        return $this->morphOne(LedgerTransaction::class, 'reference');
    }

    /**
     * A COD amount can only be settled once the parcel is actually marked
     * Delivered, and only once — cod_settled_at being set is the guard
     * against double-posting the same remittance to the ledger.
     */
    public function canSettleCod(): bool
    {
        return $this->status === 'delivered' && $this->cod_amount > 0 && ! $this->cod_settled_at;
    }
}
