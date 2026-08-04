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
     * happen by cancelling (which reverses stock at from_branch), not by
     * mutating a transfer in place.
     */
    public const STATUSES = ['in_transit', 'received', 'cancelled'];

    protected $fillable = [
        'transfer_no',
        'from_branch_id',
        'to_branch_id',
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

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
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
