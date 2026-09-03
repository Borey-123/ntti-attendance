<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>NTTI Official Attendance Slip & Summary - {{ $month }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Kantumruy+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Kantumruy Pro', 'Battambang', sans-serif;
            color: #1f2937;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.5;
        }
        .header-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .kingdom-header {
            font-family: 'Battambang', sans-serif;
            font-weight: bold;
            font-size: 14px;
            color: #1e3a8a;
            margin-bottom: 2px;
        }
        .kingdom-sub {
            font-family: 'Battambang', sans-serif;
            font-size: 12px;
            color: #3b82f6;
            margin-bottom: 15px;
        }
        .inst-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 700;
            color: #1d4ed8;
            margin-top: 5px;
            text-decoration: underline;
            text-underline-offset: 4px;
        }
        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            background: #f8fafc;
            margin-bottom: 20px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 4px 8px;
            vertical-align: middle;
        }
        .fw-bold { font-weight: 700; }
        .text-primary { color: #2563eb; }
        .text-success { color: #16a34a; }
        .text-warning { color: #d97706; }
        .text-danger { color: #dc2626; }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
        }
        table.data-table th {
            background-color: #1e40af;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .score-badge {
            font-size: 16px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .sig-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨️ Print Document / Save PDF</button>

<div class="header-section">
    <div class="kingdom-header">ព្រះរាជាណាចក្រកម្ពុជា</div>
    <div class="kingdom-sub">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
    <div class="inst-title">វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស (NTTI)</div>
    <div class="doc-title">របាយការណ៍វត្តមាន និងម៉ោងបង្រៀនគ្រូ / Monthly Attendance Slip</div>
</div>

<div class="info-card">
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell fw-bold">កាលបរិច្ឆេទ / Month:</div>
            <div class="info-cell text-primary fw-bold">{{ $month }}</div>
            <div class="info-cell fw-bold">ដេប៉ាតឺម៉ង់ / Department:</div>
            <div class="info-cell">{{ $selectedDept ? $selectedDept->name : 'All Departments' }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell fw-bold">គ្រូបង្រៀន / Teacher:</div>
            <div class="info-cell text-dark fw-bold">{{ $selectedTeacher ? ($selectedTeacher->name_kh ?: $selectedTeacher->name) . ' (' . $selectedTeacher->employee_id . ')' : 'All Staff Members' }}</div>
            <div class="info-cell fw-bold">ថ្ងៃសរុប / Total Days:</div>
            <div class="info-cell">{{ count($attendances) }}</div>
        </div>
    </div>
</div>

@if($scorecard)
<div style="margin-bottom: 20px; border: 1px solid #93c5fd; background: #eff6ff; padding: 12px; border-radius: 8px;">
    <div style="display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: middle;">
            <span style="font-size: 14px; font-weight: bold; color: #1e40af;">ពិន្ទុវិន័យ និងម៉ោងធ្វើការ / Performance Scorecard:</span>
        </div>
        <div style="display: table-cell; text-align: right;">
            <span class="score-badge bg-primary text-white" style="background-color: #2563eb; color: #fff;">
                Grade {{ $scorecard['grade'] }} ({{ $scorecard['score'] }}%)
            </span>
        </div>
    </div>
    <div style="display: table; width: 100%; margin-top: 10px; text-align: center; font-size: 11px;">
        <div style="display: table-cell;">ម៉ោងបង្រៀនសរុប: <b>{{ $scorecard['total_worked_hours'] }}h</b></div>
        <div style="display: table-cell;">ម៉ោងកំណត់: <b>{{ $scorecard['scheduled_hours'] }}h</b></div>
        <div style="display: table-cell; color: #16a34a;">ម៉ោងបន្ថែម (Overtime): <b>+{{ $scorecard['overtime_hours'] }}h</b></div>
        <div style="display: table-cell; color: #d97706;">វត្តមាន: <b>{{ $scorecard['present_days'] }} ថ្ងៃ</b></div>
        <div style="display: table-cell; color: #dc2626;">យឺត: <b>{{ $scorecard['late_days'] }} ថ្ងៃ</b></div>
    </div>
</div>
@endif

<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>កាលបរិច្ឆេទ (Date)</th>
            <th>ឈ្មោះគ្រូ (Teacher)</th>
            <th>ដេប៉ាតឺម៉ង់</th>
            <th>វេនព្រឹក (Morning)</th>
            <th>វេនរសៀល (Afternoon)</th>
            <th>ម៉ោងសរុប (Hours)</th>
            <th>ស្ថានភាព (Status)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $index => $att)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $att->date ? ($att->date instanceof \Carbon\Carbon ? $att->date->format('Y-m-d (D)') : \Carbon\Carbon::parse($att->date)->format('Y-m-d (D)')) : '-' }}</td>
                <td style="text-align: left; font-weight: bold;">
                    {{ $att->teacher ? ($att->teacher->name_kh ?: $att->teacher->name) : 'N/A' }}
                </td>
                <td>{{ $att->teacher ? $att->teacher->department : '-' }}</td>
                <td>{{ $att->morning_in ? substr($att->morning_in, 0, 5) . ' - ' . substr($att->morning_out ?? '--:--', 0, 5) : '—' }}</td>
                <td>{{ $att->afternoon_in ? substr($att->afternoon_in, 0, 5) . ' - ' . substr($att->afternoon_out ?? '--:--', 0, 5) : '—' }}</td>
                <td style="font-weight: bold;">
                    @php
                        $m = 0;
                        try {
                            if(!empty($att->morning_in) && !empty($att->morning_out)) {
                                $m += \Carbon\Carbon::parse($att->morning_in)->diffInMinutes(\Carbon\Carbon::parse($att->morning_out));
                            }
                            if(!empty($att->afternoon_in) && !empty($att->afternoon_out)) {
                                $m += \Carbon\Carbon::parse($att->afternoon_in)->diffInMinutes(\Carbon\Carbon::parse($att->afternoon_out));
                            }
                        } catch (\Throwable $e) {}
                    @endphp
                    {{ $m > 0 ? round($m/60, 1) . 'h' : '—' }}
                </td>
                <td>
                    @if($att->morning_status === 'late' || $att->afternoon_status === 'late')
                        <span class="text-warning fw-bold">យឺត (Late)</span>
                    @elseif($att->morning_status === 'present' || $att->afternoon_status === 'present')
                        <span class="text-success fw-bold">វត្តមាន (Present)</span>
                    @else
                        <span class="text-danger fw-bold">អវត្តមាន (Absent)</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="padding: 20px; text-align: center; color: #94a3b8;">គ្មានទិន្នន័យវត្តមានសម្រាប់ខែនេះទេ</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="signature-section">
    <div class="sig-box">
        <p class="fw-bold">បានឃើញ និងបញ្ជាក់ដោយប្រធានដេប៉ាតឺម៉ង់</p>
        <p style="font-size: 11px; color: #64748b;">Head of Department Signature</p>
        <br><br><br>
        <p>....................................................</p>
    </div>
    <div class="sig-box">
        <p class="fw-bold">រាជធានីភ្នំពេញ, ថ្ងៃទី....... ខែ....... ឆ្នាំ២០២...</p>
        <p class="fw-bold">ហត្ថលេខា និងត្រាក្រុមការងារ</p>
        <br><br><br>
        <p>....................................................</p>
    </div>
</div>

</body>
</html>
