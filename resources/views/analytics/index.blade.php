@extends('layouts.app')

@section('title', __('Attendance Analytics'))

@push('styles')
<style>
/* ══════════════════════════════════════════
   STAT CARDS
══════════════════════════════════════════ */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1.5rem 1.75rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 100px;
    border-radius: 50%;
    transform: translate(30%, -30%);
    opacity: 0.08;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.15); }
.stat-card:hover { border-color: var(--sc-color, var(--primary)); }
.stat-card .sc-icon {
    width: 48px; height: 48px; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; margin-bottom: 1rem;
}
.stat-card .sc-val { font-size: 2.2rem; font-weight: 900; line-height: 1; margin-bottom: 0.25rem; }
.stat-card .sc-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.6; }
.stat-card .sc-sub { font-size: 0.8rem; font-weight: 700; margin-top: 0.5rem; opacity: 0.5; }

/* ══════════════════════════════════════════
   SECTION PANELS
══════════════════════════════════════════ */
.analytics-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.panel-header {
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.panel-header h5 {
    margin: 0;
    font-weight: 800;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: var(--text-primary);
}
.panel-body { padding: 1.5rem 1.75rem; }

/* ══════════════════════════════════════════
   HEATMAP
══════════════════════════════════════════ */
.heatmap-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.4rem;
}
.hm-day-header {
    text-align: center;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-secondary);
    padding-bottom: 0.4rem;
}
.hm-cell {
    aspect-ratio: 1;
    border-radius: 0.6rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    background: var(--bg-card);
    cursor: default;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}
.hm-cell:hover { transform: scale(1.15); z-index: 5; box-shadow: 0 8px 20px rgba(0,0,0,0.25); }
.hm-cell .hm-date { font-size: 0.75rem; font-weight: 800; }
.hm-cell .hm-count { font-size: 0.6rem; font-weight: 700; opacity: 0.9; }
.hm-cell.empty { background: transparent; border-color: transparent; }
.hm-cell.level-0 { background: var(--bg-card); }
.hm-cell.level-1 { background: rgba(var(--primary-rgb), 0.18); border-color: rgba(var(--primary-rgb), 0.3); color: var(--primary); }
.hm-cell.level-2 { background: rgba(var(--primary-rgb), 0.45); border-color: rgba(var(--primary-rgb), 0.55); color: var(--primary); }
.hm-cell.level-3 { background: rgba(var(--primary-rgb), 0.8); border-color: rgba(var(--primary-rgb), 0.9); color: #fff; }
.hm-legend { display: flex; align-items: center; gap: 0.4rem; font-size: 0.7rem; font-weight: 700; color: var(--text-secondary); }
.hm-legend-box { width: 14px; height: 14px; border-radius: 4px; }

/* ══════════════════════════════════════════
   RANK CARDS
══════════════════════════════════════════ */
.rank-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border-radius: 1rem;
    border: 1px solid transparent;
    transition: all 0.2s;
    cursor: default;
}
.rank-item:hover { background: rgba(var(--primary-rgb), 0.04); border-color: rgba(var(--primary-rgb), 0.15); transform: translateX(4px); }
.rank-badge {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; font-weight: 900; flex-shrink: 0;
}
.rank-1 { background: linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow: 0 4px 12px rgba(245,158,11,0.4); }
.rank-2 { background: linear-gradient(135deg,#94a3b8,#64748b); color:#fff; }
.rank-3 { background: linear-gradient(135deg,#c97c3e,#92400e); color:#fff; }
.rank-other { background: rgba(255,255,255,0.06); color: var(--text-secondary); border: 1px solid var(--border); }
[data-theme="light"] .rank-other { background: rgba(0,0,0,0.05); }
.rank-progress { height: 4px; border-radius: 2px; background: rgba(var(--primary-rgb),0.12); overflow: hidden; margin-top: 0.35rem; }
.rank-progress-bar { height: 100%; border-radius: 2px; background: var(--primary); transition: width 1s ease; }

/* chart wrapper */
.chart-wrapper { position: relative; width: 100%; height: 260px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- ── PAGE HEADER ── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-primary mb-1 d-flex align-items-center gap-2" style="font-size:1.8rem;">
                <i class="ph ph-chart-bar"></i>
                <span>{{ __('Attendance Analytics') }}</span>
            </h2>
            <p class="text-secondary small fw-bold text-uppercase mb-0" style="letter-spacing:.5px;">
                {{ __('Institutional Intelligence & Performance Metrics') }}
            </p>
        </div>
        <form action="{{ route('analytics.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="position-relative">
                <i class="ph ph-calendar text-primary position-absolute" style="left:.9rem;top:50%;transform:translateY(-50%);font-size:1rem;pointer-events:none;"></i>
                <input type="month" name="month" value="{{ $month }}"
                       class="form-control fw-bold"
                       style="padding-left:2.4rem;padding-right:1rem;height:40px;border-radius:1rem;font-size:.9rem;background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border);"
                       onchange="this.form.submit()">
            </div>
            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px;border-radius:1rem;padding:0;">
                <i class="ph ph-funnel" style="font-size:1.1rem;"></i>
            </button>
        </form>
    </div>

    @php
        $totalDays = count(array_filter($dailyCounts));
        $totalScans = array_sum($dailyCounts);
        $avgPerDay  = $totalDays > 0 ? round($totalScans / $totalDays, 1) : 0;
        $peakDay    = $dailyCounts ? array_search(max($dailyCounts), $dailyCounts) : '—';
        $peakCount  = $dailyCounts ? max($dailyCounts) : 0;
    @endphp

    {{-- ── STAT CARDS ── --}}
    <div class="stat-grid">
        <div class="stat-card" style="--sc-color: var(--primary);">
            <div class="sc-icon" style="background:rgba(var(--primary-rgb),.12);color:var(--primary);"><i class="ph ph-fingerprint"></i></div>
            <div class="sc-val" style="color:var(--primary);">{{ $totalScans }}</div>
            <div class="sc-label">{{ __('Total Scans') }}</div>
            <div class="sc-sub">{{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</div>
            <div class="sc-icon position-absolute" style="width:90px;height:90px;top:-15px;right:-15px;border-radius:50%;background:rgba(var(--primary-rgb),.07);color:transparent;"></div>
        </div>
        <div class="stat-card" style="--sc-color:#10b981;">
            <div class="sc-icon" style="background:rgba(16,185,129,.12);color:#10b981;"><i class="ph ph-calendar-check"></i></div>
            <div class="sc-val" style="color:#10b981;">{{ $totalDays }}</div>
            <div class="sc-label">{{ __('Active Days') }}</div>
            <div class="sc-sub">{{ __('Days with records') }}</div>
        </div>
        <div class="stat-card" style="--sc-color:#f59e0b;">
            <div class="sc-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;"><i class="ph ph-trend-up"></i></div>
            <div class="sc-val" style="color:#f59e0b;">{{ $avgPerDay }}</div>
            <div class="sc-label">{{ __('Avg / Day') }}</div>
            <div class="sc-sub">{{ __('Check-ins per active day') }}</div>
        </div>
        <div class="stat-card" style="--sc-color:#8b5cf6;">
            <div class="sc-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;"><i class="ph ph-lightning"></i></div>
            <div class="sc-val" style="color:#8b5cf6;">{{ $peakCount }}</div>
            <div class="sc-label">{{ __('Peak Day') }}</div>
            <div class="sc-sub">{{ $peakDay ?: '—' }}</div>
        </div>
    </div>

    {{-- ── BAR CHART + HEATMAP ROW ── --}}
    <div class="row g-3 mb-3">
        {{-- Bar Chart --}}
        <div class="col-lg-8">
            <div class="analytics-panel h-100">
                <div class="panel-header">
                    <h5><i class="ph ph-chart-bar text-primary"></i> {{ __('Daily Check-In Activity') }}</h5>
                    <span class="badge" style="background:rgba(var(--primary-rgb),.1);color:var(--primary);font-size:.7rem;font-weight:800;padding:.35rem .75rem;border-radius:.5rem;border:1px solid rgba(var(--primary-rgb),.2);">
                        {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}
                    </span>
                </div>
                <div class="panel-body">
                    <div class="chart-wrapper">
                        <canvas id="dailyBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Heatmap --}}
        <div class="col-lg-4">
            <div class="analytics-panel h-100">
                <div class="panel-header">
                    <h5><i class="ph ph-squares-four text-primary"></i> {{ __('Activity Matrix') }}</h5>
                </div>
                <div class="panel-body">
                    @php
                        $start     = \Carbon\Carbon::parse($startDate);
                        $daysInMon = $start->daysInMonth;
                        $maxCount  = $dailyCounts ? max(array_values($dailyCounts) ?: [1]) : 1;
                        $firstDow  = \Carbon\Carbon::parse($startDate)->dayOfWeek; // 0=Sun
                        // Convert to Mon-start: Mon=0..Sun=6
                        $startOffset = ($firstDow + 6) % 7;
                    @endphp
                    <div class="heatmap-grid mb-3">
                        @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $dh)
                            <div class="hm-day-header">{{ $dh }}</div>
                        @endforeach
                        @for($i = 0; $i < $startOffset; $i++)
                            <div class="hm-cell empty"></div>
                        @endfor
                        @for($d = 1; $d <= $daysInMon; $d++)
                            @php
                                $ds  = sprintf('%s-%02d', $month, $d);
                                $cnt = $dailyCounts[$ds] ?? 0;
                                $lvl = 0;
                                if($cnt > 0) {
                                    $r = $cnt / max($maxCount, 1);
                                    $lvl = $r < 0.25 ? 1 : ($r < 0.6 ? 2 : 3);
                                }
                            @endphp
                            <div class="hm-cell level-{{ $lvl }}" title="{{ $ds }}: {{ $cnt }} {{ __('scans') }}">
                                <span class="hm-date">{{ $d }}</span>
                                @if($cnt > 0)<span class="hm-count">{{ $cnt }}</span>@endif
                            </div>
                        @endfor
                    </div>
                    <div class="d-flex align-items-center gap-2 justify-content-end mt-2">
                        <span class="hm-legend"><span class="hm-legend-box" style="background:var(--bg-card);border:1px solid var(--border);"></span>{{ __('None') }}</span>
                        <span class="hm-legend"><span class="hm-legend-box" style="background:rgba(var(--primary-rgb),.18);"></span>{{ __('Low') }}</span>
                        <span class="hm-legend"><span class="hm-legend-box" style="background:rgba(var(--primary-rgb),.45);"></span>{{ __('Mid') }}</span>
                        <span class="hm-legend"><span class="hm-legend-box" style="background:rgba(var(--primary-rgb),.8);"></span>{{ __('High') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── RANKINGS ROW ── --}}
    <div class="row g-3">
        {{-- Department Ranking --}}
        <div class="col-lg-7">
            <div class="analytics-panel">
                <div class="panel-header">
                    <h5><i class="ph ph-trophy" style="color:#f59e0b;"></i> {{ __('Department Punctuality Ranking') }}</h5>
                    <span class="small fw-bold text-secondary">{{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</span>
                </div>
                <div class="panel-body" style="padding-top:1rem;">
                    @forelse($departments as $d)
                    @php
                        $rank   = $loop->iteration;
                        $rClass = $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : 'rank-other'));
                        $dName  = app()->getLocale() == 'km' ? ($d['name_kh'] ?: $d['name']) : $d['name'];
                    @endphp
                    <div class="rank-item">
                        <div class="rank-badge {{ $rClass }}">#{{ $rank }}</div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold" style="font-size:.95rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $dName }}</div>
                            <div class="d-flex gap-3 small fw-bold text-secondary mt-1">
                                <span><i class="ph ph-users"></i> {{ $d['total_teachers'] }} {{ __('teachers') }}</span>
                                <span><i class="ph ph-fingerprint"></i> {{ $d['total_scans'] }} {{ __('scans') }}</span>
                            </div>
                            <div class="rank-progress mt-2">
                                <div class="rank-progress-bar" style="width:{{ $d['punctuality_rate'] }}%;"></div>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-black" style="font-size:1.5rem;color:var(--primary);line-height:1;">{{ $d['punctuality_rate'] }}%</div>
                            <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-secondary);">{{ __('Punctual') }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-secondary">
                        <i class="ph ph-chart-bar" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
                        {{ __('No department data available.') }}
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Teachers --}}
        <div class="col-lg-5">
            <div class="analytics-panel">
                <div class="panel-header">
                    <h5><i class="ph ph-medal" style="color:#8b5cf6;"></i> {{ __('Most Active Teachers') }}</h5>
                    <span class="small fw-bold text-secondary">{{ __('Top 5 this month') }}</span>
                </div>
                <div class="panel-body" style="padding-top:1rem;">
                    @forelse($topTeachers as $t)
                    @php
                        $rank   = $loop->iteration;
                        $rClass = $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : 'rank-other'));
                        $tName  = app()->getLocale() == 'km' ? ($t->name_kh ?: $t->name) : $t->name;
                    @endphp
                    <div class="rank-item">
                        <div class="rank-badge {{ $rClass }}" style="width:30px;height:30px;font-size:.75rem;">#{{ $rank }}</div>
                        <div style="width:42px;height:42px;border-radius:50%;overflow:hidden;flex-shrink:0;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#000;font-weight:900;font-size:1.1rem;">
                            @if($t->photo)
                                <img src="{{ to_asset_url($t->photo) }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                {{ strtoupper(substr($t->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-primary" style="font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $tName }}</div>
                            <div class="small text-secondary fw-bold">{{ $t->department }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-black" style="font-size:1.4rem;color:var(--primary);line-height:1;">{{ $t->attendance_count }}</div>
                            <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-secondary);">{{ __('Scans') }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-secondary">
                        <i class="ph ph-users" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
                        {{ __('No teacher data available.') }}
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? 'rgba(255,255,255,0.45)' : 'rgba(0,0,0,0.45)';

    const rawData = @json($dailyCounts);
    const month   = '{{ $month }}';

    // Build ordered labels & values for all days in month
    const daysInMonth = new Date(month.split('-')[0], month.split('-')[1], 0).getDate();
    const labels = [], values = [];
    for (let d = 1; d <= daysInMonth; d++) {
        const key = month + '-' + String(d).padStart(2, '0');
        labels.push(d);
        values.push(rawData[key] || 0);
    }

    // Primary color from CSS variable
    const root    = document.documentElement;
    const primary = getComputedStyle(root).getPropertyValue('--primary').trim() || '#00d4aa';

    const ctx = document.getElementById('dailyBarChart').getContext('2d');

    // Gradient fill
    const grad = ctx.createLinearGradient(0, 0, 0, 260);
    grad.addColorStop(0, primary + 'cc');
    grad.addColorStop(1, primary + '22');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: '{{ __("Check-ins") }}',
                data: values,
                backgroundColor: grad,
                borderColor: primary,
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e2533' : '#fff',
                    titleColor: isDark ? '#fff' : '#111',
                    bodyColor: isDark ? '#aaa' : '#555',
                    borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        title: (items) => `{{ \Carbon\Carbon::parse($month.'-01')->format('F') }} ${items[0].label}`,
                        label: (item) => ` ${item.raw} {{ __('check-ins') }}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: labelColor, font: { weight: '700', size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { color: labelColor, font: { weight: '700', size: 11 }, stepSize: 1 }
                }
            }
        }
    });
})();
</script>
@endsection
