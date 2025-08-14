<?php

namespace App\Livewire\Staff\LeaveRequest;

use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public ?LeaveRequest $leaveRequest = null;
    public bool $modal = false;

    public function render(): View
    {
        return view('livewire.staff.leave-request.show');
    }

    #[On('load::leave-request')]
    public function load(LeaveRequest $leaveRequest): void
    {
        $this->leaveRequest = $leaveRequest->load(['user', 'manager', 'hr', 'director']);
        $this->modal = true;
    }

    public function print(): void
    {
        $this->dispatch('print-leave-request', ['id' => $this->leaveRequest->id]);
    }
}