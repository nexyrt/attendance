<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            [
                'day_of_week' => 'monday',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'late_tolerance' => 30, // 30 minutes
            ],
            [
                'day_of_week' => 'tuesday',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'late_tolerance' => 30,
            ],
            [
                'day_of_week' => 'wednesday',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'late_tolerance' => 30,
            ],
            [
                'day_of_week' => 'thursday',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'late_tolerance' => 30,
            ],
            [
                'day_of_week' => 'friday',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00', // Jumat pulang lebih cepat
                'late_tolerance' => 30,
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
