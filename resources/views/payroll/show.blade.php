@extends('layouts.app')

@section('title', __('Payroll Detail') . ' - ' . ($payroll->teacher->name ?? ''))

@section('content')
@php 
    $sym = $settings['currency_symbol'] ?? '$';
    $teacherName = $payroll->teacher->name_kh ?: $payroll->teacher->name;
@endphp

<div class="animate-fade-up" style="max-width: 1000px; margin: 0 auto;">

    {{-- ── Top Navigation / Actions Bar ── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <div style="display:flex; align-items:center; gap:1rem;">
            <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="border-radius:0.75rem; padding:0.6rem 0.85rem;" title="{{ __('Back') }}">
                <i class="ph ph-arrow-left" style="font-size:1.15rem;"></i>
            </a>
            <div>
                <h1 style="font-size:1.6rem; font-weight:900; margin:0; display:flex; align-items:center; gap:0.5rem;">
                    <span>{{ $teacherName }}</span>
                    @if($payroll->teacher->name_kh)
                        <span style="font-size:1rem; font-weight:600; color:var(--text-secondary);">({{ $payroll->teacher->name }})</span>
                    @endif
                </h1>
                <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.25rem;">
                    {{ $payroll->teacher->employee_id ?? '' }} &bull; {{ $payroll->teacher->department ?? '' }} &bull; {{ $payroll->month->format('F Y') }}
                </div>
            </div>
        </div>

        <div style="display:flex; gap:0.65rem; flex-wrap:wrap;">
            <a href="{{ route('payroll.pdf', $payroll->id) }}" class="btn btn-secondary" target="_blank" style="border-radius:0.85rem; font-weight:700; padding:0.65rem 1.15rem; color:#818cf8; border-color:rgba(99,102,241,0.35);">
                <i class="ph ph-file-pdf" style="font-size:1.2rem;"></i> {{ __('Download Payslip (PDF)') }}
            </a>

            @if($payroll->status === 'draft')
            <button onclick="approvePayroll({{ $payroll->id }}, '{{ addslashes($teacherName) }}')" class="btn btn-primary" style="border-radius:0.85rem; font-weight:800; padding:0.65rem 1.25rem; background:#10b981; border-color:#10b981; box-shadow:0 8px 20px rgba(16,185,129,0.3);">
                <i class="ph ph-check-circle" style="font-size:1.2rem;"></i> {{ __('Approve Payroll') }}
            </button>
            @elseif($payroll->status === 'approved')
            <button onclick="markPaid({{ $payroll->id }}, '{{ addslashes($teacherName) }}')" class="btn btn-primary" style="border-radius:0.85rem; font-weight:800; padding:0.65rem 1.25rem; background:#0ea5e9; border-color:#0ea5e9; box-shadow:0 8px 20px rgba(14,165,233,0.3);">
                <i class="ph ph-money" style="font-size:1.2rem;"></i> {{ __('Mark as Paid') }}
            </button>
            @endif
        </div>
    </div>

    {{-- ── Main Voucher Layout ── --}}
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.75rem; align-items:start;">

        {{-- Left Column: Summary & Breakdown --}}
        <div style="display:flex; flex-direction:column; gap:1.75rem;">

            {{-- Teacher Banner Card --}}
            <div class="card" style="border-radius:1.5rem; padding:1.5rem; border:1px solid var(--border); display:flex; align-items:center; gap:1.25rem; background:linear-gradient(135deg,rgba(var(--primary-rgb),0.05),transparent);">
                @if($payroll->teacher->photo)
                    <img src="{{ to_asset_url($payroll->teacher->photo) }}" style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid var(--primary);" alt="">
                @else
                    <div style="width:72px; height:72px; border-radius:50%; background:rgba(var(--primary-rgb),0.15); color:var(--primary); font-size:2rem; font-weight:800; display:flex; align-items:center; justify-content:center; border:3px solid var(--border);">
                        {{ substr($payroll->teacher->name, 0, 1) }}
                    </div>
                @endif
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:0.65rem; flex-wrap:wrap;">
                        <h2 style="margin:0; font-size:1.35rem; font-weight:900; color:var(--text-primary);">{{ $teacherName }}</h2>
                        <span style="background:rgba(var(--primary-rgb),0.12); color:var(--primary); padding:0.2rem 0.6rem; border-radius:0.5rem; font-family:monospace; font-size:0.8rem; font-weight:700;">
                            {{ $payroll->teacher->employee_id }}
                        </span>
                    </div>
                    <div style="color:var(--text-secondary); font-size:0.88rem; margin-top:0.35rem;">
                        {{ $payroll->teacher->department }} &bull; <strong style="color:#10b981;">{{ $payroll->teacher->position_rank ?: ($payroll->teacher->position ?: __('Instructor')) }}</strong>
                    </div>
                </div>
                <div style="text-align:right;">
                    @if($payroll->status === 'draft')
                        <span style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid rgba(245,158,11,0.3); padding:0.4rem 0.85rem; border-radius:999px; font-weight:800; font-size:0.8rem; text-transform:uppercase;">
                            📝 {{ __('Draft') }}
                        </span>
                    @elseif($payroll->status === 'approved')
                        <span style="background:rgba(99,102,241,0.15); color:#818cf8; border:1px solid rgba(99,102,241,0.3); padding:0.4rem 0.85rem; border-radius:999px; font-weight:800; font-size:0.8rem; text-transform:uppercase;">
                            ✓ {{ __('Approved') }}
                        </span>
                    @else
                        <span style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:0.4rem 0.85rem; border-radius:999px; font-weight:800; font-size:0.8rem; text-transform:uppercase;">
                            💰 {{ __('Paid') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Attendance Performance Stats --}}
            <div class="card" style="border-radius:1.5rem; padding:1.75rem; border:1px solid var(--border);">
                <h3 style="margin:0 0 1.25rem; font-size:1.1rem; font-weight:800; display:flex; align-items:center; gap:0.5rem; color:var(--text-primary);">
                    <i class="ph ph-calendar-check" style="color:var(--primary);"></i>
                    <span>{{ __('Attendance Performance') }}</span>
                </h3>

                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
                    <div style="text-align:center; padding:1.15rem 0.75rem; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:1rem;">
                        <div style="font-size:1.85rem; font-weight:900; color:#10b981;">{{ $payroll->present_days }}</div>
                        <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:700; text-transform:uppercase; margin-top:2px;">{{ __('Present Days') }}</div>
                    </div>

                    <div style="text-align:center; padding:1.15rem 0.75rem; background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); border-radius:1rem;">
                        <div style="font-size:1.85rem; font-weight:900; color:#f59e0b;">{{ $payroll->late_minutes }}</div>
                        <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:700; text-transform:uppercase; margin-top:2px;">{{ __('Late Minutes') }}</div>
                    </div>

                    <div style="text-align:center; padding:1.15rem 0.75rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:1rem;">
                        <div style="font-size:1.85rem; font-weight:900; color:#ef4444;">{{ $payroll->absent_days }}</div>
                        <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:700; text-transform:uppercase; margin-top:2px;">{{ __('Absent Days') }}</div>
                    </div>

                    <div style="text-align:center; padding:1.15rem 0.75rem; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2); border-radius:1rem;">
                        <div style="font-size:1.85rem; font-weight:900; color:#818cf8;">{{ $payroll->approved_leave_days }}</div>
                        <div style="font-size:0.75rem; color:var(--text-secondary); font-weight:700; text-transform:uppercase; margin-top:2px;">{{ __('Paid Leave Days') }}</div>
                    </div>
                </div>

                {{-- Attendance Rate Progress Bar --}}
                <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border); border-radius:1rem; padding:1rem 1.25rem;">
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:700; margin-bottom:0.4rem;">
                        <span style="color:var(--text-secondary);">{{ __('Attendance Rate') }}</span>
                        <span style="color:{{ $payroll->attendance_rate >= 90 ? '#10b981' : ($payroll->attendance_rate >= 75 ? '#f59e0b' : '#ef4444') }}; font-weight:900;">
                            {{ $payroll->attendance_rate }}%
                        </span>
                    </div>
                    <div style="height:10px; background:rgba(255,255,255,0.08); border-radius:999px; overflow:hidden;">
                        <div style="width:{{ $payroll->attendance_rate }}%; height:100%; background:{{ $payroll->attendance_rate >= 90 ? '#10b981' : ($payroll->attendance_rate >= 75 ? '#f59e0b' : '#ef4444') }}; border-radius:999px; transition:width 0.5s ease;"></div>
                    </div>
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.4rem;">
                        {{ $payroll->present_days }} / {{ $payroll->working_days }} {{ __('Working Days') }}
                    </div>
                </div>
            </div>

            {{-- Salary Calculation Breakdown --}}
            <div class="card" style="border-radius:1.5rem; padding:1.75rem; border:1px solid var(--border);">
                <h3 style="margin:0 0 1.25rem; font-size:1.1rem; font-weight:800; display:flex; align-items:center; gap:0.5rem; color:var(--text-primary);">
                    <i class="ph ph-receipt" style="color:var(--primary);"></i>
                    <span>{{ __('Salary Breakdown') }}</span>
                </h3>

                <div style="display:flex; flex-direction:column; gap:0.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem 0; border-bottom:1px solid var(--border);">
                        <span style="font-weight:700; color:var(--text-secondary);">{{ __('Base Salary') }}</span>
                        <span style="font-weight:800; font-size:1.05rem; color:var(--text-primary);">+{{ $sym }}{{ number_format($payroll->base_salary, 2) }}</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.65rem 0; border-bottom:1px solid var(--border); opacity:0.8; padding-left:1rem; font-size:0.85rem;">
                        <span style="color:var(--text-muted);">{{ __('Daily Rate') }} ({{ $payroll->base_salary }} ÷ {{ $payroll->working_days }} ថ្ងៃ)</span>
                        <span style="color:var(--text-secondary);">{{ $sym }}{{ number_format($payroll->daily_rate, 2) }} / day</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem 0; border-bottom:1px solid var(--border);">
                        <div>
                            <span style="font-weight:700; color:#ef4444;">{{ __('Late Deduction') }}</span>
                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ $payroll->late_minutes }} នាទី × {{ $sym }}{{ number_format($settings['late_deduction_per_minute'] ?? $settings['late_deduction_rate'] ?? 0.50, 2) }}</div>
                        </div>
                        <span style="font-weight:800; font-size:1.05rem; color:#ef4444;">-{{ $sym }}{{ number_format($payroll->late_deduction, 2) }}</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem 0; border-bottom:1px solid var(--border);">
                        <div>
                            <span style="font-weight:700; color:#ef4444;">{{ __('Absent Deduction') }}</span>
                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ $payroll->absent_days }} ថ្ងៃ × Daily Rate × {{ $settings['absent_deduction_rate'] ?? 1.0 }}</div>
                        </div>
                        <span style="font-weight:800; font-size:1.05rem; color:#ef4444;">-{{ $sym }}{{ number_format($payroll->absent_deduction, 2) }}</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem 0; border-bottom:1px solid var(--border);">
                        <span style="font-weight:700; color:#10b981;">{{ __('Bonus') }}</span>
                        <span style="font-weight:800; font-size:1.05rem; color:#10b981;">+{{ $sym }}{{ number_format($payroll->bonus, 2) }}</span>
                    </div>

                    {{-- Net Payable --}}
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:1.25rem 0 0.5rem; margin-top:0.5rem;">
                        <div>
                            <div style="font-size:1.15rem; font-weight:900; color:var(--text-primary);">{{ __('Net Payable') }}</div>
                            <div style="font-size:0.8rem; color:var(--text-muted);">{{ __('Net Salary') }} = Gross - Deductions + Bonus</div>
                        </div>
                        <div style="font-size:1.85rem; font-weight:900; color:#10b981;">
                            {{ $sym }}{{ number_format($payroll->net_salary, 2) }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Meta & Actions --}}
        <div style="display:flex; flex-direction:column; gap:1.5rem;">

            {{-- 🇰🇭 Bakong KHQR Instant Payout Card --}}
            <div class="card" style="border-radius:1.5rem; padding:0; border:1px solid rgba(225,29,72,0.3); overflow:hidden; box-shadow:0 12px 30px rgba(225,29,72,0.12); background:var(--bg-card);">
                {{-- Red KHQR Banner Header --}}
                <div style="background:linear-gradient(135deg, #e11d48 0%, #be123c 100%); color:#ffffff; padding:1.15rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:0.6rem;">
                        <span style="background:#ffffff; color:#be123c; font-weight:900; font-size:0.85rem; padding:0.2rem 0.5rem; border-radius:0.35rem; letter-spacing:0.5px;">KHQR</span>
                        <div style="font-size:0.88rem; font-weight:800; letter-spacing:0.3px;">BAKONG PAYOUT</div>
                    </div>
                    <span style="font-size:0.75rem; background:rgba(255,255,255,0.2); padding:0.25rem 0.6rem; border-radius:99px; font-weight:700;">
                        {{ $payroll->status === 'paid' ? '✓ PAID' : 'READY TO SCAN' }}
                    </span>
                </div>

                <div style="padding:1.5rem; text-align:center;">
                    {{-- QR Code Container --}}
                    <div style="background:#ffffff; padding:12px; border-radius:1.25rem; display:inline-block; box-shadow:0 6px 16px rgba(0,0,0,0.08); border:2px solid #f1f5f9; margin-bottom:1rem; position:relative;">
                        <img src="{{ $khqr['qr_image_url'] }}" alt="Bakong KHQR Code" style="width:190px; height:190px; display:block; border-radius:0.5rem;">
                        @if($payroll->status === 'paid')
                        <div style="position:absolute; inset:0; background:rgba(16,185,129,0.85); border-radius:1.25rem; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff;">
                            <i class="ph ph-check-circle" style="font-size:3rem; margin-bottom:0.25rem;"></i>
                            <span style="font-weight:900; font-size:1rem; letter-spacing:0.5px;">PAID / រួចរាល់</span>
                        </div>
                        @endif
                    </div>

                    {{-- Amounts (USD & KHR) --}}
                    <div style="margin-bottom:1rem;">
                        <div style="font-size:1.6rem; font-weight:900; color:#e11d48; line-height:1.2;">
                            ${{ number_format($khqr['amount_usd'], 2) }}
                        </div>
                        <div style="font-size:0.88rem; font-weight:700; color:var(--text-secondary); margin-top:0.2rem;">
                            ≈ {{ number_format($khqr['amount_khr']) }} ៛ <span style="font-size:0.75rem; opacity:0.8;">(1$ = {{ number_format($khqr['khr_rate']) }} ៛)</span>
                        </div>
                    </div>

                    {{-- Beneficiary Details --}}
                    <div style="background:rgba(var(--primary-rgb),0.04); border:1px solid var(--border); border-radius:1rem; padding:0.85rem; text-align:left; font-size:0.8rem; margin-bottom:1rem; display:flex; flex-direction:column; gap:0.45rem;">
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">{{ __('Beneficiary') }}:</span>
                            <strong style="color:var(--text-primary);">{{ $khqr['account_name'] }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">{{ __('Bank') }}:</span>
                            <span style="font-weight:700; color:var(--text-primary);">{{ $khqr['bank_name'] }}</span>
                        </div>
                        @if(!empty($payroll->teacher->bank_account_number))
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">{{ __('Account No') }}:</span>
                            <code style="background:rgba(255,255,255,0.08); padding:0.1rem 0.4rem; border-radius:0.3rem;">{{ $khqr['account_number'] }}</code>
                        </div>
                        @endif
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">{{ __('Bakong ID') }}:</span>
                            <span style="font-weight:700; color:#818cf8; font-size:0.75rem;">{{ $khqr['bakong_id'] }}</span>
                        </div>
                    </div>

                    {{-- Banking App Support Notice --}}
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:1rem; line-height:1.4;">
                        📲 ស្កេនទូទាត់ជាមួយ App ធនាគារណាក៏បាន <br>
                        <strong>(ABA, ACLEDA, Wing, Canadia, Bakong...)</strong>
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex; gap:0.5rem; justify-content:center;">
                        <button type="button" onclick="copyKhqrData('{{ addslashes($khqr['khqr_string']) }}')" class="btn btn-secondary" style="border-radius:0.75rem; padding:0.5rem 0.85rem; font-size:0.8rem; font-weight:700;">
                            <i class="ph ph-copy"></i> {{ __('Copy KHQR String') }}
                        </button>
                        @if($payroll->status === 'approved')
                        <button type="button" onclick="markPaid({{ $payroll->id }}, '{{ addslashes($teacherName) }}')" class="btn btn-primary" style="background:#e11d48; border-color:#e11d48; border-radius:0.75rem; padding:0.5rem 0.85rem; font-size:0.8rem; font-weight:800;">
                            <i class="ph ph-check"></i> {{ __('Mark Paid via KHQR') }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Period & System Info --}}
            <div class="card" style="border-radius:1.5rem; padding:1.5rem; border:1px solid var(--border);">
                <h4 style="margin:0 0 1.25rem; font-size:0.85rem; font-weight:800; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px;">
                    {{ __('Period Info') }}
                </h4>
                <div style="display:flex; flex-direction:column; gap:1rem; font-size:0.88rem;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">{{ __('Month') }}</span>
                        <strong style="color:var(--text-primary);">{{ $payroll->month->format('F Y') }}</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">{{ __('Working Days') }}</span>
                        <strong style="color:var(--text-primary);">{{ $payroll->working_days }} {{ __('days') }}</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">{{ __('Status') }}</span>
                        <strong style="text-transform:uppercase; color:{{ $payroll->status === 'paid' ? '#10b981' : ($payroll->status === 'approved' ? '#818cf8' : '#f59e0b') }};">
                            {{ $payroll->status }}
                        </strong>
                    </div>
                    @if($payroll->approvedBy)
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">{{ __('Approved By') }}</span>
                        <strong style="color:var(--text-primary);">{{ $payroll->approvedBy->name }}</strong>
                    </div>
                    @endif
                    @if($payroll->approved_at)
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">{{ __('Approved At') }}</span>
                        <strong style="color:var(--text-primary);">{{ $payroll->approved_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notes Card --}}
            @if($payroll->notes)
            <div class="card" style="border-radius:1.5rem; padding:1.5rem; border:1px solid var(--border);">
                <h4 style="margin:0 0 0.5rem; font-size:0.85rem; font-weight:800; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px;">
                    {{ __('Notes') }}
                </h4>
                <p style="font-size:0.88rem; color:var(--text-secondary); margin:0; line-height:1.6;">
                    {{ $payroll->notes }}
                </p>
            </div>
            @endif

        </div>

    </div>

</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

async function approvePayroll(id, name) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមអនុម័តប័ណ្ណបើកប្រាក់បៀវត្សរ៍របស់ "' + name + '" ដែរឬទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'success'
    });
    if (!ok) return;

    try {
        const res = await fetch(`/payroll/${id}/approve`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast('{{ __("Payroll approved successfully!") }}', 'success');
            setTimeout(() => location.reload(), 400);
        } else {
            alert(data.message || 'Error');
        }
    } catch(err) { alert('Error: ' + err.message); }
}

async function markPaid(id, name) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមបញ្ជាក់ថាបានបើកប្រាក់បៀវត្សរ៍ជូន "' + name + '" រួចរាល់ហើយមែនទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'primary'
    });
    if (!ok) return;

    try {
        const res = await fetch(`/payroll/${id}/paid`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast('{{ __("Payroll marked as paid!") }}', 'success');
            setTimeout(() => location.reload(), 400);
        } else {
            alert(data.message || 'Error');
        }
    } catch(err) { alert('Error: ' + err.message); }
}

function copyKhqrData(str) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(str).then(() => {
            if (window.showToast) window.showToast('{{ __("KHQR String copied to clipboard!") }}', 'success');
            else alert('KHQR String copied!');
        });
    } else {
        const ta = document.createElement('textarea');
        ta.value = str;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        if (window.showToast) window.showToast('{{ __("KHQR String copied to clipboard!") }}', 'success');
        else alert('KHQR String copied!');
    }
}
</script>
@endpush
@endsection
