<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'late_hours',
        'working_hours',
        'early_leave_reason',
        'notes',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_office_id',
        'check_out_office_id',
        'device_type'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'working_hours' => 'decimal:2',
        'late_hours' => 'decimal:2',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checkInOffice()
    {
        return $this->belongsTo(OfficeLocation::class, 'check_in_office_id');
    }

    public function checkOutOffice()
    {
        return $this->belongsTo(OfficeLocation::class, 'check_out_office_id');
    }
}
