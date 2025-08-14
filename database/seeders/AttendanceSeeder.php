<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', '!=', 'director')->get(); // Director jarang absen manual
        $officeLocations = OfficeLocation::all();
        $mainOffice = $officeLocations->first();

        // Generate attendance untuk 30 hari terakhir (working days only)
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(30);

        foreach ($users as $user) {
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                // Skip weekends
                if (!$currentDate->isWeekend()) {
                    $attendanceData = $this->generateAttendanceForUser($user, $currentDate, $mainOffice);
                    if ($attendanceData) {
                        Attendance::create($attendanceData);
                    }
                }
                $currentDate->addDay();
            }
        }
    }

    private function generateAttendanceForUser(User $user, Carbon $date, $officeLocation): ?array
    {
        // 85% kemungkinan hadir
        if (rand(1, 100) > 85) {
            return null; // Tidak hadir
        }

        $baseCheckIn = $date->copy()->setTime(8, 0, 0); // 08:00 base time
        $baseCheckOut = $date->copy()->setTime(17, 0, 0); // 17:00 base time
        
        // Adjust Friday checkout time
        if ($date->isFriday()) {
            $baseCheckOut = $date->copy()->setTime(16, 0, 0); // 16:00 on Friday
        }

        // Generate realistic check-in time (07:30 - 09:00)
        $checkInMinutes = rand(-30, 60); // -30 to +60 minutes from 08:00
        $checkIn = $baseCheckIn->copy()->addMinutes($checkInMinutes);
        
        // Generate realistic check-out time (16:30 - 18:00)
        $checkOutMinutes = rand(-30, 60); // -30 to +60 minutes from base checkout
        $checkOut = $baseCheckOut->copy()->addMinutes($checkOutMinutes);

        // Calculate late hours
        $lateHours = 0;
        if ($checkIn->greaterThan($baseCheckIn->copy()->addMinutes(30))) { // More than 30 min late
            $lateHours = $checkIn->diffInMinutes($baseCheckIn->copy()->addMinutes(30)) / 60;
        }

        // Calculate working hours
        $workingHours = $checkOut->diffInMinutes($checkIn) / 60;
        
        // Subtract 1 hour for lunch break if worked more than 4 hours
        if ($workingHours > 4) {
            $workingHours -= 1;
        }

        // Determine status
        $status = 'present';
        if ($lateHours > 0) {
            $status = 'late';
        }
        
        // 5% chance of early leave
        if (rand(1, 100) <= 5 && $checkOut->lessThan($baseCheckOut->copy()->subMinutes(60))) {
            $status = 'early_leave';
        }

        // Generate realistic location data (with some variance)
        $latVariance = (rand(-50, 50) / 1000000); // Small variance in location
        $lngVariance = (rand(-50, 50) / 1000000);

        return [
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'check_in' => $checkIn->format('Y-m-d H:i:s'),
            'check_out' => $checkOut->format('Y-m-d H:i:s'),
            'late_hours' => round($lateHours, 2),
            'status' => $status,
            'working_hours' => round($workingHours, 2),
            'early_leave_reason' => $status === 'early_leave' ? 'Keperluan keluarga' : null,
            'notes' => $this->getRandomNotes(),
            'check_in_latitude' => $officeLocation->latitude + $latVariance,
            'check_in_longitude' => $officeLocation->longitude + $lngVariance,
            'check_out_latitude' => $officeLocation->latitude + $latVariance,
            'check_out_longitude' => $officeLocation->longitude + $lngVariance,
            'check_in_office_id' => $officeLocation->id,
            'check_out_office_id' => $officeLocation->id,
            'device_type' => $this->getRandomDeviceType(),
            'created_at' => $checkIn,
            'updated_at' => $checkOut,
        ];
    }

    private function getRandomNotes(): ?string
    {
        $notes = [
            null,
            null,
            null, // More chances for null
            'Meeting dengan klien',
            'Lembur project deadline',
            'Training internal',
            'Rapat departemen',
            'Koordinasi dengan tim',
        ];

        return $notes[array_rand($notes)];
    }

    private function getRandomDeviceType(): string
    {
        $devices = [
            'Android',
            'iOS',
            'Web Browser',
            'Mobile App',
        ];

        return $devices[array_rand($devices)];
    }
}
