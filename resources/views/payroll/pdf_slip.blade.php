<!DOCTYPE html>
<html lang="km">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip — {{ $payroll->teacher->name }} — {{ $payroll->month->format('F Y') }}</title>
    <style>
        @font-face {
            font-family: 'KhmerOSBattambang';
            src: url('{{ public_path("fonts/KhmerOS_battambang.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'KhmerOSMoul';
            src: url('{{ public_path("fonts/KhmerOSMoul.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page { size: A4 portrait; margin: 12mm 18mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'KhmerOSBattambang', 'DejaVu Sans', sans-serif;
            color: #0f172a;
            background: #fff;
            font-size: 9pt;
            line-height: 1.4;
        }

        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 12px; }
        .motto { font-family: 'KhmerOSMoul', 'KhmerOSBattambang', serif; font-size: 11pt; color: #1e3a8a; margin-bottom: 2px; }
        .school-name { font-family: 'KhmerOSBattambang', sans-serif; font-size: 12pt; font-weight: bold; color: #1e3a8a; }
        .doc-title { font-size: 13pt; font-weight: bold; color: #1e40af; margin: 6px 0 2px; letter-spacing: 0.5px; text-transform: uppercase; }
        .doc-sub { font-size: 8pt; color: #64748b; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        .info-table td { padding: 4px 8px; font-size: 8.5pt; vertical-align: top; }
        .info-label { font-weight: bold; color: #64748b; width: 18%; }
        .info-value { color: #0f172a; width: 32%; }

        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin: 10px 0 6px;
            text-transform: uppercase;
        }
        .section-marker {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #1e3a8a;
            border-radius: 2px;
            margin-right: 4px;
        }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th { background: #1e3a8a; color: #fff; padding: 5px 8px; font-size: 8pt; font-weight: bold; text-align: left; }
        table.data-table td { padding: 4px 8px; font-size: 8.5pt; border-bottom: 1px solid #f1f5f9; }
        table.data-table tr:nth-child(even) td { background: #f8fafc; }

        .amount { text-align: right; font-weight: bold; }
        .deduct { color: #dc2626; }
        .bonus-col { color: #059669; }

        .net-row td { background: #1e3a8a !important; color: #fff !important; font-size: 10pt; font-weight: bold; padding: 7px 8px; }

        .attendance-boxes { width: 100%; margin-bottom: 8px; border-collapse: separate; border-spacing: 6px 0; }
        .att-box { text-align: center; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px; background: #fff; width: 33.33%; }
        .att-num { font-size: 15pt; font-weight: bold; line-height: 1.1; }
        .att-label { font-size: 7pt; color: #64748b; text-transform: uppercase; font-weight: bold; margin-top: 2px; }

        .footer { margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .sig-table { width: 100%; text-align: center; }
        .sig-line { width: 110px; border-bottom: 1px solid #94a3b8; margin: 30px auto 3px; }
        .sig-label { font-size: 7.5pt; color: #64748b; }
        .footer-note { font-size: 7pt; color: #94a3b8; text-align: center; margin-top: 8px; font-style: italic; }

        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; }
        .status-draft    { background: #fef3c7; color: #92400e; }
        .status-approved { background: #ede9fe; color: #4c1d95; }
        .status-paid     { background: #d1fae5; color: #064e3b; }
    </style>
</head>
<body>
@php
    $sym = $settings['currency_symbol'] ?? '$';
    $totalDeduct = $payroll->late_deduction + $payroll->absent_deduction;
@endphp

{{-- Official Header --}}
<div class="header">
    <div class="motto">ព្រះរាជាណាចក្រកម្ពុជា &bull; ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
    <div class="school-name">{{ \App\Models\Setting::getValue('university_name', 'វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស (NTTI)') }}</div>
    <div class="doc-title">Salary Payslip / ប័ណ្ណបើកប្រាក់បៀវត្សរ៍</div>
    <div class="doc-sub">
        {{ $payroll->month->format('F Y') }} &nbsp;|&nbsp;
        Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp;
        <span class="status-badge status-{{ $payroll->status }}">{{ strtoupper($payroll->status) }}</span>
    </div>
</div>

{{-- Teacher Info --}}
<table class="info-table">
    <tr>
        <td class="info-label">Name (English):</td>
        <td class="info-value"><strong>{{ $payroll->teacher->name }}</strong></td>
        <td class="info-label">ឈ្មោះជាខ្មែរ:</td>
        <td class="info-value"><strong>{{ $payroll->teacher->name_kh ?? '—' }}</strong></td>
    </tr>
    <tr>
        <td class="info-label">Employee ID:</td>
        <td class="info-value">{{ $payroll->teacher->employee_id }}</td>
        <td class="info-label">Department:</td>
        <td class="info-value">{{ $payroll->teacher->department ?? '—' }}</td>
    </tr>
    <tr>
        <td class="info-label">Position / តួនាទី:</td>
        <td class="info-value">{{ $payroll->teacher->position ?? '—' }}</td>
        <td class="info-label">Rank / កម្រិត:</td>
        <td class="info-value">{{ $payroll->teacher->position_rank ?? '—' }}</td>
    </tr>
    @if($payroll->academicPeriod)
    <tr>
        <td class="info-label">Academic Period:</td>
        <td class="info-value" colspan="3">{{ $payroll->academicPeriod->display_name }}</td>
    </tr>
    @endif
    @if($payroll->approved_at)
    <tr>
        <td class="info-label">Approved Date:</td>
        <td class="info-value" colspan="3">{{ $payroll->approved_at->format('d M Y') }} by {{ $payroll->approvedByUser->name ?? 'Admin' }}</td>
    </tr>
    @endif
</table>

{{-- Attendance Summary --}}
<div class="section-title">
    <span class="section-marker"></span> Attendance Summary / សង្ខេបវត្តមាន
</div>
<table class="attendance-boxes">
    <tr>
        <td class="att-box">
            <div class="att-num" style="color:#059669;">{{ $payroll->present_days }}</div>
            <div class="att-label">Present Days / ថ្ងៃមានវត្តមាន</div>
        </td>
        <td class="att-box">
            <div class="att-num" style="color:#dc2626;">{{ $payroll->absent_days }}</div>
            <div class="att-label">Absent Days / ថ្ងៃអវត្តមាន</div>
        </td>
        <td class="att-box">
            <div class="att-num" style="color:#d97706;">{{ $payroll->late_count }}</div>
            <div class="att-label">Late Times / មកយឺត ({{ $payroll->late_minutes_total }} min)</div>
        </td>
    </tr>
</table>
<div style="margin-bottom:8px; font-size:8pt; color:#64748b;">
    Working Days: <strong>{{ $payroll->working_days }} days</strong> &nbsp;|&nbsp;
    Attendance Rate: <strong>{{ $payroll->attendance_rate }}%</strong>
    @if($payroll->approved_leave_days > 0)
        &nbsp;|&nbsp; Approved Leave (Paid): <strong>{{ $payroll->approved_leave_days }} days</strong>
    @endif
</div>

{{-- Salary Breakdown --}}
<div class="section-title">
    <span class="section-marker"></span> Salary Calculation / ការគណនាប្រាក់បៀវត្សរ៍
</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Description / ការពិពណ៌នា</th>
            <th>Details / ព័ត៌មានលម្អិត</th>
            <th class="amount">Amount / ទឹកប្រាក់</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Base Salary / ប្រាក់ខែមូលដ្ឋាន</td>
            <td style="color:#64748b; font-size:8pt;">Monthly base</td>
            <td class="amount">{{ $sym }}{{ number_format($payroll->base_salary,2) }}</td>
        </tr>
        <tr>
            <td>Daily Rate / តម្លៃប្រចាំថ្ងៃ</td>
            <td style="color:#64748b; font-size:8pt;">{{ $sym }}{{ number_format($payroll->base_salary,2) }} &divide; {{ $payroll->working_days }} days</td>
            <td class="amount">{{ $sym }}{{ number_format($payroll->daily_rate,2) }}</td>
        </tr>
        <tr>
            <td>Gross Salary / ប្រាក់ចំណូលសរុប</td>
            <td style="color:#64748b; font-size:8pt;">{{ $payroll->effective_worked_days }} effective days &times; {{ $sym }}{{ number_format($payroll->daily_rate,2) }}</td>
            <td class="amount">{{ $sym }}{{ number_format($payroll->gross_salary,2) }}</td>
        </tr>
        @if($payroll->late_deduction > 0)
        <tr>
            <td class="deduct">Late Deduction / កាត់ពេលមកយឺត</td>
            <td style="color:#64748b; font-size:8pt;">{{ $payroll->late_minutes_total }} min &times; {{ $sym }}{{ number_format($settings['late_deduction_rate_per_min'] ?? 0.05, 2) }}</td>
            <td class="amount deduct">-{{ $sym }}{{ number_format($payroll->late_deduction,2) }}</td>
        </tr>
        @endif
        @if($payroll->absent_deduction > 0)
        <tr>
            <td class="deduct">Absent Deduction / កាត់ពេលអវត្តមាន</td>
            <td style="color:#64748b; font-size:8pt;">{{ $payroll->absent_days }} days &times; {{ $sym }}{{ number_format($payroll->daily_rate,2) }}</td>
            <td class="amount deduct">-{{ $sym }}{{ number_format($payroll->absent_deduction,2) }}</td>
        </tr>
        @endif
        @if($payroll->bonus > 0)
        <tr>
            <td class="bonus-col">Bonus / ប្រាក់រង្វាន់</td>
            <td></td>
            <td class="amount bonus-col">+{{ $sym }}{{ number_format($payroll->bonus,2) }}</td>
        </tr>
        @endif
        @if($payroll->overtime_pay > 0)
        <tr>
            <td class="bonus-col">Overtime Pay / បន្ថែមម៉ោង</td>
            <td style="color:#64748b; font-size:8pt;">{{ $payroll->overtime_hours }} hrs</td>
            <td class="amount bonus-col">+{{ $sym }}{{ number_format($payroll->overtime_pay,2) }}</td>
        </tr>
        @endif
        <tr class="net-row">
            <td colspan="2">NET SALARY / ប្រាក់បៀវត្សរ៍សុទ្ធត្រូវបើក</td>
            <td class="amount">{{ $sym }}{{ number_format($payroll->net_salary,2) }}</td>
        </tr>
    </tbody>
</table>

{{-- Bakong KHQR Payout Voucher Block --}}
<table style="width:100%; border:1.5px solid #e11d48; border-radius:4px; margin:8px 0; border-collapse:collapse; background:#fff;">
    <tr>
        <td colspan="2" style="background:#e11d48; color:#fff; padding:4px 8px; font-weight:bold; font-size:8pt;">
            <table style="width:100%; color:#fff;">
                <tr>
                    <td style="font-weight:bold; font-size:8pt; padding:0;">
                        BAKONG KHQR DIRECT SALARY PAYOUT / ការទូទាត់ប្រាក់បៀវត្សរ៍តាម KHQR
                    </td>
                    <td style="text-align:right; font-size:7pt; padding:0;">
                        <span style="background:rgba(255,255,255,0.25); padding:1px 6px; border-radius:99px; font-weight:bold;">
                            {{ $payroll->status === 'paid' ? 'PAID' : 'SCAN TO PAY' }}
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding:6px 10px; vertical-align:middle; font-size:8pt; color:#334155;">
            <div style="margin-bottom:2px;">
                <span style="color:#64748b; font-weight:bold;">Beneficiary / ឈ្មោះម្ចាស់គណនី:</span>
                <strong style="color:#0f172a;">{{ $khqr['account_name'] ?? $payroll->teacher->name }}</strong>
            </div>
            <div style="margin-bottom:2px;">
                <span style="color:#64748b; font-weight:bold;">Bank / ធនាគារ:</span>
                <strong>{{ $khqr['bank_name'] ?? ($payroll->teacher->bank_name ?: 'Bakong / ABA / ACLEDA') }}</strong>
                @if(!empty($payroll->teacher->bank_account_number))
                    &nbsp;|&nbsp; <span style="color:#64748b; font-weight:bold;">A/C:</span> <strong>{{ $payroll->teacher->bank_account_number }}</strong>
                @endif
            </div>
            <div style="margin-bottom:2px;">
                <span style="color:#64748b; font-weight:bold;">Bakong ID:</span>
                <strong style="color:#1e40af;">{{ $khqr['bakong_id'] ?? 'N/A' }}</strong>
            </div>
            <div style="margin-top:4px; font-size:8.5pt;">
                <span style="color:#e11d48; font-weight:bold; font-size:10pt;">${{ number_format($payroll->net_salary, 2) }}</span>
                &nbsp;~&nbsp;
                <span style="color:#059669; font-weight:bold;">{{ number_format(($khqr['amount_khr'] ?? round($payroll->net_salary * 4100, -2))) }} ៛</span>
                <span style="font-size:7pt; color:#94a3b8;">(Rate: 1$ = {{ number_format($khqr['khr_rate'] ?? 4100) }} ៛)</span>
            </div>
            <div style="font-size:7pt; color:#94a3b8; margin-top:2px; font-style:italic;">
                Scan with any Cambodian Banking App (ABA, ACLEDA, Wing, Canadia, Sathapana, etc.)
            </div>
        </td>
        <td style="width:100px; text-align:center; padding:6px; vertical-align:middle;">
            @if(!empty($qrBase64))
                <img src="{{ $qrBase64 }}" alt="KHQR" style="width:85px; height:85px; display:block; margin:0 auto; border:1px solid #e2e8f0; padding:2px; background:#fff;">
            @elseif(!empty($khqr['qr_image_url']))
                <img src="{{ $khqr['qr_image_url'] }}" alt="KHQR" style="width:85px; height:85px; display:block; margin:0 auto; border:1px solid #e2e8f0; padding:2px; background:#fff;">
            @endif
            <div style="font-size:6.5pt; font-weight:bold; color:#e11d48; margin-top:2px;">KHQR</div>
        </td>
    </tr>
</table>

{{-- Signatures --}}
<div class="footer">
    <table class="sig-table">
        <tr>
            <td style="width:33.33%;">
                <div class="sig-line"></div>
                <div class="sig-label">Teacher's Signature / ហត្ថលេខាគ្រូ</div>
            </td>
            <td style="width:33.33%;">
                <div class="sig-line"></div>
                <div class="sig-label">Department Head / ប្រធានដេប៉ាតម៉ង់</div>
            </td>
            <td style="width:33.33%;">
                <div class="sig-line"></div>
                <div class="sig-label">HR & Finance / ហិរញ្ញវត្ថុ</div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-note">
    {{ $settings['payroll_note_footer'] ?? 'This payslip is system-generated from NTTI Attendance System.' }}
    | Ref: PAY-{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}
</div>
</body>
</html>
