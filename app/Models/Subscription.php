<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const STATUSES = ['trialing', 'active', 'past_due', 'cancelled', 'expired'];

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'current_period_ends_at',
        'cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Still within its free trial window, whether or not it has since been
     * cancelled — cancelling doesn't cut a trial short, it just stops the
     * next paid period from starting.
     */
    public function onTrial(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Paid and currently within its billed period.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->current_period_ends_at && $this->current_period_ends_at->isFuture();
    }

    /**
     * Grants access — either a live trial or a live paid period.
     */
    public function grantsAccess(): bool
    {
        return $this->onTrial() || $this->isActive();
    }
}
