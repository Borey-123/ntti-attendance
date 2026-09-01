@extends('layouts.app')

@section('title', __('Leave & Absence Requests'))

@push('styles')
<style>
/* ══════════════════════════════════════════
   STAT CARDS
══════════════════════════════════════════ */
.leave-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.leave-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1.4rem 1.6rem;
    display: flex;
    align-items: center;
    gap: 1.1rem;
    transition: transform .25s, border-color .25s, box-shadow .25s;
    cursor: default;
}
.leave-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 36px rgba(0,0,0,0.14);
    border-color: var(--ls-color, var(--primary));
}
.ls-icon {
    width: 50px; height: 50px; border-radius: 1.1rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
}
.ls-info .ls-val { font-size: 2rem; font-weight: 900; line-height: 1; }
.ls-info .ls-label { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; opacity: .55; margin-top: .25rem; }

/* ══════════════════════════════════════════
   FILTER BAR
══════════════════════════════════════════ */
.filter-bar {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.25rem;
    padding: .75rem 1.25rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.filter-chip {
    padding: .4rem 1rem;
    border-radius: 2rem;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text-secondary);
    font-size: .78rem;
    font-weight: 800;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: .35rem;
}
.filter-chip:hover { border-color: var(--primary); color: var(--primary); }
.filter-chip.active { background: var(--primary); border-color: var(--primary); color: #000; }

/* ══════════════════════════════════════════
   REQUEST CARDS
══════════════════════════════════════════ */
.leave-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1.25rem 1.5rem;
    transition: transform .2s, border-color .2s, box-shadow .2s;
    margin-bottom: .9rem;
    position: relative;
    overflow: hidden;
}
.leave-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    border-radius: 4px 0 0 4px;
}
.leave-card.pending::before   { background: var(--warning); }
.leave-card.approved::before  { background: var(--success); }
.leave-card.rejected::before  { background: var(--danger); }

.leave-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    border-color: rgba(var(--primary-rgb), .3);
}

/* Teacher avatar */
.teacher-avatar {
    width: 48px; height: 48px;
    border-radius: 50%;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; font-weight: 900;
    background: var(--primary); color: #000;
    flex-shrink: 0;
}

/* type pills */
.type-pill {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .75rem;
    border-radius: .5rem;
    font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px;
}
.pill-sick     { background: rgba(248,81,73,.1);  color: var(--danger);  border: 1px solid rgba(248,81,73,.2); }
.pill-mission  { background: rgba(88,166,255,.1); color: var(--info);    border: 1px solid rgba(88,166,255,.2); }
.pill-annual   { background: rgba(63,185,80,.1);  color: var(--success); border: 1px solid rgba(63,185,80,.2); }
.pill-personal { background: rgba(210,153,34,.1); color: var(--warning); border: 1px solid rgba(210,153,34,.2); }

/* status pills */
.status-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .8rem;
    border-radius: 2rem;
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px;
}
.s-pending  { background: rgba(210,153,34,.15); color: var(--warning); border: 1px solid rgba(210,153,34,.3); }
.s-approved { background: rgba(63,185,80,.15);  color: var(--success); border: 1px solid rgba(63,185,80,.3); }
.s-rejected { background: rgba(248,81,73,.15);  color: var(--danger);  border: 1px solid rgba(248,81,73,.3); }

/* action icon buttons */
.btn-approve, .btn-reject {
    width: 40px; height: 40px;
    border-radius: .9rem;
    border: none;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all .2s;
}
.btn-approve { background: rgba(63,185,80,.1); color: var(--success); border: 1px solid rgba(63,185,80,.25); }
.btn-approve:hover { background: var(--success); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(63,185,80,.35); }
.btn-reject  { background: rgba(248,81,73,.1);  color: var(--danger);  border: 1px solid rgba(248,81,73,.25); }
.btn-reject:hover  { background: var(--danger);  color: #fff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(248,81,73,.35); }

.date-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border);
    border-radius: .5rem;
    padding: .3rem .65rem;
    font-size: .78rem; font-weight: 700; font-family: monospace;
}
[data-theme="light"] .date-pill { background: rgba(0,0,0,.04); }

/* Empty state */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
    border: 2px dashed var(--border);
    border-radius: 1.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- ── PAGE HEADER ── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-primary mb-1 d-flex align-items-center gap-2" style="font-size:1.8rem;">
                <i class="ph ph-files"></i>
                <span>{{ __('Leave & Absence Requests') }}</span>
            </h2>
            <p class="text-secondary small fw-bold text-uppercase mb-0" style="letter-spacing:.5px;">
                {{ __('Review and manage teacher leave applications') }}
            </p>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    @php
        $totalReqs    = $leaveRequests->total();
        $pendingCount = \App\Models\LeaveRequest::where('status','pending')->count();
        $approvedCount= \App\Models\LeaveRequest::where('status','approved')->count();
        $rejectedCount= \App\Models\LeaveRequest::where('status','rejected')->count();
        $activeStatus = request('status', 'all');
    @endphp
    <div class="leave-stat-grid">
        <div class="leave-stat-card" style="--ls-color:var(--primary);">
            <div class="ls-icon" style="background:rgba(var(--primary-rgb),.1);color:var(--primary);"><i class="ph ph-files"></i></div>
            <div class="ls-info">
                <div class="ls-val" style="color:var(--primary);">{{ \App\Models\LeaveRequest::count() }}</div>
                <div class="ls-label">{{ __('Total') }}</div>
            </div>
        </div>
        <div class="leave-stat-card" style="--ls-color:var(--warning);">
            <div class="ls-icon" style="background:rgba(var(--warning-rgb),.1);color:var(--warning);"><i class="ph ph-clock-countdown"></i></div>
            <div class="ls-info">
                <div class="ls-val" style="color:var(--warning);">{{ $pendingCount }}</div>
                <div class="ls-label">{{ __('Pending') }}</div>
            </div>
        </div>
        <div class="leave-stat-card" style="--ls-color:var(--success);">
            <div class="ls-icon" style="background:rgba(var(--success-rgb),.1);color:var(--success);"><i class="ph ph-check-circle"></i></div>
            <div class="ls-info">
                <div class="ls-val" style="color:var(--success);">{{ $approvedCount }}</div>
                <div class="ls-label">{{ __('Approved') }}</div>
            </div>
        </div>
        <div class="leave-stat-card" style="--ls-color:var(--danger);">
            <div class="ls-icon" style="background:rgba(var(--danger-rgb),.1);color:var(--danger);"><i class="ph ph-x-circle"></i></div>
            <div class="ls-info">
                <div class="ls-val" style="color:var(--danger);">{{ $rejectedCount }}</div>
                <div class="ls-label">{{ __('Rejected') }}</div>
            </div>
        </div>
    </div>

    {{-- ── FILTER CHIPS ── --}}
    <div class="filter-bar">
        <span class="small fw-bold text-secondary me-1"><i class="ph ph-funnel"></i> {{ __('Filter:') }}</span>
        <a href="{{ route('leave.index') }}" class="filter-chip {{ $activeStatus == 'all' ? 'active' : '' }}">
            <i class="ph ph-list"></i> {{ __('All') }}
        </a>
        <a href="{{ route('leave.index', ['status' => 'pending']) }}" class="filter-chip {{ $activeStatus == 'pending' ? 'active' : '' }}">
            <i class="ph ph-clock"></i> {{ __('Pending') }}
            @if($pendingCount > 0)<span style="background:var(--warning);color:#000;border-radius:2rem;padding:0 .45rem;font-size:.65rem;margin-left:.25rem;">{{ $pendingCount }}</span>@endif
        </a>
        <a href="{{ route('leave.index', ['status' => 'approved']) }}" class="filter-chip {{ $activeStatus == 'approved' ? 'active' : '' }}">
            <i class="ph ph-check"></i> {{ __('Approved') }}
        </a>
        <a href="{{ route('leave.index', ['status' => 'rejected']) }}" class="filter-chip {{ $activeStatus == 'rejected' ? 'active' : '' }}">
            <i class="ph ph-x"></i> {{ __('Rejected') }}
        </a>
    </div>

    {{-- ── REQUEST CARDS ── --}}
    @forelse($leaveRequests as $req)
    @php
        $typeMap = ['sick'=>'pill-sick','mission'=>'pill-mission','annual'=>'pill-annual','personal'=>'pill-personal'];
        $typeNames = ['sick'=>__('Sick Leave'),'mission'=>__('Official Mission'),'annual'=>__('Annual Leave'),'personal'=>__('Personal Leave')];
        $tClass = $typeMap[$req->leave_type] ?? 'pill-personal';
        $tName  = $typeNames[$req->leave_type] ?? ucfirst($req->leave_type);
        $sClass = ['pending'=>'s-pending','approved'=>'s-approved','rejected'=>'s-rejected'][$req->status] ?? 's-pending';
        $sIcon  = ['pending'=>'ph-clock','approved'=>'ph-check-circle','rejected'=>'ph-x-circle'][$req->status] ?? 'ph-clock';
        $teacherName = app()->getLocale()=='km' ? ($req->teacher->name_kh ?: $req->teacher->name) : $req->teacher->name;

        $start = \Carbon\Carbon::parse($req->start_date);
        $end   = \Carbon\Carbon::parse($req->end_date);
        $days  = $start->diffInDays($end) + 1;
    @endphp
    <div class="leave-card {{ $req->status }}">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            {{-- Avatar --}}
            <div class="teacher-avatar">
                @if($req->teacher && $req->teacher->photo)
                    <img src="{{ to_asset_url($req->teacher->photo) }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    {{ strtoupper(substr($req->teacher->name ?? 'T', 0, 1)) }}
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="fw-bold" style="font-size:1rem;">{{ $teacherName }}</span>
                    <span class="text-secondary small fw-bold">{{ $req->teacher->department ?? '' }}</span>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <span class="type-pill {{ $tClass }}">{{ $tName }}</span>
                    <span class="status-pill {{ $sClass }}"><i class="ph {{ $sIcon }}"></i> {{ ucfirst($req->status) }}</span>
                    <span class="small fw-bold text-secondary">
                        <i class="ph ph-timer"></i> {{ $days }} {{ $days == 1 ? __('day') : __('days') }}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <span class="date-pill"><i class="ph ph-calendar-blank" style="color:var(--primary);"></i> {{ $req->start_date }}</span>
                    <i class="ph ph-arrow-right text-secondary"></i>
                    <span class="date-pill"><i class="ph ph-calendar-check" style="color:var(--primary);"></i> {{ $req->end_date }}</span>
                </div>
                @if($req->reason)
                <div style="font-size:.82rem;color:var(--text-secondary);font-weight:600;background:rgba(var(--primary-rgb),.04);border:1px solid var(--border);border-radius:.75rem;padding:.5rem .85rem;max-width:600px;">
                    <i class="ph ph-note text-primary" style="font-size:.9rem;"></i>
                    {{ Str::limit($req->reason, 120) }}
                </div>
                @endif
                @if($req->admin_note)
                <div style="font-size:.78rem;color:var(--text-secondary);margin-top:.5rem;font-style:italic;">
                    <i class="ph ph-shield-check text-primary"></i> <strong>{{ __('Admin note:') }}</strong> {{ $req->admin_note }}
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                <span class="text-secondary small fw-bold" style="font-size:.7rem;opacity:.6;">
                    {{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}
                </span>
                @if($req->status === 'pending')
                <div class="d-flex gap-2">
                    <button class="btn-approve" onclick="updateLeaveStatus({{ $req->id }}, 'approved')" title="{{ __('Approve') }}">
                        <i class="ph ph-check-bold"></i>
                    </button>
                    <button class="btn-reject" onclick="updateLeaveStatus({{ $req->id }}, 'rejected')" title="{{ __('Reject') }}">
                        <i class="ph ph-x-bold"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="ph ph-folder-open" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
        <div class="fw-bold" style="font-size:1.1rem;margin-bottom:.5rem;">{{ __('No leave requests found') }}</div>
        <div class="small">{{ __('Requests submitted by teachers from the portal will appear here.') }}</div>
    </div>
    @endforelse

    {{-- ── PAGINATION ── --}}
    @if($leaveRequests->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $leaveRequests->appends(request()->query())->links() }}
    </div>
    @endif

</div>

<script>
async function updateLeaveStatus(id, status) {
    const label = status === 'approved' ? '{{ __("Approve") }}' : '{{ __("Reject") }}';
    if (!confirm(`${label} this leave request?`)) return;
    try {
        await window.fetchApi(`{{ url('/leave-requests') }}/${id}/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status })
        });
        window.location.reload();
    } catch(e) {
        alert(e.message);
    }
}
</script>
@endsection
