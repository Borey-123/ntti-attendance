@extends('layouts.app')

@section('title', __('Teaching Timetables'))

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
        flex-wrap: wrap;
    }
    .pill-btn {
        padding: 0.6rem 1.25rem;
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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem; font-weight: 800;">{{ __('Teaching Timetables') }}</h1>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">{{ __('Manage weekly class schedules, room allocations, and instructor slots.') }}</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="openModal('addScheduleModal')" style="width: auto !important; padding: 0.55rem 1.25rem; font-size: 0.85rem; font-weight: 800; border-radius: 0.75rem; display: inline-flex; align-items: center; gap: 0.45rem; flex-shrink: 0; white-space: nowrap;">
                <i class="ph ph-plus-circle" style="font-size: 1.15rem;"></i> <span>{{ __('Add Slot') }}</span>
            </button>
        </div>
    </div>

    @php
        $totalSlots     = $schedules->count();
        $activeTeachers = $schedules->pluck('teacher_id')->unique()->count();
        $assignedRooms  = $schedules->pluck('room_number')->filter()->unique()->count();
        $totalSubjects  = $schedules->pluck('subject_name')->unique()->count();

        $daysMap = [
            1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'),
            4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday'), 7 => __('Sunday')
        ];
    @endphp

    {{-- ── Summary Metrics Grid ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-books"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Weekly Classes') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $totalSlots }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-users-three"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Instructors') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $activeTeachers }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-door"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Rooms Assigned') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $assignedRooms }}</div>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-book-open"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Unique Subjects') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $totalSubjects }}</div>
            </div>
        </div>
    </div>

    {{-- ── Sliding Day Filter Tabs ── --}}
    <div class="pill-tabs">
        <button class="pill-btn active" onclick="switchDay('all', this)">
            <i class="ph ph-list"></i> {{ __('All Days') }}
        </button>
        @foreach($daysMap as $dNum => $dName)
        @php $dayCount = $schedules->where('day_of_week', $dNum)->count(); @endphp
        <button class="pill-btn" onclick="switchDay({{ $dNum }}, this)">
            {{ $dName }}
            @if($dayCount > 0)
            <span style="background: rgba(var(--primary-rgb), 0.2); color: var(--primary); border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 900;">{{ $dayCount }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- ── Schedule Registry Table ── --}}
    <div class="card" style="border-radius: 2rem; overflow: hidden;">
        <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                <i class="ph ph-calendar"></i>
                {{ __('Schedule Registry') }}
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary);">({{ $schedules->count() }})</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="min-width: 850px;">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">#</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Time Slot') }}</th>
                        <th>{{ __('Subject / Class Name') }}</th>
                        <th>{{ __('Room') }}</th>
                        <th>{{ __('Instructor') }}</th>
                        <th style="width: 100px; text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="schedTableBody">
                    @forelse($schedules as $index => $s)
                    @php
                        $teacherName = $s->teacher ? (app()->getLocale() == 'km' ? ($s->teacher->name_kh ?: $s->teacher->name) : $s->teacher->name) : 'N/A';
                    @endphp
                    <tr class="sched-row" data-day="{{ $s->day_of_week }}">
                        <td style="text-align: center;">
                            <div style="width: 32px; height: 32px; border-radius: 0.6rem; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                {{ $index + 1 }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info" style="font-size: 0.8rem; font-weight: 800;">
                                <i class="ph ph-calendar-blank me-1"></i>{{ $daysMap[$s->day_of_week] ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 800; font-size: 0.88rem; color: var(--primary); font-family: monospace;">
                                <i class="ph ph-clock me-1"></i>{{ substr($s->start_time,0,5) }} – {{ substr($s->end_time,0,5) }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 800; color: var(--text-primary); font-size: 0.95rem;">{{ $s->subject_name }}</div>
                        </td>
                        <td>
                            @if($s->room_number)
                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); font-weight: 800;">
                                    <i class="ph ph-door me-1"></i>{{ $s->room_number }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: #000; font-weight: 800; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                    @if($s->teacher && $s->teacher->photo)
                                        <img src="{{ to_asset_url($s->teacher->photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        {{ strtoupper(substr($s->teacher->name ?? 'T', 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-primary); line-height: 1.2;">{{ $teacherName }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-secondary);">{{ $s->teacher->department ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" class="sched-delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger btn-delete-sched" title="{{ __('Delete') }}" style="border-radius: 0.6rem; width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="ph ph-trash" style="font-size: 1.1rem;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                            <i class="ph ph-calendar-x" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            <div style="font-weight: 800; font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 0.3rem;">{{ __('No teaching slots created yet.') }}</div>
                            <div style="font-size: 0.85rem;">{{ __('Click "Add Teaching Slot" to set up weekly timetables.') }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Add Slot Modal ── --}}
<div class="modal-overlay" id="addScheduleModal" onclick="if(event.target == this) closeModal('addScheduleModal')">
    <div class="modal-content" style="border-radius: 2rem; padding: 2.5rem; max-width: 520px;">
        <div class="modal-header" style="margin-bottom: 1.75rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-weight: 800; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                <i class="ph ph-calendar-plus"></i> {{ __('Add Teaching Slot') }}
            </h3>
            <button class="modal-close" onclick="closeModal('addScheduleModal')">&times;</button>
        </div>
        <form action="{{ route('schedules.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">
                    <i class="ph ph-funnel" style="margin-right:3px;"></i> {{ __('Filter by Department') }}
                </label>
                <select id="slotDeptFilter" class="form-control" style="border-radius: 1rem;" onchange="filterSlotTeachers()">
                    <option value="">{{ __('All Departments') }}</option>
                    @foreach($teachers->pluck('department')->filter()->unique()->sort() as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">{{ __('Teacher') }}</label>
                <select name="teacher_id" id="slotTeacherSelect" class="form-control" style="border-radius: 1rem;" required>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" data-dept="{{ $t->department }}">{{ app()->getLocale() == 'km' ? ($t->name_kh ?: $t->name) : $t->name }} ({{ $t->department }})</option>
                    @endforeach
                </select>
                <div id="slotTeacherCount" style="font-size:0.78rem; color:var(--text-muted); margin-top:0.35rem;"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">{{ __('Day of Week') }}</label>
                    <select name="day_of_week" class="form-control" style="border-radius: 1rem;" required>
                        <option value="1">{{ __('Monday') }}</option>
                        <option value="2">{{ __('Tuesday') }}</option>
                        <option value="3">{{ __('Wednesday') }}</option>
                        <option value="4">{{ __('Thursday') }}</option>
                        <option value="5">{{ __('Friday') }}</option>
                        <option value="6">{{ __('Saturday') }}</option>
                        <option value="7">{{ __('Sunday') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">{{ __('Room') }}</label>
                    <input type="text" name="room_number" class="form-control" style="border-radius: 1rem;" placeholder="{{ __('e.g. Room 204') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">{{ __('Start Time') }}</label>
                    <input type="time" name="start_time" class="form-control" style="border-radius: 1rem;" value="08:00" required>
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">{{ __('End Time') }}</label>
                    <input type="time" name="end_time" class="form-control" style="border-radius: 1rem;" value="11:00" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.75rem;">
                <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">{{ __('Subject / Class Name') }}</label>
                <input type="text" name="subject_name" class="form-control" style="border-radius: 1rem;" placeholder="{{ __('e.g. Web Development 101') }}" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border); padding-top: 1.25rem;">
                <button type="button" class="btn btn-secondary" style="border-radius: 1rem; padding: 0.6rem 1.5rem;" onclick="closeModal('addScheduleModal')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 1rem; padding: 0.6rem 1.5rem; font-weight: 800;">
                    <i class="ph ph-check me-1"></i> {{ __('Save Slot') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Confirm Modal ── --}}
<div id="confirmModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:2rem; padding:2.5rem; max-width:420px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,0.4); text-align:center; position:relative;">
        <div id="confirmIcon" style="width:70px;height:70px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.25rem;"></div>
        <h3 id="confirmTitle" style="font-weight:800;margin-bottom:0.5rem;color:var(--text-primary);"></h3>
        <p id="confirmMessage" style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:1.75rem;"></p>
        <div style="display:flex;gap:0.75rem;justify-content:center;">
            <button onclick="closeConfirmModal()" class="btn btn-secondary" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:700;">{{ __('Cancel') }}</button>
            <button id="confirmActionBtn" class="btn" style="border-radius:1rem;padding:0.65rem 1.75rem;font-weight:800;"></button>
        </div>
    </div>
</div>

<script>
function openConfirmModal({ title, message, actionLabel, actionClass, icon, iconBg, onConfirm }) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    const iconEl = document.getElementById('confirmIcon');
    iconEl.innerHTML = `<i class="${icon}"></i>`;
    iconEl.style.background = iconBg;
    iconEl.style.color = '#fff';
    const btn = document.getElementById('confirmActionBtn');
    btn.textContent = actionLabel;
    btn.className = `btn ${actionClass}`;
    btn.style.borderRadius = '1rem';
    btn.style.padding = '0.65rem 1.75rem';
    btn.style.fontWeight = '800';
    btn.onclick = () => { closeConfirmModal(); onConfirm(); };
    const modal = document.getElementById('confirmModal');
    modal.style.display = 'flex';
}
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
// Close on backdrop click
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

// Hook delete buttons
document.querySelectorAll('.btn-delete-sched').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('.sched-delete-form');
        openConfirmModal({
            title: '{{ __("Delete Schedule Slot?") }}',
            message: '{{ __("This teaching slot will be permanently removed. This action cannot be undone.") }}',
            actionLabel: '{{ __("Yes, Delete") }}',
            actionClass: 'btn-danger',
            icon: 'ph ph-trash',
            iconBg: '#ef4444',
            onConfirm: () => form.submit()
        });
    });
});

function switchDay(day, btn) {
    document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const rows = document.querySelectorAll('.sched-row');
    rows.forEach(r => {
        const show = day === 'all' || r.dataset.day == day;
        r.style.display = show ? '' : 'none';
    });
}
function openModal(id) {
    document.getElementById(id).classList.add('active');
    if (id === 'addScheduleModal') {
        const deptFilter = document.getElementById('slotDeptFilter');
        if (deptFilter) { deptFilter.value = ''; filterSlotTeachers(); }
    }
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Filter Teacher dropdown by department inside Add Slot modal
function filterSlotTeachers() {
    const dept = document.getElementById('slotDeptFilter').value;
    const select = document.getElementById('slotTeacherSelect');
    const countEl = document.getElementById('slotTeacherCount');
    const options = select.querySelectorAll('option');
    let visibleCount = 0;
    let firstVisible = null;
    options.forEach(opt => {
        const match = !dept || opt.dataset.dept === dept;
        opt.hidden = !match;
        opt.disabled = !match;
        if (match) { visibleCount++; if (!firstVisible) firstVisible = opt; }
    });
    if (firstVisible) select.value = firstVisible.value;
    if (countEl) {
        countEl.textContent = dept
            ? visibleCount + ' teacher' + (visibleCount !== 1 ? 's' : '') + ' in this department'
            : '';
    }
}
</script>
@endsection
