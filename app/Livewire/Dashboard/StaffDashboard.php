<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Traits\Alert;
use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\OfficeLocation;
use App\Models\Schedule;
use App\Models\ScheduleException;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class StaffDashboard extends Component
{
    use Alert;

    // Real-time properties
    public string $currentTime = '';
    public string $status = 'not_started';
    public ?string $checkInTime = null;
    public ?string $checkOutTime = null;
    public ?string $selectedDate = null;

    // Location properties
    public ?float $latitude = null;
    public ?float $longitude = null;
    public bool $locationLoading = true;
    public ?string $locationError = null;

    // Notes modal properties
    public bool $modal = false;
    public ?string $notes = null;
    public ?string $early_leave_reason = null;

    public function mount(): void
    {
        $this->currentTime = now()->format('H:i:s');
        $this->selectedDate = today()->format('Y-m-d');
        $this->updateCheckInStatus();
    }

    public function render(): View
    {
        return view('livewire.dashboard.staff-dashboard');
    }

    // ============================================================
    // COMPUTED PROPERTIES - ATTENDANCE
    // ============================================================

    #[Computed]
    public function todayAttendance(): ?Attendance
    {
        return Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();
    }

    #[Computed]
    public function todaySchedule(): ?array
    {
        $user = Auth::user();
        $today = today();
        $dayOfWeek = strtolower($today->format('l'));

        // Check for schedule exceptions first (holiday/event)
        $exception = ScheduleException::where('date', $today)
            ->whereHas('departments', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->first();

        if ($exception) {
            return [
                'status' => $exception->status,
                'start_time' => $exception->start_time?->format('H:i'),
                'end_time' => $exception->end_time?->format('H:i'),
                'late_tolerance' => $exception->late_tolerance ?? 30,
                'title' => $exception->title ?? ucfirst($exception->status),
                'note' => $exception->note
            ];
        }

        // Check regular schedule
        $schedule = Schedule::where('day_of_week', $dayOfWeek)->first();

        if (!$schedule) {
            return null;
        }

        return [
            'status' => 'regular',
            'start_time' => $schedule->start_time->format('H:i'),
            'end_time' => $schedule->end_time->format('H:i'),
            'late_tolerance' => $schedule->late_tolerance,
            'title' => 'Work Day',
            'note' => null
        ];
    }

    #[Computed]
    public function officeLocations()
    {
        return OfficeLocation::all();
    }

    #[Computed]
    public function canCheckIn(): bool
    {
        $schedule = $this->todaySchedule;

        // No schedule or holiday
        if (!$schedule || $schedule['status'] === 'holiday') {
            return false;
        }

        // Already checked in
        if ($this->todayAttendance?->check_in) {
            return false;
        }

        // Location not available
        if (!$this->latitude || !$this->longitude) {
            return false;
        }

        // Check if in office radius
        return $this->findValidOffice() !== null;
    }

    #[Computed]
    public function canCheckOut(): bool
    {
        $attendance = $this->todayAttendance;

        return $attendance?->check_in &&
            !$attendance->check_out &&
            $this->latitude &&
            $this->longitude;
    }

    #[Computed]
    public function isEarlyLeave(): bool
    {
        $schedule = $this->todaySchedule;
        if (!$schedule || $schedule['status'] === 'holiday') {
            return false;
        }

        $workEnd = today()->setTimeFromTimeString($schedule['end_time']);
        return now()->isBefore($workEnd);
    }

    // ============================================================
    // COMPUTED PROPERTIES - STATS & DATA
    // ============================================================

    #[Computed]
    public function quickStats(): array
    {
        $userId = Auth::id();
        $thirtyDaysAgo = now()->subDays(30);

        $attendances = Attendance::where('user_id', $userId)
            ->where('date', '>=', $thirtyDaysAgo)
            ->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $totalHours = $attendances->sum('working_hours');
        $avgHours = $totalDays > 0 ? round($totalHours / $totalDays, 1) : 0;

        return [
            'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
            'late_count' => $lateDays,
            'avg_hours' => $avgHours,
            'days_worked' => $presentDays
        ];
    }

    #[Computed]
    public function leaveBalances(): array
    {
        // Get current year leave balance using relationship
        $balance = Auth::user()
            ->leaveBalances()
            ->where('year', now()->year)
            ->first();

        if (!$balance) {
            return [
                ['type' => 'Annual Leave', 'icon' => 'sun', 'total' => 12, 'used' => 0, 'color' => 'text-blue-600'],
                ['type' => 'Sick Leave', 'icon' => 'heart', 'total' => 12, 'used' => 0, 'color' => 'text-red-600'],
                ['type' => 'Important Leave', 'icon' => 'shield-exclamation', 'total' => 12, 'used' => 0, 'color' => 'text-yellow-600'],
                ['type' => 'Other', 'icon' => 'ellipsis-horizontal', 'total' => 12, 'used' => 0, 'color' => 'text-gray-600'],
            ];
        }

        return [
            ['type' => 'Annual Leave', 'icon' => 'sun', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-blue-600'],
            ['type' => 'Sick Leave', 'icon' => 'heart', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-red-600'],
            ['type' => 'Important Leave', 'icon' => 'shield-exclamation', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-yellow-600'],
            ['type' => 'Other', 'icon' => 'ellipsis-horizontal', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-gray-600'],
        ];
    }

    #[Computed]
    public function scheduleExceptions(): array
    {
        $user = Auth::user();
        
        return ScheduleException::whereYear('date', now()->year)
            ->whereHas('departments', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->orderBy('date')
            ->get()
            ->map(fn($exc) => [
                'id' => $exc->id,
                'title' => $exc->title,
                'date' => $exc->date->format('Y-m-d'),
                'status' => $exc->status,
                'note' => $exc->note
            ])
            ->toArray();
    }

    #[Computed]
    public function weekData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $weekData = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            
            $attendance = Attendance::where('user_id', Auth::id())
                ->where('date', $date)
                ->first();

            $weekData[] = [
                'day' => $date->format('D'),
                'date' => $date->format('d'),
                'full_date' => $date->format('Y-m-d'),
                'status' => $attendance ? $attendance->status : null,
                'hours' => $attendance ? $attendance->working_hours : null
            ];
        }

        return $weekData;
    }

    #[Computed]
    public function activities(): array
    {
        return Attendance::where('user_id', Auth::id())
            ->whereNotNull('check_in')
            ->latest('date')
            ->take(5)
            ->get()
            ->flatMap(function ($att) {
                $activities = [];
                
                if ($att->check_in) {
                    $activities[] = [
                        'id' => 'in-' . $att->id,
                        'type' => 'check_in',
                        'desc' => 'Checked in' . ($att->status === 'late' ? ' (Late)' : ''),
                        'time' => $att->check_in->format('Y-m-d H:i:s'),
                        'date' => $att->date->format('Y-m-d')
                    ];
                }
                
                if ($att->check_out) {
                    $activities[] = [
                        'id' => 'out-' . $att->id,
                        'type' => 'check_out',
                        'desc' => 'Checked out' . ($att->status === 'early_leave' ? ' (Early)' : ''),
                        'time' => $att->check_out->format('Y-m-d H:i:s'),
                        'date' => $att->date->format('Y-m-d')
                    ];
                }
                
                return $activities;
            })
            ->sortByDesc('time')
            ->take(5)
            ->values()
            ->toArray();
    }

    // ============================================================
    // CHECK-IN/OUT ACTIONS
    // ============================================================

    public function updateLocation(float $lat, float $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->locationLoading = false;
        $this->locationError = null;
        
        // Refresh computed properties
        unset($this->canCheckIn);
        unset($this->canCheckOut);
    }

    public function setLocationError(string $error): void
    {
        $this->locationError = $error;
        $this->locationLoading = false;
    }

    public function checkIn(): void
    {
        if (!$this->canCheckIn) {
            $this->error('Cannot check in at this time. Please check location and schedule.');
            return;
        }

        $validOffice = $this->findValidOffice();

        $attendance = $this->todayAttendance ?: new Attendance([
            'user_id' => Auth::id(),
            'date' => today(),
        ]);

        $status = $this->calculateStatus();
        $lateHours = $this->calculateLateHours();

        $attendance->fill([
            'check_in' => now(),
            'check_in_latitude' => $this->latitude,
            'check_in_longitude' => $this->longitude,
            'check_in_office_id' => $validOffice->id,
            'device_type' => 'web',
            'status' => $status,
            'late_hours' => $lateHours,
        ]);

        $attendance->save();

        // Update status
        $this->updateCheckInStatus();

        // Reset computed properties
        unset($this->todayAttendance);
        unset($this->canCheckIn);
        unset($this->canCheckOut);
        unset($this->quickStats);
        unset($this->weekData);
        unset($this->activities);

        $message = $status === 'late'
            ? "Checked in successfully, but you are late by {$lateHours}h"
            : 'Checked in successfully!';

        $this->success($message);
    }

    public function openNotesModal(): void
    {
        if (!$this->canCheckOut) {
            $this->error('Cannot check out at this time.');
            return;
        }

        $this->modal = true;
    }

    public function closeNotesModal(): void
    {
        $this->modal = false;
        $this->notes = null;
        $this->early_leave_reason = null;
        $this->resetErrorBag();
    }

    public function rules(): array
    {
        $rules = ['notes' => ['required', 'string', 'min:10', 'max:1000']];

        if ($this->isEarlyLeave) {
            $rules['early_leave_reason'] = ['required', 'string', 'min:10', 'max:500'];
        }

        return $rules;
    }

    public function checkOut(): void
    {
        if (!$this->canCheckOut) {
            $this->error('Cannot check out at this time');
            return;
        }

        $this->validate();

        $attendance = $this->todayAttendance;
        $validOffice = $this->findValidOffice();
        $workingHours = $this->calculateWorkingHours($attendance);

        $status = $attendance->status;
        if ($this->isEarlyLeave) {
            $status = 'early_leave';
        }

        $attendance->fill([
            'check_out' => now(),
            'check_out_latitude' => $this->latitude,
            'check_out_longitude' => $this->longitude,
            'check_out_office_id' => $validOffice?->id,
            'working_hours' => $workingHours,
            'notes' => $this->notes,
            'early_leave_reason' => $this->early_leave_reason,
            'status' => $status,
        ]);

        $attendance->save();

        // Update status
        $this->updateCheckInStatus();

        // Reset computed properties
        unset($this->todayAttendance);
        unset($this->canCheckIn);
        unset($this->canCheckOut);
        unset($this->quickStats);
        unset($this->weekData);
        unset($this->activities);

        $this->closeNotesModal();
        $this->success("Checked out successfully! Working hours: {$workingHours}h");
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    private function findValidOffice(): ?OfficeLocation
    {
        foreach ($this->officeLocations as $office) {
            $distance = $this->calculateDistance(
                $this->latitude,
                $this->longitude,
                $office->latitude,
                $office->longitude
            );

            if ($distance <= $office->radius) {
                return $office;
            }
        }

        return null;
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function calculateStatus(): string
    {
        $schedule = $this->todaySchedule;
        if (!$schedule) {
            return 'present';
        }

        $workStart = today()->setTimeFromTimeString($schedule['start_time']);
        $lateThreshold = $workStart->copy()->addMinutes($schedule['late_tolerance']);

        return now()->isAfter($lateThreshold) ? 'late' : 'present';
    }

    private function calculateLateHours(): ?float
    {
        $schedule = $this->todaySchedule;
        if (!$schedule) {
            return null;
        }

        $workStart = today()->setTimeFromTimeString($schedule['start_time']);
        $checkInTime = now();

        if ($checkInTime->isAfter($workStart)) {
            return round($checkInTime->diffInMinutes($workStart) / 60, 2);
        }

        return 0;
    }

    private function calculateWorkingHours(Attendance $attendance): float
    {
        return round($attendance->check_in->diffInMinutes(now()) / 60, 2);
    }

    public function updateCheckInStatus(): void
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();

        if (!$attendance) {
            $this->status = 'not_started';
            $this->checkInTime = null;
            $this->checkOutTime = null;
        } elseif ($attendance->check_in && !$attendance->check_out) {
            $this->status = 'checked_in';
            $this->checkInTime = $attendance->check_in->format('Y-m-d H:i:s');
            $this->checkOutTime = null;
        } elseif ($attendance->check_in && $attendance->check_out) {
            $this->status = 'completed';
            $this->checkInTime = $attendance->check_in->format('Y-m-d H:i:s');
            $this->checkOutTime = $attendance->check_out->format('Y-m-d H:i:s');
        }
    }

    #[On('refresh-dashboard')]
    public function refreshDashboard(): void
    {
        $this->updateCheckInStatus();
        
        // Clear all computed caches
        unset($this->todayAttendance);
        unset($this->quickStats);
        unset($this->leaveBalances);
        unset($this->weekData);
        unset($this->activities);
    }
}