<?php

namespace App\Livewire\Attendance\TeamAttendance;

use App\Livewire\Traits\Alert;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Export extends Component
{
    use Alert;

    public bool $modal = false;
    public ?string $format = 'csv';
    public ?string $period = 'month';
    public ?string $month = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public array $selectedMembers = [];
    public bool $includeStats = true;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function render(): View
    {
        return view('livewire.manager.team-attendance.export');
    }

    #[Computed]
    public function teamMembers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('department_id', Auth::user()->department_id)
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function rules(): array
    {
        return [
            'format' => ['required', 'in:csv,excel,pdf'],
            'period' => ['required', 'in:month,custom'],
            'month' => ['required_if:period,month', 'nullable', 'date_format:Y-m'],
            'startDate' => ['required_if:period,custom', 'nullable', 'date'],
            'endDate' => ['required_if:period,custom', 'nullable', 'date', 'after_or_equal:startDate'],
            'selectedMembers' => ['array'],
            'selectedMembers.*' => ['exists:users,id'],
        ];
    }

    public function export(): StreamedResponse
    {
        $this->validate();

        $data = $this->getExportData();
        $filename = $this->generateFilename();

        return match ($this->format) {
            'csv' => $this->exportCsv($data, $filename),
            'excel' => $this->exportExcel($data, $filename),
            'pdf' => $this->exportPdf($data, $filename),
        };
    }

    public function updatedPeriod(): void
    {
        if ($this->period === 'month') {
            $this->startDate = now()->setMonth(substr($this->month, 5, 2))
                ->setYear(substr($this->month, 0, 4))
                ->startOfMonth()
                ->format('Y-m-d');
            $this->endDate = now()->setMonth(substr($this->month, 5, 2))
                ->setYear(substr($this->month, 0, 4))
                ->endOfMonth()
                ->format('Y-m-d');
        }
    }

    public function updatedMonth(): void
    {
        $this->updatedPeriod();
    }

    private function getExportData(): array
    {
        $departmentId = Auth::user()->department_id;

        $query = Attendance::whereHas('user', function (Builder $query) use ($departmentId) {
            $query->where('department_id', $departmentId);

            if (!empty($this->selectedMembers)) {
                $query->whereIn('id', $this->selectedMembers);
            }
        })
            ->with(['user', 'checkInOffice', 'checkOutOffice'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date')
            ->orderBy('user_id');

        $attendances = $query->get();

        $data = [
            'attendances' => $attendances->map(function ($attendance) {
                return [
                    'employee_name' => $attendance->user->name,
                    'date' => $attendance->date->format('Y-m-d'),
                    'day_of_week' => $attendance->date->format('l'),
                    'check_in' => $attendance->check_in?->format('H:i:s'),
                    'check_out' => $attendance->check_out?->format('H:i:s'),
                    'working_hours' => $attendance->working_hours,
                    'late_hours' => $attendance->late_hours,
                    'status' => ucfirst($attendance->status),
                    'check_in_office' => $attendance->checkInOffice?->name,
                    'check_out_office' => $attendance->checkOutOffice?->name,
                ];
            })->toArray(),
        ];

        if ($this->includeStats) {
            $data['summary'] = $this->generateSummary($attendances);
        }

        return $data;
    }

    private function generateSummary($attendances): array
    {
        $groupedByUser = $attendances->groupBy('user_id');

        return [
            'period' => [
                'start' => $this->startDate,
                'end' => $this->endDate,
                'department' => Auth::user()->department->name,
            ],
            'totals' => [
                'total_records' => $attendances->count(),
                'total_employees' => $groupedByUser->count(),
                'total_working_hours' => round($attendances->sum('working_hours'), 2),
                'total_late_hours' => round($attendances->sum('late_hours'), 2),
                'present_count' => $attendances->where('status', 'present')->count(),
                'late_count' => $attendances->where('status', 'late')->count(),
                'early_leave_count' => $attendances->where('status', 'early_leave')->count(),
            ],
            'by_employee' => $groupedByUser->map(function ($userAttendances, $userId) {
                $user = $userAttendances->first()->user;
                return [
                    'name' => $user->name,
                    'total_days' => $userAttendances->count(),
                    'present_days' => $userAttendances->where('status', 'present')->count(),
                    'late_days' => $userAttendances->where('status', 'late')->count(),
                    'total_hours' => round($userAttendances->sum('working_hours'), 2),
                    'avg_daily_hours' => round($userAttendances->avg('working_hours'), 2),
                    'total_late_hours' => round($userAttendances->sum('late_hours'), 2),
                ];
            })->values()->toArray(),
        ];
    }

    private function generateFilename(): string
    {
        $department = Auth::user()->department->name;
        $period = $this->period === 'month'
            ? date('Y-m', strtotime($this->month))
            : date('Y-m-d', strtotime($this->startDate)) . '_to_' . date('Y-m-d', strtotime($this->endDate));

        return "team-attendance_{$department}_{$period}";
    }

    private function exportCsv(array $data, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Headers
            fputcsv($handle, [
                'Employee Name',
                'Date',
                'Day',
                'Check In',
                'Check Out',
                'Working Hours',
                'Late Hours',
                'Status',
                'Check In Office',
                'Check Out Office'
            ]);

            // Data
            foreach ($data['attendances'] as $attendance) {
                fputcsv($handle, $attendance);
            }

            fclose($handle);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportExcel(array $data, string $filename): StreamedResponse
    {
        // Simple Excel export using CSV with Excel headers
        return response()->streamDownload(function () use ($data) {
            echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Employee Name',
                'Date',
                'Day',
                'Check In',
                'Check Out',
                'Working Hours',
                'Late Hours',
                'Status',
                'Check In Office',
                'Check Out Office'
            ]);

            foreach ($data['attendances'] as $attendance) {
                fputcsv($handle, $attendance);
            }

            fclose($handle);
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportPdf(array $data, string $filename): StreamedResponse
    {
        // This would typically use a PDF library like dompdf
        // For now, return a simple text-based PDF
        return response()->streamDownload(function () use ($data) {
            echo "Team Attendance Report\n";
            echo "=====================\n\n";

            if (isset($data['summary'])) {
                echo "Period: {$data['summary']['period']['start']} to {$data['summary']['period']['end']}\n";
                echo "Department: {$data['summary']['period']['department']}\n\n";
            }

            foreach ($data['attendances'] as $attendance) {
                echo sprintf(
                    "%-20s %-12s %-10s %-10s %-8s %-6s %-15s\n",
                    $attendance['employee_name'],
                    $attendance['date'],
                    $attendance['check_in'] ?? '-',
                    $attendance['check_out'] ?? '-',
                    $attendance['working_hours'] ?? '0',
                    $attendance['late_hours'] ?? '0',
                    $attendance['status']
                );
            }
        }, $filename . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function getFormatOptions(): array
    {
        return [
            'csv' => 'CSV',
            'excel' => 'Excel',
            'pdf' => 'PDF',
        ];
    }

    public function getPeriodOptions(): array
    {
        return [
            'month' => 'Monthly',
            'custom' => 'Custom Range',
        ];
    }
}
