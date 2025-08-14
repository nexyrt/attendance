<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Leave Request - {{ $leaveRequest->user->name }}</title>
        <style>
            @media print {
                body {
                    margin: 0;
                }

                .no-print {
                    display: none;
                }
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                margin: 20px;
                color: #000;
            }

            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #000;
                padding-bottom: 20px;
            }

            .form-title {
                font-size: 18px;
                font-weight: bold;
                text-transform: uppercase;
                margin-bottom: 10px;
            }

            .form-section {
                margin-bottom: 20px;
            }

            .form-row {
                display: flex;
                margin-bottom: 10px;
            }

            .form-label {
                width: 150px;
                font-weight: bold;
            }

            .form-value {
                flex: 1;
                border-bottom: 1px solid #ccc;
                min-height: 20px;
                padding-bottom: 2px;
            }

            .signature-section {
                margin-top: 40px;
                display: flex;
                justify-content: space-between;
            }

            .signature-box {
                width: 200px;
                text-align: center;
            }

            .signature-line {
                border-top: 1px solid #000;
                margin-top: 60px;
                padding-top: 5px;
            }

            .signature-img {
                max-width: 150px;
                max-height: 60px;
                margin-bottom: 10px;
            }

            .approval-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            .approval-table th,
            .approval-table td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }

            .approval-table th {
                background-color: #f5f5f5;
                font-weight: bold;
            }

            .print-btn {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #007bff;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
            }
        </style>
    </head>

    <body>
        <button class="print-btn no-print" onclick="window.print()">Print</button>

        <div class="header">
            <div class="form-title">Leave Request Form</div>
            <div>{{ config('app.name') }}</div>
        </div>

        <div class="form-section">
            <div class="form-row">
                <div class="form-label">Employee Name:</div>
                <div class="form-value">{{ $leaveRequest->user->name }}</div>
            </div>

            <div class="form-row">
                <div class="form-label">Department:</div>
                <div class="form-value">{{ $leaveRequest->user->department?->name }}</div>
            </div>

            <div class="form-row">
                <div class="form-label">Request Date:</div>
                <div class="form-value">{{ $leaveRequest->created_at->format('F j, Y') }}</div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-row">
                <div class="form-label">Leave Type:</div>
                <div class="form-value">{{ ucfirst($leaveRequest->type) }} Leave</div>
            </div>

            <div class="form-row">
                <div class="form-label">Start Date:</div>
                <div class="form-value">{{ $leaveRequest->start_date->format('F j, Y') }}</div>
            </div>

            <div class="form-row">
                <div class="form-label">End Date:</div>
                <div class="form-value">{{ $leaveRequest->end_date->format('F j, Y') }}</div>
            </div>

            <div class="form-row">
                <div class="form-label">Duration:</div>
                <div class="form-value">{{ $leaveRequest->getDurationInDays() }} working day(s)</div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-row">
                <div class="form-label">Reason:</div>
                <div class="form-value" style="min-height: 60px;">{{ $leaveRequest->reason }}</div>
            </div>
        </div>

        {{-- Approval Timeline --}}
        <table class="approval-table">
            <thead>
                <tr>
                    <th>Approval Level</th>
                    <th>Approver</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Manager</td>
                    <td>{{ $leaveRequest->manager?->name ?? 'Pending Assignment' }}</td>
                    <td>
                        @if ($leaveRequest->manager_approved_at)
                            Approved
                        @elseif(str_starts_with($leaveRequest->status, 'rejected_manager'))
                            Rejected
                        @else
                            Pending
                        @endif
                    </td>
                    <td>{{ $leaveRequest->manager_approved_at?->format('M j, Y') ?? '-' }}</td>
                    <td style="height: 50px;">
                        @if ($leaveRequest->manager_signature && file_exists(public_path('storage/' . $leaveRequest->manager_signature)))
                            <img src="{{ asset('storage/' . $leaveRequest->manager_signature) }}" class="signature-img"
                                alt="Manager Signature">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>HR</td>
                    <td>{{ $leaveRequest->hr?->name ?? 'Pending Assignment' }}</td>
                    <td>
                        @if ($leaveRequest->hr_approved_at)
                            Approved
                        @elseif(str_starts_with($leaveRequest->status, 'rejected_hr'))
                            Rejected
                        @else
                            Pending
                        @endif
                    </td>
                    <td>{{ $leaveRequest->hr_approved_at?->format('M j, Y') ?? '-' }}</td>
                    <td style="height: 50px;">
                        @if ($leaveRequest->hr_signature && file_exists(public_path('storage/' . $leaveRequest->hr_signature)))
                            <img src="{{ asset('storage/' . $leaveRequest->hr_signature) }}" class="signature-img"
                                alt="HR Signature">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Director</td>
                    <td>{{ $leaveRequest->director?->name ?? 'Pending Assignment' }}</td>
                    <td>
                        @if ($leaveRequest->director_approved_at)
                            Approved
                        @elseif(str_starts_with($leaveRequest->status, 'rejected_director'))
                            Rejected
                        @else
                            Pending
                        @endif
                    </td>
                    <td>{{ $leaveRequest->director_approved_at?->format('M j, Y') ?? '-' }}</td>
                    <td style="height: 50px;">
                        @if ($leaveRequest->director_signature && file_exists(public_path('storage/' . $leaveRequest->director_signature)))
                            <img src="{{ asset('storage/' . $leaveRequest->director_signature) }}"
                                class="signature-img" alt="Director Signature">
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Staff Signature --}}
        <div class="signature-section">
            <div class="signature-box">
                <div><strong>Employee Signature:</strong></div>
                @if ($leaveRequest->staff_signature && file_exists(public_path('storage/' . $leaveRequest->staff_signature)))
                    <img src="{{ asset('storage/' . $leaveRequest->staff_signature) }}" class="signature-img"
                        alt="Staff Signature">
                @endif
                <div class="signature-line">{{ $leaveRequest->user->name }}</div>
                <div>{{ $leaveRequest->created_at->format('F j, Y') }}</div>
            </div>

            <div class="signature-box">
                <div><strong>Date Printed:</strong></div>
                <div class="signature-line">{{ now()->format('F j, Y H:i') }}</div>
            </div>
        </div>

        <script>
            // Auto print when opened in new window
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        </script>
    </body>

</html>
