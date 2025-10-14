<?php

namespace App\Livewire\Attendance;

use App\Livewire\Traits\Alert;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\Schedule;
use App\Models\ScheduleException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CheckIn extends Component
{
    use Alert;

    public ?float $latitude = null;
    public ?float $longitude = null;
    public bool $locationLoading = true;
    public ?string $locationError = null;
    public bool $modal = false;
    public ?string $notes = null;
    public ?string $early_leave_reason = null;

    public function render(): View
    {
        return view('livewire.attendance.check-in');
    }

    #[Computed]
    public function todayAttendance(): ?Attendance
    {
        return Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();
    }

    #[Computed]
    public function officeLocations(): \Illuminate\Database\Eloquent\Collection
    {
        return OfficeLocation::all();
    }

    #[Computed]
    public function todaySchedule(): ?array
    {
        $user = Auth::user();
        $today = today();
        $dayOfWeek = strtolower($today->format('l'));

        // Check for schedule exceptions first
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
                'title' => $exception->title ?? ucfirst($exception->status)
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
            'title' => 'Work Day'
        ];
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

        // Check time window
        $now = now();
        $workStart = today()->setTimeFromTimeString($schedule['start_time']);
        $workEnd = today()->setTimeFromTimeString($schedule['end_time']);

        if (!$now->between($workStart, $workEnd)) {
            return false;
        }

        // Check location
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

    public function updateLocation(float $lat, float $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->locationLoading = false;
        $this->locationError = null;
    }

    public function setLocationError(string $error): void
    {
        $this->locationError = $error;
        $this->locationLoading = false;
    }

    public function openNotesModal(): void
    {
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

    public function checkIn(): void
    {
        if (!$this->canCheckIn) {
            $this->error('Cannot check in at this time');
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

        // Reset computed properties untuk refresh data
        unset($this->todayAttendance);
        unset($this->canCheckIn);
        unset($this->canCheckOut);

        $message = $status === 'late'
            ? "Check-in successful, but you are late"
            : 'Check-in successful!';

        $this->success($message);
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

        // Reset computed properties untuk refresh data
        unset($this->todayAttendance);
        unset($this->canCheckIn);
        unset($this->canCheckOut);

        $this->closeNotesModal();
        $this->success("Check-out successful! Working hours: {$workingHours}h");
    }

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
        $earthRadius = 6371000;
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
        if (!$schedule)
            return 'present';

        $workStart = today()->setTimeFromTimeString($schedule['start_time']);
        $lateThreshold = $workStart->copy()->addMinutes($schedule['late_tolerance']);

        return now()->isAfter($lateThreshold) ? 'late' : 'present';
    }

    private function calculateLateHours(): ?float
    {
        $schedule = $this->todaySchedule;
        if (!$schedule)
            return null;

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
}