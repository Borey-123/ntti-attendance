<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip — {{ $payroll->teacher->name }} — {{ $payroll->month->format('F Y') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&family=Moul&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 portrait; margin: 15mm 20mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Battambang', 'Inter', sans-serif; color: #0f172a; background: #fff; font-size: 10.5pt; }

        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 16px; }
        .motto { font-family: 'Moul', cursive; font-size: 12pt; color: #1e3a8a; margin-bottom: 2px; }
        .school-name { font-family: 'Battambang', sans-serif; font-size: 13pt; font-weight: 900; color: #1e3a8a; }
        .doc-title { font-size: 15pt; font-weight: 900; color: #1e40af; margin: 10px 0 4px; letter-spacing: 1px; text-transform: uppercase; }
        .doc-sub { font-size: 9pt; color: #64748b; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin-bottom: 16px; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; }
        .info-row { display: flex; gap: 6px; }
        .info-label { font-weight: 700; font-size: 9pt; color: #64748b; min-width: 100px; }
        .info-value { font-size: 9pt; color: #0f172a; }

        .section-title { font-size: 10pt; font-weight: 900; color: #1e3a8a; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin: 14px 0 8px; text-transform: uppercase; letter-spacing: 0.5px; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a8a; color: #fff; padding: 6px 10px; font-size: 8.5pt; font-weight: 700; text-align: left; }
        td { padding: 5px 10px; font-size: 9pt; border-bottom: 1px solid #f1f5f9; }
        tr:nth-child(even) td { background: #f8fafc; }

        .amount { text-align: right; font-weight: 700; }
        .deduct { color: #dc2626; }
        .bonus-col { color: #059669; }

        .net-row td { background: #1e3a8a !important; color: #fff !important; font-size: 11pt; font-weight: 900; padding: 10px 10px; }

        .attendance-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin-bottom: 14px; }
        .att-box { text-align: center; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; }
        .att-num { font-size: 18pt; font-weight: 900; }
        .att-label { font-size: 7.5pt; color: #64748b; text-transform: uppercase; font-weight: 700; }

        .progress-bar { width: 100%; height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; margin-top: 4px; }
        .progress-fill { height: 100%; border-radius: 99px; }

        .footer { margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
        .sig-box { text-align: center; }
        .sig-line { width: 120px; border-bottom: 1px solid #94a3b8; margin: 40px auto 4px; }
        .sig-label { font-size: 8pt; color: #64748b; }
        .footer-note { font-size: 7.5pt; color: #94a3b8; text-align: center; margin-top: 10px; font-style: italic; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 8pt; font-weight: 800; text-transform: uppercase; }
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
    <div class="motto">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
    <div class="school-name">{{ \App\Models\Setting::getValue('university_name', 'National Technical Training Institute') }}</div>
    <div class="doc-title">Salary Payslip / បន្ទាត់ប្រាក់ខែ</div>
    <div class="doc-sub">
        {{ $payroll->month->format('F Y') }} &nbsp;|&nbsp;
        Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp;
        <span class="status-badge status-{{ $payroll->status }}">{{ $payroll->status }}</span>
    </div>
</div>

{{-- Teacher Info --}}
<div class="info-grid">
    <div class="info-row"><span class="info-label">Name:</span><span class="info-value"><strong>{{ $payroll->teacher->name }}</strong></span></div>
    <div class="info-row"><span class="info-label">ឈ្មោះខ្មែរ:</span><span class="info-value">{{ $payroll->teacher->name_kh ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Employee ID:</span><span class="info-value">{{ $payroll->teacher->employee_id }}</span></div>
    <div class="info-row"><span class="info-label">Department:</span><span class="info-value">{{ $payroll->teacher->department ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Position:</span><span class="info-value">{{ $payroll->teacher->position ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Rank:</span><span class="info-value">{{ $payroll->teacher->position_rank ?? '—' }}</span></div>
    @if($payroll->academicPeriod)
    <div class="info-row"><span class="info-label">Academic Period:</span><span class="info-value">{{ $payroll->academicPeriod->display_name }}</span></div>
    @endif
    @if($payroll->approved_at)
    <div class="info-row"><span class="info-label">Approved:</span><span class="info-value">{{ $payroll->approved_at->format('d M Y') }} by {{ $payroll->approvedByUser->name ?? 'Admin' }}</span></div>
    @endif
</div>

{{-- Attendance Summary --}}
<div class="section-title">📋 Attendance Summary</div>
<div class="attendance-grid">
    <div class="att-box">
        <div class="att-num" style="color:#059669;">{{ $payroll->present_days }}</div>
        <div class="att-label">Present Days</div>
    </div>
    <div class="att-box">
        <div class="att-num" style="color:#dc2626;">{{ $payroll->absent_days }}</div>
        <div class="att-label">Absent Days</div>
    </div>
    <div class="att-box">
        <div class="att-num" style="color:#d97706;">{{ $payroll->late_count }}</div>
        <div class="att-label">Late Times</div>
    </div>
</div>
<div style="margin-bottom:14px; font-size:9pt; color:#64748b;">
    Working Days: <strong>{{ $payroll->working_days }}</strong> &nbsp;|&nbsp;
    Attendance Rate: <strong>{{ $payroll->attendance_rate }}%</strong> &nbsp;|&nbsp;
    Late Duration: <strong>{{ $payroll->late_minutes_total }} min</strong>
    @if($payroll->approved_leave_days > 0)
        &nbsp;|&nbsp; Approved Leave (Paid): <strong>{{ $payroll->approved_leave_days }} days</strong>
    @endif
</div>

{{-- Salary Breakdown --}}
<div class="section-title">💰 Salary Calculation</div>
<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Details</th>
            <th class="amount">Amount ({{ $settings['currency'] ?? 'USD' }})</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Base Salary</td>
            <td style="color:#64748b; font-size:8.5pt;">Monthly base</td>
            <td class="amount">{{ $sym }}{{ number_format($payroll->base_salary,2) }}</td>
        </tr>
        <tr>
            <td>Daily Rate</td>
            <td style="color:#64748b; font-size:8.5pt;">{{ $sym }}{{ number_format($payroll->base_salary,2) }} ÷ {{ $payroll->working_days }} days</td>
            <td class="amount" style="color:#64748b;">{{ $sym }}{{ number_format($payroll->daily_rate,4) }}/day</td>
        </tr>
        <tr>
            <td>Gross Salary</td>
            <td style="color:#64748b; font-size:8.5pt;">{{ $payroll->present_days + $payroll->approved_leave_days }} effective days × {{ $sym }}{{ number_format($payroll->daily_rate,2) }}</td>
            <td class="amount" style="color:#4338ca; font-weight:800;">{{ $sym }}{{ number_format($payroll->gross_salary,2) }}</td>
        </tr>
        @if($payroll->late_deduction > 0)
        <tr>
            <td class="deduct">Late Deduction</td>
            <td style="color:#64748b; font-size:8.5pt;">{{ $payroll->late_minutes_total }} min × {{ $sym }}{{ $settings['late_deduction_per_minute'] ?? '0.50' }}</td>
            <td class="amount deduct">-{{ $sym }}{{ number_format($payroll->late_deduction,2) }}</td>
        </tr>
        @endif
        @if($payroll->absent_deduction > 0)
        <tr>
            <td class="deduct">Absent Deduction</td>
            <td style="color:#64748b; font-size:8.5pt;">{{ $payroll->absent_days }} days × {{ $sym }}{{ number_format($payroll->daily_rate,2) }}</td>
            <td class="amount deduct">-{{ $sym }}{{ number_format($payroll->absent_deduction,2) }}</td>
        </tr>
        @endif
        @if($payroll->bonus > 0)
        <tr>
            <td class="bonus-col">Bonus</td>
            <td></td>
            <td class="amount bonus-col">+{{ $sym }}{{ number_format($payroll->bonus,2) }}</td>
        </tr>
        @endif
        @if($payroll->overtime_pay > 0)
        <tr>
            <td class="bonus-col">Overtime Pay</td>
            <td style="color:#64748b; font-size:8.5pt;">{{ $payroll->overtime_hours }} hrs</td>
            <td class="amount bonus-col">+{{ $sym }}{{ number_format($payroll->overtime_pay,2) }}</td>
        </tr>
        @endif
        <tr class="net-row">
            <td colspan="2">💰 NET SALARY / ប្រាក់ខែសុទ្ធ</td>
            <td class="amount">{{ $sym }}{{ number_format($payroll->net_salary,2) }}</td>
        </tr>
    </tbody>
</table>

{{-- Signature --}}
<div class="footer">
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">Teacher's Signature / ហត្ថលេខាគ្រូ</div>
    </div>
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">Department Head / ប្រធានដេប៉ាតម៉ង់</div>
    </div>
    <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-label">HR / Finance / ហិរញ្ញវត្ថុ</div>
    </div>
</div>

<div class="footer-note">
    {{ $settings['payroll_note_footer'] ?? 'This payslip is system-generated from NTTI Attendance System.' }}
    | Ref: PAY-{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}
</div>
</body>
</html>
