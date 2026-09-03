@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1">
                <i class="fas fa-bullhorn text-warning me-2"></i>{{ __('Announcements & Broadcasts') }}
            </h1>
            <p class="text-muted small mb-0">{{ __('Publish institution notices, portal banners, and instant Telegram broadcast alerts.') }}</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
            <i class="fas fa-plus-circle me-1"></i>{{ __('Create Announcement') }}
        </button>
    </div>

    <!-- Announcements List -->
    <div class="row g-3">
        @forelse($announcements as $item)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <span class="badge rounded-pill bg-{{ $item->priority === 'urgent' ? 'danger' : ($item->priority === 'warning' ? 'warning' : 'info') }} px-3 py-1">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ strtoupper($item->priority) }}
                        </span>
                        <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="deleteAnnouncement({{ $item->id }})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <h5 class="fw-bold text-dark mb-1">{{ $item->title }}</h5>
                        @if($item->title_kh)
                            <h6 class="text-primary small fw-semibold mb-2" style="font-family: 'Battambang', sans-serif;">{{ $item->title_kh }}</h6>
                        @endif
                        <p class="text-muted small mb-3 text-truncate-3" style="min-height: 48px;">
                            {{ $item->content }}
                        </p>
                        @if($item->content_kh)
                            <p class="text-secondary small text-truncate-3 mb-0" style="font-family: 'Battambang', sans-serif;">
                                {{ $item->content_kh }}
                            </p>
                        @endif
                    </div>
                    <div class="card-footer bg-light border-0 py-2 px-3 d-flex justify-content-between align-items-center small text-muted">
                        <span><i class="far fa-clock me-1"></i>{{ $item->created_at->diffForHumans() }}</span>
                        @if($item->send_telegram)
                            <span class="badge bg-success-subtle text-success"><i class="fab fa-telegram me-1"></i>Sent to Telegram</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                    <i class="fas fa-bullhorn fa-3x text-light mb-3"></i>
                    <h5 class="fw-bold text-secondary">{{ __('No Announcements Published Yet') }}</h5>
                    <p class="text-muted small">{{ __('Click "Create Announcement" to post your first announcement.') }}</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- New Announcement Modal -->
<div class="modal fade" id="newAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-bullhorn text-warning me-2"></i>{{ __('New Announcement') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="announcementForm" onsubmit="submitAnnouncement(event)">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Title (English)') }}</label>
                            <input type="text" name="title" class="form-control rounded-3" required placeholder="e.g. Midterm Examination Schedule">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Title (Khmer)') }}</label>
                            <input type="text" name="title_kh" class="form-control rounded-3" placeholder="ឧទាហរណ៍៖ ប្រតិទិនប្រឡងពាក់កណ្តាលឆមាស">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">{{ __('Content (English)') }}</label>
                            <textarea name="content" class="form-control rounded-3" rows="3" required placeholder="Write announcement text..."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">{{ __('Content (Khmer)') }}</label>
                            <textarea name="content_kh" class="form-control rounded-3" rows="3" placeholder="សរសេរខ្លឹមសារជាភាសាខ្មែរ..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Priority Level') }}</label>
                            <select name="priority" class="form-select rounded-3">
                                <option value="info">{{ __('Information (Blue)') }}</option>
                                <option value="warning">{{ __('Warning (Yellow)') }}</option>
                                <option value="urgent">{{ __('Urgent (Red)') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Expiration Date (Optional)') }}</label>
                            <input type="date" name="expires_at" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="send_telegram" value="1" id="sendTelegramCheck" checked>
                                <label class="form-check-input-label fw-semibold text-dark small" for="sendTelegramCheck">
                                    <i class="fab fa-telegram text-primary me-1"></i>{{ __('Broadcast immediately to Telegram channel & teachers') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">{{ __('Publish & Broadcast') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function submitAnnouncement(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('announcementForm'));
    fetch("{{ route('api.announcements.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    }).then(r => r.json()).then(d => {
        if (d.success) {
            alert(d.message);
            location.reload();
        } else {
            alert(d.message || "Failed to publish announcement.");
        }
    });
}

function deleteAnnouncement(id) {
    if (confirm("{{ __('Are you sure you want to delete this announcement?') }}")) {
        fetch(`/api-web/announcements/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
