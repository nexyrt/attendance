<?php

namespace App\Livewire\LeaveRequests\Approvals;

use App\Livewire\Traits\Alert;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class Approve extends Component
{
    use Alert;

    public ?LeaveRequest $leaveRequest = null;
    public bool $modal = false;
    public ?string $signature = null;


    public function render(): View
    {
        return view('livewire.manager.leave-request.approve');
    }

    #[On('load::approve-leave-request')]
    public function load(LeaveRequest $leaveRequest): void
    {
        $this->leaveRequest = $leaveRequest;
        $this->modal = true;
        
        // Reset form
        $this->signature = null;
    }

    public function rules(): array
    {
        return [
            'signature' => ['required', 'string'],
        ];
    }

    public function approve(): void
    {
        $this->validate();

        if (!$this->leaveRequest || $this->leaveRequest->status !== LeaveRequest::STATUS_PENDING_MANAGER) {
            $this->error('Pengajuan cuti tidak dapat disetujui');
            return;
        }

        $signaturePath = $this->saveSignature();
        
        $this->leaveRequest->update([
            'status' => LeaveRequest::STATUS_PENDING_HR,
            'manager_id' => Auth::id(),
            'manager_approved_at' => now(),
            'manager_signature' => $signaturePath,
        ]);

        $this->dispatch('approved');
        $this->modal = false;
        $this->resetExcept('leaveRequest');
        $this->success('Pengajuan cuti berhasil disetujui');
    }

    private function saveSignature(): string
    {
        $manager = Auth::user();
        $filename = "{$manager->id}_{$manager->name}_manager.png";
        $path = "signatures/manager/{$filename}";

        // Convert base64 to image and save (overwrites existing)
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->signature));
        Storage::disk('public')->put($path, $imageData);

        return $path;
    }
}
