<?php

namespace Database\Seeders;

use App\Models\SalaryHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SalaryHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create salary history for each user
            $this->createSalaryHistoryForUser($user);
        }
    }

    private function createSalaryHistoryForUser(User $user): void
    {
        $currentSalary = $user->salary;
        
        if (!$currentSalary) {
            return; // Skip if no salary defined
        }

        // Create initial salary record (when user joined)
        $joinDate = $user->created_at ?? Carbon::now()->subYears(rand(1, 5));
        
        // Calculate initial salary (70-90% of current salary)
        $initialSalary = $currentSalary * (rand(70, 90) / 100);
        
        SalaryHistory::create([
            'user_id' => $user->id,
            'amount' => $initialSalary,
            'effective_date' => $joinDate->format('Y-m-d'),
            'notes' => 'Gaji awal bergabung dengan perusahaan',
            'created_at' => $joinDate,
            'updated_at' => $joinDate,
        ]);

        // Create 1-3 salary adjustments over time
        $numberOfAdjustments = rand(1, 3);
        $lastSalary = $initialSalary;
        $lastDate = $joinDate->copy();

        for ($i = 0; $i < $numberOfAdjustments; $i++) {
            // Add 6-18 months between adjustments
            $adjustmentDate = $lastDate->copy()->addMonths(rand(6, 18));
            
            // Don't create future salary adjustments
            if ($adjustmentDate->greaterThan(Carbon::now())) {
                break;
            }

            // Calculate salary increase (5-25%)
            $increasePercent = rand(5, 25);
            $newSalary = $lastSalary * (1 + $increasePercent / 100);
            
            // Ensure we don't exceed current salary too much
            if ($newSalary > $currentSalary) {
                $newSalary = $currentSalary;
            }

            $adjustmentReasons = [
                'Kenaikan gaji tahunan',
                'Promosi jabatan',
                'Penyesuaian berdasarkan kinerja',
                'Kenaikan berdasarkan evaluasi',
                'Penyesuaian standar industri',
                'Bonus kinerja yang dikonversi ke gaji pokok',
                'Kenaikan berdasarkan masa kerja',
            ];

            SalaryHistory::create([
                'user_id' => $user->id,
                'amount' => $newSalary,
                'effective_date' => $adjustmentDate->format('Y-m-d'),
                'notes' => $adjustmentReasons[array_rand($adjustmentReasons)] . ' (+' . $increasePercent . '%)',
                'created_at' => $adjustmentDate,
                'updated_at' => $adjustmentDate,
            ]);

            $lastSalary = $newSalary;
            $lastDate = $adjustmentDate;
        }

        // If there's still a gap to current salary, create final adjustment
        if (abs($lastSalary - $currentSalary) > 100000) { // If difference > 100k
            $finalDate = Carbon::now()->subMonths(rand(1, 6));
            
            SalaryHistory::create([
                'user_id' => $user->id,
                'amount' => $currentSalary,
                'effective_date' => $finalDate->format('Y-m-d'),
                'notes' => 'Penyesuaian gaji terkini',
                'created_at' => $finalDate,
                'updated_at' => $finalDate,
            ]);
        }
    }
}
