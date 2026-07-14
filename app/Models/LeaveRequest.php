<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class LeaveRequest extends Model
{
    use Auditable;

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'days',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'days' => 'decimal:1',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (LeaveRequest $request) {
            if (! $request->days) {
                $request->days = Carbon::parse($request->from_date)->diffInDays(Carbon::parse($request->to_date)) + 1;
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Returns false when this approval pushes the employee's balance for
     * the year negative — the caller decides whether to just warn (the
     * approver still has final say, same "editable templates, not fixed
     * business logic" spirit as the doc's Level 3 compliance note) rather
     * than hard-blocking.
     */
    public function approve(User $actor): bool
    {
        if ($this->status !== 'pending') {
            throw new RuntimeException('Only a pending leave request can be approved.');
        }

        $balance = LeaveBalance::firstOrCreateFor($this->employee, $this->leaveType, $this->from_date->year);
        $sufficientBalance = $balance->remainingDays() >= (float) $this->days;
        $balance->increment('used_days', $this->days);

        $this->update([
            'status' => 'approved',
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
        ]);

        return $sufficientBalance;
    }

    public function reject(User $actor, ?string $reason = null): void
    {
        if ($this->status !== 'pending') {
            throw new RuntimeException('Only a pending leave request can be rejected.');
        }

        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
