<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function leaveRequest(LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->load(['user', 'user.department', 'manager', 'hr', 'director']);
        
        $pdf = Pdf::loadView('print.leave-request', compact('leaveRequest'))
            ->setPaper('a4', 'portrait');
            
        return $pdf->stream("leave-request-{$leaveRequest->id}.pdf");
    }
}