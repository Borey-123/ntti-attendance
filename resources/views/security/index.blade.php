@extends('layouts.app')

@section('title', __('Security & Audit Logs'))

@push('styles')
<style>
/* ── Hero Section ── */
.security-hero {
    background: linear-gradient(135deg, rgba(239,68,68,0.12) 0%, rgba(var(--primary-rgb),0.08) 100%);
    border: 1px solid rgba(239,68,68,0.2);
    border-radius: 1.75rem;
    padding: 2rem 2.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
    position: relative;
    overflow: hidden;
}
.security-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(239,68,68,0.06);
    pointer-events: none;
}
.security-hero-icon {
    width: 64px; height: 64px;
    border-radius: 1.25rem;
    background: rgba(239,68,68,0.15);
    color: #ef4444;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    flex-shrink: 0;
}
.security-hero-text h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.25rem; }
.security-hero-text p  { font-size: 0.9rem; color: var(--text-secondary); }
.security-hero-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }

/* ── Stat Cards ── */
.security-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}
.sec-stat {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.25rem;
    padding: 1.25rem;
    display: flex; align-items: center; gap: 1rem;
    transition: all 0.3s;
}
.sec-stat:hover { transform: translateY(-3px); border-color: var(--primary); }
.sec-stat-icon {
    width: 46px; height: 46px; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.sec-stat-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); }
.sec-stat-value { font-size: 1.6rem; font-weight: 800; line-height: 1.1; color: var(--text-primary); }
.sec-stat.clickable { cursor: pointer; }
.sec-stat.clickable:active { transform: scale(0.98); }

/* ── Layout ── */
.security-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1024px) { .security-layout { grid-template-columns: 1fr; } }

/* ── Log Table ── */
.log-row { transition: background 0.2s; }
.log-row:hover { background: rgba(255,255,255,0.03); }

/* ── Action Badge Colours ── */
.action-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.8rem; border-radius: 2rem;
    font-size: 0.72rem; font-weight: 700; letter-spacing: 0.3px; white-space: nowrap;
}
.action-badge.danger  { background: rgba(239,68,68,0.12);   color: #ef4444; }
.action-badge.success { background: rgba(16,185,129,0.12);  color: #10b981; }
.action-badge.warning { background: rgba(245,158,11,0.12);  color: #f59e0b; }
.action-badge.info    { background: rgba(59,130,246,0.12);  color: #3b82f6; }
.action-badge.primary { background: rgba(var(--primary-rgb),0.12); color: var(--primary); }
.action-badge.secondary{ background: rgba(255,255,255,0.05); color: var(--text-muted); }

/* ── Side Panel ── */
.side-panel-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
}
.side-panel-card h4 {
    font-size: 0.95rem; font-weight: 800;
    display: flex; align-items: center; gap: 0.6rem;
    margin-bottom: 1.25rem;
}

/* ── Integrity Items ── */
.integrity-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.65rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.82rem;
}
.integrity-item:last-child { border-bottom: none; }
.integrity-item .label { color: var(--text-secondary); }
.integrity-item .status-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--success);
    display: inline-block; margin-right: 0.4rem;
}
.status-dot.bad { background: var(--danger); animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

/* ── Scrollable table wrapper ── */
.log-table-wrap {
    overflow-x: auto;
    overflow-y: auto;
    position: relative;
    max-height: 550px;
}
/* ── Modal Details ── */
.log-detail-card {
    background: var(--bg-dark);
    border-radius: 1rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.log-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 1rem;
}
.log-detail-item:last-child { margin-bottom: 0; }
.log-detail-item .label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted);
    letter-spacing: 0.5px;
}
.log-detail-item .value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
}
.log-detail-item .value.code {
    font-family: 'JetBrains Mono', 'Roboto Mono', var(--font-family), monospace;
    background: var(--bg-dark);
    border: 1px solid var(--border);
    color: var(--text-primary);
    padding: 0.75rem;
    border-radius: 0.5rem;
    word-break: break-all;
    font-size: 0.85rem;
    line-height: 1.6;
}

/* ── Diff table inside modal ── */
.diff-row {
    display: grid;
    grid-template-columns: 110px 1fr 1fr;
    gap: 0.5rem;
    align-items: center;
    padding: 0.45rem 0.5rem;
    border-radius: 0.35rem;
    margin-bottom: 2px;
    font-size: 0.82rem;
}
.diff-row:nth-child(even) { background: rgba(128,128,128,0.06); }
.diff-row .diff-label {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--text-secondary);
}
.diff-row .diff-old {
    font-weight: 700;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
}
.diff-row .diff-new {
    font-weight: 700;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
}
.diff-row.changed .diff-old {
    color: #b91c1c;
    background: rgba(239,68,68,0.12);
    text-decoration: line-through;
    text-decoration-color: #ef444480;
}
.diff-row.changed .diff-new {
    color: #15803d;
    background: rgba(16,185,129,0.12);
}
.diff-row.unchanged .diff-old,
.diff-row.unchanged .diff-new {
    color: var(--text-secondary);
}
.diff-header {
    display: grid;
    grid-template-columns: 110px 1fr 1fr;
    gap: 0.5rem;
    padding: 0.3rem 0.5rem 0.5rem;
    border-bottom: 2px solid var(--border);
    margin-bottom: 0.4rem;
}
.diff-header span {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.diff-reason {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.6rem 0.75rem;
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.25);
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
    color: var(--text-primary);
    font-weight: 600;
    line-height: 1.5;
}

/* ── Pagination Arrow Size Override ── */
nav[role="navigation"] svg {
    width: 1.2rem !important;
    height: 1.2rem !important;
    max-width: 1.2rem !important;
    max-height: 1.2rem !important;
}
nav[role="navigation"] span, nav[role="navigation"] a {
    font-size: 0.85rem !important;
    padding: 0.35rem 0.65rem !important;
    border-radius: 0.5rem !important;
}
</style>
@endpush

@section('content')

{{-- ── Hero Section ── --}}
<div class="security-hero">
    <div style="display: flex; align-items: center; gap: 1.25rem;">
        <div class="security-hero-icon">
            <i class="ph ph-shield-checkered"></i>
        </div>
        <div class="security-hero-text">
            <h1>{{ __('Security & Audit Center') }}</h1>
            <p>{{ __('Full administrative audit trail with system health monitoring.') }}</p>
        </div>
    </div>
</div>

@php
    $allCurrentLogs = $logs->getCollection()->load('admin');
    $deleteLogs = $allCurrentLogs->filter(fn($l) => str_contains(strtolower($l->action), 'delete') || str_contains(strtolower($l->action), 'remove'))->values();
    $loginLogs = $allCurrentLogs->filter(fn($l) => str_contains(strtolower($l->action), 'login'))->values();
    $adjustLogs = $allCurrentLogs->filter(fn($l) => str_contains(strtolower($l->action), 'adjust') || str_contains(strtolower($l->action), 'update'))->values();
@endphp

{{-- ── Summary Stats ── --}}
<div class="security-stats">
    <div class="sec-stat clickable" onclick="showStatDetail('{{ __("Total Logs") }}', '{{ json_encode($allCurrentLogs) }}')">
        <div class="sec-stat-icon" style="background: rgba(var(--primary-rgb),0.1); color: var(--primary);">
            <i class="ph ph-list-magnifying-glass"></i>
        </div>
        <div>
            <div class="sec-stat-label">{{ __('Total Logs') }}</div>
            <div class="sec-stat-value">{{ $logs->total() }}</div>
        </div>
    </div>
    <div class="sec-stat clickable" onclick="showStatDetail('{{ __("Delete Actions") }}', '{{ json_encode($deleteLogs) }}')">
        <div class="sec-stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
            <i class="ph ph-trash"></i>
        </div>
        <div>
            <div class="sec-stat-label">{{ __('Delete Actions') }}</div>
            <div class="sec-stat-value" style="color: #ef4444;">
                {{ $deleteLogs->count() }}
            </div>
        </div>
    </div>
    <div class="sec-stat clickable" onclick="showStatDetail('{{ __("Login Events") }}', '{{ json_encode($loginLogs) }}')">
        <div class="sec-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
            <i class="ph ph-sign-in"></i>
        </div>
        <div>
            <div class="sec-stat-label">{{ __('Login Events') }}</div>
            <div class="sec-stat-value" style="color: #10b981;">
                {{ $loginLogs->count() }}
            </div>
        </div>
    </div>
    <div class="sec-stat clickable" onclick="showStatDetail('{{ __("Adjustments") }}', '{{ json_encode($adjustLogs) }}')">
        <div class="sec-stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
            <i class="ph ph-pencil-simple"></i>
        </div>
        <div>
            <div class="sec-stat-label">{{ __('Adjustments') }}</div>
            <div class="sec-stat-value" style="color: #f59e0b;">
                {{ $adjustLogs->count() }}
            </div>
        </div>
    </div>
</div>

{{-- ── Main Layout ── --}}
<div class="security-layout">

    {{-- ── Audit Log Table ── --}}
    <div class="card" style="border-radius: 1.75rem; overflow: hidden;">
        <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.6rem;">
                    <i class="ph ph-scroll" style="color: var(--primary);"></i>
                    {{ __('Audit Trail') }}
                </h3>
                <span style="font-size: 0.75rem; color: var(--text-secondary);">
                    {{ __('Showing page') }} {{ $logs->currentPage() }}/{{ $logs->lastPage() }}
                    &bull; {{ $logs->total() }} {{ __('total entries') }}
                </span>
            </div>
        </div>
        <div class="log-table-wrap">
            <table class="table" style="margin: 0;">
                <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <tr>
                        <th style="width: 160px;">{{ __('Timestamp') }}</th>
                        <th>{{ __('Administrator') }}</th>
                        <th>{{ __('Action') }}</th>
                        <th>{{ __('Target') }}</th>
                        <th style="width: 130px;">{{ __('IP Address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $action = strtolower($log->action ?? '');
                        $badgeClass = 'secondary';
                        $badgeIcon  = 'ph-circle';
                        if ($action === 'telegram_sent') {
                            $badgeClass = 'info'; $badgeIcon = 'ph-telegram-logo';
                        } elseif ($action === 'telegram_failed') {
                            $badgeClass = 'danger'; $badgeIcon = 'ph-telegram-logo';
                        } elseif (str_contains($action, 'delete') || str_contains($action, 'remove')) {
                            $badgeClass = 'danger'; $badgeIcon = 'ph-trash';
                        } elseif (str_contains($action, 'login') || str_contains($action, 'logout')) {
                            $badgeClass = 'success'; $badgeIcon = 'ph-sign-in';
                        } elseif (str_contains($action, 'portal') || str_contains($action, 'check-in') || str_contains($action, 'scan')) {
                            $badgeClass = 'success'; $badgeIcon = 'ph-user-check';
                        } elseif (str_contains($action, 'create') || str_contains($action, 'register') || str_contains($action, 'add')) {
                            $badgeClass = 'primary'; $badgeIcon = 'ph-plus-circle';
                        } elseif (str_contains($action, 'update') || str_contains($action, 'edit') || str_contains($action, 'adjust')) {
                            $badgeClass = 'warning'; $badgeIcon = 'ph-pencil-simple';
                        } elseif (str_contains($action, 'fail') || str_contains($action, 'error')) {
                            $badgeClass = 'danger'; $badgeIcon = 'ph-warning-circle';
                        } elseif (str_contains($action, 'clear') || str_contains($action, 'cache')) {
                            $badgeClass = 'info'; $badgeIcon = 'ph-broom';
                        }
                    @endphp
                    <tr class="log-row" style="cursor: pointer;" onclick="showLogDetails({{ json_encode($log->load('admin')) }})">
                        <td style="font-size: 0.78rem; white-space: nowrap; color: var(--text-secondary); font-family: monospace;">
                            {{ \Carbon\Carbon::parse($log->timestamp)->format('d M Y') }}<br>
                            <span style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($log->timestamp)->format('H:i:s') }}</span>
                        </td>
                        <td>
                            @if($log->admin)
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; flex-shrink: 0;">
                                        {{ strtoupper(substr($log->admin->name, 0, 1)) }}
                                    </div>
                                    <span style="font-weight: 600; font-size: 0.85rem;">{{ $log->admin->name }}</span>
                                </div>
                            @elseif(str_contains($action, 'portal') || str_contains($action, 'check-in') || str_contains($action, 'scan'))
                                <span style="display: flex; align-items: center; gap: 0.4rem; color: #10b981; font-size: 0.82rem; font-weight: 700;">
                                    <i class="ph ph-user-check"></i> {{ __('Teacher Portal') }}
                                </span>
                            @else
                                <span style="display: flex; align-items: center; gap: 0.4rem; color: var(--text-muted); font-size: 0.82rem;">
                                    <i class="ph ph-robot"></i> {{ __('System') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="action-badge {{ $badgeClass }}">
                                <i class="ph {{ $badgeIcon }}"></i>
                                {{ $log->action }}
                            </span>
                        </td>
                        <td style="font-weight: 600; font-size: 0.85rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->target }}">
                            {{ $log->target ?? '—' }}
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.75rem; background: var(--bg-dark); padding: 0.2rem 0.6rem; border-radius: 0.5rem; color: var(--text-secondary);">
                                {{ $log->ip_address ?? '—' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                            <i class="ph ph-shield-check" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem; color: var(--success);"></i>
                            {{ __('No audit logs recorded yet. System is clean.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
            {{ $logs->links() }}
        </div>
    </div>

    {{-- ── Side Panel ── --}}
    <div>

        {{-- System Health --}}
        <div class="side-panel-card" style="border-top: 3px solid var(--primary);">
            <h4>
                <i class="ph ph-heartbeat" style="color: var(--primary);"></i>
                {{ __('System Health') }}
            </h4>
            <div class="integrity-item">
                <span class="label">{{ __('Database') }}</span>
                <span style="color: var(--success); font-weight: 700; font-size: 0.82rem;"><span class="status-dot"></span>{{ __('Connected') }}</span>
            </div>
            <div class="integrity-item">
                <span class="label">{{ __('Storage') }}</span>
                <span style="color: var(--success); font-weight: 700; font-size: 0.82rem;"><span class="status-dot"></span>{{ __('Writable') }}</span>
            </div>
            <div class="integrity-item">
                <span class="label">{{ __('Cache') }}</span>
                <span style="color: var(--success); font-weight: 700; font-size: 0.82rem;"><span class="status-dot"></span>{{ __('Active') }}</span>
            </div>
        </div>

        {{-- System Maintenance --}}
        <div class="side-panel-card" style="padding: 0; overflow: hidden; border: 1px solid var(--border);">
            <div style="padding: 1.25rem 1.25rem 0.75rem;">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800;">
                    <i class="ph ph-wrench" style="color: var(--text-primary);"></i>
                    {{ __('System Maintenance') }}
                </h4>
            </div>
            
            <div style="display: flex; flex-direction: column;">
                <!-- Integrity Check Row -->
                <div id="integrityRow" style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.05); transition: background 0.2s; cursor: pointer;" 
                     onclick="runIntegrityCheck()" 
                     onmouseover="this.style.background='rgba(239,68,68,0.05)'" 
                     onmouseout="this.style.background='transparent'">
                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                        <div style="width: 34px; height: 34px; border-radius: 0.6rem; background: rgba(239,68,68,0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                            <i id="integrityIcon" class="ph ph-shield-check"></i>
                        </div>
                        <div>
                            <div id="integrityTitle" style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary); line-height: 1.2;">{{ __('Run Integrity') }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">{{ __('Scan for orphan records') }}</div>
                        </div>
                    </div>
                    <i class="ph ph-caret-right" style="color: var(--text-muted); font-size: 0.85rem;"></i>
                </div>

                <!-- Clear Cache Row -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.05); transition: background 0.2s; cursor: pointer;" 
                     onclick="document.getElementById('form-clear-cache').submit()" 
                     onmouseover="this.style.background='rgba(255,255,255,0.03)'" 
                     onmouseout="this.style.background='transparent'">
                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                        <div style="width: 34px; height: 34px; border-radius: 0.6rem; background: rgba(255,255,255,0.05); color: var(--text-secondary); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                            <i class="ph ph-broom"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary); line-height: 1.2;">{{ __('Clear Cache') }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">{{ __('Refresh system data') }}</div>
                        </div>
                    </div>
                    <i class="ph ph-caret-right" style="color: var(--text-muted); font-size: 0.85rem;"></i>
                </div>
            </div>
            
            <div id="maintenance-results" style="padding: 1.25rem; border-top: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.1);">
                <div id="integrity-details" style="font-size: 0.75rem; color: var(--text-muted); text-align: center; padding: 0.75rem; background: var(--bg-dark); border-radius: 0.6rem; border: 1px dashed var(--border);">
                    <i class="ph ph-info" style="margin-right: 0.3rem;"></i>
                    {{ __('Scan database and clear stale cache.') }}
                </div>
            </div>

            <form id="form-clear-cache" action="{{ route('security.clear-cache') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>



        {{-- Today's Teacher Check-ins --}}
        <div class="side-panel-card" style="border-top: 3px solid #10b981;">
            <h4>
                <i class="ph ph-user-check" style="color: #10b981;"></i>
                {{ __("Today's Teacher Check-ins") }}
            </h4>
            <div style="max-height: 280px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.2rem;">
                @forelse($todayCheckIns ?? [] as $att)
                    @php
                        $tName = $att->teacher ? (app()->getLocale() == 'km' ? ($att->teacher->name_kh ?: $att->teacher->name) : $att->teacher->name) : __('Unknown');
                        $tId   = $att->teacher->employee_id ?? '-';
                        $timeStr = $att->afternoon_in ? \Carbon\Carbon::parse($att->afternoon_in)->format('h:i A') : ($att->morning_in ? \Carbon\Carbon::parse($att->morning_in)->format('h:i A') : '');
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.75rem; background: var(--bg-dark); border: 1px solid var(--border); border-radius: 0.75rem; font-size: 0.8rem;">
                        <div>
                            <div style="font-weight: 800; color: var(--text-primary);">{{ $tName }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">
                                <span style="color: var(--primary); font-weight: 700;">ID: {{ $tId }}</span> &bull; {{ $att->teacher->department ?? '' }}
                            </div>
                        </div>
                        <span style="font-family: monospace; font-size: 0.75rem; font-weight: 800; background: rgba(16,185,129,0.15); color: #10b981; padding: 0.25rem 0.55rem; border-radius: 0.4rem; border: 1px solid rgba(16,185,129,0.3);" title="{{ __('Check-in time') }}">
                            <i class="ph ph-clock" style="margin-right: 2px;"></i>{{ $timeStr }}
                        </span>
                    </div>
                @empty
                    <div style="font-size: 0.78rem; color: var(--text-muted); text-align: center; padding: 1rem;">
                        {{ __('No teacher check-ins logged for today yet.') }}
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Telegram Delivery Status --}}
        <div class="side-panel-card" style="border-top: 3px solid #0ea5e9;">
            <h4>
                <i class="ph ph-telegram-logo" style="color: #0ea5e9;"></i>
                {{ __('Telegram Notifications') }}
            </h4>
            <div style="max-height: 280px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.2rem;">
                @forelse($telegramLogs ?? [] as $tlog)
                    @php
                        $isSuccess = $tlog->action === 'telegram_sent';
                        $tTime = \Carbon\Carbon::parse($tlog->timestamp)->format('h:i A');
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.75rem; background: var(--bg-dark); border: 1px solid var(--border); border-radius: 0.75rem; font-size: 0.8rem;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $tlog->target }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                                @if($isSuccess)
                                    <span style="color: #10b981; font-weight: 700;"><i class="ph ph-check-circle" style="vertical-align: middle;"></i> {{ __('Sent') }}</span>
                                @else
                                    <span style="color: #ef4444; font-weight: 700;"><i class="ph ph-warning-circle" style="vertical-align: middle;"></i> {{ __('Failed') }}</span>
                                @endif
                                &bull; <span title="{{ $tlog->details }}" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Str::limit(str_replace(['Telegram notification sent [', 'Telegram notification FAILED [', ']'], '', $tlog->details), 20) }}</span>
                            </div>
                        </div>
                        <span style="font-family: monospace; font-size: 0.75rem; font-weight: 800; background: rgba(14,165,233,0.15); color: #0ea5e9; padding: 0.25rem 0.55rem; border-radius: 0.4rem; border: 1px solid rgba(14,165,233,0.3);" title="{{ __('Sent time') }}">
                            <i class="ph ph-clock" style="margin-right: 2px;"></i>{{ $tTime }}
                        </span>
                    </div>
                @empty
                    <div style="font-size: 0.78rem; color: var(--text-muted); text-align: center; padding: 1rem;">
                        {{ __('No recent Telegram notifications.') }}
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Security Note --}}
        <div class="side-panel-card">
            <h4>
                <i class="ph ph-lock-key" style="color: #f59e0b;"></i>
                {{ __('Security Policy') }}
            </h4>
            <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.7; margin: 0;">
                {{ __('All administrative actions are recorded with timestamps, admin identity, and originating IP to ensure full institutional accountability.') }}
            </p>
            <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 0.75rem; font-size: 0.75rem; color: #f59e0b; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-warning"></i>
                {{ __('Logs cannot be edited or deleted by administrators.') }}
            </div>
        </div>



    </div>
</div>


{{-- Log Detail Modal --}}
<div class="modal-overlay" id="logModal" onclick="if(event.target===this) closeLogModal()">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="ph ph-info"></i> {{ __('Audit Log Details') }}</h3>
            <button class="modal-close" onclick="closeLogModal()">&times;</button>
        </div>
        <div style="padding: 1.5rem;">
            <div class="log-detail-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <div id="modal-timestamp" style="font-family: monospace; font-size: 0.85rem; color: var(--text-secondary);"></div>
                    <span id="modal-badge" class="action-badge">
                        <i id="modal-icon" class="ph"></i>
                        <span id="modal-action"></span>
                    </span>
                </div>
                
                <div class="log-detail-item">
                    <div class="label">{{ __('Administrator') }}</div>
                    <div class="value" id="modal-admin"></div>
                </div>
                
                <div class="log-detail-item">
                    <div class="label">{{ __('Target Entity') }}</div>
                    <div class="value" id="modal-target"></div>
                </div>
                
                <div class="log-detail-item">
                    <div class="label">{{ __('IP Address') }}</div>
                    <div class="value code" id="modal-ip"></div>
                </div>
            </div>

            <div class="log-detail-item">
                <div class="label">{{ __('Action Details') }}</div>
                <div class="value code" id="modal-details" style="min-height: 80px; white-space: pre-wrap; overflow-x: auto;"></div>
            </div>

            <div style="margin-top: 1.5rem; text-align: right;">
                <button class="btn btn-secondary" onclick="closeLogModal()">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>


{{-- Stat Detail Modal --}}
<div class="modal-overlay" id="statModal" onclick="if(event.target===this) closeStatModal()">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 id="stat-modal-title"></h3>
            <button class="modal-close" onclick="closeStatModal()">&times;</button>
        </div>
        <div style="padding: 1.5rem;">
            <div class="log-table-wrap" style="max-height: 400px; border: 1px solid var(--border); border-radius: 1rem;">
                <table class="table" style="margin: 0; font-size: 0.85rem;">
                    <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10;">
                        <tr>
                            <th>{{ __('Timestamp') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Target') }}</th>
                            <th>{{ __('Admin') }}</th>
                        </tr>
                    </thead>
                    <tbody id="stat-modal-body"></tbody>
                </table>
            </div>
            <div style="margin-top: 1.5rem; text-align: right;">
                <button class="btn btn-secondary" onclick="closeStatModal()">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    async function runIntegrityCheck() {
        const row = document.getElementById('integrityRow');
        const icon = document.getElementById('integrityIcon');
        const title = document.getElementById('integrityTitle');
        
        const details = document.getElementById('integrity-details');

        if(row) row.style.pointerEvents = 'none';
        if(icon) icon.className = 'ph ph-circle-notch animate-spin';
        if(title) title.textContent = '{{ __("Scanning...") }}';
        
        details.innerHTML = '<i class="ph ph-circle-notch animate-spin" style="margin-right:0.3rem;"></i> {{ __("Running integrity scan...") }}';

        try {
            const data = await window.fetchApi("{{ route('security.integrity') }}");

            const attOk  = data.orphaned_attendance === 0;
            const rfidOk = data.orphaned_rfid === 0;
            const healthy = attOk && rfidOk;

            // Update results directly in the card for better visibility
            const panelArea = document.getElementById('maintenance-results');
            if (panelArea) panelArea.style.background = healthy ? 'rgba(16,185,129,0.05)' : 'rgba(239,68,68,0.05)';

            details.innerHTML = `
                <div style="text-align: center; margin-bottom: 1rem;">
                    <span style="color: ${healthy ? 'var(--success)' : 'var(--danger)'}; font-weight: 800; font-size: 0.9rem;">
                        <i class="ph ph-${healthy ? 'check-circle' : 'warning'}" style="font-size: 1.2rem; vertical-align: middle;"></i>
                        ${healthy ? '{{ __("System Healthy") }}' : '{{ __("Issues Detected!") }}'}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ __("Orphan Attendance") }}</span>
                    <span class="action-badge ${attOk ? 'success' : 'danger'}" style="font-size: 0.7rem;">
                        ${data.orphaned_attendance} {{ __("pts") }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ __("Orphan RFID") }}</span>
                    <span class="action-badge ${rfidOk ? 'success' : 'danger'}" style="font-size: 0.7rem;">
                        ${data.orphaned_rfid} {{ __("pts") }}
                    </span>
                </div>
                <div style="font-size: 0.65rem; color: var(--text-muted); border-top: 1px dashed var(--border); padding-top: 0.5rem; margin-top: 0.5rem;">
                    {{ __('Last Checked') }}: ${new Date().toLocaleTimeString()}
                </div>
            `;

            window.showToast(
                healthy ? '{{ __("Database integrity verified — All clean!") }}' : '{{ __("Issues found — Review the report.") }}',
                healthy ? 'success' : 'warning'
            );
        } catch (e) {
            details.innerHTML = '<span style="color: var(--danger);">{{ __("Scan failed. Check server logs.") }}</span>';
            window.showToast('{{ __("Integrity check failed.") }}', 'error');
        } finally {
            if(row) row.style.pointerEvents = 'auto';
            if(icon) icon.className = 'ph ph-shield-check';
            if(title) title.textContent = '{{ __("Run Integrity") }}';
        }
    }

    function showLogDetails(log) {
        // Format timestamp
        const date = new Date(log.timestamp);
        const ts = date.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
        
        document.getElementById('modal-timestamp').textContent = ts;
        document.getElementById('modal-admin').textContent = log.admin ? log.admin.name : '{{ __("System") }}';
        document.getElementById('modal-action').textContent = log.action;
        document.getElementById('modal-target').textContent = log.target || '—';
        document.getElementById('modal-ip').textContent = log.ip_address || '—';
        // Format the details field
        const detailsEl = document.getElementById('modal-details');
        const rawDetails = log.details || '';
        let parsedDetails = null;
        try { parsedDetails = JSON.parse(rawDetails); } catch(e) {}

        if (parsedDetails && parsedDetails.old && parsedDetails.new) {
            detailsEl.style.whiteSpace = 'normal';
            // Edit Attendance audit entry — render a clean Old vs New diff
            const fmt = v => v || '—';
            const row = (label, oldVal, newVal) => {
                const changed = (oldVal || '') !== (newVal || '');
                const cls = changed ? 'changed' : 'unchanged';
                return `<div class="diff-row ${cls}">
                    <span class="diff-label">${label}</span>
                    <span class="diff-old">${fmt(oldVal)}</span>
                    <span class="diff-new">${fmt(newVal)}</span>
                </div>`;
            };
            detailsEl.innerHTML = `
                <div class="diff-reason">
                    <i class="ph ph-chat-text" style="color:#f59e0b;font-size:1rem;flex-shrink:0;margin-top:2px;"></i>
                    <span><span style="color:#d97706;font-weight:800;">Reason: </span>${parsedDetails.new.reason || '—'}</span>
                </div>
                <div class="diff-header">
                    <span style="color:var(--text-muted);">Field</span>
                    <span style="color:#dc2626;">◀ Before</span>
                    <span style="color:#16a34a;">After ▶</span>
                </div>
                ${row('Morning In',   parsedDetails.old.morning_in,       parsedDetails.new.morning_in)}
                ${row('Morning Out',  parsedDetails.old.morning_out,      parsedDetails.new.morning_out)}
                ${row('Morn. Status', parsedDetails.old.morning_status,   parsedDetails.new.morning_status)}
                ${row('Aftern. In',   parsedDetails.old.afternoon_in,     parsedDetails.new.afternoon_in)}
                ${row('Aftern. Out',  parsedDetails.old.afternoon_out,    parsedDetails.new.afternoon_out)}
                ${row('Aftn. Status', parsedDetails.old.afternoon_status, parsedDetails.new.afternoon_status)}
            `;
        } else if (parsedDetails) {
            // Generic JSON — pretty-print
            detailsEl.style.whiteSpace = 'pre-wrap';
            detailsEl.textContent = JSON.stringify(parsedDetails, null, 2);
        } else {
            detailsEl.style.whiteSpace = 'pre-wrap';
            detailsEl.textContent = rawDetails || '{{ __("No additional details recorded.") }}';
        }
        
        // Determine badge type based on action
        const action = log.action.toLowerCase();
        let badgeClass = 'secondary';
        let badgeIcon  = 'ph-circle';
        
        if (action.includes('delete') || action.includes('remove')) {
            badgeClass = 'danger'; badgeIcon = 'ph-trash';
        } else if (action.includes('login') || action.includes('logout')) {
            badgeClass = 'success'; badgeIcon = 'ph-sign-in';
        } else if (action.includes('create') || action.includes('register') || action.includes('add')) {
            badgeClass = 'primary'; badgeIcon = 'ph-plus-circle';
        } else if (action.includes('update') || action.includes('edit') || action.includes('adjust')) {
            badgeClass = 'warning'; badgeIcon = 'ph-pencil-simple';
        } else if (action.includes('fail') || action.includes('error')) {
            badgeClass = 'danger'; badgeIcon = 'ph-warning-circle';
        } else if (action.includes('clear') || action.includes('cache')) {
            badgeClass = 'info'; badgeIcon = 'ph-broom';
        }

        const badge = document.getElementById('modal-badge');
        badge.className = `action-badge ${badgeClass}`;
        
        const icon = document.getElementById('modal-icon');
        icon.className = `ph ${badgeIcon}`;
        
        document.getElementById('logModal').classList.add('active');
    }

    function closeLogModal() {
        document.getElementById('logModal').classList.remove('active');
    }

    function showStatDetail(title, logsJson) {
        const logs = JSON.parse(logsJson);
        document.getElementById('stat-modal-title').textContent = title + ' (' + logs.length + ')';
        
        const tbody = document.getElementById('stat-modal-body');
        tbody.innerHTML = '';
        
        if (logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted);">{{ __("No logs found for this category.") }}</td></tr>';
        } else {
            logs.forEach(log => {
                const date = new Date(log.timestamp);
                const timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const dateStr = date.toLocaleDateString('en-GB', {day:'2-digit', month:'short'});
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-family:monospace;font-size:0.75rem;">${dateStr}<br><span style="color:var(--text-muted)">${timeStr}</span></td>
                    <td style="font-weight:700;color:var(--primary)">${log.action}</td>
                    <td style="font-size:0.8rem;">${log.target || '—'}</td>
                    <td>${log.admin ? log.admin.name : '<span style="color:var(--text-muted)">System</span>'}</td>
                `;
                tr.style.cursor = 'pointer';
                tr.onclick = () => {
                    closeStatModal();
                    showLogDetails(log);
                };
                tbody.appendChild(tr);
            });
        }
        
        document.getElementById('statModal').classList.add('active');
    }

    function closeStatModal() {
        document.getElementById('statModal').classList.remove('active');
    }
</script>
@endpush

