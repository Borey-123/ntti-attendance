<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Official Attendance Slip') }} - {{ $teacher->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&family=Moul&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Battambang', 'Inter', sans-serif;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            font-size: 11pt;
            line-height: 1.4;
        }
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border-radius: 12px;
            padding: 30px 40px;
            position: relative;
        }
        /* Top Action Bar for screen */
        .slip-action-bar {
            max-width: 800px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary { background: #1a73e8; color: #fff; }
        .btn-primary:hover { background: #1557b0; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }

        /* Cambodia Official Header */
        .official-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px double #1a73e8;
            padding-bottom: 15px;
        }
        .cambodia-motto {
            font-family: 'Moul', 'Battambang', cursive;
            font-size: 14pt;
            color: #1e3a8a;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .cambodia-sub {
            font-family: 'Moul', 'Battambang', cursive;
            font-size: 11pt;
            color: #1e3a8a;
            margin: 3px 0 12px;
        }
        .ministry-block {
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            margin-top: 10px;
        }
        .inst-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 50%;
        }
        .inst-names {
            flex: 1;
            margin-left: 15px;
        }
        .inst-names h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: 700;
            color: #334155;
        }
        .inst-names h2 {
            margin: 2px 0 0;
            font-size: 13pt;
            font-weight: 900;
            color: #1a73e8;
        }
        .doc-title-block {
            text-align: center;
            margin: 18px 0 15px;
        }
        .doc-title {
            font-family: 'Moul', 'Battambang', cursive;
            font-size: 14pt;
            color: #0f172a;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 10pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }

        /* Teacher Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 20px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 10.5pt;
        }
        .info-item {
            display: flex;
        }
        .info-label {
            width: 140px;
            font-weight: 700;
            color: #475569;
        }
        .info-val {
            font-weight: 800;
            color: #0f172a;
        }

        /* Summary Stats Cards */
        .stats-summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 18px;
            text-align: center;
        }
        .stat-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 4px;
            background: #fff;
        }
        .stat-box .val {
            font-size: 14pt;
            font-weight: 900;
            line-height: 1.1;
        }
        .stat-box .lbl {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 3px;
        }

        /* Table */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 25px;
        }
        .attendance-table th {
            background: #1a73e8;
            color: #ffffff;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-weight: 800;
            text-align: center;
        }
        .attendance-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .attendance-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .tc { text-align: center; }
        .badge-present { color: #16a34a; font-weight: 800; }
        .badge-late { color: #d97706; font-weight: 800; }
        .badge-absent { color: #dc2626; font-weight: 800; }

        /* Verification QR & Signatures */
        .footer-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            margin-top: 25px;
            padding-top: 15px;
            text-align: center;
        }
        .sig-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 120px;
        }
        .sig-title {
            font-size: 9.5pt;
            font-weight: 800;
            color: #1e293b;
        }
        .sig-line {
            width: 80%;
            border-bottom: 1px dotted #94a3b8;
            margin-top: 60px;
        }
        .sig-name {
            font-size: 9pt;
            font-weight: 700;
            color: #475569;
            margin-top: 4px;
        }
        .qr-stamp-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .slip-action-bar { display: none !important; }
            .slip-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <div class="slip-action-bar">
        <a href="{{ route('portal.index') }}" class="btn-action btn-secondary">
            <i class="ph ph-arrow-left"></i> {{ __('Back to Portal') }}
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-action btn-primary">
                <i class="ph ph-printer"></i> {{ __('Print Slip') }}
            </button>
        </div>
    </div>

    <div class="slip-container">
        {{-- Kingdom of Cambodia Crest --}}
        <div class="official-header">
            <div class="cambodia-motto">ព្រះរាជាណាចក្រកម្ពុជា</div>
            <div class="cambodia-sub">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>

            <div class="ministry-block">
                <div style="display: flex; align-items: center;">
                    @if($uLogo)
                        <img src="{{ $uLogo }}" class="inst-logo" alt="Logo">
                    @endif
                    <div class="inst-names">
                        <h3>ក្រសួងការងារ និងបណ្តុះបណ្តាលវិជ្ជាជីវៈ</h3>
                        <h2>{{ $uName }}</h2>
                    </div>
                </div>
                <div style="text-align: right; font-size: 8.5pt; color: #475569;">
                    <div>{{ __('Academic Year') }}: <strong>{{ $academicYear }}</strong></div>
                    <div>{{ __('Semester') }}: <strong>{{ $academicSemester }}</strong></div>
                    <div>{{ __('Issued Date') }}: {{ now()->format('d-m-Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Document Title --}}
        <div class="doc-title-block">
            <h1 class="doc-title">ប័ណ្ណវត្តមាន និងម៉ោងបង្រៀនប្រចាំខែ</h1>
            <div class="doc-subtitle">OFFICIAL MONTHLY ATTENDANCE & TEACHING HOURS VOUCHER</div>
        </div>

        {{-- Teacher Details --}}
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">{{ __('Teacher Name') }}:</span>
                <span class="info-val">{{ $teacher->name_kh ? $teacher->name_kh . ' (' . $teacher->name . ')' : $teacher->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('Employee ID') }}:</span>
                <span class="info-val">{{ $teacher->employee_id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('Department') }}:</span>
                <span class="info-val">{{ $teacher->department }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('Evaluation Period') }}:</span>
                <span class="info-val" style="color: #1a73e8;">{{ $targetDate->format('F Y') }}</span>
            </div>
        </div>

        {{-- KPI Stat Boxes --}}
        <div class="stats-summary-grid">
            <div class="stat-box">
                <div class="val" style="color: #1a73e8;">{{ $totalWorkingDays }}</div>
                <div class="lbl">{{ __('Working Days') }}</div>
            </div>
            <div class="stat-box">
                <div class="val" style="color: #16a34a;">{{ $totalPresent }}</div>
                <div class="lbl">{{ __('Present Days') }}</div>
            </div>
            <div class="stat-box">
                <div class="val" style="color: #d97706;">{{ $totalLate }}</div>
                <div class="lbl">{{ __('Late Days') }}</div>
            </div>
            <div class="stat-box">
                <div class="val" style="color: #dc2626;">{{ $totalAbsent }}</div>
                <div class="lbl">{{ __('Absent Days') }}</div>
            </div>
            <div class="stat-box" style="background: #eff6ff; border-color: #93c5fd;">
                <div class="val" style="color: #1e40af;">{{ $actualHours }}<span style="font-size: 9pt;">h</span></div>
                <div class="lbl">{{ __('Worked Hours') }}</div>
            </div>
            <div class="stat-box" style="background: #fef3c7; border-color: #fcd34d;">
                <div class="val" style="color: #b45309;">{{ $overtimeHours }}<span style="font-size: 9pt;">h</span></div>
                <div class="lbl">{{ __('Overtime') }}</div>
            </div>
        </div>

        {{-- Detailed Records Table --}}
        <table class="attendance-table">
            <thead>
                <tr>
                    <th width="35">#</th>
                    <th width="85">{{ __('Date') }}</th>
                    <th width="65">{{ __('Day') }}</th>
                    <th>{{ __('Morning Session') }}</th>
                    <th>{{ __('Afternoon Session') }}</th>
                    <th width="85">{{ __('Worked') }}</th>
                    <th width="80">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $idx => $r)
                    @php
                        $d = \Carbon\Carbon::parse($r->date);
                        $mText = $r->morning_in ? substr($r->morning_in, 0, 5) . ($r->morning_out ? ' - ' . substr($r->morning_out, 0, 5) : ' (In)') : '—';
                        $aText = $r->afternoon_in ? substr($r->afternoon_in, 0, 5) . ($r->afternoon_out ? ' - ' . substr($r->afternoon_out, 0, 5) : ' (In)') : '—';
                        $dayMins = 0;
                        if ($r->morning_in && $r->morning_out) {
                            $dayMins += \Carbon\Carbon::createFromTimeString($r->morning_in)->diffInMinutes(\Carbon\Carbon::createFromTimeString($r->morning_out));
                        }
                        if ($r->afternoon_in && $r->afternoon_out) {
                            $dayMins += \Carbon\Carbon::createFromTimeString($r->afternoon_in)->diffInMinutes(\Carbon\Carbon::createFromTimeString($r->afternoon_out));
                        }
                        $dayHours = $dayMins > 0 ? floor($dayMins / 60) . 'h ' . ($dayMins % 60) . 'm' : '—';
                        $statusLabel = __('Present');
                        $statusClass = 'badge-present';
                        if ($r->morning_status === 'late' || $r->afternoon_status === 'late') {
                            $statusLabel = __('Late');
                            $statusClass = 'badge-late';
                        } elseif (!$r->morning_in && !$r->afternoon_in) {
                            $statusLabel = __('Absent');
                            $statusClass = 'badge-absent';
                        }
                    @endphp
                    <tr>
                        <td class="tc">{{ $idx + 1 }}</td>
                        <td class="tc">{{ $d->format('d/m/Y') }}</td>
                        <td class="tc">{{ $d->format('D') }}</td>
                        <td class="tc">{{ $mText }}</td>
                        <td class="tc">{{ $aText }}</td>
                        <td class="tc" style="font-weight: 700;">{{ $dayHours }}</td>
                        <td class="tc {{ $statusClass }}">{{ $statusLabel }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="tc" style="padding: 20px; color: #64748b;">
                            {{ __('No attendance records found for this period.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Verification Block & Signatures --}}
        <div class="footer-signatures">
            <div class="sig-col">
                <div class="sig-title">{{ __('Lecturer Signature') }}</div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $teacher->name_kh ?: $teacher->name }}</div>
            </div>

            <div class="sig-col qr-stamp-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode(url('/portal/slip?month=' . $month . '&year=' . $year . '&id=' . $teacher->employee_id)) }}" style="width: 75px; height: 75px; border: 1px solid #cbd5e1; padding: 2px; border-radius: 4px;" alt="Verification QR">
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 4px; font-weight: 700;">
                    CODE: NTTI-{{ strtoupper(substr(md5($teacher->id . $month . $year), 0, 8)) }}
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-title">{{ __('Faculty Dean / Academic Affairs') }}</div>
                <div class="sig-line"></div>
                <div class="sig-name">វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស</div>
            </div>
        </div>
    </div>

</body>
</html>
