<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Digital Marketing',
                'code' => 'DM001',
            ],
            [
                'name' => 'Sydital',
                'code' => 'SYD001',
            ],
            [
                'name' => 'Detax',
                'code' => 'DTX001',
            ],
            [
                'name' => 'HR',
                'code' => 'HR001',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
