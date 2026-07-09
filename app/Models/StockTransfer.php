<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockTransfer extends Model
{
    /**
     * Dispatch → Receive, with Cancel as the only other exit from
     * in_transit. Never moves back to in_transit once left — corrections
     * happen by cancelling (which reverses stock at from_site), not by
     * mutating a transfer in place.
     */
    public const STATUSES = ['in_transit', 'received', 'cancelled'];

    protected $fillable = [
        'transfer_no',
        'from_site_id',
        'to_site_id',
        'status',
        'note',
        'dispatched_at',
        'dispatched_by',
        'received_at',
        'received_by',
    ];

    protected $casts = [
        'dispatched_at' => 'date',
        'received_at' => 'date',
    ];

    public function fromSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'from_site_id');
    }

    public function toSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'to_site_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}
