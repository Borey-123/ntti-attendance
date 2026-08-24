<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Validator;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $teacher = null;
        $history = [];
        $stats = ['present' => 0, 'late' => 0, 'absent' => 0];
        $error = null;
        $calendar = [];
        $corrections = collect();
        $todayRecord = null;
        $calendarMonth = null;
        $calendarYear = null;
        $calendarLabel = '';

        // ── Global Front Page Data ──
        $upcomingHolidays = \App\Models\Holiday::where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->take(5)
            ->get();

        $presentToday = Attendance::whereDate('date', today())->count();
        $totalTeachers = Teacher::where(function($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->count();
        
        $lastHeartbeat = \App\Models\Setting::getValue('last_scanner_heartbeat');
        $isOnline = false;
        if ($lastHeartbeat) {
            $diff = Carbon::parse($lastHeartbeat)->diffInMinutes(now());
            $isOnline = $diff <= 3;
        }

        $teacherId = session('portal_teacher_id');
        
        if (!$teacherId) {
            return view('portal.login');
        }

        $teacher = Teacher::find($teacherId);

        if (!$teacher || $teacher->status !== 'active') {
            session()->forget('portal_teacher_id');
            return redirect()->route('portal.index')->with('error', 'Session invalid or teacher inactive.');
        }

        try {
            \App\Models\SecurityLog::create([
                'admin_id'   => null,
                'action'     => 'Teacher Portal Check-in',
                'target'     => ($teacher->name_kh ?: $teacher->name) . ' (' . $teacher->employee_id . ')',
                'ip_address' => $request->ip(),
                'details'    => 'Department: ' . ($teacher->department ?? '-'),
                'timestamp'  => now()
            ]);
        } catch (\Exception $e) {}

        // ── Month navigation: determine target month ──
        $calendarMonth = $request->input('month', now()->month);
        $calendarYear  = $request->input('year', now()->year);
        $targetDate    = Carbon::createFromDate($calendarYear, $calendarMonth, 1);
        $startOfMonth  = $targetDate->copy()->startOfMonth();
        $endOfMonth    = $targetDate->copy()->endOfMonth();
        $daysInMonth   = $endOfMonth->day;
        $isCurrentMonth = $targetDate->isSameMonth(now());
        $calendarLabel = $targetDate->format('F Y');

        // ── History records for the selected month ──
        $historyRecords = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->get();

        $history = $historyRecords->map(function($a) {
            $dateObj = is_string($a->date) ? \Carbon\Carbon::parse($a->date) : $a->date;
            $isToday = $dateObj->isToday();
            
            return (object)[
                'date' => $dateObj->format('M d, Y'),
                'day'  => $dateObj->format('D'),
                'morning' => $this->formatShiftStatus($a->morning_in, $a->morning_out, $isToday, 'morning'),
                'afternoon' => $this->formatShiftStatus($a->afternoon_in, $a->afternoon_out, $isToday, 'afternoon'),
                'has_late' => ($a->morning_status === 'late' || $a->afternoon_status === 'late'),
                'morning_late' => ($a->morning_status === 'late'),
                'afternoon_late' => ($a->afternoon_status === 'late')
            ];
        });

        // ── Stats: present, late, absent ──
        $monthHolidays = \App\Models\Holiday::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()->keyBy(function($item) {
                return is_string($item->date) ? substr($item->date, 0, 10) : $item->date->format('Y-m-d');
            });

        $lastCountDate = $isCurrentMonth ? now() : $endOfMonth;
        $workingDays = 0;
        for ($d = 1; $d <= $lastCountDate->day; $d++) {
            $dt = $startOfMonth->copy()->addDays($d - 1);
            $dtStr = $dt->format('Y-m-d');
            if (!$dt->isWeekend() && !$monthHolidays->has($dtStr) && ($dt->isPast() || $dt->isToday())) {
                $workingDays++;
            }
        }

        $presentCount = $historyRecords->count();
        $lateCount = $historyRecords->filter(fn($a) => $a->morning_status === 'late' || $a->afternoon_status === 'late')->count();
        $absentCount = max(0, $workingDays - $presentCount);

        $stats = [
            'present' => $presentCount,
            'late'    => $lateCount,
            'absent'  => $absentCount,
        ];

        // ── Today's attendance record ──
        $todayRecord = Attendance::where('teacher_id', $teacher->id)
            ->where('date', now()->toDateString())
            ->first();

        // ── Calendar data ──
        $monthAttendances = $historyRecords->keyBy(function($item) {
            return is_string($item->date) ? substr($item->date, 0, 10) : $item->date->format('Y-m-d');
        });

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $startOfMonth->copy()->addDays($i - 1);
            $dateStr = $date->format('Y-m-d');
            
            $status = 'none';
            if ($date->isFuture()) {
                $status = 'future';
            } elseif ($monthAttendances->has($dateStr)) {
                $att = $monthAttendances->get($dateStr);
                if ($att->morning_status === 'late' || $att->afternoon_status === 'late') {
                    $status = 'late';
                } else {
                    $status = 'present';
                }
            } elseif ($monthHolidays->has($dateStr)) {
                $status = 'holiday';
            } elseif ($date->isWeekend()) {
                $status = 'weekend';
            } else {
                if ($date->isPast() && !$date->isToday()) {
                    $status = 'absent';
                }
            }
            
            $calendar[] = (object)[
                'day' => $i,
                'date' => $dateStr,
                'status' => $status,
                'is_today' => $date->isToday()
            ];
        }

        // Get Corrections History
        $corrections = AttendanceCorrection::where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $departments = \App\Models\Department::all();
        return view('portal.index', compact(
            'teacher', 'history', 'stats', 'error', 'departments', 'calendar', 'corrections',
            'todayRecord', 'upcomingHolidays', 'calendarMonth', 'calendarYear', 'calendarLabel',
            'presentToday', 'totalTeachers', 'isOnline'
        ));
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        $key = 'portal_login:' . request()->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            return redirect()->back()->with('error', "Too many login attempts. Please try again in {$seconds} seconds.");
        }

        $teacher = Teacher::where('employee_id', $request->employee_id)->where('status', 'active')->first();

        if (!$teacher || !\Illuminate\Support\Facades\Hash::check($request->pin, $teacher->portal_pin)) {
            \Illuminate\Support\Facades\RateLimiter::hit($key);
            return redirect()->back()->withInput($request->only('employee_id'))->with('error', 'Invalid employee ID or PIN.');
        }

        \Illuminate\Support\Facades\RateLimiter::clear($key);
        session(['portal_teacher_id' => $teacher->id]);
        session()->regenerate();

        return redirect()->route('portal.index');
    }

    public function logout(Request $request)
    {
        session()->forget('portal_teacher_id');
        session()->regenerateToken();
        return redirect()->route('portal.index');
    }

    public function changePassword(Request $request)
    {
        $teacherId = session('portal_teacher_id');
        if (!$teacherId) {
            return redirect()->route('portal.index')->with('error', 'Unauthorized.');
        }

        $request->validate([
            'current_pin' => 'required|string|size:6',
            'new_pin' => 'required|string|size:6|confirmed',
        ]);

        $teacher = Teacher::find($teacherId);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_pin, $teacher->portal_pin)) {
            return redirect()->back()->with('error', 'Current PIN is incorrect.');
        }

        $teacher->update([
            'portal_pin' => \Illuminate\Support\Facades\Hash::make($request->new_pin)
        ]);

        // Invalidate session requiring login again
        session()->forget('portal_teacher_id');
        session()->regenerateToken();

        return redirect()->route('portal.index')->with('success', 'PIN changed successfully. Please log in with your new PIN.');
    }

    public function export(Request $request)
    {
        $id = trim($request->employee_id);
        if (!$id) return redirect()->back()->with('error', 'Employee ID is required.');
        
        $teacher = Teacher::where('employee_id', $id)->first();
        if (!$teacher) return redirect()->back()->with('error', 'Teacher not found.');

        $attendances = Attendance::where('teacher_id', $teacher->id)
            ->where('date', '>=', now()->subDays(31)->startOfDay())
            ->orderBy('date', 'asc')
            ->get();

        $filename = "attendance_{$teacher->employee_id}_" . date('Y-m-d') . ".xls";
        $uName = \App\Models\Setting::getValue('university_name', 'National Technical Training Institute');
        $isKm = app()->getLocale() == 'km';
        $depts = \App\Models\Department::all()->keyBy('name');

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Attendance</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
                <style>
                    body { font-family: "Khmer OS Battambang", "Khmer OS Siemreap", "DaunPenh", "Kantumruy Pro", "Arial", sans-serif; }
                    .header-title { font-size: 18pt; font-weight: bold; color: #1a73e8; }
                    .header-sub { font-size: 14pt; font-weight: bold; }
                    .table-head { background-color: #1a73e8; color: #ffffff; font-weight: bold; }
                    .text-center { text-align: center; }
                    .badge-present { color: #34a853; font-weight: bold; }
                    .badge-late { color: #fbbc05; font-weight: bold; }
                    .badge-absent { color: #ea4335; font-weight: bold; }
                    td { border: 0.5pt solid #ccc; }
                </style>
                </head><body>';
        
        $html .= '<table border="0" style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr><td colspan="9" class="text-center"><div class="header-title">' . htmlspecialchars($uName) . '</div></td></tr>';
        $html .= '<tr><td colspan="9" class="text-center"><div class="header-sub">' . __('Teacher Attendance Official Report') . '</div></td></tr>';
        $html .= '<tr><td colspan="9" class="text-center">' . __('Export Date') . ': ' . now()->format('d-m-Y H:i') . '</td></tr>';
        $html .= '<tr><td colspan="9" height="20"></td></tr>'; // Spacer
        $html .= '</table>';

        $html .= '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">';
        $html .= '<tr class="table-head">';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">#</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Date') . '</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Teacher ID') . '</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Teacher Name') . '</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Department') . '</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Morning') . '</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Afternoon') . '</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Working Hours') . '</th>';
        $html .= '<th style="background-color: #1a73e8; color: #ffffff;">' . __('Status') . '</th>';
        $html .= '</tr>';

        $rowCount = 1;
        $deptName = $isKm && isset($depts[$teacher->department]) ? $depts[$teacher->department]->name_kh : $teacher->department;

        foreach ($attendances as $record) {
            $dateObj = is_string($record->date) ? \Carbon\Carbon::parse($record->date) : $record->date;

            $totalMins = 0;
            $mStatusText = '—';
            $aStatusText = '—';
            $status = __('Absent');
            $statusClass = 'badge-absent';

            if ($record->morning_in && $record->morning_out) {
                $totalMins += \Carbon\Carbon::createFromTimeString($record->morning_in)->diffInMinutes(\Carbon\Carbon::createFromTimeString($record->morning_out));
            }
            if ($record->afternoon_in && $record->afternoon_out) {
                $totalMins += \Carbon\Carbon::createFromTimeString($record->afternoon_in)->diffInMinutes(\Carbon\Carbon::createFromTimeString($record->afternoon_out));
            }

            $mIn = $record->morning_in ? \Carbon\Carbon::parse($record->morning_in)->format('h:i A') : '—';
            $mOut = $record->morning_out ? \Carbon\Carbon::parse($record->morning_out)->format('h:i A') : '—';
            $mStatusText = $record->morning_in ? "$mIn - $mOut" : '—';

            $aIn = $record->afternoon_in ? \Carbon\Carbon::parse($record->afternoon_in)->format('h:i A') : '—';
            $aOut = $record->afternoon_out ? \Carbon\Carbon::parse($record->afternoon_out)->format('h:i A') : '—';
            $aStatusText = $record->afternoon_in ? "$aIn - $aOut" : '—';

            if ($record->morning_status == 'late' || $record->afternoon_status == 'late') {
                $status = __('Late');
                $statusClass = 'badge-late';
            } elseif ($record->morning_status == 'present' || $record->afternoon_status == 'present') {
                $status = __('Present');
                $statusClass = 'badge-present';
            }

            $workHours = $totalMins > 0 ? floor($totalMins / 60) . 'h ' . ($totalMins % 60) . 'm' : '—';

            $html .= '<tr>';
            $html .= '<td class="text-center">' . $rowCount++ . '</td>';
            $html .= '<td class="text-center">' . $dateObj->format('d-m-Y') . '</td>';
            $html .= '<td class="text-center" style="mso-number-format:\'\\@\';">' . htmlspecialchars($teacher->employee_id ?? '') . '</td>';
            $html .= '<td>';
            if ($teacher->name_kh) {
                $html .= '<span style="font-weight: bold; color: #1a73e8;">' . htmlspecialchars($teacher->name_kh) . '</span><br style="mso-data-placement:same-cell;" />';
            }
            $html .= '<span>' . htmlspecialchars($teacher->name ?? '') . '</span>';
            $html .= '</td>';
            $html .= '<td class="text-center">' . htmlspecialchars($deptName ?? '') . '</td>';
            $html .= '<td class="text-center">' . $mStatusText . '</td>';
            $html .= '<td class="text-center">' . $aStatusText . '</td>';
            $html .= '<td class="text-center" style="font-weight: bold;">' . $workHours . '</td>';
            $html .= '<td class="text-center ' . $statusClass . '">' . $status . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

        return response($bom . $html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function storeCorrection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|string',
            'date' => 'required|date',
            'shift' => 'required|in:morning,afternoon,both',
            'reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
        }

        $teacher = Teacher::where('employee_id', $request->employee_id)->first();
        if (!$teacher) {
            return response()->json(['status' => 'error', 'message' => 'Teacher not found.'], 404);
        }

        AttendanceCorrection::create([
            'teacher_id' => $teacher->id,
            'date' => $request->date,
            'shift' => $request->shift,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return response()->json(['status' => 'success', 'message' => 'Correction request submitted successfully!']);
    }

    // Keep search for API compatibility if needed, but we will use index for the main UI
    public function search(Request $request)
    {
        try {
            $id = trim($request->query('employee_id'));
            
            if (empty($id)) {
                return response()->json(['status' => 'error', 'message' => 'Please enter an ID'], 400);
            }

            // Search for active teacher with flexible ID matching
            $teacher = Teacher::where('status', 'active')
                ->where(function($q) use ($id) {
                    $q->where('employee_id', $id)
                      ->orWhere('employee_id', 'LIKE', '%' . $id . '%');
                })->first();

            if (!$teacher) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Employee ID "' . $id . '" was not found. Please verify the ID in the Teacher list.'
                ], 404);
            }

            // Get last 30 days of attendance
            $historyRecords = Attendance::where('teacher_id', $teacher->id)
                ->where('date', '>=', now()->subDays(31)->startOfDay())
                ->orderBy('date', 'desc')
                ->get();

            $history = $historyRecords->map(function($a) {
                try {
                    $dateObj = is_string($a->date) ? \Carbon\Carbon::parse($a->date) : $a->date;
                    $isToday = $dateObj->isToday();
                    
                    return [
                        'date' => $dateObj->format('M d, Y'),
                        'day'  => $dateObj->format('D'),
                        'morning' => $this->formatShiftStatus($a->morning_in, $a->morning_out, $isToday, 'morning'),
                        'afternoon' => $this->formatShiftStatus($a->afternoon_in, $a->afternoon_out, $isToday, 'afternoon'),
                        'has_late' => ($a->morning_status === 'late' || $a->afternoon_status === 'late')
                    ];
                } catch (\Exception $e) {
                    return null;
                }
            })->filter()->values();

            return response()->json([
                'status' => 'success',
                'teacher' => [
                    'name' => $teacher->name,
                    'department' => $teacher->department,
                    'photo' => $teacher->photo ? url($teacher->photo) : null,
                ],
                'stats' => [
                    'present' => $historyRecords->count(),
                    'late'    => $historyRecords->filter(fn($a) => $a->morning_status === 'late' || $a->afternoon_status === 'late')->count(),
                ],
                'history' => $history
            ]);
        } catch (\Exception $e) {
            \Log::error("Portal Search Failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }


    private function formatShiftStatus($in, $out, $isToday, $shift)
    {
        if (!$in) return '—';
        
        $inTime = substr($in, 0, 5);
        
        if ($out) {
            return $inTime . ' - ' . substr($out, 0, 5);
        }
        
        // No check-out
        if ($isToday) {
            $now = Carbon::now();
            $hour = $now->hour + ($now->minute / 60);
            
            // Check if still within shift hours
            $isStillShift = false;
            if ($shift === 'morning' && $hour < 13.0) $isStillShift = true;
            if ($shift === 'afternoon' && $hour < 23.5) $isStillShift = true;
            
            if ($isStillShift) {
                return $inTime . ' - <span class="status-on-duty">On Duty</span>';
            }
        }
        
        return $inTime . ' - <span class="status-missing">No Out</span>';
    }
}
