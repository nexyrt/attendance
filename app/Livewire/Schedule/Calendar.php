<?php

namespace App\Livewire\Schedule;

use App\Models\Schedule as ScheduleModel;
use App\Models\ScheduleException;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Calendar extends Component
{
    public int $currentMonth;
    public int $currentYear;
    public bool $modal = false;
    public ?Carbon $selectedDate = null;

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

    public function showDateDetails(string $dateString): void
    {
        $this->selectedDate = Carbon::createFromFormat('Y-m-d', $dateString);
        $this->modal = true;
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
                'dateString' => $currentDate->format('Y-m-d'),
                'isCurrentMonth' => $currentDate->month === $this->currentMonth,
                'isToday' => $currentDate->isToday(),
                'isWeekend' => $currentDate->isWeekend(),
                'isPast' => $currentDate->isPast(),
                'schedule' => $this->getScheduleForDate($currentDate),
                'exception' => $this->getExceptionForDate($currentDate),
                'hasActivity' => $this->hasActivityOnDate($currentDate),
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

    #[Computed]
    public function selectedDateDetails(): ?array
    {
        if (!$this->selectedDate) {
            return null;
        }

        $schedule = $this->getScheduleForDate($this->selectedDate);
        $exception = $this->getExceptionForDate($this->selectedDate);
        $attendances = $this->getAttendancesForDate($this->selectedDate);

        return [
            'date' => $this->selectedDate,
            'dayName' => $this->selectedDate->locale('id')->format('l'),
            'dateFormatted' => $this->selectedDate->locale('id')->format('d F Y'),
            'isToday' => $this->selectedDate->isToday(),
            'isPast' => $this->selectedDate->isPast(),
            'isWeekend' => $this->selectedDate->isWeekend(),
            'schedule' => $schedule,
            'exception' => $exception,
            'attendances' => $attendances,
            'attendanceCount' => $attendances->count(),
            'workingSchedule' => $this->getWorkingScheduleText($schedule, $exception),
            'status' => $this->getDateStatus($schedule, $exception),
        ];
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

    private function getAttendancesForDate(Carbon $date): \Illuminate\Database\Eloquent\Collection
    {
        $query = Attendance::where('date', $date->format('Y-m-d'))
            ->with(['user' => function($q) {
                $q->select('id', 'name', 'department_id');
            }, 'user.department']);

        // Filter by department for managers
        if (Auth::user()->role === 'manager') {
            $query->whereHas('user', function($q) {
                $q->where('department_id', Auth::user()->department_id);
            });
        }

        return $query->orderBy('check_in')->get();
    }

    private function hasActivityOnDate(Carbon $date): bool
    {
        // Check if there's any attendance record for this date
        $attendanceCount = Attendance::where('date', $date->format('Y-m-d'))
            ->when(Auth::user()->role === 'manager', function ($query) {
                $query->whereHas('user', function($q) {
                    $q->where('department_id', Auth::user()->department_id);
                });
            })
            ->count();

        return $attendanceCount > 0;
    }

    private function getWorkingScheduleText(?ScheduleModel $schedule, ?ScheduleException $exception): string
    {
        if ($exception) {
            switch ($exception->status) {
                case 'holiday':
                    return 'Hari Libur';
                case 'event':
                    if ($exception->start_time && $exception->end_time) {
                        return "Event: {$exception->start_time->format('H:i')} - {$exception->end_time->format('H:i')}";
                    }
                    return 'Event/Training';
                case 'regular':
                    if ($exception->start_time && $exception->end_time) {
                        return "Jadwal Khusus: {$exception->start_time->format('H:i')} - {$exception->end_time->format('H:i')}";
                    }
                    return 'Jadwal Khusus';
            }
        }

        if ($schedule && $schedule->start_time && $schedule->end_time) {
            return "Jam Kerja: {$schedule->start_time->format('H:i')} - {$schedule->end_time->format('H:i')}";
        }

        return 'Tidak ada jadwal';
    }

    private function getDateStatus(?ScheduleModel $schedule, ?ScheduleException $exception): string
    {
        if ($exception) {
            return match($exception->status) {
                'holiday' => 'libur',
                'event' => 'event',
                'regular' => 'khusus',
                default => 'normal'
            };
        }

        if ($schedule && $schedule->start_time) {
            return 'kerja';
        }

        return 'libur';
    }
}