<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'allocated_days' => 'decimal:1',
        'used_days' => 'decimal:1',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function remainingDays(): float
    {
        return (float) $this->allocated_days - (float) $this->used_days;
    }

    /**
     * Lazily allocates a balance row the first time an employee applies
     * for a given leave type/year — never pre-seeded in bulk.
     */
    public static function firstOrCreateFor(Employee $employee, LeaveType $leaveType, int $year): self
    {
        return static::firstOrCreate(
            ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => $year],
            ['allocated_days' => $leaveType->default_days_per_year, 'used_days' => 0]
        );
    }
}
