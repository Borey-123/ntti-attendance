@extends('layouts.app')

@section('title', __('Academic Calendar'))

@push('styles')
<style>
    .academic-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .academic-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 1.5rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.3s ease;
    }
    .academic-stat-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-icon-box {
        width: 56px;
        height: 56px;
        border-radius: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }
    .stat-meta .label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .stat-meta .value {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
        margin-top: 2px;
    }

    /* Period Cards */
    .periods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }
    .period-card {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--border);
        border-radius: 1.25rem;
        padding: 1.25rem;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .period-card:hover {
        border-color: rgba(var(--primary-rgb), 0.4);
        background: rgba(var(--primary-rgb), 0.02);
        transform: translateY(-2px);
    }
    .period-accent-bar {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
    }

    /* Timeline Bar */
    .timeline-track {
        height: 36px;
        background: rgba(255,255,255,0.04);
        border-radius: 0.75rem;
        overflow: hidden;
        display: flex;
        position: relative;
        border: 1px solid var(--border);
    }
    .timeline-segment {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        padding: 0 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: filter 0.2s;
        cursor: pointer;
    }
    .timeline-segment:hover { filter: brightness(1.2); }
    .timeline-today-marker {
        position: absolute;
        top: -6px;
        bottom: -6px;
        width: 3px;
        background: #ef4444;
        box-shadow: 0 0 10px #ef4444;
        z-index: 10;
        pointer-events: none;
    }
    .timeline-today-label {
        position: absolute;
        top: -24px;
        transform: translateX(-50%);
        background: #ef4444;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 4px;
        pointer-events: none;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<div class="animate-fade-up">

    {{-- ── Header ── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom:0.35rem; font-weight:800; display:flex; align-items:center; gap:0.75rem;">
                <span style="width:46px; height:46px; background:linear-gradient(135deg,rgba(var(--primary-rgb),0.2),rgba(99,102,241,0.2)); border:1px solid rgba(var(--primary-rgb),0.3); border-radius:1rem; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">🎓</span>
                <span>{{ __('Academic Calendar') }}</span>
            </h1>
            <p style="color:var(--text-secondary); margin:0; font-size:0.9rem;">
                {{ __('Manage academic years, semesters, examination periods, and campus timeline.') }}
            </p>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <button onclick="openAddYearModal()" class="btn btn-primary" style="border-radius:1rem; padding:0.75rem 1.4rem; font-weight:800; display:inline-flex; align-items:center; gap:0.5rem; box-shadow:0 8px 20px rgba(var(--primary-rgb),0.25);">
                <i class="ph ph-plus-circle" style="font-size:1.2rem;"></i>
                <span>{{ __('New Academic Year') }}</span>
            </button>
        </div>
    </div>

    @php
        $activePeriod = \App\Models\AcademicPeriod::active()->with('academicYear')->first();
        $currentYear = $years->firstWhere('is_current', true);
        $totalYears = $years->count();
        $totalPeriods = $years->sum(fn($y) => $y->periods->count());
    @endphp

    {{-- ── Metric Stat Cards ── --}}
    <div class="academic-stat-grid">
        <div class="academic-stat-card">
            <div class="stat-icon-box" style="background:rgba(var(--primary-rgb),0.12); color:var(--primary);">
                <i class="ph ph-calendar"></i>
            </div>
            <div class="stat-meta">
                <div class="label">{{ __('Academic Years') }}</div>
                <div class="value">{{ $totalYears }}</div>
            </div>
        </div>

        <div class="academic-stat-card">
            <div class="stat-icon-box" style="background:rgba(16,185,129,0.12); color:#10b981;">
                <i class="ph ph-star"></i>
            </div>
            <div class="stat-meta">
                <div class="label">{{ __('Current Academic Year') }}</div>
                <div class="value" style="font-size:1.25rem; color:#10b981;">
                    {{ $currentYear ? ($currentYear->name_kh ?: $currentYear->name) : __('None') }}
                </div>
            </div>
        </div>

        <div class="academic-stat-card">
            <div class="stat-icon-box" style="background:rgba(99,102,241,0.12); color:#818cf8;">
                <i class="ph ph-clock-countdown"></i>
            </div>
            <div class="stat-meta">
                <div class="label">{{ __('Active Period') }}</div>
                <div class="value" style="font-size:1.25rem; color:#818cf8;">
                    {{ $activePeriod ? $activePeriod->display_name : __('No Active Period') }}
                </div>
            </div>
        </div>

        <div class="academic-stat-card">
            <div class="stat-icon-box" style="background:rgba(245,158,11,0.12); color:#f59e0b;">
                <i class="ph ph-briefcase"></i>
            </div>
            <div class="stat-meta">
                <div class="label">{{ __('Total Working Days') }}</div>
                <div class="value" style="color:#f59e0b;">
                    {{ $activePeriod ? $activePeriod->working_days_count : $years->sum(fn($y) => $y->periods->sum('working_days_count')) }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Active Period Progress Banner ── --}}
    @if($activePeriod)
    <div class="card" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(var(--primary-rgb),0.06)); border:1px solid rgba(99,102,241,0.3); border-radius:1.5rem; padding:1.5rem; margin-bottom:2rem;">
        <div style="display:flex; align-items:center; gap:1.25rem; flex-wrap:wrap;">
            <div style="width:54px; height:54px; border-radius:1.25rem; background:rgba(99,102,241,0.2); color:#818cf8; display:flex; align-items:center; justify-content:center; font-size:1.8rem; flex-shrink:0;">
                <i class="ph ph-bookmark-simple"></i>
            </div>
            <div style="flex:1; min-width:250px;">
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <span style="font-size:0.75rem; font-weight:800; text-transform:uppercase; background:rgba(99,102,241,0.25); color:#a5b4fc; padding:0.2rem 0.6rem; border-radius:999px;">
                        ● {{ __('Active Period') }}
                    </span>
                    <strong style="font-size:1.15rem; color:var(--text-primary);">
                        {{ $activePeriod->display_name }} ({{ $activePeriod->academicYear->name }})
                    </strong>
                </div>
                <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.35rem;">
                    📅 {{ $activePeriod->start_date->format('d M Y') }} → {{ $activePeriod->end_date->format('d M Y') }}
                    &bull; <span style="color:#a5b4fc; font-weight:700;">{{ $activePeriod->duration_days }} {{ __('Days Total') }}</span>
                    &bull; <span style="color:var(--primary); font-weight:700;">{{ $activePeriod->working_days_count }} {{ __('Working Days') }}</span>
                    &bull; <span style="color:var(--text-muted);">{{ $activePeriod->days_remaining }} {{ __('Days Left') }}</span>
                </div>
            </div>
            <div style="min-width:180px; text-align:right;">
                <div style="display:flex; justify-content:space-between; font-size:0.8rem; font-weight:700; margin-bottom:0.4rem;">
                    <span style="color:var(--text-secondary);">{{ __('Progress') }}</span>
                    <span style="color:#a5b4fc;">{{ $activePeriod->progress_percent }}%</span>
                </div>
                <div style="width:100%; height:10px; background:rgba(255,255,255,0.08); border-radius:999px; overflow:hidden;">
                    <div style="width:{{ $activePeriod->progress_percent }}%; height:100%; background:linear-gradient(90deg, #6366f1, var(--primary)); border-radius:999px; transition:width 0.5s ease;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Academic Years List ── --}}
    @forelse($years as $year)
    <div class="card" style="margin-bottom:2rem; border-radius:1.75rem; border:1px solid var(--border); overflow:hidden;">
        
        {{-- Year Top Banner --}}
        <div style="padding:1.5rem 1.75rem; display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.02); border-bottom:1px solid var(--border); flex-wrap:wrap; gap:1rem;">
            <div>
                <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                    <h2 style="margin:0; font-size:1.35rem; font-weight:800; color:var(--text-primary);">
                        {{ $year->name }}
                    </h2>
                    @if($year->name_kh)
                        <span style="font-size:1.05rem; font-weight:700; color:var(--primary);">
                            ({{ $year->name_kh }})
                        </span>
                    @endif
                    @if($year->is_current)
                        <span style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:0.25rem 0.75rem; border-radius:999px; font-size:0.75rem; font-weight:800; display:inline-flex; align-items:center; gap:0.35rem;">
                            <i class="ph ph-star"></i> {{ __('CURRENT') }}
                        </span>
                    @endif
                    <span style="background:rgba(
                        {{ $year->status === 'active' ? '16,185,129' : ($year->status === 'completed' ? '99,102,241' : '245,158,11') }},0.12);
                        color:{{ $year->status === 'active' ? '#10b981' : ($year->status === 'completed' ? '#818cf8' : '#f59e0b') }};
                        border:1px solid rgba({{ $year->status === 'active' ? '16,185,129' : ($year->status === 'completed' ? '99,102,241' : '245,158,11') }},0.25);
                        padding:0.25rem 0.65rem; border-radius:999px; font-size:0.72rem; font-weight:700; text-transform:uppercase;">
                        {{ $year->status }}
                    </span>
                </div>
                <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.4rem;">
                    📅 {{ $year->start_date->format('d M Y') }} → {{ $year->end_date->format('d M Y') }}
                    &bull; <span>{{ $year->periods->count() }} {{ __('Academic Periods') }}</span>
                </div>
            </div>

            {{-- Year Actions --}}
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                @if(!$year->is_current)
                <button onclick="setCurrentYear({{ $year->id }}, '{{ addslashes($year->name) }}')" class="btn btn-secondary" style="border-radius:0.75rem; font-size:0.82rem; padding:0.55rem 0.95rem; font-weight:700;">
                    <i class="ph ph-star" style="color:#f59e0b;"></i> {{ __('Set as Current Year') }}
                </button>
                @endif
                <button onclick="openAddPeriodModal({{ $year->id }}, '{{ addslashes($year->name) }}')" class="btn btn-secondary" style="border-radius:0.75rem; font-size:0.82rem; padding:0.55rem 0.95rem; font-weight:700;">
                    <i class="ph ph-plus" style="color:var(--primary);"></i> {{ __('Add Period') }}
                </button>
                <button onclick="editYear({{ $year->id }}, {{ json_encode($year) }})" class="btn btn-secondary" style="border-radius:0.75rem; padding:0.55rem 0.8rem;" title="{{ __('Edit') }}">
                    <i class="ph ph-pencil-simple"></i>
                </button>
                <button onclick="deleteYear({{ $year->id }}, '{{ addslashes($year->name) }}')" class="btn btn-secondary" style="border-radius:0.75rem; padding:0.55rem 0.8rem; color:var(--danger); border-color:rgba(239,68,68,0.3);" title="{{ __('Remove') }}">
                    <i class="ph ph-trash"></i>
                </button>
            </div>
        </div>

        {{-- Visual Horizontal Timeline Bar --}}
        <div style="padding:1.75rem;">
            @if($year->periods->count() > 0)
                <div style="margin-bottom:2rem;">
                    <div style="font-size:0.75rem; font-weight:800; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem;">
                        <i class="ph ph-chart-bar-horizontal"></i>
                        <span>{{ __('Visual Timeline') }}</span>
                    </div>

                    @php
                        $yearStart = $year->start_date;
                        $yearEnd = $year->end_date;
                        $totalDays = max(1, $yearStart->diffInDays($yearEnd));
                        $now = now();
                        $todayPos = null;
                        if ($now->between($yearStart, $yearEnd)) {
                            $todayPos = ($yearStart->diffInDays($now) / $totalDays) * 100;
                        }
                    @endphp

                    <div style="position:relative; padding-top:24px;">
                        @if($todayPos !== null)
                            <div class="timeline-today-label" style="left:{{ $todayPos }}%;">{{ __('TODAY') }}</div>
                        @endif

                        <div class="timeline-track">
                            @if($todayPos !== null)
                                <div class="timeline-today-marker" style="left:{{ $todayPos }}%;"></div>
                            @endif

                            @foreach($year->periods->sortBy('start_date') as $p)
                                @php
                                    $pStart = max($yearStart, $p->start_date);
                                    $pEnd = min($yearEnd, $p->end_date);
                                    $segDays = max(1, $pStart->diffInDays($pEnd));
                                    $widthPercent = ($segDays / $totalDays) * 100;
                                    $color = $p->color ?: '#00d4a0';
                                @endphp
                                <div class="timeline-segment"
                                     style="width:{{ $widthPercent }}%; background:{{ $color }};"
                                     title="{{ $p->display_name }} ({{ $p->start_date->format('d M') }} - {{ $p->end_date->format('d M') }})"
                                     onclick="editPeriod({{ $p->id }}, {{ json_encode($p) }})">
                                    {{ $p->display_name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Period Cards List --}}
                <div class="periods-grid">
                    @foreach($year->periods->sortBy('start_date') as $period)
                    @php
                        $pColor = $period->color ?: '#00d4a0';
                    @endphp
                    <div class="period-card">
                        <div class="period-accent-bar" style="background:{{ $pColor }};"></div>
                        
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                            <div>
                                <h4 style="margin:0; font-size:1.05rem; font-weight:800; color:var(--text-primary);">
                                    {{ $period->name }}
                                </h4>
                                @if($period->name_kh)
                                    <div style="font-size:0.85rem; font-weight:700; color:var(--primary); margin-top:2px;">
                                        {{ $period->name_kh }}
                                    </div>
                                @endif
                            </div>
                            <span style="background:rgba(255,255,255,0.06); border:1px solid var(--border); padding:0.2rem 0.55rem; border-radius:0.5rem; font-size:0.72rem; font-weight:700; text-transform:uppercase; color:{{ $pColor }};">
                                {{ $period->type_label }}
                            </span>
                        </div>

                        <div style="font-size:0.82rem; color:var(--text-secondary); margin-bottom:0.75rem; line-height:1.6;">
                            <div>📅 {{ $period->start_date->format('d M Y') }} → {{ $period->end_date->format('d M Y') }}</div>
                            <div style="display:flex; gap:0.75rem; margin-top:0.25rem;">
                                <span><strong style="color:var(--text-primary);">{{ $period->duration_days }}</strong> {{ __('Days Total') }}</span>
                                &bull;
                                <span><strong style="color:#10b981;">{{ $period->working_days_count }}</strong> {{ __('Working Days') }}</span>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div style="margin-bottom:1rem;">
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:700; margin-bottom:0.25rem;">
                                <span style="color:var(--text-muted);">{{ __('Progress') }}</span>
                                <span style="color:{{ $pColor }};">{{ $period->progress_percent }}%</span>
                            </div>
                            <div style="width:100%; height:6px; background:rgba(255,255,255,0.06); border-radius:999px; overflow:hidden;">
                                <div style="width:{{ $period->progress_percent }}%; height:100%; background:{{ $pColor }}; border-radius:999px;"></div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:0.75rem;">
                            <span style="font-size:0.75rem; color:{{ $period->is_attendance_required ? '#10b981' : 'var(--text-muted)' }};">
                                <i class="ph ph-check-circle"></i> {{ $period->is_attendance_required ? __('Attendance Required') : __('Break / Off') }}
                            </span>
                            <div style="display:flex; gap:0.35rem;">
                                <button onclick="editPeriod({{ $period->id }}, {{ json_encode($period) }})" class="btn btn-secondary" style="padding:0.35rem 0.65rem; border-radius:0.5rem; font-size:0.75rem;" title="{{ __('Edit') }}">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button onclick="deletePeriod({{ $period->id }}, '{{ addslashes($period->display_name) }}')" class="btn btn-secondary" style="padding:0.35rem 0.65rem; border-radius:0.5rem; font-size:0.75rem; color:var(--danger); border-color:rgba(239,68,68,0.25);" title="{{ __('Remove') }}">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center; padding:3rem 1rem; border:2px dashed var(--border); border-radius:1.5rem;">
                    <i class="ph ph-calendar-x" style="font-size:3rem; color:var(--text-muted); opacity:0.4; margin-bottom:0.75rem; display:block;"></i>
                    <h4 style="color:var(--text-secondary); margin-bottom:0.25rem;">{{ __('No periods added yet') }}</h4>
                    <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1rem;">
                        {{ __('Add Semester 1, Semester 2, Exam periods or Holidays for this year.') }}
                    </p>
                    <button onclick="openAddPeriodModal({{ $year->id }}, '{{ addslashes($year->name) }}')" class="btn btn-primary" style="border-radius:0.75rem; font-weight:700;">
                        <i class="ph ph-plus-circle"></i> {{ __('Add First Period') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
    @empty
    <div class="card" style="text-align:center; padding:4rem 2rem; border-radius:2rem;">
        <i class="ph ph-graduation-cap" style="font-size:4rem; color:var(--primary); opacity:0.5; margin-bottom:1rem; display:block;"></i>
        <h3 style="color:var(--text-primary); font-weight:800; margin-bottom:0.5rem;">{{ __('No Academic Years Created') }}</h3>
        <p style="color:var(--text-secondary); font-size:0.95rem; margin-bottom:1.5rem; max-width:480px; margin-left:auto; margin-right:auto;">
            {{ __('Get started by creating your institute academic year (e.g. 2026-2027) with start and end dates.') }}
        </p>
        <button onclick="openAddYearModal()" class="btn btn-primary" style="border-radius:1rem; padding:0.85rem 1.75rem; font-weight:800;">
            <i class="ph ph-plus-circle"></i> {{ __('Create Academic Year') }}
        </button>
    </div>
    @endforelse

</div>

{{-- ── Add/Edit Year Modal ── --}}
<div class="modal-overlay" id="yearModal" style="z-index: 1000001;">
    <div class="modal-content" style="max-width: 500px; border-radius: 1.75rem;">
        <div class="modal-header">
            <h3 id="yearModalTitle" style="font-weight: 800; color: var(--text-primary);">{{ __('New Academic Year') }}</h3>
            <button class="modal-close" onclick="closeModal('yearModal')">&times;</button>
        </div>
        <form id="yearForm">
            <input type="hidden" id="yearId">
            <div class="form-group">
                <label>{{ __('Academic Year Name') }} (English) <span style="color:var(--danger)">*</span></label>
                <input type="text" id="yearName" class="form-control" placeholder="e.g. 2026-2027" required>
            </div>
            <div class="form-group">
                <label>{{ __('Academic Year Name') }} (Khmer)</label>
                <input type="text" id="yearNameKh" class="form-control" placeholder="e.g. ឆ្នាំសិក្សា ២០២៦-២០២៧">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>{{ __('Start Date') }} <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="yearStart" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>{{ __('End Date') }} <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="yearEnd" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>{{ __('Status') }}</label>
                <select id="yearStatus" class="form-control">
                    <option value="upcoming">{{ __('Upcoming') }}</option>
                    <option value="active" selected>{{ __('Active') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.6rem; cursor:pointer; font-weight:700;">
                    <input type="checkbox" id="yearIsCurrent" value="1" style="width:18px; height:18px; accent-color:var(--primary);">
                    <span><i class="ph ph-star" style="color:#f59e0b;"></i> {{ __('Set as Current Academic Year') }}</span>
                </label>
            </div>
            <div class="d-flex justify-between align-center mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeModal('yearModal')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary" style="width:auto; font-weight:800;">
                    <i class="ph ph-check-circle"></i> {{ __('Save Academic Year') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Add/Edit Period Modal ── --}}
<div class="modal-overlay" id="periodModal" style="z-index: 1000001;">
    <div class="modal-content" style="max-width: 520px; border-radius: 1.75rem;">
        <div class="modal-header">
            <h3 id="periodModalTitle" style="font-weight: 800; color: var(--text-primary);">{{ __('Add Period') }}</h3>
            <button class="modal-close" onclick="closeModal('periodModal')">&times;</button>
        </div>
        <form id="periodForm">
            <input type="hidden" id="periodId">
            <input type="hidden" id="periodYearId">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>{{ __('Period Type') }} <span style="color:var(--danger)">*</span></label>
                    <select id="periodType" class="form-control" onchange="updatePeriodColor()" required>
                        <option value="semester">{{ __('Semester') }}</option>
                        <option value="exam">{{ __('Exam Period') }}</option>
                        <option value="break">{{ __('Semester Break') }}</option>
                        <option value="holiday">{{ __('Holiday') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ __('Color') }}</label>
                    <input type="color" id="periodColor" class="form-control" value="#00d4a0" style="height:44px; padding:4px;">
                </div>
            </div>

            <div class="form-group">
                <label>{{ __('Name') }} (English) <span style="color:var(--danger)">*</span></label>
                <input type="text" id="periodName" class="form-control" placeholder="e.g. Semester 1" required>
            </div>
            <div class="form-group">
                <label>{{ __('Name') }} (Khmer)</label>
                <input type="text" id="periodNameKh" class="form-control" placeholder="e.g. ឆមាសទី១">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>{{ __('Start Date') }} <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="periodStart" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>{{ __('End Date') }} <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="periodEnd" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.6rem; cursor:pointer; font-weight:700;">
                    <input type="checkbox" id="periodAttendance" value="1" checked style="width:18px; height:18px; accent-color:var(--primary);">
                    <span><i class="ph ph-check-square" style="color:var(--primary);"></i> {{ __('Attendance is required during this period') }}</span>
                </label>
            </div>

            <div class="form-group">
                <label>{{ __('Notes') }}</label>
                <textarea id="periodNotes" class="form-control" rows="2" placeholder="e.g. Regular classes for degree programs"></textarea>
            </div>

            <div class="d-flex justify-between align-center mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeModal('periodModal')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary" style="width:auto; font-weight:800;">
                    <i class="ph ph-check-circle"></i> {{ __('Save Period') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const typeColors = {
    semester: '#00d4a0',
    exam: '#ef4444',
    break: '#f59e0b',
    holiday: '#ec4899',
    other: '#8b5cf6',
};

function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

// ── Academic Year ─────────────────────────────────────────
function openAddYearModal() {
    document.getElementById('yearId').value = '';
    document.getElementById('yearModalTitle').textContent = '{{ __("New Academic Year") }}';
    document.getElementById('yearName').value = '';
    document.getElementById('yearNameKh').value = '';
    document.getElementById('yearStart').value = '';
    document.getElementById('yearEnd').value = '';
    document.getElementById('yearStatus').value = 'active';
    document.getElementById('yearIsCurrent').checked = false;
    openModal('yearModal');
}

function editYear(id, year) {
    document.getElementById('yearId').value = id;
    document.getElementById('yearModalTitle').textContent = '{{ __("Edit Academic Year") }}';
    document.getElementById('yearName').value = year.name;
    document.getElementById('yearNameKh').value = year.name_kh || '';
    document.getElementById('yearStart').value = year.start_date;
    document.getElementById('yearEnd').value = year.end_date;
    document.getElementById('yearStatus').value = year.status;
    document.getElementById('yearIsCurrent').checked = !!year.is_current;
    openModal('yearModal');
}

document.getElementById('yearForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('yearId').value;
    const url = id ? `/academic-calendar/years/${id}` : '/academic-calendar/years';
    const method = id ? 'PUT' : 'POST';

    const payload = {
        name: document.getElementById('yearName').value,
        name_kh: document.getElementById('yearNameKh').value,
        start_date: document.getElementById('yearStart').value,
        end_date: document.getElementById('yearEnd').value,
        status: document.getElementById('yearStatus').value,
        is_current: document.getElementById('yearIsCurrent').checked ? 1 : 0,
    };

    try {
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            closeModal('yearModal');
            if (window.showToast) window.showToast('{{ __("Academic year saved successfully!") }}', 'success');
            setTimeout(() => location.reload(), 400);
        } else {
            alert(data.message || 'Error');
        }
    } catch(err) { alert('Error: ' + err.message); }
});

async function deleteYear(id, name) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមលុបឆ្នាំសិក្សា "' + name + '" និងដំណាក់កាលទាំងអស់ដែរឬទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានឡើយ។',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'danger'
    });
    if (!ok) return;

    try {
        const res = await fetch(`/academic-calendar/years/${id}`, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast('{{ __("Academic year deleted") }}', 'info');
            setTimeout(() => location.reload(), 400);
        } else {
            alert(data.message || 'Error');
        }
    } catch (err) { alert('Error: ' + err.message); }
}

async function setCurrentYear(id, name) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមជ្រើសរើសឆ្នាំសិក្សា "' + name + '" ជាឆ្នាំសកម្មបច្ចុប្បន្នដែរឬទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'success'
    });
    if (!ok) return;

    try {
        const res = await fetch(`/academic-calendar/years/${id}/set-current`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast('{{ __("Current academic year updated!") }}', 'success');
            setTimeout(() => location.reload(), 400);
        }
    } catch (err) { alert('Error: ' + err.message); }
}

// ── Academic Period ──────────────────────────────────────
function updatePeriodColor() {
    const type = document.getElementById('periodType').value;
    if (typeColors[type]) document.getElementById('periodColor').value = typeColors[type];
}

function openAddPeriodModal(yearId, yearName) {
    document.getElementById('periodId').value = '';
    document.getElementById('periodYearId').value = yearId;
    document.getElementById('periodModalTitle').textContent = `{{ __("Add Period") }} — ${yearName}`;
    document.getElementById('periodName').value = '';
    document.getElementById('periodNameKh').value = '';
    document.getElementById('periodType').value = 'semester';
    document.getElementById('periodStart').value = '';
    document.getElementById('periodEnd').value = '';
    document.getElementById('periodColor').value = '#00d4a0';
    document.getElementById('periodAttendance').checked = true;
    document.getElementById('periodNotes').value = '';
    openModal('periodModal');
}

function editPeriod(id, period) {
    document.getElementById('periodId').value = id;
    document.getElementById('periodYearId').value = period.academic_year_id;
    document.getElementById('periodModalTitle').textContent = '{{ __("Edit Period") }}';
    document.getElementById('periodName').value = period.name;
    document.getElementById('periodNameKh').value = period.name_kh || '';
    document.getElementById('periodType').value = period.type;
    document.getElementById('periodStart').value = period.start_date;
    document.getElementById('periodEnd').value = period.end_date;
    document.getElementById('periodColor').value = period.color || '#00d4a0';
    document.getElementById('periodAttendance').checked = !!period.is_attendance_required;
    document.getElementById('periodNotes').value = period.notes || '';
    openModal('periodModal');
}

document.getElementById('periodForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('periodId').value;
    const url = id ? `/academic-calendar/periods/${id}` : '/academic-calendar/periods';
    const method = id ? 'PUT' : 'POST';

    const payload = {
        academic_year_id: document.getElementById('periodYearId').value,
        name: document.getElementById('periodName').value,
        name_kh: document.getElementById('periodNameKh').value,
        type: document.getElementById('periodType').value,
        start_date: document.getElementById('periodStart').value,
        end_date: document.getElementById('periodEnd').value,
        color: document.getElementById('periodColor').value,
        is_attendance_required: document.getElementById('periodAttendance').checked ? 1 : 0,
        notes: document.getElementById('periodNotes').value,
    };

    try {
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            closeModal('periodModal');
            if (window.showToast) window.showToast('{{ __("Period saved successfully!") }}', 'success');
            setTimeout(() => location.reload(), 400);
        } else {
            alert(data.message || 'Error');
        }
    } catch(err) { alert('Error: ' + err.message); }
});

async function deletePeriod(id, name) {
    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមលុបដំណាក់កាលសិក្សា "' + name + '" នេះដែរឬទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'danger'
    });
    if (!ok) return;

    try {
        const res = await fetch(`/academic-calendar/periods/${id}`, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast('{{ __("Period deleted") }}', 'info');
            setTimeout(() => location.reload(), 400);
        } else {
            alert(data.message || 'Error');
        }
    } catch(err) { alert('Error: ' + err.message); }
}
</script>
@endpush
@endsection
