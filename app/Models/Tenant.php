<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'owner_user_id',
        'status',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * A tenant accumulates one Subscription row per plan change/renewal —
     * the latest one is always its current standing, never mutated in place
     * once superseded (see BillingController).
     */
    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()->latest('id')->first();
    }

    public function currentPlan(): ?Plan
    {
        return $this->currentSubscription()?->plan;
    }

    public function onTrial(): bool
    {
        return (bool) $this->currentSubscription()?->onTrial();
    }

    public function subscribed(): bool
    {
        return (bool) $this->currentSubscription()?->isActive();
    }

    public function hasActiveAccess(): bool
    {
        return (bool) $this->currentSubscription()?->grantsAccess();
    }

    /**
     * False once branches() count is at or past the current plan's limit —
     * a plan with no subscription yet (shouldn't happen post-registration)
     * fails closed at 1, not open/unlimited.
     */
    public function canCreateAnotherBranch(): bool
    {
        $maxBranches = $this->currentPlan()?->max_branches ?? 1;

        return $this->branches()->count() < $maxBranches;
    }
}
