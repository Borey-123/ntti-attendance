@extends('layouts.app')

@section('title', __('Leave & Absence Requests'))

@push('styles')
<style>
    .btn.btn-edit-premium {
        background: rgba(var(--primary-rgb), 0.1);
        border: 2px solid var(--primary);
        color: var(--primary);
        box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);
        font-weight: 800;
    }
    .btn.btn-edit-premium:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.4);
    }
    .pill-tabs {
        display: inline-flex;
        background: rgba(255,255,255,0.03);
        padding: 0.4rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border);
        gap: 0.25rem;
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
        display: inline-flex; align-items: center; gap: 0.5rem;
        text-decoration: none;
    }
    .pill-btn:hover { color: var(--text-primary); }
    .pill-btn.active {
        background: var(--primary);
        color: #000;
        box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.2);
    }
    .leave-actions-bar {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
        background: rgba(255, 255, 255, 0.03);
        padding: 0.25rem 0.35rem;
        border-radius: 0.85rem;
        border: 1px solid var(--border);
    }
    .btn-leave-action {
        height: 32px;
        padding: 0 0.65rem;
        border-radius: 0.6rem;
        font-size: 0.76rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
    }
    .btn-leave-action.btn-approve {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.3);
    }
    .btn-leave-action.btn-approve:hover {
        background: #10b981;
        color: #fff;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        transform: translateY(-1px);
    }
    .btn-leave-action.btn-reject {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.3);
    }
    .btn-leave-action.btn-reject:hover {
        background: #ef4444;
        color: #fff;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        transform: translateY(-1px);
    }
    .btn-leave-action.btn-sub {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
        border-color: rgba(59, 130, 246, 0.3);
    }
    .btn-leave-action.btn-sub:hover {
        background: #3b82f6;
        color: #fff;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        transform: translateY(-1px);
    }
    .btn-leave-action.btn-sub.has-sub {
        background: rgba(139, 92, 246, 0.14);
        color: #a855f7;
        border-color: rgba(139, 92, 246, 0.35);
    }
    .btn-leave-action.btn-sub.has-sub:hover {
        background: #8b5cf6;
        color: #fff;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.35);
    }
</style>
@endpush

@section('content')
<div class="animate-fade-up">

    {{-- ── Header ── --}}
    <div class="d-flex justify-between align-center" style="margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">{{ __('Leave & Absence Requests') }}</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Review, approve, and track teacher leave applications.') }}</p>
        </div>
    </div>

    {{-- ── Summary Metrics Grid ── --}}
    @php
        $pendingCount  = \App\Models\LeaveRequest::where('status','pending')->count();
        $approvedCount = \App\Models\LeaveRequest::where('status','approved')->count();
        $rejectedCount = \App\Models\LeaveRequest::where('status','rejected')->count();
        $totalCount    = \App\Models\LeaveRequest::count();
        $activeStatus  = request('status', 'all');
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-files"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Total Applications') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $totalCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-clock"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Pending Review') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $pendingCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-check-circle"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Approved') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $approvedCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-x-circle"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Rejected') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $rejectedCount }}</div>
            </div>
        </div>
    </div>

    {{-- ── Sliding Filter Tabs ── --}}
    <div class="pill-tabs">
        <a href="{{ route('leave-requests.index') }}" class="pill-btn {{ $activeStatus == 'all' ? 'active' : '' }}">
            <i class="ph ph-list"></i> {{ __('All Requests') }}
        </a>
        <a href="{{ route('leave-requests.index', ['status' => 'pending']) }}" class="pill-btn {{ $activeStatus == 'pending' ? 'active' : '' }}">
            <i class="ph ph-clock"></i> {{ __('Pending') }}
            @if($pendingCount > 0)<span style="background:#f59e0b; color:#000; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 900;">{{ $pendingCount }}</span>@endif
        </a>
        <a href="{{ route('leave-requests.index', ['status' => 'approved']) }}" class="pill-btn {{ $activeStatus == 'approved' ? 'active' : '' }}">
            <i class="ph ph-check"></i> {{ __('Approved') }}
        </a>
        <a href="{{ route('leave-requests.index', ['status' => 'rejected']) }}" class="pill-btn {{ $activeStatus == 'rejected' ? 'active' : '' }}">
            <i class="ph ph-x"></i> {{ __('Rejected') }}
        </a>
    </div>

    {{-- ── Requests Table Container ── --}}
    <div class="card" style="border-radius: 2rem; overflow: hidden;">
        <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                <i class="ph ph-files"></i>
                {{ __('Leave Request Registry') }}
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary);">({{ $leaveRequests->total() }})</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">#</th>
                        <th>{{ __('Teacher') }}</th>
                        <th>{{ __('Leave Type') }}</th>
                        <th>{{ __('Period / Duration') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th style="text-align: center; width: 120px;">{{ __('Status') }}</th>
                        <th style="width: 220px; text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $index => $req)
                    @php
                        $teacherName = app()->getLocale() == 'km' ? ($req->teacher->name_kh ?: $req->teacher->name) : $req->teacher->name;
                        $start = \Carbon\Carbon::parse($req->start_date);
                        $end   = \Carbon\Carbon::parse($req->end_date);
                        $days  = $start->diffInDays($end) + 1;

                        $typeNames = [
                            'sick'     => __('Sick Leave'),
                            'mission'  => __('Official Mission'),
                            'annual'   => __('Annual Leave'),
                            'personal' => __('Personal Leave')
                        ];
                        $typeLabel = $typeNames[$req->leave_type] ?? ucfirst($req->leave_type);
                    @endphp
                    <tr>
                        <td style="text-align: center;">
                            <div style="width: 32px; height: 32px; border-radius: 0.6rem; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                {{ $index + 1 + ($leaveRequests->currentPage() - 1) * $leaveRequests->perPage() }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #000; font-weight: 800; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                    @if($req->teacher && $req->teacher->photo)
                                        <img src="{{ to_asset_url($req->teacher->photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        {{ strtoupper(substr($req->teacher->name ?? 'T', 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--text-primary); font-size: 0.95rem; line-height: 1.2;">{{ $teacherName }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">{{ $req->teacher->department ?? '—' }}</div>
                                    @if($req->substituteTeacher)
                                        <div style="margin-top: 5px; display: inline-flex; align-items: center; gap: 5px; font-size: 0.72rem; background: rgba(59, 130, 246, 0.12); color: #3b82f6; padding: 2px 8px; border-radius: 6px; font-weight: 700; border: 1px solid rgba(59, 130, 246, 0.25);">
                                            <i class="ph ph-user-switch"></i>
                                            <span>{{ __('Sub:') }} {{ app()->getLocale() == 'km' ? ($req->substituteTeacher->name_kh ?: $req->substituteTeacher->name) : $req->substituteTeacher->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info" style="font-size: 0.8rem; font-weight: 800;">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 0.88rem; color: var(--text-primary);">
                                {{ $req->start_date }} <i class="ph ph-arrow-right" style="font-size: 0.75rem; color: var(--text-secondary);"></i> {{ $req->end_date }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--primary); font-weight: 800; margin-top: 2px;">
                                <i class="ph ph-timer"></i> {{ $days }} {{ $days == 1 ? __('day') : __('days') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $req->reason }}">
                                {{ $req->reason }}
                            </div>
                            @if($req->admin_note)
                            <div style="font-size: 0.75rem; color: var(--primary); font-style: italic; margin-top: 2px;">
                                {{ __('Note:') }} {{ $req->admin_note }}
                            </div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($req->status === 'approved')
                                <span class="badge badge-success"><i class="ph ph-check-circle me-1"></i>{{ __('Approved') }}</span>
                            @elseif($req->status === 'rejected')
                                <span class="badge badge-danger"><i class="ph ph-x-circle me-1"></i>{{ __('Rejected') }}</span>
                            @else
                                <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);"><i class="ph ph-clock me-1"></i>{{ __('Pending') }}</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="leave-actions-bar">
                                @if($req->status === 'pending')
                                    <button class="btn-leave-action btn-approve"
                                        onclick="confirmLeaveAction({{ $req->id }}, 'approved', '{{ addslashes($teacherName) }}')"
                                        title="{{ __('Approve Leave Request') }}">
                                        <i class="ph ph-check-bold"></i>
                                        <span>{{ __('Approve') }}</span>
                                    </button>

                                    <button class="btn-leave-action btn-reject"
                                        onclick="confirmLeaveAction({{ $req->id }}, 'rejected', '{{ addslashes($teacherName) }}')"
                                        title="{{ __('Reject Leave Request') }}">
                                        <i class="ph ph-x-bold"></i>
                                        <span>{{ __('Reject') }}</span>
                                    </button>
                                @endif

                                @if($req->status !== 'rejected')
                                    <button class="btn-leave-action btn-sub {{ $req->substitute_teacher_id ? 'has-sub' : '' }}"
                                        onclick="openSubstituteModal({{ $req->id }}, '{{ addslashes($teacherName) }}', '{{ $req->start_date }}', '{{ $req->end_date }}')"
                                        title="{{ $req->substitute_teacher_id ? __('Change Substitute Teacher') : __('Assign Substitute Teacher') }}">
                                        <i class="ph ph-user-switch"></i>
                                        <span>{{ $req->substitute_teacher_id ? __('Sub') : __('Sub') }}</span>
                                    </button>
                                @endif

                                @if($req->status === 'rejected')
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic; padding: 0 0.5rem;">{{ __('Completed') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <i class="ph ph-folder-open" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            <div style="font-weight: 800; font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 0.3rem;">{{ __('No leave requests found.') }}</div>
                            <div style="font-size: 0.85rem;">{{ __('Submitted leave applications will appear here.') }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($leaveRequests->hasPages())
    <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
        {{ $leaveRequests->appends(request()->query())->links() }}
    </div>
    @endif

</div>

{{-- ── Custom Confirm Modal ── --}}
<div id="leaveConfirmModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:2rem; padding:2.5rem; max-width:440px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,0.4); text-align:center;">
        <div id="leaveConfirmIcon" style="width:70px;height:70px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.25rem;"></div>
        <h3 id="leaveConfirmTitle" style="font-weight:800;margin-bottom:0.5rem;color:var(--text-primary);"></h3>
        <p id="leaveConfirmMessage" style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:1.75rem;"></p>
        <div style="display:flex;gap:0.75rem;justify-content:center;">
            <button onclick="closeLeaveConfirm()" class="btn btn-secondary" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:700;">{{ __('Cancel') }}</button>
            <button id="leaveConfirmBtn" class="btn" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:800;"></button>
        </div>
    </div>
</div>

{{-- ── Smart Substitute Teacher Recommender Modal ── --}}
<div id="substituteModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:1.75rem; max-width:680px; width:100%; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 25px 70px rgba(0,0,0,0.5); overflow:hidden; animation:modalPop 0.25s cubic-bezier(0.16,1,0.3,1);">
        {{-- Header --}}
        <div style="padding:1.25rem 1.75rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.02);">
            <div style="display:flex; align-items:center; gap:0.85rem;">
                <div style="width:42px; height:42px; border-radius:12px; background:rgba(59,130,246,0.15); color:#3b82f6; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0;">
                    <i class="ph ph-user-switch"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:var(--text-primary);">{{ __('Smart Substitute Matcher') }}</h3>
                    <p id="subModalSubtitle" style="margin:2px 0 0; font-size:0.8rem; color:var(--text-secondary);"></p>
                </div>
            </div>
            <button onclick="closeSubstituteModal()" type="button" style="background:none; border:none; color:var(--text-secondary); font-size:1.35rem; cursor:pointer; padding:0.4rem; border-radius:0.5rem; transition:color 0.2s;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="ph ph-x"></i>
            </button>
        </div>

        {{-- Body --}}
        <div id="subModalBody" style="overflow-y:auto; padding:1.5rem 1.75rem; flex:1;">
            {{-- Loading State --}}
            <div id="subModalLoading" style="text-align:center; padding:3rem 1rem;">
                <div style="display:inline-block; width:36px; height:36px; border:3px solid rgba(59,130,246,0.2); border-top-color:#3b82f6; border-radius:50%; animation:spin 0.8s linear infinite; margin-bottom:1rem;"></div>
                <div style="font-weight:700; color:var(--text-primary); font-size:0.95rem;">{{ __('Analyzing Timetables...') }}</div>
                <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:0.35rem;">{{ __('Checking department schedules for conflict-free availability') }}</div>
            </div>

            {{-- Content Container --}}
            <div id="subModalContent" style="display:none;"></div>
        </div>

        {{-- Footer --}}
        <div style="padding:1rem 1.75rem; border-top:1px solid var(--border); background:rgba(255,255,255,0.015); display:flex; justify-content:flex-end; gap:0.75rem;">
            <button onclick="closeSubstituteModal()" class="btn btn-secondary" style="border-radius:0.85rem; padding:0.55rem 1.35rem; font-weight:700; font-size:0.88rem;">{{ __('Close') }}</button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.cand-card {
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.02);
    border-radius: 1.15rem;
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    transition: all 0.2s ease;
}
.cand-card:hover {
    border-color: rgba(59,130,246,0.4);
    background: rgba(59,130,246,0.03);
}
.cand-card.free-cand {
    border-color: rgba(16,185,129,0.3);
}
.cand-card.free-cand:hover {
    border-color: #10b981;
    background: rgba(16,185,129,0.04);
}
.cand-card.assigned-cand {
    border-color: #3b82f6;
    background: rgba(59,130,246,0.08);
}
</style>

<script>
function closeLeaveConfirm() {
    document.getElementById('leaveConfirmModal').style.display = 'none';
}
document.getElementById('leaveConfirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeLeaveConfirm();
});

function confirmLeaveAction(id, status, teacherName) {
    const isApprove = status === 'approved';
    const modal = document.getElementById('leaveConfirmModal');
    const iconEl = document.getElementById('leaveConfirmIcon');
    iconEl.innerHTML = isApprove ? '<i class="ph ph-check-circle"></i>' : '<i class="ph ph-x-circle"></i>';
    iconEl.style.background = isApprove ? '#10b981' : '#ef4444';
    iconEl.style.color = '#fff';
    document.getElementById('leaveConfirmTitle').textContent = isApprove
        ? '{{ __("Approve Leave Request?") }}'
        : '{{ __("Reject Leave Request?") }}';
    document.getElementById('leaveConfirmMessage').textContent = isApprove
        ? `{{ __("Approve the leave request for") }} ${teacherName}? {{ __("This action will be recorded in the system.") }}`
        : `{{ __("Reject the leave request for") }} ${teacherName}? {{ __("The teacher will be notified.") }}`;
    const btn = document.getElementById('leaveConfirmBtn');
    btn.textContent = isApprove ? '{{ __("Yes, Approve") }}' : '{{ __("Yes, Reject") }}';
    btn.className = isApprove ? 'btn btn-success' : 'btn btn-danger';
    btn.style.cssText = 'border-radius:1rem;padding:0.65rem 1.75rem;font-weight:800;';
    btn.onclick = () => { closeLeaveConfirm(); doLeaveStatusUpdate(id, status); };
    modal.style.display = 'flex';
}

async function doLeaveStatusUpdate(id, status) {
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

// ── Smart Substitute Matcher Logic ──
let currentLeaveIdForSub = null;

function closeSubstituteModal() {
    document.getElementById('substituteModal').style.display = 'none';
    currentLeaveIdForSub = null;
}

document.getElementById('substituteModal').addEventListener('click', function(e) {
    if (e.target === this) closeSubstituteModal();
});

async function openSubstituteModal(leaveId, teacherName, startDate, endDate) {
    currentLeaveIdForSub = leaveId;
    const modal = document.getElementById('substituteModal');
    const subtitle = document.getElementById('subModalSubtitle');
    const loading = document.getElementById('subModalLoading');
    const content = document.getElementById('subModalContent');

    subtitle.textContent = `${teacherName} • ${startDate} → ${endDate}`;
    loading.style.display = 'block';
    content.style.display = 'none';
    content.innerHTML = '';
    modal.style.display = 'flex';

    try {
        const res = await window.fetchApi(`{{ url('/leave-requests') }}/${leaveId}/substitutes`);
        renderSubstituteModal(res, leaveId);
    } catch(err) {
        loading.style.display = 'none';
        content.style.display = 'block';
        content.innerHTML = `
            <div style="text-align:center; padding:2rem 1rem; color:#ef4444;">
                <i class="ph ph-warning-circle" style="font-size:2.5rem; margin-bottom:0.5rem; display:block;"></i>
                <div style="font-weight:700;">{{ __('Failed to load suggestions') }}</div>
                <div style="font-size:0.85rem; margin-top:0.35rem;">${err.message || 'Server error'}</div>
            </div>
        `;
    }
}

function getDayName(dow) {
    const days = ['', '{{ __("Monday") }}', '{{ __("Tuesday") }}', '{{ __("Wednesday") }}', '{{ __("Thursday") }}', '{{ __("Friday") }}', '{{ __("Saturday") }}', '{{ __("Sunday") }}'];
    return days[dow] || `Day ${dow}`;
}

function renderSubstituteModal(data, leaveId) {
    const loading = document.getElementById('subModalLoading');
    const content = document.getElementById('subModalContent');
    loading.style.display = 'none';
    content.style.display = 'block';

    const slots = data.slots_to_cover || [];
    const candidates = data.candidates || [];

    let html = '';

    // 1. Slots to cover
    html += `
        <div style="margin-bottom:1.5rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:1.15rem; padding:1rem 1.25rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                <span style="font-size:0.8rem; font-weight:800; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="ph ph-calendar-check" style="color:var(--primary); margin-right:4px;"></i>
                    {{ __('Classes Requiring Coverage') }} (${slots.length})
                </span>
            </div>
    `;

    if (slots.length === 0) {
        html += `
            <div style="font-size:0.85rem; color:var(--text-secondary); font-style:italic; padding:0.25rem 0;">
                <i class="ph ph-info me-1"></i> {{ __('No weekly recurring classes scheduled on these days.') }}
            </div>
        `;
    } else {
        html += `<div style="display:flex; flex-wrap:wrap; gap:0.5rem;">`;
        slots.forEach(s => {
            const timeRange = (s.start_time || '').substr(0, 5) + ' - ' + (s.end_time || '').substr(0, 5);
            html += `
                <div style="background:rgba(var(--primary-rgb),0.08); border:1px solid rgba(var(--primary-rgb),0.25); border-radius:0.75rem; padding:0.4rem 0.75rem; font-size:0.78rem;">
                    <strong style="color:var(--primary);">${getDayName(s.day_of_week)}</strong>
                    <span style="color:var(--text-primary); margin-left:4px; font-weight:700;">${timeRange}</span>
                    <span style="color:var(--text-secondary); margin-left:4px;">• ${s.subject_name || 'Class'} (${s.room_number || 'Room'})</span>
                </div>
            `;
        });
        html += `</div>`;
    }
    html += `</div>`;

    // 2. Candidate Teachers
    html += `
        <div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.85rem;">
                <span style="font-size:0.8rem; font-weight:800; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="ph ph-users-three" style="color:#3b82f6; margin-right:4px;"></i>
                    {{ __('Department Candidates') }} (${candidates.length})
                </span>
                <span style="font-size:0.75rem; color:var(--text-secondary);">
                    {{ __('Ranked by timetable availability') }}
                </span>
            </div>
    `;

    if (candidates.length === 0) {
        html += `
            <div style="text-align:center; padding:2rem; color:var(--text-muted); background:rgba(255,255,255,0.02); border-radius:1rem; border:1px solid var(--border);">
                <i class="ph ph-user-minus" style="font-size:2rem; opacity:0.4; display:block; margin-bottom:0.5rem;"></i>
                <div>{{ __('No other active teachers in this department.') }}</div>
            </div>
        `;
    } else {
        candidates.forEach(cand => {
            const cardClass = cand.is_assigned ? 'cand-card assigned-cand' : (cand.is_free ? 'cand-card free-cand' : 'cand-card');
            const avatarLetter = (cand.name || 'T').charAt(0).toUpperCase();

            html += `
                <div class="${cardClass}">
                    <div style="display:flex; align-items:center; gap:0.85rem; min-width:0; flex:1;">
                        <div style="width:44px; height:44px; border-radius:50%; background:var(--primary); color:#000; font-weight:800; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                            ${cand.photo ? `<img src="${cand.photo}" style="width:100%; height:100%; object-fit:cover;">` : avatarLetter}
                        </div>
                        <div style="min-width:0;">
                            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                <span style="font-weight:800; font-size:0.95rem; color:var(--text-primary);">${cand.name}</span>
                                ${cand.name_kh ? `<span style="font-size:0.8rem; color:var(--text-secondary);">(${cand.name_kh})</span>` : ''}
                                ${cand.is_assigned ? `<span class="badge" style="background:#3b82f6; color:#fff; font-size:0.7rem; padding:2px 7px;"><i class="ph ph-check-circle me-1"></i>{{ __('Current Sub') }}</span>` : ''}
                            </div>
                            <div style="font-size:0.78rem; color:var(--text-secondary); margin-top:2px; display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                <span>${cand.employee_id || 'ID'}</span>
                                ${cand.phone ? `<span>• <i class="ph ph-phone"></i> ${cand.phone}</span>` : ''}
                            </div>
                            <div style="margin-top:4px;">
                                ${cand.is_free
                                    ? `<span style="display:inline-flex; align-items:center; gap:4px; font-size:0.73rem; color:#10b981; font-weight:800;"><i class="ph ph-check-circle-fill"></i> {{ __('100% Conflict-Free') }}</span>`
                                    : `<span style="display:inline-flex; align-items:center; gap:4px; font-size:0.73rem; color:#f59e0b; font-weight:800;"><i class="ph ph-warning"></i> ${cand.conflict_count} {{ __('timetable conflict(s)') }}</span>`
                                }
                            </div>
                        </div>
                    </div>

                    <div style="flex-shrink:0;">
                        ${cand.is_assigned ? `
                            <button disabled class="btn btn-sm" style="background:rgba(59,130,246,0.2); color:#3b82f6; border:1px solid rgba(59,130,246,0.4); border-radius:0.75rem; padding:0.45rem 0.9rem; font-weight:800; cursor:default;">
                                <i class="ph ph-check"></i> {{ __('Assigned') }}
                            </button>
                        ` : `
                            <button onclick="doAssignSubstitute(${leaveId}, ${cand.id}, '${escapeJsString(cand.name)}', ${cand.is_free})"
                                class="btn btn-sm ${cand.is_free ? 'btn-primary' : 'btn-secondary'}"
                                style="border-radius:0.75rem; padding:0.45rem 1rem; font-weight:800; display:inline-flex; align-items:center; gap:0.35rem; font-size:0.82rem;">
                                <i class="ph ph-user-switch"></i>
                                <span>${cand.is_free ? '{{ __("Assign") }}' : '{{ __("Assign Anyway") }}'}</span>
                            </button>
                        `}
                    </div>
                </div>
            `;
        });
    }

    html += `</div>`;
    content.innerHTML = html;
}

function escapeJsString(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

async function doAssignSubstitute(leaveId, subId, subName, isFree) {
    if (!isFree) {
        if (!confirm(`{{ __("Warning: This teacher has conflicting classes during this period. Assign") }} ${subName} {{ __("anyway?") }}`)) {
            return;
        }
    }

    try {
        const res = await window.fetchApi(`{{ url('/leave-requests') }}/${leaveId}/substitute`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ substitute_teacher_id: subId })
        });
        alert(res.message || 'Substitute assigned successfully!');
        window.location.reload();
    } catch(err) {
        alert(err.message || 'Failed to assign substitute.');
    }
}
</script>
@endsection
