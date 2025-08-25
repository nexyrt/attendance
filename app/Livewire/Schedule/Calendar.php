<?php

namespace App\Livewire\Schedule;

use App\Models\Schedule as ScheduleModel;
use App\Models\ScheduleException;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Calendar extends Component
{
    public int $currentMonth;
    public int $currentYear;

    public function mount(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function render(): View
    {
        return view('livewire.schedule.calendar');
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function goToday(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    #[Computed]
    public function calendarDays(): array
    {
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $days[] = [
                'date' => $currentDate->copy(),
                'isCurrentMonth' => $currentDate->month === $this->currentMonth,
                'isToday' => $currentDate->isToday(),
                'isWeekend' => $currentDate->isWeekend(),
                'schedule' => $this->getScheduleForDate($currentDate),
                'exception' => $this->getExceptionForDate($currentDate),
            ];
            $currentDate->addDay();
        }

        return collect($days)->chunk(7)->toArray();
    }

    #[Computed]
    public function monthName(): string
    {
        return Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)
            ->locale('id')
            ->format('F Y');
    }

    private function getScheduleForDate(Carbon $date): ?ScheduleModel
    {
        $dayName = strtolower($date->format('l'));

        return ScheduleModel::where('day_of_week', $dayName)->first();
    }

    private function getExceptionForDate(Carbon $date): ?ScheduleException
    {
        return ScheduleException::where('date', $date->format('Y-m-d'))
            ->when(Auth::user()->role === 'manager', function ($query) {
                $query->whereHas('departments', function ($q) {
                    $q->where('departments.id', Auth::user()->department_id);
                });
            })
            ->first();
    }
}