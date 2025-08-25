<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveRequest extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_PENDING_MANAGER = 'pending_manager';
    public const STATUS_PENDING_HR = 'pending_hr';
    public const STATUS_PENDING_DIRECTOR = 'pending_director';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED_MANAGER = 'rejected_manager';
    public const STATUS_REJECTED_HR = 'rejected_hr';
    public const STATUS_REJECTED_DIRECTOR = 'rejected_director';
    public const STATUS_CANCEL = 'cancel';

    // Leave Type Constants
    public const TYPE_SICK = 'sick';
    public const TYPE_ANNUAL = 'annual';
    public const TYPE_IMPORTANT = 'important';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'manager_id',
        'manager_approved_at',
        'manager_signature',
        'hr_id',
        'hr_approved_at',
        'hr_signature',
        'director_id',
        'director_approved_at',
        'director_signature',
        'attachment_path',
        'staff_signature',
        'rejection_reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'manager_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'director_approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function hr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_MANAGER,
            self::STATUS_PENDING_HR,
            self::STATUS_PENDING_DIRECTOR
        ]);
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending();
    }

    public function cancel(): bool
    {
        if ($this->canBeCancelled()) {
            return $this->update([
                'status' => self::STATUS_CANCEL
            ]);
        }
        return false;
    }

    public function getDurationInDays(): float
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        $duration = 0;
        for ($date = $start; $date->lessThanOrEqualTo($end); $date = $date->addDays(1)) {
            if (!$date->isWeekend()) {
                $duration++;
            }
        }

        return $duration;
    }

    // Scope for pending requests (used in stats)
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING_MANAGER,
            self::STATUS_PENDING_HR,
            self::STATUS_PENDING_DIRECTOR
        ]);
    }
}