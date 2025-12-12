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
            ScheduleExceptionSeeder::class,
            UserSeeder::class,
            RolePermissionSeeder::class,
            LeaveBalanceSeeder::class,
        ]);
    }
}
