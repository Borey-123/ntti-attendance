@extends('layouts.app')

@section('title', __('Payroll Detail') . ' - ' . ($payroll->teacher->name ?? ''))

@section('content')
@php $sym = $settings['currency_symbol'] ?? '$'; @endphp

<div style="max-width:900px; margin:0 auto;">

{{-- Header --}}
<div style="display:flex; align-items:center; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
    <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="padding:0.5rem 0.75rem;">
        <i class="ph ph-arrow-left"></i>
    </a>
    <div style="flex:1;">
        <h1 style="font-size:1.5rem; font-weight:900; margin:0;">
            💰 {{ $payroll->teacher->name ?? 'N/A' }}
            <span style="font-size:1rem; color:var(--text-muted); font-weight:400;">— {{ $payroll->month->format('F Y') }}</span>
        </h1>
        <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.25rem;">
            {{ $payroll->teacher->employee_id ?? '' }} &bull; {{ $payroll->teacher->department ?? '' }} &bull; {{ $payroll->teacher->position ?? '' }}
        </div>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="{{ route('payroll.pdf', $payroll->id) }}" class="btn btn-secondary" target="_blank" style="gap:0.5rem;">
            <i class="ph ph-file-pdf"></i> {{ __('Download PDF') }}
        </a>
        @if($payroll->status === 'draft')
        <button onclick="approvePayroll({{ $payroll->id }})" class="btn btn-primary" style="gap:0.5rem; background:linear-gradient(135deg,#6366f1,#8b5cf6);">
            <i class="ph ph-check-circle"></i> {{ __('Approve') }}
        </button>
        @elseif($payroll->status === 'approved')
        <button onclick="markPaid({{ $payroll->id }})" class="btn btn-primary" style="gap:0.5rem;">
            <i class="ph ph-money"></i> {{ __('Mark as Paid') }}
        </button>
        @endif
    </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; align-items:start;">

    {{-- Left Column --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        {{-- Attendance Summary --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h3 style="margin:0 0 1.25rem; font-size:1rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-calendar-check" style="color:var(--primary);"></i> {{ __('Attendance Summary') }}
            </h3>
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.25rem;">
                <div style="text-align:center; padding:1rem; background:rgba(0,212,160,0.08); border:1px solid rgba(0,212,160,0.2); border-radius:0.75rem;">
                    <div style="font-size:1.75rem; font-weight:900; color:#00d4a0;">{{ $payroll->present_days }}</div>
                    <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Present') }}</div>
                </div>
                <div style="text-align:center; padding:1rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:0.75rem;">
                    <div style="font-size:1.75rem; font-weight:900; color:#ef4444;">{{ $payroll->absent_days }}</div>
                    <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Absent') }}</div>
                </div>
                <div style="text-align:center; padding:1rem; background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); border-radius:0.75rem;">
                    <div style="font-size:1.75rem; font-weight:900; color:#f59e0b;">{{ $payroll->late_count }}</div>
                    <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Late') }}</div>
                </div>
            </div>

            {{-- Attendance Rate Bar --}}
            <div>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:0.4rem;">
                    <span style="color:var(--text-secondary); font-weight:600;">{{ __('Attendance Rate') }}</span>
                    <span style="font-weight:800; color:{{ $payroll->attendance_rate >= 90 ? '#00d4a0' : ($payroll->attendance_rate >= 70 ? '#f59e0b' : '#ef4444') }}">{{ $payroll->attendance_rate }}%</span>
                </div>
                <div style="height:8px; background:rgba(255,255,255,0.08); border-radius:99px; overflow:hidden;">
                    <div style="width:{{ $payroll->attendance_rate }}%; height:100%; background:{{ $payroll->attendance_rate >= 90 ? '#00d4a0' : ($payroll->attendance_rate >= 70 ? '#f59e0b' : '#ef4444') }}; border-radius:99px; transition:width 0.5s;"></div>
                </div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.3rem;">
                    {{ $payroll->present_days }} / {{ $payroll->working_days }} {{ __('working days') }}
                    @if($payroll->approved_leave_days > 0)
                        &bull; {{ $payroll->approved_leave_days }} {{ __('approved leave (paid)') }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Salary Breakdown --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h3 style="margin:0 0 1.25rem; font-size:1rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-receipt" style="color:var(--primary);"></i> {{ __('Salary Breakdown') }}
            </h3>
            <div style="display:flex; flex-direction:column; gap:0;">
                @php
                    $rows = [
                        ['label' => __('Base Salary'), 'value' => $payroll->base_salary, 'color' => 'var(--text-primary)', 'prefix' => '+'],
                        ['label' => __('Daily Rate'), 'value' => $payroll->daily_rate, 'color' => 'var(--text-secondary)', 'prefix' => '', 'sub' => true],
                        ['label' => __('Gross Salary'), 'value' => $payroll->gross_salary, 'color' => '#6366f1', 'prefix' => ''],
                        ['label' => __('Late Deduction') . ' (' . $payroll->late_minutes_total . ' min)', 'value' => $payroll->late_deduction, 'color' => '#ef4444', 'prefix' => '-'],
                        ['label' => __('Absent Deduction') . ' (' . $payroll->absent_days . ' days)', 'value' => $payroll->absent_deduction, 'color' => '#ef4444', 'prefix' => '-'],
                        ['label' => __('Bonus'), 'value' => $payroll->bonus, 'color' => '#10b981', 'prefix' => '+'],
                    ];
                @endphp
                @foreach($rows as $row)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 0; border-bottom:1px solid var(--border); {{ $row['sub'] ?? false ? 'opacity:0.7; padding-left:1rem;' : '' }}">
                    <span style="font-size:0.85rem; color:var(--text-secondary);">
                        @if($row['sub'] ?? false)<i class="ph ph-arrow-bend-down-right" style="font-size:0.75rem;"></i> @endif
                        {{ $row['label'] }}
                    </span>
                    <span style="font-weight:700; color:{{ $row['color'] }}; font-size:0.9rem;">
                        {{ $row['prefix'] }}{{ $sym }}{{ number_format($row['value'], 2) }}
                    </span>
                </div>
                @endforeach

                {{-- Net Salary --}}
                <div style="display:flex; justify-content:space-between; align-items:center; padding:1rem 0; border-top:2px solid var(--border); margin-top:0.25rem;">
                    <span style="font-size:1rem; font-weight:800; color:var(--text-primary);">💰 {{ __('Net Salary') }}</span>
                    <span style="font-size:1.4rem; font-weight:900; color:#10b981;">{{ $sym }}{{ number_format($payroll->net_salary, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Attendance Log --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h3 style="margin:0 0 1.25rem; font-size:1rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-list-checks" style="color:var(--primary);"></i> {{ __('Daily Attendance Log') }}
            </h3>
            @if($attendances->isEmpty())
                <div style="text-align:center; color:var(--text-muted); padding:1.5rem;">{{ __('No attendance records') }}</div>
            @else
                <div style="max-height:300px; overflow-y:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.82rem;">
                        <thead>
                            <tr style="background:rgba(255,255,255,0.03);">
                                <th style="padding:0.5rem 0.75rem; text-align:left; color:var(--text-muted); font-weight:700;">{{ __('Date') }}</th>
                                <th style="padding:0.5rem; text-align:center; color:var(--text-muted); font-weight:700;">Morning In</th>
                                <th style="padding:0.5rem; text-align:center; color:var(--text-muted); font-weight:700;">Morning Out</th>
                                <th style="padding:0.5rem; text-align:center; color:var(--text-muted); font-weight:700;">Afternoon In</th>
                                <th style="padding:0.5rem; text-align:center; color:var(--text-muted); font-weight:700;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $att)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:0.5rem 0.75rem; color:var(--text-primary); font-weight:600;">{{ $att->date->format('d M') }}</td>
                                <td style="padding:0.5rem; text-align:center; color:{{ $att->morning_in ? '#00d4a0' : 'var(--text-muted)' }};">{{ $att->morning_in ?? '—' }}</td>
                                <td style="padding:0.5rem; text-align:center; color:var(--text-secondary);">{{ $att->morning_out ?? '—' }}</td>
                                <td style="padding:0.5rem; text-align:center; color:var(--text-secondary);">{{ $att->afternoon_in ?? '—' }}</td>
                                <td style="padding:0.5rem; text-align:center;">
                                    @if($att->morning_status === 'late')
                                        <span style="color:#f59e0b; font-size:0.7rem; font-weight:700;">LATE</span>
                                    @else
                                        <span style="color:#00d4a0; font-size:0.7rem; font-weight:700;">OK</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Right Column --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        {{-- Status Card --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem; text-align:center; border-top:4px solid {{ $payroll->status_color }};">
            <i class="ph {{ $payroll->status_icon }}" style="font-size:2.5rem; color:{{ $payroll->status_color }}; display:block; margin-bottom:0.75rem;"></i>
            <div style="font-size:1.1rem; font-weight:900; color:{{ $payroll->status_color }}; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">{{ $payroll->status }}</div>
            @if($payroll->approved_at)
                <div style="font-size:0.78rem; color:var(--text-muted);">
                    {{ __('Approved by') }}: <strong>{{ $payroll->approvedByUser->name ?? 'Admin' }}</strong><br>
                    {{ $payroll->approved_at->format('d M Y H:i') }}
                </div>
            @endif
            @if($payroll->paid_at)
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.5rem;">
                    {{ __('Paid at') }}: <strong>{{ $payroll->paid_at->format('d M Y H:i') }}</strong>
                </div>
            @endif
        </div>

        {{-- Quick Info --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h4 style="margin:0 0 1rem; font-size:0.85rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">{{ __('Period Info') }}</h4>
            <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.85rem;">
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">{{ __('Month') }}</span>
                    <strong>{{ $payroll->month->format('F Y') }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">{{ __('Working Days') }}</span>
                    <strong>{{ $payroll->working_days }}</strong>
                </div>
                @if($payroll->academicPeriod)
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">{{ __('Period') }}</span>
                    <strong style="color:#a5b4fc; font-size:0.8rem;">{{ $payroll->academicPeriod->display_name }}</strong>
                </div>
                @endif
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">{{ __('Late Time') }}</span>
                    <strong style="{{ $payroll->late_minutes_total > 0 ? 'color:#f59e0b;' : '' }}">{{ $payroll->late_minutes_total }} min</strong>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if($payroll->notes)
        <div class="card" style="border-radius:1.25rem; padding:1.25rem;">
            <h4 style="margin:0 0 0.75rem; font-size:0.85rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">{{ __('Notes') }}</h4>
            <p style="font-size:0.85rem; color:var(--text-secondary); margin:0; line-height:1.6;">{{ $payroll->notes }}</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function approvePayroll(id) {
    if (!confirm('Approve this payroll?')) return;
    const res = await fetch(`/payroll/${id}/approve`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) location.reload(); else alert(data.message);
}

async function markPaid(id) {
    if (!confirm('Mark this payroll as PAID?')) return;
    const res = await fetch(`/payroll/${id}/paid`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) location.reload(); else alert(data.message);
}
</script>
@endpush
</div>
@endsection
