@extends('layouts.app')

@section('title', __('Payroll Management'))

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-size:1.75rem; font-weight:900; margin:0; display:flex; align-items:center; gap:0.75rem;">
            <span style="width:42px; height:42px; background:linear-gradient(135deg,#10b981,#059669); border-radius:0.75rem; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">💰</span>
            {{ __('Payroll Management') }}
        </h1>
        <p style="color:var(--text-secondary); margin:0.25rem 0 0; font-size:0.9rem;">
            {{ __('Auto-generate & manage teacher salary payrolls') }}
        </p>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <a href="{{ route('payroll.settings') }}" class="btn btn-secondary" style="gap:0.5rem;">
            <i class="ph ph-gear"></i> {{ __('Settings') }}
        </a>
        <a href="{{ route('payroll.export.csv') }}?month={{ $month }}" class="btn btn-secondary" style="gap:0.5rem;">
            <i class="ph ph-download-simple"></i> CSV
        </a>
        <button onclick="openGenerateModal()" class="btn btn-primary" style="gap:0.5rem;">
            <i class="ph ph-lightning"></i> {{ __('Generate Payroll') }}
        </button>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:2rem;">
    @php $sym = $settings['currency_symbol'] ?? '$'; @endphp
    <div class="stat-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.6rem; font-weight:900; color:var(--primary);">{{ $summary['total_teachers'] }}</div>
        <div style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Teachers') }}</div>
    </div>
    <div class="stat-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.6rem; font-weight:900; color:#10b981;">{{ $sym }}{{ number_format($summary['total_payout'],2) }}</div>
        <div style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Total Payout') }}</div>
    </div>
    <div class="stat-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.6rem; font-weight:900; color:#6366f1;">{{ $sym }}{{ number_format($summary['avg_salary'],2) }}</div>
        <div style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Avg Salary') }}</div>
    </div>
    <div class="stat-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.6rem; font-weight:900; color:#f59e0b;">{{ $summary['draft_count'] }}</div>
        <div style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Draft') }}</div>
    </div>
    <div class="stat-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.6rem; font-weight:900; color:#a5b4fc;">{{ $summary['approved_count'] }}</div>
        <div style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Approved') }}</div>
    </div>
    <div class="stat-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.6rem; font-weight:900; color:#00d4a0;">{{ $summary['paid_count'] }}</div>
        <div style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">{{ __('Paid') }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="padding:1.25rem 1.5rem; margin-bottom:1.5rem; border-radius:1rem; display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
    <div style="display:flex; align-items:center; gap:0.5rem;">
        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); white-space:nowrap;">{{ __('Month') }}:</label>
        <input type="month" id="filterMonth" class="form-control" value="{{ $month }}" style="width:160px;" onchange="applyFilter()">
    </div>
    <div style="display:flex; align-items:center; gap:0.5rem;">
        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary);">{{ __('Department') }}:</label>
        <select id="filterDept" class="form-control" style="min-width:150px;" onchange="applyFilter()">
            <option value="">{{ __('All Departments') }}</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    </div>
    <div style="display:flex; align-items:center; gap:0.5rem;">
        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary);">{{ __('Status') }}:</label>
        <select id="filterStatus" class="form-control" style="min-width:130px;" onchange="applyFilter()">
            <option value="">{{ __('All Status') }}</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>📝 Draft</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✓ Approved</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>💰 Paid</option>
        </select>
    </div>
    @if($payrolls->where('status','draft')->count() > 0)
    <button onclick="bulkApproveAll()" class="btn btn-secondary" style="margin-left:auto; gap:0.5rem; color:#a5b4fc; border-color:rgba(99,102,241,0.3);">
        <i class="ph ph-check-circle"></i> {{ __('Approve All Drafts') }} ({{ $payrolls->where('status','draft')->count() }})
    </button>
    @endif
</div>

{{-- Payroll Table --}}
<div class="card" style="border-radius:1.25rem; overflow:hidden;">
    @if($payrolls->isEmpty())
        <div style="text-align:center; padding:4rem 2rem; color:var(--text-muted);">
            <i class="ph ph-money" style="font-size:3rem; display:block; margin-bottom:1rem; opacity:0.3;"></i>
            <p style="margin:0 0 1rem;">{{ __('No payroll data for this month.') }}</p>
            <button onclick="openGenerateModal()" class="btn btn-primary">
                <i class="ph ph-lightning"></i> {{ __('Generate Now') }}
            </button>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.03); border-bottom:1px solid var(--border);">
                        <th style="padding:1rem 1.25rem; text-align:left; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">{{ __('Teacher') }}</th>
                        <th style="padding:1rem; text-align:center; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('Days') }}</th>
                        <th style="padding:1rem; text-align:center; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('Late') }}</th>
                        <th style="padding:1rem; text-align:right; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('Base') }}</th>
                        <th style="padding:1rem; text-align:right; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; color:#ef4444;">{{ __('Deductions') }}</th>
                        <th style="padding:1rem; text-align:right; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; color:#10b981;">{{ __('Net Salary') }}</th>
                        <th style="padding:1rem; text-align:center; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('Status') }}</th>
                        <th style="padding:1rem; text-align:center; font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrolls as $p)
                    <tr style="border-bottom:1px solid var(--border); transition:all 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:1rem 1.25rem;">
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,var(--primary),#00b894); color:#000; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">
                                    {{ strtoupper(substr($p->teacher->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700; font-size:0.9rem;">{{ $p->teacher->name ?? 'N/A' }}</div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $p->teacher->department ?? '' }} &bull; {{ $p->teacher->employee_id ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:1rem; text-align:center;">
                            <div style="font-weight:700; font-size:0.85rem;">{{ $p->present_days }}/{{ $p->working_days }}</div>
                            <div style="font-size:0.7rem; color:{{ $p->attendance_rate >= 90 ? '#10b981' : ($p->attendance_rate >= 70 ? '#f59e0b' : '#ef4444') }}; font-weight:700;">{{ $p->attendance_rate }}%</div>
                        </td>
                        <td style="padding:1rem; text-align:center;">
                            @if($p->late_count > 0)
                                <span style="color:#f59e0b; font-weight:700; font-size:0.85rem;">{{ $p->late_count }}x</span>
                                <div style="font-size:0.7rem; color:var(--text-muted);">{{ $p->late_minutes_total }} min</div>
                            @else
                                <span style="color:#10b981; font-size:0.8rem;">✓ On time</span>
                            @endif
                        </td>
                        <td style="padding:1rem; text-align:right; font-size:0.85rem; color:var(--text-secondary);">{{ $sym }}{{ number_format($p->base_salary,2) }}</td>
                        <td style="padding:1rem; text-align:right;">
                            @php $totalDeduct = $p->late_deduction + $p->absent_deduction; @endphp
                            @if($totalDeduct > 0)
                                <span style="color:#ef4444; font-weight:700; font-size:0.85rem;">-{{ $sym }}{{ number_format($totalDeduct,2) }}</span>
                            @else
                                <span style="color:#10b981; font-size:0.82rem;">None</span>
                            @endif
                        </td>
                        <td style="padding:1rem; text-align:right;">
                            <span style="font-weight:900; font-size:1rem; color:#10b981;">{{ $sym }}{{ number_format($p->net_salary,2) }}</span>
                        </td>
                        <td style="padding:1rem; text-align:center;">
                            <span style="background:{{ $p->status_color }}22; color:{{ $p->status_color }}; border:1px solid {{ $p->status_color }}44; padding:0.25rem 0.7rem; border-radius:99px; font-size:0.72rem; font-weight:800; text-transform:uppercase;">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td style="padding:1rem; text-align:center;">
                            <div style="display:flex; justify-content:center; gap:0.35rem;">
                                <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-secondary" style="padding:0.35rem 0.6rem; font-size:0.78rem;" title="{{ __('View Detail') }}">
                                    <i class="ph ph-eye"></i>
                                </a>
                                <a href="{{ route('payroll.pdf', $p->id) }}" class="btn btn-secondary" style="padding:0.35rem 0.6rem; font-size:0.78rem;" title="{{ __('Download PDF') }}" target="_blank">
                                    <i class="ph ph-file-pdf"></i>
                                </a>
                                @if($p->status === 'draft')
                                <button onclick="approvePayroll({{ $p->id }})" class="btn btn-secondary" style="padding:0.35rem 0.6rem; font-size:0.78rem; color:#a5b4fc; border-color:rgba(99,102,241,0.3);" title="{{ __('Approve') }}">
                                    <i class="ph ph-check"></i>
                                </button>
                                @elseif($p->status === 'approved')
                                <button onclick="markPaid({{ $p->id }})" class="btn btn-secondary" style="padding:0.35rem 0.6rem; font-size:0.78rem; color:#10b981; border-color:rgba(16,185,129,0.3);" title="{{ __('Mark as Paid') }}">
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

{{-- Generate Payroll Modal --}}
<div id="generateModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:480px; border-radius:1.25rem; padding:2rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-lightning" style="color:#f59e0b;"></i> {{ __('Generate Payroll') }}
            </h3>
            <button onclick="closeModal('generateModal')" style="background:none; border:none; color:var(--text-secondary); font-size:1.5rem; cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>
        <div style="display:grid; gap:1rem;">
            <div>
                <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Month') }} *</label>
                <input type="month" id="genMonth" class="form-control" value="{{ $month }}">
            </div>
            <div>
                <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Scope') }}</label>
                <select id="genScope" class="form-control" onchange="toggleTeacherSelect()">
                    <option value="all">{{ __('All Active Teachers') }}</option>
                    <option value="select">{{ __('Select Specific Teachers') }}</option>
                </select>
            </div>
            <div id="teacherSelectWrapper" style="display:none;">
                <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Select Teachers') }}</label>
                <select id="genTeachers" class="form-control" multiple style="height:150px;">
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->employee_id }})</option>
                    @endforeach
                </select>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Ctrl+click to select multiple</div>
            </div>
            <div style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); border-radius:0.75rem; padding:0.875rem;">
                <div style="font-size:0.82rem; color:#fbbf24; font-weight:700; margin-bottom:0.25rem;">⚠️ {{ __('Note') }}</div>
                <div style="font-size:0.8rem; color:var(--text-secondary);">{{ __('Existing payrolls for this month will be recalculated. Approved/Paid records will not be changed.') }}</div>
            </div>
        </div>
        <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
            <button onclick="generatePayroll()" id="genBtn" class="btn btn-primary" style="flex:1;">
                <i class="ph ph-lightning"></i> {{ __('Generate') }}
            </button>
            <button onclick="closeModal('generateModal')" class="btn btn-secondary" style="flex:1;">{{ __('Cancel') }}</button>
        </div>
        <div id="genResult" style="margin-top:1rem; display:none;"></div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
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

async function generatePayroll() {
    const btn = document.getElementById('genBtn');
    const result = document.getElementById('genResult');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin" style="display:inline-block">⟳</span> Generating...';

    const scope = document.getElementById('genScope').value;
    const payload = { month: document.getElementById('genMonth').value };

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
        result.style.display = 'block';
        result.innerHTML = `<div style="background:rgba(0,212,160,0.1); border:1px solid rgba(0,212,160,0.3); border-radius:0.75rem; padding:1rem; color:#00d4a0; font-weight:700;">
            ✅ Generated payroll for ${data.generated} teachers.
            ${data.errors?.length ? '<br><span style="color:#ef4444;">⚠️ ' + data.errors.join(', ') + '</span>' : ''}
        </div>`;
        setTimeout(() => { closeModal('generateModal'); location.reload(); }, 1800);
    } catch(err) {
        result.style.display = 'block';
        result.innerHTML = `<div style="color:var(--danger);">Error: ${err.message}</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-lightning"></i> Generate';
    }
}

async function approvePayroll(id) {
    if (!confirm('{{ __("Approve this payroll?") }}')) return;
    const res = await fetch(`/payroll/${id}/approve`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message || 'Error');
}

async function markPaid(id) {
    if (!confirm('{{ __("Mark this payroll as PAID?") }}')) return;
    const res = await fetch(`/payroll/${id}/paid`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message || 'Error');
}

async function bulkApproveAll() {
    if (!confirm('{{ __("Approve all draft payrolls?") }}')) return;
    const draftIds = @json($payrolls->where('status','draft')->pluck('id'));
    const res = await fetch('/payroll/bulk-approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ ids: draftIds }),
    });
    const data = await res.json();
    if (data.success) { alert(`Approved ${data.count} payrolls.`); location.reload(); }
}
</script>
@endpush
@endsection
