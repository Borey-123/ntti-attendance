@extends('layouts.app')

@section('title', __('Attendance Analytics'))

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
    .hm-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.4rem;
    }
    .hm-day-header {
        text-align: center;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
        padding-bottom: 0.3rem;
    }
    .hm-cell {
        aspect-ratio: 1;
        border-radius: 0.6rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        background: var(--bg-dark);
        cursor: default;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .hm-cell:hover { transform: scale(1.15); z-index: 5; box-shadow: 0 6px 16px rgba(0,0,0,0.25); }
    .hm-cell .hm-date { font-size: 0.75rem; font-weight: 800; }
    .hm-cell .hm-count { font-size: 0.6rem; font-weight: 700; opacity: 0.85; }
    .hm-cell.empty { background: transparent; border-color: transparent; }
    .hm-cell.level-0 { background: var(--bg-dark); }
    .hm-cell.level-1 { background: rgba(var(--primary-rgb), 0.18); border-color: rgba(var(--primary-rgb), 0.3); color: var(--primary); }
    .hm-cell.level-2 { background: rgba(var(--primary-rgb), 0.45); border-color: rgba(var(--primary-rgb), 0.55); color: var(--primary); }
    .hm-cell.level-3 { background: rgba(var(--primary-rgb), 0.8); border-color: rgba(var(--primary-rgb), 0.9); color: #fff; }
</style>
@endpush

@section('content')
<div class="animate-fade-up">

    {{-- ── Header ── --}}
    <div class="d-flex justify-between align-center" style="margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">{{ __('Attendance Analytics') }}</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Institutional intelligence, punctuality trends, and active personnel metrics.') }}</p>
        </div>
        <form action="{{ route('analytics.index') }}" method="GET" style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="position: relative;">
                <i class="ph ph-calendar" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--primary); font-size: 1.1rem; pointer-events: none;"></i>
                <input type="month" name="month" value="{{ $month }}" class="form-control" style="padding-left: 2.75rem; border-radius: 1rem; height: 42px; font-weight: 700; background: var(--bg-elevated); border: 1px solid var(--border);" onchange="this.form.submit()">
            </div>
            <button type="submit" class="btn btn-primary" style="border-radius: 1rem; height: 42px; padding: 0 1.25rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-funnel" style="font-size: 1.1rem;"></i>
                <span>{{ __('Filter') }}</span>
            </button>
        </form>
    </div>

    @php
        $totalDays  = count(array_filter($dailyCounts));
        $totalScans = array_sum($dailyCounts);
        $avgPerDay  = $totalDays > 0 ? round($totalScans / $totalDays, 1) : 0;
        $peakDay    = $dailyCounts ? array_search(max($dailyCounts), $dailyCounts) : '—';
        $peakCount  = $dailyCounts ? max($dailyCounts) : 0;
    @endphp

    {{-- ── Summary Metrics Grid ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-fingerprint"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Total Scans') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $totalScans }}</div>
            </div>
        </div>
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-calendar-check"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Active Days') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $totalDays }}</div>
            </div>
        </div>
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-trend-up"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Avg Scans / Day') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $avgPerDay }}</div>
            </div>
        </div>
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
            <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="ph ph-lightning"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Peak Activity Day') }}</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px;">{{ $peakCount }}</div>
            </div>
        </div>
    </div>

    {{-- ── Charts & Activity Matrix ── --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        
        {{-- Daily Activity Bar Chart --}}
        <div class="card" style="border-radius: 2rem; overflow: hidden; display: flex; flex-direction: column;">
            <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph ph-chart-bar" style="color: var(--primary);"></i>
                    {{ __('Daily Check-In Activity') }}
                </h3>
                <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary); background: rgba(var(--primary-rgb), 0.1); padding: 0.3rem 0.8rem; border-radius: 0.6rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                    {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}
                </span>
            </div>
            <div style="padding: 1.5rem; flex: 1; position: relative; min-height: 280px;">
                <canvas id="dailyBarChart"></canvas>
            </div>
        </div>

        {{-- Activity Heatmap Matrix --}}
        <div class="card" style="border-radius: 2rem; overflow: hidden; display: flex; flex-direction: column;">
            <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border);">
                <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph ph-squares-four" style="color: var(--primary);"></i>
                    {{ __('Activity Matrix') }}
                </h3>
            </div>
            <div style="padding: 1.5rem; flex: 1;">
                @php
                    $start     = \Carbon\Carbon::parse($startDate);
                    $daysInMon = $start->daysInMonth;
                    $maxCount  = $dailyCounts ? max(array_values($dailyCounts) ?: [1]) : 1;
                    $firstDow  = \Carbon\Carbon::parse($startDate)->dayOfWeek;
                    $startOffset = ($firstDow + 6) % 7;
                @endphp
                <div class="hm-grid mb-3">
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
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); margin-top: 1rem;">
                    <span>{{ __('Less') }}</span>
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--bg-dark); border: 1px solid var(--border);"></span>
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(var(--primary-rgb), 0.2);"></span>
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(var(--primary-rgb), 0.5);"></span>
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(var(--primary-rgb), 0.9);"></span>
                    <span>{{ __('More') }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Department Punctuality Table & Top Teachers Grid ── --}}
    <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 1.5rem;">

        {{-- Department Punctuality Table --}}
        <div class="card" style="border-radius: 2rem; overflow: hidden;">
            <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph ph-trophy" style="color: #f59e0b;"></i>
                    {{ __('Department Punctuality Ranking') }}
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th>{{ __('Department') }}</th>
                            <th style="text-align: center;">{{ __('Teachers') }}</th>
                            <th style="text-align: center;">{{ __('Total Scans') }}</th>
                            <th style="text-align: right; padding-right: 2rem;">{{ __('Punctuality Rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $index => $d)
                        @php
                            $dName = app()->getLocale() == 'km' ? ($d['name_kh'] ?: $d['name']) : $d['name'];
                        @endphp
                        <tr>
                            <td style="text-align: center;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $index == 0 ? 'linear-gradient(135deg, #f59e0b, #d97706)' : ($index == 1 ? 'linear-gradient(135deg, #94a3b8, #64748b)' : ($index == 2 ? 'linear-gradient(135deg, #c97c3e, #92400e)' : 'var(--bg-elevated)')) }}; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--text-primary); font-size: 0.95rem;">{{ $dName }}</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-secondary" style="font-size: 0.8rem; font-weight: 700;">{{ $d['total_teachers'] }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-info" style="font-size: 0.8rem; font-weight: 800;">{{ $d['total_scans'] }}</span>
                            </td>
                            <td style="text-align: right; padding-right: 2rem;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                                    <div style="width: 100px; height: 6px; border-radius: 3px; background: var(--bg-dark); overflow: hidden;">
                                        <div style="width: {{ $d['punctuality_rate'] }}%; height: 100%; background: var(--primary); border-radius: 3px;"></div>
                                    </div>
                                    <span style="font-weight: 900; font-size: 1.1rem; color: var(--primary); width: 50px;">{{ $d['punctuality_rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <i class="ph ph-chart-bar" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                                {{ __('No department data available.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Most Active Teachers --}}
        <div class="card" style="border-radius: 2rem; overflow: hidden;">
            <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph ph-medal" style="color: #8b5cf6;"></i>
                    {{ __('Most Active Teachers') }}
                </h3>
            </div>
            <div style="padding: 1rem 1.5rem;">
                @forelse($topTeachers as $index => $t)
                @php
                    $tName = app()->getLocale() == 'km' ? ($t->name_kh ?: $t->name) : $t->name;
                @endphp
                <div style="display: flex; align-items: center; gap: 1rem; padding: 0.85rem 1rem; border-radius: 1rem; background: var(--bg-dark); margin-bottom: 0.75rem; border: 1px solid var(--border);">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; flex-shrink: 0;">
                        #{{ $index + 1 }}
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden; background: var(--primary); color: #000; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if($t->photo)
                            <img src="{{ to_asset_url($t->photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr($t->name, 0, 1)) }}
                        @endif
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 800; color: var(--text-primary); font-size: 0.92rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $tName }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 600;">{{ $t->department }}</div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <div style="font-weight: 900; font-size: 1.25rem; color: var(--primary); line-height: 1;">{{ $t->attendance_count }}</div>
                        <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">{{ __('Scans') }}</div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i class="ph ph-users" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                    {{ __('No teacher data available.') }}
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? 'rgba(255,255,255,0.45)' : 'rgba(0,0,0,0.45)';

    const rawData = @json($dailyCounts);
    const month   = '{{ $month }}';

    const daysInMonth = new Date(month.split('-')[0], month.split('-')[1], 0).getDate();
    const labels = [], values = [];
    for (let d = 1; d <= daysInMonth; d++) {
        const key = month + '-' + String(d).padStart(2, '0');
        labels.push(d);
        values.push(rawData[key] || 0);
    }

    const root    = document.documentElement;
    const primary = getComputedStyle(root).getPropertyValue('--primary').trim() || '#00d4aa';

    const canvas = document.getElementById('dailyBarChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const grad = ctx.createLinearGradient(0, 0, 0, 260);
    grad.addColorStop(0, primary + 'cc');
    grad.addColorStop(1, primary + '11');

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
@endpush
@endsection
