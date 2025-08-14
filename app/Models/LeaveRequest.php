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

    // Arrays for Validation
    public const STATUSES = [
        self::STATUS_PENDING_MANAGER,
        self::STATUS_PENDING_HR,
        self::STATUS_PENDING_DIRECTOR,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED_MANAGER,
        self::STATUS_REJECTED_HR,
        self::STATUS_REJECTED_DIRECTOR,
        self::STATUS_CANCEL
    ];

    public const TYPES = [
        self::TYPE_SICK,
        self::TYPE_ANNUAL,
        self::TYPE_IMPORTANT,
        self::TYPE_OTHER
    ];

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

    // Status Check Methods
    public function isPendingManager(): bool
    {
        return $this->status === self::STATUS_PENDING_MANAGER;
    }

    public function isPendingHR(): bool
    {
        return $this->status === self::STATUS_PENDING_HR;
    }

    public function isPendingDirector(): bool
    {
        return $this->status === self::STATUS_PENDING_DIRECTOR;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejectedByManager(): bool
    {
        return $this->status === self::STATUS_REJECTED_MANAGER;
    }

    public function isRejectedByHR(): bool
    {
        return $this->status === self::STATUS_REJECTED_HR;
    }

    public function isRejectedByDirector(): bool
    {
        return $this->status === self::STATUS_REJECTED_DIRECTOR;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCEL;
    }

    public function isRejected(): bool
    {
        return in_array($this->status, [
            self::STATUS_REJECTED_MANAGER,
            self::STATUS_REJECTED_HR,
            self::STATUS_REJECTED_DIRECTOR
        ]);
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

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_MANAGER,
            self::STATUS_PENDING_HR,
            self::STATUS_PENDING_DIRECTOR => 'bg-yellow-100 text-yellow-800',

            self::STATUS_APPROVED => 'bg-green-100 text-green-800',

            self::STATUS_REJECTED_MANAGER,
            self::STATUS_REJECTED_HR,
            self::STATUS_REJECTED_DIRECTOR => 'bg-red-100 text-red-800',

            self::STATUS_CANCEL => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
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

    // Scopes
    public function scopePendingManager($query)
    {
        return $query->where('status', self::STATUS_PENDING_MANAGER);
    }

    public function scopePendingHR($query)
    {
        return $query->where('status', self::STATUS_PENDING_HR);
    }

    public function scopePendingDirector($query)
    {
        return $query->where('status', self::STATUS_PENDING_DIRECTOR);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING_MANAGER,
            self::STATUS_PENDING_HR,
            self::STATUS_PENDING_DIRECTOR
        ]);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING_MANAGER,
            self::STATUS_PENDING_HR,
            self::STATUS_PENDING_DIRECTOR,
            self::STATUS_APPROVED
        ])->where('end_date', '>=', now());
    }
}