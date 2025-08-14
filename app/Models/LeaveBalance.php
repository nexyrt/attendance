<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'total_balance',
        'used_balance',
        'remaining_balance'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to update used and remaining balance
    public function updateBalance($usedDays)
    {
        $this->used_balance = $usedDays;
        $this->remaining_balance = $this->total_balance - $usedDays;
        $this->save();
    }

    // Scope for current year
    public function scopeCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }
}
