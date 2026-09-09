<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ប័ណ្ណបើកប្រាក់បៀវត្សរ៍ — {{ $payroll->teacher->name_kh ?: $payroll->teacher->name }} — {{ $payroll->month->format('F Y') }}</title>
    
    <!-- Google Fonts for 100% Perfect Native Khmer Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&family=Moul&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #2563eb;
            --danger: #dc2626;
            --success: #059669;
            --warning: #d97706;
            --border: #e2e8f0;
            --bg-muted: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Kantumruy Pro', 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            color: var(--text-dark);
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ── Floating Print Header Bar ── */
        .print-actions-bar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .bar-title {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .bar-btns {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
            font-family: inherit;
        }

        .btn-print {
            background: #10b981;
            color: #fff;
        }
        .btn-print:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-download-pdf {
            background: #6366f1;
            color: #fff;
        }
        .btn-download-pdf:hover {
            background: #4f46e5;
        }

        .btn-back {
            background: rgba(255,255,255,0.12);
            color: #f1f5f9;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.22);
        }

        /* ── A4 Page Container ── */
        .sheet-container {
            display: flex;
            justify-content: center;
            padding: 2rem 1rem 4rem;
        }

        .payslip-sheet {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            padding: 14mm 16mm;
            border-radius: 6px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            position: relative;
        }

        /* ── Slip Typography & Content ── */
        .motto {
            font-family: 'Moul', serif;
            font-size: 11pt;
            color: var(--primary);
            text-align: center;
            line-height: 1.6;
        }

        .motto-sub {
            font-family: 'Moul', serif;
            font-size: 10pt;
            color: var(--primary);
            text-align: center;
            margin-bottom: 4px;
        }

        .header-divider {
            height: 2px;
            background: var(--primary);
            margin: 6px 0 10px;
        }

        .school-name {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
        }

        .doc-title {
            font-size: 13.5pt;
            font-weight: 800;
            color: var(--primary-light);
            text-align: center;
            margin: 6px 0 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-meta {
            text-align: center;
            font-size: 8.5pt;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-draft    { background: #fef3c7; color: #92400e; }
        .status-approved { background: #ede9fe; color: #4c1d95; }
        .status-paid     { background: #d1fae5; color: #064e3b; }

        /* ── Info Table ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: var(--bg-muted);
            border: 1px solid var(--border);
            border-radius: 6px;
        }
        .info-table td {
            padding: 4px 10px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 20%;
        }
        .info-val {
            font-weight: 600;
            color: var(--text-dark);
            width: 30%;
        }

        /* ── Sections ── */
        .section-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 9.5pt;
            font-weight: 700;
            color: var(--primary);
            border-bottom: 1.5px solid var(--border);
            padding-bottom: 3px;
            margin: 10px 0 6px;
            text-transform: uppercase;
        }
        .sec-box-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 2px;
        }

        /* ── Attendance Cards ── */
        .att-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 6px;
        }
        .att-card {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 6px;
            text-align: center;
            background: #fff;
        }
        .att-card-num {
            font-size: 15pt;
            font-weight: 800;
            line-height: 1.1;
        }
        .att-card-label {
            font-size: 7.5pt;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 2px;
        }

        /* ── Salary Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }
        .data-table th {
            background: var(--primary);
            color: #fff;
            padding: 5px 8px;
            font-weight: 700;
            text-align: left;
        }
        .data-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .data-table tr:nth-child(even) td {
            background: var(--bg-muted);
        }
        .amount {
            text-align: right;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .deduct { color: var(--danger); }
        .bonus-col { color: var(--success); }

        .net-row td {
            background: var(--primary) !important;
            color: #fff !important;
            font-size: 10pt;
            font-weight: 800;
            padding: 6px 8px;
        }

        /* ── KHQR Voucher ── */
        .khqr-voucher {
            border: 1.5px solid #e11d48;
            border-radius: 6px;
            margin: 8px 0;
            background: #fff;
            overflow: hidden;
        }
        .khqr-header {
            background: #e11d48;
            color: #fff;
            padding: 4px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8pt;
            font-weight: 700;
        }
        .khqr-body {
            display: grid;
            grid-template-columns: 1fr 100px;
            gap: 12px;
            padding: 8px 12px;
            align-items: center;
        }
        .khqr-info-line {
            font-size: 8pt;
            margin-bottom: 3px;
        }
        .khqr-price {
            margin-top: 4px;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        /* ── Signatures & Footer ── */
        .sig-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            text-align: center;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }
        .sig-line {
            width: 120px;
            border-bottom: 1px solid #94a3b8;
            margin: 28px auto 4px;
        }
        .sig-label {
            font-size: 8pt;
            color: var(--text-muted);
            font-weight: 600;
        }

        .footer-note {
            text-align: center;
            font-size: 7.5pt;
            color: var(--text-muted);
            margin-top: 10px;
        }

        /* ── Print Media Query ── */
        @media print {
            body {
                background: #ffffff !important;
            }
            .no-print, .print-actions-bar {
                display: none !important;
            }
            .sheet-container {
                padding: 0 !important;
            }
            .payslip-sheet {
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
        }
    </style>
</head>
<body>

@php
    $sym = $settings['currency_symbol'] ?? '$';
    $totalDeduct = $payroll->late_deduction + $payroll->absent_deduction;
    $teacherName = $payroll->teacher->name_kh ?: $payroll->teacher->name;
@endphp

<!-- Top Floating Action Bar -->
<div class="print-actions-bar no-print">
    <div class="bar-title">
        <i class="bi bi-file-earmark-text" style="font-size:1.25rem; color:#38bdf8;"></i>
        <span>ប័ណ្ណបើកប្រាក់បៀវត្សរ៍ (Payslip Preview) — {{ $teacherName }}</span>
    </div>
    <div class="bar-btns">
        <button onclick="window.print()" class="btn-action btn-print">
            <i class="bi bi-printer-fill"></i> បោះពុម្ព ឬ រក្សាទុកជា PDF (Print / Save PDF)
        </button>
        <a href="{{ route('payroll.pdf', $payroll->id) }}" class="btn-action btn-download-pdf">
            <i class="bi bi-download"></i> ទាញយក Server PDF
        </a>
        <a href="{{ route('payroll.show', $payroll->id) }}" class="btn-action btn-back">
            <i class="bi bi-arrow-left"></i> ត្រឡប់ក្រោយ
        </a>
    </div>
</div>

<div class="sheet-container">
    <div class="payslip-sheet">
        {{-- National Motto --}}
        <div class="motto">ព្រះរាជាណាចក្រកម្ពុជា</div>
        <div class="motto-sub">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
        <div class="school-name">{{ \App\Models\Setting::getValue('university_name', 'វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស (NTTI)') }}</div>
        <div class="header-divider"></div>

        <div class="doc-title">Salary Payslip / ប័ណ្ណបើកប្រាក់បៀវត្សរ៍</div>
        <div class="doc-meta">
            ខែ: <strong>{{ $payroll->month->format('F Y') }}</strong> &nbsp;|&nbsp;
            កាលបរិច្ឆេទបង្កើត: {{ now()->format('d M Y, H:i') }} &nbsp;|&nbsp;
            ស្ថានភាព: <span class="badge-status status-{{ $payroll->status }}">{{ strtoupper($payroll->status) }}</span>
        </div>

        {{-- Teacher Details --}}
        <table class="info-table">
            <tr>
                <td class="info-label">ឈ្មោះជាខ្មែរ:</td>
                <td class="info-val">{{ $payroll->teacher->name_kh ?? '—' }}</td>
                <td class="info-label">Name (English):</td>
                <td class="info-val">{{ $payroll->teacher->name }}</td>
            </tr>
            <tr>
                <td class="info-label">អត្តលេខគ្រូ (ID):</td>
                <td class="info-val">{{ $payroll->teacher->employee_id }}</td>
                <td class="info-label">ដេប៉ាតឺម៉ង់:</td>
                <td class="info-val">{{ $payroll->teacher->department ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-label">តួនាទី / មុខតំណែង:</td>
                <td class="info-val">{{ $payroll->teacher->position ?? '—' }}</td>
                <td class="info-label">កម្រិត / Rank:</td>
                <td class="info-val">{{ $payroll->teacher->position_rank ?? '—' }}</td>
            </tr>
            @if($payroll->academicPeriod)
            <tr>
                <td class="info-label">ឆមាស / ឆ្នាំសិក្សា:</td>
                <td class="info-val" colspan="3">{{ $payroll->academicPeriod->display_name }}</td>
            </tr>
            @endif
            @if($payroll->approved_at)
            <tr>
                <td class="info-label">អនុម័តនៅថ្ងៃ:</td>
                <td class="info-val" colspan="3">{{ $payroll->approved_at->format('d M Y') }} ដោយ {{ $payroll->approvedByUser->name ?? 'Admin' }}</td>
            </tr>
            @endif
        </table>

        {{-- Attendance Summary --}}
        <div class="section-header">
            <span class="sec-box-dot"></span>
            <span>Attendance Summary / សង្ខេបវត្តមាន</span>
        </div>
        <div class="att-grid">
            <div class="att-card">
                <div class="att-card-num" style="color:var(--success);">{{ $payroll->present_days }}</div>
                <div class="att-card-label">ថ្ងៃមានវត្តមាន (Present Days)</div>
            </div>
            <div class="att-card">
                <div class="att-card-num" style="color:var(--danger);">{{ $payroll->absent_days }}</div>
                <div class="att-card-label">ថ្ងៃអវត្តមាន (Absent Days)</div>
            </div>
            <div class="att-card">
                <div class="att-card-num" style="color:var(--warning);">{{ $payroll->late_count }}</div>
                <div class="att-card-label">មកយឺត ({{ $payroll->late_minutes_total }} នាទី)</div>
            </div>
        </div>
        <div style="font-size:8pt; color:var(--text-muted); margin-bottom:8px;">
            ថ្ងៃធ្វើការសរុប: <strong>{{ $payroll->working_days }} ថ្ងៃ</strong> &nbsp;|&nbsp;
            អត្រាវត្តមាន: <strong>{{ $payroll->attendance_rate }}%</strong>
            @if($payroll->approved_leave_days > 0)
                &nbsp;|&nbsp; ច្បាប់អនុញ្ញាត: <strong>{{ $payroll->approved_leave_days }} ថ្ងៃ</strong>
            @endif
        </div>

        {{-- Salary Breakdown --}}
        <div class="section-header">
            <span class="sec-box-dot"></span>
            <span>Salary Calculation / ការគណនាប្រាក់បៀវត្សរ៍</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ការពិពណ៌នា / Description</th>
                    <th>ព័ត៌មានលម្អិត / Details</th>
                    <th class="amount">ទឹកប្រាក់ / Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ប្រាក់ខែមូលដ្ឋាន (Base Salary)</td>
                    <td style="color:var(--text-muted);">ប្រចាំខែ</td>
                    <td class="amount">{{ $sym }}{{ number_format($payroll->base_salary, 2) }}</td>
                </tr>
                <tr>
                    <td>តម្លៃប្រចាំថ្ងៃ (Daily Rate)</td>
                    <td style="color:var(--text-muted);">{{ $sym }}{{ number_format($payroll->base_salary,2) }} &divide; {{ $payroll->working_days }} ថ្ងៃ</td>
                    <td class="amount">{{ $sym }}{{ number_format($payroll->daily_rate, 2) }}</td>
                </tr>
                <tr>
                    <td>ប្រាក់ចំណូលសរុប (Gross Salary)</td>
                    <td style="color:var(--text-muted);">{{ $payroll->effective_worked_days }} ថ្ងៃជាក់ស្តែង &times; {{ $sym }}{{ number_format($payroll->daily_rate,2) }}</td>
                    <td class="amount">{{ $sym }}{{ number_format($payroll->gross_salary, 2) }}</td>
                </tr>
                @if($payroll->late_deduction > 0)
                <tr>
                    <td class="deduct">កាត់ពេលមកយឺត (Late Deduction)</td>
                    <td style="color:var(--text-muted);">{{ $payroll->late_minutes_total }} នាទី &times; {{ $sym }}{{ number_format($settings['late_deduction_rate_per_min'] ?? 0.05, 2) }}</td>
                    <td class="amount deduct">-{{ $sym }}{{ number_format($payroll->late_deduction, 2) }}</td>
                </tr>
                @endif
                @if($payroll->absent_deduction > 0)
                <tr>
                    <td class="deduct">កាត់ពេលអវត្តមាន (Absent Deduction)</td>
                    <td style="color:var(--text-muted);">{{ $payroll->absent_days }} ថ្ងៃ &times; {{ $sym }}{{ number_format($payroll->daily_rate,2) }}</td>
                    <td class="amount deduct">-{{ $sym }}{{ number_format($payroll->absent_deduction, 2) }}</td>
                </tr>
                @endif
                @if($payroll->bonus > 0)
                <tr>
                    <td class="bonus-col">ប្រាក់រង្វាន់លើកទឹកចិត្ត (Bonus)</td>
                    <td style="color:var(--text-muted);">ការលើកទឹកចិត្តបន្ថែម</td>
                    <td class="amount bonus-col">+{{ $sym }}{{ number_format($payroll->bonus, 2) }}</td>
                </tr>
                @endif
                @if($payroll->overtime_pay > 0)
                <tr>
                    <td class="bonus-col">ប្រាក់បង្រៀនបន្ថែមម៉ោង (Overtime Pay)</td>
                    <td style="color:var(--text-muted);">{{ $payroll->overtime_hours }} ម៉ោង</td>
                    <td class="amount bonus-col">+{{ $sym }}{{ number_format($payroll->overtime_pay, 2) }}</td>
                </tr>
                @endif
                <tr class="net-row">
                    <td colspan="2">ប្រាក់បៀវត្សរ៍សុទ្ធត្រូវបើក (NET SALARY)</td>
                    <td class="amount">{{ $sym }}{{ number_format($payroll->net_salary, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Bakong KHQR Block --}}
        <div class="khqr-voucher">
            <div class="khqr-header">
                <span>🇰🇭 BAKONG KHQR DIRECT SALARY PAYOUT / ការទូទាត់ប្រាក់បៀវត្សរ៍តាម KHQR</span>
                <span style="background:rgba(255,255,255,0.2); padding:1px 6px; border-radius:99px;">
                    {{ $payroll->status === 'paid' ? 'ទូទាត់រួច (PAID)' : 'ស្កេនទូទាត់ (SCAN TO PAY)' }}
                </span>
            </div>
            <div class="khqr-body">
                <div>
                    <div class="khqr-info-line">
                        <span style="color:var(--text-muted);">ឈ្មោះម្ចាស់គណនី:</span>
                        <strong>{{ $khqr['account_name'] ?? $payroll->teacher->name }}</strong>
                    </div>
                    <div class="khqr-info-line">
                        <span style="color:var(--text-muted);">ធនាគារ:</span>
                        <strong>{{ $khqr['bank_name'] ?? ($payroll->teacher->bank_name ?: 'Bakong / ABA / ACLEDA') }}</strong>
                        @if(!empty($payroll->teacher->bank_account_number))
                            &nbsp;|&nbsp; <span style="color:var(--text-muted);">លេខគណនី:</span> <strong>{{ $payroll->teacher->bank_account_number }}</strong>
                        @endif
                    </div>
                    <div class="khqr-info-line">
                        <span style="color:var(--text-muted);">Bakong Account ID:</span>
                        <strong style="color:var(--primary-light);">{{ $khqr['bakong_id'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="khqr-price">
                        <span style="color:#e11d48; font-size:12pt; font-weight:800;">${{ number_format($payroll->net_salary, 2) }}</span>
                        <span style="color:var(--text-muted); font-size:9pt;">~</span>
                        <span style="color:var(--success); font-size:11pt; font-weight:800;">{{ number_format(($khqr['amount_khr'] ?? round($payroll->net_salary * 4100, -2))) }} ៛</span>
                        <span style="font-size:7.5pt; color:var(--text-muted);">(អត្រាប្តូរប្រាក់: 1$ = {{ number_format($khqr['khr_rate'] ?? 4100) }} ៛)</span>
                    </div>
                    <div style="font-size:7pt; color:var(--text-muted); margin-top:3px;">
                        ស្កេនទូទាត់ជាមួយ App ធនាគារណាក៏បានក្នុងប្រទេសកម្ពុជា (ABA Mobile, ACLEDA mobile, Wing, Bakong App, etc.)
                    </div>
                </div>
                <div style="text-align:center;">
                    @if(!empty($khqr['qr_image_url']))
                        <img src="{{ $khqr['qr_image_url'] }}" alt="Bakong KHQR" style="width:88px; height:88px; border:1px solid var(--border); border-radius:4px; padding:2px; display:block; margin:0 auto;">
                    @endif
                    <div style="font-size:7pt; font-weight:700; color:#e11d48; margin-top:2px;">KHQR</div>
                </div>
            </div>
        </div>

        {{-- Signatures --}}
        <div class="sig-grid">
            <div>
                <div class="sig-line"></div>
                <div class="sig-label">ហត្ថលេខាគ្រូបង្រៀន</div>
            </div>
            <div>
                <div class="sig-line"></div>
                <div class="sig-label">ប្រធានដេប៉ាតឺម៉ង់</div>
            </div>
            <div>
                <div class="sig-line"></div>
                <div class="sig-label">ការិយាល័យបុគ្គលិក និងហិរញ្ញវត្ថុ</div>
            </div>
        </div>

        <div class="footer-note">
            {{ $settings['payroll_note_footer'] ?? 'ប្រាក់ខែនេះបានគណនាដោយស្វ័យប្រវត្តិ ផ្អែកលើទិន្នន័យវត្តមានពី NTTI Attendance System' }}
            | លេខយោង: PAY-{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}
        </div>
    </div>
</div>

</body>
</html>
