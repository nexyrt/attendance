<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'staff')->get();
        $managers = User::where('role', 'manager')->get();
        $hrUsers = User::whereIn('role', ['admin', 'manager'])
                      ->whereHas('department', function($query) {
                          $query->where('name', 'HR');
                      })->get();
        $director = User::where('role', 'director')->first();

        foreach ($users as $user) {
            // Generate 2-5 leave requests per user
            $numberOfRequests = rand(2, 5);
            
            for ($i = 0; $i < $numberOfRequests; $i++) {
                $this->createLeaveRequest($user, $managers, $hrUsers, $director);
            }
        }

        // Also generate some requests for managers
        foreach ($managers->take(3) as $manager) {
            $numberOfRequests = rand(1, 3);
            
            for ($i = 0; $i < $numberOfRequests; $i++) {
                $this->createLeaveRequest($manager, $managers, $hrUsers, $director);
            }
        }
    }

    private function createLeaveRequest(User $user, $managers, $hrUsers, $director): void
    {
        // Random leave type
        $leaveTypes = ['sick', 'annual', 'important', 'other'];
        $leaveType = $leaveTypes[array_rand($leaveTypes)];

        // Generate random dates (some past, some future)
        $startDate = Carbon::now()->addDays(rand(-60, 60));
        
        // Ensure it's a weekday
        while ($startDate->isWeekend()) {
            $startDate->addDay();
        }

        // Duration of leave (1-5 days)
        $duration = rand(1, 5);
        $endDate = $startDate->copy();
        
        // Add working days only
        for ($i = 1; $i < $duration; $i++) {
            $endDate->addDay();
            while ($endDate->isWeekend()) {
                $endDate->addDay();
            }
        }

        // Get appropriate manager (from same department or random if not available)
        $manager = $managers->where('department_id', $user->department_id)->first() 
                  ?? $managers->random();
        
        $hrUser = $hrUsers->random();

        // Determine status based on date
        $status = $this->determineStatus($startDate);
        
        $leaveRequest = [
            'user_id' => $user->id,
            'type' => $leaveType,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => $this->getReasonByType($leaveType),
            'status' => $status,
            'manager_id' => $manager->id,
            'hr_id' => $hrUser->id,
            'director_id' => $director->id,
            'created_at' => $startDate->copy()->subDays(rand(1, 14)),
        ];

        // Add approval timestamps and signatures based on status
        $this->addApprovalData($leaveRequest, $startDate, $status);

        LeaveRequest::create($leaveRequest);
    }

    private function determineStatus(Carbon $startDate): string
    {
        $now = Carbon::now();
        
        if ($startDate->isPast()) {
            // Past requests - most should be approved
            $statuses = [
                'approved' => 70,
                'rejected_manager' => 10,
                'rejected_hr' => 10,
                'rejected_director' => 5,
                'cancel' => 5,
            ];
        } else {
            // Future requests - mix of pending and approved
            $statuses = [
                'pending_manager' => 30,
                'pending_hr' => 20,
                'pending_director' => 15,
                'approved' => 25,
                'rejected_manager' => 5,
                'rejected_hr' => 3,
                'rejected_director' => 2,
            ];
        }

        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach ($statuses as $status => $probability) {
            $cumulative += $probability;
            if ($random <= $cumulative) {
                return $status;
            }
        }
        
        return 'pending_manager';
    }

    private function addApprovalData(array &$leaveRequest, Carbon $startDate, string $status): void
    {
        $createdAt = Carbon::parse($leaveRequest['created_at']);
        
        switch ($status) {
            case 'approved':
                $leaveRequest['manager_approved_at'] = $createdAt->copy()->addHours(rand(2, 24));
                $leaveRequest['manager_signature'] = 'manager_signature_' . uniqid();
                $leaveRequest['hr_approved_at'] = $createdAt->copy()->addHours(rand(26, 48));
                $leaveRequest['hr_signature'] = 'hr_signature_' . uniqid();
                $leaveRequest['director_approved_at'] = $createdAt->copy()->addHours(rand(50, 72));
                $leaveRequest['director_signature'] = 'director_signature_' . uniqid();
                break;
                
            case 'rejected_manager':
                $leaveRequest['rejection_reason'] = 'Tidak dapat ditinggalkan pada periode tersebut';
                break;
                
            case 'rejected_hr':
                $leaveRequest['manager_approved_at'] = $createdAt->copy()->addHours(rand(2, 24));
                $leaveRequest['manager_signature'] = 'manager_signature_' . uniqid();
                $leaveRequest['rejection_reason'] = 'Kuota cuti sudah habis';
                break;
                
            case 'rejected_director':
                $leaveRequest['manager_approved_at'] = $createdAt->copy()->addHours(rand(2, 24));
                $leaveRequest['manager_signature'] = 'manager_signature_' . uniqid();
                $leaveRequest['hr_approved_at'] = $createdAt->copy()->addHours(rand(26, 48));
                $leaveRequest['hr_signature'] = 'hr_signature_' . uniqid();
                $leaveRequest['rejection_reason'] = 'Periode kritis untuk perusahaan';
                break;
                
            case 'pending_hr':
                $leaveRequest['manager_approved_at'] = $createdAt->copy()->addHours(rand(2, 24));
                $leaveRequest['manager_signature'] = 'manager_signature_' . uniqid();
                break;
                
            case 'pending_director':
                $leaveRequest['manager_approved_at'] = $createdAt->copy()->addHours(rand(2, 24));
                $leaveRequest['manager_signature'] = 'manager_signature_' . uniqid();
                $leaveRequest['hr_approved_at'] = $createdAt->copy()->addHours(rand(26, 48));
                $leaveRequest['hr_signature'] = 'hr_signature_' . uniqid();
                break;
                
            case 'cancel':
                $leaveRequest['rejection_reason'] = 'Dibatalkan oleh pemohon';
                break;
        }
    }

    private function getReasonByType(string $type): string
    {
        $reasons = [
            'sick' => [
                'Demam dan flu',
                'Sakit kepala berkepanjangan',
                'Gangguan pencernaan',
                'Checkup kesehatan rutin',
                'Pemulihan setelah operasi kecil',
            ],
            'annual' => [
                'Liburan keluarga',
                'Acara pernikahan kerabat',
                'Refreshing dan istirahat',
                'Traveling ke luar kota',
                'Quality time dengan keluarga',
            ],
            'important' => [
                'Mengurus dokumen penting',
                'Acara keluarga yang mendesak',
                'Pernikahan saudara kandung',
                'Pemakaman keluarga',
                'Wisuda anak',
            ],
            'other' => [
                'Keperluan pribadi mendesak',
                'Pindah rumah',
                'Mengurus administrasi bank',
                'Konsultasi dengan dokter spesialis',
                'Mengantar orang tua ke rumah sakit',
            ],
        ];

        $typeReasons = $reasons[$type] ?? $reasons['other'];
        return $typeReasons[array_rand($typeReasons)];
    }
}
