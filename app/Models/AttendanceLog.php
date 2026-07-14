<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use Auditable;

    public const STATUSES = ['present', 'absent', 'half_day', 'leave'];

    public const SOURCES = ['manual', 'self'];

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'check_in_at',
        'check_out_at',
        'is_late',
        'source',
        'marked_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
