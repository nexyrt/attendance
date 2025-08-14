<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            OfficeLocationSeeder::class,
            UserSeeder::class,
            ScheduleSeeder::class,
            ScheduleExceptionSeeder::class,
            LeaveBalanceSeeder::class,
            AttendanceSeeder::class,
            LeaveRequestSeeder::class,
            SalaryHistorySeeder::class,
        ]);
    }
}
