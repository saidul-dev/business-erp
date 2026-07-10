<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    /**
     * pending -> partial -> received, with cancelled as the only other
     * exit. Never moves backward — see recalculateStatus().
     */
    public const STATUSES = ['pending', 'partial', 'received', 'cancelled'];

    protected $fillable = [
        'purchase_no',
        'party_id',
        'site_id',
        'status',
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
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * pending (nothing received) / partial (some but not all lines fully
     * received) / received (every line fully received). Never touches
     * `cancelled` — cancelling is a separate, explicit action.
     */
    public function recalculateStatus(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $items = $this->items;
        $totalReceived = $items->sum('received_quantity');

        $status = match (true) {
            $totalReceived <= 0 => 'pending',
            $items->every(fn (PurchaseItem $item) => (float) $item->received_quantity >= (float) $item->quantity) => 'received',
            default => 'partial',
        };

        if ($status !== $this->status) {
            $this->update(['status' => $status]);
        }
    }

    /**
     * The discount entered on the order, prorated onto a receipt's gross
     * value by its share of the order's subtotal — see
     * PurchaseController::receive(). Mirrors Sale::discountRate() exactly.
     */
    public function discountRate(): float
    {
        if ((float) $this->subtotal_amount <= 0) {
            return 0;
        }

        return (float) $this->discount_amount / (float) $this->subtotal_amount;
    }
}
