@extends('layouts.app')

@section('title', __('Attendance Reports'))

@push('styles')
<style>
/* ── Premium Analytic Cards ── */
.analytic-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}
.a-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.25rem;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    min-height: 90px;
}
.a-card:hover { transform: translateY(-3px); border-color: var(--primary); }
.a-icon {
    width: 46px; height: 46px; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
}
.a-info .label { font-size: 0.7rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
.a-info .value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); line-height: 1.1; margin-top: 1px; }

/* ── Sliding Pill Tabs ── */
.pill-tabs {
    display: inline-flex;
    background: rgba(255,255,255,0.03);
    padding: 0.4rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border);
}
.pill-btn {
    padding: 0.6rem 1.5rem;
    border-radius: 0.75rem;
    border: none;
    background: none;
    color: var(--text-secondary);
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex; align-items: center; gap: 0.5rem;
}
.pill-btn.active {
    background: var(--primary);
    color: #000;
    box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.2);
}

/* ── Data Sync HUD ── */
.data-hud {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.75rem;
    background: var(--bg-dark);
    border: 1px solid var(--border);
    border-radius: 0.75rem;
    position: relative;
    overflow: hidden;
    min-width: 300px;
    height: 38px;
    box-sizing: border-box;
    justify-content: center;
    white-space: nowrap;
}
.data-hud.loading::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(var(--primary-rgb), 0.1), transparent);
    animation: hudSweep 1.4s linear infinite;
}
@keyframes hudSweep {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.hud-spinner {
    width: 13px; height: 13px;
    border: 2px solid rgba(var(--primary-rgb), 0.25);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    display: none;
    flex-shrink: 0;
}
.data-hud.loading .hud-spinner { display: block; }
.hud-dot {
    width: 8px; height: 8px;
    background: var(--success);
    border-radius: 50%;
    box-shadow: 0 0 8px var(--success);
    flex-shrink: 0;
}
.data-hud.loading .hud-dot { display: none; }
.hud-text {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
    line-height: 1;
}
.hud-status {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--text-secondary);
}
.data-hud.loading .hud-status { color: var(--primary); }
.hud-divider {
    width: 1px;
    height: 12px;
    background: var(--border);
}
.hud-value {
    font-size: 0.9rem;
    font-weight: 900;
    color: var(--text-primary);
}

/* ── Time Pills ── */
.time-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.6rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.time-pill i { font-size: 0.9rem; }
.time-pill.success { color: var(--success); border-color: rgba(var(--success-rgb), 0.2); }
.time-pill.warning { color: var(--warning); border-color: rgba(var(--warning-rgb), 0.2); }

/* ── Modal Form Dark/Light Mode Fixes ── */
.modal-body-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding: 0.9rem 1rem;
    background: var(--bg-dark);
    border: 1px solid var(--border);
    border-radius: 0.6rem;
}
.modal-section-title {
    margin: 1.5rem 0 0.5rem;
    font-size: 0.9rem;
    font-weight: 800;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border);
    padding-bottom: 0.35rem;
}
/* Cancel button */
.btn-cancel {
    padding: 0.55rem 1.3rem;
    background: var(--bg-dark);
    color: var(--text-primary);
    border: 1px solid var(--border);
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.btn-cancel:hover {
    background: var(--border);
    color: var(--text-primary);
}
/* Action col hidden in print/export — handled inside @media print block below */

/* ── Centered table cell class (used in report tables) ── */
.tc { text-align: center !important; vertical-align: middle !important; }

/* ── Modal form field theming (dark + light) ── */
.modal-field-label {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--text-secondary);
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.modal-field-label i {
    color: var(--primary);
    font-size: 0.95rem;
    margin-right: 0.35rem;
}
.modal-section-card {
    background: var(--bg-dark);
    border: 1px solid var(--border);
    border-radius: 0.85rem;
    padding: 1rem 1.1rem 0.8rem;
    margin-bottom: 1rem;
}
.modal-section-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.8rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
}
/* monthly table row striping */
#monthly-tbody tr:nth-child(even) { background: rgba(255,255,255,0.02); }
[data-theme="light"] #monthly-tbody tr:nth-child(even) { background: rgba(0,0,0,0.02); }
#monthly-tbody tr:hover { background: rgba(var(--primary-rgb), 0.06); }

/* ── Progress Rings ── */
.progress-mini {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    position: relative;
    font-size: 0.6rem; font-weight: 800;
}

.chart-glass-card {
    background: var(--bg-card);
    backdrop-filter: blur(10px);
    border: 1px solid var(--border);
    border-radius: 2rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

/* ── Toolbar Enhancements ── */
.form-control-minimal {
    background: transparent;
    border: none;
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 700;
    padding: 0.2rem 0;
    width: 105px;
    outline: none;
    cursor: pointer;
}
.form-control-minimal::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(1) brightness(1.5);
    opacity: 0.8;
    transition: opacity 0.2s;
}
[data-theme="light"] .form-control-minimal::-webkit-calendar-picker-indicator {
    filter: none;
}
.form-control-minimal::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}
.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}
.input-with-icon i {
    position: absolute;
    left: 1rem;
    color: var(--primary);
    font-size: 1.1rem;
    pointer-events: none;
}
.btn-icon-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 0.4rem 0.8rem;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 0.5rem;
    transition: all 0.2s;
    min-width: 50px;
    color: var(--text-primary);
}
.btn-icon-label:hover { background: rgba(255,255,255,0.08); }
[data-theme="light"] .btn-icon-label:hover { background: rgba(0,0,0,0.05); }
.btn-icon-label i { font-size: 1.25rem; color: var(--primary); }
.btn-icon-label span { font-size: 0.65rem; font-weight: 800; color: var(--text-secondary); }

@media print {
    @page { margin: 20mm; size: A4 landscape; }
    html, body { height: auto !important; overflow: visible !important; font-family: 'Battambang', 'Inter', sans-serif !important; background: white !important; color: black !important; }
    
    /* Hide ALL UI Elements */
    .sidebar, .topbar, .main-footer, .no-print, .pill-tabs, .chart-glass-card, button, select, input, .ph, .page-title, .no-print *, .skeleton, .summary-search-wrap { display: none !important; }
    /* Hide action and source columns entirely in print */
    th.col-action, td.col-action, th.col-source, td.col-source { display: none !important; }

    /* Hide analytic grid in print (we use print-stats-row instead) */
    .analytic-grid { display: none !important; }

    /* Only show the panel marked for printing */
    .tab-panel { display: none !important; }
    .tab-panel.print-active, .tab-panel.active.print-active { display: block !important; opacity: 1 !important; visibility: visible !important; }
    
    /* Force Layout & Prevent Clipping */
    .main-content, .content-wrapper, .card, .table-responsive { 
        margin: 0 !important; 
        padding: 0 !important; 
        width: 100% !important; 
        display: block !important; 
        overflow: visible !important;
        position: static !important;
        max-width: none !important;
        box-shadow: none !important;
        background: white !important;
    }

    /* Clean Header */
    .print-header { 
        display: block !important; 
        text-align: center; 
        border-bottom: 2px solid #000; 
        padding-bottom: 1.5rem; 
        margin-bottom: 1.5rem;
        padding-top: 40px !important;
    }
    .print-header .logo { 
        max-height: 80px; 
        margin-bottom: 0.5rem; 
        border-radius: 50%;
        background: transparent !important;
    }
    .print-header .title { font-size: 1.4rem; font-weight: 800; text-transform: uppercase; margin: 0; color: #000 !important; }
    .print-header .subtitle { font-size: 1.1rem; font-weight: 700; margin: 0.2rem 0 0.5rem 0; color: #000 !important; }
    
    .print-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border: 1.5px solid #000;
        border-radius: 4px;
        margin-top: 1rem;
    }
    .print-meta-item { 
        padding: 6px 12px;
        border-bottom: 1px solid #000;
        border-right: 1px solid #000;
        font-size: 0.85rem; 
        font-weight: 800; 
        color: #000; 
        text-align: left;
    }
    .print-meta-item:nth-child(2n) { border-right: none; }
    .print-meta-item:nth-last-child(-n+2) { border-bottom: none; }

    .a-card { 
        border: 1px solid #000 !important; 
        border-radius: 4px !important; 
        padding: 0.5rem !important; 
        text-align: center;
        flex-direction: column !important;
        min-height: 60px !important;
    }
    .a-info .label { font-size: 0.65rem !important; font-weight: 800 !important; margin: 0 !important; }
    .a-info .value { font-size: 1.2rem !important; font-weight: 900 !important; margin: 0 !important; }
    .a-icon { display: none !important; }

    /* TABLE FOR PRINT */
    .table { width: 100% !important; border-collapse: collapse !important; font-size: 0.72rem !important; }
    .table thead th { 
        background: #f1f5f9 !important; 
        color: #000 !important; 
        font-weight: 900 !important;
        font-size: 0.75rem !important;
        padding: 6px 4px !important;
        border: 1px solid #000 !important;
        word-wrap: break-word;
        white-space: normal;
        text-align: center;
    }
    .table td { 
        border: 1px solid #000 !important; 
        padding: 6px 5px !important; 
        font-size: 0.72rem !important;
        font-weight: 900 !important;
        color: #000 !important;
        word-wrap: break-word;
        white-space: normal;
    }
    .table td * {
        font-weight: 900 !important;
        color: #000 !important;
    }
    /* Force badge text to black for print */
    .badge { background: none !important; border: 1.5px solid #000 !important; color: #000 !important; padding: 2px 5px !important; border-radius: 3px !important; font-size: 0.7rem !important; font-weight: 900 !important; }
    .time-pill { border: none !important; padding: 0 !important; background: none !important; font-size: 0.72rem !important; font-weight: 900 !important; color: #000 !important; }
    .time-pill i { display: none !important; }

    .stagger-item {
        opacity: 1 !important;
        animation: none !important;
        transform: none !important;
    }

    * { -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }

    /* Print Stats Row */
    .print-stats-row {
        display: flex !important;
        justify-content: space-around;
        margin-top: 1rem;
        border: 1.5px solid #000;
        border-radius: 4px;
        padding: 0.5rem 0;
    }
    .print-stat {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        flex: 1;
        border-right: 1px solid #ccc;
        padding: 4px 8px;
    }
    .print-stat:last-child { border-right: none; }
    .print-stat-value { font-size: 1.4rem; font-weight: 900; line-height: 1.1; }
    .print-stat-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: #555; letter-spacing: 0.4px; }

    /* Signature Block */
    .print-signature-block {
        display: flex !important;
        justify-content: space-between;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #ccc;
    }
    .sig-col {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        width: 25%;
    }
    .sig-line {
        width: 100%;
        border-bottom: 1px solid #000;
        height: 60px;
        margin-bottom: 6px;
    }
    .sig-title { font-size: 0.85rem; font-weight: 800; color: #000; }
    .sig-sub   { font-size: 0.75rem; color: #555; }
}
</style>
@endpush

@section('content')

@php
    $uName = \App\Models\Setting::getValue('university_name', 'NTTI System');
    $uLogo = \App\Models\Setting::getValue('university_logo', '');
@endphp

{{-- Premium Print Header --}}
<div class="print-header" style="display: none;">
    @if($uLogo)
        <img src="{{ $uLogo }}" alt="Logo" class="logo">
    @endif
    <h1 class="title">{{ $uName }}</h1>
    <h2 class="subtitle">{{ __('Teacher Attendance Official Report') }}</h2>
    
    <div class="print-meta-grid">
        <div class="print-meta-item">{{ __('Report Period') }}: <span id="print-range"></span></div>
        <div class="print-meta-item">{{ __('Export Date') }}: <span>{{ now()->format('d-m-Y H:i:s') }}</span></div>
        <div class="print-meta-item">{{ __('Target Department') }}: <span id="print-dept-val">{{ __('All') }}</span></div>
        <div class="print-meta-item">{{ __('Target Personnel') }}: <span id="print-teacher-val">{{ __('All') }}</span></div>
    </div>

    {{-- Print-only summary stats row --}}
    <div class="print-stats-row">
        <div class="print-stat">
            <span class="print-stat-value" style="color:#34a853;" id="psum-present">—</span>
            <span class="print-stat-label">{{ __('Present') }}</span>
        </div>
        <div class="print-stat">
            <span class="print-stat-value" style="color:#f59e0b;" id="psum-late">—</span>
            <span class="print-stat-label">{{ __('Late') }}</span>
        </div>
        <div class="print-stat">
            <span class="print-stat-value" style="color:#ea4335;" id="psum-absent">—</span>
            <span class="print-stat-label">{{ __('Absent') }}</span>
        </div>
        <div class="print-stat">
            <span class="print-stat-value" style="color:#1a73e8;" id="psum-workdays">—</span>
            <span class="print-stat-label">{{ __('Working Days') }}</span>
        </div>
    </div>
</div>

{{-- Premium Action Toolbar --}}
<div class="no-print" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.25rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        
        {{-- Left: Page Title --}}
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">{{ __('Attendance Reports') }}</h1>
            <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0;">{{ __('Filter, analyze and export institution data') }}</p>
        </div>

        

        {{-- Right: Filters & Actions --}}
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            
            {{-- Date Group --}}
            <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-dark); padding: 0.4rem 0.75rem; border-radius: 0.75rem; border: 1px solid var(--border);">
                <i class="ph ph-calendar" style="color: var(--primary);"></i>
                <input type="date" id="dateFrom" class="form-control-minimal" value="{{ now()->startOfMonth()->toDateString() }}">
                <span style="color: var(--text-secondary); font-size: 0.8rem;">{{ __('to') }}</span>
                <input type="date" id="dateTo" class="form-control-minimal" value="{{ now()->toDateString() }}">
            </div>

            {{-- Dropdown Filters --}}
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <div class="input-with-icon">
                    <i class="ph ph-file-text"></i>
                    <select id="reportTypeFilter" class="form-control" style="width: 190px; padding-left: 2.2rem; background: var(--bg-dark); font-weight: 700;" onchange="handleReportTypeChange()">
                        <option value="daily">{{ __('Daily Attendance') }}</option>
                        <option value="monthly">{{ __('Monthly Attendance') }}</option>
                        <option value="absent">{{ __('Absent Report') }}</option>
                        <option value="late">{{ __('Late Report') }}</option>
                    </select>
                </div>

                <div class="input-with-icon">
                    <i class="ph ph-buildings"></i>
                    <select id="departmentFilter" class="form-control" style="width: 180px; padding-left: 2.2rem; background: var(--bg-dark); font-weight: 700;">
                        <option value="">{{ __('All Departments') }}</option>
                        @foreach($departments as $d)
                            <option value="{{$d->name}}">{{ app()->getLocale() == 'km' ? ($d->name_kh ?: $d->name) : $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="input-with-icon">
                    <i class="ph ph-user-circle"></i>
                    <select id="teacherFilter" class="form-control" style="width: 190px; padding-left: 2.2rem; background: var(--bg-dark); font-weight: 700;">
                        <option value="">{{ __('All Teachers') }}</option>
                        @foreach($teachers as $t)
                            <option value="{{$t->id}}" data-dept="{{$t->department}}">
                                {{ app()->getLocale() == 'km' ? ($t->name_kh ?: $t->name) : $t->name }} ({{$t->employee_id}})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display: flex; gap: 0.5rem; border-left: 1px solid var(--border); padding-left: 1rem; margin-left: 0.5rem;">
                <button type="button" class="btn btn-primary" onclick="loadAll()" style="padding: 0.6rem 1.5rem; background: linear-gradient(135deg, var(--primary), #00b894); border: none; color: #000; font-weight: 800; box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3); transition: all 0.3s; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="ph ph-funnel" style="font-size: 1.1rem;"></i> {{ __('Filter') }}
                </button>
                <div style="display: flex; gap: 0.25rem; background: rgba(255,255,255,0.05); padding: 0.25rem; border-radius: 0.75rem;">
                    <button class="btn-icon-label" onclick="exportPdf()" title="{{ __('Export PDF') }}">
                        <i class="ph ph-file-pdf" style="color: #ff4d4d;"></i>
                        <span>PDF</span>
                    </button>
                    <button class="btn-icon-label" onclick="exportExcel()" title="{{ __('Export Excel') }}">
                        <i class="ph ph-file-xls" style="color: #2ecc71;"></i>
                        <span>Excel</span>
                    </button>
                    <button class="btn-icon-label" onclick="openManualModal()" title="{{ __('Manual Attendance') }}" style="border-left: 1px solid rgba(255,255,255,0.1); padding-left: 0.5rem; margin-left: 0.25rem;">
                        <i class="ph ph-plus-circle" style="color: var(--primary);"></i>
                        <span>Manual</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="analytic-grid" id="main-analytic-grid">
    <div class="a-card">
        <div class="a-icon" style="background: rgba(var(--success-rgb), 0.1); color: var(--success);"><i class="ph ph-check-circle"></i></div>
        <div class="a-info">
            <div class="label">{{ __('Total Present') }}</div>
            <div class="value" id="sum-present">0</div>
        </div>
    </div>
    <div class="a-card">
        <div class="a-icon" style="background: rgba(var(--warning-rgb), 0.1); color: var(--warning);"><i class="ph ph-warning-circle"></i></div>
        <div class="a-info">
            <div class="label">{{ __('Total Late') }}</div>
            <div class="value" id="sum-late">0</div>
        </div>
    </div>
    <div class="a-card">
        <div class="a-icon" style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger);"><i class="ph ph-x-circle"></i></div>
        <div class="a-info">
            <div class="label">{{ __('Total Absent') }}</div>
            <div class="value" id="sum-absent">0</div>
        </div>
    </div>
    <div class="a-card">
        <div class="a-icon" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);"><i class="ph ph-calendar-check"></i></div>
        <div class="a-info">
            <div class="label">{{ __('Working Days') }}</div>
            <div class="value" id="sum-workdays">—</div>
        </div>
    </div>
</div>

{{-- Report Panels --}}

{{-- 1. Daily Records --}}
<div id="panel-daily" class="tab-panel">
    <div class="chart-glass-card">
        <h3 style="margin-bottom: 2rem; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
            <i class="ph ph-chart-line-up" style="color:var(--primary);"></i>
            {{ __('Attendance Volatility & Trend') }}
        </h3>
        <div class="chart-container" style="height: 350px;">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Teacher') }}</th>
                    <th>{{ __('Department') }}</th>
                    <th>{{ __('Morning') }}</th>
                    <th>{{ __('Afternoon') }}</th>
                    <th>{{ __('Working Hours') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="col-source">{{ __('Source') }}</th>
                    <th class="col-action">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody id="report-tbody">
                <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 2. Monthly Attendance --}}
<div id="panel-monthly" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-ranking" style="margin-right:0.4rem;"></i>{{ __('Teacher Monthly Attendance') }}</h3>
            <span id="monthly-period" style="font-size:0.78rem; color:var(--text-secondary);"></span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="min-width:120px;">{{ __('Teacher ID') }}</th>
                        <th style="min-width:260px; padding-left:1.25rem; padding-right:1.25rem;">{{ __('Teacher Name') }}</th>
                        <th style="min-width:220px; padding-left:1.25rem; padding-right:1.25rem;">{{ __('Department') }}</th>
                        <th style="text-align:center;">{{ __('Present') }}</th>
                        <th style="text-align:center;">{{ __('Late') }}</th>
                        <th style="text-align:center;">{{ __('Absent') }}</th>
                        <th style="text-align:center;">{{ __('Working Days') }}</th>
                        <th style="text-align:center;">{{ __('Total Hours') }}</th>
                        <th style="text-align:center;">{{ __('Attendance %') }}</th>
                    </tr>
                </thead>
                <tbody id="monthly-tbody">
                    <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 3. Absent Report --}}
<div id="panel-absent" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-user-minus" style="margin-right:0.4rem;color:var(--danger);"></i>{{ __('Absent Report') }}</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th>{{ __('Teacher ID') }}</th>
                        <th>{{ __('Teacher Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Absent Date') }}</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Remark') }}</th>
                        <th class="col-action" style="text-align:center;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody id="absent-tbody">
                    <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 4. Late Report --}}
<div id="panel-late" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-clock-afternoon" style="margin-right:0.4rem;color:var(--warning);"></i>{{ __('Late Report') }}</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th>{{ __('Teacher ID') }}</th>
                        <th>{{ __('Teacher Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Check In') }}</th>
                        <th>{{ __('Expected Time') }}</th>
                        <th>{{ __('Late Minutes') }}</th>
                        <th class="col-action" style="text-align:center;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody id="late-tbody">
                    <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Official Signature Block (print only) --}}
<div class="print-signature-block" style="display: none;">
    <div class="sig-col">
        <div class="sig-line"></div>
        <div class="sig-title">{{ __('Prepared by') }}</div>
        <div class="sig-sub">{{ __('Report Officer') }}</div>
    </div>
    <div class="sig-col">
        <div class="sig-line"></div>
        <div class="sig-title">{{ __('Verified by') }}</div>
        <div class="sig-sub">{{ __('Head of Department') }}</div>
    </div>
    <div class="sig-col">
        <div class="sig-line"></div>
        <div class="sig-title">{{ __('Approved by') }}</div>
        <div class="sig-sub">{{ __('Director / Principal') }}</div>
    </div>
</div>

{{-- Manual Attendance Modal --}}
<div id="manualAttendanceModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(6px);">
    <div style="background:var(--bg-card); width:100%; max-width:640px; border-radius:1.25rem; border:1px solid var(--border); box-shadow:0 20px 60px rgba(0,0,0,0.3); overflow:hidden; max-height:90vh; display:flex; flex-direction:column;">

        {{-- Modal Header --}}
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg, rgba(var(--primary-rgb),0.08), transparent); flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <div style="width:36px; height:36px; border-radius:0.75rem; background:rgba(var(--primary-rgb),0.15); display:flex; align-items:center; justify-content:center;">
                    <i class="ph ph-clipboard-text" style="color:var(--primary); font-size:1.2rem;"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1rem; font-weight:900;">{{ __('Manual Attendance') }}</h3>
                    <p style="margin:0; font-size:0.72rem; color:var(--text-secondary);">{{ __('Enter attendance record manually') }}</p>
                </div>
            </div>
            <button onclick="closeModal('manualAttendanceModal')" style="background:var(--bg-dark); border:1px solid var(--border); color:var(--text-secondary); width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.1rem; transition:all 0.2s;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="ph ph-x"></i>
            </button>
        </div>

        {{-- Scrollable Body --}}
        <div style="padding:1.25rem 1.5rem; overflow-y:auto; flex:1;">
            <form id="manualAttendanceForm" onsubmit="submitManualAttendance(event)">

                {{-- Row 1: Department + Date --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.85rem; margin-bottom:0.85rem;">
                    <div>
                        <label class="modal-field-label"><i class="ph ph-buildings" style="margin-right:0.25rem;"></i>{{ __('Department') }}</label>
                        <select id="manual_department_id" class="form-control" style="width:100%;" onchange="filterManualTeachers()">
                            <option value="">{{ __('All Departments') }}</option>
                            @foreach($departments as $d)
                                <option value="{{$d->name}}">{{ app()->getLocale() == 'km' ? ($d->name_kh ?: $d->name) : $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="modal-field-label"><i class="ph ph-calendar" style="margin-right:0.25rem;"></i>{{ __('Date') }} <span style="color:var(--danger);">*</span></label>
                        <input type="date" id="manual_date" class="form-control" required style="width:100%;" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                {{-- Teacher --}}
                <div style="margin-bottom:1.1rem;">
                    <label class="modal-field-label"><i class="ph ph-user-circle" style="margin-right:0.25rem;"></i>{{ __('Teacher') }} <span style="color:var(--danger);">*</span></label>
                    <select id="manual_teacher_id" class="form-control" required style="width:100%;">
                        <option value="">-- {{ __('Select Teacher') }} --</option>
                        @foreach($teachers as $t)
                            <option value="{{$t->id}}" data-dept="{{$t->department}}">{{ app()->getLocale() == 'km' ? ($t->name_kh ?: $t->name) : $t->name }} ({{ $t->employee_id }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Morning Session Card --}}
                <div class="modal-section-card">
                    <div class="modal-section-header" style="color:var(--warning);">
                        <i class="ph ph-sun-dim" style="font-size:1rem;"></i>
                        {{ __('Morning Session') }}
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem;">
                        <div>
                            <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check In') }}</label>
                            <input type="time" id="manual_morning_in" class="form-control" style="width:100%;">
                        </div>
                        <div>
                            <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check Out') }}</label>
                            <input type="time" id="manual_morning_out" class="form-control" style="width:100%;">
                        </div>
                        <div>
                            <label class="modal-field-label"><i class="ph ph-check-circle"></i>{{ __('Status') }} <span style="color:var(--danger);">*</span></label>
                            <select id="manual_morning_status" class="form-control" required style="width:100%;">
                                <option value="none">— {{ __('None') }} —</option>
                                <option value="present">✓ {{ __('Present') }}</option>
                                <option value="late">⏰ {{ __('Late') }}</option>
                                <option value="absent">✗ {{ __('Absent') }}</option>
                                                            </select>
                        </div>
                    </div>
                </div>

                {{-- Afternoon Session Card --}}
                <div class="modal-section-card">
                    <div class="modal-section-header" style="color:var(--primary);">
                        <i class="ph ph-cloud-sun" style="font-size:1rem;"></i>
                        {{ __('Afternoon Session') }}
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem;">
                        <div>
                            <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check In') }}</label>
                            <input type="time" id="manual_afternoon_in" class="form-control" style="width:100%;">
                        </div>
                        <div>
                            <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check Out') }}</label>
                            <input type="time" id="manual_afternoon_out" class="form-control" style="width:100%;">
                        </div>
                        <div>
                            <label class="modal-field-label"><i class="ph ph-check-circle"></i>{{ __('Status') }} <span style="color:var(--danger);">*</span></label>
                            <select id="manual_afternoon_status" class="form-control" required style="width:100%;">
                                <option value="none">— {{ __('None') }} —</option>
                                <option value="present">✓ {{ __('Present') }}</option>
                                <option value="late">⏰ {{ __('Late') }}</option>
                                <option value="absent">✗ {{ __('Absent') }}</option>
                                                            </select>
                        </div>
                    </div>
                </div>

                {{-- Reason --}}
                <div style="margin-bottom:0.5rem;">
                    <label class="modal-field-label"><i class="ph ph-note-pencil" style="margin-right:0.25rem;"></i>{{ __('Reason / Note') }} <span style="color:var(--danger);">*</span></label>
                    <input type="text" id="manual_reason" class="form-control" required style="width:100%;" placeholder="{{ __('e.g., Forgot to scan, Official duty...') }}">
                </div>

                {{-- Footer Buttons --}}
                <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem; border-top:1px solid var(--border); padding-top:1.1rem;">
                    <button type="button" onclick="closeModal('manualAttendanceModal')" class="btn-cancel">
                        <i class="ph ph-x"></i> {{ __('Cancel') }}
                    </button>
                    <button type="submit" style="padding:0.55rem 1.5rem; border-radius:0.75rem; font-weight:800; border:none; background:linear-gradient(135deg, var(--primary), #00b894); color:#000; display:inline-flex; align-items:center; gap:0.4rem; cursor:pointer; font-size:0.85rem; transition:all 0.2s;">
                        <i class="ph ph-floppy-disk"></i> {{ __('Save Attendance') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editAttendanceModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:var(--bg-card); width:100%; max-width:600px; border-radius:1rem; border:1px solid var(--border); box-shadow:0 10px 40px rgba(0,0,0,0.2); overflow:hidden;">
        <div style="padding:1.5rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;"><i class="ph ph-pencil-simple" style="color:var(--warning); margin-right:0.5rem;"></i>{{ __('Edit Attendance') }}</h3>
            <button onclick="closeModal('editAttendanceModal')" class="btn-icon-label" style="min-width:auto; padding:0.2rem;"><i class="ph ph-x"></i></button>
        </div>
        <div style="padding:1.5rem;">
            <form id="editAttendanceForm" onsubmit="submitEditAttendance(event)">
                <input type="hidden" id="edit_attendance_id">
                <div class="modal-body-info">
                    <div><span style="color:var(--text-secondary); font-size:0.8rem;">Teacher:</span> <br><strong id="edit_teacher_name"></strong></div>
                    <div style="text-align:right;"><span style="color:var(--text-secondary); font-size:0.8rem;">Date:</span> <br><strong id="edit_date"></strong></div>
                </div>
                
                <h4 style="margin:0 0 0.5rem; font-size:0.9rem; color:var(--text-primary); border-bottom:1px solid var(--border); padding-bottom:0.25rem;">{{ __('Morning Session') }}</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check In') }}</label>
                        <input type="time" id="edit_morning_in" class="form-control" style="width:100%;">
                    </div>
                    <div>
                        <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check Out') }}</label>
                        <input type="time" id="edit_morning_out" class="form-control" style="width:100%;">
                    </div>
                    <div>
                        <label class="modal-field-label"><i class="ph ph-check-circle"></i>{{ __('Status') }} <span style="color:var(--danger);">*</span></label>
                        <select id="edit_morning_status" class="form-control" required style="width:100%;">
                            <option value="none">None</option>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                                                    </select>
                    </div>
                </div>

                <h4 style="margin:1.5rem 0 0.5rem; font-size:0.9rem; color:var(--text-primary); border-bottom:1px solid var(--border); padding-bottom:0.25rem;">{{ __('Afternoon Session') }}</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check In') }}</label>
                        <input type="time" id="edit_afternoon_in" class="form-control" style="width:100%;">
                    </div>
                    <div>
                        <label class="modal-field-label"><i class="ph ph-clock"></i>{{ __('Check Out') }}</label>
                        <input type="time" id="edit_afternoon_out" class="form-control" style="width:100%;">
                    </div>
                    <div>
                        <label class="modal-field-label"><i class="ph ph-check-circle"></i>{{ __('Status') }} <span style="color:var(--danger);">*</span></label>
                        <select id="edit_afternoon_status" class="form-control" required style="width:100%;">
                            <option value="none">None</option>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                                                    </select>
                    </div>
                </div>
                
                <div style="margin-bottom:1rem;">
                    <label class="modal-field-label"><i class="ph ph-note-pencil" style="margin-right:0.25rem;"></i>{{ __('Reason for Edit') }} <span style="color:var(--danger);">*</span></label>
                    <input type="text" id="edit_reason" class="form-control" required style="width:100%;" placeholder="{{ __('Why is this being edited?') }}">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem; border-top:1px solid var(--border); padding-top:1.25rem;">
                    <button type="button" onclick="closeModal('editAttendanceModal')" class="btn-cancel">
                        <i class="ph ph-x"></i> {{ __('Cancel') }}
                    </button>
                    <button type="submit" style="padding:0.55rem 1.5rem; border-radius:0.75rem; font-weight:800; border:none; background:linear-gradient(135deg, var(--primary), #00b894); color:#000; display:inline-flex; align-items:center; gap:0.4rem; cursor:pointer; font-size:0.85rem; transition:all 0.2s;">
                        <i class="ph ph-pencil-simple"></i> {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="attendanceHistoryModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:var(--bg-card); width:100%; max-width:600px; border-radius:1rem; border:1px solid var(--border); box-shadow:0 10px 40px rgba(0,0,0,0.2); overflow:hidden;">
        <div style="padding:1.5rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;"><i class="ph ph-clock-counter-clockwise" style="color:var(--primary); margin-right:0.5rem;"></i>{{ __('Attendance Edit History') }}</h3>
            <button onclick="closeModal('attendanceHistoryModal')" class="btn-icon-label" style="min-width:auto; padding:0.2rem;"><i class="ph ph-x"></i></button>
        </div>
        <div style="padding:1.5rem; max-height:60vh; overflow-y:auto;" id="historyModalContent">
            <div style="text-align:center; color:var(--text-secondary);">{{ __('Loading history...') }}</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
let chartInstance = null;
let summaryData   = [];
let maxMinutes    = 1;

const deptMap = {
    @foreach($departments as $d)
    "{{ $d->name }}": "{{ app()->getLocale() == 'km' ? ($d->name_kh ?: $d->name) : $d->name }}",
    @endforeach
};
window.transDept = function(d) { 
    if (!d) return d;
    const entry = Object.entries(deptMap).find(([k]) => k.toLowerCase() === d.trim().toLowerCase());
    return entry ? entry[1] : d;
};


// ── Tab switching ──────────────────────────────────
function handleReportTypeChange() {
    const type = document.getElementById('reportTypeFilter').value;
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    document.getElementById('panel-' + type).style.display = 'block';
    
    // Hide analytic grid for non-daily/monthly report types
    const grid = document.getElementById('main-analytic-grid');
    if (grid) {
        if (type === 'daily' || type === 'monthly') {
            grid.style.display = 'grid';
        } else {
            grid.style.display = 'none';
        }
    }

    // Automatically load data for the newly selected report type
    loadAll();
}

async function loadAll() {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    const dept = document.getElementById('departmentFilter').value;
    const teacherId = document.getElementById('teacherFilter').value;
    const type = document.getElementById('reportTypeFilter').value;
    
    const params = new URLSearchParams({ from, to });
    if (dept) params.append('department', dept);
    if (teacherId) params.append('teacher_id', teacherId);

    // Print header
    const fmt = s => { const p = s.split('-'); return p.length===3 ? `${p[2]}-${p[1]}-${p[0]}` : s; };
    const printRangeEl = document.getElementById('print-range');
    if (printRangeEl) printRangeEl.textContent = `${fmt(from)} — ${fmt(to)}`;
    
    const deptEl = document.getElementById('print-dept-val');
    if (deptEl) deptEl.textContent = window.transDept(dept) || '{{ __("All Departments") }}';


    const teacherEl = document.getElementById('print-teacher-val');
    const teacherSelect = document.getElementById('teacherFilter');
    if (teacherEl) {
        if (teacherId && teacherSelect) {
            teacherEl.textContent = teacherSelect.options[teacherSelect.selectedIndex].text;
        } else {
            teacherEl.textContent = '{{ __("All Teachers") }}';
        }
    }

    const hud = document.getElementById('dataSyncHud');
    const hudStatus = document.getElementById('hudStatusText');
    const hudCount  = document.getElementById('hudRecordCount');
    if (hud) {
        hud.classList.add('loading');
        if (hudStatus) hudStatus.textContent = '{{ __("Fetching...") }}';
        if (hudCount)  hudCount.textContent  = '--';
    }

    try {
        if (type === 'daily') {
            await loadDaily(params);
        } else if (type === 'monthly') {
            await loadMonthly(params);
        } else if (type === 'absent') {
            await loadAbsent(params);
        } else if (type === 'late') {
            await loadLate(params);
        }
    } catch(e) {
        console.error('Error loading report data:', e);
        window.showToast('Report load error: ' + e.message, 'error');
    } finally {
        if (hud) {
            hud.classList.remove('loading');
            if (hudStatus) hudStatus.textContent = '{{ __("Data Synced") }}';
        }
    }
}

// Reuse skeletons for generic tables
function buildGenericSkeletons(cols = 5, rows = 5) {
    let html = '';
    for (let i = 0; i < rows; i++) {
        html += '<tr>';
        for (let j = 0; j < cols; j++) {
            html += `<td><div class="skeleton skeleton-text" style="width:80%;"></div></td>`;
        }
        html += '</tr>';
    }
    return html;
}

// ── 1. Daily Records ─────────────────────────────────
async function loadDaily(params) {
    const tbody = document.getElementById('report-tbody');
    tbody.innerHTML = buildGenericSkeletons(7, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports') }}?${params.toString()}`);
        document.getElementById('sum-present').textContent = res.summary.present;
        document.getElementById('sum-late').textContent    = res.summary.late;
        document.getElementById('sum-absent').textContent  = res.summary.absent;
        document.getElementById('sum-workdays').textContent = res.summary.working_days;
        
        const countEl = document.getElementById('hudRecordCount');
        if (countEl) countEl.textContent = res.records.length;

        tbody.innerHTML = '';
        if (res.records.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center" style="padding:2rem;color:var(--text-secondary);">{{ __('No records found for this period.') }}</td></tr>`;
        } else {
            let html = '';
            const currentLocale = document.documentElement.lang || 'en';
            res.records.forEach((r, i) => {
                const bc = r.status === 'present' ? 'success' : (r.status === 'late' ? 'warning' : 'danger');
                const morningStatus = r.morning_status === 'late' ? 'warning' : 'success';
                const afternoonStatus = r.afternoon_status === 'late' ? 'warning' : 'success';
                const statusLabel = r.status === 'present' ? '{{ __("Present") }}' : (r.status === 'late' ? '{{ __("Late") }}' : '{{ __("Absent") }}');
                const mainName = currentLocale === 'km' ? (r.teacher.name_kh || r.teacher.name) : r.teacher.name;
                const subName = currentLocale === 'km' ? r.teacher.name : (r.teacher.name_kh || '');
                html += `
                <tr class="stagger-item" style="animation-delay: ${(i * 0.02).toFixed(2)}s">
                    <td><div style="font-weight:700;">${r.date}</div></td>
                    <td>
                        <div style="font-weight:800; color: var(--primary); font-size: 1.05rem;">${mainName}</div>
                        <div style="font-weight:700; opacity: 0.8;">${subName}</div>
                        <div style="font-size:0.72rem;color:var(--text-secondary);font-family:monospace;">${r.teacher.employee_id}</div>
                    </td>
                    <td style="color:var(--text-secondary);font-size:0.8rem;font-weight:600;">${window.transDept ? window.transDept(r.teacher.department) : r.teacher.department}</td>
                    <td>
                        <div class="time-pill ${morningStatus}">
                            <i class="ph ph-sun-dim"></i>
                            <span>${r.morning_in ? `${r.morning_in.split(' ')[0]} - ${r.morning_out ? r.morning_out.split(' ')[0] : '?'}` : '—'}</span>
                        </div>
                    </td>
                    <td>
                        <div class="time-pill ${afternoonStatus}">
                            <i class="ph ph-cloud-sun"></i>
                            <span>${r.afternoon_in ? `${r.afternoon_in.split(' ')[0]} - ${r.afternoon_out ? r.afternoon_out.split(' ')[0] : '?'}` : '—'}</span>
                        </div>
                    </td>
                    <td style="font-weight:800;color:var(--primary);">
                        ${r.working_hours||'0.0h'}
                    </td>
                    <td><span class="badge badge-${bc}" style="border-radius:2rem; padding: 0.4rem 0.8rem;">${statusLabel}</span></td>
                    <td class="col-source">
                        ${(() => {
                            const src = r.source || 'RFID';
                            const note = r.manual_note ? ` title="${r.manual_note}"` : '';
                            const historyIcon = `<i class="ph ph-clock-counter-clockwise" style="cursor:pointer; color:var(--primary); margin-left:0.25rem;" onclick="viewHistory(${r.id})" title="{{ __('View Edit History') }}"></i>`;
                            if (src === 'Manual') {
                                return `<span style="font-size:0.72rem;font-weight:800;color:var(--success,#10b981);background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);padding:0.2rem 0.55rem;border-radius:0.5rem;display:inline-flex;align-items:center;gap:0.3rem;"${note}><i class="ph ph-pencil-simple-line"></i> {{ __("Admin") }} ${historyIcon}</span>`;
                            } else if (src.includes('Edited')) {
                                return `<span style="font-size:0.72rem;font-weight:700;color:var(--warning,#f59e0b);background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);padding:0.2rem 0.55rem;border-radius:0.5rem;display:inline-flex;align-items:center;gap:0.3rem;"${note}><i class="ph ph-pencil-simple"></i> {{ __("Edited") }} ${historyIcon}</span>`;
                            }
                            return `<span style="font-size:0.72rem;font-weight:700;color:var(--text-secondary);background:rgba(255,255,255,0.05);padding:0.2rem 0.5rem;border-radius:0.5rem;">${src}</span>`;
                        })()}
                    </td>
                    <td class="col-action">
                        <button onclick='openEditModal(${JSON.stringify(r).replace(/'/g, "&#39;")})' class="btn btn-sm" style="background: rgba(255,255,255,0.1); border: none; color: var(--text-primary); border-radius: 0.5rem; padding: 0.3rem 0.6rem;">
                            <i class="ph ph-pencil-simple"></i>
                        </button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }
        renderChart(res.chart);
    } catch(e) { console.error(e); }
}

function renderChart(chartData) {
    const canvas = document.getElementById('attendanceChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
    if (!chartData || chartData.length === 0) return;

    const labels  = chartData.map(d => d.date);
    const present = chartData.map(d => d.present);
    const late    = chartData.map(d => d.late);
    const absent  = chartData.map(d => d.absent);

    const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? 'rgba(255,255,255,0.6)'  : 'rgba(0,0,0,0.6)';

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: '{{ __("Present") }}', data: present, backgroundColor: 'rgba(0,212,156,0.7)', borderColor: 'rgba(0,212,156,1)', borderWidth: 1, borderRadius: 4 },
                { label: '{{ __("Late") }}',    data: late,    backgroundColor: 'rgba(245,158,11,0.7)', borderColor: 'rgba(245,158,11,1)', borderWidth: 1, borderRadius: 4 },
                { label: '{{ __("Absent") }}',  data: absent,  backgroundColor: 'rgba(239,68,68,0.5)',  borderColor: 'rgba(239,68,68,1)',  borderWidth: 1, borderRadius: 4 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: textColor, font: { weight: '700', size: 12 } } },
                tooltip: { backgroundColor: 'rgba(0,0,0,0.85)', titleFont: { weight: '800' } }
            },
            scales: {
                x: { ticks: { color: textColor, maxRotation: 45, font: { size: 10 } }, grid: { color: gridColor } },
                y: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
            }
        }
    });
}

// ── 2. Monthly Attendance (formerly Summary) ─────────
async function loadMonthly(params) {
    const tbody = document.getElementById('monthly-tbody');
    tbody.innerHTML = buildGenericSkeletons(10, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.teacher-summary') }}?${params.toString()}`);
        document.getElementById('sum-workdays').textContent = res.total_working_days;
        document.getElementById('monthly-period').textContent = `{{ __('Period') }}: ${res.period_from} — ${res.period_to}`;
        const countEl = document.getElementById('hudRecordCount');
        if (countEl) countEl.textContent = res.summary.length;
        
        tbody.innerHTML = '';
        if (res.summary.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:2rem;">{{ __('No data found.') }}</td></tr>`;
            return;
        }
        const currentLocale = document.documentElement.lang || 'en';
        res.summary.forEach((t, i) => {
            const rateColor = t.attendance_rate >= 80 ? 'var(--success)' : t.attendance_rate >= 50 ? 'var(--warning)' : 'var(--danger)';
            const mainName = currentLocale === 'km' ? (t.name_kh || t.name) : t.name;
            const subName = currentLocale === 'km' ? t.name : (t.name_kh || '');
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: ${(i * 0.03).toFixed(2)}s">
                <td class="tc">${i + 1}</td>
                <td class="tc">${t.employee_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary); font-size: 1rem;">${mainName}</div>
                    <div style="font-weight:600; font-size: 0.9rem; opacity: 0.8;">${subName}</div>
                </td>
                <td style="color:var(--text-secondary);">${window.transDept(t.department)}</td>
                <td class="tc"><span class="badge badge-success">${t.days_present}</span></td>
                <td class="tc"><span class="badge badge-warning">${t.days_late}</span></td>
                <td class="tc"><span class="badge badge-danger">${t.days_absent}</span></td>
                <td class="tc">${res.total_working_days}</td>
                <td class="tc" style="font-weight:bold; color:var(--primary);">${t.total_hours_label || '0h 0m'}</td>
                <td class="tc" style="font-weight:bold;color:${rateColor};">${t.attendance_rate}%</td>
            </tr>`;
        });
    } catch(e) { console.error(e); }
}

// ── 3. Absent Report ─────────────────────────────────
async function loadAbsent(params) {
    const tbody = document.getElementById('absent-tbody');
    tbody.innerHTML = buildGenericSkeletons(8, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.absent') }}?${params.toString()}`);
        if (document.getElementById('hudRecordCount')) document.getElementById('hudRecordCount').textContent = res.total;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:2rem;">{{ __('No absent records found.') }}</td></tr>`;
            return;
        }
        const currentLocale = document.documentElement.lang || 'en';
        res.rows.forEach((r, i) => {
            const mainName = currentLocale === 'km' ? (r.teacher_name_kh || r.teacher_name) : r.teacher_name;
            const subName = currentLocale === 'km' ? r.teacher_name : (r.teacher_name_kh || '');
            const editBtn = `<button onclick='openManualForAbsent(${JSON.stringify({teacher_db_id: r.teacher_db_id, date: r.absent_date_raw, teacher_name: r.teacher_name, department: r.department})})' class="btn btn-sm" title="{{ __('Add Manual Attendance') }}" style="background:rgba(var(--primary-rgb),0.12); border:1px solid rgba(var(--primary-rgb),0.25); color:var(--primary); border-radius:0.5rem; padding:0.3rem 0.6rem; display:inline-flex; align-items:center; gap:0.3rem; font-size:0.78rem; font-weight:700;">
                <i class="ph ph-plus-circle"></i> {{ __('Manual') }}
            </button>`;
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: ${(i * 0.02).toFixed(2)}s">
                <td class="tc">${i + 1}</td>
                <td class="tc">${r.teacher_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary);">${mainName}</div>
                    <div style="font-weight:600; opacity: 0.8;">${subName}</div>
                </td>
                <td>${window.transDept(r.department)}</td>
                <td>${r.absent_date}</td>
                <td>${r.day_name}</td>
                <td><span class="badge badge-danger">${r.status}</span></td>
                <td>${r.remark}</td>
                <td class="col-action tc">${editBtn}</td>
            </tr>`;
        });
    } catch(e) {
        console.error('loadAbsent error:', e);
        if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--danger);">Error: ${e.message}</td></tr>`;
    }
}

// ── 4. Late Report ───────────────────────────────────
async function loadLate(params) {
    const tbody = document.getElementById('late-tbody');
    tbody.innerHTML = buildGenericSkeletons(8, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.late') }}?${params.toString()}`);
        if (document.getElementById('hudRecordCount')) document.getElementById('hudRecordCount').textContent = res.total;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:2rem;">{{ __('No late records found.') }}</td></tr>`;
            return;
        }
        const currentLocale = document.documentElement.lang || 'en';
        res.rows.forEach((r, i) => {
            const mainName = currentLocale === 'km' ? (r.teacher_name_kh || r.teacher_name) : r.teacher_name;
            const subName = currentLocale === 'km' ? r.teacher_name : (r.teacher_name_kh || '');
            // Build a record object compatible with openEditModal
            const recForEdit = {
                id: r.id,
                date: r.date,
                teacher: { name: r.teacher_name, name_kh: r.teacher_name_kh, employee_id: r.teacher_id, department: r.department },
                morning_in: r.morning_in, morning_out: r.morning_out, morning_status: r.morning_status,
                afternoon_in: r.afternoon_in, afternoon_out: r.afternoon_out, afternoon_status: r.afternoon_status,
                manual_note: r.manual_note
            };
            const editBtn = `<button onclick='openEditModal(${JSON.stringify(recForEdit).replace(/'/g, "&#39;")})' class="btn btn-sm" title="{{ __('Edit') }}" style="background:rgba(var(--warning-rgb),0.12); border:1px solid rgba(var(--warning-rgb),0.3); color:var(--warning); border-radius:0.5rem; padding:0.3rem 0.6rem; display:inline-flex; align-items:center; gap:0.3rem; font-size:0.78rem; font-weight:700;">
                <i class="ph ph-pencil-simple"></i> {{ __('Edit') }}
            </button>`;
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: ${(i * 0.02).toFixed(2)}s">
                <td class="tc">${i + 1}</td>
                <td class="tc">${r.teacher_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary);">${mainName}</div>
                    <div style="font-weight:600; opacity: 0.8;">${subName}</div>
                </td>
                <td>${window.transDept(r.department)}</td>
                <td>${r.date}</td>
                <td style="color:var(--warning);font-weight:bold;">${r.check_in}</td>
                <td>${r.expected_time}</td>
                <td><span class="badge badge-warning">${r.late_minutes}</span></td>
                <td class="col-action tc">${editBtn}</td>
            </tr>`;
        });
    } catch(e) {
        console.error('loadLate error:', e);
        if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--danger);">Error: ${e.message}</td></tr>`;
    }
}

// ── 5. Leave Report ──────────────────────────────────
async function loadLeave(params) {
    const tbody = document.getElementById('leave-tbody');
    tbody.innerHTML = buildGenericSkeletons(8, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.leave') }}?${params.toString()}`);
        if (document.getElementById('hudRecordCount')) document.getElementById('hudRecordCount').textContent = res.total;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;">{{ __('No leave records found.') }}</td></tr>`;
            return;
        }
        res.rows.forEach((r, i) => {
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: ${(i * 0.02).toFixed(2)}s">
                <td class="tc">${i + 1}</td>
                <td class="tc">${r.teacher_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary);">${r.teacher_name_kh || ''}</div>
                    <div style="font-weight:600; opacity: 0.8;">${r.teacher_name}</div>
                </td>
                <td>${window.transDept(r.department)}</td>
                <td>${r.leave_date}</td>
                <td>${r.leave_type}</td>
                <td><span class="badge badge-info">${r.status}</span></td>
                <td>${r.remark}</td>
            </tr>`;
        });
    } catch(e) { console.error(e); }
}

// ── 6. Individual Teacher Report ─────────────────────
async function loadIndividual(params) {
    const tbody = document.getElementById('indiv-tbody');
    const tfoot = document.getElementById('indiv-tfoot');
    tbody.innerHTML = buildGenericSkeletons(7, 5);
    tfoot.style.display = 'none';
    
    if (!params.get('teacher_id')) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;">{{ __('Please select a specific teacher from the dropdown.') }}</td></tr>`;
        if (document.getElementById('hudRecordCount')) document.getElementById('hudRecordCount').textContent = '0';
        return;
    }
    
    try {
        const res = await window.fetchApi(`{{ route('api.reports.individual') }}?${params.toString()}`);
        if (res.error) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;">${res.error}</td></tr>`;
            return;
        }
        if (document.getElementById('hudRecordCount')) document.getElementById('hudRecordCount').textContent = res.rows.length;
        
        document.getElementById('indiv-title').innerHTML = `<i class="ph ph-user-focus" style="margin-right:0.4rem;"></i>${res.teacher.name} (${res.teacher.employee_id}) - ${window.transDept(res.teacher.department)}`;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;">{{ __('No records found.') }}</td></tr>`;
            return;
        }
        res.rows.forEach((r, i) => {
            const bc = r.status === 'present' ? 'success' : (r.status === 'late' ? 'warning' : (r.status === 'leave' ? 'info' : 'danger'));
            const statusLabel = r.status.charAt(0).toUpperCase() + r.status.slice(1);
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: ${(i * 0.02).toFixed(2)}s">
                <td>${r.date}</td>
                <td>${r.day_name}</td>
                <td>${r.check_in}</td>
                <td>${r.check_out}</td>
                <td style="font-weight:bold;color:var(--primary);">${r.working_hours}</td>
                <td><span class="badge badge-${bc}">${statusLabel}</span></td>
                <td>${r.remark}</td>
            </tr>`;
        });
        
        // Show summary footer
        tfoot.style.display = '';
        document.getElementById('indiv-hours').textContent = res.summary.total_hours;
        document.getElementById('indiv-summary').innerHTML = `
            <span style="color:var(--success)">P: ${res.summary.present}</span> | 
            <span style="color:var(--warning)">L: ${res.summary.late}</span> | 
            <span style="color:var(--danger)">A: ${res.summary.absent}</span> | 
            <span style="color:var(--info)">Lv: ${res.summary.leave}</span> | 
            <span style="color:var(--text-primary);font-weight:900;">Rate: ${res.summary.attendance_rate}%</span>
        `;
    } catch(e) { console.error(e); }
}

function updateTeacherFilter() {
    const dept = document.getElementById('departmentFilter').value;
    const teacherSelect = document.getElementById('teacherFilter');
    const options = teacherSelect.querySelectorAll('option[data-dept]');
    
    let currentSelectedVisible = false;
    const currentVal = teacherSelect.value;

    options.forEach(opt => {
        const match = !dept || opt.dataset.dept === dept;
        opt.style.display = match ? '' : 'none';
        if (match && opt.value === currentVal) currentSelectedVisible = true;
    });

    if (!currentSelectedVisible && currentVal !== '') {
        teacherSelect.value = '';
    }
}

document.getElementById('departmentFilter').addEventListener('change', updateTeacherFilter);

document.addEventListener('DOMContentLoaded', () => {
    loadAll();
    updateTeacherFilter();
});
function exportPdf() { 
    const type = document.getElementById('reportTypeFilter').value;
    const fromVal = document.getElementById('dateFrom').value;
    const toVal = document.getElementById('dateTo').value;
    
    // Save original title and set document title to desired PDF filename
    const originalTitle = document.title;
    document.title = `attendance_${type}_report_${fromVal}_to_${toVal}`;
    
    // Trigger Vector A4 Print / PDF Engine
    printReport();
    
    // Restore document title after print dialog closes
    setTimeout(() => {
        document.title = originalTitle;
    }, 1000);
}

function printReport() {
    const type = document.getElementById('reportTypeFilter').value;
    
    // Remove print-active class from all panels so ONLY selected panel prints
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.remove('print-active');
    });
    
    const activePanel = document.getElementById('panel-' + type);
    if (activePanel) {
        activePanel.classList.add('print-active');
    }
    
    // Synchronize print header text (title, range, dept, teacher, stats)
    const typeLabels = {
        daily: '{{ __('Daily Attendance Report') }}',
        monthly: '{{ __('Monthly Attendance Report') }}',
        absent: '{{ __('Absent Report') }}',
        late: '{{ __('Late Report') }}',
        leave: '{{ __('Leave Report') }}',
        individual: '{{ __('Individual Teacher Report') }}',
        department: '{{ __('Department Report') }}',
    };
    const subtitle = document.querySelector('.print-header .subtitle');
    if (subtitle) subtitle.textContent = typeLabels[type] || '{{ __('Attendance Report') }}';

    const pPresent  = document.getElementById('psum-present');
    const pLate     = document.getElementById('psum-late');
    const pAbsent   = document.getElementById('psum-absent');
    const pWorkdays = document.getElementById('psum-workdays');
    if (pPresent)  pPresent.textContent  = document.getElementById('sum-present')?.textContent  ?? '—';
    if (pLate)     pLate.textContent     = document.getElementById('sum-late')?.textContent     ?? '—';
    if (pAbsent)   pAbsent.textContent   = document.getElementById('sum-absent')?.textContent   ?? '—';
    if (pWorkdays) pWorkdays.textContent = document.getElementById('sum-workdays')?.textContent ?? '—';
    
    const printRangeEl = document.getElementById('print-range');
    if (printRangeEl) {
        const from = document.getElementById('dateFrom').value;
        const to = document.getElementById('dateTo').value;
        const fmt = s => { const p = s.split('-'); return p.length===3 ? `${p[2]}-${p[1]}-${p[0]}` : s; };
        printRangeEl.textContent = `${fmt(from)} — ${fmt(to)}`;
    }
    const deptEl = document.getElementById('print-dept-val');
    if (deptEl) {
        const dept = document.getElementById('departmentFilter').value;
        deptEl.textContent = (window.transDept && dept) ? window.transDept(dept) : (dept || '{{ __('All Departments') }}');
    }
    const teacherEl = document.getElementById('print-teacher-val');
    const teacherSelect = document.getElementById('teacherFilter');
    if (teacherEl) {
        const teacherId = document.getElementById('teacherFilter').value;
        if (teacherId && teacherSelect && teacherSelect.selectedIndex >= 0) {
            teacherEl.textContent = teacherSelect.options[teacherSelect.selectedIndex].text;
        } else {
            teacherEl.textContent = '{{ __('All Teachers') }}';
        }
    }
    
    window.print();
}

function exportExcel() {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    const dept = document.getElementById('departmentFilter').value;
    const teacherId = document.getElementById('teacherFilter').value;
    const type = document.getElementById('reportTypeFilter').value;
    
    const params = new URLSearchParams({ from, to, type });
    if (dept) params.append('department', dept);
    if (teacherId) params.append('teacher_id', teacherId);
    
    window.location.href = `{{ route('api.reports.export') }}?${params.toString()}`;
}
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function openManualModal() {
    document.getElementById('manualAttendanceForm').reset();
    document.getElementById('manual_date').value = document.getElementById('dateTo').value || new Date().toISOString().split('T')[0];
    const reportDept = document.getElementById('departmentFilter').value;
    if (reportDept) {
        document.getElementById('manual_department_id').value = reportDept;
    }
    filterManualTeachers();
    openModal('manualAttendanceModal');
}

function openManualForAbsent(info) {
    // Pre-fill manual modal for a specific absent teacher/date
    document.getElementById('manualAttendanceForm').reset();
    document.getElementById('manual_date').value = info.date;
    if (info.department) {
        document.getElementById('manual_department_id').value = info.department;
        filterManualTeachers();
    }
    if (info.teacher_db_id) {
        document.getElementById('manual_teacher_id').value = info.teacher_db_id;
    }
    openModal('manualAttendanceModal');
}

function filterManualTeachers() {
    const dept = document.getElementById('manual_department_id').value;
    const select = document.getElementById('manual_teacher_id');
    const options = select.querySelectorAll('option[data-dept]');
    
    let currentSelectedVisible = false;
    const currentVal = select.value;

    options.forEach(opt => {
        const match = !dept || opt.dataset.dept === dept;
        opt.style.display = match ? '' : 'none';
        if (match && opt.value === currentVal) currentSelectedVisible = true;
    });

    if (!currentSelectedVisible && currentVal !== '') {
        select.value = '';
    }
}
function openEditModal(record) {
    document.getElementById('editAttendanceForm').reset();
    document.getElementById('edit_attendance_id').value = record.id;
    document.getElementById('edit_teacher_name').textContent = record.teacher.name;
    document.getElementById('edit_date').textContent = record.date;
    
    const parseTime = (t) => {
        if (!t) return '';
        const match = t.match(/(\d+):(\d+)\s*(AM|PM)/i);
        if (!match) return '';
        let [_, h, m, p] = match;
        h = parseInt(h, 10);
        if (p.toUpperCase() === 'PM' && h < 12) h += 12;
        if (p.toUpperCase() === 'AM' && h === 12) h = 0;
        return String(h).padStart(2, '0') + ':' + m;
    };
    
    document.getElementById('edit_morning_in').value = parseTime(record.morning_in);
    document.getElementById('edit_morning_out').value = parseTime(record.morning_out);
    document.getElementById('edit_morning_status').value = record.morning_status || 'none';
    
    document.getElementById('edit_afternoon_in').value = parseTime(record.afternoon_in);
    document.getElementById('edit_afternoon_out').value = parseTime(record.afternoon_out);
    document.getElementById('edit_afternoon_status').value = record.afternoon_status || 'none';
    
    document.getElementById('edit_reason').value = record.manual_note || '';
    
    openModal('editAttendanceModal');
}

async function submitManualAttendance(e) {
    e.preventDefault();
    const payload = {
        teacher_id: document.getElementById('manual_teacher_id').value,
        date: document.getElementById('manual_date').value,
        morning_in: document.getElementById('manual_morning_in').value,
        morning_out: document.getElementById('manual_morning_out').value,
        morning_status: document.getElementById('manual_morning_status').value,
        afternoon_in: document.getElementById('manual_afternoon_in').value,
        afternoon_out: document.getElementById('manual_afternoon_out').value,
        afternoon_status: document.getElementById('manual_afternoon_status').value,
        reason: document.getElementById('manual_reason').value,
        _token: '{{ csrf_token() }}'
    };
    try {
        const res = await window.fetchApi(`{{ route('api.reports.attendance.manual') }}`, {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        if (res.success) {
            window.showToast("{{ __('Attendance saved successfully') }}", 'success');
            closeModal('manualAttendanceModal');
            loadAll();
        } else {
            window.showToast(res.message || 'Error saving attendance', 'error');
        }
    } catch(err) {
        // fetchApi displays error toast automatically
    }
}

async function submitEditAttendance(e) {
    e.preventDefault();
    const id = document.getElementById('edit_attendance_id').value;
    const payload = {
        morning_in: document.getElementById('edit_morning_in').value,
        morning_out: document.getElementById('edit_morning_out').value,
        morning_status: document.getElementById('edit_morning_status').value,
        afternoon_in: document.getElementById('edit_afternoon_in').value,
        afternoon_out: document.getElementById('edit_afternoon_out').value,
        afternoon_status: document.getElementById('edit_afternoon_status').value,
        reason: document.getElementById('edit_reason').value,
        _token: '{{ csrf_token() }}'
    };
    try {
        const res = await window.fetchApi(`/api-web/reports/attendance/${id}`, {
            method: 'PUT',
            body: JSON.stringify(payload)
        });
        if (res.success) {
            window.showToast("{{ __('Attendance updated successfully') }}", 'success');
            closeModal('editAttendanceModal');
            loadAll();
        } else {
            window.showToast(res.message || 'Error updating attendance', 'error');
        }
    } catch(err) {
        // fetchApi displays error toast automatically
    }
}

async function viewHistory(id) {
    openModal('attendanceHistoryModal');
    const content = document.getElementById('historyModalContent');
    content.innerHTML = '<div style="text-align:center; color:var(--text-secondary);">Loading history...</div>';
    try {
        const res = await fetch(`/api-web/reports/attendance/${id}/history`);
        const data = await res.json();
        if (!data.history || data.history.length === 0) {
            content.innerHTML = '<div style="text-align:center; color:var(--text-secondary);">No history found.</div>';
            return;
        }
        
        let html = '';
        data.history.forEach(log => {
            const isManual = log.action === 'Manual Attendance';
            const actionColor = isManual ? 'var(--success,#10b981)' : 'var(--warning,#f59e0b)';
            const actionIcon  = isManual ? 'ph-pencil-simple-line' : 'ph-pencil-simple';
            const actionLabel = isManual ? '{{ __("Admin Entry") }}' : '{{ __("Admin Edit") }}';
            html += `<div style="margin-bottom:1rem; padding:1rem; border:1px solid var(--border); border-radius:0.5rem; background:rgba(255,255,255,0.02);">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span style="font-size:0.7rem; font-weight:800; color:${actionColor}; background:rgba(0,0,0,0.1); padding:0.15rem 0.5rem; border-radius:999px; display:inline-flex; align-items:center; gap:0.25rem;"><i class="${actionIcon}"></i> ${actionLabel}</span>
                    <span style="font-size:0.78rem; color:var(--text-secondary); margin-left:auto;">${log.timestamp}</span>
                </div>
                <div style="font-weight:700; margin-bottom:0.5rem;">{{ __("Reason") }}: <span style="font-weight:400;">${log.new.reason || '—'}</span></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <div style="font-weight:700; color:var(--danger); border-bottom:1px solid rgba(255,0,0,0.2); padding-bottom:0.2rem; margin-bottom:0.4rem;">{{ __("Before") }}</div>
                        <div style="font-size:0.85rem;">
                            {{ __("Morning") }}: ${log.old.morning_in||'--'} - ${log.old.morning_out||'--'} (${log.old.morning_status||''})<br>
                            {{ __("Afternoon") }}: ${log.old.afternoon_in||'--'} - ${log.old.afternoon_out||'--'} (${log.old.afternoon_status||''})
                        </div>
                    </div>
                    <div>
                        <div style="font-weight:700; color:var(--success); border-bottom:1px solid rgba(0,255,0,0.2); padding-bottom:0.2rem; margin-bottom:0.4rem;">{{ __("After") }}</div>
                        <div style="font-size:0.85rem;">
                            {{ __("Morning") }}: ${log.new.morning_in||'--'} - ${log.new.morning_out||'--'} (${log.new.morning_status||''})<br>
                            {{ __("Afternoon") }}: ${log.new.afternoon_in||'--'} - ${log.new.afternoon_out||'--'} (${log.new.afternoon_status||''})
                        </div>
                    </div>
                </div>
            </div>`;
        });
        content.innerHTML = html;
    } catch(err) {
        content.innerHTML = '<div style="text-align:center; color:var(--danger);">Error loading history.</div>';
    }
}

function exportPdf() {
    const from = document.getElementById('dateFrom').value;
    const teacherId = document.getElementById('teacherFilter').value;
    const dept = document.getElementById('departmentFilter').value;
    const month = from ? from.substring(0, 7) : '{{ now()->format("Y-m") }}';
    
    let url = `{{ route('reports.pdf') }}?month=${month}`;
    if (teacherId) url += `&teacher_id=${teacherId}`;
    if (dept) url += `&department_id=${dept}`;
    
    window.open(url, '_blank');
}
</script>
@endpush

