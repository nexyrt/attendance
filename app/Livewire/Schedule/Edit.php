<?php

namespace App\Livewire\Schedule;

use App\Livewire\Traits\Alert;
use App\Models\Schedule as ScheduleModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Carbon\Carbon;

class Edit extends Component
{
    use Alert;

    public bool $modal = false;
    public array $schedules = [];

    public function render(): View
    {
        return view('livewire.schedule.edit');
    }

    #[On('load::edit-schedule')]
    public function load(): void
    {
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

        foreach ($this->schedules as $schedule) {
            ScheduleModel::where('day_of_week', $schedule['day_of_week'])
                ->update([
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'late_tolerance' => $schedule['late_tolerance']
                ]);
        }

        $this->dispatch('updated');
        $this->modal = false;
        $this->success('Jadwal kerja berhasil diperbarui');
    }

    #[Renderless]
    public function confirmDelete(string $dayOfWeek): void
    {
        $dayName = $this->getDayName($dayOfWeek);
        
        $this->question("Hapus jadwal {$dayName}?", "Jadwal kerja untuk hari {$dayName} akan dihapus permanen.")
            ->confirm(method: 'deleteSchedule', params: $dayOfWeek)
            ->cancel()
            ->send();
    }

    public function deleteSchedule(string $dayOfWeek): void
    {
        $schedule = ScheduleModel::where('day_of_week', $dayOfWeek)->first();
        
        if (!$schedule) {
            $this->error('Jadwal tidak ditemukan');
            return;
        }

        $schedule->delete();
        $this->loadExistingSchedules();
        $this->success('Jadwal ' . $this->getDayName($dayOfWeek) . ' berhasil dihapus');
    }

    public function calculateWorkingHours(string $startTime, string $endTime): string
    {
        try {
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            
            if ($end->lessThan($start)) {
                $end->addDay();
            }
            
            $diffInMinutes = $start->diffInMinutes($end);
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;
            
            if ($minutes > 0) {
                return $hours . '.' . str_pad(round($minutes * 100 / 60), 2, '0', STR_PAD_LEFT);
            }
            
            return (string) $hours;
        } catch (\Exception $e) {
            return '0';
        }
    }

    private function loadExistingSchedules(): void
    {
        $existing = ScheduleModel::orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
                           ->get()
                           ->keyBy('day_of_week');

        $this->schedules = [];
        foreach ($existing as $schedule) {
            $this->schedules[] = [
                'id' => $schedule->id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time ? $schedule->start_time->format('H:i') : '08:00',
                'end_time' => $schedule->end_time ? $schedule->end_time->format('H:i') : '17:00',
                'late_tolerance' => $schedule->late_tolerance ?? 30
            ];
        }
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
}