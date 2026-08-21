<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->getData($request);
        }
        $departments = Department::all();
        $teachers = Teacher::orderBy('name')->get();
        return view('reports.index', compact('departments', 'teachers'));
    }

    public function getData(Request $request): JsonResponse
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : Carbon::now();

        $query = Attendance::with('teacher')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        if ($request->department) {
            $query->whereHas('teacher', fn($q) => $q->where('department', $request->department));
        }
        if ($request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $records = $query->orderBy('date')->get();

        // Get total active teachers for context (absent calculation)
        if ($request->teacher_id) {
            $totalActiveTeachers = 1;
        } else {
            $teacherQuery = Teacher::query();
            if ($request->department) {
                $teacherQuery->where('department', $request->department);
            }
            $totalActiveTeachers = $teacherQuery->count();
        }

        // Calculate working days (Mon-Sat, exclude holidays)
        $holidays = \App\Models\Holiday::whereBetween('date', [$from->toDateString(), $to->toDateString()])->pluck('date')->toArray();
        $period = $from->toPeriod($to, '1 day');
        $workingDays = 0;
        foreach ($period as $day) {
            if ($day->dayOfWeek !== Carbon::SUNDAY && !in_array($day->toDateString(), $holidays)) {
                $workingDays++;
            }
        }
        $workingDays = max(1, $workingDays);

        // Group by date for chart
        $byDate = $records->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->date)->toDateString();
        });
        $chartData = $byDate->map(function ($dayRecords, $date) use ($totalActiveTeachers) {
            $present = 0; $late = 0;
            foreach($dayRecords as $r) {
                if ($r->morning_status == 'late' || $r->afternoon_status == 'late') {
                    $late++;
                } else {
                    $present++;
                }
            }
            $absent = max(0, $totalActiveTeachers - ($present + $late));
            return [
                'date' => $date,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
            ];
        })->values();

        // Summary stats
        $totalPresent = 0; $totalLate = 0;
        foreach($records as $r) {
            if ($r->morning_status == 'late' || $r->afternoon_status == 'late') {
                $totalLate++;
            } else {
                $totalPresent++;
            }
        }
        $totalAbsent = max(0, ($totalActiveTeachers * $workingDays) - ($totalPresent + $totalLate));

        $summary = [
            'total_records' => $records->count(),
            'present' => $totalPresent,
            'late' => $totalLate,
            'absent' => $totalAbsent,
            'working_days' => $workingDays,
        ];

        return response()->json([
            'records' => $records->map(function ($r) {
                $totalMins = 0;
                if ($r->morning_in && $r->morning_out) {
                    $totalMins += Carbon::createFromTimeString($r->morning_in)->diffInMinutes(Carbon::createFromTimeString($r->morning_out));
                }
                if ($r->afternoon_in && $r->afternoon_out) {
                    $totalMins += Carbon::createFromTimeString($r->afternoon_in)->diffInMinutes(Carbon::createFromTimeString($r->afternoon_out));
                }
                
                $workHours = $totalMins > 0 ? floor($totalMins / 60) . 'h ' . ($totalMins % 60) . 'm' : '—';
                
                $status = 'absent';
                if ($r->morning_status == 'late' || $r->afternoon_status == 'late') {
                    $status = 'late';
                } elseif ($r->morning_status == 'present' || $r->afternoon_status == 'present') {
                    $status = 'present';
                }

                $source = 'RFID';
                if ($r->rfid_uid === 'Manual') {
                    $source = 'Manual';
                } elseif (str_contains($r->rfid_uid ?? '', 'Edited')) {
                    $source = 'Edited';
                }

                return [
                    'id'               => $r->id,
                    'date'             => Carbon::parse($r->date)->format('d-m-Y'),
                    'morning_in'       => $r->morning_in ? Carbon::parse($r->morning_in)->format('h:i A') : null,
                    'morning_out'      => $r->morning_out ? Carbon::parse($r->morning_out)->format('h:i A') : null,
                    'morning_status'   => $r->morning_status,
                    'afternoon_in'     => $r->afternoon_in ? Carbon::parse($r->afternoon_in)->format('h:i A') : null,
                    'afternoon_out'    => $r->afternoon_out ? Carbon::parse($r->afternoon_out)->format('h:i A') : null,
                    'afternoon_status' => $r->afternoon_status,
                    'status'           => $status,
                    'source'           => $source,
                    'rfid_uid'         => $r->rfid_uid,
                    'working_hours'    => $workHours,
                    'manual_note'      => $r->manual_note,
                    'teacher' => [
                        'id'          => $r->teacher->id ?? null,
                        'name'        => $r->teacher->name ?? 'N/A',
                        'name_kh'     => $r->teacher->name_kh ?? '',
                        'department'  => $r->teacher->department ?? '',
                        'employee_id' => $r->teacher->employee_id ?? '',
                    ],
                ];
            })->values(),
            'chart'   => $chartData,
            'summary' => $summary,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::now();

        $holidays = \App\Models\Holiday::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('date')->toArray();

        $query = Attendance::with('teacher')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        if ($request->department) {
            $query->whereHas('teacher', fn($q) => $q->where('department', $request->department));
        }
        if ($request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }
        $records = $query->get()
            ->groupBy(fn($r) => \Carbon\Carbon::parse($r->date)->toDateString())
            ->map(fn($day) => $day->keyBy('teacher_id'));

        $teacherQuery = Teacher::query();
        if ($request->department) $teacherQuery->where('department', $request->department);
        if ($request->teacher_id) $teacherQuery->where('id', $request->teacher_id);
        $allTeachers = $teacherQuery->orderBy('name')->get();

        $depts    = Department::all()->keyBy('name');
        $uName    = \App\Models\Setting::getValue('university_name', 'NTTI System');
        $isKm     = app()->getLocale() == 'km';
        $exportType = $request->type ?? 'daily';
        $filename = "attendance_{$exportType}_report_{$from->format('Ymd')}_to_{$to->format('Ymd')}.xls";

        // Working days (Mon–Sat, exclude holidays)
        $dateRange = [];
        $cur = $from->copy();
        while ($cur <= $to) {
            if ($cur->dayOfWeek !== Carbon::SUNDAY && !in_array($cur->toDateString(), $holidays)) {
                $dateRange[] = $cur->copy();
            }
            $cur->addDay();
        }
        $totalWorkingDays = count($dateRange);

        $deptLabel = $request->department
            ? ($isKm && isset($depts[$request->department]) ? $depts[$request->department]->name_kh : $request->department)
            : __('All Departments');

        // Per-teacher stats accumulated while building daily rows
        $teacherStats = [];
        foreach ($allTeachers as $t) {
            $teacherStats[$t->id] = ['teacher' => $t, 'days_present' => 0, 'days_late' => 0, 'days_absent' => 0, 'total_mins' => 0];
        }

        // ── HTML/XLS header ──────────────────────────────────────────────
        $thBlue  = 'style="background-color:#1a73e8;color:#fff;text-align:center;padding:6px;"';
        $thGreen = 'style="background-color:#34a853;color:#fff;text-align:center;padding:6px;"';

        $html  = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>Attendance</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        $html .= '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>';
        $html .= 'body{font-family:"Khmer OS Battambang","DaunPenh",Arial,sans-serif;}';
        $html .= '.h-title{font-size:18pt;font-weight:bold;color:#1a73e8;}';
        $html .= '.h-sub{font-size:13pt;font-weight:bold;}';
        $html .= '.s-label{font-size:11pt;font-weight:bold;}';
        $html .= '.tc{text-align:center;}';
        $html .= '.present{color:#34a853;font-weight:bold;}';
        $html .= '.late{color:#f59e0b;font-weight:bold;}';
        $html .= '.absent{color:#ea4335;font-weight:bold;}';
        $html .= 'th{border:0.5pt solid #ccc;padding:10pt;font-size:11pt;vertical-align:middle;height:30px;}';
        $html .= 'td{border:0.5pt solid #ccc;padding:8pt;font-size:10pt;vertical-align:middle;}';
        $html .= '</style></head><body>';

        $reportTypeLabels = [
            'daily'      => __('Daily Attendance Report'),
            'monthly'    => __('Monthly Attendance Report'),
            'absent'     => __('Absent Report'),
            'late'       => __('Late Report'),
            'leave'      => __('Leave Report'),
            'individual' => __('Individual Teacher Report'),
            'department' => __('Department Report'),
        ];
        $reportTypeLabel = $reportTypeLabels[$exportType] ?? __('Attendance Report');

        // Document header
        $html .= '<table border="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<tr><td colspan="9" class="tc"><div class="h-title">' . htmlspecialchars($uName) . '</div></td></tr>';
        $html .= '<tr><td colspan="9" class="tc"><div class="h-sub">' . $reportTypeLabel . '</div></td></tr>';
        $html .= '<tr><td colspan="9" class="tc">' . __('Period') . ': ' . $from->format('d-m-Y') . ' ' . __('to') . ' ' . $to->format('d-m-Y') . '</td></tr>';
        $html .= '<tr><td colspan="9" class="tc" style="color:#555;">';
        $html .= __('Department') . ': ' . htmlspecialchars($deptLabel);
        $html .= ' &nbsp;|&nbsp; ' . __('Working Days') . ': ' . $totalWorkingDays;
        $html .= ' &nbsp;|&nbsp; ' . __('Export Date') . ': ' . now()->format('d-m-Y H:i');
        $html .= '</td></tr>';
        $html .= '<tr><td colspan="9" style="border:none;height:12pt;"></td></tr>';
        $html .= '</table>';

        // $exportType is already set above
        
        // Define common table header style
        $thStyle = 'style="background-color:#1a73e8;color:#fff;text-align:center;padding:10px;height:35px;"';
        
        switch ($exportType) {
            case 'monthly':
                $html .= '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;">';
                $html .= '<tr>';
                $html .= '<th ' . $thStyle . ' width="40">#</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Teacher ID') . '</th>';
                $html .= '<th ' . $thStyle . ' style="min-width:220px;white-space:nowrap;">' . __('Teacher Name') . '</th>';
                $html .= '<th ' . $thStyle . ' style="min-width:180px;white-space:nowrap;">' . __('Department') . '</th>';
                $html .= '<th ' . $thStyle . ' width="80">' . __('Present') . '</th>';
                $html .= '<th ' . $thStyle . ' width="80">' . __('Late') . '</th>';
                $html .= '<th ' . $thStyle . ' width="80">' . __('Absent') . '</th>';
                $html .= '<th ' . $thStyle . ' width="110">' . __('Working Days') . '</th>';
                $html .= '<th ' . $thStyle . ' width="130">' . __('Working Hours') . '</th>';
                $html .= '<th ' . $thStyle . ' width="90">' . __('Attendance %') . '</th>';
                $html .= '</tr>';
                
                $sRow = 1;
                foreach ($teacherStats as $stat) {
                    $t = $stat['teacher'];
                    $deptName = $isKm && isset($depts[$t->department]) ? $depts[$t->department]->name_kh : $t->department;
                    
                    $present = $late = 0;
                    $totalTeacherMins = 0;
                    foreach ($dateRange as $date) {
                        $rec = $records->get($date->toDateString(), collect())->get($t->id);
                        if ($rec) {
                            if ($rec->morning_in || $rec->afternoon_in) {
                                if ($rec->morning_status === 'late' || $rec->afternoon_status === 'late') $late++;
                                else $present++;
                            }
                            if ($rec->morning_in && $rec->morning_out) {
                                $totalTeacherMins += \Carbon\Carbon::createFromTimeString($rec->morning_in)
                                    ->diffInMinutes(\Carbon\Carbon::createFromTimeString($rec->morning_out));
                            }
                            if ($rec->afternoon_in && $rec->afternoon_out) {
                                $totalTeacherMins += \Carbon\Carbon::createFromTimeString($rec->afternoon_in)
                                    ->diffInMinutes(\Carbon\Carbon::createFromTimeString($rec->afternoon_out));
                            }
                        }
                    }
                    $absent = max(0, $totalWorkingDays - $present - $late);
                    $rate = $totalWorkingDays > 0 ? round(($present + $late) / $totalWorkingDays * 100, 1) : 0;
                    $workHoursLabel = $totalTeacherMins > 0
                        ? floor($totalTeacherMins / 60) . 'h ' . ($totalTeacherMins % 60) . 'm'
                        : '0h 0m';
                    
                    $html .= '<tr>';
                    $html .= '<td class="tc">' . $sRow++ . '</td>';
                    $html .= '<td class="tc" style="mso-number-format:\'\@\';">' . htmlspecialchars($t->employee_id ?? '') . '</td>';
                    $html .= '<td style="white-space:nowrap;min-width:220px;">' . htmlspecialchars($t->name) . '</td>';
                    $html .= '<td style="white-space:nowrap;min-width:180px;">' . htmlspecialchars($deptName ?? '') . '</td>';
                    $html .= '<td class="tc">' . $present . '</td>';
                    $html .= '<td class="tc">' . $late . '</td>';
                    $html .= '<td class="tc">' . $absent . '</td>';
                    $html .= '<td class="tc">' . $totalWorkingDays . '</td>';
                    $html .= '<td class="tc" style="font-weight:bold;color:#1a73e8;">' . $workHoursLabel . '</td>';
                    $html .= '<td class="tc">' . $rate . '%</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                break;
                
            case 'absent':
                $html .= '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;">';
                $html .= '<tr>';
                $html .= '<th ' . $thStyle . ' width="40">No.</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Teacher ID') . '</th>';
                $html .= '<th ' . $thStyle . ' width="200">' . __('Teacher Name') . '</th>';
                $html .= '<th ' . $thStyle . ' width="150">' . __('Department') . '</th>';
                $html .= '<th ' . $thStyle . ' width="120">' . __('Absent Date') . '</th>';
                $html .= '<th ' . $thStyle . ' width="120">' . __('Day') . '</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Status') . '</th>';
                $html .= '<th ' . $thStyle . ' width="150">' . __('Remark') . '</th>';
                $html .= '</tr>';
                
                $rowNum = 1;
                foreach ($allTeachers as $t) {
                    $deptName = $isKm && isset($depts[$t->department]) ? $depts[$t->department]->name_kh : $t->department;
                    foreach ($dateRange as $date) {
                        $rec = $records->get($date->toDateString(), collect())->get($t->id);
                        $isAbsent = !$rec || (!$rec->morning_in && !$rec->afternoon_in);
                        $isLeave  = $rec && ($rec->morning_status === 'leave' || $rec->afternoon_status === 'leave');
                        if ($isAbsent && !$isLeave) {
                            $html .= '<tr>';
                            $html .= '<td class="tc">' . $rowNum++ . '</td>';
                            $html .= '<td class="tc" style="mso-number-format:\'\\@\';">' . htmlspecialchars($t->employee_id ?? '') . '</td>';
                            $html .= '<td>' . htmlspecialchars($t->name) . '</td>';
                            $html .= '<td class="tc">' . htmlspecialchars($deptName ?? '') . '</td>';
                            $html .= '<td class="tc">' . $date->format('d/m/Y') . '</td>';
                            $html .= '<td class="tc">' . $date->format('l') . '</td>';
                            $html .= '<td class="tc absent">' . __('Absent') . '</td>';
                            $html .= '<td>' . htmlspecialchars($rec->manual_note ?? '-') . '</td>';
                            $html .= '</tr>';
                        }
                    }
                }
                $html .= '</table>';
                break;
                
            case 'late':
                $html .= '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;">';
                $html .= '<tr>';
                $html .= '<th ' . $thStyle . ' width="40">No.</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Teacher ID') . '</th>';
                $html .= '<th ' . $thStyle . ' width="200">' . __('Teacher Name') . '</th>';
                $html .= '<th ' . $thStyle . ' width="150">' . __('Department') . '</th>';
                $html .= '<th ' . $thStyle . ' width="120">' . __('Date') . '</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Check In') . '</th>';
                $html .= '<th ' . $thStyle . ' width="120">' . __('Expected Time') . '</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Late Minutes') . '</th>';
                $html .= '</tr>';
                
                $rowNum = 1;
                $morningCutoff = \App\Models\Setting::getValue('morning_late_cutoff', '07:45');
                $afternoonCutoff = \App\Models\Setting::getValue('afternoon_late_cutoff', '14:15');
                
                foreach ($dateRange as $date) {
                    $dayRecs = $records->get($date->toDateString(), collect());
                    foreach ($allTeachers as $t) {
                        $rec = $dayRecs->get($t->id);
                        if ($rec && ($rec->morning_status === 'late' || $rec->afternoon_status === 'late')) {
                            $deptName = $isKm && isset($depts[$t->department]) ? $depts[$t->department]->name_kh : $t->department;
                            
                            $session = 'Morning'; $checkIn = $rec->morning_in; $cutoff = $morningCutoff;
                            if ($rec->morning_status !== 'late' && $rec->afternoon_status === 'late') {
                                $session = 'Afternoon'; $checkIn = $rec->afternoon_in; $cutoff = $afternoonCutoff;
                            }
                            $lateMins = $checkIn ? max(0, Carbon::createFromTimeString($checkIn)->diffInSeconds(Carbon::createFromTimeString($cutoff), false) * -1 / 60) : 0;
                            $lateMins = number_format($lateMins, 2, '.', '');
                            
                            $html .= '<tr>';
                            $html .= '<td class="tc">' . $rowNum++ . '</td>';
                            $html .= '<td class="tc" style="mso-number-format:\'\\@\';">' . htmlspecialchars($t->employee_id ?? '') . '</td>';
                            $html .= '<td>' . htmlspecialchars($t->name) . '</td>';
                            $html .= '<td class="tc">' . htmlspecialchars($deptName ?? '') . '</td>';
                            $html .= '<td class="tc">' . $date->format('d/m/Y') . '</td>';
                            $html .= '<td class="tc">' . ($checkIn ? Carbon::createFromTimeString($checkIn)->format('H:i') : '-') . '</td>';
                            $html .= '<td class="tc">' . $cutoff . '</td>';
                            $html .= '<td class="tc late">' . $lateMins . '</td>';
                            $html .= '</tr>';
                        }
                    }
                }
                $html .= '</table>';
                break;
                
            case 'leave':
                $html .= '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;">';
                $html .= '<tr>';
                $html .= '<th ' . $thStyle . ' width="40">No.</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Teacher ID') . '</th>';
                $html .= '<th ' . $thStyle . ' width="200">' . __('Teacher Name') . '</th>';
                $html .= '<th ' . $thStyle . ' width="150">' . __('Department') . '</th>';
                $html .= '<th ' . $thStyle . ' width="120">' . __('Leave Date') . '</th>';
                $html .= '<th ' . $thStyle . ' width="120">' . __('Leave Type') . '</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Status') . '</th>';
                $html .= '<th ' . $thStyle . ' width="150">' . __('Remark') . '</th>';
                $html .= '</tr>';
                
                $rowNum = 1;
                foreach ($dateRange as $date) {
                    $dayRecs = $records->get($date->toDateString(), collect());
                    foreach ($allTeachers as $t) {
                        $rec = $dayRecs->get($t->id);
                        if ($rec && ($rec->morning_status === 'leave' || $rec->afternoon_status === 'leave')) {
                            $deptName = $isKm && isset($depts[$t->department]) ? $depts[$t->department]->name_kh : $t->department;
                            $leaveType = ($rec->morning_status === 'leave' && $rec->afternoon_status === 'leave') ? 'Full Day' : (($rec->morning_status === 'leave') ? 'Morning' : 'Afternoon');
                            
                            $html .= '<tr>';
                            $html .= '<td class="tc">' . $rowNum++ . '</td>';
                            $html .= '<td class="tc" style="mso-number-format:\'\\@\';">' . htmlspecialchars($t->employee_id ?? '') . '</td>';
                            $html .= '<td>' . htmlspecialchars($t->name) . '</td>';
                            $html .= '<td class="tc">' . htmlspecialchars($deptName ?? '') . '</td>';
                            $html .= '<td class="tc">' . $date->format('d/m/Y') . '</td>';
                            $html .= '<td class="tc">' . $leaveType . '</td>';
                            $html .= '<td class="tc present">' . __('Leave') . '</td>';
                            $html .= '<td>' . htmlspecialchars($rec->manual_note ?? '-') . '</td>';
                            $html .= '</tr>';
                        }
                    }
                }
                $html .= '</table>';
                break;
                
            case 'individual':
                if (!$request->teacher_id) {
                    $html .= '<h3>Please select a teacher.</h3>';
                    break;
                }
                $teacher = Teacher::find($request->teacher_id);
                $deptName = $isKm && isset($depts[$teacher->department]) ? $depts[$teacher->department]->name_kh : $teacher->department;
                
                $html .= '<h3>Teacher: ' . htmlspecialchars($teacher->name) . ' (' . htmlspecialchars($teacher->employee_id) . ') - ' . htmlspecialchars($deptName) . '</h3>';
                $html .= '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;">';
                $html .= '<tr>';
                $html .= "<th $thStyle>" . __('Date') . '</th>';
                $html .= "<th $thStyle>" . __('Day') . '</th>';
                $html .= "<th $thStyle>" . __('Check In') . '</th>';
                $html .= "<th $thStyle>" . __('Check Out') . '</th>';
                $html .= "<th $thStyle>" . __('Working Hours') . '</th>';
                $html .= "<th $thStyle>" . __('Status') . '</th>';
                $html .= "<th $thStyle>" . __('Remark') . '</th>';
                $html .= '</tr>';
                
                $tRecs = $records->map(fn($day) => $day->get($teacher->id));
                $daysPresent = $daysLate = $daysAbsent = $daysLeave = $totalMins = 0;
                
                foreach ($dateRange as $date) {
                    $rec = $tRecs->get($date->toDateString());
                    if (!$rec || (!$rec->morning_in && !$rec->afternoon_in)) {
                        $isLeave = $rec && ($rec->morning_status === 'leave' || $rec->afternoon_status === 'leave');
                        if ($isLeave) { $status = 'Leave'; $daysLeave++; $cls = 'present'; }
                        else { $status = 'Absent'; $daysAbsent++; $cls = 'absent'; }
                        
                        $html .= '<tr>';
                        $html .= '<td class="tc">' . $date->format('d/m/Y') . '</td>';
                        $html .= '<td class="tc">' . $date->format('l') . '</td>';
                        $html .= '<td class="tc">-</td>';
                        $html .= '<td class="tc">-</td>';
                        $html .= '<td class="tc">-</td>';
                        $html .= "<td class=\"tc $cls\">" . __($status) . '</td>';
                        $html .= '<td>' . htmlspecialchars($rec->manual_note ?? '-') . '</td>';
                        $html .= '</tr>';
                    } else {
                        $mins = 0;
                        if ($rec->morning_in && $rec->morning_out) $mins += Carbon::createFromTimeString($rec->morning_in)->diffInMinutes(Carbon::createFromTimeString($rec->morning_out));
                        if ($rec->afternoon_in && $rec->afternoon_out) $mins += Carbon::createFromTimeString($rec->afternoon_in)->diffInMinutes(Carbon::createFromTimeString($rec->afternoon_out));
                        $totalMins += $mins;
                        
                        $status = ($rec->morning_status === 'late' || $rec->afternoon_status === 'late') ? 'Late' : 'Present';
                        $cls = $status === 'Late' ? 'late' : 'present';
                        if ($status === 'Late') $daysLate++; else $daysPresent++;
                        
                        $checkIn = $rec->morning_in ? Carbon::createFromTimeString($rec->morning_in)->format('H:i') : ($rec->afternoon_in ? Carbon::createFromTimeString($rec->afternoon_in)->format('H:i') : '-');
                        $checkOut = $rec->afternoon_out ? Carbon::createFromTimeString($rec->afternoon_out)->format('H:i') : ($rec->morning_out ? Carbon::createFromTimeString($rec->morning_out)->format('H:i') : '-');
                        
                        $html .= '<tr>';
                        $html .= '<td class="tc">' . $date->format('d/m/Y') . '</td>';
                        $html .= '<td class="tc">' . $date->format('l') . '</td>';
                        $html .= '<td class="tc">' . $checkIn . '</td>';
                        $html .= '<td class="tc">' . $checkOut . '</td>';
                        $html .= '<td class="tc">' . ($mins > 0 ? floor($mins/60).'h '.($mins%60).'m' : '-') . '</td>';
                        $html .= "<td class=\"tc $cls\">" . __($status) . '</td>';
                        $html .= '<td>' . htmlspecialchars($rec->manual_note ?? '-') . '</td>';
                        $html .= '</tr>';
                    }
                }
                
                // Summary row
                $rate = $totalWorkingDays > 0 ? round(($daysPresent + $daysLate) / $totalWorkingDays * 100, 1) : 0;
                $html .= '<tr style="background:#f1f5f9;font-weight:bold;">';
                $html .= '<td colspan="4" class="tc">TOTAL (' . $totalWorkingDays . ' Working Days)</td>';
                $html .= '<td class="tc">' . floor($totalMins/60) . 'h ' . ($totalMins%60) . 'm</td>';
                $html .= '<td colspan="2" class="tc">Present: ' . $daysPresent . ' | Late: ' . $daysLate . ' | Absent: ' . $daysAbsent . ' | Leave: ' . $daysLeave . ' | Rate: ' . $rate . '%</td>';
                $html .= '</tr>';
                $html .= '</table>';
                break;
                
            case 'department':
                $html .= '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;">';
                $html .= '<tr>';
                $html .= '<th ' . $thStyle . ' width="40">No.</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Teacher ID') . '</th>';
                $html .= '<th ' . $thStyle . ' width="200">' . __('Teacher Name') . '</th>';
                $html .= '<th ' . $thStyle . ' width="150">' . __('Department') . '</th>';
                $html .= '<th ' . $thStyle . ' width="80">' . __('Present') . '</th>';
                $html .= '<th ' . $thStyle . ' width="80">' . __('Late') . '</th>';
                $html .= '<th ' . $thStyle . ' width="80">' . __('Absent') . '</th>';
                $html .= '<th ' . $thStyle . ' width="80">' . __('Leave') . '</th>';
                $html .= '<th ' . $thStyle . ' width="100">' . __('Attendance %') . '</th>';
                $html .= '</tr>';
                
                $rowNum = 1;
                $totPresent = $totLate = $totAbsent = $totLeave = 0;
                
                foreach ($allTeachers as $t) {
                    $deptName = $isKm && isset($depts[$t->department]) ? $depts[$t->department]->name_kh : $t->department;
                    $present = $late = $leave = 0;
                    
                    foreach ($dateRange as $date) {
                        $rec = $records->get($date->toDateString(), collect())->get($t->id);
                        if ($rec) {
                            if ($rec->morning_status === 'leave' || $rec->afternoon_status === 'leave') {
                                $leave++;
                            } elseif ($rec->morning_in || $rec->afternoon_in) {
                                if ($rec->morning_status === 'late' || $rec->afternoon_status === 'late') $late++;
                                else $present++;
                            }
                        }
                    }
                    $absent = max(0, $totalWorkingDays - $present - $late - $leave);
                    $rate = $totalWorkingDays > 0 ? round(($present + $late) / $totalWorkingDays * 100, 1) : 0;
                    
                    $totPresent += $present; $totLate += $late; $totAbsent += $absent; $totLeave += $leave;
                    
                    $html .= '<tr>';
                    $html .= '<td class="tc">' . $rowNum++ . '</td>';
                    $html .= '<td class="tc" style="mso-number-format:\'\\@\';">' . htmlspecialchars($t->employee_id ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($t->name) . '</td>';
                    $html .= '<td class="tc">' . htmlspecialchars($deptName ?? '') . '</td>';
                    $html .= '<td class="tc">' . $present . '</td>';
                    $html .= '<td class="tc">' . $late . '</td>';
                    $html .= '<td class="tc">' . $absent . '</td>';
                    $html .= '<td class="tc">' . $leave . '</td>';
                    $html .= '<td class="tc">' . $rate . '%</td>';
                    $html .= '</tr>';
                }
                
                // Dept Summary
                $totTeachers = count($allTeachers);
                $totalPossible = $totTeachers * $totalWorkingDays;
                $overallRate = $totalPossible > 0 ? round(($totPresent + $totLate) / $totalPossible * 100, 1) : 0;
                
                $html .= '<tr style="background:#e8f0fe;font-weight:bold;">';
                $html .= '<td colspan="4" class="tc">DEPARTMENT SUMMARY (' . $totTeachers . ' Teachers)</td>';
                $html .= '<td class="tc">' . $totPresent . '</td>';
                $html .= '<td class="tc">' . $totLate . '</td>';
                $html .= '<td class="tc">' . $totAbsent . '</td>';
                $html .= '<td class="tc">' . $totLeave . '</td>';
                $html .= '<td class="tc">' . $overallRate . '%</td>';
                $html .= '</tr>';
                
                $html .= '</table>';
                break;
                
            case 'daily':
            default:
            // ── Section 1: Daily Records ─────────────────────────────────────
            $html .= '<table border="0"><tr><td style="border:none;padding:4pt 0;">';
            $html .= '<span class="s-label" style="color:#1a73e8;">&#9654; ' . __('Daily Attendance Records') . '</span>';
            $html .= '</td></tr></table>';

            $html .= '<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;">';
            $html .= '<tr>';
            $html .= "<th $thBlue width=\"40\">#</th>";
            $html .= "<th $thBlue width=\"100\">" . __('Date') . '</th>';
            $html .= "<th $thBlue width=\"100\">" . __('Teacher ID') . '</th>';
            $html .= "<th $thBlue width=\"200\">" . __('Teacher Name') . '</th>';
            $html .= "<th $thBlue width=\"150\">" . __('Department') . '</th>';
            $html .= "<th $thBlue width=\"150\">" . __('Morning Session') . '</th>';
            $html .= "<th $thBlue width=\"150\">" . __('Afternoon Session') . '</th>';
            $html .= "<th $thBlue width=\"120\">" . __('Working Hours') . '</th>';
            $html .= "<th $thBlue width=\"100\">" . __('Status') . '</th>';
            $html .= "<th $thBlue width=\"100\">" . __('Source') . '</th>';
            $html .= '</tr>';
        }

        $rowNum = 1;
        $totPresent = 0;
        $totLate    = 0;
        $totAbsent  = 0;

        foreach ($dateRange as $date) {
            $dayRecords = $records->get($date->toDateString(), collect());

            foreach ($allTeachers as $teacher) {
                $record   = $dayRecords->get($teacher->id);
                $deptName = $isKm && isset($depts[$teacher->department]) ? $depts[$teacher->department]->name_kh : $teacher->department;
                $rowBg    = ($rowNum % 2 === 0) ? '#f3f4f6' : '#ffffff';

                // Build teacher name cell
                $nameTd = '';
                if ($teacher->name_kh) {
                    $nameTd .= '<span style="font-weight:bold;color:#1a73e8;">' . htmlspecialchars($teacher->name_kh) . '</span><br style="mso-data-placement:same-cell;"/>';
                }
                $nameTd .= '<span>' . htmlspecialchars($teacher->name ?? '') . '</span>';

                $empTd = '<td class="tc" style="mso-number-format:\'\\@\';">'
                    . htmlspecialchars($teacher->employee_id ?? '') . '</td>';
                $deptTd = '<td class="tc">' . htmlspecialchars($deptName ?? '') . '</td>';

                // ── Absent row ───────────────────────────────────────────
                if (!$record || (!$record->morning_in && !$record->afternoon_in)) {
                    if ($exportType === 'daily') {
                        $html .= "<tr style=\"background:{$rowBg};\">";
                        $html .= '<td class="tc">' . $rowNum++ . '</td>';
                        $html .= '<td class="tc">' . $date->format('d-m-Y') . '</td>';
                        $html .= $empTd;
                        $html .= '<td>' . $nameTd . '</td>';
                        $html .= $deptTd;
                        $html .= '<td class="tc" style="color:#bbb;">—</td>';
                        $html .= '<td class="tc" style="color:#bbb;">—</td>';
                        $html .= '<td class="tc" style="color:#bbb;">—</td>';
                        $html .= '<td class="tc absent">' . __('Absent') . '</td>';
                        $html .= '</tr>';
                    }
                    $totAbsent++;
                    if (isset($teacherStats[$teacher->id])) $teacherStats[$teacher->id]['days_absent']++;
                    continue;
                }

                // ── Present/Late row ─────────────────────────────────────
                $totalMins = 0;
                if ($record->morning_in && $record->morning_out) {
                    $totalMins += Carbon::createFromTimeString($record->morning_in)
                        ->diffInMinutes(Carbon::createFromTimeString($record->morning_out));
                }
                if ($record->afternoon_in && $record->afternoon_out) {
                    $totalMins += Carbon::createFromTimeString($record->afternoon_in)
                        ->diffInMinutes(Carbon::createFromTimeString($record->afternoon_out));
                }

                $mIn  = $record->morning_in   ? Carbon::parse($record->morning_in)->format('h:i A')   : '—';
                $mOut = $record->morning_out  ? Carbon::parse($record->morning_out)->format('h:i A')  : '—';
                $aIn  = $record->afternoon_in  ? Carbon::parse($record->afternoon_in)->format('h:i A') : '—';
                $aOut = $record->afternoon_out ? Carbon::parse($record->afternoon_out)->format('h:i A'): '—';
                $mText = $record->morning_in   ? "$mIn → $mOut" : '—';
                $aText = $record->afternoon_in ? "$aIn → $aOut" : '—';

                $isLate = $record->morning_status === 'late' || $record->afternoon_status === 'late';
                if ($isLate) {
                    $status = __('Late'); $cls = 'late'; $totLate++;
                    if (isset($teacherStats[$teacher->id])) { $teacherStats[$teacher->id]['days_present']++; $teacherStats[$teacher->id]['days_late']++; }
                } else {
                    $status = __('Present'); $cls = 'present'; $totPresent++;
                    if (isset($teacherStats[$teacher->id])) $teacherStats[$teacher->id]['days_present']++;
                }
                
                $source = 'RFID';
                if ($record->rfid_uid === 'Manual') {
                    $source = 'Manual';
                } elseif (str_contains($record->rfid_uid ?? '', 'Edited')) {
                    $source = 'Edited';
                }

                if (isset($teacherStats[$teacher->id])) $teacherStats[$teacher->id]['total_mins'] += $totalMins;

                $workHours = $totalMins > 0 ? floor($totalMins / 60) . 'h ' . ($totalMins % 60) . 'm' : '—';

                if ($exportType === 'daily') {
                    $html .= "<tr style=\"background:{$rowBg};\">";
                    $html .= '<td class="tc">' . $rowNum++ . '</td>';
                    $html .= '<td class="tc">' . $date->format('d-m-Y') . '</td>';
                    $html .= $empTd;
                    $html .= '<td>' . $nameTd . '</td>';
                    $html .= $deptTd;
                    $html .= '<td class="tc">' . $mText . '</td>';
                    $html .= '<td class="tc">' . $aText . '</td>';
                    $html .= '<td class="tc" style="font-weight:bold;color:#1a73e8;">' . $workHours . '</td>';
                    $html .= "<td class=\"tc {$cls}\">{$status}</td>";
                    $html .= '<td class="tc">' . $source . '</td>';
                    $html .= '</tr>';
                }
            }
        }

        if ($exportType === 'daily') {
            // Totals row
            $html .= '<tr style="background:#e8f0fe;font-weight:bold;border-top:2pt solid #1a73e8;">';
            $html .= '<td colspan="5" style="background:#1a73e8;color:#fff;font-weight:bold;text-align:center;">' . __('TOTALS') . ' — ' . __('Working Days') . ': ' . $totalWorkingDays . '</td>';
            $html .= '<td class="tc present">' . __('Present') . ': ' . $totPresent . '</td>';
            $html .= '<td class="tc late">'    . __('Late')    . ': ' . $totLate    . '</td>';
            $html .= '<td></td>';
            $html .= '<td class="tc absent">'  . __('Absent')  . ': ' . $totAbsent  . '</td>';
            $html .= '<td></td>';
            $html .= '</tr>';
            $html .= '</table>';
        }
        
        // Remove the Section 2 logic (summary) which is now handled in the switch block
        
        $html .= '</body></html>';

        // UTF-8 BOM ensures Excel opens Khmer Unicode correctly
        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

        return response($bom . $html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }



    public function teacherSummary(Request $request): JsonResponse
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::now();

        $query = \App\Models\Attendance::with('teacher')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        if ($request->department) {
            $query->whereHas('teacher', fn($q) => $q->where('department', $request->department));
        }

        $records = $query->get();

        // Get all active teachers in range to detect absent days
        $teacherQuery = \App\Models\Teacher::query();
        if ($request->department) {
            $teacherQuery->where('department', $request->department);
        }
        if ($request->teacher_id) {
            $teacherQuery->where('id', $request->teacher_id);
        }
        $allTeachers = $teacherQuery->get()->keyBy('id');

        // Build working days list (Mon–Sat, exclude holidays)
        $holidays = \App\Models\Holiday::whereBetween('date', [$from->toDateString(), $to->toDateString()])->pluck('date')->toArray();
        $period = Carbon::parse($from)->toPeriod($to, '1 day');
        $workingDays = collect();
        foreach ($period as $day) {
            if ($day->dayOfWeek !== Carbon::SUNDAY && !in_array($day->toDateString(), $holidays)) {
                $workingDays->push($day->toDateString());
            }
        }
        $totalWorkingDays = $workingDays->count();

        // Group records by teacher
        $byTeacher = $records->groupBy('teacher_id');

        $summary = $allTeachers->map(function ($teacher) use ($byTeacher, $totalWorkingDays) {
            $teacherRecords = $byTeacher->get($teacher->id, collect());

            $totalMins   = 0;
            $daysPresent = 0;
            $daysLate    = 0;

            foreach ($teacherRecords as $r) {
                $dayMins = 0;
                if ($r->morning_in && $r->morning_out) {
                    $dayMins += Carbon::createFromTimeString($r->morning_in)
                        ->diffInMinutes(Carbon::createFromTimeString($r->morning_out));
                }
                if ($r->afternoon_in && $r->afternoon_out) {
                    $dayMins += Carbon::createFromTimeString($r->afternoon_in)
                        ->diffInMinutes(Carbon::createFromTimeString($r->afternoon_out));
                }
                $totalMins += $dayMins;

                $isLate = $r->morning_status === 'late' || $r->afternoon_status === 'late';
                $isPresent = $r->morning_in || $r->afternoon_in;

                if ($isPresent) $daysPresent++;
                if ($isLate)    $daysLate++;
            }

            $daysAbsent = max(0, $totalWorkingDays - $daysPresent);
            $hours      = floor($totalMins / 60);
            $mins       = $totalMins % 60;

            return [
                'teacher_id'        => $teacher->id,
                'name'              => $teacher->name,
                'name_kh'           => $teacher->name_kh,
                'employee_id'       => $teacher->employee_id,
                'department'        => $teacher->department,
                'days_present'      => $daysPresent,
                'days_late'         => $daysLate,
                'days_absent'       => $daysAbsent,
                'total_minutes'     => $totalMins,
                'total_hours_label' => $totalMins > 0 ? "{$hours}h {$mins}m" : '0h 0m',
                'attendance_rate'   => $totalWorkingDays > 0
                    ? round(($daysPresent / $totalWorkingDays) * 100, 1)
                    : 0,
            ];
        })->values()->sortByDesc('total_minutes')->values();

        return response()->json([
            'summary'           => $summary,
            'total_working_days'=> $totalWorkingDays,
            'period_from'       => $from->format('d-m-Y'),
            'period_to'         => $to->format('d-m-Y'),
        ]);
    }

    // ── Helper: working days list ──────────────────────────────────
    private function workingDaysList(Carbon $from, Carbon $to): array
    {
        $holidays = \App\Models\Holiday::whereBetween('date', [$from->toDateString(), $to->toDateString()])->pluck('date')->toArray();
        $days = [];
        $cur = $from->copy();
        while ($cur <= $to) {
            if ($cur->dayOfWeek !== Carbon::SUNDAY && !in_array($cur->toDateString(), $holidays)) {
                $days[] = $cur->toDateString();
            }
            $cur->addDay();
        }
        return $days;
    }

    // ── Absent Report ──────────────────────────────────────────────
    public function absentReport(Request $request): JsonResponse
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::now();
        $workingDays = $this->workingDaysList($from, $to);

        $tQuery = Teacher::query();
        if ($request->department) $tQuery->where('department', $request->department);
        if ($request->teacher_id)  $tQuery->where('id', $request->teacher_id);
        $teachers = $tQuery->orderBy('name')->get();

        $attended = Attendance::with('teacher')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($request->department, fn($q) => $q->whereHas('teacher', fn($q2) => $q2->where('department', $request->department)))
            ->when($request->teacher_id, fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->get()->groupBy(fn($r) => $r->teacher_id.'-'.Carbon::parse($r->date)->toDateString());

        $rows = [];
        foreach ($teachers as $t) {
            foreach ($workingDays as $day) {
                $key = $t->id.'-'.$day;
                $rec = $attended->get($key)?->first();
                $isAbsent = !$rec || (!$rec->morning_in && !$rec->afternoon_in);
                $isLeave  = $rec && (in_array($rec->morning_status, ['leave']) || in_array($rec->afternoon_status, ['leave']));
                if ($isAbsent && !$isLeave) {
                    $d = Carbon::parse($day);
                    $rows[] = [
                        'teacher_id'      => $t->employee_id,
                        'teacher_db_id'   => $t->id,
                        'teacher_name'    => $t->name,
                        'teacher_name_kh' => $t->name_kh,
                        'department'      => $t->department,
                        'absent_date'     => $d->format('d/m/Y'),
                        'absent_date_raw' => $day,
                        'day_name'        => $d->format('l'),
                        'status'          => 'Absent',
                        'remark'          => $rec?->manual_note ?? '—',
                        'attendance_id'   => $rec?->id,
                    ];
                }
            }
        }
        return response()->json(['rows' => $rows, 'total' => count($rows)]);
    }

    // ── Late Report ────────────────────────────────────────────────
    public function lateReport(Request $request): JsonResponse
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::now();
        $morningCutoff   = \App\Models\Setting::getValue('morning_late_cutoff', '07:45');
        $afternoonCutoff = \App\Models\Setting::getValue('afternoon_late_cutoff', '14:15');

        $query = Attendance::with('teacher')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where(fn($q) => $q->where('morning_status', 'late')->orWhere('afternoon_status', 'late'))
            ->when($request->department, fn($q) => $q->whereHas('teacher', fn($q2) => $q2->where('department', $request->department)))
            ->when($request->teacher_id, fn($q) => $q->where('teacher_id', $request->teacher_id));

        $rows = [];
        foreach ($query->orderBy('date')->get() as $r) {
            $session = 'Morning';
            $checkIn = $r->morning_in;
            $cutoff  = $morningCutoff;
            if ($r->morning_status !== 'late' && $r->afternoon_status === 'late') {
                $session = 'Afternoon'; $checkIn = $r->afternoon_in; $cutoff = $afternoonCutoff;
            }
            $lateMins = 0;
            if ($checkIn) {
                $lateMins = max(0, Carbon::createFromTimeString($checkIn)->diffInSeconds(Carbon::createFromTimeString($cutoff), false) * -1 / 60);
                $lateMins = number_format($lateMins, 2, '.', '');
            }
            $rows[] = [
                'id'              => $r->id,
                'teacher_id'      => $r->teacher->employee_id ?? '',
                'teacher_db_id'   => $r->teacher_id,
                'teacher_name'    => $r->teacher->name ?? 'N/A',
                'teacher_name_kh' => $r->teacher->name_kh ?? '',
                'department'      => $r->teacher->department ?? '',
                'date'            => Carbon::parse($r->date)->format('d/m/Y'),
                'date_raw'        => Carbon::parse($r->date)->toDateString(),
                'day_name'        => Carbon::parse($r->date)->format('l'),
                'session'         => $session,
                'check_in'        => $checkIn ? Carbon::createFromTimeString($checkIn)->format('H:i') : '—',
                'expected_time'   => $cutoff,
                'late_minutes'    => $lateMins,
                'remark'          => $r->manual_note ?? '—',
                'morning_in'      => $r->morning_in ? Carbon::parse($r->morning_in)->format('h:i A') : null,
                'morning_out'     => $r->morning_out ? Carbon::parse($r->morning_out)->format('h:i A') : null,
                'morning_status'  => $r->morning_status,
                'afternoon_in'    => $r->afternoon_in ? Carbon::parse($r->afternoon_in)->format('h:i A') : null,
                'afternoon_out'   => $r->afternoon_out ? Carbon::parse($r->afternoon_out)->format('h:i A') : null,
                'afternoon_status'=> $r->afternoon_status,
                'manual_note'     => $r->manual_note,
            ];
        }
        return response()->json(['rows' => $rows, 'total' => count($rows), 'morning_cutoff' => $morningCutoff, 'afternoon_cutoff' => $afternoonCutoff]);
    }

    // ── Leave Report ───────────────────────────────────────────────
    public function leaveReport(Request $request): JsonResponse
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::now();

        $query = Attendance::with('teacher')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where(fn($q) => $q->where('morning_status', 'leave')->orWhere('afternoon_status', 'leave'))
            ->when($request->department, fn($q) => $q->whereHas('teacher', fn($q2) => $q2->where('department', $request->department)))
            ->when($request->teacher_id, fn($q) => $q->where('teacher_id', $request->teacher_id));

        $rows = [];
        foreach ($query->orderBy('date')->get() as $r) {
            $leaveType = ($r->morning_status === 'leave' && $r->afternoon_status === 'leave') ? 'Full Day' : (($r->morning_status === 'leave') ? 'Morning' : 'Afternoon');
            $rows[] = [
                'teacher_id'   => $r->teacher->employee_id ?? '',
                'teacher_name' => $r->teacher->name ?? 'N/A',
                'teacher_name_kh' => $r->teacher->name_kh ?? '',
                'department'   => $r->teacher->department ?? '',
                'leave_date'   => Carbon::parse($r->date)->format('d/m/Y'),
                'day_name'     => Carbon::parse($r->date)->format('l'),
                'leave_type'   => $leaveType,
                'status'       => 'Leave',
                'remark'       => $r->manual_note ?? '—',
            ];
        }
        return response()->json(['rows' => $rows, 'total' => count($rows)]);
    }

    // ── Individual Teacher Report ──────────────────────────────────
    public function individualReport(Request $request): JsonResponse
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::now();
        $workingDays = $this->workingDaysList($from, $to);

        if (!$request->teacher_id) {
            return response()->json(['error' => 'Please select a teacher.'], 422);
        }
        $teacher = Teacher::findOrFail($request->teacher_id);
        $records = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        $rows = [];
        $daysPresent = $daysLate = $daysAbsent = $daysLeave = $totalMins = 0;
        foreach ($workingDays as $day) {
            $r = $records->get($day);
            $d = Carbon::parse($day);
            if (!$r || (!$r->morning_in && !$r->afternoon_in)) {
                $isLeave = $r && (in_array($r->morning_status, ['leave']) || in_array($r->afternoon_status, ['leave']));
                if ($isLeave) { $status = 'leave'; $daysLeave++; }
                else          { $status = 'absent'; $daysAbsent++; }
                $rows[] = ['date'=>$d->format('d/m/Y'),'day_name'=>$d->format('l'),'check_in'=>'—','check_out'=>'—','working_hours'=>'—','status'=>$status,'remark'=>$r?->manual_note??'—'];
            } else {
                $mins = 0;
                if ($r->morning_in && $r->morning_out)   $mins += Carbon::createFromTimeString($r->morning_in)->diffInMinutes(Carbon::createFromTimeString($r->morning_out));
                if ($r->afternoon_in && $r->afternoon_out) $mins += Carbon::createFromTimeString($r->afternoon_in)->diffInMinutes(Carbon::createFromTimeString($r->afternoon_out));
                $totalMins += $mins;
                $status = ($r->morning_status === 'late' || $r->afternoon_status === 'late') ? 'late' : 'present';
                if ($status === 'late') $daysLate++; else $daysPresent++;
                $checkIn  = $r->morning_in  ? Carbon::createFromTimeString($r->morning_in)->format('H:i')  : ($r->afternoon_in  ? Carbon::createFromTimeString($r->afternoon_in)->format('H:i')  : '—');
                $checkOut = $r->afternoon_out ? Carbon::createFromTimeString($r->afternoon_out)->format('H:i') : ($r->morning_out ? Carbon::createFromTimeString($r->morning_out)->format('H:i') : '—');
                $rows[] = ['date'=>$d->format('d/m/Y'),'day_name'=>$d->format('l'),'check_in'=>$checkIn,'check_out'=>$checkOut,'working_hours'=>$mins>0?floor($mins/60).'h '.($mins%60).'m':'—','status'=>$status,'remark'=>$r->manual_note??'—'];
            }
        }
        $totalWorkingDays = count($workingDays);
        $rate = $totalWorkingDays > 0 ? round(($daysPresent + $daysLate) / $totalWorkingDays * 100, 1) : 0;
        return response()->json([
            'teacher' => ['name'=>$teacher->name,'name_kh'=>$teacher->name_kh,'employee_id'=>$teacher->employee_id,'department'=>$teacher->department],
            'rows'    => $rows,
            'summary' => ['total_working_days'=>$totalWorkingDays,'present'=>$daysPresent,'late'=>$daysLate,'absent'=>$daysAbsent,'leave'=>$daysLeave,'total_hours'=>floor($totalMins/60).'h '.($totalMins%60).'m','attendance_rate'=>$rate],
        ]);
    }

    // ── Department Report ──────────────────────────────────────────
    public function departmentReport(Request $request): JsonResponse
    {
        $from = $request->from ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)   : Carbon::now();
        $workingDays = $this->workingDaysList($from, $to);
        $totalWorkingDays = count($workingDays);

        $tQuery = Teacher::query();
        if ($request->department) $tQuery->where('department', $request->department);
        $teachers = $tQuery->orderBy('name')->get();

        $records = Attendance::with('teacher')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($request->department, fn($q) => $q->whereHas('teacher', fn($q2) => $q2->where('department', $request->department)))
            ->get()->groupBy('teacher_id');

        $rows = [];
        foreach ($teachers as $t) {
            $tRecords = $records->get($t->id, collect());
            $present = $late = $leave = 0;
            foreach ($tRecords as $r) {
                if ($r->morning_status === 'leave' || $r->afternoon_status === 'leave') { $leave++; continue; }
                if ($r->morning_in || $r->afternoon_in) {
                    if ($r->morning_status === 'late' || $r->afternoon_status === 'late') $late++;
                    else $present++;
                }
            }
            $absent = max(0, $totalWorkingDays - $present - $late - $leave);
            $rate   = $totalWorkingDays > 0 ? round(($present + $late) / $totalWorkingDays * 100, 1) : 0;
            $rows[] = ['teacher_id'=>$t->employee_id,'teacher_name'=>$t->name,'teacher_name_kh'=>$t->name_kh,'department'=>$t->department,'present'=>$present,'late'=>$late,'absent'=>$absent,'leave'=>$leave,'working_days'=>$totalWorkingDays,'attendance_rate'=>$rate];
        }
        $totPresent = array_sum(array_column($rows,'present'));
        $totLate    = array_sum(array_column($rows,'late'));
        $totAbsent  = array_sum(array_column($rows,'absent'));
        $totLeave   = array_sum(array_column($rows,'leave'));
        $totTeachers = count($rows);
        $totalPossible = $totTeachers * $totalWorkingDays;
        $overallRate = $totalPossible > 0 ? round(($totPresent + $totLate) / $totalPossible * 100, 1) : 0;
        return response()->json([
            'rows' => $rows,
            'summary' => ['total_teachers'=>$totTeachers,'total_present'=>$totPresent,'total_late'=>$totLate,'total_absent'=>$totAbsent,'total_leave'=>$totLeave,'overall_rate'=>$overallRate,'working_days'=>$totalWorkingDays],
            'period_from' => $from->format('d-m-Y'),
            'period_to'   => $to->format('d-m-Y'),
        ]);
    }

    // ── Attendance Edit & Manual (Source & History logic) ────────────────────────────────────────────────
    public function storeManualAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'reason' => 'required|string|max:255',
            'morning_in' => 'nullable|string',
            'morning_out' => 'nullable|string',
            'morning_status' => 'required|string',
            'afternoon_in' => 'nullable|string',
            'afternoon_out' => 'nullable|string',
            'afternoon_status' => 'required|string',
        ]);

        $existing = \App\Models\Attendance::where('teacher_id', $request->teacher_id)->where('date', $request->date)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Attendance record for this date already exists. Use Edit instead.'], 400);
        }

        $att = new \App\Models\Attendance();
        $att->teacher_id = $request->teacher_id;
        $att->date = $request->date;
        $att->morning_in = $request->morning_in;
        $att->morning_out = $request->morning_out;
        $att->morning_status = $request->morning_status;
        $att->afternoon_in = $request->afternoon_in;
        $att->afternoon_out = $request->afternoon_out;
        $att->afternoon_status = $request->afternoon_status;
        $att->manual_note = $request->reason;
        $att->rfid_uid = 'Manual';
        $att->save();

        return response()->json(['success' => true]);
    }

    public function updateAttendance(Request $request, $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'morning_in' => 'nullable|string',
            'morning_out' => 'nullable|string',
            'morning_status' => 'required|string',
            'afternoon_in' => 'nullable|string',
            'afternoon_out' => 'nullable|string',
            'afternoon_status' => 'required|string',
        ]);

        $att = \App\Models\Attendance::with('teacher')->findOrFail($id);

        $oldData = [
            'morning_in' => $att->morning_in ? \Carbon\Carbon::parse($att->morning_in)->format('H:i') : null,
            'morning_out' => $att->morning_out ? \Carbon\Carbon::parse($att->morning_out)->format('H:i') : null,
            'morning_status' => $att->morning_status,
            'afternoon_in' => $att->afternoon_in ? \Carbon\Carbon::parse($att->afternoon_in)->format('H:i') : null,
            'afternoon_out' => $att->afternoon_out ? \Carbon\Carbon::parse($att->afternoon_out)->format('H:i') : null,
            'afternoon_status' => $att->afternoon_status,
            'reason' => $att->manual_note,
        ];

        $newData = [
            'morning_in' => $request->morning_in,
            'morning_out' => $request->morning_out,
            'morning_status' => $request->morning_status,
            'afternoon_in' => $request->afternoon_in,
            'afternoon_out' => $request->afternoon_out,
            'afternoon_status' => $request->afternoon_status,
            'reason' => $request->reason,
        ];

        $att->morning_in = $request->morning_in;
        $att->morning_out = $request->morning_out;
        $att->morning_status = $request->morning_status;
        $att->afternoon_in = $request->afternoon_in;
        $att->afternoon_out = $request->afternoon_out;
        $att->afternoon_status = $request->afternoon_status;
        $att->manual_note = $request->reason;
        
        if (!str_contains($att->rfid_uid ?? '', 'Edited') && $att->rfid_uid !== 'Manual') {
            $att->rfid_uid = $att->rfid_uid ? $att->rfid_uid . ' (Edited)' : 'Edited';
        } elseif ($att->rfid_uid === 'Manual') {
            $att->rfid_uid = 'Manual (Edited)';
        }
        $att->save();

        $teacherName = $att->teacher->name ?? 'Unknown';
        \App\Models\SecurityLog::create([
            'admin_id'   => \Illuminate\Support\Facades\Auth::id(),
            'action'     => 'Edit Attendance',
            'target'     => 'Teacher: ' . $teacherName . ', Date: ' . \Carbon\Carbon::parse($att->date)->format('d/m/Y'),
            'details'    => json_encode(['old' => $oldData, 'new' => $newData, 'attendance_id' => $att->id]),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getAttendanceHistory($id): JsonResponse
    {
        $logs = \App\Models\SecurityLog::where('action', 'Edit Attendance')
            ->where('details', 'LIKE', '%"attendance_id":' . $id . '%')
            ->orderBy('timestamp', 'desc')
            ->get()
            ->map(function ($log) {
                $details = json_decode($log->details, true);
                return [
                    'timestamp' => \Carbon\Carbon::parse($log->timestamp)->format('d/m/Y H:i:s'),
                    'old' => $details['old'] ?? [],
                    'new' => $details['new'] ?? [],
                ];
            });

        return response()->json(['history' => $logs]);
    }
}
