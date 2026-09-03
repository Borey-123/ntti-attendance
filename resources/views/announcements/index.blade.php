@extends('layouts.app')

@section('title', __('Announcements & Broadcasts'))

@section('content')
<div class="animate-fade-up">

    {{-- ── Header ── --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <h1 class="page-title" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-megaphone" style="color: var(--primary);"></i>{{ __('Announcements & Broadcasts') }}
                </h1>
                <button class="btn btn-primary" onclick="openAnnouncementModal()" style="border-radius: 0.75rem; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.95rem; background: linear-gradient(135deg, var(--primary), #00b894); border: none; color: #000; white-space: nowrap; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25);">
                    <i class="ph ph-plus-circle" style="font-size: 1.05rem;"></i>
                    {{ __('Create Announcement') }}
                </button>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.88rem; margin-top: 0.25rem; margin-bottom: 0;">{{ __('Publish institution notices, portal banners, and instant Telegram broadcast alerts.') }}</p>
        </div>
    </div>

    {{-- ── Summary Metrics Grid ── --}}
    @php
        $totalAnnounce = $announcements->count();
        $urgentCount   = $announcements->where('priority', 'urgent')->count();
        $telegramCount = $announcements->where('send_telegram', 1)->count();
        $infoCount     = $announcements->where('priority', 'info')->count();
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-broadcast"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Total Announcements') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $totalAnnounce }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-warning-circle"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Urgent Notices') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $urgentCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-paper-plane-tilt"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Telegram Broadcasts') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $telegramCount }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-info"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('General Notices') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $infoCount }}</div>
            </div>
        </div>
    </div>

    {{-- ── Announcements List Grid ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
        @forelse($announcements as $item)
            @php
                $priorityBadge = 'badge-info';
                $priorityLabel = __('Information (Blue)');
                if ($item->priority === 'urgent') {
                    $priorityBadge = 'badge-danger';
                    $priorityLabel = __('Urgent (Red)');
                } elseif ($item->priority === 'warning') {
                    $priorityBadge = 'badge-warning';
                    $priorityLabel = __('Warning (Yellow)');
                }
            @endphp
            <div class="card" style="border-radius: 1.5rem; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: transform 0.2s, box-shadow 0.2s;">
                <div>
                    {{-- Header Row --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <span class="badge {{ $priorityBadge }}" style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.35rem 0.75rem; border-radius: 0.6rem;">
                            <i class="ph ph-circle-wavy-warning" style="margin-right: 0.3rem;"></i>{{ strtoupper($item->priority) }}
                        </span>
                        <button onclick="confirmDeleteAnnouncement({{ $item->id }})" style="background: rgba(239, 68, 68, 0.1); border: none; color: #ef4444; width: 32px; height: 32px; border-radius: 0.6rem; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                            <i class="ph ph-trash" style="font-size: 1rem;"></i>
                        </button>
                    </div>

                    {{-- Title --}}
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.4rem; line-height: 1.3;">
                        {{ $item->title }}
                    </h3>

                    @if($item->title_kh)
                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--primary); font-family: 'Battambang', 'Kantumruy Pro', sans-serif; margin-bottom: 0.8rem;">
                            {{ $item->title_kh }}
                        </h4>
                    @endif

                    {{-- Content --}}
                    <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.5; margin-bottom: 0.75rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $item->content }}
                    </p>

                    @if($item->content_kh)
                        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; font-family: 'Battambang', 'Kantumruy Pro', sans-serif; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $item->content_kh }}
                        </p>
                    @endif
                </div>

                {{-- Footer Info --}}
                <div style="border-top: 1px solid var(--border); padding-top: 0.85rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.78rem; color: var(--text-secondary);">
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <i class="ph ph-clock" style="color: var(--primary);"></i>
                        <span>{{ $item->created_at->diffForHumans() }}</span>
                    </div>

                    @if($item->send_telegram)
                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <i class="ph ph-paper-plane-tilt"></i> {{ __('Sent to Telegram') }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1;">
                <div class="card" style="border-radius: 2rem; text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                    <i class="ph ph-megaphone-simple" style="font-size: 3.5rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                    <h3 style="font-weight: 800; font-size: 1.25rem; color: var(--text-secondary); margin-bottom: 0.4rem;">
                        {{ __('No Announcements Published Yet') }}
                    </h3>
                    <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 420px; margin: 0 auto 1.5rem auto;">
                        {{ __('Click "Create Announcement" to post your first announcement.') }}
                    </p>
                    <button class="btn btn-primary" onclick="openAnnouncementModal()" style="border-radius: 0.85rem; font-weight: 800; padding: 0.65rem 1.5rem;">
                        <i class="ph ph-plus-circle"></i> {{ __('Create Announcement') }}
                    </button>
                </div>
            </div>
        @endforelse
    </div>

</div>

{{-- ── Create Announcement Modal ── --}}
<div id="announcementModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:2rem; padding:2rem; max-width:650px; width:92%; box-shadow:0 25px 60px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <h3 style="margin:0; font-weight:800; display:flex; align-items:center; gap:0.6rem; color:var(--text-primary);">
                <i class="ph ph-megaphone" style="color:var(--primary);"></i>
                {{ __('New Announcement') }}
            </h3>
            <button onclick="closeAnnouncementModal()" style="background:none; border:none; color:var(--text-secondary); font-size:1.5rem; cursor:pointer;">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form id="announcementForm" onsubmit="submitAnnouncement(event)">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.4rem;">
                        {{ __('Title (English)') }} *
                    </label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Midterm Examination Schedule" style="background:var(--bg-dark); font-weight:600;">
                </div>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.4rem;">
                        {{ __('Title (Khmer)') }}
                    </label>
                    <input type="text" name="title_kh" class="form-control" placeholder="ឧទាហរណ៍៖ ប្រតិទិនប្រឡងពាក់កណ្តាលឆមាស" style="background:var(--bg-dark); font-family:'Battambang', sans-serif;">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.4rem;">
                    {{ __('Content (English)') }} *
                </label>
                <textarea name="content" class="form-control" rows="3" required placeholder="Write announcement text..." style="background:var(--bg-dark); font-weight:500;"></textarea>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.4rem;">
                    {{ __('Content (Khmer)') }}
                </label>
                <textarea name="content_kh" class="form-control" rows="3" placeholder="សរសេរខ្លឹមសារជាភាសាខ្មែរ..." style="background:var(--bg-dark); font-family:'Battambang', sans-serif;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.4rem;">
                        {{ __('Priority Level') }}
                    </label>
                    <select name="priority" class="form-control" style="background:var(--bg-dark); font-weight:700;">
                        <option value="info">{{ __('Information (Blue)') }}</option>
                        <option value="warning">{{ __('Warning (Yellow)') }}</option>
                        <option value="urgent">{{ __('Urgent (Red)') }}</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.4rem;">
                        {{ __('Expiration Date (Optional)') }}
                    </label>
                    <input type="date" name="expires_at" class="form-control" style="background:var(--bg-dark);">
                </div>
            </div>

            <div style="background: rgba(var(--primary-rgb), 0.08); border: 1px solid rgba(var(--primary-rgb), 0.2); border-radius: 1rem; padding: 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <input type="checkbox" name="send_telegram" value="1" id="sendTelegramCheck" checked style="width: 20px; height: 20px; accent-color: var(--primary);">
                <label for="sendTelegramCheck" style="font-size: 0.88rem; font-weight: 700; color: var(--text-primary); cursor: pointer; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="ph ph-paper-plane-tilt" style="color: var(--primary); font-size: 1.1rem;"></i>
                    {{ __('Broadcast immediately to Telegram channel & teachers') }}
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeAnnouncementModal()" class="btn btn-secondary" style="border-radius: 0.85rem; padding: 0.65rem 1.5rem; font-weight: 700;">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="btn btn-primary" style="border-radius: 0.85rem; padding: 0.65rem 1.75rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), #00b894); border: none; color: #000;">
                    {{ __('Publish & Broadcast') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Confirm Delete Modal ── --}}
<div id="deleteAnnounceModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:2rem; padding:2.5rem; max-width:440px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,0.4); text-align:center;">
        <div style="width:70px;height:70px;border-radius:50%;background:#ef4444;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.25rem;">
            <i class="ph ph-trash"></i>
        </div>
        <h3 style="font-weight:800;margin-bottom:0.5rem;color:var(--text-primary);">{{ __('Delete Announcement?') }}</h3>
        <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:1.75rem;">
            {{ __('Are you sure you want to delete this announcement?') }}
        </p>
        <div style="display:flex;gap:0.75rem;justify-content:center;">
            <button onclick="closeDeleteAnnounceModal()" class="btn btn-secondary" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:700;">{{ __('Cancel') }}</button>
            <button id="confirmDeleteBtn" class="btn btn-danger" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:800;">{{ __('Yes, Delete') }}</button>
        </div>
    </div>
</div>

<script>
function openAnnouncementModal() {
    document.getElementById('announcementModal').style.display = 'flex';
}
function closeAnnouncementModal() {
    document.getElementById('announcementModal').style.display = 'none';
}
document.getElementById('announcementModal').addEventListener('click', function(e) {
    if (e.target === this) closeAnnouncementModal();
});

let deletingId = null;
function confirmDeleteAnnouncement(id) {
    deletingId = id;
    document.getElementById('confirmDeleteBtn').onclick = () => doDeleteAnnouncement(id);
    document.getElementById('deleteAnnounceModal').style.display = 'flex';
}
function closeDeleteAnnounceModal() {
    document.getElementById('deleteAnnounceModal').style.display = 'none';
}

async function submitAnnouncement(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('announcementForm'));
    try {
        const res = await window.fetchApi("{{ route('api.announcements.store') }}", {
            method: 'POST',
            body: formData
        });
        closeAnnouncementModal();
        window.location.reload();
    } catch(err) {
        alert(err.message || "Failed to publish announcement.");
    }
}

async function doDeleteAnnouncement(id) {
    try {
        await window.fetchApi(`/api-web/announcements/${id}`, {
            method: 'DELETE'
        });
        closeDeleteAnnounceModal();
        window.location.reload();
    } catch(err) {
        alert(err.message || "Failed to delete announcement.");
    }
}
</script>
@endsection
