@extends('layouts.app')

@section('title', __('Teaching Timetables'))

@push('styles')
<style>
/* ══════════════════════════════════════════
   STAT CARDS
══════════════════════════════════════════ */
.sched-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.sched-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1.4rem 1.6rem;
    display: flex; align-items: center; gap: 1rem;
    transition: transform .25s, border-color .25s, box-shadow .25s;
    cursor: default;
}
.sched-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 36px rgba(0,0,0,0.13);
    border-color: var(--ssc, var(--primary));
}
.ssc-icon { width:48px;height:48px;border-radius:1rem;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0; }
.ssc-val  { font-size:2rem;font-weight:900;line-height:1; }
.ssc-label{ font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;opacity:.55;margin-top:.2rem; }

/* ══════════════════════════════════════════
   DAY TABS
══════════════════════════════════════════ */
.day-tabs-wrap {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.25rem;
    padding: .5rem .75rem;
    display: flex; gap: .4rem;
    overflow-x: auto; margin-bottom: 1.5rem;
}
.day-tab {
    padding: .5rem 1.25rem;
    border-radius: .85rem;
    border: 1px solid transparent;
    background: transparent;
    color: var(--text-secondary);
    font-size: .82rem; font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s;
    display: inline-flex; align-items: center; gap: .4rem;
}
.day-tab:hover { color: var(--text-primary); border-color: var(--border); }
.day-tab.active {
    background: var(--primary);
    color: #000;
    border-color: var(--primary);
    box-shadow: 0 4px 14px rgba(var(--primary-rgb), .25);
}

/* ══════════════════════════════════════════
   SCHEDULE CARDS (grid view)
══════════════════════════════════════════ */
.sched-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.sched-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1.25rem 1.4rem;
    display: flex; flex-direction: column; gap: .9rem;
    transition: transform .22s, border-color .22s, box-shadow .22s;
    position: relative; overflow: hidden;
}
.sched-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), rgba(var(--primary-rgb),.3));
    border-radius: 1.5rem 1.5rem 0 0;
}
.sched-card:hover {
    transform: translateY(-3px);
    border-color: rgba(var(--primary-rgb), .35);
    box-shadow: 0 12px 30px rgba(0,0,0,0.13);
}
.day-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(var(--primary-rgb),.1);
    color: var(--primary);
    border: 1px solid rgba(var(--primary-rgb),.2);
    border-radius: .6rem;
    padding: .28rem .7rem;
    font-size: .7rem; font-weight: 900; text-transform: uppercase; letter-spacing: .5px;
}
.time-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(var(--primary-rgb),.06);
    border: 1px solid rgba(var(--primary-rgb),.15);
    border-radius: .6rem;
    padding: .3rem .8rem;
    font-size: .82rem; font-weight: 800; font-family: monospace;
    color: var(--primary);
}
.room-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.2);
    border-radius: .6rem;
    padding: .28rem .7rem;
    font-size: .78rem; font-weight: 800;
    color: #f59e0b;
}
.teacher-mini-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 900;
    background: var(--primary); color: #000;
    flex-shrink: 0;
}
.btn-del {
    width: 34px; height: 34px;
    border-radius: .75rem;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(248,81,73,.08); color: var(--danger);
    border: 1px solid rgba(248,81,73,.2);
    cursor: pointer; transition: all .2s;
}
.btn-del:hover { background: var(--danger); color: #fff; transform: scale(1.1); box-shadow: 0 6px 14px rgba(248,81,73,.35); }

/* ══════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════ */
.empty-sched {
    grid-column: 1/-1;
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
    border: 2px dashed var(--border);
    border-radius: 1.5rem;
}

/* ══════════════════════════════════════════
   ADD SLOT MODAL
══════════════════════════════════════════ */
.modal-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 2rem;
    box-shadow: 0 30px 70px rgba(0,0,0,0.4);
    overflow: hidden;
}
.modal-card-header {
    padding: 1.5rem 1.75rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.modal-card-body { padding: 1.75rem; }
.modal-card-footer {
    padding: 1.25rem 1.75rem;
    border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end; gap: .75rem;
}
.form-field { margin-bottom: 1.25rem; }
.form-field label {
    display: flex; align-items: center; gap: .4rem;
    font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-secondary);
    margin-bottom: .5rem;
}
.form-field .form-control {
    border-radius: .9rem;
    padding: .75rem 1rem;
    font-size: .92rem;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    color: var(--text-primary);
    transition: border-color .2s;
    width: 100%;
}
.form-field .form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb),.1);
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- ── PAGE HEADER ── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-primary mb-1 d-flex align-items-center gap-2" style="font-size:1.8rem;">
                <i class="ph ph-clock-afternoon"></i>
                <span>{{ __('Teaching Timetables') }}</span>
            </h2>
            <p class="text-secondary small fw-bold text-uppercase mb-0" style="letter-spacing:.5px;">
                {{ __('Weekly schedule & room assignments') }}
            </p>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2"
                style="padding:.55rem 1.25rem;border-radius:1rem;font-size:.85rem;font-weight:800;box-shadow:0 4px 16px rgba(var(--primary-rgb),.25);"
                data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="ph ph-plus-bold" style="font-size:1rem;"></i> {{ __('Add Slot') }}
        </button>
    </div>

    {{-- ── STAT CARDS ── --}}
    @php
        $totalSlots   = $schedules->count();
        $activeTeachers = $schedules->pluck('teacher_id')->unique()->count();
        $assignedRooms  = $schedules->pluck('room_number')->filter()->unique()->count();
        $totalSubjects  = $schedules->pluck('subject_name')->unique()->count();

        $daysMap = [
            1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'),
            4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday'), 7 => __('Sunday')
        ];
    @endphp
    <div class="sched-stat-grid">
        <div class="sched-stat-card" style="--ssc:var(--primary);">
            <div class="ssc-icon" style="background:rgba(var(--primary-rgb),.1);color:var(--primary);"><i class="ph ph-books"></i></div>
            <div><div class="ssc-val" style="color:var(--primary);">{{ $totalSlots }}</div><div class="ssc-label">{{ __('Weekly Classes') }}</div></div>
        </div>
        <div class="sched-stat-card" style="--ssc:#10b981;">
            <div class="ssc-icon" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="ph ph-users-three"></i></div>
            <div><div class="ssc-val" style="color:#10b981;">{{ $activeTeachers }}</div><div class="ssc-label">{{ __('Instructors') }}</div></div>
        </div>
        <div class="sched-stat-card" style="--ssc:#f59e0b;">
            <div class="ssc-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class="ph ph-door"></i></div>
            <div><div class="ssc-val" style="color:#f59e0b;">{{ $assignedRooms }}</div><div class="ssc-label">{{ __('Rooms Used') }}</div></div>
        </div>
        <div class="sched-stat-card" style="--ssc:#8b5cf6;">
            <div class="ssc-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6;"><i class="ph ph-book-open"></i></div>
            <div><div class="ssc-val" style="color:#8b5cf6;">{{ $totalSubjects }}</div><div class="ssc-label">{{ __('Subjects') }}</div></div>
        </div>
    </div>

    {{-- ── DAY FILTER TABS ── --}}
    <div class="day-tabs-wrap">
        <button class="day-tab active" onclick="switchDay('all', this)">
            <i class="ph ph-list"></i> {{ __('All Days') }}
        </button>
        @foreach($daysMap as $dNum => $dName)
        @php $dayCount = $schedules->where('day_of_week', $dNum)->count(); @endphp
        <button class="day-tab" onclick="switchDay({{ $dNum }}, this)">
            {{ $dName }}
            @if($dayCount > 0)
            <span style="background:rgba(var(--primary-rgb),.15);color:var(--primary);border-radius:2rem;padding:0 .4rem;font-size:.65rem;">{{ $dayCount }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- ── SCHEDULE CARD GRID ── --}}
    <div class="sched-grid" id="schedGrid">
        @forelse($schedules as $s)
        @php
            $teacherName = $s->teacher ? ($s->teacher->name_kh && app()->getLocale()=='km' ? $s->teacher->name_kh : $s->teacher->name) : 'N/A';
        @endphp
        <div class="sched-card" data-day="{{ $s->day_of_week }}">
            {{-- Top row: day badge + time --}}
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <span class="day-badge"><i class="ph ph-calendar-blank"></i> {{ $daysMap[$s->day_of_week] ?? 'N/A' }}</span>
                <span class="time-badge"><i class="ph ph-clock"></i> {{ substr($s->start_time,0,5) }} – {{ substr($s->end_time,0,5) }}</span>
            </div>

            {{-- Subject name --}}
            <div>
                <div class="fw-black" style="font-size:1.05rem;color:var(--text-primary);line-height:1.3;">{{ $s->subject_name }}</div>
                @if($s->room_number)
                <div class="mt-1"><span class="room-badge"><i class="ph ph-door"></i> {{ $s->room_number }}</span></div>
                @endif
            </div>

            {{-- Instructor row + delete --}}
            <div class="d-flex align-items-center justify-content-between gap-2 pt-1" style="border-top:1px solid var(--border);margin-top:auto;">
                <div class="d-flex align-items-center gap-2">
                    <div class="teacher-mini-avatar">
                        @if($s->teacher && $s->teacher->photo)
                            <img src="{{ to_asset_url($s->teacher->photo) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($s->teacher->name ?? 'T', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold text-primary" style="font-size:.88rem;line-height:1.2;">{{ $teacherName }}</div>
                        <div class="small text-secondary fw-bold" style="font-size:.72rem;">{{ $s->teacher->department ?? '' }}</div>
                    </div>
                </div>
                <form action="{{ route('schedules.destroy', $s->id) }}" method="POST"
                      onsubmit="return confirm('{{ __('Delete this slot?') }}');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del" title="{{ __('Delete') }}">
                        <i class="ph ph-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-sched">
            <i class="ph ph-calendar-x" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
            <div class="fw-bold" style="font-size:1.1rem;margin-bottom:.5rem;">{{ __('No classes scheduled') }}</div>
            <div class="small">{{ __('Click "Add Slot" to create the first teaching slot.') }}</div>
        </div>
        @endforelse
    </div>

    {{-- No-results for day filter --}}
    <div id="noResultsMsg" style="display:none;" class="empty-sched">
        <i class="ph ph-calendar-slash" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
        <div class="fw-bold">{{ __('No classes on this day') }}</div>
    </div>

</div>

{{-- ══════════════════════════════════════════
     ADD SLOT MODAL
══════════════════════════════════════════ --}}
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-card">
            <div class="modal-card-header">
                <h5 class="fw-bold d-flex align-items-center gap-2 text-primary mb-0" style="font-size:1.1rem;">
                    <i class="ph ph-calendar-plus fs-4"></i> {{ __('Add Teaching Slot') }}
                </h5>
                <button type="button" class="btn-del" data-bs-dismiss="modal" style="background:rgba(255,255,255,.06);color:var(--text-secondary);border-color:var(--border);">
                    <i class="ph ph-x-bold"></i>
                </button>
            </div>
            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf
                <div class="modal-card-body">

                    <div class="form-field">
                        <label><i class="ph ph-user text-primary"></i> {{ __('Teacher') }}</label>
                        <select name="teacher_id" class="form-control" required>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->department }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-field mb-0">
                                <label><i class="ph ph-calendar text-primary"></i> {{ __('Day') }}</label>
                                <select name="day_of_week" class="form-control" required>
                                    <option value="1">{{ __('Monday') }}</option>
                                    <option value="2">{{ __('Tuesday') }}</option>
                                    <option value="3">{{ __('Wednesday') }}</option>
                                    <option value="4">{{ __('Thursday') }}</option>
                                    <option value="5">{{ __('Friday') }}</option>
                                    <option value="6">{{ __('Saturday') }}</option>
                                    <option value="7">{{ __('Sunday') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field mb-0">
                                <label><i class="ph ph-door text-primary"></i> {{ __('Room') }}</label>
                                <input type="text" name="room_number" class="form-control" placeholder="{{ __('e.g. 204') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-6">
                            <div class="form-field mb-0">
                                <label><i class="ph ph-clock text-primary"></i> {{ __('Start Time') }}</label>
                                <input type="time" name="start_time" class="form-control" value="08:00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field mb-0">
                                <label><i class="ph ph-clock text-primary"></i> {{ __('End Time') }}</label>
                                <input type="time" name="end_time" class="form-control" value="11:00" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-field mt-3 mb-0">
                        <label><i class="ph ph-book-open text-primary"></i> {{ __('Subject / Class Name') }}</label>
                        <input type="text" name="subject_name" class="form-control"
                               placeholder="{{ __('e.g. Web Development 101') }}" required>
                    </div>

                </div>
                <div class="modal-card-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:.9rem;padding:.6rem 1.5rem;font-weight:700;">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"
                            style="border-radius:.9rem;padding:.6rem 1.5rem;font-weight:800;">
                        <i class="ph ph-floppy-disk me-1"></i> {{ __('Save Slot') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchDay(day, btn) {
    document.querySelectorAll('.day-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('.sched-card');
    let visible = 0;
    cards.forEach(c => {
        const show = day === 'all' || c.dataset.day == day;
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const noMsg = document.getElementById('noResultsMsg');
    if (noMsg) noMsg.style.display = (visible === 0 && cards.length > 0) ? 'block' : 'none';
}
</script>
@endsection
