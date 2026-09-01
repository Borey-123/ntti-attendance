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
                        <th style="width: 140px; text-align: center;">{{ __('Actions') }}</th>
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
                        <td>
                            <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                @if($req->status === 'pending')
                                    <button class="btn btn-sm btn-success" onclick="updateLeaveStatus({{ $req->id }}, 'approved')" title="{{ __('Approve') }}" style="border-radius: 0.6rem; padding: 0.4rem 0.8rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <i class="ph ph-check-bold"></i> {{ __('Approve') }}
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="updateLeaveStatus({{ $req->id }}, 'rejected')" title="{{ __('Reject') }}" style="border-radius: 0.6rem; padding: 0.4rem 0.8rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <i class="ph ph-x-bold"></i> {{ __('Reject') }}
                                    </button>
                                @else
                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">{{ __('Completed') }}</span>
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
