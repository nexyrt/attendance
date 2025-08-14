<?php

namespace App\Livewire\Staff\Attendance;

use App\Livewire\Traits\Alert;
use App\Models\Attendance;
use App\Models\OfficeLocation;
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

    public function render(): View
    {
        return view('livewire.staff.attendance.check-in');
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

    public function updateLocation(float $lat, float $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->locationLoading = false;
        $this->locationError = null;
    }

    public function locationError(string $error): void
    {
        $this->locationError = $error;
        $this->locationLoading = false;
    }

    public function checkIn(): void
    {
        if (!$this->latitude || !$this->longitude) {
            $this->error('Location is required for check-in');
            return;
        }

        $validOffice = $this->findValidOffice();
        
        if (!$validOffice) {
            $this->error('You are not within any office location radius');
            return;
        }

        $attendance = $this->todayAttendance;
        
        if ($attendance?->check_in) {
            $this->error('You have already checked in today');
            return;
        }

        if (!$attendance) {
            $attendance = new Attendance([
                'user_id' => Auth::id(),
                'date' => today(),
            ]);
        }

        $attendance->fill([
            'check_in' => now(),
            'check_in_latitude' => $this->latitude,
            'check_in_longitude' => $this->longitude,
            'check_in_office_id' => $validOffice->id,
            'device_type' => 'web',
            'status' => $this->calculateStatus(),
        ]);

        $attendance->save();

        $this->success('Check-in successful!');
        $this->dispatch('attendance-updated');
    }

    public function checkOut(): void
    {
        if (!$this->latitude || !$this->longitude) {
            $this->error('Location is required for check-out');
            return;
        }

        $attendance = $this->todayAttendance;

        if (!$attendance?->check_in) {
            $this->error('You must check in first');
            return;
        }

        if ($attendance->check_out) {
            $this->error('You have already checked out today');
            return;
        }

        $validOffice = $this->findValidOffice();

        $attendance->fill([
            'check_out' => now(),
            'check_out_latitude' => $this->latitude,
            'check_out_longitude' => $this->longitude,
            'check_out_office_id' => $validOffice?->id,
            'working_hours' => $this->calculateWorkingHours($attendance),
        ]);

        $attendance->save();

        $this->success('Check-out successful!');
        $this->dispatch('attendance-updated');
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

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    private function calculateStatus(): string
    {
        // Simplified: check if late based on 9:00 AM
        $scheduledTime = today()->setTime(9, 0);
        return now()->isAfter($scheduledTime) ? 'late' : 'present';
    }

    private function calculateWorkingHours(Attendance $attendance): float
    {
        $checkIn = $attendance->check_in;
        $checkOut = now();

        return $checkOut->diffInMinutes($checkIn) / 60;
    }
}