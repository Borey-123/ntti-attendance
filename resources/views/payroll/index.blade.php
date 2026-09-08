@extends('layouts.app')

@section('title', __('Payroll Management'))

@push('styles')
<style>
    .payroll-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .p-summary-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 1.5rem;
        padding: 1.35rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.15rem;
        transition: all 0.3s ease;
    }
    .p-summary-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .p-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 1.15rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }
    .p-stat-data .label {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .p-stat-data .value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
        margin-top: 2px;
    }

    /* Custom table styles */
    .payroll-table th {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
        padding: 1rem 1.25rem;
        background: rgba(255,255,255,0.02);
        border-bottom: 1px solid var(--border);
    }
    .payroll-table td {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        font-size: 0.88rem;
        vertical-align: middle;
    }
    .payroll-table tr:hover td {
        background: rgba(var(--primary-rgb), 0.02);
    }
    .badge-status {
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
</style>
@endpush

@section('content')
<div class="animate-fade-up">

    {{-- ── Page Header ── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom:0.35rem; font-weight:800; display:flex; align-items:center; gap:0.75rem;">
                <span style="width:46px; height:46px; background:linear-gradient(135deg,rgba(16,185,129,0.2),rgba(5,150,105,0.2)); border:1px solid rgba(16,185,129,0.3); border-radius:1rem; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#10b981;">💰</span>
                <span>{{ __('Payroll Management') }}</span>
            </h1>
            <p style="color:var(--text-secondary); margin:0; font-size:0.9rem;">
                {{ __('Auto-calculate & manage teacher monthly salary payrolls based on attendance.') }}
            </p>
        </div>
        <div style="display:flex; gap:0.65rem; flex-wrap:wrap;">
            <a href="{{ route('payroll.settings') }}" class="btn btn-secondary" style="border-radius:0.85rem; font-weight:700; padding:0.65rem 1.1rem;">
                <i class="ph ph-gear"></i> {{ __('Payroll Settings') }}
            </a>
            <a href="{{ route('payroll.export.csv') }}?month={{ $month }}" class="btn btn-secondary" style="border-radius:0.85rem; font-weight:700; padding:0.65rem 1.1rem;">
                <i class="ph ph-download-simple"></i> {{ __('Export CSV') }}
            </a>
            <button onclick="openGenerateModal()" class="btn btn-primary" style="border-radius:0.85rem; font-weight:800; padding:0.65rem 1.25rem; box-shadow:0 8px 20px rgba(var(--primary-rgb),0.25);">
                <i class="ph ph-lightning"></i> {{ __('Generate Payroll') }}
            </button>
        </div>
    </div>

    @php $sym = $settings['currency_symbol'] ?? '$'; @endphp

    {{-- ── Summary Stat Grid ── --}}
    <div class="payroll-summary-grid">
        <div class="p-summary-card">
            <div class="p-stat-icon" style="background:rgba(var(--primary-rgb),0.12); color:var(--primary);">
                <i class="ph ph-users-four"></i>
            </div>
            <div class="p-stat-data">
                <div class="label">{{ __('Total Staff') }}</div>
                <div class="value">{{ $summary['total_teachers'] }}</div>
            </div>
        </div>

        <div class="p-summary-card">
            <div class="p-stat-icon" style="background:rgba(16,185,129,0.12); color:#10b981;">
                <i class="ph ph-currency-dollar"></i>
            </div>
            <div class="p-stat-data">
                <div class="label">{{ __('Total Payout') }}</div>
                <div class="value" style="color:#10b981;">{{ $sym }}{{ number_format($summary['total_payout'], 2) }}</div>
            </div>
        </div>

        <div class="p-summary-card">
            <div class="p-stat-icon" style="background:rgba(245,158,11,0.12); color:#f59e0b;">
                <i class="ph ph-clock-countdown"></i>
            </div>
            <div class="p-stat-data">
                <div class="label">{{ __('Drafts Pending') }}</div>
                <div class="value" style="color:#f59e0b;">{{ $summary['draft_count'] }}</div>
            </div>
        </div>

        <div class="p-summary-card">
            <div class="p-stat-icon" style="background:rgba(99,102,241,0.12); color:#818cf8;">
                <i class="ph ph-check-circle"></i>
            </div>
            <div class="p-stat-data">
                <div class="label">{{ __('Approved') }}</div>
                <div class="value" style="color:#818cf8;">{{ $summary['approved_count'] }}</div>
            </div>
        </div>

        <div class="p-summary-card">
            <div class="p-stat-icon" style="background:rgba(14,165,233,0.12); color:#0ea5e9;">
                <i class="ph ph-money"></i>
            </div>
            <div class="p-stat-data">
                <div class="label">{{ __('Paid Salaries') }}</div>
                <div class="value" style="color:#0ea5e9;">{{ $summary['paid_count'] }}</div>
            </div>
        </div>
    </div>

    {{-- ── Control & Filter Bar ── --}}
    <div class="card" style="padding:1.25rem 1.5rem; margin-bottom:2rem; border-radius:1.5rem; border:1px solid var(--border); display:flex; gap:1.25rem; flex-wrap:wrap; align-items:center;">
        <div style="display:flex; align-items:center; gap:0.6rem;">
            <i class="ph ph-calendar" style="color:var(--primary); font-size:1.2rem;"></i>
            <label style="font-size:0.85rem; font-weight:700; color:var(--text-secondary); white-space:nowrap;">{{ __('Month') }}:</label>
            <input type="month" id="filterMonth" class="form-control" value="{{ $month }}" style="width:165px; border-radius:0.75rem;" onchange="applyFilter()">
        </div>

        <div style="display:flex; align-items:center; gap:0.6rem;">
            <i class="ph ph-buildings" style="color:var(--text-muted); font-size:1.2rem;"></i>
            <label style="font-size:0.85rem; font-weight:700; color:var(--text-secondary);">{{ __('Department') }}:</label>
            <select id="filterDept" class="form-control" style="min-width:160px; border-radius:0.75rem;" onchange="applyFilter()">
                <option value="">{{ __('All Departments') }}</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:0.6rem;">
            <i class="ph ph-funnel" style="color:var(--text-muted); font-size:1.2rem;"></i>
            <label style="font-size:0.85rem; font-weight:700; color:var(--text-secondary);">{{ __('Status') }}:</label>
            <select id="filterStatus" class="form-control" style="min-width:140px; border-radius:0.75rem;" onchange="applyFilter()">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>📝 {{ __('Draft') }}</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✓ {{ __('Approved') }}</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>💰 {{ __('Paid') }}</option>
            </select>
        </div>

        @if($payrolls->where('status','draft')->count() > 0)
        <button onclick="bulkApproveAll()" class="btn btn-secondary" style="margin-left:auto; border-radius:0.85rem; font-weight:700; gap:0.5rem; color:#818cf8; border-color:rgba(99,102,241,0.35); background:rgba(99,102,241,0.08);">
            <i class="ph ph-check-circle" style="font-size:1.15rem;"></i>
            <span>{{ __('Bulk Approve') }} ({{ $payrolls->where('status','draft')->count() }})</span>
        </button>
        @endif
    </div>

    {{-- ── Payrolls Table ── --}}
    <div class="card" style="border-radius:1.5rem; border:1px solid var(--border); overflow:hidden;">
        @if($payrolls->isEmpty())
            <div style="text-align:center; padding:4.5rem 2rem; color:var(--text-muted);">
                <i class="ph ph-receipt" style="font-size:3.5rem; display:block; margin-bottom:1rem; opacity:0.3; color:var(--primary);"></i>
                <h3 style="color:var(--text-secondary); font-weight:800; margin-bottom:0.35rem;">{{ __('No payroll records found for this month') }}</h3>
                <p style="margin:0 0 1.5rem; font-size:0.9rem;">
                    {{ __('Generate payroll records from teacher attendance data with one click.') }}
                </p>
                <button onclick="openGenerateModal()" class="btn btn-primary" style="border-radius:0.85rem; padding:0.75rem 1.5rem; font-weight:800;">
                    <i class="ph ph-lightning"></i> {{ __('Generate Payroll') }}
                </button>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="payroll-table" style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr>
                            <th>{{ __('Teacher') }}</th>
                            <th>{{ __('Base Salary') }}</th>
                            <th>{{ __('Attendance') }}</th>
                            <th>{{ __('Deductions') }}</th>
                            <th>{{ __('Bonus') }}</th>
                            <th>{{ __('Net Salary') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="text-align:right;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $p)
                        <tr>
                            {{-- Teacher Info --}}
                            <td>
                                <div style="display:flex; align-items:center; gap:0.85rem;">
                                    @if($p->teacher->photo)
                                        <img src="{{ to_asset_url($p->teacher->photo) }}" style="width:44px; height:44px; border-radius:50%; object-fit:cover; border:2px solid var(--border);" alt="">
                                    @else
                                        <div style="width:44px; height:44px; border-radius:50%; background:rgba(var(--primary-rgb),0.15); color:var(--primary); font-weight:800; display:flex; align-items:center; justify-content:center; border:2px solid var(--border);">
                                            {{ substr($p->teacher->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:800; color:var(--text-primary);">
                                            {{ $p->teacher->name_kh ?: $p->teacher->name }}
                                        </div>
                                        @if($p->teacher->name_kh)
                                            <div style="font-size:0.8rem; color:var(--text-secondary);">{{ $p->teacher->name }}</div>
                                        @endif
                                        <div style="font-size:0.75rem; color:var(--text-muted); font-family:monospace; margin-top:2px;">
                                            {{ $p->teacher->employee_id }} &bull; {{ $p->teacher->department }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Base Salary --}}
                            <td>
                                <div style="font-weight:800; color:var(--text-primary); font-size:0.95rem;">
                                    {{ $sym }}{{ number_format($p->base_salary, 2) }}
                                </div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">
                                    {{ $p->teacher->position_rank ?: ($p->teacher->position ?: __('Instructor')) }}
                                </div>
                            </td>

                            {{-- Attendance Performance --}}
                            <td>
                                <div style="font-size:0.82rem; line-height:1.5;">
                                    <span style="color:#10b981; font-weight:700;">{{ $p->present_days }}/{{ $p->working_days }}</span> {{ __('days') }}
                                    @if($p->late_minutes > 0)
                                        <div style="color:#f59e0b; font-size:0.75rem;">
                                            <i class="ph ph-clock-countdown"></i> {{ $p->late_minutes }} {{ __('min late') }}
                                        </div>
                                    @endif
                                    @if($p->absent_days > 0)
                                        <div style="color:#ef4444; font-size:0.75rem;">
                                            <i class="ph ph-x-circle"></i> {{ $p->absent_days }} {{ __('days absent') }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Deductions --}}
                            <td>
                                <div style="color:{{ $p->total_deductions > 0 ? '#ef4444' : 'var(--text-muted)' }}; font-weight:700;">
                                    {{ $p->total_deductions > 0 ? '-' . $sym . number_format($p->total_deductions, 2) : $sym . '0.00' }}
                                </div>
                                @if($p->late_deduction > 0 || $p->absent_deduction > 0)
                                    <div style="font-size:0.72rem; color:var(--text-muted);">
                                        Late: -{{ $sym }}{{ number_format($p->late_deduction, 2) }} &bull; Absent: -{{ $sym }}{{ number_format($p->absent_deduction, 2) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Bonus --}}
                            <td>
                                <span style="color:{{ $p->bonus > 0 ? '#10b981' : 'var(--text-muted)' }}; font-weight:700;">
                                    {{ $p->bonus > 0 ? '+' . $sym . number_format($p->bonus, 2) : $sym . '0.00' }}
                                </span>
                            </td>

                            {{-- Net Salary --}}
                            <td>
                                <div style="font-size:1.1rem; font-weight:900; color:#10b981;">
                                    {{ $sym }}{{ number_format($p->net_salary, 2) }}
                                </div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">
                                    Rate: {{ $p->attendance_rate }}%
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td>
                                @if($p->status === 'draft')
                                    <span class="badge-status" style="background:rgba(245,158,11,0.12); color:#f59e0b; border:1px solid rgba(245,158,11,0.3);">
                                        📝 {{ __('Draft') }}
                                    </span>
                                @elseif($p->status === 'approved')
                                    <span class="badge-status" style="background:rgba(99,102,241,0.12); color:#818cf8; border:1px solid rgba(99,102,241,0.3);">
                                        ✓ {{ __('Approved') }}
                                    </span>
                                @else
                                    <span class="badge-status" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3);">
                                        💰 {{ __('Paid') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:0.35rem; align-items:center;">
                                    <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-secondary" style="padding:0.45rem 0.65rem; border-radius:0.6rem; font-size:0.85rem;" title="{{ __('View Details') }}">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                    <a href="{{ route('payroll.pdf', $p->id) }}" target="_blank" class="btn btn-secondary" style="padding:0.45rem 0.65rem; border-radius:0.6rem; font-size:0.85rem; color:#818cf8;" title="{{ __('Download Payslip (PDF)') }}">
                                        <i class="ph ph-file-pdf"></i>
                                    </a>

                                    <button onclick="recalculatePayroll({{ $p->id }}, '{{ addslashes($p->teacher->name) }}')" class="btn btn-secondary" style="padding:0.45rem 0.65rem; border-radius:0.6rem; font-size:0.85rem; color:#f59e0b;" title="{{ __('Recalculate') }}">
                                        <i class="ph ph-arrows-clockwise"></i>
                                    </button>

                                    @if($p->status === 'draft')
                                    <button onclick="approvePayroll({{ $p->id }}, '{{ addslashes($p->teacher->name) }}')" class="btn btn-secondary" style="padding:0.45rem 0.65rem; border-radius:0.6rem; font-size:0.85rem; color:#10b981; border-color:rgba(16,185,129,0.3);" title="{{ __('Approve Payroll') }}">
                                        <i class="ph ph-check-circle"></i>
                                    </button>
                                    @endif

                                    @if($p->status === 'approved')
                                    <button onclick="markPaid({{ $p->id }}, '{{ addslashes($p->teacher->name) }}')" class="btn btn-secondary" style="padding:0.45rem 0.65rem; border-radius:0.6rem; font-size:0.85rem; color:#0ea5e9; border-color:rgba(14,165,233,0.3);" title="{{ __('Mark as Paid') }}">
                                        <i class="ph ph-money"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- ── Generate Payroll Modal ── --}}
<div class="modal-overlay" id="generateModal" style="z-index: 1000001;">
    <div class="modal-content" style="max-width: 480px; border-radius: 1.75rem;">
        <div class="modal-header">
            <h3 style="font-weight: 800; color: var(--text-primary); display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-lightning" style="color:var(--primary);"></i>
                <span>{{ __('Generate Payroll') }}</span>
            </h3>
            <button class="modal-close" onclick="closeModal('generateModal')">&times;</button>
        </div>
        <div style="padding:0.5rem 0;">
            <div class="form-group">
                <label style="font-weight:700;">{{ __('Select Month') }} <span style="color:var(--danger)">*</span></label>
                <input type="month" id="genMonth" class="form-control" value="{{ $month }}" style="font-size:1rem; border-radius:0.75rem;" required>
            </div>

            <div class="form-group">
                <label style="font-weight:700;">{{ __('Scope') }}</label>
                <select id="genScope" class="form-control" style="border-radius:0.75rem;" onchange="toggleTeacherSelect()">
                    <option value="all">{{ __('All Active Teachers') }} ({{ $teachers->count() }})</option>
                    <option value="select">{{ __('Select Specific Teachers') }}</option>
                </select>
            </div>

            <div id="teacherSelectWrapper" style="display:none;" class="form-group">
                <label style="font-weight:700;">{{ __('Select Teachers') }}</label>
                <select id="genTeachers" class="form-control" multiple style="height:140px; border-radius:0.75rem;">
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name_kh ?: $t->name }} ({{ $t->employee_id }})</option>
                    @endforeach
                </select>
                <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Hold Ctrl/Cmd to select multiple</small>
            </div>

            <div style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.25); border-radius:1rem; padding:1rem; margin-top:1rem;">
                <div style="font-size:0.85rem; color:#f59e0b; font-weight:800; margin-bottom:0.25rem; display:flex; align-items:center; gap:0.35rem;">
                    <i class="ph ph-warning"></i> {{ __('Please Confirm') }}
                </div>
                <div style="font-size:0.8rem; color:var(--text-secondary); line-height:1.5;">
                    {{ __('Existing draft payrolls for this month will be updated with the latest attendance records. Approved and paid records will remain locked.') }}
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.85rem; margin-top:1.5rem;">
            <button onclick="closeModal('generateModal')" class="btn btn-secondary" style="border-radius:1rem; font-weight:700;">{{ __('Cancel') }}</button>
            <button onclick="triggerGeneratePayroll()" id="genBtn" class="btn btn-primary" style="border-radius:1rem; font-weight:800;">
                <i class="ph ph-lightning"></i> {{ __('Generate') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function openGenerateModal() { openModal('generateModal'); }

function applyFilter() {
    const m = document.getElementById('filterMonth').value;
    const d = document.getElementById('filterDept').value;
    const s = document.getElementById('filterStatus').value;
    let url = `?month=${m}`;
    if (d) url += `&department=${encodeURIComponent(d)}`;
    if (s) url += `&status=${s}`;
    window.location.href = url;
}

function toggleTeacherSelect() {
    const scope = document.getElementById('genScope').value;
    document.getElementById('teacherSelectWrapper').style.display = scope === 'select' ? 'block' : 'none';
}

async function triggerGeneratePayroll() {
    const month = document.getElementById('genMonth').value;
    closeModal('generateModal');

    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមបង្កើតតារាងប្រាក់បៀវត្សរ៍សម្រាប់ខែ ' + month + ' ពីទិន្នន័យវត្តមានដែរឬទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'primary'
    });
    if (!ok) return;

    const btn = document.getElementById('genBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Generating...';

    const scope = document.getElementById('genScope').value;
    const payload = { month: month };

    if (scope === 'select') {
        const sel = document.getElementById('genTeachers');
        payload.teacher_ids = [...sel.selectedOptions].map(o => o.value);
    }

    try {
        const res = await fetch('{{ route("payroll.generate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.status === 'success' || data.success) {
            if (window.showToast) window.showToast(`Generated payroll for ${data.generated} teachers!`, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            alert(data.message || 'Error generating payroll');
        }
    } catch(err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-lightning"></i> Generate';
    }
}

async function recalculatePayroll(id, teacherName) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមគណនាប្រាក់បៀវត្សរ៍ឡើងវិញសម្រាប់លោកគ្រូ/អ្នកគ្រូ "' + teacherName + '" ដែរឬទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'warning'
    });
    if (!ok) return;

    try {
        const res = await fetch(`/payroll/${id}/recalculate`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success || data.status === 'success') {
            if (window.showToast) window.showToast('{{ __("Payroll recalculated successfully!") }}', 'success');
            setTimeout(() => location.reload(), 400);
        } else {
            alert(data.message || 'Error');
        }
    } catch(err) { alert('Error: ' + err.message); }
}

async function approvePayroll(id, teacherName) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមអនុម័តប្រាក់បៀវត្សរ៍របស់លោកគ្រូ/អ្នកគ្រូ "' + teacherName + '" ដែរឬទេ?',
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

async function markPaid(id, teacherName) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមបញ្ជាក់ថាបានបើកប្រាក់បៀវត្សរ៍ជូន "' + teacherName + '" រួចរាល់ហើយមែនទេ?',
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

async function bulkApproveAll() {
    const draftCount = {{ $payrolls->where('status','draft')->count() }};
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមអនុម័តគ្រប់តារាងប្រាក់បៀវត្សរ៍ដែលព្រាងទុកទាំងអស់ (ចំនួន ' + draftCount + ' នាក់) ដែរឬទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'success'
    });
    if (!ok) return;

    const draftIds = @json($payrolls->where('status','draft')->pluck('id'));
    try {
        const res = await fetch('/payroll/bulk-approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ ids: draftIds }),
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast(`Approved ${data.count} payroll records!`, 'success');
            setTimeout(() => location.reload(), 400);
        }
    } catch(err) { alert('Error: ' + err.message); }
}
</script>
@endpush
@endsection
