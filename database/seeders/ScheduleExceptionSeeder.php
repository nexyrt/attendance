<?php

namespace Database\Seeders;

use App\Models\ScheduleException;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ScheduleExceptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();

        $scheduleExceptions = [
            // National Holidays 2025
            [
                'title' => 'Tahun Baru Masehi',
                'date' => '2025-01-01',
                'status' => 'holiday',
                'start_time' => null,
                'end_time' => null,
                'late_tolerance' => null,
                'note' => 'Libur nasional Tahun Baru Masehi',
            ],
            [
                'title' => 'Hari Raya Idul Fitri',
                'date' => '2025-03-30',
                'status' => 'holiday',
                'start_time' => null,
                'end_time' => null,
                'late_tolerance' => null,
                'note' => 'Libur nasional Hari Raya Idul Fitri (estimasi)',
            ],
            [
                'title' => 'Hari Raya Idul Fitri',
                'date' => '2025-03-31',
                'status' => 'holiday',
                'start_time' => null,
                'end_time' => null,
                'late_tolerance' => null,
                'note' => 'Libur nasional Hari Raya Idul Fitri (estimasi)',
            ],
            [
                'title' => 'Hari Buruh',
                'date' => '2025-05-01',
                'status' => 'holiday',
                'start_time' => null,
                'end_time' => null,
                'late_tolerance' => null,
                'note' => 'Libur nasional Hari Buruh',
            ],
            [
                'title' => 'Hari Kemerdekaan RI',
                'date' => '2025-08-17',
                'status' => 'holiday',
                'start_time' => null,
                'end_time' => null,
                'late_tolerance' => null,
                'note' => 'Libur nasional Hari Kemerdekaan Indonesia',
            ],
            [
                'title' => 'Hari Natal',
                'date' => '2025-12-25',
                'status' => 'holiday',
                'start_time' => null,
                'end_time' => null,
                'late_tolerance' => null,
                'note' => 'Libur nasional Hari Natal',
            ],
            // Company Events
            [
                'title' => 'Rapat Tahunan Perusahaan',
                'date' => '2025-06-15',
                'status' => 'event',
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'late_tolerance' => 15,
                'note' => 'Rapat tahunan seluruh karyawan, jam kerja disesuaikan',
            ],
            [
                'title' => 'Team Building Day',
                'date' => '2025-09-10',
                'status' => 'event',
                'start_time' => '08:30:00',
                'end_time' => '16:30:00',
                'late_tolerance' => 30,
                'note' => 'Acara team building perusahaan',
            ],
            [
                'title' => 'Training IT Security',
                'date' => '2025-04-20',
                'status' => 'event',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'late_tolerance' => 10,
                'note' => 'Pelatihan keamanan IT untuk semua departemen',
            ],
            // Department Specific Events
            [
                'title' => 'Workshop Digital Marketing',
                'date' => '2025-07-22',
                'status' => 'event',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'late_tolerance' => 20,
                'note' => 'Workshop khusus untuk tim Digital Marketing',
            ],
        ];

        foreach ($scheduleExceptions as $exception) {
            $scheduleException = ScheduleException::create($exception);
            
            // Attach departments to schedule exceptions
            if ($exception['title'] === 'Workshop Digital Marketing') {
                // Only for Digital Marketing department
                $dmDepartment = $departments->where('name', 'Digital Marketing')->first();
                if ($dmDepartment) {
                    $scheduleException->departments()->attach($dmDepartment->id);
                }
            } else {
                // For all departments (holidays and general events)
                $scheduleException->departments()->attach($departments->pluck('id'));
            }
        }
    }
}
