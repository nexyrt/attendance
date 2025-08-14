<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class PrintController extends Controller
{
    public function leaveRequest(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['user', 'manager', 'hr', 'director']);

        return view('print.leave-request', compact('leaveRequest'));
    }
}