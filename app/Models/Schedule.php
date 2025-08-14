<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'late_tolerance'
    ];

    protected $casts = [
        'day_of_week' => 'string',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'late_tolerance' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday'
    ];
}
