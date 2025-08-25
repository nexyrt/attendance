<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();

        // Create Admin Users
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone_number' => '+6281234567890',
            'birthdate' => '1985-01-15',
            'salary' => 15000000.00,
            'address' => 'Jl. Admin Street No. 1, Jakarta',
            'department_id' => $departments->where('name', 'HR')->first()->id,
        ]);

        // Create Director
        $director = User::create([
            'name' => 'Budi Santoso',
            'email' => 'direktur@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'director',
            'phone_number' => '+6281234567891',
            'birthdate' => '1975-03-20',
            'salary' => 25000000.00,
            'address' => 'Jl. Director Avenue No. 5, Jakarta',
            'department_id' => null,
        ]);

        // Create HR Manager
        $hrManager = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'hr@attendance.com',
            'password' => Hash::make('hr123'),
            'role' => 'manager',
            'phone_number' => '+6281234567892',
            'birthdate' => '1988-07-10',
            'salary' => 12000000.00,
            'address' => 'Jl. HR Complex No. 12, Jakarta',
            'department_id' => $departments->where('name', 'HR')->first()->id,
        ]);

        // Create Department Managers
        $digitalMarketingManager = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'phone_number' => '+6281234567893',
            'birthdate' => '1987-05-15',
            'salary' => 10000000.00,
            'address' => 'Jl. Marketing Street No. 8, Jakarta',
            'department_id' => $departments->where('name', 'Digital Marketing')->first()->id,
        ]);

        $syditilManager = User::create([
            'name' => 'Rina Wijaya',
            'email' => 'sydital.manager@attendance.com',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'phone_number' => '+6281234567894',
            'birthdate' => '1986-09-22',
            'salary' => 11000000.00,
            'address' => 'Jl. Sydital Plaza No. 15, Jakarta',
            'department_id' => $departments->where('name', 'Sydital')->first()->id,
        ]);

        $detaxManager = User::create([
            'name' => 'Joko Susilo',
            'email' => 'detax.manager@attendance.com',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'phone_number' => '+6281234567895',
            'birthdate' => '1984-12-08',
            'salary' => 10500000.00,
            'address' => 'Jl. Detax Center No. 20, Jakarta',
            'department_id' => $departments->where('name', 'Detax')->first()->id,
        ]);

        // Create Staff Members
        $staffMembers = [
            // Digital Marketing Staff
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.dm@attendance.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone_number' => '+6281234567896',
                'birthdate' => '1992-04-18',
                'salary' => 6500000.00,
                'address' => 'Jl. Staff Residence No. 101, Jakarta',
                'department_id' => $departments->where('name', 'Digital Marketing')->first()->id,
            ],
            [
                'name' => 'Randi Pratama',
                'email' => 'randi.pratama@attendance.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone_number' => '+6281234567897',
                'birthdate' => '1993-11-25',
                'salary' => 6000000.00,
                'address' => 'Jl. Staff Complex No. 102, Jakarta',
                'department_id' => $departments->where('name', 'Digital Marketing')->first()->id,
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya.dm@attendance.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone_number' => '+6281234567898',
                'birthdate' => '1994-02-14',
                'salary' => 6200000.00,
                'address' => 'Jl. Staff Avenue No. 103, Jakarta',
                'department_id' => $departments->where('name', 'Digital Marketing')->first()->id,
            ],
            // Sydital Staff
            [
                'name' => 'Arif Hidayat',
                'email' => 'arif.syd@attendance.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone_number' => '+6281234567899',
                'birthdate' => '1991-08-30',
                'salary' => 7000000.00,
                'address' => 'Jl. Sydital Street No. 201, Jakarta',
                'department_id' => $departments->where('name', 'Sydital')->first()->id,
            ],
            [
                'name' => 'Fitri Rahayu',
                'email' => 'fitri.syd@attendance.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone_number' => '+6281234567900',
                'birthdate' => '1990-06-12',
                'salary' => 7200000.00,
                'address' => 'Jl. Sydital Plaza No. 202, Jakarta',
                'department_id' => $departments->where('name', 'Sydital')->first()->id,
            ],
            // Detax Staff
            [
                'name' => 'Bambang Sutrisno',
                'email' => 'staff@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone_number' => '+6281234567901',
                'birthdate' => '1989-10-05',
                'salary' => 6800000.00,
                'address' => 'Jl. Detax Road No. 301, Jakarta',
                'department_id' => $departments->where('name', 'Detax')->first()->id,
            ],
            [
                'name' => 'Lisa Permata',
                'email' => 'lisa.dtx@attendance.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone_number' => '+6281234567902',
                'birthdate' => '1992-01-28',
                'salary' => 6600000.00,
                'address' => 'Jl. Detax Center No. 302, Jakarta',
                'department_id' => $departments->where('name', 'Detax')->first()->id,
            ],
            // HR Staff
            [
                'name' => 'Indra Gunawan',
                'email' => 'indra.hr@attendance.com',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone_number' => '+6281234567903',
                'birthdate' => '1993-03-17',
                'salary' => 6500000.00,
                'address' => 'Jl. HR Building No. 401, Jakarta',
                'department_id' => $departments->where('name', 'HR')->first()->id,
            ],
        ];

        foreach ($staffMembers as $staff) {
            User::create($staff);
        }
    }
}
