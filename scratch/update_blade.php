<?php

$file = __DIR__ . '/../resources/views/reports/index.blade.php';
$content = file_get_contents($file);

// 1. Update Print Header (Date -> Period, etc. are already there)

// 2. Replace the Dropdown Filters and Action Buttons
$filtersStart = '{{-- Dropdown Filters --}}';
$filtersEnd = '</div>
        </div>
    </div>
</div>';

$newFilters = <<<HTML
{{-- Dropdown Filters --}}
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <div class="input-with-icon">
                    <i class="ph ph-file-text"></i>
                    <select id="reportTypeFilter" class="form-control" style="width: 190px; padding-left: 2.2rem; background: var(--bg-dark); font-weight: 700;" onchange="handleReportTypeChange()">
                        <option value="daily">{{ __('Daily Attendance') }}</option>
                        <option value="monthly">{{ __('Monthly Attendance') }}</option>
                        <option value="absent">{{ __('Absent Report') }}</option>
                        <option value="late">{{ __('Late Report') }}</option>
                        <option value="leave">{{ __('Leave Report') }}</option>
                        <option value="individual">{{ __('Individual Teacher Report') }}</option>
                        <option value="department">{{ __('Department Report') }}</option>
                    </select>
                </div>

                <div class="input-with-icon">
                    <i class="ph ph-buildings"></i>
                    <select id="departmentFilter" class="form-control" style="width: 180px; padding-left: 2.2rem; background: var(--bg-dark); font-weight: 700;">
                        <option value="">{{ __('All Departments') }}</option>
                        @foreach(\$departments as \$d)
                            <option value="{{\$d->name}}">{{ app()->getLocale() == 'km' ? (\$d->name_kh ?: \$d->name) : \$d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="input-with-icon">
                    <i class="ph ph-user-circle"></i>
                    <select id="teacherFilter" class="form-control" style="width: 190px; padding-left: 2.2rem; background: var(--bg-dark); font-weight: 700;">
                        <option value="">{{ __('All Teachers') }}</option>
                        @foreach(\$teachers as \$t)
                            <option value="{{\$t->id}}" data-dept="{{\$t->department}}">
                                {{ app()->getLocale() == 'km' ? (\$t->name_kh ?: \$t->name) : \$t->name }} ({{\$t->employee_id}})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display: flex; gap: 0.5rem; border-left: 1px solid var(--border); padding-left: 1rem; margin-left: 0.5rem;">
                <button type="button" class="btn btn-primary" onclick="loadAll()" style="padding: 0.6rem 1.5rem; background: linear-gradient(135deg, var(--primary), #00b894); border: none; color: #000; font-weight: 800; box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3); transition: all 0.3s; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="ph ph-funnel" style="font-size: 1.1rem;"></i> {{ __('Filter') }}
                </button>
                <div style="display: flex; gap: 0.25rem; background: rgba(255,255,255,0.05); padding: 0.25rem; border-radius: 0.75rem;">
                    <button class="btn-icon-label" onclick="exportPdf()" title="{{ __('Export PDF') }}">
                        <i class="ph ph-file-pdf" style="color: #ff4d4d;"></i>
                        <span>PDF</span>
                    </button>
                    <button class="btn-icon-label" onclick="exportExcel()" title="{{ __('Export Excel') }}">
                        <i class="ph ph-file-xls" style="color: #2ecc71;"></i>
                        <span>Excel</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;

// Use regex to replace the filters section
$content = preg_replace('/\{\{-- Dropdown Filters --\}\}.*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s', $newFilters, $content);

// 3. Replace the Tabs and Panels
$panelsStart = '{{-- Tabs --}}';
$panelsEnd = '{{-- Premium Print Footer --}}';

$newPanels = <<<HTML
{{-- Report Panels --}}

{{-- 1. Daily Records --}}
<div id="panel-daily" class="tab-panel">
    <div class="chart-glass-card">
        <h3 style="margin-bottom: 2rem; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
            <i class="ph ph-chart-line-up" style="color:var(--primary);"></i>
            {{ __('Attendance Volatility & Trend') }}
        </h3>
        <div class="chart-container" style="height: 350px;">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Teacher') }}</th>
                    <th>{{ __('Department') }}</th>
                    <th>{{ __('Morning') }}</th>
                    <th>{{ __('Afternoon') }}</th>
                    <th>{{ __('Working Hours') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody id="report-tbody">
                <tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 2. Monthly Attendance --}}
<div id="panel-monthly" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-ranking" style="margin-right:0.4rem;"></i>{{ __('Teacher Monthly Attendance') }}</h3>
            <span id="monthly-period" style="font-size:0.78rem; color:var(--text-secondary);"></span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>{{ __('Teacher ID') }}</th>
                        <th>{{ __('Teacher Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th style="text-align:center;">{{ __('Present') }}</th>
                        <th style="text-align:center;">{{ __('Late') }}</th>
                        <th style="text-align:center;">{{ __('Absent') }}</th>
                        <th style="text-align:center;">{{ __('Leave') }}</th>
                        <th style="text-align:center;">{{ __('Working Days') }}</th>
                        <th style="text-align:center;">{{ __('Attendance %') }}</th>
                    </tr>
                </thead>
                <tbody id="monthly-tbody">
                    <tr><td colspan="10" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 3. Absent Report --}}
<div id="panel-absent" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-user-minus" style="margin-right:0.4rem;color:var(--danger);"></i>{{ __('Absent Report') }}</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th>{{ __('Teacher ID') }}</th>
                        <th>{{ __('Teacher Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Absent Date') }}</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Remark') }}</th>
                    </tr>
                </thead>
                <tbody id="absent-tbody">
                    <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 4. Late Report --}}
<div id="panel-late" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-clock-afternoon" style="margin-right:0.4rem;color:var(--warning);"></i>{{ __('Late Report') }}</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th>{{ __('Teacher ID') }}</th>
                        <th>{{ __('Teacher Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Check In') }}</th>
                        <th>{{ __('Expected Time') }}</th>
                        <th>{{ __('Late Minutes') }}</th>
                    </tr>
                </thead>
                <tbody id="late-tbody">
                    <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 5. Leave Report --}}
<div id="panel-leave" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-calendar-x" style="margin-right:0.4rem;color:var(--primary);"></i>{{ __('Leave Report') }}</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th>{{ __('Teacher ID') }}</th>
                        <th>{{ __('Teacher Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Leave Date') }}</th>
                        <th>{{ __('Leave Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Remark') }}</th>
                    </tr>
                </thead>
                <tbody id="leave-tbody">
                    <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 6. Individual Teacher Report --}}
<div id="panel-individual" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3 id="indiv-title"><i class="ph ph-user-focus" style="margin-right:0.4rem;"></i>{{ __('Individual Teacher Report') }}</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Check In') }}</th>
                        <th>{{ __('Check Out') }}</th>
                        <th>{{ __('Working Hours') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Remark') }}</th>
                    </tr>
                </thead>
                <tbody id="indiv-tbody">
                    <tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Please select a teacher and click Filter.') }}</td></tr>
                </tbody>
                <tfoot id="indiv-tfoot" style="display:none;">
                    <tr style="background:var(--bg-dark); font-weight:bold;">
                        <td colspan="4" style="text-align:right;">{{ __('TOTALS') }}:</td>
                        <td id="indiv-hours" style="color:var(--primary);"></td>
                        <td colspan="2" id="indiv-summary" style="font-size:0.85rem;"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- 7. Department Report --}}
<div id="panel-department" class="tab-panel" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-buildings" style="margin-right:0.4rem;"></i>{{ __('Department Report') }}</h3>
            <span id="dept-period" style="font-size:0.78rem; color:var(--text-secondary);"></span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th>{{ __('Teacher ID') }}</th>
                        <th>{{ __('Teacher Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th style="text-align:center;">{{ __('Present') }}</th>
                        <th style="text-align:center;">{{ __('Late') }}</th>
                        <th style="text-align:center;">{{ __('Absent') }}</th>
                        <th style="text-align:center;">{{ __('Leave') }}</th>
                        <th style="text-align:center;">{{ __('Attendance %') }}</th>
                    </tr>
                </thead>
                <tbody id="dept-tbody">
                    <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-secondary);">{{ __('Loading...') }}</td></tr>
                </tbody>
                <tfoot id="dept-tfoot" style="display:none;">
                    <tr style="background:var(--bg-dark); font-weight:bold;">
                        <td colspan="4" style="text-align:right;">{{ __('DEPARTMENT SUMMARY') }}:</td>
                        <td class="tc" id="dept-present"></td>
                        <td class="tc" id="dept-late"></td>
                        <td class="tc" id="dept-absent"></td>
                        <td class="tc" id="dept-leave"></td>
                        <td class="tc" id="dept-rate" style="color:var(--success);"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Premium Print Footer --}}
HTML;

$content = preg_replace('/\{\{-- Tabs --\}\}.*?\{\{-- Premium Print Footer --\}\}/s', $newPanels, $content);

// 4. Update JavaScript
$jsStart = 'function switchTab(tab) {';
$jsEnd = 'function updateTeacherFilter() {';

$newJs = <<<JAVASCRIPT
function handleReportTypeChange() {
    const type = document.getElementById('reportTypeFilter').value;
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    document.getElementById('panel-' + type).style.display = 'block';
    
    // Auto trigger load if switching types (optional, but good UX)
    // loadAll();
}

async function loadAll() {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    const dept = document.getElementById('departmentFilter').value;
    const teacherId = document.getElementById('teacherFilter').value;
    const type = document.getElementById('reportTypeFilter').value;
    
    const params = new URLSearchParams({ from, to });
    if (dept) params.append('department', dept);
    if (teacherId) params.append('teacher_id', teacherId);

    // Print header
    const fmt = s => { const p = s.split('-'); return p.length===3 ? `\${p[2]}-\${p[1]}-\${p[0]}` : s; };
    document.getElementById('print-range').textContent = `\${fmt(from)} — \${fmt(to)}`;
    
    const deptEl = document.getElementById('print-dept-val');
    deptEl.textContent = window.transDept(dept) || '{{ __("All Departments") }}';

    const teacherEl = document.getElementById('print-teacher-val');
    const teacherSelect = document.getElementById('teacherFilter');
    if (teacherId) {
        teacherEl.textContent = teacherSelect.options[teacherSelect.selectedIndex].text;
    } else {
        teacherEl.textContent = '{{ __("All Teachers") }}';
    }

    const hud = document.getElementById('dataSyncHud');
    const hudStatus = document.getElementById('hudStatusText');
    const hudCount  = document.getElementById('hudRecordCount');
    if (hud) {
        hud.classList.add('loading');
        hudStatus.textContent = '{{ __("Fetching...") }}';
        hudCount.textContent  = '--';
    }

    try {
        if (type === 'daily') {
            await loadDaily(params);
        } else if (type === 'monthly') {
            await loadMonthly(params);
        } else if (type === 'absent') {
            await loadAbsent(params);
        } else if (type === 'late') {
            await loadLate(params);
        } else if (type === 'leave') {
            await loadLeave(params);
        } else if (type === 'individual') {
            await loadIndividual(params);
        } else if (type === 'department') {
            await loadDepartment(params);
        }
    } catch(e) {
        console.error('Error loading report data:', e);
    } finally {
        if (hud) {
            hud.classList.remove('loading');
            hudStatus.textContent = '{{ __("Data Synced") }}';
        }
    }
}

// Reuse skeletons for generic tables
function buildGenericSkeletons(cols = 5, rows = 5) {
    let html = '';
    for (let i = 0; i < rows; i++) {
        html += '<tr>';
        for (let j = 0; j < cols; j++) {
            html += `<td><div class="skeleton skeleton-text" style="width:80%;"></div></td>`;
        }
        html += '</tr>';
    }
    return html;
}

// ── 1. Daily Records ─────────────────────────────────
async function loadDaily(params) {
    const tbody = document.getElementById('report-tbody');
    tbody.innerHTML = buildGenericSkeletons(7, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports') }}?\${params.toString()}`);
        document.getElementById('sum-present').textContent = res.summary.present;
        document.getElementById('sum-late').textContent    = res.summary.late;
        document.getElementById('sum-absent').textContent  = res.summary.absent;
        
        const countEl = document.getElementById('hudRecordCount');
        if (countEl) countEl.textContent = res.records.length;

        tbody.innerHTML = '';
        if (res.records.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center" style="padding:2rem;color:var(--text-secondary);">{{ __('No records found for this period.') }}</td></tr>`;
        } else {
            let html = '';
            res.records.forEach((r, i) => {
                const bc = r.status === 'present' ? 'success' : (r.status === 'late' ? 'warning' : 'danger');
                const morningStatus = r.morning_status === 'late' ? 'warning' : 'success';
                const afternoonStatus = r.afternoon_status === 'late' ? 'warning' : 'success';
                const statusLabel = r.status === 'present' ? '{{ __("Present") }}' : (r.status === 'late' ? '{{ __("Late") }}' : '{{ __("Absent") }}');
                html += `
                <tr class="stagger-item" style="animation-delay: \${(i * 0.02).toFixed(2)}s">
                    <td><div style="font-weight:700;">\${r.date}</div></td>
                    <td>
                        <div style="font-weight:800; color: var(--primary); font-size: 1.05rem;">\${r.teacher.name_kh || ''}</div>
                        <div style="font-weight:700; opacity: 0.8;">\${r.teacher.name}</div>
                        <div style="font-size:0.72rem;color:var(--text-secondary);font-family:monospace;">\${r.teacher.employee_id}</div>
                    </td>
                    <td style="color:var(--text-secondary);font-size:0.8rem;font-weight:600;">\${window.transDept ? window.transDept(r.teacher.department) : r.teacher.department}</td>
                    <td>
                        <div class="time-pill \${morningStatus}">
                            <i class="ph ph-sun-dim"></i>
                            <span>\${r.morning_in ? `\${r.morning_in.split(' ')[0]} - \${r.morning_out ? r.morning_out.split(' ')[0] : '?'}` : '—'}</span>
                        </div>
                    </td>
                    <td>
                        <div class="time-pill \${afternoonStatus}">
                            <i class="ph ph-cloud-sun"></i>
                            <span>\${r.afternoon_in ? `\${r.afternoon_in.split(' ')[0]} - \${r.afternoon_out ? r.afternoon_out.split(' ')[0] : '?'}` : '—'}</span>
                        </div>
                    </td>
                    <td style="font-weight:800;color:var(--primary);">
                        \${r.working_hours||'0.0h'}
                    </td>
                    <td><span class="badge badge-\${bc}" style="border-radius:2rem; padding: 0.4rem 0.8rem;">\${statusLabel}</span></td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }
        renderChart(res.chart);
    } catch(e) { console.error(e); }
}

// ── 2. Monthly Attendance (formerly Summary) ─────────
async function loadMonthly(params) {
    const tbody = document.getElementById('monthly-tbody');
    tbody.innerHTML = buildGenericSkeletons(10, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.teacher-summary') }}?\${params.toString()}`);
        document.getElementById('sum-workdays').textContent = res.total_working_days;
        document.getElementById('monthly-period').textContent = `{{ __('Period') }}: \${res.period_from} — \${res.period_to}`;
        const countEl = document.getElementById('hudRecordCount');
        if (countEl) countEl.textContent = res.summary.length;
        
        tbody.innerHTML = '';
        if (res.summary.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:2rem;">{{ __('No data found.') }}</td></tr>`;
            return;
        }
        res.summary.forEach((t, i) => {
            // Need to pull 'leave' if API supports it, if not default 0 for now as the endpoint teacher-summary might not have it yet.
            const leave = t.days_leave || 0; 
            const rateColor = t.attendance_rate >= 80 ? 'var(--success)' : t.attendance_rate >= 50 ? 'var(--warning)' : 'var(--danger)';
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: \${(i * 0.03).toFixed(2)}s">
                <td class="tc">\${i + 1}</td>
                <td class="tc">\${t.employee_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary); font-size: 1rem;">\${t.name_kh || ''}</div>
                    <div style="font-weight:600; font-size: 0.9rem; opacity: 0.8;">\${t.name}</div>
                </td>
                <td style="color:var(--text-secondary);">\${window.transDept(t.department)}</td>
                <td class="tc"><span class="badge badge-success">\${t.days_present}</span></td>
                <td class="tc"><span class="badge badge-warning">\${t.days_late}</span></td>
                <td class="tc"><span class="badge badge-danger">\${t.days_absent}</span></td>
                <td class="tc"><span class="badge badge-info">\${leave}</span></td>
                <td class="tc">\${res.total_working_days}</td>
                <td class="tc" style="font-weight:bold;color:\${rateColor};">\${t.attendance_rate}%</td>
            </tr>`;
        });
    } catch(e) { console.error(e); }
}

// ── 3. Absent Report ─────────────────────────────────
async function loadAbsent(params) {
    const tbody = document.getElementById('absent-tbody');
    tbody.innerHTML = buildGenericSkeletons(8, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.absent') }}?\${params.toString()}`);
        document.getElementById('hudRecordCount').textContent = res.total;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;">{{ __('No absent records found.') }}</td></tr>`;
            return;
        }
        res.rows.forEach((r, i) => {
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: \${(i * 0.02).toFixed(2)}s">
                <td class="tc">\${i + 1}</td>
                <td class="tc">\${r.teacher_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary);">\${r.teacher_name_kh || ''}</div>
                    <div style="font-weight:600; opacity: 0.8;">\${r.teacher_name}</div>
                </td>
                <td>\${window.transDept(r.department)}</td>
                <td>\${r.absent_date}</td>
                <td>\${r.day_name}</td>
                <td><span class="badge badge-danger">\${r.status}</span></td>
                <td>\${r.remark}</td>
            </tr>`;
        });
    } catch(e) { console.error(e); }
}

// ── 4. Late Report ───────────────────────────────────
async function loadLate(params) {
    const tbody = document.getElementById('late-tbody');
    tbody.innerHTML = buildGenericSkeletons(8, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.late') }}?\${params.toString()}`);
        document.getElementById('hudRecordCount').textContent = res.total;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;">{{ __('No late records found.') }}</td></tr>`;
            return;
        }
        res.rows.forEach((r, i) => {
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: \${(i * 0.02).toFixed(2)}s">
                <td class="tc">\${i + 1}</td>
                <td class="tc">\${r.teacher_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary);">\${r.teacher_name_kh || ''}</div>
                    <div style="font-weight:600; opacity: 0.8;">\${r.teacher_name}</div>
                </td>
                <td>\${window.transDept(r.department)}</td>
                <td>\${r.date}</td>
                <td style="color:var(--warning);font-weight:bold;">\${r.check_in}</td>
                <td>\${r.expected_time}</td>
                <td><span class="badge badge-warning">\${r.late_minutes} min</span></td>
            </tr>`;
        });
    } catch(e) { console.error(e); }
}

// ── 5. Leave Report ──────────────────────────────────
async function loadLeave(params) {
    const tbody = document.getElementById('leave-tbody');
    tbody.innerHTML = buildGenericSkeletons(8, 5);
    try {
        const res = await window.fetchApi(`{{ route('api.reports.leave') }}?\${params.toString()}`);
        document.getElementById('hudRecordCount').textContent = res.total;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;">{{ __('No leave records found.') }}</td></tr>`;
            return;
        }
        res.rows.forEach((r, i) => {
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: \${(i * 0.02).toFixed(2)}s">
                <td class="tc">\${i + 1}</td>
                <td class="tc">\${r.teacher_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary);">\${r.teacher_name_kh || ''}</div>
                    <div style="font-weight:600; opacity: 0.8;">\${r.teacher_name}</div>
                </td>
                <td>\${window.transDept(r.department)}</td>
                <td>\${r.leave_date}</td>
                <td>\${r.leave_type}</td>
                <td><span class="badge badge-info">\${r.status}</span></td>
                <td>\${r.remark}</td>
            </tr>`;
        });
    } catch(e) { console.error(e); }
}

// ── 6. Individual Teacher Report ─────────────────────
async function loadIndividual(params) {
    const tbody = document.getElementById('indiv-tbody');
    const tfoot = document.getElementById('indiv-tfoot');
    tbody.innerHTML = buildGenericSkeletons(7, 5);
    tfoot.style.display = 'none';
    
    if (!params.get('teacher_id')) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;">{{ __('Please select a specific teacher from the dropdown.') }}</td></tr>`;
        document.getElementById('hudRecordCount').textContent = '0';
        return;
    }
    
    try {
        const res = await window.fetchApi(`{{ route('api.reports.individual') }}?\${params.toString()}`);
        if (res.error) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;">\${res.error}</td></tr>`;
            return;
        }
        document.getElementById('hudRecordCount').textContent = res.rows.length;
        
        document.getElementById('indiv-title').innerHTML = `<i class="ph ph-user-focus" style="margin-right:0.4rem;"></i>\${res.teacher.name} (\${res.teacher.employee_id}) - \${window.transDept(res.teacher.department)}`;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;">{{ __('No records found.') }}</td></tr>`;
            return;
        }
        res.rows.forEach((r, i) => {
            const bc = r.status === 'present' ? 'success' : (r.status === 'late' ? 'warning' : (r.status === 'leave' ? 'info' : 'danger'));
            const statusLabel = r.status.charAt(0).toUpperCase() + r.status.slice(1);
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: \${(i * 0.02).toFixed(2)}s">
                <td>\${r.date}</td>
                <td>\${r.day_name}</td>
                <td>\${r.check_in}</td>
                <td>\${r.check_out}</td>
                <td style="font-weight:bold;color:var(--primary);">\${r.working_hours}</td>
                <td><span class="badge badge-\${bc}">\${statusLabel}</span></td>
                <td>\${r.remark}</td>
            </tr>`;
        });
        
        // Show summary footer
        tfoot.style.display = '';
        document.getElementById('indiv-hours').textContent = res.summary.total_hours;
        document.getElementById('indiv-summary').innerHTML = `
            <span style="color:var(--success)">P: \${res.summary.present}</span> | 
            <span style="color:var(--warning)">L: \${res.summary.late}</span> | 
            <span style="color:var(--danger)">A: \${res.summary.absent}</span> | 
            <span style="color:var(--info)">Lv: \${res.summary.leave}</span> | 
            <span style="color:var(--text-primary);font-weight:900;">Rate: \${res.summary.attendance_rate}%</span>
        `;
    } catch(e) { console.error(e); }
}

// ── 7. Department Report ─────────────────────────────
async function loadDepartment(params) {
    const tbody = document.getElementById('dept-tbody');
    const tfoot = document.getElementById('dept-tfoot');
    tbody.innerHTML = buildGenericSkeletons(9, 5);
    tfoot.style.display = 'none';
    
    try {
        const res = await window.fetchApi(`{{ route('api.reports.department') }}?\${params.toString()}`);
        document.getElementById('hudRecordCount').textContent = res.rows.length;
        document.getElementById('dept-period').textContent = `{{ __('Period') }}: \${res.period_from} — \${res.period_to}`;
        
        tbody.innerHTML = '';
        if (res.rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:2rem;">{{ __('No records found.') }}</td></tr>`;
            return;
        }
        res.rows.forEach((r, i) => {
            const rateColor = r.attendance_rate >= 80 ? 'var(--success)' : r.attendance_rate >= 50 ? 'var(--warning)' : 'var(--danger)';
            tbody.innerHTML += `
            <tr class="stagger-item" style="animation-delay: \${(i * 0.02).toFixed(2)}s">
                <td class="tc">\${i + 1}</td>
                <td class="tc">\${r.teacher_id}</td>
                <td>
                    <div style="font-weight:700; color: var(--primary);">\${r.teacher_name_kh || ''}</div>
                    <div style="font-weight:600; opacity: 0.8;">\${r.teacher_name}</div>
                </td>
                <td>\${window.transDept(r.department)}</td>
                <td class="tc"><span class="badge badge-success">\${r.present}</span></td>
                <td class="tc"><span class="badge badge-warning">\${r.late}</span></td>
                <td class="tc"><span class="badge badge-danger">\${r.absent}</span></td>
                <td class="tc"><span class="badge badge-info">\${r.leave}</span></td>
                <td class="tc" style="font-weight:bold;color:\${rateColor};">\${r.attendance_rate}%</td>
            </tr>`;
        });
        
        // Show summary footer
        tfoot.style.display = '';
        document.getElementById('dept-present').textContent = res.summary.total_present;
        document.getElementById('dept-late').textContent = res.summary.total_late;
        document.getElementById('dept-absent').textContent = res.summary.total_absent;
        document.getElementById('dept-leave').textContent = res.summary.total_leave;
        document.getElementById('dept-rate').textContent = res.summary.overall_rate + '%';
    } catch(e) { console.error(e); }
}

function updateTeacherFilter() {
JAVASCRIPT;

// Replace JS section
$content = preg_replace('/function switchTab\(tab\).*?function updateTeacherFilter\(\) \{/s', $newJs, $content);

// 5. Update exportPdf and exportExcel
$exportStart = 'function exportPdf() {';
$exportEnd = "window.location.href = `{{ route('api.reports.export') }}?\${params.toString()}`;
}";

$newExportJs = <<<JAVASCRIPT
function exportPdf() { 
    const originalTitle = document.title;
    document.title = ''; 
    
    // The active panel is determined by the dropdown
    const type = document.getElementById('reportTypeFilter').value;
    const activePanel = document.getElementById('panel-' + type);
    activePanel.classList.add('print-active');
    
    setTimeout(() => {
        window.print(); 
        activePanel.classList.remove('print-active');
        document.title = originalTitle;
    }, 100);
}

function exportExcel() {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    const dept = document.getElementById('departmentFilter').value;
    const teacherId = document.getElementById('teacherFilter').value;
    const type = document.getElementById('reportTypeFilter').value;
    
    const params = new URLSearchParams({ from, to, type });
    if (dept) params.append('department', dept);
    if (teacherId) params.append('teacher_id', teacherId);
    
    window.location.href = `{{ route('api.reports.export') }}?\${params.toString()}`;
}
JAVASCRIPT;

$content = preg_replace('/function exportPdf\(\) \{.*window\.location\.href \= `\{\{ route\(\'api\.reports\.export\'\) \}\}\?\$\{params\.toString\(\)\}`;\\s*\}/s', $newExportJs, $content);

file_put_contents($file, $content);
echo "Blade file updated successfully.";
?>
