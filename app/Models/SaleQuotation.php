<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SaleQuotation extends Model
{
    /**
     * pending -> approved -> converted, with rejected/cancelled as the
     * other exits. Never moves backward. Mirrors PurchaseRequisition::STATUSES.
     */
    public const STATUSES = ['pending', 'approved', 'rejected', 'converted', 'cancelled'];

    protected $fillable = [
        'quotation_no',
        'party_id',
        'site_id',
        'status',
        'quote_date',
        'valid_until',
        'note',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'created_by',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
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
        return $this->hasMany(SaleQuotationItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * The discount entered on the quotation, prorated the same way as
     * Sale::discountRate() — kept for parity even though a quotation
     * itself never posts to the ledger.
     */
    public function discountRate(): float
    {
        if ((float) $this->subtotal_amount <= 0) {
            return 0;
        }

        return (float) $this->discount_amount / (float) $this->subtotal_amount;
    }
}
