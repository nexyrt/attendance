<?php

namespace App\Models;

use App\Models\User;
use App\Models\ScheduleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relationship with Schedule Exceptions
    public function scheduleExceptions()
    {
        return $this->belongsToMany(ScheduleException::class, 'department_schedule_exception')
            ->withTimestamps();
    }
}
