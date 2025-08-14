<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $currentYear = 2025;

        foreach ($users as $user) {
            // Berdasarkan role, berikan jumlah cuti yang berbeda
            $totalBalance = match($user->role) {
                'staff' => 12, // 12 hari cuti per tahun
                'manager' => 15, // 15 hari cuti per tahun
                'director' => 18, // 18 hari cuti per tahun
                'admin' => 12, // 12 hari cuti per tahun
                default => 12,
            };

            // Simulasi beberapa sudah menggunakan cuti
            $usedBalance = match($user->role) {
                'staff' => rand(0, 5), // Staff sudah pakai 0-5 hari
                'manager' => rand(0, 7), // Manager sudah pakai 0-7 hari
                'director' => rand(0, 3), // Director sudah pakai 0-3 hari
                'admin' => rand(0, 4), // Admin sudah pakai 0-4 hari
                default => 0,
            };

            $remainingBalance = $totalBalance - $usedBalance;

            LeaveBalance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'year' => $currentYear,
                ],
                [
                    'total_balance' => $totalBalance,
                    'used_balance' => $usedBalance,
                    'remaining_balance' => $remainingBalance,
                ]
            );

            // Juga buat untuk tahun sebelumnya (2024)
            $previousYearUsed = match($user->role) {
                'staff' => rand(8, 12), // Staff tahun lalu pakai 8-12 hari
                'manager' => rand(10, 15), // Manager tahun lalu pakai 10-15 hari
                'director' => rand(12, 18), // Director tahun lalu pakai 12-18 hari
                'admin' => rand(8, 12), // Admin tahun lalu pakai 8-12 hari
                default => 12,
            };

            $previousYearRemaining = $totalBalance - $previousYearUsed;

            LeaveBalance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'year' => 2024,
                ],
                [
                    'total_balance' => $totalBalance,
                    'used_balance' => $previousYearUsed,
                    'remaining_balance' => max(0, $previousYearRemaining),
                ]
            );
        }
    }
}
