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

        // ── 1. KPI Calculations: Worked Hours, Avg Arrival, On-time Streak ──
        $totalWorkedMinutes = 0;
        $morningTimes = [];
        foreach ($historyRecords as $rec) {
            if ($rec->morning_in && $rec->morning_out) {
                $mIn = Carbon::createFromTimeString($rec->morning_in);
                $mOut = Carbon::createFromTimeString($rec->morning_out);
                if ($mOut->greaterThanOrEqualTo($mIn)) {
                    $totalWorkedMinutes += $mOut->diffInMinutes($mIn);
                }
            }
            if ($rec->afternoon_in && $rec->afternoon_out) {
                $aIn = Carbon::createFromTimeString($rec->afternoon_in);
                $aOut = Carbon::createFromTimeString($rec->afternoon_out);
                if ($aOut->greaterThanOrEqualTo($aIn)) {
                    $totalWorkedMinutes += $aOut->diffInMinutes($aIn);
                }
            }
            if ($rec->morning_in) {
                $morningTimes[] = Carbon::createFromTimeString($rec->morning_in)->secondsSinceMidnight();
            }
        }

        $avgArrivalTime = '—';
        if (count($morningTimes) > 0) {
            $avgSecs = array_sum($morningTimes) / count($morningTimes);
            $avgArrivalTime = Carbon::today()->addSeconds($avgSecs)->format('h:i A');
        }

        // Streak Calculation
        $onTimeStreak = 0;
        $allTeacherHistory = Attendance::where('teacher_id', $teacher->id)
            ->where('date', '<=', today()->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        foreach ($allTeacherHistory as $att) {
            if ($att->morning_status === 'late' || $att->afternoon_status === 'late') {
                break;
            }
            if ($att->morning_status === 'present' || $att->afternoon_status === 'present') {
                $onTimeStreak++;
            }
        }

        // ── 2. Today's Teaching Schedule ──
        $todayDayOfWeek = now()->format('l'); // Monday, Tuesday, etc.
        $todaySchedules = \App\Models\TeacherSchedule::where('teacher_id', $teacher->id)
            ->where('day_of_week', $todayDayOfWeek)
            ->orderBy('start_time')
            ->get();

        // ── 3. Recent Leave Requests History ──
        $leaveRequestsHistory = \App\Models\LeaveRequest::where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $portalAnnouncements = \App\Models\Announcement::where(function($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
        })->latest()->take(5)->get();

        $departments = \App\Models\Department::all();
        return view('portal.index', compact(
            'teacher', 'history', 'stats', 'error', 'departments', 'calendar', 'corrections',
            'todayRecord', 'upcomingHolidays', 'calendarMonth', 'calendarYear', 'calendarLabel',
            'presentToday', 'totalTeachers', 'isOnline',
            'totalWorkedMinutes', 'avgArrivalTime', 'onTimeStreak', 'todaySchedules', 'leaveRequestsHistory',
            'portalAnnouncements'
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

        \App\Models\SecurityLog::recordPortal('Portal Login', 'Teacher ID: ' . $teacher->employee_id, 'Teacher successfully logged in to the portal.');

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

        \App\Models\SecurityLog::recordPortal('Portal PIN Reset', 'Teacher ID: ' . $teacher->employee_id, 'Teacher updated their portal PIN.');

        return redirect()->route('portal.index')->with('success', 'PIN changed successfully. Please log in with your new PIN.');
    }

    public function changePhoto(Request $request)
    {
        $teacherId = session('portal_teacher_id');
        if (!$teacherId) {
            return redirect()->route('portal.index')->with('error', 'Unauthorized.');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $teacher = Teacher::find($teacherId);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($teacher->photo && file_exists(public_path($teacher->photo))) {
                unlink(public_path($teacher->photo));
            }
            
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/teachers'), $filename);
            $teacher->photo = 'uploads/teachers/' . $filename;
            $teacher->save();
            
            \App\Models\SecurityLog::recordPortal('Portal Photo Update', 'Teacher ID: ' . $teacher->employee_id, 'Teacher uploaded a new profile picture.');
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Profile picture updated successfully!',
                    'photo_url' => to_asset_url($teacher->photo)
                ]);
            }
            return redirect()->back()->with('success', 'Profile picture updated successfully!');
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => 'No photo provided.'], 400);
        }
        return redirect()->back()->with('error', 'No photo provided.');
    }

    public function changeFace(Request $request)
    {
        $teacherId = session('portal_teacher_id');
        if (!$teacherId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'face_descriptor' => 'required|string'
        ]);

        $teacher = Teacher::find($teacherId);
        if (!$teacher) {
            return response()->json(['status' => 'error', 'message' => 'Teacher not found.'], 404);
        }

        $teacher->update([
            'face_descriptor' => $request->face_descriptor
        ]);
        
        \App\Models\SecurityLog::recordPortal('Portal Face Update', 'Teacher ID: ' . $teacher->employee_id, 'Teacher manually registered face ID.');

        return response()->json(['status' => 'success', 'message' => 'Face registered successfully!']);
    }

    public function gpsCheckin(Request $request)
    {
        $teacherId = session('portal_teacher_id');
        if (!$teacherId) {
            return response()->json(['success' => false, 'message' => __('Unauthorized. Please log in first.')], 401);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $teacher = Teacher::find($teacherId);
        if (!$teacher || $teacher->status !== 'active') {
            return response()->json(['success' => false, 'message' => __('Teacher invalid or inactive.')], 403);
        }

        // Campus coordinates (default NTTI campus or configured in Settings)
        $campusLat = (float) \App\Models\Setting::getValue('campus_latitude', '11.5621');
        $campusLng = (float) \App\Models\Setting::getValue('campus_longitude', '104.8885');
        $allowedRadiusMeters = (float) \App\Models\Setting::getValue('campus_gps_radius', '1000'); // 1km radius

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        // Haversine distance calculation in meters
        $earthRadius = 6371000;
        $dLat = deg2rad($lat - $campusLat);
        $dLng = deg2rad($lng - $campusLng);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($campusLat)) * cos(deg2rad($lat)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        $isExempt = !empty($teacher->is_geofence_exempt);
        $gpsRestricted = \App\Models\Setting::getValue('enforce_gps_geofence', 'true') === 'true';

        if ($gpsRestricted && !$isExempt && $distance > $allowedRadiusMeters) {
            return response()->json([
                'success' => false,
                'can_dispute' => true,
                'distance' => round($distance),
                'allowed_radius' => round($allowedRadiusMeters),
                'latitude' => $lat,
                'longitude' => $lng,
                'message' => __('GPS Check-in Failed: You are outside the allowed campus location radius (:dist m away).', ['dist' => round($distance)])
            ], 422);
        }

        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');
        $hour = now()->hour + (now()->minute / 60);

        $attendance = Attendance::firstOrCreate(
            ['teacher_id' => $teacher->id, 'date' => $today],
            ['checkin_method' => 'gps', 'latitude' => $lat, 'longitude' => $lng]
        );

        $attendance->latitude = $lat;
        $attendance->longitude = $lng;
        $attendance->checkin_method = 'gps';

        // Determine morning or afternoon shift check-in/out
        if ($hour < 12.0) {
            if (!$attendance->morning_in) {
                $attendance->morning_in = $currentTime;
                $attendance->morning_status = ($hour > 8.25) ? 'late' : 'present';
            } else {
                $attendance->morning_out = $currentTime;
            }
        } else {
            if (!$attendance->afternoon_in) {
                $attendance->afternoon_in = $currentTime;
                $attendance->afternoon_status = ($hour > 14.25) ? 'late' : 'present';
            } else {
                $attendance->afternoon_out = $currentTime;
            }
        }

        $attendance->save();

        $distNote = $isExempt ? "Exempt Person (Distance: " . round($distance) . "m)" : "Distance: " . round($distance) . "m";
        \App\Models\SecurityLog::recordPortal('GPS Check-in', 'Teacher: ' . $teacher->name, "Coordinates: {$lat}, {$lng} ({$distNote})");

        // Send Telegram Notification if teacher has linked Telegram
        if (!empty($teacher->telegram_chat_id)) {
            $botToken = \App\Models\Setting::getValue('telegram_bot_token');
            if ($botToken) {
                try {
                    $mapsUrl = "https://maps.google.com/?q={$lat},{$lng}";
                    $statusTxt = $isExempt ? "✅ Successful (Geofence Exempt)" : "✅ Successful (Within Boundary)";
                    $msgText = "📍 *Mobile GPS Check-in Verified*\n"
                             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
                             . "👤 *Teacher:* {$teacher->name}\n"
                             . "🕒 *Time:* " . now()->format('h:i:s A') . "\n"
                             . "📏 *Distance to Campus:* " . round($distance) . "m\n"
                             . "🛡️ *Status:* {$statusTxt}\n\n"
                             . "🗺️ [View Pin on Google Maps]({$mapsUrl})";

                    \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $teacher->telegram_chat_id,
                        'text' => $msgText,
                        'parse_mode' => 'Markdown',
                        'disable_web_page_preview' => false
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Telegram GPS alert failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $isExempt 
                ? __('GPS Check-in recorded! (Geofence Exempt - :dist m away)', ['dist' => round($distance)])
                : __('GPS Check-in recorded successfully! Distance to campus: :dist m', ['dist' => round($distance)]),
            'distance' => round($distance),
            'latitude' => $lat,
            'longitude' => $lng,
            'record' => $attendance
        ]);
    }

    public function export(Request $request)
    {
        $teacherId = session('portal_teacher_id');
        if (!$teacherId) return redirect()->route('portal.index')->with('error', 'Unauthorized. Please login first.');

        $id = trim($request->employee_id);
        if (!$id) return redirect()->back()->with('error', 'Employee ID is required.');
        
        $teacher = Teacher::find($teacherId);
        if (!$teacher || $teacher->employee_id !== $id) {
            return redirect()->back()->with('error', 'Unauthorized. You can only export your own attendance.');
        }

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
        $teacherId = session('portal_teacher_id');
        if (!$teacherId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Please login first.'], 401);
        }
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
        if (!$teacher || $teacher->id !== $teacherId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized or teacher not found.'], 404);
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
        
        $inTime = htmlspecialchars(substr($in, 0, 5));
        
        if ($out) {
            return $inTime . ' - ' . htmlspecialchars(substr($out, 0, 5));
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
