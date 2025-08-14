<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'effective_date',
        'pay_slip',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
