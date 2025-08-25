<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Pengajuan Cuti - {{ $leaveRequest->user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 10px;
        }
        
        .form-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .no-border {
            border: none;
        }
        
        .info-table td {
            border: 1px solid #000;
            padding: 10px;
        }
        
        .info-table .label {
            width: 30%;
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .signature-box .title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .signature-box .box {
            border: 1px solid #000;
            height: 80px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .signature-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-cancel { background-color: #e2e3e5; color: #383d41; }
        
        .reason-box {
            border: 1px solid #000;
            padding: 15px;
            min-height: 60px;
            margin-bottom: 20px;
        }
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>PT. JAYA KONSTRUKSI BERKELANJUTAN</h1>
        <p>Jl. Contoh No. 123, Jakarta 12345</p>
        <p>Telp: (021) 12345678 | Email: info@jkb.com</p>
    </div>
    
    {{-- Form Title --}}
    <div class="form-title">
        FORMULIR PENGAJUAN CUTI
    </div>
    
    {{-- Employee Information --}}
    <table class="info-table">
        <tr>
            <td class="label">Nama Karyawan</td>
            <td>{{ $leaveRequest->user->name }}</td>
            <td class="label">Departemen</td>
            <td>{{ $leaveRequest->user->department?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pengajuan</td>
            <td>{{ $leaveRequest->created_at->format('d F Y') }}</td>
            <td class="label">Status</td>
            <td>
                <span class="status-badge {{ match($leaveRequest->status) {
                    'approved' => 'status-approved',
                    'rejected_manager', 'rejected_hr', 'rejected_director' => 'status-rejected',
                    'cancel' => 'status-cancel',
                    default => 'status-pending'
                } }}">
                    {{ match($leaveRequest->status) {
                        'approved' => 'DISETUJUI',
                        'rejected_manager' => 'DITOLAK MANAGER',
                        'rejected_hr' => 'DITOLAK HR',
                        'rejected_director' => 'DITOLAK DIREKTUR',
                        'cancel' => 'DIBATALKAN',
                        'pending_manager' => 'MENUNGGU MANAGER',
                        'pending_hr' => 'MENUNGGU HR',
                        'pending_director' => 'MENUNGGU DIREKTUR',
                        default => 'UNKNOWN'
                    } }}
                </span>
            </td>
        </tr>
    </table>
    
    {{-- Leave Details --}}
    <table class="info-table">
        <tr>
            <td class="label">Jenis Cuti</td>
            <td>{{ match($leaveRequest->type) {
                'sick' => 'Cuti Sakit',
                'annual' => 'Cuti Tahunan',
                'important' => 'Cuti Penting',
                'other' => 'Lainnya'
            } }}</td>
            <td class="label">Durasi</td>
            <td>{{ $leaveRequest->getDurationInDays() }} hari</td>
        </tr>
        <tr>
            <td class="label">Tanggal Mulai</td>
            <td>{{ $leaveRequest->start_date->format('d F Y') }}</td>
            <td class="label">Tanggal Berakhir</td>
            <td>{{ $leaveRequest->end_date->format('d F Y') }}</td>
        </tr>
    </table>
    
    {{-- Reason --}}
    <h3>Alasan Pengajuan Cuti:</h3>
    <div class="reason-box">
        {{ $leaveRequest->reason }}
    </div>
    
    {{-- Rejection Reason if any --}}
    @if($leaveRequest->rejection_reason)
        <h3>Alasan Penolakan:</h3>
        <div class="reason-box">
            {{ $leaveRequest->rejection_reason }}
        </div>
    @endif
    
    {{-- Signatures --}}
    <div class="signature-section">
        {{-- Staff Signature --}}
        <div class="signature-box">
            <div class="title">Pemohon</div>
            <div class="box">
                @if($leaveRequest->staff_signature && Storage::disk('public')->exists($leaveRequest->staff_signature))
                    <img src="{{ public_path('storage/' . $leaveRequest->staff_signature) }}" 
                         alt="Staff Signature" class="signature-image">
                @endif
            </div>
            <div>{{ $leaveRequest->user->name }}</div>
            <div>{{ $leaveRequest->created_at->format('d/m/Y') }}</div>
        </div>
        
        {{-- Manager Signature --}}
        <div class="signature-box">
            <div class="title">Manager</div>
            <div class="box">
                @if($leaveRequest->manager_signature && Storage::disk('public')->exists($leaveRequest->manager_signature))
                    <img src="{{ public_path('storage/' . $leaveRequest->manager_signature) }}" 
                         alt="Manager Signature" class="signature-image">
                @endif
            </div>
            <div>{{ $leaveRequest->manager?->name ?? '-' }}</div>
            <div>{{ $leaveRequest->manager_approved_at?->format('d/m/Y') ?? '-' }}</div>
        </div>
        
        {{-- HR Signature --}}
        <div class="signature-box">
            <div class="title">HR</div>
            <div class="box">
                @if($leaveRequest->hr_signature && Storage::disk('public')->exists($leaveRequest->hr_signature))
                    <img src="{{ public_path('storage/' . $leaveRequest->hr_signature) }}" 
                         alt="HR Signature" class="signature-image">
                @endif
            </div>
            <div>{{ $leaveRequest->hr?->name ?? '-' }}</div>
            <div>{{ $leaveRequest->hr_approved_at?->format('d/m/Y') ?? '-' }}</div>
        </div>
    </div>
    
    {{-- Director Signature --}}
    <div class="signature-section" style="margin-top: 20px;">
        <div class="signature-box" style="width: 50%; margin: 0 auto;">
            <div class="title">Direktur</div>
            <div class="box">
                @if($leaveRequest->director_signature && Storage::disk('public')->exists($leaveRequest->director_signature))
                    <img src="{{ public_path('storage/' . $leaveRequest->director_signature) }}" 
                         alt="Director Signature" class="signature-image">
                @endif
            </div>
            <div>{{ $leaveRequest->director?->name ?? '-' }}</div>
            <div>{{ $leaveRequest->director_approved_at?->format('d/m/Y') ?? '-' }}</div>
        </div>
    </div>
    
    {{-- Footer --}}
    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
        Dokumen ini dicetak pada {{ now()->format('d F Y H:i') }} WIB
    </div>
</body>
</html>