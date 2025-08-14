<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'description'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    // Check if a given date falls within this holiday
    public function includesDate($date)
    {
        return $date->between($this->start_date, $this->end_date);
    }

    // Scope for upcoming holidays
    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now());
    }

    // Scope for past holidays
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }
}
