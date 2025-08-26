<?php

namespace App\Livewire\Schedule;

use App\Livewire\Traits\Alert;
use App\Models\Schedule as ScheduleModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Carbon\Carbon;

class Edit extends Component
{
    use Alert;

    public bool $modal = false;
    public array $schedules = [];
    public array $schedulesToDelete = [];

    public function render(): View
    {
        return view('livewire.schedule.edit');
    }

    #[On('load::edit-schedule')]
    public function load(): void
    {
        $this->reset(['schedules', 'schedulesToDelete']);
        $this->loadExistingSchedules();
        $this->modal = true;
    }

    public function rules(): array
    {
        $rules = [];
        foreach ($this->schedules as $index => $schedule) {
            $rules["schedules.{$index}.start_time"] = ['required', 'date_format:H:i'];
            $rules["schedules.{$index}.end_time"] = ['required', 'date_format:H:i', 'after:schedules.' . $index . '.start_time'];
            $rules["schedules.{$index}.late_tolerance"] = ['required', 'integer', 'min:0', 'max:120'];
        }
        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        $deletedCount = 0;
        $updatedCount = 0;

        // Delete marked schedules first
        foreach ($this->schedulesToDelete as $dayOfWeek) {
            $deleted = ScheduleModel::where('day_of_week', $dayOfWeek)->delete();
            if ($deleted) {
                $deletedCount++;
            }
        }

        // Update remaining schedules
        foreach ($this->schedules as $schedule) {
            $updated = ScheduleModel::where('day_of_week', $schedule['day_of_week'])
                ->update([
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'late_tolerance' => $schedule['late_tolerance']
                ]);
            if ($updated) {
                $updatedCount++;
            }
        }

        // Build success message
        $message = [];
        if ($updatedCount > 0) {
            $message[] = "{$updatedCount} jadwal diperbarui";
        }
        if ($deletedCount > 0) {
            $message[] = "{$deletedCount} jadwal dihapus";
        }

        $finalMessage = implode(' dan ', $message);

        $this->dispatch('updated');
        $this->modal = false;
        $this->success($finalMessage ?: 'Tidak ada perubahan');
    }

    public function markForDeletion(string $dayOfWeek): void
    {
        // Add to deletion list
        if (!in_array($dayOfWeek, $this->schedulesToDelete)) {
            $this->schedulesToDelete[] = $dayOfWeek;
        }
        
        // Remove from schedules array
        $this->schedules = array_values(
            array_filter($this->schedules, fn($schedule) => $schedule['day_of_week'] !== $dayOfWeek)
        );
    }

    public function calculateWorkingHours(string $startTime, string $endTime): string
    {
        try {
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            
            // Handle overnight shifts
            if ($end->lessThan($start)) {
                $end->addDay();
            }
            
            $diffInMinutes = $start->diffInMinutes($end);
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;
            
            if ($minutes > 0) {
                $decimalMinutes = round(($minutes / 60) * 100) / 100;
                return number_format($hours + $decimalMinutes, 1);
            }
            
            return (string) $hours;
        } catch (\Exception $e) {
            return '0';
        }
    }

    private function loadExistingSchedules(): void
    {
        $existingSchedules = ScheduleModel::orderByRaw("
            FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
        ")->get();

        if ($existingSchedules->isEmpty()) {
            $this->schedules = [];
            return;
        }

        $this->schedules = $existingSchedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time ? $schedule->start_time->format('H:i') : '08:00',
                'end_time' => $schedule->end_time ? $schedule->end_time->format('H:i') : '17:00',
                'late_tolerance' => $schedule->late_tolerance ?? 30
            ];
        })->toArray();
    }

    private function getDayName(string $dayOfWeek): string
    {
        return match($dayOfWeek) {
            'monday' => 'Senin',
            'tuesday' => 'Selasa', 
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
            default => ucfirst($dayOfWeek)
        };
    }

    #[Computed]
    public function workingHoursSummary(): array
    {
        $summary = [];
        foreach ($this->schedules as $schedule) {
            $summary[$schedule['day_of_week']] = $this->calculateWorkingHours(
                $schedule['start_time'], 
                $schedule['end_time']
            );
        }
        return $summary;
    }

    #[Computed]
    public function hasSchedules(): bool
    {
        return count($this->schedules) > 0;
    }

    #[Computed]
    public function totalWorkingDays(): int
    {
        return count($this->schedules);
    }
}