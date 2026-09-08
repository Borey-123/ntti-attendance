@extends('layouts.app')

@section('title', __('Academic Calendar'))

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-size:1.75rem; font-weight:900; margin:0; display:flex; align-items:center; gap:0.75rem;">
            <span style="width:42px; height:42px; background:linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:0.75rem; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">🎓</span>
            {{ __('Academic Calendar') }}
        </h1>
        <p style="color:var(--text-secondary); margin:0.25rem 0 0; font-size:0.9rem;">
            {{ __('Manage academic years, semesters and examination periods') }}
        </p>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <button onclick="openAddYearModal()" class="btn btn-primary" style="gap:0.5rem;">
            <i class="ph ph-plus-circle"></i> {{ __('New Academic Year') }}
        </button>
    </div>
</div>

{{-- Current Period Banner --}}
@php
    $activePeriod = \App\Models\AcademicPeriod::active()->with('academicYear')->first();
@endphp
@if($activePeriod)
<div style="background:linear-gradient(135deg,rgba(99,102,241,0.15),rgba(139,92,246,0.1)); border:1px solid rgba(99,102,241,0.3); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:2rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
    <span style="font-size:2rem;">📌</span>
    <div style="flex:1;">
        <div style="font-weight:800; font-size:1rem; color:#a5b4fc;">{{ __('Current Period') }}: {{ $activePeriod->display_name }}</div>
        <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.25rem;">
            {{ $activePeriod->academicYear->name }} &bull;
            {{ $activePeriod->start_date->format('d M Y') }} → {{ $activePeriod->end_date->format('d M Y') }} &bull;
            <strong style="color:#a5b4fc;">{{ $activePeriod->duration_days }} {{ __('days') }}</strong> &bull;
            {{ __('Progress') }}: {{ $activePeriod->progress_percent }}%
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.25rem;">{{ __('Progress') }}</div>
        <div style="width:160px; height:8px; background:rgba(255,255,255,0.1); border-radius:99px; overflow:hidden;">
            <div style="width:{{ $activePeriod->progress_percent }}%; height:100%; background:linear-gradient(90deg,#6366f1,#8b5cf6); border-radius:99px; transition:width 0.5s;"></div>
        </div>
        <div style="font-size:0.8rem; font-weight:700; color:#a5b4fc; margin-top:0.25rem;">{{ $activePeriod->progress_percent }}%</div>
    </div>
</div>
@endif

{{-- Academic Years Timeline --}}
@forelse($years as $year)
<div class="card" style="margin-bottom:1.5rem; border-radius:1.25rem; overflow:hidden;">
    {{-- Year Header --}}
    <div style="padding:1.25rem 1.5rem; display:flex; align-items:center; gap:1rem; background:rgba(255,255,255,0.03); border-bottom:1px solid var(--border); flex-wrap:wrap;">
        <div style="flex:1;">
            <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                <h3 style="margin:0; font-size:1.1rem; font-weight:800;">{{ $year->name }}</h3>
                @if($year->name_kh)
                    <span style="font-size:0.9rem; color:var(--text-secondary);">{{ $year->name_kh }}</span>
                @endif
                @if($year->is_current)
                    <span style="background:rgba(0,212,160,0.15); color:#00d4a0; border:1px solid rgba(0,212,160,0.3); padding:0.2rem 0.6rem; border-radius:99px; font-size:0.7rem; font-weight:800;">● {{ __('CURRENT') }}</span>
                @endif
                <span style="background:rgba(
                    {{ $year->status === 'active' ? '0,212,160' : ($year->status === 'completed' ? '99,102,241' : '245,158,11') }},0.15);
                    color:{{ $year->status === 'active' ? '#00d4a0' : ($year->status === 'completed' ? '#a5b4fc' : '#fbbf24') }};
                    border:1px solid rgba({{ $year->status === 'active' ? '0,212,160' : ($year->status === 'completed' ? '99,102,241' : '245,158,11') }},0.3);
                    padding:0.2rem 0.6rem; border-radius:99px; font-size:0.7rem; font-weight:700; text-transform:uppercase;">
                    {{ $year->status }}
                </span>
            </div>
            <div style="font-size:0.82rem; color:var(--text-muted); margin-top:0.3rem;">
                📅 {{ $year->start_date->format('d M Y') }} → {{ $year->end_date->format('d M Y') }}
                &bull; {{ $year->periods->count() }} {{ __('periods') }}
            </div>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
            @if(!$year->is_current)
            <button onclick="setCurrentYear({{ $year->id }})" class="btn btn-secondary" style="font-size:0.78rem; padding:0.4rem 0.8rem;" title="{{ __('Set as current') }}">
                <i class="ph ph-star"></i> {{ __('Set Current') }}
            </button>
            @endif
            <button onclick="openAddPeriodModal({{ $year->id }}, '{{ addslashes($year->name) }}')" class="btn btn-secondary" style="font-size:0.78rem; padding:0.4rem 0.8rem;">
                <i class="ph ph-plus"></i> {{ __('Add Period') }}
            </button>
            <button onclick="editYear({{ $year->id }}, {{ json_encode($year) }})" class="btn btn-secondary" style="font-size:0.78rem; padding:0.4rem 0.8rem;">
                <i class="ph ph-pencil"></i>
            </button>
            <button onclick="deleteYear({{ $year->id }}, '{{ addslashes($year->name) }}')" class="btn btn-secondary" style="font-size:0.78rem; padding:0.4rem 0.8rem; color:var(--danger); border-color:rgba(239,68,68,0.3);">
                <i class="ph ph-trash"></i>
            </button>
        </div>
    </div>

    {{-- Timeline Bar --}}
    <div style="padding:1.5rem;">
        @if($year->periods->count() > 0)
            {{-- Visual Timeline --}}
            <div style="position:relative; margin-bottom:1.5rem;">
                <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.75rem;">{{ __('Timeline') }}</div>
                @php
                    $yearStart = $year->start_date->timestamp;
                    $yearEnd   = $year->end_date->timestamp;
                    $yearSpan  = max(1, $yearEnd - $yearStart);
                @endphp
                <div style="position:relative; height:40px; background:rgba(255,255,255,0.04); border-radius:0.5rem; overflow:hidden; border:1px solid var(--border);">
                    @foreach($year->periods as $period)
                        @php
                            $left  = max(0, min(100, (($period->start_date->timestamp - $yearStart) / $yearSpan) * 100));
                            $width = max(0.5, min(100 - $left, (($period->end_date->timestamp - $period->start_date->timestamp) / $yearSpan) * 100));
                            $colors = \App\Models\AcademicPeriod::TYPE_COLORS;
                            $color  = $period->color ?? ($colors[$period->type] ?? '#94a3b8');
                        @endphp
                        <div title="{{ $period->name }} ({{ $period->start_date->format('d M') }} - {{ $period->end_date->format('d M') }})"
                             style="position:absolute; top:4px; bottom:4px; left:{{ $left }}%; width:{{ $width }}%; background:{{ $color }}33; border:1px solid {{ $color }}66; border-radius:4px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; padding:0 4px; overflow:hidden;"
                             onmouseover="this.style.background='{{ $color }}55'"
                             onmouseout="this.style.background='{{ $color }}33'">
                            <span style="font-size:0.65rem; font-weight:700; color:{{ $color }}; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $period->name }}</span>
                        </div>
                    @endforeach

                    {{-- Today marker --}}
                    @php
                        $todayLeft = (now()->timestamp - $yearStart) / $yearSpan * 100;
                    @endphp
                    @if($todayLeft >= 0 && $todayLeft <= 100)
                    <div style="position:absolute; top:0; bottom:0; left:{{ $todayLeft }}%; width:2px; background:var(--primary); z-index:5; box-shadow:0 0 8px var(--primary);">
                        <div style="position:absolute; top:-16px; left:50%; transform:translateX(-50%); font-size:0.6rem; background:var(--primary); color:#000; padding:1px 4px; border-radius:3px; white-space:nowrap; font-weight:700;">TODAY</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Period Cards Grid --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:0.75rem;">
                @foreach($year->periods->sortBy('start_date') as $period)
                    @php
                        $colors = \App\Models\AcademicPeriod::TYPE_COLORS;
                        $color  = $period->color ?? ($colors[$period->type] ?? '#94a3b8');
                        $statusLabel = $period->status_label;
                    @endphp
                    <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border); border-left:3px solid {{ $color }}; border-radius:0.75rem; padding:1rem; position:relative;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                            <div>
                                <div style="font-weight:700; font-size:0.9rem; color:{{ $color }};">{{ $period->name }}</div>
                                @if($period->name_kh)
                                    <div style="font-size:0.78rem; color:var(--text-secondary);">{{ $period->name_kh }}</div>
                                @endif
                            </div>
                            <div style="display:flex; gap:0.35rem;">
                                <button onclick="editPeriod({{ $period->id }}, {{ json_encode($period) }})" style="background:none; border:1px solid var(--border); color:var(--text-secondary); width:26px; height:26px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                <button onclick="deletePeriod({{ $period->id }}, '{{ addslashes($period->name) }}')" style="background:none; border:1px solid rgba(239,68,68,0.2); color:var(--danger); width:26px; height:26px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.5rem;">
                            📅 {{ $period->start_date->format('d M Y') }} → {{ $period->end_date->format('d M Y') }}
                        </div>
                        <div style="display:flex; gap:0.4rem; flex-wrap:wrap; align-items:center;">
                            <span style="font-size:0.68rem; font-weight:700; background:{{ $color }}22; color:{{ $color }}; padding:0.15rem 0.5rem; border-radius:99px; text-transform:uppercase;">{{ $period->type }}</span>
                            <span style="font-size:0.68rem; color:var(--text-muted);">{{ $period->duration_days }} days</span>
                            @if($statusLabel === 'active')
                                <span style="font-size:0.68rem; font-weight:700; background:rgba(0,212,160,0.15); color:#00d4a0; padding:0.15rem 0.5rem; border-radius:99px;">● Active</span>
                            @elseif($statusLabel === 'completed')
                                <span style="font-size:0.68rem; color:var(--text-muted);">✓ Completed</span>
                            @else
                                <span style="font-size:0.68rem; color:#fbbf24;">⏳ Upcoming</span>
                            @endif
                            @if(!$period->is_attendance_required)
                                <span style="font-size:0.65rem; color:#f59e0b; background:rgba(245,158,11,0.1); padding:0.1rem 0.4rem; border-radius:99px;">No Attendance</span>
                            @endif
                        </div>
                        @if($statusLabel === 'active')
                        <div style="margin-top:0.75rem;">
                            <div style="display:flex; justify-content:space-between; font-size:0.7rem; color:var(--text-muted); margin-bottom:0.3rem;">
                                <span>Progress</span>
                                <span>{{ $period->progress_percent }}%</span>
                            </div>
                            <div style="height:4px; background:rgba(255,255,255,0.08); border-radius:99px; overflow:hidden;">
                                <div style="width:{{ $period->progress_percent }}%; height:100%; background:{{ $color }}; border-radius:99px;"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                <i class="ph ph-calendar-blank" style="font-size:2rem; display:block; margin-bottom:0.5rem; opacity:0.4;"></i>
                <p style="margin:0;">{{ __('No periods yet.') }} <button onclick="openAddPeriodModal({{ $year->id }}, '{{ addslashes($year->name) }}')" style="background:none; border:none; color:var(--primary); cursor:pointer; font-family:inherit; font-size:inherit; padding:0;">{{ __('Add one now') }}</button></p>
            </div>
        @endif
    </div>
</div>
@empty
<div class="card" style="text-align:center; padding:4rem 2rem; border-radius:1.25rem;">
    <div style="font-size:4rem; margin-bottom:1rem;">🎓</div>
    <h3 style="margin:0 0 0.5rem; font-size:1.25rem;">{{ __('No Academic Years Yet') }}</h3>
    <p style="color:var(--text-muted); margin:0 0 1.5rem;">{{ __('Create your first academic year to get started.') }}</p>
    <button onclick="openAddYearModal()" class="btn btn-primary">
        <i class="ph ph-plus-circle"></i> {{ __('Create Academic Year') }}
    </button>
</div>
@endforelse


{{-- ═══════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════ --}}

{{-- Add/Edit Academic Year Modal --}}
<div id="yearModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:520px; border-radius:1.25rem; padding:2rem; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h3 id="yearModalTitle" style="margin:0; font-size:1.1rem; font-weight:800;">{{ __('New Academic Year') }}</h3>
            <button onclick="closeModal('yearModal')" style="background:none; border:none; color:var(--text-secondary); font-size:1.5rem; cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>
        <form id="yearForm">
            <input type="hidden" id="yearId" value="">
            <div style="display:grid; gap:1rem;">
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Academic Year Name') }} *</label>
                    <input type="text" id="yearName" class="form-control" placeholder="e.g. 2026-2027" required>
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Name (Khmer)') }}</label>
                    <input type="text" id="yearNameKh" class="form-control" placeholder="ឆ្នាំសិក្សា ២០២៦-២០២៧">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Start Date') }} *</label>
                        <input type="date" id="yearStart" class="form-control" required>
                    </div>
                    <div>
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('End Date') }} *</label>
                        <input type="date" id="yearEnd" class="form-control" required>
                    </div>
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Status') }}</label>
                    <select id="yearStatus" class="form-control">
                        <option value="upcoming">{{ __('Upcoming') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="completed">{{ __('Completed') }}</option>
                    </select>
                </div>
                <div>
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.9rem;">
                        <input type="checkbox" id="setCurrentCheck" style="width:16px; height:16px;">
                        <span>{{ __('Set as Current Academic Year') }}</span>
                    </label>
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Notes') }}</label>
                    <textarea id="yearNotes" class="form-control" rows="2" placeholder="{{ __('Optional notes...') }}"></textarea>
                </div>
            </div>
            <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="ph ph-floppy-disk"></i> {{ __('Save') }}
                </button>
                <button type="button" onclick="closeModal('yearModal')" class="btn btn-secondary" style="flex:1;">{{ __('Cancel') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Add/Edit Period Modal --}}
<div id="periodModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:520px; border-radius:1.25rem; padding:2rem; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h3 id="periodModalTitle" style="margin:0; font-size:1.1rem; font-weight:800;">{{ __('Add Period') }}</h3>
            <button onclick="closeModal('periodModal')" style="background:none; border:none; color:var(--text-secondary); font-size:1.5rem; cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>
        <form id="periodForm">
            <input type="hidden" id="periodId" value="">
            <input type="hidden" id="periodYearId" value="">
            <div style="display:grid; gap:1rem;">
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Period Name') }} *</label>
                    <input type="text" id="periodName" class="form-control" placeholder="e.g. Semester 1" required>
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Name (Khmer)') }}</label>
                    <input type="text" id="periodNameKh" class="form-control" placeholder="ឆមាសទី១">
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Type') }} *</label>
                    <select id="periodType" class="form-control" onchange="updatePeriodColor()">
                        <option value="semester">📚 {{ __('Semester') }}</option>
                        <option value="term">📖 {{ __('Term / Module') }}</option>
                        <option value="exam">📝 {{ __('Examination') }}</option>
                        <option value="holiday_break">🏖️ {{ __('Holiday / Break') }}</option>
                        <option value="other">📋 {{ __('Other') }}</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Start Date') }} *</label>
                        <input type="date" id="periodStart" class="form-control" required>
                    </div>
                    <div>
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('End Date') }} *</label>
                        <input type="date" id="periodEnd" class="form-control" required>
                    </div>
                </div>
                <div style="display:flex; gap:1rem; align-items:center;">
                    <div style="flex:1;">
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Color') }}</label>
                        <input type="color" id="periodColor" value="#00d4a0" class="form-control" style="height:40px; padding:4px;">
                    </div>
                    <div style="flex:2;">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.9rem; margin-top:1.4rem;">
                            <input type="checkbox" id="periodAttendance" checked style="width:16px; height:16px;">
                            <span>{{ __('Attendance Required') }}</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Notes') }}</label>
                    <textarea id="periodNotes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="ph ph-floppy-disk"></i> {{ __('Save Period') }}
                </button>
                <button type="button" onclick="closeModal('periodModal')" class="btn btn-secondary" style="flex:1;">{{ __('Cancel') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const typeColors = { semester:'#00d4a0', term:'#6366f1', exam:'#ef4444', holiday_break:'#f59e0b', other:'#94a3b8' };

function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

// ── Academic Year ────────────────────────────────────────
function openAddYearModal() {
    document.getElementById('yearId').value = '';
    document.getElementById('yearModalTitle').textContent = '{{ __("New Academic Year") }}';
    document.getElementById('yearName').value = '';
    document.getElementById('yearNameKh').value = '';
    document.getElementById('yearStart').value = '';
    document.getElementById('yearEnd').value = '';
    document.getElementById('yearStatus').value = 'upcoming';
    document.getElementById('yearNotes').value = '';
    document.getElementById('setCurrentCheck').checked = false;
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
    document.getElementById('yearNotes').value = year.notes || '';
    document.getElementById('setCurrentCheck').checked = year.is_current;
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
        notes: document.getElementById('yearNotes').value,
        set_as_current: document.getElementById('setCurrentCheck').checked ? 1 : 0,
    };

    try {
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) { closeModal('yearModal'); location.reload(); }
        else alert(data.message || 'Error');
    } catch(err) { alert('Error: ' + err.message); }
});

async function deleteYear(id, name) {
    if (!confirm(`Delete academic year "${name}"? All periods will be deleted too.`)) return;
    const res = await fetch(`/academic-calendar/years/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message || 'Error');
}

async function setCurrentYear(id) {
    if (!confirm('{{ __("Set this as the current academic year?") }}')) return;
    const res = await fetch(`/academic-calendar/years/${id}/set-current`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) location.reload();
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
    document.getElementById('periodAttendance').checked = period.is_attendance_required;
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
        if (data.success) { closeModal('periodModal'); location.reload(); }
        else alert(data.message || 'Error');
    } catch(err) { alert('Error: ' + err.message); }
});

async function deletePeriod(id, name) {
    if (!confirm(`Delete period "${name}"?`)) return;
    const res = await fetch(`/academic-calendar/periods/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message || 'Error');
}
</script>
@endpush
@endsection
