<?php

namespace App\Livewire\Staff\LeaveRequest;

use App\Livewire\Traits\Alert;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class Create extends Component
{
    use Alert, WithFileUploads;

    public LeaveRequest $leaveRequest;
    public array $dateRange = [];
    public ?string $signature = null;
    public $attachment = null;

    public function mount(): void
    {
        $this->leaveRequest = new LeaveRequest([
            'user_id' => Auth::id(),
        ]);
        $this->dateRange = [
            now()->addDay()->format('Y-m-d'),
            now()->addDay()->format('Y-m-d')
        ];
    }

    public function render(): View
    {
        return view('livewire.staff.leave-request.create');
    }

    public function rules(): array
    {
        return [
            'leaveRequest.type' => ['required', 'in:sick,annual,important,other'],
            'dateRange' => ['required', 'array', 'min:2'],
            'dateRange.0' => ['required', 'date', 'after_or_equal:today'],
            'dateRange.1' => ['required', 'date', 'after_or_equal:dateRange.0'],
            'leaveRequest.reason' => ['required', 'string', 'min:10', 'max:500'],
            'signature' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf'],
        ];
    }

    #[Computed]
    public function leaveBalance(): ?LeaveBalance
    {
        return LeaveBalance::where('user_id', Auth::id())
            ->where('year', now()->year)
            ->first();
    }

    #[Computed]
    public function requestedDays(): int
    {
        if (!is_array($this->dateRange) || count($this->dateRange) !== 2) {
            return 0;
        }

        $tempRequest = new LeaveRequest([
            'start_date' => $this->dateRange[0],
            'end_date' => $this->dateRange[1],
        ]);

        return $tempRequest->getDurationInDays();
    }

    #[Computed]
    public function balanceAfterRequest(): int
    {
        return $this->leaveBalance?->remaining_balance - $this->requestedDays ?? 0;
    }

    #[Computed]
    public function hasValidBalance(): bool
    {
        return $this->leaveRequest->type !== 'annual' || $this->balanceAfterRequest >= 0;
    }

    public function save(): void
    {
        $this->validate();

        if (!$this->hasValidBalance) {
            $this->error('Insufficient leave balance for annual leave');
            return;
        }

        $signaturePath = $this->saveSignature();
        $attachmentPath = $this->saveAttachment();

        $this->leaveRequest->fill([
            'user_id' => Auth::id(),
            'start_date' => $this->dateRange[0],
            'end_date' => $this->dateRange[1],
            'staff_signature' => $signaturePath,
            'attachment_path' => $attachmentPath,
            'status' => LeaveRequest::STATUS_PENDING_MANAGER,
        ]);

        $this->leaveRequest->save();

        // Update leave balance for annual leave
        if ($this->leaveRequest->type === 'annual' && $this->leaveBalance) {
            $this->leaveBalance->updateBalance(
                $this->leaveBalance->used_balance + $this->requestedDays
            );
        }

        $this->success('Leave request submitted successfully');
        $this->redirect(route('leave-requests.index'));
    }

    private function saveSignature(): string
    {
        $user = Auth::user();
        $filename = "{$user->id}_{$user->name}.png";
        $path = "signatures/staff/{$filename}";

        // Convert base64 to image and save
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->signature));
        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    private function saveAttachment(): ?string
    {
        if (!$this->attachment) {
            return null;
        }

        return $this->attachment->store('leave-attachments', 'public');
    }
}