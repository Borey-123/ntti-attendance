@extends('layouts.app')

@section('title', __('System Audit Logs'))

@section('content')
<div class="animate-fade-up">

    {{-- ── Header ── --}}
    <div class="d-flex justify-between align-center" style="margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">
                <i class="ph ph-shield-checkered" style="color: var(--primary); margin-right: 0.5rem;"></i>{{ __('System Audit Logs') }}
            </h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Comprehensive audit trail tracking administrative actions, system modifications, and IP addresses.') }}</p>
        </div>
        <button class="btn btn-danger" onclick="confirmClearAuditLogs()" style="border-radius: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem;">
            <i class="ph ph-trash" style="font-size: 1.1rem;"></i>
            {{ __('Clear Audit Logs') }}
        </button>
    </div>

    {{-- ── Summary Metrics Grid ── --}}
    @php
        $totalLogsCount = \App\Models\AuditLog::count();
        $todayLogsCount = \App\Models\AuditLog::whereDate('created_at', \Carbon\Carbon::today())->count();
        $uniqueUsersCount = \App\Models\AuditLog::distinct('user_id')->count('user_id');
        $uniqueIpsCount = \App\Models\AuditLog::distinct('ip_address')->count('ip_address');
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-list-checks"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Total System Logs') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $totalLogsCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-clock-counter-clockwise"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __("Today's Actions") }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $todayLogsCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-user-gear"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Active Admins') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $uniqueUsersCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-globe-hemisphere-west"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Unique IP Addresses') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $uniqueIpsCount }}</div>
            </div>
        </div>
    </div>

    {{-- ── Search & Filter Bar ── --}}
    <div class="card" style="padding: 1rem 1.5rem; border-radius: 1.25rem; margin-bottom: 2rem;">
        <form method="GET" action="{{ route('security.audit_logs') }}" style="display: flex; gap: 0.75rem; align-items: center; width: 100%;">
            <div class="input-with-icon" style="flex: 1;">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" class="form-control" placeholder="{{ __('Search by user, action, IP or description...') }}" value="{{ request('search') }}" style="background: var(--bg-dark); font-weight: 600; padding-top: 0.55rem; padding-bottom: 0.55rem; border-radius: 0.75rem;">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.2rem; border-radius: 0.75rem; font-weight: 800; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; flex-shrink: 0;">
                <i class="ph ph-funnel"></i> {{ __('Filter') }}
            </button>
            @if(request('search'))
            <a href="{{ route('security.audit_logs') }}" class="btn btn-secondary" style="padding: 0.55rem 1rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.88rem; text-decoration: none; white-space: nowrap; flex-shrink: 0;">
                {{ __('Reset') }}
            </a>
            @endif
        </form>
    </div>

    {{-- ── Audit Log Table Container ── --}}
    <div class="card" style="border-radius: 2rem; overflow: hidden;">
        <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem; color: var(--text-primary);">
                <i class="ph ph-shield-check"></i>
                {{ __('Audit Trail Registry') }}
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary);">({{ $logs->total() }})</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">#</th>
                        <th>{{ __('Timestamp') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Action') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th style="text-align: center; width: 140px;">{{ __('IP Address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                    <tr>
                        <td style="text-align: center;">
                            <div style="width: 32px; height: 32px; border-radius: 0.6rem; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                {{ $index + 1 + ($logs->currentPage() - 1) * $logs->perPage() }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.88rem;">
                                <i class="ph ph-calendar-blank" style="color: var(--primary); margin-right: 0.3rem;"></i>
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #00b894); color: #000; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;">
                                    {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--text-primary); font-size: 0.9rem; line-height: 1.2;">
                                        {{ $log->user_name ?? __('System') }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">
                                        ID: {{ $log->user_id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $act = strtolower($log->action ?? '');
                                $badgeClass = 'badge-info';
                                if(str_contains($act, 'delete') || str_contains($act, 'clear')) $badgeClass = 'badge-danger';
                                elseif(str_contains($act, 'update') || str_contains($act, 'toggle')) $badgeClass = 'badge-warning';
                                elseif(str_contains($act, 'create') || str_contains($act, 'login')) $badgeClass = 'badge-success';
                            @endphp
                            <span class="badge {{ $badgeClass }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.35rem 0.75rem; border-radius: 0.6rem;">
                                <i class="ph ph-shield" style="margin-right: 0.3rem;"></i>{{ $log->action }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.88rem; color: var(--text-secondary); max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->description }}">
                                {{ $log->description ?? '—' }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; background: rgba(255,255,255,0.06); padding: 0.3rem 0.6rem; border-radius: 0.5rem; color: var(--primary); border: 1px solid var(--border);">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <i class="ph ph-shield-slash" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            <div style="font-weight: 800; font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 0.3rem;">{{ __('No audit logs found.') }}</div>
                            <div style="font-size: 0.85rem;">{{ __('All administrative and security actions will be logged here.') }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
        {{ $logs->appends(request()->query())->links() }}
    </div>
    @endif

</div>

{{-- ── Confirm Clear Modal ── --}}
<div id="clearAuditModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:2rem; padding:2.5rem; max-width:440px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,0.4); text-align:center;">
        <div style="width:70px;height:70px;border-radius:50%;background:#ef4444;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.25rem;">
            <i class="ph ph-warning"></i>
        </div>
        <h3 style="font-weight:800;margin-bottom:0.5rem;color:var(--text-primary);">{{ __('Clear Audit Logs?') }}</h3>
        <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:1.75rem;">
            {{ __('Are you sure you want to clear all audit logs? This action cannot be undone.') }}
        </p>
        <div style="display:flex;gap:0.75rem;justify-content:center;">
            <button onclick="closeClearAuditModal()" class="btn btn-secondary" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:700;">{{ __('Cancel') }}</button>
            <button onclick="executeClearAuditLogs()" class="btn btn-danger" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:800;">{{ __('Yes, Clear All') }}</button>
        </div>
    </div>
</div>

<script>
function confirmClearAuditLogs() {
    document.getElementById('clearAuditModal').style.display = 'flex';
}
function closeClearAuditModal() {
    document.getElementById('clearAuditModal').style.display = 'none';
}
document.getElementById('clearAuditModal').addEventListener('click', function(e) {
    if (e.target === this) closeClearAuditModal();
});

async function executeClearAuditLogs() {
    try {
        const res = await window.fetchApi("{{ route('security.audit_logs.clear') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        closeClearAuditModal();
        window.location.reload();
    } catch(e) {
        alert(e.message || "{{ __('Failed to clear audit logs') }}");
    }
}
</script>
@endsection
