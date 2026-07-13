<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseRequisition extends Model
{
    /**
     * pending -> approved -> converted, with rejected/cancelled as the
     * other exits. Never moves backward.
     */
    public const STATUSES = ['pending', 'approved', 'rejected', 'converted', 'cancelled'];

    protected $fillable = [
        'requisition_no',
        'site_id',
        'party_id',
        'status',
        'request_date',
        'required_by_date',
        'note',
        'estimated_total_amount',
        'created_by',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_by_date' => 'date',
        'estimated_total_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class);
    }
}
