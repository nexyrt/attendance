<?php

namespace App\Livewire\Staff\LeaveRequest;

use App\Livewire\Traits\Alert;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use Alert, WithFileUploads;

    // Modal control
    public bool $modal = false;

    // Form fields
    public ?string $type = null;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?string $reason = null;
    public $attachment = null;

    public function render(): View
    {
        return view('livewire.staff.leave-request.create');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:sick,annual,important,other'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Check leave balance
        $leaveBalance = auth()->user()->currentLeaveBalance();
        $duration = $this->calculateDuration($validated['start_date'], $validated['end_date']);

        if ($leaveBalance && $leaveBalance->remaining_balance < $duration) {
            $this->toast()
                ->error('Gagal!', 'Saldo cuti tidak mencukupi')
                ->send();
            return;
        }

        // Handle file upload
        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('leave-attachments', 'public');
        }

        // Create leave request
        LeaveRequest::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'status' => LeaveRequest::STATUS_PENDING_MANAGER,
        ]);

        $this->dispatch('created');
        $this->reset(['type', 'start_date', 'end_date', 'reason', 'attachment']);
        $this->modal = false;

        $this->toast()
            ->success('Berhasil!', 'Pengajuan cuti berhasil dibuat')
            ->send();
    }

    private function calculateDuration(string $startDate, string $endDate): int
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        $duration = 0;
        for ($date = $start; $date->lessThanOrEquals($end); $date = $date->addDay()) {
            if (!$date->isWeekend()) {
                $duration++;
            }
        }

        return $duration;
    }
}