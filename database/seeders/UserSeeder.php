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
        $this->command->info('👥 Creating Users...');

        $departments = Department::all();

        // 1. Administrator (HR Department)
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'phone_number' => '+6281234567890',
            'birthdate' => '1985-01-15',
            'salary' => 15000000.00,
            'address' => 'Jl. Admin Street No. 1, Jakarta',
            'department_id' => $departments->where('name', 'HR')->first()->id,
        ]);
        $this->command->line('  ✓ Created: Administrator (admin@gmail.com)');

        // 2. Director (No Department)
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'direktur@gmail.com',
            'password' => Hash::make('password'),
            'phone_number' => '+6281234567891',
            'birthdate' => '1975-03-20',
            'salary' => 25000000.00,
            'address' => 'Jl. Director Avenue No. 5, Jakarta',
            'department_id' => null,
        ]);
        $this->command->line('  ✓ Created: Budi Santoso (direktur@gmail.com)');

        // 3. Manager (Digital Marketing Department)
        User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
            'phone_number' => '+6281234567893',
            'birthdate' => '1987-05-15',
            'salary' => 10000000.00,
            'address' => 'Jl. Marketing Street No. 8, Jakarta',
            'department_id' => $departments->where('name', 'Digital Marketing')->first()->id,
        ]);
        $this->command->line('  ✓ Created: Ahmad Fauzi (manager@gmail.com)');

        // 4. Staff 1 (Digital Marketing Department)
        User::create([
            'name' => 'Dewi Lestari',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('password'),
            'phone_number' => '+6281234567896',
            'birthdate' => '1992-04-18',
            'salary' => 6500000.00,
            'address' => 'Jl. Staff Residence No. 101, Jakarta',
            'department_id' => $departments->where('name', 'Digital Marketing')->first()->id,
        ]);
        $this->command->line('  ✓ Created: Dewi Lestari (staff@gmail.com)');

        // 5. Staff 2 (Sistem Digital Department)
        User::create([
            'name' => 'Arif Hidayat',
            'email' => 'arif@gmail.com',
            'password' => Hash::make('password'),
            'phone_number' => '+6281234567899',
            'birthdate' => '1991-08-30',
            'salary' => 7000000.00,
            'address' => 'Jl. Sistem Digital Street No. 201, Jakarta',
            'department_id' => $departments->where('name', 'Sistem Digital')->first()->id,
        ]);
        $this->command->line('  ✓ Created: Arif Hidayat (arif@gmail.com)');

        $this->command->newLine();
        $this->command->info('✅ Created 5 users successfully!');
        $this->command->line('   Roles will be assigned by RolePermissionSeeder');
    }
}