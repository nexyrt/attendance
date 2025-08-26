<?php

namespace App\Livewire\Schedule;

use App\Models\Schedule as ScheduleModel;
use App\Models\ScheduleException;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.schedule.index');
    }

    #[Computed]
    public function schedules()
    {
        return ScheduleModel::orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
                      ->get();
    }

    #[Computed]
    public function upcomingExceptions()
    {
        return ScheduleException::where('date', '>=', now())
            ->when(Auth::user()->role === 'manager', function ($query) {
                $query->whereHas('departments', function ($q) {
                    $q->where('departments.id', Auth::user()->department_id);
                });
            })
            ->orderBy('date')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function canManage(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'director']);
    }

    #[Computed] 
    public function canCreateSchedule(): bool
    {
        return $this->canManage() && ScheduleModel::count() < 7;
    }

    #[Computed]
    public function hasSchedules(): bool
    {
        return ScheduleModel::exists();
    }

    #[Computed]
    public function scheduleCompletionStatus(): array
    {
        $totalDays = 7;
        $existingDays = ScheduleModel::count();
        
        return [
            'total' => $totalDays,
            'existing' => $existingDays,
            'missing' => $totalDays - $existingDays,
            'percentage' => round(($existingDays / $totalDays) * 100)
        ];
    }

    #[On('load::schedule-calendar')]
    public function loadCalendar(): void
    {
        $this->dispatch('load::calendar-view');
    }
}