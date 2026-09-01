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
    <div class="d-flex justify-between align-center" style="margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">{{ __('Teaching Timetables') }}</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Manage weekly class schedules, room allocations, and instructor slots.') }}</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addScheduleModal')" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 1.25rem; padding: 0.8rem 1.5rem; font-weight: 800; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25); transition: all 0.2s ease;">
            <i class="ph ph-plus-circle" style="font-size: 1.2rem;"></i> <span>{{ __('Add Teaching Slot') }}</span>
        </button>
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
                            <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this schedule slot?') }}');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Delete') }}" style="border-radius: 0.6rem; width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
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
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">{{ __('Teacher') }}</label>
                <select name="teacher_id" class="form-control" style="border-radius: 1rem;" required>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ app()->getLocale() == 'km' ? ($t->name_kh ?: $t->name) : $t->name }} ({{ $t->department }})</option>
                    @endforeach
                </select>
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

<script>
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
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
</script>
@endsection
