<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /**
     * pending -> partial -> delivered, with cancelled as the only other
     * exit. Never moves backward — see recalculateStatus(). Mirrors
     * Purchase::STATUSES exactly.
     */
    public const STATUSES = ['pending', 'partial', 'delivered', 'cancelled'];

    /**
     * Where the order originated — 'pos' (admin panel) vs 'online' (the
     * e-commerce storefront, once it can place orders). Purely descriptive:
     * every other rule (statuses, delivery, ledger posting) is identical
     * for both channels.
     */
    public const CHANNELS = ['pos', 'online'];

    protected $fillable = [
        'sale_no',
        'party_id',
        'sale_quotation_id',
        'site_id',
        'status',
        'channel',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'delivery_zone_name',
        'delivery_charge',
        'order_date',
        'note',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SaleQuotation::class, 'sale_quotation_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(SaleDelivery::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * pending (nothing delivered) / partial (some but not all lines fully
     * delivered) / delivered (every line fully delivered). Never touches
     * `cancelled` — cancelling is a separate, explicit action.
     */
    public function recalculateStatus(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $items = $this->items;
        $totalDelivered = $items->sum('delivered_quantity');

        $status = match (true) {
            $totalDelivered <= 0 => 'pending',
            $items->every(fn (SaleItem $item) => (float) $item->delivered_quantity >= (float) $item->quantity) => 'delivered',
            default => 'partial',
        };

        if ($status !== $this->status) {
            $this->update(['status' => $status]);
        }
    }

    /**
     * The discount entered on the order, prorated onto a delivery's gross
     * revenue by its share of the order's subtotal — see
     * SaleController::deliver(). Zero subtotal (shouldn't happen once
     * items exist) or zero discount both simply yield zero.
     */
    public function discountRate(): float
    {
        if ((float) $this->subtotal_amount <= 0) {
            return 0;
        }

        return (float) $this->discount_amount / (float) $this->subtotal_amount;
    }
}
