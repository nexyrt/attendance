<?php

namespace App\Livewire\Schedule;

use App\Livewire\Traits\Alert;
use App\Models\Schedule as ScheduleModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    use Alert;

    public bool $modal = false;
    public array $schedules = [];

    public function mount(): void
    {
        $this->initializeSchedules();
    }

    public function render(): View
    {
        return view('livewire.schedule.create');
    }

    #[On('load::create-schedule')]
    public function load(): void
    {
        $this->initializeSchedules();
        $this->modal = true;
    }

    #[Computed]
    public function canCreateSchedule(): bool
    {
        return ScheduleModel::count() < 7;
    }

    #[Computed]
    public function availableDays(): array
    {
        $existingDays = ScheduleModel::pluck('day_of_week')->toArray();
        $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        return array_diff($allDays, $existingDays);
    }

    public function rules(): array
    {
        $rules = [];
        foreach ($this->schedules as $index => $schedule) {
            $rules["schedules.{$index}.start_time"] = ['required', 'date_format:H:i'];
            $rules["schedules.{$index}.end_time"] = ['required', 'date_format:H:i', 'after:schedules.' . $index . '.start_time'];
            $rules["schedules.{$index}.late_tolerance"] = ['required', 'integer', 'min:0', 'max:120'];
            $rules["schedules.{$index}.is_workday"] = ['boolean'];
        }
        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        $created = 0;
        foreach ($this->schedules as $schedule) {
            if (!ScheduleModel::where('day_of_week', $schedule['day_of_week'])->exists()) {
                ScheduleModel::create([
                    'day_of_week' => $schedule['day_of_week'],
                    'start_time' => $schedule['is_workday'] ? $schedule['start_time'] : null,
                    'end_time' => $schedule['is_workday'] ? $schedule['end_time'] : null,
                    'late_tolerance' => $schedule['is_workday'] ? $schedule['late_tolerance'] : 0
                ]);
                $created++;
            }
        }

        $this->dispatch('created');
        $this->modal = false;
        $this->success("Berhasil menambahkan {$created} jadwal kerja");
    }

    private function initializeSchedules(): void
    {
        $this->schedules = [];
        foreach ($this->availableDays as $day) {
            $isWeekday = in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
            
            $this->schedules[] = [
                'day_of_week' => $day,
                'start_time' => $isWeekday ? '08:00' : '09:00',
                'end_time' => $isWeekday ? '17:00' : '12:00',
                'late_tolerance' => $isWeekday ? 30 : 60,
                'is_workday' => $isWeekday
            ];
        }
    }
}