<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use Auditable;

    public const STATUSES = ['planned', 'in_progress', 'completed', 'on_hold', 'cancelled'];

    protected $fillable = [
        'branch_id',
        'party_id',
        'project_manager_id',
        'name',
        'description',
        'status',
        'due_date',
        'estimated_hours',
        'budget_amount',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'budget_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * All tasks under this project, including those nested under a
     * Milestone — Task denormalizes project_id for exactly this query.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function collectedAmount(): float
    {
        return (float) $this->collections()->sum('amount');
    }

    public function remainingBudget(): ?float
    {
        return $this->budget_amount === null ? null : (float) $this->budget_amount - $this->collectedAmount();
    }

    public function timeLogs(): HasManyThrough
    {
        return $this->hasManyThrough(TaskTimeLog::class, Task::class);
    }

    public function actualHours(): float
    {
        return (float) $this->timeLogs()->sum('hours');
    }
}
