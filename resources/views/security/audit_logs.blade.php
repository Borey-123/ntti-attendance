@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1">
                <i class="fas fa-history text-primary me-2"></i>{{ __('System Audit Logs') }}
            </h1>
            <p class="text-muted small mb-0">{{ __('Comprehensive audit trail tracking administrative actions, system modifications, and IP addresses.') }}</p>
        </div>
        <button class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="clearAuditLogs()">
            <i class="fas fa-trash-alt me-1"></i>{{ __('Clear Audit Logs') }}
        </button>
    </div>

    <!-- Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('security.audit_logs') }}" class="row g-2 align-items-center">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="{{ __('Search by user, action, IP or description...') }}" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary rounded-3"><i class="fas fa-filter me-1"></i>{{ __('Filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>{{ __('Timestamp') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('IP Address') }}</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                <td class="text-nowrap text-dark fw-semibold">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                            {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $log->user_name ?? __('System') }}</div>
                                            <span class="text-muted micro-text">ID: {{ $log->user_id ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        <i class="fas fa-shield-alt me-1"></i>{{ $log->action }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-secondary text-truncate" style="max-width: 350px;" title="{{ $log->description }}">
                                        {{ $log->description ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <code class="text-primary bg-light px-2 py-1 rounded">{{ $log->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 text-light"></i>
                                    <p class="mb-0 fw-semibold">{{ __('No audit logs found.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<script>
function clearAuditLogs() {
    if (confirm("{{ __('Are you sure you want to clear all audit logs?') }}")) {
        fetch("{{ route('security.audit_logs.clear') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                alert(d.message);
                location.reload();
            }
        });
    }
}
</script>
@endsection
