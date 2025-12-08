<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'phone_number',
        'birthdate',
        'salary',
        'address',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthdate' => 'date',
        'salary' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->initializeYearlyLeaveBalance();
        });
    }

    // Relationships
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveBalance()
    {
        return $this->hasOne(LeaveBalance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function approvedLeaves()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function currentLeaveBalance()
    {
        return $this->leaveBalances()
            ->where('year', now()->year)
            ->first();
    }

    public function initializeYearlyLeaveBalance($totalBalance = 12)
    {
        return $this->leaveBalances()->create([
            'year' => now()->year,
            'total_balance' => $totalBalance,
            'used_balance' => 0,
            'remaining_balance' => $totalBalance
        ]);
    }

    // ============================================================
    // SPATIE PERMISSION HELPER METHODS
    // ============================================================

    /**
     * Get the user's primary role name
     * Returns the first role name or null if no roles assigned
     */
    public function getRoleName(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Get all role names as array
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is director
     */
    public function isDirector(): bool
    {
        return $this->hasRole('director');
    }

    /**
     * Get user initials for avatar
     */
    public function getInitials(): string
    {
        $words = explode(' ', $this->name);

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }
}