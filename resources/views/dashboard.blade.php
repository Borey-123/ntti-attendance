@extends('layouts.app')

@section('title', __('Today\'s Attendance'))

@push('styles')
<style>
    @keyframes hiBounce {
        0%, 100% { transform: rotate(12deg) scale(1); }
        50% { transform: rotate(8deg) scale(1.15); box-shadow: 0 0 15px var(--primary); }
    }

    /* Stat Value Pulse */
    .stat-value.changed {
        animation: valuePulse 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes valuePulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); color: var(--primary); text-shadow: 0 0 10px var(--primary); }
        100% { transform: scale(1); }
    }

    /* Quick Action Icon Animations */
    .btn-quick-action:hover .qa-icon i {
        animation: iconBounce 0.5s ease-in-out;
    }
    .btn-quick-action:hover .ph-heartbeat {
        animation: iconPulse 1s ease-in-out infinite !important;
    }
    .btn-quick-action:hover .ph-file-pdf {
        animation: iconFloat 1.5s ease-in-out infinite !important;
    }
    .btn-quick-action:hover .ph-identification-card {
        animation: iconShake 0.5s ease-in-out infinite !important;
    }

    @keyframes iconBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    @keyframes iconPulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.7; }
    }
    @keyframes iconFloat {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-4px) scale(1.05); }
    }
    @keyframes iconShake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }

    /* ── ECG Pulse Scanner Widget ── */
    .ecg-hud {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--bg-dark);
        border: 1px solid rgba(var(--primary-rgb), 0.25);
        border-radius: 1rem;
        padding: 0.5rem 1rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 18px rgba(var(--primary-rgb), 0.08), inset 0 0 16px rgba(var(--primary-rgb), 0.03);
        min-width: 200px;
    }
    .ecg-hud::before {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            90deg,
            transparent,
            transparent 19px,
            rgba(var(--primary-rgb), 0.03) 19px,
            rgba(var(--primary-rgb), 0.03) 20px
        ),
        repeating-linear-gradient(
            0deg,
            transparent,
            transparent 9px,
            rgba(var(--primary-rgb), 0.03) 9px,
            rgba(var(--primary-rgb), 0.03) 10px
        );
        pointer-events: none;
    }
    .ecg-canvas-wrap {
        position: relative;
        flex: 1;
        height: 44px;
    }
    .ecg-canvas-wrap canvas {
        display: block;
        width: 100%;
        height: 100%;
    }
    .ecg-label {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
        flex-shrink: 0;
        z-index: 1;
    }
    .ecg-title {
        font-size: 0.58rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: rgba(var(--primary-rgb), 0.6);
        line-height: 1;
    }
    .ecg-stat {
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--primary);
        text-shadow: 0 0 10px rgba(var(--primary-rgb), 0.5);
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .ecg-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--primary);
        box-shadow: 0 0 8px var(--primary);
        animation: ecgBlink 1s ease-in-out infinite;
        margin-top: 2px;
    }
    @keyframes ecgBlink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.3; transform: scale(0.7); }
    }
</style>
@endpush

@section('content')

@php
    // Departments are passed from the controller as full objects
@endphp

{{-- ─── Welcome Hero Section ─────────────────────────── --}}
<div class="welcome-banner animate-fade-up" style="margin-bottom: 2rem;">

    <div class="welcome-content">
        <div class="welcome-text">
            @php
                $hour = now()->hour;
                if ($hour < 12) $greeting = __('Good Morning, Administrator');
                elseif ($hour < 17) $greeting = __('Good Afternoon, Administrator');
                else $greeting = __('Good Evening, Administrator');
            @endphp
            <h1 id="greetingText" style="display: flex; align-items: center; gap: 0.75rem;">
                {{ $greeting }}
                <span class="hi-badge" style="display: inline-flex; align-items: center; justify-content: center; background: var(--primary); color: #000; font-size: 0.7rem; font-weight: 900; padding: 0.2rem 0.5rem; border-radius: 0.5rem; transform: rotate(12deg); animation: hiBounce 2s ease-in-out infinite;">{{ __('Hello') }}</span>
            </h1>
            <p>{{ __('You have :count teachers across :depts departments under management.', ['count' => $totalTeachers ?? 0, 'depts' => $totalDepartments ?? 0]) }}</p>
        </div>

        {{-- Permanent Recent Scan Card --}}
        <div id="recentScanCard" style="display: none; background: var(--bg-dark); border: 1px solid rgba(var(--primary-rgb), 0.3); padding: 1rem; border-radius: 1.25rem; min-width: 280px; align-items: center; gap: 1rem; animation: fadeUp 0.5s ease-out;">
            <div id="recentScanPhoto" style="width: 54px; height: 54px; border-radius: 14px; overflow: hidden; border: 2px solid var(--primary); flex-shrink: 0;"></div>
            <div style="flex: 1;">
                <div id="recentScanLabel" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: var(--primary); letter-spacing: 0.5px; margin-bottom: 2px;">{{ __('Last Scan') }}</div>
                <div id="recentScanName" style="font-weight: 800; font-size: 1.1rem; line-height: 1.1; color: var(--text-primary);"></div>
                <div id="recentScanDept" style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;"></div>
                <div id="recentScanTime" style="font-size: 0.85rem; font-weight: 700; color: var(--primary); margin-top: 4px; font-family: 'JetBrains Mono', monospace;"></div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <i class="ph ph-check-circle" style="font-size: 1.25rem;"></i>
            </div>
        </div>

        <div class="welcome-stats">
            <div class="w-stat">
                <span class="w-label">{{ __('System Status') }}</span>
                <span class="w-val" style="color:var(--success);">
                    <i class="ph ph-shield-check"></i> {{ __('Operational') }}
                </span>
            </div>
            <div class="w-divider"></div>
            <div class="w-stat">
                <span class="w-label">{{ __('Device Status') }}</span>
                <span class="w-val" id="deviceLabelHero">
                    <i class="ph ph-broadcast"></i> {{ __('Connecting...') }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ─── Quick Actions ─────────────────────────────── --}}
<div class="quick-actions animate-fade-up" style="display:flex; gap:1rem; margin-bottom:2rem; animation-delay: 0.05s; overflow-x: auto; padding-top: 1rem; padding-bottom: 1rem; margin-top: -1rem; scrollbar-width: none; position: relative; z-index: 10;">
    <a href="{{ route('teachers.index') }}?action=register" class="btn-quick-action">
        <div class="qa-icon"><i class="ph ph-user-plus"></i></div>
        <div class="qa-text">
            <span class="qa-title">{{ __('Register Teacher') }}</span>
            <span class="qa-desc">{{ __('Add new personnel') }}</span>
        </div>
    </a>
    <a href="{{ route('rfid.index') }}?action=assign" class="btn-quick-action">
        <div class="qa-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;"><i class="ph ph-identification-card"></i></div>
        <div class="qa-text">
            <span class="qa-title">{{ __('Assign RFID') }}</span>
            <span class="qa-desc">{{ __('Manage smart cards') }}</span>
        </div>
    </a>
    <a href="{{ route('reports.index') }}" class="btn-quick-action">
        <div class="qa-icon" style="background: rgba(139,92,246,0.1); color: #8b5cf6;"><i class="ph ph-file-pdf"></i></div>
        <div class="qa-text">
            <span class="qa-title">{{ __('Daily Report') }}</span>
            <span class="qa-desc">{{ __('View and export') }}</span>
        </div>
    </a>
    <a href="{{ route('settings.index') }}" class="btn-quick-action">
        <div class="qa-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;"><i class="ph ph-heartbeat"></i></div>
        <div class="qa-text">
            <span class="qa-title">{{ __('System Health') }}</span>
            <span class="qa-desc">{{ __('Device status & config') }}</span>
        </div>
    </a>
</div>



{{-- ─── Consolidated Stats Panels ────────────────────── --}}
<div class="stats-panels animate-fade-up" style="animation-delay: 0.1s;" id="statsGrid">

    {{-- ① Today's Attendance Panel --}}
    <div class="panel-today">
        <div class="panel-header">
            <div class="panel-title">
                <i class="ph ph-calendar-check"></i>
                <span>{{ __("Today's Attendance") }}</span>
            </div>
            <div class="panel-live-dot">
                <span class="live-dot-anim"></span>
                <span>{{ __('Live') }}</span>
            </div>
        </div>

        <div class="panel-body-today">
            {{-- Sparkline Chart --}}
            <div class="panel-sparkline">
                <div class="sparkline-value" id="stat-rate">{{ $rate ?? 0 }}%</div>
                <div class="sparkline-label">{{ __('Attendance') }}</div>
                <canvas id="sparklineChart" class="sparkline-chart"></canvas>
            </div>

            {{-- Stat Items --}}
            <div class="panel-stat-items">
                <div class="psi stat-card" id="card-present" onclick="setDashboardFilter('present')">
                    <div class="psi-icon" style="background: rgba(16,185,129,0.1); color: #10b981;"><i class="ph ph-check-circle"></i></div>
                    <div class="psi-val" id="stat-present" style="color: #10b981;">{{ $presentCount ?? 0 }}</div>
                    <div class="psi-label">{{ __('Present') }}</div>
                    <div class="psi-tooltip">
                        <div style="font-size:0.7rem; font-weight:800; color:var(--text-muted); margin-bottom:0.5rem; text-transform:uppercase;">{{ __('Recent') }}</div>
                        <div class="psi-tooltip-item"><div class="psi-tooltip-avatar">A</div><span>Sokha M.</span></div>
                        <div class="psi-tooltip-item"><div class="psi-tooltip-avatar">B</div><span>Dara P.</span></div>
                    </div>
                </div>
                <div class="psi stat-card" id="card-late" onclick="setDashboardFilter('late')">
                    <div class="psi-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;"><i class="ph ph-clock"></i></div>
                    <div class="psi-val" id="stat-late" style="color: #f59e0b;">{{ $lateCount ?? 0 }}</div>
                    <div class="psi-label">{{ __('Late') }}</div>
                    <div class="psi-tooltip">
                        <div style="font-size:0.7rem; font-weight:800; color:var(--text-muted); margin-bottom:0.5rem; text-transform:uppercase;">{{ __('Recent') }}</div>
                        <div class="psi-tooltip-item"><div class="psi-tooltip-avatar">C</div><span>Rithy S.</span></div>
                    </div>
                </div>
                <div class="psi stat-card" id="card-absent" onclick="showShiftAbsentModal()">
                    <div class="psi-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;"><i class="ph ph-x-circle"></i></div>
                    <div class="psi-val" id="stat-absent" style="color: #ef4444;">{{ $absentCount ?? 0 }}</div>
                    <div class="psi-label">{{ __('Absent') }}</div>
                </div>
            </div>
        </div>

        {{-- Bottom row: Currently In / Out --}}
        <div class="panel-footer-row">
            <div class="pfr-item stat-card" id="card-currently_in" onclick="setDashboardFilter('currently_in')">
                <i class="ph ph-sign-in" style="color: #3b82f6;"></i>
                <span class="pfr-val" id="stat-currently-in" style="color: #3b82f6;">{{ $currentlyCheckedInCount ?? 0 }}</span>
                <span class="pfr-label">{{ __('Currently In') }}</span>
            </div>
            <div class="pfr-divider"></div>
            <div class="pfr-item stat-card" id="card-currently_out" onclick="setDashboardFilter('currently_out')">
                <i class="ph ph-sign-out" style="color: var(--text-secondary);"></i>
                <span class="pfr-val" id="stat-currently-out" style="color: var(--text-secondary);">{{ $currentlyCheckedOutCount ?? 0 }}</span>
                <span class="pfr-label">{{ __('Currently Out') }}</span>
            </div>
            <div class="pfr-divider"></div>
            <div class="pfr-item stat-card" id="card-checkins" onclick="setDashboardFilter('checkins')">
                <i class="ph ph-scan" style="color: #8b5cf6;"></i>
                <span class="pfr-val" id="stat-checkins" style="color: #8b5cf6;">{{ $checkinCount ?? 0 }}</span>
                <span class="pfr-label">{{ __('Check-ins') }}</span>
            </div>
        </div>
    </div>

    {{-- ② System Overview Panel --}}
    <div class="panel-system">
        <div class="panel-header">
            <div class="panel-title">
                <i class="ph ph-gear-six"></i>
                <span>{{ __('System Overview') }}</span>
                <span class="ph ph-heartbeat" style="color:var(--success); animation: ecgBlink 1s infinite; margin-left: 0.5rem; font-size:1.2rem;" title="System Healthy"></span>
            </div>
        </div>

        <div class="panel-system-grid">
            <div class="psg-item stat-card" id="card-total" onclick="window.location.href='{{ route('teachers.index') }}'">
                <div class="psg-icon-wrap" style="background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(59,130,246,0.05));">
                    <i class="ph ph-users" style="color: #3b82f6;"></i>
                </div>
                <div class="psg-val" id="stat-total">{{ $totalTeachers ?? 0 }} <span class="psg-trend">+2</span></div>
                <div class="psg-label">{{ __('Total Teachers') }}</div>
                <div class="psg-bg-icon"><i class="ph ph-users"></i></div>
            </div>

            <div class="psg-item stat-card" id="card-rfid" onclick="window.location.href='{{ route('rfid.index') }}'">
                <div class="psg-icon-wrap" style="background: linear-gradient(135deg, rgba(249,115,22,0.15), rgba(249,115,22,0.05));">
                    <i class="ph ph-identification-card" style="color: #f97316;"></i>
                </div>
                <div class="psg-val" id="stat-rfid" style="color: #f97316;">{{ $totalRfidTeachers ?? 0 }}</div>
                <div class="psg-label">{{ __('RFID Teachers') }}</div>
                <div class="psg-bg-icon"><i class="ph ph-identification-card" style="color: #f97316;"></i></div>
            </div>

            <div class="psg-item stat-card" id="card-departments" onclick="window.location.href='{{ route('departments.index') }}'">
                <div class="psg-icon-wrap" style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(99,102,241,0.05));">
                    <i class="ph ph-buildings" style="color: #6366f1;"></i>
                </div>
                <div class="psg-val" id="stat-depts" style="color: #6366f1;">{{ $totalDepartments ?? 0 }}</div>
                <div class="psg-label">{{ __('Total Departments') }}</div>
                <div class="psg-bg-icon"><i class="ph ph-buildings" style="color: #6366f1;"></i></div>
            </div>

            <div class="psg-item stat-card" id="card-admins" onclick="window.location.href='{{ route('settings.index') }}'">
                <div class="psg-icon-wrap" style="background: linear-gradient(135deg, rgba(var(--primary-rgb),0.15), rgba(var(--primary-rgb),0.05));">
                    <i class="ph ph-shield-check" style="color: var(--primary);"></i>
                </div>
                <div class="psg-val" style="color: var(--primary);"><span id="stat-total-admins">{{ $totalAdmins ?? 0 }}</span> <span class="psg-trend">Safe</span></div>
                <div class="psg-label">{{ __('System Admins') }}</div>
                <div class="psg-bg-icon"><i class="ph ph-shield-check" style="color: var(--primary);"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Analytics & Insights ─────────────────────────── --}}
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;" class="analytics-row">
    {{-- Trend Chart --}}
    <div class="card animate-fade-up" style="animation-delay: 0.45s;">
        <div class="card-header">
            <h3><i class="ph ph-chart-line" style="margin-right:0.4rem;"></i>{{ __('Attendance Volatility & Trend') }}</h3>
        </div>
        <div style="padding:1.5rem;">
            <canvas id="trendChart" style="max-height: 250px;"></canvas>
        </div>
    </div>

    {{-- Performance Leaderboard --}}
    <div class="card animate-fade-up" style="animation-delay: 0.5s;">
        <div class="card-header">
            <h3><i class="ph ph-medal" style="margin-right:0.4rem;"></i>{{ __('Monthly Performance') }}</h3>
        </div>
        <div style="padding:1rem;">
            {{-- Tabs for On-Time vs Late --}}
            <div style="display:flex; gap:0.5rem; margin-bottom:1rem; border-bottom:1px solid var(--border); padding-bottom:0.5rem;">
                <button class="btn btn-sm btn-primary" onclick="showPerfTab('ontime')" id="btn-tab-ontime">{{ __('On-Time') }}</button>
                <button class="btn btn-sm btn-secondary" onclick="showPerfTab('late')" id="btn-tab-late">{{ __('Late') }}</button>
            </div>

            <div id="perf-ontime">
                @foreach($topOnTime as $r)
                <div style="display:flex; justify-between; align-center; padding:0.6rem 0; border-bottom:1px solid var(--border);">
                    <div style="font-size:0.9rem;">{{ $r->teacher->name }}</div>
                    <div class="badge badge-success">{{ $r->count }} Days</div>
                </div>
                @endforeach
                @if($topOnTime->isEmpty()) <div style="color:var(--text-muted); font-size:0.8rem; text-align:center; padding:1rem;">{{ __('No data yet') }}</div> @endif
            </div>

            <div id="perf-late" style="display:none;">
                @foreach($topLate as $r)
                <div style="display:flex; justify-between; align-center; padding:0.6rem 0; border-bottom:1px solid var(--border);">
                    <div style="font-size:0.9rem;">{{ $r->teacher->name }}</div>
                    <div class="badge badge-danger">{{ $r->count }} Times</div>
                </div>
                @endforeach
                @if($topLate->isEmpty()) <div style="color:var(--text-muted); font-size:0.8rem; text-align:center; padding:1rem;">{{ __('No data yet') }}</div> @endif
            </div>
        </div>
    </div>
</div>

{{-- ─── Attendance Table ─────────────────────────────── --}}
<div class="card animate-fade-up" style="animation-delay: 0.55s;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 1.5rem 2rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <h3 style="margin: 0;"><i class="ph ph-list-bullets" style="margin-right:0.4rem;"></i>{{ __('Attendance List') }}</h3>
            <div id="liveIndicator" style="display:flex;align-items:center;gap:0.4rem;font-size:0.75rem;color:var(--text-secondary);">
                <span style="width:6px;height:6px;border-radius:50%;background:var(--success);animation:pulse 2s infinite;display:inline-block;"></span>
                {{ __('Real-time updates enabled') }}
            </div>
        </div>

        {{-- Animated ECG Scanner Widget --}}
        <div class="ecg-hud" id="ecgHud" title="{{ __('Live attendance pulse') }}">
            <div class="ecg-canvas-wrap">
                <canvas id="ecgCanvas" height="44"></canvas>
            </div>
            <div class="ecg-label">
                <span class="ecg-title">{{ __('Scan Pulse') }}</span>
                <span class="ecg-stat" id="ecgCount">{{ $totalScans ?? 0 }}</span>
                <div class="ecg-dot"></div>
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="search-wrapper" style="width: 250px;">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" name="search" id="search-input" class="form-control" style="padding-top: 0.5rem !important; padding-bottom: 0.5rem !important;"
                   placeholder="{{ __('Search teacher...') }}" value="{{ request('search') }}">
        </form>
    </div>
    <div style="overflow-x:auto; max-height: 550px; overflow-y: auto; position: relative;">
        <table class="table" id="attendance-table">
            <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <tr>
                    <th>{{ __('Teacher') }}</th>
                    <th>{{ __('Department') }}</th>
                    <th>{{ __('Morning') }}</th>
                    <th>{{ __('Afternoon') }}</th>
                    <th>{{ __('Duration') }}</th>
                    <th>{{ __('Source') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody id="attendance-body">
                @foreach($attendance as $record)
                @php
                    $completedWorkedSeconds = 0;
                    if ($record->morning_in && $record->morning_out) {
                        $mIn = \Carbon\Carbon::createFromTimeString($record->morning_in);
                        $mOut = \Carbon\Carbon::createFromTimeString($record->morning_out);
                        if ($mOut->greaterThanOrEqualTo($mIn)) {
                            $completedWorkedSeconds += $mOut->diffInSeconds($mIn);
                        }
                    }
                    if ($record->afternoon_in && $record->afternoon_out) {
                        $aIn = \Carbon\Carbon::createFromTimeString($record->afternoon_in);
                        $aOut = \Carbon\Carbon::createFromTimeString($record->afternoon_out);
                        if ($aOut->greaterThanOrEqualTo($aIn)) {
                            $completedWorkedSeconds += $aOut->diffInSeconds($aIn);
                        }
                    }

                    $activeStartTime = null;
                    if ($record->afternoon_in && !$record->afternoon_out) $activeStartTime = $record->afternoon_in;
                    elseif ($record->morning_in && !$record->morning_out) $activeStartTime = $record->morning_in;
                    
                    $initial = $record->teacher->name ? strtoupper(substr($record->teacher->name, 0, 1)) : '?';
                @endphp
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem; cursor:pointer;" onclick="openTeacherInsights({{ $record->teacher->id }})">
                            <div class="teacher-avatar">
                                @if($record->teacher->photo)
                                    <img src="{{ to_asset_url($record->teacher->photo) }}" alt="">
                                @else
                                    <div class="avatar-placeholder">{{ $initial }}</div>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--primary); font-size: 1.1rem; line-height: 1.2;" translate="no" class="notranslate">{{ app()->getLocale() == 'km' ? ($record->teacher->name_kh ?: $record->teacher->name) : $record->teacher->name }}</div>
                                <div style="font-weight:600; color:var(--text-primary); font-size: 0.85rem; opacity: 0.8;" translate="no" class="notranslate">{{ app()->getLocale() == 'km' ? $record->teacher->name : ($record->teacher->name_kh ?: '') }}</div>
                                <div style="font-size:0.75rem;color:var(--text-secondary);">{{ $record->teacher->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text-secondary);">
                        @php
                            $teacherDept = $record->teacher ? $record->teacher->department : '';
                            $deptObj = $departments->firstWhere('name', $teacherDept);
                            $deptLabel = $deptObj ? (app()->getLocale() == 'km' ? ($deptObj->name_kh ?: $deptObj->name) : $deptObj->name) : $teacherDept;
                        @endphp
                        {{ $deptLabel }}
                    </td>
                    <td>
                        @if($record->morning_in)
                            <div style="font-size:0.85rem;"><span style="color:var(--success); font-weight:700;">{{ substr($record->morning_in,0,5) }}</span> <i class="ph ph-arrow-right" style="font-size:0.7rem; opacity:0.5;"></i> {{ $record->morning_out ? substr($record->morning_out,0,5) : '?' }}</div>
                            @if($record->morning_status == 'late') <span class="badge badge-warning" style="font-size:0.65rem; padding:1px 6px;">LATE</span> @endif
                        @else — @endif
                    </td>
                    <td>
                        @if($record->afternoon_in)
                            <div style="font-size:0.85rem;"><span style="color:var(--success); font-weight:700;">{{ substr($record->afternoon_in,0,5) }}</span> <i class="ph ph-arrow-right" style="font-size:0.7rem; opacity:0.5;"></i> {{ $record->afternoon_out ? substr($record->afternoon_out,0,5) : '?' }}</div>
                            @if($record->afternoon_status == 'late') <span class="badge badge-warning" style="font-size:0.65rem; padding:1px 6px;">LATE</span> @endif
                        @else — @endif
                    </td>

                    <td>
                        @if($activeStartTime)
                            @php
                                $start = \Carbon\Carbon::createFromTimeString($activeStartTime);
                                $diff = now()->diffInSeconds($start) + $completedWorkedSeconds;
                                $h = str_pad(floor($diff / 3600), 2, '0', STR_PAD_LEFT);
                                $m = str_pad(floor(($diff % 3600) / 60), 2, '0', STR_PAD_LEFT);
                                $s = str_pad($diff % 60, 2, '0', STR_PAD_LEFT);
                                $initialDuration = "$h:$m:$s";
                            @endphp
                            <div class="runtime-timer" data-start="{{ $activeStartTime }}" data-completed="{{ $completedWorkedSeconds }}" style="font-family:'JetBrains Mono', monospace; font-weight:700; color:var(--primary); font-size:0.9rem;">
                                {{ $initialDuration }}
                                @if(str_contains($record->manual_note ?? '', '[Auto'))
                                    <i class="ph ph-robot" style="margin-left:4px; font-size:0.85rem; color:var(--text-muted);" title="{{ $record->manual_note }}"></i>
                                @endif
                            </div>
                        @elseif($completedWorkedSeconds > 0)
                            @php
                                $h = str_pad(floor($completedWorkedSeconds / 3600), 2, '0', STR_PAD_LEFT);
                                $m = str_pad(floor(($completedWorkedSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
                                $formattedCompleted = "{$h}h {$m}m";
                            @endphp
                            <div style="font-family:'JetBrains Mono', monospace; font-weight:700; color:var(--success); font-size:0.9rem; display:inline-flex; align-items:center; gap:4px;">
                                <i class="ph ph-clock" style="font-size:0.85rem;"></i> {{ $formattedCompleted }}
                                @if(str_contains($record->manual_note ?? '', '[Auto'))
                                    <i class="ph ph-robot" style="margin-left:4px; font-size:0.85rem; color:var(--text-muted);" title="{{ $record->manual_note }}"></i>
                                @endif
                            </div>
                        @else
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="color:var(--text-muted);">—</span>
                                @if(str_contains($record->manual_note ?? '', '[Auto'))
                                    <i class="ph ph-robot" style="font-size:0.85rem; color:var(--text-muted);" title="{{ $record->manual_note }}"></i>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($record->rfid_uid)
                            <span class="badge-pill info"><i class="ph ph-identification-card"></i> RFID</span>
                        @else
                            <span class="badge-pill secondary"><i class="ph ph-hand-tap"></i> Manual</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-pill success">{{ __('Present') }}</span>
                    </td>
                </tr>
                @endforeach

                @foreach($absentTeachers as $teacher)
                @php $initial = $teacher->name ? strtoupper(substr($teacher->name, 0, 1)) : '?'; @endphp
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem; cursor:pointer;" onclick="openTeacherInsights({{ $teacher->id }})">
                            <div class="teacher-avatar grayscale">
                                @if($teacher->photo)
                                    <img src="{{ to_asset_url($teacher->photo) }}" alt="">
                                @else
                                    <div class="avatar-placeholder">{{ $initial }}</div>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--primary); font-size: 1.1rem; line-height: 1.2;" translate="no" class="notranslate">{{ app()->getLocale() == 'km' ? ($teacher->name_kh ?: $teacher->name) : $teacher->name }}</div>
                                <div style="font-weight:600; color:var(--text-secondary); font-size: 0.85rem;" translate="no" class="notranslate">{{ app()->getLocale() == 'km' ? $teacher->name : ($teacher->name_kh ?: '') }}</div>
                                <div style="font-size:0.75rem;color:var(--text-muted);">{{ $teacher->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text-secondary);">
                        @php
                            $teacherDept = $teacher ? $teacher->department : '';
                            $deptObj = $departments->firstWhere('name', $teacherDept);
                            $deptLabel = $deptObj ? (app()->getLocale() == 'km' ? ($deptObj->name_kh ?: $deptObj->name) : $deptObj->name) : $teacherDept;
                        @endphp
                        {{ $deptLabel }}
                    </td>
                    <td style="color:var(--text-muted);">—</td>
                    <td style="color:var(--text-muted);">—</td>

                    <td style="color:var(--text-muted);">—</td>
                    <td><span class="badge-pill secondary">—</span></td>
                    <td><span class="badge-pill danger">{{ __('Absent') }}</span></td>
                </tr>
                @endforeach

                @if($attendance->isEmpty() && $absentTeachers->isEmpty())
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted);">
                        <i class="ph ph-users" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                        {{ __('No teachers found for today.') }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>


{{-- ECG Pulse Scanner JS --}}
<script>
(function() {
    const canvas = document.getElementById('ecgCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = 180, H = 44;
    canvas.width = W;
    canvas.height = H;

    // ECG waveform shape (one heartbeat cycle as normalized points)
    const ecgShape = [
        [0,0],[0.06,0],[0.08,0.08],[0.10,0],[0.12,0],
        [0.20,0.05],[0.28,-0.7],[0.33,1],[0.38,-0.3],
        [0.42,0],[0.48,0.08],[0.55,0.06],[0.62,0],[1,0]
    ];

    let points = Array(W).fill(H / 2);
    let phase = 0;
    let beatTimer = 0;
    let isBeat = false;
    let beatSpeed = 1.5; // px per frame
    let glowAlpha = 0;

    function getEcgY(t) {
        // interpolate through shape
        for (let i = 0; i < ecgShape.length - 1; i++) {
            const [x0, y0] = ecgShape[i];
            const [x1, y1] = ecgShape[i + 1];
            if (t >= x0 && t <= x1) {
                const r = (t - x0) / (x1 - x0);
                return (y0 + r * (y1 - y0));
            }
        }
        return 0;
    }

    function triggerBeat() {
        isBeat = true;
        beatTimer = 0;
        glowAlpha = 1;
    }

    function drawFrame() {
        ctx.clearRect(0, 0, W, H);

        // Scroll points left
        if (isBeat) {
            const t = beatTimer / (W * 0.4);
            const y = getEcgY(t) * (H * 0.42) + H / 2;
            points.push(y);
            beatTimer += beatSpeed;
            if (t >= 1) { isBeat = false; }
        } else {
            points.push(H / 2 + (Math.random() - 0.5) * 1.5); // tiny baseline noise
        }
        if (points.length > W) points.shift();

        // Fade glow
        if (glowAlpha > 0) glowAlpha -= 0.02;

        // Primary RGB from CSS var (approx green)
        const pr = getComputedStyle(document.documentElement).getPropertyValue('--primary-rgb').trim() || '0,212,160';

        // Glow layer
        if (glowAlpha > 0.05) {
            ctx.save();
            ctx.shadowColor = `rgba(${pr},${glowAlpha})`;
            ctx.shadowBlur = 18;
        }

        // Trail gradient: fades toward left
        const grad = ctx.createLinearGradient(0, 0, W, 0);
        grad.addColorStop(0, `rgba(${pr},0)`);
        grad.addColorStop(0.6, `rgba(${pr},0.4)`);
        grad.addColorStop(1, `rgba(${pr},1)`);

        ctx.beginPath();
        ctx.moveTo(0, points[0]);
        for (let i = 1; i < points.length; i++) {
            ctx.lineTo(i, points[i]);
        }
        ctx.strokeStyle = grad;
        ctx.lineWidth = 1.8;
        ctx.lineJoin = 'round';
        ctx.stroke();

        if (glowAlpha > 0.05) ctx.restore();

        // Moving dot at tip
        ctx.beginPath();
        ctx.arc(points.length - 1, points[points.length - 1], 3, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${pr},0.9)`;
        ctx.shadowColor = `rgba(${pr},0.8)`;
        ctx.shadowBlur = 8;
        ctx.fill();
        ctx.shadowBlur = 0;
    }

    // Auto-trigger a beat every ~3 seconds to show idle pulse
    function autoBeat() {
        triggerBeat();
        // randomize next beat slightly
        setTimeout(autoBeat, 2800 + Math.random() * 800);
    }
    setTimeout(autoBeat, 600);

    // Also trigger when attendance data updates
    window.ecgTriggerBeat = function(count) {
        const el = document.getElementById('ecgCount');
        if (el && count !== undefined) el.textContent = count;
        triggerBeat();
    };

    (function loop() {
        drawFrame();
        requestAnimationFrame(loop);
    })();
})();
</script>


<!-- SHIFT ABSENT MODAL -->
<div class="modal-overlay" id="shiftAbsentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card, #fff); width:560px; max-width:95%; border-radius:1rem; padding:2rem; box-shadow:0 10px 25px rgba(0,0,0,0.2); max-height:85vh; display:flex; flex-direction:column;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h3 style="margin:0; color:var(--text-main, #333);"><i class="ph ph-x-circle" style="color:var(--danger, #ef4444); margin-right:0.5rem;"></i> {{ __('Absent Teachers by Shift') }}</h3>
            <button onclick="closeShiftAbsentModal()" style="background:none; border:none; font-size:1.5rem; color:var(--text-muted, #888); cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>
        <div style="overflow-y:auto; flex:1; padding-right:0.5rem;">

            {{-- Morning Shift Absent --}}
            <h4 style="display:flex; align-items:center; gap:0.5rem; color:var(--warning, #f59e0b); border-bottom:1px solid var(--border-color, #eee); padding-bottom:0.5rem; margin-bottom:1rem;">
                <i class="ph ph-sun-dim"></i> {{ __('Morning Shift') }}
                <span style="margin-left:auto; background:rgba(245,158,11,0.15); color:var(--warning,#f59e0b); border-radius:999px; padding:0.1rem 0.6rem; font-size:0.8rem; font-weight:800;" id="modal-morning-count">{{ isset($morningAbsentTeachers) ? $morningAbsentTeachers->count() : 0 }}</span>
            </h4>
            <div id="modal-morning-list" style="margin-bottom:2rem; display:flex; flex-direction:column; gap:0.4rem;">
                @forelse($morningAbsentTeachers ?? [] as $t)
                <div style="display:flex; align-items:center; gap:0.5rem; padding:0.3rem 0.5rem; border-radius:0.5rem; background:rgba(255,255,255,0.03);"><i class="ph ph-user" style="color:var(--text-secondary);"></i> {{ $t->name }}</div>
                @empty
                <div style="color:var(--text-muted,#888); font-style:italic; font-size:0.85rem;">{{ __('All checked in') }}</div>
                @endforelse
            </div>

            {{-- Afternoon Shift Absent --}}
            <h4 style="display:flex; align-items:center; gap:0.5rem; color:var(--primary, #3b82f6); border-bottom:1px solid var(--border-color, #eee); padding-bottom:0.5rem; margin-bottom:1rem;">
                <i class="ph ph-cloud-sun"></i> {{ __('Afternoon Shift') }}
                <span style="margin-left:auto; background:rgba(59,130,246,0.15); color:var(--primary,#3b82f6); border-radius:999px; padding:0.1rem 0.6rem; font-size:0.8rem; font-weight:800;" id="modal-afternoon-count">{{ isset($afternoonAbsentTeachers) ? $afternoonAbsentTeachers->count() : 0 }}</span>
            </h4>
            <div id="modal-afternoon-list" style="margin-bottom:2rem; display:flex; flex-direction:column; gap:0.4rem;">
                @forelse($afternoonAbsentTeachers ?? [] as $t)
                <div style="display:flex; align-items:center; gap:0.5rem; padding:0.3rem 0.5rem; border-radius:0.5rem; background:rgba(255,255,255,0.03);"><i class="ph ph-user" style="color:var(--text-secondary);"></i> {{ $t->name }}</div>
                @empty
                <div style="color:var(--text-muted,#888); font-style:italic; font-size:0.85rem;">{{ __('All checked in') }}</div>
                @endforelse
            </div>

            {{-- Full Day Absent (no scan at all) --}}
            @php $fulldayAbsent = $absentTeachers ?? collect(); @endphp
            <h4 style="display:flex; align-items:center; gap:0.5rem; color:var(--danger, #ef4444); border-bottom:1px solid var(--border-color, #eee); padding-bottom:0.5rem; margin-bottom:1rem;">
                <i class="ph ph-calendar-x"></i> {{ __('Full Day (No Scan)') }}
                <span style="margin-left:auto; background:rgba(239,68,68,0.15); color:var(--danger,#ef4444); border-radius:999px; padding:0.1rem 0.6rem; font-size:0.8rem; font-weight:800;" id="modal-fullday-count">{{ $fulldayAbsent->count() }}</span>
            </h4>
            <div id="modal-fullday-list" style="display:flex; flex-direction:column; gap:0.4rem;">
                @forelse($fulldayAbsent as $t)
                <div style="display:flex; align-items:center; gap:0.5rem; padding:0.3rem 0.5rem; border-radius:0.5rem; background:rgba(239,68,68,0.04);"><i class="ph ph-user-minus" style="color:var(--danger,#ef4444);"></i> {{ $t->name }}</div>
                @empty
                <div style="color:var(--text-muted,#888); font-style:italic; font-size:0.85rem;">{{ __('All checked in') }}</div>
                @endforelse
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
    display: inline-block;
}

@keyframes pulse {
    0%,100% { opacity:1; }
    50%      { opacity:0.3; }
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.welcome-banner {
    background: linear-gradient(135deg,
        rgba(var(--primary-rgb), 0.1) 0%,
        rgba(var(--primary-rgb), 0.04) 100%);
    border: 1px solid rgba(var(--primary-rgb), 0.15);
    border-radius: 1.75rem;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -60px;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.07) 0%, transparent 65%);
    pointer-events: none;
}

.welcome-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    position: relative;
    z-index: 2;
}

.btn-quick-action {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: var(--bg-card);
    border: 1px solid var(--border);
    padding: 1.1rem 1.4rem;
    border-radius: 1.25rem;
    flex: 1;
    min-width: 220px;
    text-decoration: none;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
}
.btn-quick-action:hover {
    transform: translateY(-3px);
    border-color: rgba(var(--primary-rgb), 0.25);
    box-shadow: 0 10px 28px rgba(0,0,0,0.18);
    text-decoration: none;
}
.qa-icon {
    width: 44px; height: 44px;
    border-radius: 1rem;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    transition: transform 0.25s ease;
}
.btn-quick-action:hover .qa-icon {
    transform: scale(1.08);
}
.qa-text { display: flex; flex-direction: column; overflow: hidden; }
.qa-title { font-weight: 800; color: var(--text-primary); font-size: 0.95rem; margin-bottom: 0.15rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; }
.qa-desc { font-size: 0.75rem; color: var(--text-secondary); font-weight: 600; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; }


.welcome-text h1 {
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.welcome-text p {
    font-size: 1rem;
    color: var(--text-secondary);
}

.welcome-stats {
    display: flex;
    align-items: center;
    gap: 3rem;
    background: var(--bg-dark);
    padding: 1.25rem 2rem;
    border-radius: 1rem;
    border: 1px solid var(--border);
}

.w-stat {
    display: flex;
    flex-direction: column;
}

.w-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.w-val {
    font-size: 1.1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.w-divider {
    width: 1px;
    height: 40px;
    background: var(--border);
}

.search-wrapper {
    position: relative;
    width: 300px;
}

.search-wrapper i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.search-wrapper .form-control {
    padding-left: 2.75rem !important;
    border-radius: 1rem !important;
    background: var(--bg-card) !important;
}

.stat-card {
    border-radius: 1.25rem !important;
    padding: 1.5rem !important;
    border: 1px solid var(--border) !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    border-color: var(--primary) !important;
}

/* ── Consolidated Stats Panels ──────────────────── */
.stats-panels {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.panel-today, .panel-system {
    background: rgba(var(--bg-card-rgb, 22, 27, 34), 0.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1.5rem;
    overflow: hidden;
    transition: box-shadow 0.3s, border-color 0.3s;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}
.panel-today:hover, .panel-system:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.3);
    border-color: rgba(var(--primary-rgb), 0.3);
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    background: linear-gradient(90deg, rgba(var(--primary-rgb), 0.05), transparent);
}
.panel-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 800;
    font-size: 0.95rem;
    color: var(--text-primary);
    letter-spacing: 0.3px;
}
.panel-title i {
    font-size: 1.2rem;
    color: var(--primary);
    filter: drop-shadow(0 0 5px rgba(var(--primary-rgb), 0.5));
}
.panel-live-dot {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 1px;
}
.live-dot-anim {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--primary);
    animation: livePulse 2s ease-in-out infinite;
    box-shadow: 0 0 10px var(--primary);
}

/* ── Today Panel Body ── */
.panel-body-today {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1.75rem 2rem;
}

.panel-sparkline {
    position: relative;
    width: 140px;
    height: 140px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.sparkline-value {
    font-size: 2.8rem;
    font-weight: 900;
    color: var(--primary);
    line-height: 1;
    letter-spacing: -1px;
    text-shadow: 0 0 15px rgba(var(--primary-rgb), 0.4);
    margin-bottom: 0.2rem;
}
.sparkline-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 1px;
}
.sparkline-chart {
    width: 100%;
    height: 40px;
    margin-top: 0.5rem;
}

.panel-stat-items {
    display: flex;
    flex: 1;
    gap: 0.75rem;
}

.psi {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1rem 0.75rem !important;
    border-radius: 1rem !important;
    background: rgba(255,255,255,0.02) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}
.psi:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2) !important;
    background: rgba(var(--primary-rgb), 0.08) !important;
    border-color: rgba(var(--primary-rgb), 0.2) !important;
}
.psi-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 0.65rem;
}
.psi-val {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 0.3rem;
}
.psi-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-secondary);
}

/* Quick Filter Tooltip */
.psi-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(10px);
    background: rgba(15, 23, 42, 0.95);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 0.75rem;
    padding: 0.75rem;
    width: 160px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 20;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    pointer-events: none;
}
.psi:hover .psi-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-10px);
}
.psi-tooltip-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: var(--text-primary);
    padding: 0.35rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    text-align: left;
}
.psi-tooltip-item:last-child {
    border-bottom: none;
}
.psi-tooltip-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    font-weight: 700;
    color: var(--primary);
    overflow: hidden;
}
.psi-tooltip-avatar img {
    width: 100%; height: 100%; object-fit: cover;
}

/* ── Today Panel Footer Row ── */
.panel-footer-row {
    display: flex;
    align-items: center;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    padding: 0;
    background: rgba(0,0,0,0.15);
}
.pfr-item {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.85rem 0.75rem !important;
    cursor: pointer;
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    transition: background 0.2s;
}
.pfr-item:hover {
    background: rgba(var(--primary-rgb), 0.08) !important;
    transform: none !important;
    box-shadow: none !important;
}
.pfr-item i { font-size: 1.1rem; }
.pfr-val { font-size: 1.15rem; font-weight: 800; }
.pfr-label { font-size: 0.72rem; font-weight: 600; color: var(--text-secondary); }
.pfr-divider {
    width: 1px;
    height: 28px;
    background: rgba(255, 255, 255, 0.05);
    flex-shrink: 0;
}

/* ── System Overview Panel Grid ── */
.panel-system-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}
.psg-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.75rem 1rem !important;
    border-radius: 0 !important;
    border: none !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
    cursor: pointer;
    overflow: hidden;
    background: transparent !important;
    transition: all 0.3s ease;
}
.psg-item:nth-child(2n) { border-right: none !important; }
.psg-item:nth-last-child(-n+2) { border-bottom: none !important; }
.psg-item:hover {
    background: rgba(255, 255, 255, 0.02) !important;
    transform: translateY(-2px) !important;
}
.psg-item:hover .psg-icon-wrap i {
    transform: scale(1.15);
    filter: drop-shadow(0 0 8px currentColor);
}
.psg-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
}
.psg-icon-wrap i {
    transition: all 0.3s ease;
}
.psg-val {
    font-size: 2.2rem;
    font-weight: 900;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 0.35rem;
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.psg-trend {
    font-size: 0.65rem;
    font-weight: 800;
    color: var(--success);
    background: rgba(16,185,129,0.15);
    padding: 0.15rem 0.4rem;
    border-radius: 0.5rem;
    letter-spacing: 0.5px;
    line-height: 1;
}
.psg-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-secondary);
    position: relative;
    z-index: 2;
}
.psg-bg-icon {
    position: absolute;
    bottom: -5px;
    right: -5px;
    font-size: 4.5rem;
    opacity: 0.03;
    pointer-events: none;
    transition: all 0.3s ease;
}
.psg-item:hover .psg-bg-icon {
    opacity: 0.06;
    transform: scale(1.1) rotate(-5deg);
}

[data-theme="light"] .panel-today, 
[data-theme="light"] .panel-system {
    background: rgba(255, 255, 255, 0.7);
    border-color: rgba(0, 0, 0, 0.1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
}
[data-theme="light"] .panel-header {
    border-bottom-color: rgba(0, 0, 0, 0.06);
    background: linear-gradient(90deg, rgba(var(--primary-rgb), 0.05), transparent);
}
[data-theme="light"] .psi {
    background: rgba(0, 0, 0, 0.02) !important;
    border-color: rgba(0, 0, 0, 0.05) !important;
}
[data-theme="light"] .psi-tooltip {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(0, 0, 0, 0.1);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
[data-theme="light"] .psi-tooltip-item {
    border-bottom-color: rgba(0, 0, 0, 0.05);
}
[data-theme="light"] .psi-tooltip-avatar {
    background: rgba(0, 0, 0, 0.05);
}
[data-theme="light"] .panel-footer-row {
    border-top-color: rgba(0, 0, 0, 0.05);
    background: rgba(0, 0, 0, 0.02);
}
[data-theme="light"] .pfr-divider {
    background: rgba(0, 0, 0, 0.05);
}
[data-theme="light"] .psg-item {
    border-bottom-color: rgba(0, 0, 0, 0.05) !important;
    border-right-color: rgba(0, 0, 0, 0.05) !important;
}
[data-theme="light"] .psg-item:hover {
    background: rgba(0, 0, 0, 0.02) !important;
}

@media (max-width: 992px) {
    .stats-panels { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .panel-body-today { flex-direction: column; padding: 1.25rem; }
    .panel-gauge { width: 110px; height: 110px; }
    .panel-stat-items { width: 100%; }
    .psi-val { font-size: 1.5rem; }
    .panel-footer-row { flex-wrap: wrap; }
    .pfr-divider { display: none; }
    .pfr-item { min-width: 33%; }
}

.teacher-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: transparent;
    border: none;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: translateZ(0);
    outline: none !important;
}

.teacher-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    object-position: center top;
    display: block;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: var(--primary);
    background: rgba(var(--primary-rgb), 0.1);
    border-radius: 50%;
}

.badge-pill {
    padding: 0.35rem 0.85rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.badge-pill.success { background: rgba(16,185,129,0.1); color: #10b981; }
.badge-pill.danger  { background: rgba(239,68,68,0.1);  color: #ef4444; }
.badge-pill.warning { background: rgba(245,158,11,0.1); color: #f59e0b; }
.badge-pill.info    { background: rgba(59,130,246,0.1); color: #3b82f6; }
.badge-pill.secondary { background: var(--bg-dark); border: 1px solid var(--border); color: var(--text-muted); }

.grayscale img { filter: grayscale(1); }

.animate-fade-up {
    animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

.scan-notification {
    position: fixed;
    top: 2rem;
    right: 2rem;
    width: 320px;
    background: rgba(var(--bg-card-rgb, 22, 27, 34), 0.95);
    /* Removed blur filter */
    border: 1px solid rgba(var(--primary-rgb), 0.3);
    border-radius: 1rem;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 20px 50px rgba(0,0,0,0.4);
    z-index: 9999;
    animation: scanNotifIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scanNotifIn {
    from { transform: translateX(120%); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

.scan-notif-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    border: 2px solid rgba(var(--primary-rgb), 0.2);
}

.scan-notif-avatar img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    transform: translateZ(0);
    backface-visibility: hidden;
}

.scan-notif-body { flex: 1; }

.scan-notif-title {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--primary);
    margin-bottom: 0.2rem;
}

.scan-notif-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.scan-notif-meta {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.scan-notif-icon { font-size: 1.5rem; }

    @media (max-width: 992px) {
        .analytics-row {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- 0. Initialize Sparkline ---
    document.addEventListener('DOMContentLoaded', function() {
        const sparkCtx = document.getElementById('sparklineChart');
        if (sparkCtx) {
            new Chart(sparkCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['7AM','8AM','9AM','10AM','11AM','12PM','1PM','2PM'],
                    datasets: [{
                        data: [5, 25, 15, 10, 8, 12, 30, 20],
                        borderColor: 'rgba(0, 212, 160, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false, min: 0 }
                    },
                    layout: { padding: 0 }
                }
            });
        }
    });

    // --- 1. Initialize Chart ---
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('trendChart').getContext('2d');
        const trendData = @json($trendData);
        
        const labels = trendData.map(d => d.day);
        const presentValues = trendData.map(d => d.present);
        const lateValues    = trendData.map(d => d.late);
        const absentValues  = trendData.map(d => d.absent);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '{{ __("Present") }}',
                        data: presentValues,
                        borderColor: 'rgba(0, 212, 160, 1)',
                        backgroundColor: 'rgba(0, 212, 160, 0.2)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(0, 212, 160, 1)',
                        pointBorderColor: '#fff',
                        pointRadius: 4
                    },
                    {
                        label: '{{ __("Late") }}',
                        data: lateValues,
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                        pointBorderColor: '#fff',
                        pointRadius: 4
                    },
                    {
                        label: '{{ __("Absent") }}',
                        data: absentValues,
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                        pointBorderColor: '#fff',
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { 
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#7d8590',
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 2, color: '#7d8590' },
                        grid: { color: 'rgba(125, 133, 144, 0.1)', borderDash: [5, 5], drawBorder: false },
                        border: { display: false }
                    },
                    x: {
                        ticks: { 
                            color: '#7d8590', 
                            maxRotation: 0, 
                            autoSkip: true, 
                            maxTicksLimit: 6 
                        },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    });

    // --- 2. Performance Tabs ---
    function showPerfTab(tab) {
        const ontime = document.getElementById('perf-ontime');
        const late = document.getElementById('perf-late');
        const btnOntime = document.getElementById('btn-tab-ontime');
        const btnLate = document.getElementById('btn-tab-late');

        if (tab === 'ontime') {
            ontime.style.display = 'block';
            late.style.display = 'none';
            btnOntime.classList.replace('btn-secondary', 'btn-primary');
            btnLate.classList.replace('btn-primary', 'btn-secondary');
        } else {
            ontime.style.display = 'none';
            late.style.display = 'block';
            btnOntime.classList.replace('btn-primary', 'btn-secondary');
            btnLate.classList.replace('btn-secondary', 'btn-primary');
        }
    }
    const POLL_INTERVAL = 10000; // 10 seconds
    const DEVICE_INTERVAL = 15000; // 15 seconds



    // ── Build a table row (present/late) ──────────
    function buildRow(r, index = 0, animate = false) {
        let sourceBadge = `<span class="badge-pill secondary"><i class="ph ph-hand-tap"></i> {{ __('Manual') }}</span>`;
        if (r.rfid_uid === 'PORTAL') {
            sourceBadge = `<span class="badge-pill" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="ph ph-globe"></i> {{ __('Portal') }}</span>`;
        } else if (r.rfid_uid && r.rfid_uid !== 'MANUAL') {
            sourceBadge = `<span class="badge-pill info"><i class="ph ph-identification-card"></i> RFID</span>`;
        }

        const initial = r.teacher && r.teacher.name ? r.teacher.name.charAt(0).toUpperCase() : '?';
        const photoUrl = r.teacher && r.teacher.photo ? (r.teacher.photo.startsWith('data:') || r.teacher.photo.startsWith('http') ? r.teacher.photo : '{{ url('/') }}/' + r.teacher.photo) : null;
        const avatar = photoUrl 
            ? `<img src="${photoUrl}" alt="">` 
            : `<div class="avatar-placeholder">${initial}</div>`;

        // Generate shift columns
        const mIn = r.morning_in ? r.morning_in.substring(0,5) : '—';
        const mOut = r.morning_out ? r.morning_out.substring(0,5) : '?';
        const mLate = r.morning_status === 'late' ? '<span class="badge badge-warning" style="font-size:0.65rem; padding:1px 6px;">LATE</span>' : '';
        const mCell = r.morning_in ? `<div style="font-size:0.85rem;"><span style="color:var(--success); font-weight:700;">${mIn}</span> <i class="ph ph-arrow-right" style="font-size:0.7rem; opacity:0.5;"></i> ${mOut}</div>${mLate}` : '—';

        const aIn = r.afternoon_in ? r.afternoon_in.substring(0,5) : '—';
        const aOut = r.afternoon_out ? r.afternoon_out.substring(0,5) : '?';
        const aLate = r.afternoon_status === 'late' ? '<span class="badge badge-warning" style="font-size:0.65rem; padding:1px 6px;">LATE</span>' : '';
        const aCell = r.afternoon_in ? `<div style="font-size:0.85rem;"><span style="color:var(--success); font-weight:700;">${aIn}</span> <i class="ph ph-arrow-right" style="font-size:0.7rem; opacity:0.5;"></i> ${aOut}</div>${aLate}` : '—';

        let completedSeconds = 0;
        if (r.morning_in && r.morning_out) {
            const [h1, m1, s1] = r.morning_in.split(':').map(Number);
            const [h2, m2, s2] = r.morning_out.split(':').map(Number);
            const sec1 = h1 * 3600 + m1 * 60 + (s1 || 0);
            const sec2 = h2 * 3600 + m2 * 60 + (s2 || 0);
            if (sec2 >= sec1) completedSeconds += (sec2 - sec1);
        }
        if (r.afternoon_in && r.afternoon_out) {
            const [h1, m1, s1] = r.afternoon_in.split(':').map(Number);
            const [h2, m2, s2] = r.afternoon_out.split(':').map(Number);
            const sec1 = h1 * 3600 + m1 * 60 + (s1 || 0);
            const sec2 = h2 * 3600 + m2 * 60 + (s2 || 0);
            if (sec2 >= sec1) completedSeconds += (sec2 - sec1);
        }

        let activeStart = null;
        if (r.afternoon_in && !r.afternoon_out) activeStart = r.afternoon_in;
        else if (r.morning_in && !r.morning_out) activeStart = r.morning_in;

        const autoMark = (r.manual_note && r.manual_note.includes('[Auto')) 
            ? `<i class="ph ph-robot" style="margin-left:4px; font-size:0.85rem; color:var(--text-muted);" title="${r.manual_note}"></i>` 
            : '';

        let runtimeCell = '';
        if (activeStart) {
            const now = new Date();
            const [h, m, s] = activeStart.split(':').map(Number);
            const startDate = new Date();
            startDate.setHours(h, m, s || 0, 0);
            let diff = Math.floor((now - startDate) / 1000) + completedSeconds;
            if (diff < 0) diff = 0;
            const hours = String(Math.floor(diff / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const seconds = String(diff % 60).padStart(2, '0');
            const initialDuration = `${hours}:${minutes}:${seconds}`;
            runtimeCell = `<div class="runtime-timer" data-start="${activeStart}" data-completed="${completedSeconds}" style="font-family:'JetBrains Mono', monospace; font-weight:700; color:var(--primary); font-size:0.9rem;">${initialDuration}${autoMark}</div>`;
        } else if (completedSeconds > 0) {
            const hours = String(Math.floor(completedSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((completedSeconds % 3600) / 60)).padStart(2, '0');
            runtimeCell = `<div style="font-family:'JetBrains Mono', monospace; font-weight:700; color:var(--success); font-size:0.9rem; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-clock" style="font-size:0.85rem;"></i> ${hours}h ${minutes}m${autoMark}</div>`;
        } else {
            runtimeCell = `<div style="display: flex; align-items: center; gap: 4px;"><span style="color:var(--text-muted);">—</span>${autoMark}</div>`;
        }

        const currentLocale = document.documentElement.lang || 'en';
        const mainName = currentLocale === 'km' ? (r.teacher.name_kh || r.teacher.name) : r.teacher.name;
        const subName = currentLocale === 'km' ? r.teacher.name : (r.teacher.name_kh || '');
        const delay = (index * 0.04).toFixed(2);
        const rowClass = animate ? `class="stagger-item" style="animation-delay: ${delay}s"` : '';
        return `<tr ${rowClass}>
            <td>
                <div style="display:flex; align-items:center; gap:1rem; cursor:pointer;" onclick="openTeacherInsights(${r.teacher.id})">
                    <div class="teacher-avatar">${avatar}</div>
                    <div>
                        <div style="font-weight:700; color:var(--primary); font-size: 1.1rem; line-height: 1.2;" translate="no" class="notranslate">${mainName}</div>
                        <div style="font-weight:600; color:var(--text-primary); font-size: 0.85rem; opacity: 0.8;" translate="no" class="notranslate">${subName}</div>
                        <div style="font-size:0.75rem;color:var(--text-secondary);">${r.teacher.employee_id}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--text-secondary);">${window.transDept ? window.transDept(r.teacher.department) : r.teacher.department}</td>
            <td>${mCell}</td>
            <td>${aCell}</td>
            <td>${runtimeCell}</td>
            <td>${sourceBadge}</td>
            <td><span class="badge-pill success">{{ __("Present") }}</span></td>
        </tr>`;
    }

    // ── Build an absent row ───────────────────────
    function buildAbsentRow(t, index = 0, animate = false) {
        const initial = t.name ? t.name.charAt(0).toUpperCase() : '?';
        const photoUrl = t.photo ? (t.photo.startsWith('data:') || t.photo.startsWith('http') ? t.photo : '{{ url('/') }}/' + t.photo) : null;
        const avatar = photoUrl 
            ? `<img src="${photoUrl}" alt="">` 
            : `<div class="avatar-placeholder">${initial}</div>`;

        const currentLocale = document.documentElement.lang || 'en';
        const mainName = currentLocale === 'km' ? (t.name_kh || t.name) : t.name;
        const subName = currentLocale === 'km' ? t.name : (t.name_kh || '');
        const delay = (index * 0.04).toFixed(2);
        const rowClass = animate ? `class="stagger-item" style="animation-delay: ${delay}s"` : '';
        return `<tr ${rowClass}>
            <td>
                <div style="display:flex; align-items:center; gap:1rem; cursor:pointer;" onclick="openTeacherInsights(${t.id})">
                    <div class="teacher-avatar grayscale">${avatar}</div>
                    <div>
                        <div style="font-weight:700; color:var(--primary); font-size: 1.1rem; line-height: 1.2;" translate="no" class="notranslate">${mainName}</div>
                        <div style="font-weight:600; color:var(--text-secondary); font-size: 0.85rem;" translate="no" class="notranslate">${subName}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted);">${t.employee_id}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--text-secondary);">${window.transDept ? window.transDept(t.department) : t.department}</td>
            <td style="color:var(--text-muted);">—</td>
            <td style="color:var(--text-muted);">—</td>
            <td style="color:var(--text-muted);">—</td>
            <td><span class="badge-pill secondary">—</span></td>
            <td><span class="badge-pill danger">{{ __("Absent") }}</span></td>
        </tr>`;
    }

    function buildSkeletonRows(count = 5) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <div class="skeleton skeleton-avatar"></div>
                            <div style="flex:1;">
                                <div class="skeleton skeleton-text" style="width:120px; height:1.1rem; margin-bottom:4px;"></div>
                                <div class="skeleton skeleton-text" style="width:80px; height:0.8rem;"></div>
                            </div>
                        </div>
                    </td>
                    <td><div class="skeleton skeleton-text" style="width:100px;"></div></td>
                    <td><div class="skeleton skeleton-text" style="width:60px;"></div></td>
                    <td><div class="skeleton skeleton-text" style="width:60px;"></div></td>
                    <td><div class="skeleton skeleton-text" style="width:80px;"></div></td>
                    <td><div class="skeleton skeleton-badge"></div></td>
                    <td><div class="skeleton skeleton-badge"></div></td>
                </tr>
            `;
        }
        return html;
    }

    // ── Live Runtime Ticking ──────────────────────
    function updateTimers() {
        const now = new Date();
        const timers = document.querySelectorAll('.runtime-timer');
        
        timers.forEach(timer => {
            const startStr = timer.getAttribute('data-start'); // HH:MM:SS
            const completedSec = parseInt(timer.getAttribute('data-completed') || '0', 10);
            if (!startStr) return;

            const [h, m, s] = startStr.split(':').map(Number);
            const startDate = new Date();
            startDate.setHours(h, m, s || 0, 0);

            let diff = Math.floor((now - startDate) / 1000) + completedSec;
            if (diff < 0) diff = 0;

            const hours = String(Math.floor(diff / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const seconds = String(diff % 60).padStart(2, '0');

            if (timer.childNodes.length > 0 && timer.childNodes[0].nodeType === 3) {
                timer.childNodes[0].nodeValue = `${hours}:${minutes}:${seconds}`;
            } else {
                timer.textContent = `${hours}:${minutes}:${seconds}`;
            }
        });
    }
    setInterval(updateTimers, 1000);
    updateTimers();

    // ── Live AJAX polling ─────────────────────────
    let lastKnownScanId = null;
    let lastKnownUpdatedAt = null;
    let activeDashboardFilter = 'total';
    let scanAlertDuration = 15; // Default 15s

    let forceAnimateNext = true;

    function setDashboardFilter(f) {
        activeDashboardFilter = f;
        // Highlight active item
        document.querySelectorAll('.psi, .pfr-item, .psg-item').forEach(c => {
            c.style.outline = 'none';
            c.style.outlineOffset = '';
        });
        const activeCard = document.getElementById(`card-${f}`);
        if(activeCard) {
            activeCard.style.outline = '2px solid var(--primary)';
            activeCard.style.outlineOffset = '-2px';
        }
        
        forceAnimateNext = true;
        refreshAttendance();
    }

    async function refreshAttendance() {
        try {
            const tbody = document.getElementById('attendance-body');
            const searchInput = document.getElementById('search-input');
            const search = encodeURIComponent(searchInput?.value || '');
            
            const isTyping = searchInput && document.activeElement === searchInput;
            const hasSkeletons = tbody.querySelector('.skeleton') !== null;
            const isInitialLoad = !tbody.innerHTML.trim() || hasSkeletons;

            let shouldAnimate = forceAnimateNext || isInitialLoad || isTyping;
            forceAnimateNext = false; // Reset flag after using

            // Show skeletons if it's the first load or if searching
            if (isInitialLoad || isTyping) {
                tbody.innerHTML = buildSkeletonRows(5);
                shouldAnimate = true;
            }

            const filter = activeDashboardFilter;
            const data   = await window.fetchApi(`/api-web/attendance?search=${search}&filter=${filter}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (data.scan_alert_duration) {
                scanAlertDuration = data.scan_alert_duration;
            }

            // Handle Notifications for new scans
            if (data.attendance && data.attendance.length > 0) {
                const latest = data.attendance[0];
                
                // On the very first load, or when a new scan happens, update the persistent card
                updateRecentScanCard(latest);

                const isNewScan = lastKnownScanId !== null && 
                                 (latest.id !== lastKnownScanId || latest.updated_at !== lastKnownUpdatedAt);

                if (isNewScan) {
                    showToastNotification(latest);
                }
                lastKnownScanId = latest.id;
                lastKnownUpdatedAt = latest.updated_at;
            }

            // Update stats with animation if changed
            updateStatValue('stat-present', data.present_count);
            updateStatValue('stat-late', data.late_count);
            updateStatValue('stat-absent', data.absent_count);
            if(data.morning_absent_teachers) updateShiftAbsentLists(data.morning_absent_teachers, data.afternoon_absent_teachers, data.absent_teachers);
            updateStatValue('stat-total', data.total);
            updateStatValue('stat-checkins', data.checkin_count);
            updateStatValue('stat-currently-in', data.currently_checked_in);
            updateStatValue('stat-currently-out', data.currently_checked_out);
            updateStatValue('stat-rfid', data.total_rfid_teachers);
            updateStatValue('stat-rate', data.attendance_rate + '%');
            if (data.total_admins !== undefined) updateStatSilent('stat-total-admins', data.total_admins);

            // Trigger ECG pulse on every live data refresh
            if (typeof window.ecgTriggerBeat === 'function') {
                const scanCount = data.total_scans !== undefined ? data.total_scans : (data.attendance ? data.attendance.length : 0);
                window.ecgTriggerBeat(scanCount);
            }

            // Rebuild table
            let html = '';
            let globalIndex = 0;
            data.attendance.forEach(r => html += buildRow(r, globalIndex++, shouldAnimate));
            data.absent_teachers.forEach(t => html += buildAbsentRow(t, globalIndex++, shouldAnimate));
            
            if (!html) {
                html = `<tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted);">
                    <i class="ph ph-users" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                    {{ __("No teachers found for today.") }}
                </td></tr>`;
            }
            tbody.innerHTML = html;
        } catch (e) {
            console.error('Poll error:', e);
        }
    }

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


    function updateRecentScanCard(record) {
        const teacher = record.teacher || {};
        const initials = teacher.name ? teacher.name.charAt(0).toUpperCase() : '?';
        const photoUrl = teacher.photo ? teacher.photo : null;
        
        const permanentCard = document.getElementById('recentScanCard');
        if (permanentCard) {
            permanentCard.style.display = 'flex';
            permanentCard.style.cursor = 'pointer';
            permanentCard.onclick = () => openTeacherInsights(teacher.id);
            document.getElementById('recentScanName').innerHTML = `
                <div style="color:var(--primary); font-size:1.1rem; line-height:1.2;">${teacher.name_kh || ''}</div>
                <div style="color:var(--text-primary); font-size:0.9rem; opacity:0.8;">${teacher.name}</div>
            `;
            document.getElementById('recentScanDept').textContent = window.transDept(teacher.department);
            
            // Dynamic Label
            const labelEl = document.getElementById('recentScanLabel');
            if (labelEl) {
                const isCheckOut = record.type === 'check-out';
                labelEl.textContent = isCheckOut ? '{{ __("Last Check-out") }}' : '{{ __("Last Check-in") }}';
                labelEl.style.color = isCheckOut ? 'var(--warning)' : 'var(--primary)';
            }

            document.getElementById('recentScanPhoto').innerHTML = photoUrl 
                ? `<img src="${photoUrl}" style="width:100%; height:100%; object-fit:cover; image-rendering: -webkit-optimize-contrast;">`
                : `<div class="avatar-placeholder" style="font-size:1.5rem;">${initials}</div>`;

            // Update Time
            const timeEl = document.getElementById('recentScanTime');
            if (timeEl) {
                // Determine the time to show: the most recent scan time
                const times = [record.morning_in, record.morning_out, record.afternoon_in, record.afternoon_out].filter(t => !!t);
                const latestTime = times.length > 0 ? times[times.length - 1].substring(0, 5) : '';
                timeEl.innerHTML = `<i class="ph ph-clock" style="vertical-align: middle; margin-right: 4px;"></i>${latestTime}`;
            }
        }
    }

    function showToastNotification(record) {
        const teacher = record.teacher || {};
        const initials = teacher.name ? teacher.name.charAt(0).toUpperCase() : '?';
        const photoUrl = teacher.photo ? teacher.photo : null;

        const isCheckOut = record.type === 'check-out';
        const title = isCheckOut ? '{{ __("Checked Out") }}' : '{{ __("Checked In") }}';
        const color = isCheckOut ? 'var(--warning)' : 'var(--primary)';

        const toast = document.createElement('div');
        toast.className = 'scan-notification';
        toast.innerHTML = `
            <div class="scan-notif-avatar">
                ${photoUrl ? `<img src="${photoUrl}">` : `<div class="avatar-placeholder">${initials}</div>`}
            </div>
            <div class="scan-notif-body">
                <div class="scan-notif-title" style="color:${color}">${title}</div>
                <div class="scan-notif-name" style="color:var(--primary); line-height:1.2;">${teacher.name_kh || ''}</div>
                <div class="scan-notif-name" style="font-size:0.9rem; opacity:0.8;">${teacher.name}</div>
                <div class="scan-notif-meta">${window.transDept(teacher.department)} · {{ __('Just now') }}</div>
            </div>
            <div class="scan-notif-icon">
                <div style="text-align: right;">
                    <i class="ph ph-check-circle" style="color:var(--success); font-size: 1.5rem;"></i>
                    <div style="font-size: 0.7rem; font-weight: 700; color: var(--text-secondary); margin-top: 2px; font-family: 'JetBrains Mono', monospace;">
                        ${(function(){
                            const times = [record.morning_in, record.morning_out, record.afternoon_in, record.afternoon_out].filter(t => !!t);
                            return times.length > 0 ? times[times.length - 1].substring(0, 5) : '';
                        })()}
                    </div>
                </div>
            </div>
        `;
        
        toast.style.cursor = 'pointer';
        toast.onclick = () => openTeacherInsights(teacher.id);
        
        document.body.appendChild(toast);
        
        // Remove after scanAlertDuration
        setTimeout(() => {
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, scanAlertDuration * 1000);
    }

    function updateStatValue(id, newValue) {
        const el = document.getElementById(id);
        if (el && el.textContent !== String(newValue)) {
            el.textContent = newValue;
            el.classList.remove('changed');
            void el.offsetWidth; // trigger reflow
            el.classList.add('changed');
            setTimeout(() => el.classList.remove('changed'), 1000);

            // Sync gauge when rate changes
            if (id === 'stat-rate') {
                const pct = parseFloat(newValue);
                const gauge = document.getElementById('dashGaugeFill');
                if (gauge && !isNaN(pct)) {
                    gauge.setAttribute('stroke-dashoffset', 326.73 - (326.73 * pct / 100));
                }
            }
        }
    }

    // Function to specifically handle non-animated stat updates (like total scans)
    function updateStatSilent(id, newValue) {
        const el = document.getElementById(id);
        if (el && el.textContent !== String(newValue)) {
            el.textContent = newValue;
        }
    }

    // ── Device status check ───────────────────────
    async function checkDeviceStatus() {
        try {
            const data  = await window.fetchApi('{{ route("api.device.status") }}', {
                headers: { 'Accept': 'application/json' }
            });
            const labelHero = document.getElementById('deviceLabelHero');

            if (data.online) {
                if(labelHero) labelHero.innerHTML = `<i class="ph ph-broadcast" style="color:var(--success); animation:pulse 2s infinite;"></i> <span style="color:var(--success);">{{ __('Scanner Online') }} · ${data.last_seen_ago}</span>`;
            } else {
                if(labelHero) labelHero.innerHTML = `<i class="ph ph-broadcast" style="color:var(--danger);"></i> <span style="color:var(--danger);">${data.timestamp ? `{{ __('Scanner Offline') }} · ${data.last_seen_ago}` : '{{ __("Scanner Offline") }}'}</span>`;
            }
        } catch {
            if(document.getElementById('deviceLabelHero')) document.getElementById('deviceLabelHero').textContent = '{{ __("Device Unknown") }}';
        }
    }

    
    let currentMorningAbsent = @json($morningAbsentTeachers ?? []);
    let currentAfternoonAbsent = @json($afternoonAbsentTeachers ?? []);

    function showShiftAbsentModal() {
        document.getElementById('shiftAbsentModal').style.display = 'flex';
    }
    function closeShiftAbsentModal() {
        document.getElementById('shiftAbsentModal').style.display = 'none';
    }

    function updateShiftAbsentLists(morning, afternoon, fullday) {
        currentMorningAbsent   = morning  || [];
        currentAfternoonAbsent = afternoon || [];

        // Morning list
        document.getElementById('modal-morning-count').innerText = currentMorningAbsent.length;
        let mHtml = '';
        currentMorningAbsent.forEach(t => {
            mHtml += `<div style="display:flex;align-items:center;gap:0.5rem;padding:0.3rem 0.5rem;border-radius:0.5rem;background:rgba(255,255,255,0.03);"><i class="ph ph-user" style="color:var(--text-secondary);"></i> ${t.name}</div>`;
        });
        if (currentMorningAbsent.length === 0) mHtml = '<div style="color:var(--text-muted);font-style:italic;font-size:0.85rem;">' + '{{ __("All checked in") }}' + '</div>';
        document.getElementById('modal-morning-list').innerHTML = mHtml;

        // Afternoon list
        document.getElementById('modal-afternoon-count').innerText = currentAfternoonAbsent.length;
        let aHtml = '';
        currentAfternoonAbsent.forEach(t => {
            aHtml += `<div style="display:flex;align-items:center;gap:0.5rem;padding:0.3rem 0.5rem;border-radius:0.5rem;background:rgba(255,255,255,0.03);"><i class="ph ph-user" style="color:var(--text-secondary);"></i> ${t.name}</div>`;
        });
        if (currentAfternoonAbsent.length === 0) aHtml = '<div style="color:var(--text-muted);font-style:italic;font-size:0.85rem;">' + '{{ __("All checked in") }}' + '</div>';
        document.getElementById('modal-afternoon-list').innerHTML = aHtml;

        // Full Day Absent list (no scan at all)
        const fdList = fullday || [];
        const fdEl = document.getElementById('modal-fullday-count');
        const fdHtmlEl = document.getElementById('modal-fullday-list');
        if (fdEl) fdEl.innerText = fdList.length;
        if (fdHtmlEl) {
            let fdHtml = '';
            fdList.forEach(t => {
                fdHtml += `<div style="display:flex;align-items:center;gap:0.5rem;padding:0.3rem 0.5rem;border-radius:0.5rem;background:rgba(239,68,68,0.04);"><i class="ph ph-user-minus" style="color:var(--danger,#ef4444);"></i> ${t.name}</div>`;
            });
            if (fdList.length === 0) fdHtml = '<div style="color:var(--text-muted);font-style:italic;font-size:0.85rem;">' + '{{ __("All checked in") }}' + '</div>';
            fdHtmlEl.innerHTML = fdHtml;
        }
    }


    function closeTeacherInsights() {
        document.getElementById('insightModal').classList.remove('active');
    }


    // ── Init ──────────────────────────────────────
    checkDeviceStatus();
    setInterval(refreshAttendance, POLL_INTERVAL);
    setInterval(checkDeviceStatus, DEVICE_INTERVAL);

    // Don't poll table when typing in search
    document.getElementById('search-input')?.addEventListener('keydown', () => {
        clearInterval(window._pollTimer);
        window._pollTimer = setTimeout(() => {
            setInterval(refreshAttendance, POLL_INTERVAL);
        }, 3000);
    });
</script>
@endpush

