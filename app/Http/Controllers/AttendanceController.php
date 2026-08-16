<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\Setting;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SecurityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Dashboard view and data source.
     */
    public function index(Request $request)
    {
        try {
            $this->performAutoCheckout();
            $today = today()->toDateString();
            $search = $request->search;

            // Base Query - Include teachers with 'active' status or NULL status
            $teacherBaseQuery = Teacher::where(function($q) {
                $q->where('status', 'active')->orWhereNull('status');
            });

            $totalTeachers = (clone $teacherBaseQuery)->count();
            $totalRfidTeachers = (clone $teacherBaseQuery)->has('rfidCard')->count();

            // Attendance records for today
            $attendanceQuery = Attendance::with(['teacher.rfidCard'])->whereDate('date', $today);
            if ($search) {
                $attendanceQuery->whereHas('teacher', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")->orWhere('employee_id', 'LIKE', "%{$search}%");
                });
            }
            $attendance = $attendanceQuery->orderBy('updated_at', 'desc')->get();

            // Absent teachers
            $presentIds = $attendance->pluck('teacher_id');
            $absentQuery = (clone $teacherBaseQuery)->with('rfidCard')->whereNotIn('id', $presentIds);
            if ($search) {
                $absentQuery->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")->orWhere('employee_id', 'LIKE', "%{$search}%");
                });
            }
            
            $isWorkingDay = !now()->isWeekend() && !\App\Models\Holiday::where('date', $today)->exists();
            
            if ($isWorkingDay) {
                $absentTeachers = $absentQuery->get();
            } else {
                $absentTeachers = collect();
            }

            // Stats
            $presentCount = (int)$attendance->count();
            $absentCount  = (int)$absentTeachers->count();
            $lateCount    = (int)$attendance->where(fn($a) => $a->morning_status === 'late' || $a->afternoon_status === 'late')->count();
            
            $checkinCount = (int)($attendance->whereNotNull('morning_in')->count() + $attendance->whereNotNull('afternoon_in')->count());
            $totalScans = (int)(
                $attendance->whereNotNull('morning_in')->count() +
                $attendance->whereNotNull('morning_out')->count() +
                $attendance->whereNotNull('afternoon_in')->count() +
                $attendance->whereNotNull('afternoon_out')->count()
            );
            
            // Logic for "Currently In" - Robust check for all shifts
            $isCurrentlyIn = function($a) {
                // Check if they have clocked in but not clocked out for ANY shift
                $mIn = !empty($a->morning_in);
                $mOut = !empty($a->morning_out);
                $aIn = !empty($a->afternoon_in);
                $aOut = !empty($a->afternoon_out);
                $eIn = !empty($a->evening_in);
                $eOut = !empty($a->evening_out);

                return ($mIn && !$mOut) || ($aIn && !$aOut) || ($eIn && !$eOut);
            };

            $currentlyCheckedInCount = (int)$attendance->filter($isCurrentlyIn)->count();
            $currentlyCheckedOutCount = (int)($presentCount - $currentlyCheckedInCount);

            $rate = $totalTeachers > 0 ? round(($presentCount / $totalTeachers) * 100, 1) : 0;
            $totalDepartments = (int)Department::count();
            $departments = Department::all();


            // Apply Filters to the returned lists
            $filter = $request->filter;
            if ($filter === 'present') {
                $absentTeachers = collect();
            } elseif ($filter === 'late') {
                $attendance = $attendance->where(fn($a) => $a->morning_status === 'late' || $a->afternoon_status === 'late')->values();
                $absentTeachers = collect();
            } elseif ($filter === 'absent') {
                $attendance = collect();
            } elseif ($filter === 'rfid') {
                $attendance = $attendance->filter(fn($a) => $a->teacher && $a->teacher->rfidCard !== null)->values();
                $absentTeachers = $absentTeachers->filter(fn($t) => $t->rfidCard !== null)->values();
            } elseif ($filter === 'currently_in') {
                $attendance = $attendance->filter($isCurrentlyIn)->values();
                $absentTeachers = collect();
            } elseif ($filter === 'currently_out') {
                $attendance = $attendance->filter(fn($a) => !$isCurrentlyIn($a))->values();
                $absentTeachers = collect();
            } elseif ($filter === 'checkins') {
                $absentTeachers = collect();
            }

            // Volatility & Trend Data (Last 10 days)
            $trendData = [];
            $totalT = Teacher::count();
            for ($i = 9; $i >= 0; $i--) {
                $d = today()->subDays($i);
                $presentC = Attendance::whereDate('date', $d)->where('morning_status', 'present')->count();
                $lateC    = Attendance::whereDate('date', $d)->where('morning_status', 'late')->count();
                $totalAtt = Attendance::whereDate('date', $d)->count();
                $absentC  = max(0, $totalT - $totalAtt);

                $trendData[] = [
                    'day'     => $d->locale(app()->getLocale())->isoFormat('dddd, D MMM'),
                    'present' => $presentC,
                    'late'    => $lateC,
                    'absent'  => $absentC
                ];
            }

            // Monthly Performance
            $monthStart = now()->startOfMonth();
            $topOnTime = Attendance::with('teacher')
                ->where('date', '>=', $monthStart)
                ->where('morning_status', 'present')
                ->get()
                ->groupBy('teacher_id')
                ->map(fn($group) => (object)['teacher' => $group->first()->teacher, 'count' => $group->count()])
                ->sortByDesc('count')
                ->take(5);

            $topLate = Attendance::with('teacher')
                ->where('date', '>=', $monthStart)
                ->where(function($q) {
                    $q->where('morning_status', 'late')->orWhere('afternoon_status', 'late');
                })
                ->get()
                ->groupBy('teacher_id')
                ->map(fn($group) => (object)['teacher' => $group->first()->teacher, 'count' => $group->count()])
                ->sortByDesc('count')
                ->take(5);

            // Handle AJAX/JSON requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'attendance' => $attendance,
                    'absent_teachers' => $absentTeachers,
                    'present_count' => $presentCount,
                    'late_count' => $lateCount,
                    'absent_count' => $absentCount,
                    'total' => $totalTeachers,
                    'total_rfid_teachers' => $totalRfidTeachers,
                    'total_departments' => $totalDepartments,
                    'checkin_count' => $checkinCount,
                    'total_scans' => $totalScans,
                    'currently_checked_in' => $currentlyCheckedInCount,
                    'currently_checked_out' => $currentlyCheckedOutCount,
                    'attendance_rate' => $rate,
                    'scan_alert_duration' => (int)\App\Models\Setting::getValue('scan_alert_duration', 15),
                ]);
            }

            return view('dashboard', compact(
                'attendance', 'absentTeachers', 'presentCount', 'absentCount', 'lateCount',
                'totalTeachers', 'totalRfidTeachers', 'totalDepartments', 'checkinCount', 'totalScans', 'currentlyCheckedInCount', 
                'currentlyCheckedOutCount', 'rate', 'trendData', 'topOnTime', 'topLate', 'departments'
            ));
        } catch (\Exception $e) {
            \Log::error("Dashboard Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return response("Dashboard error: " . $e->getMessage(), 500);
        }
    }

    public function deviceStatus()
    {
        try {
            $lastHeartbeat = Setting::getValue('last_scanner_heartbeat');
            $ip   = Setting::getValue('last_scanner_ip', '0.0.0.0');
            $rssi = Setting::getValue('last_scanner_rssi', '0');
            
            $online = false;
            $ago = 'Never';
            
            if ($lastHeartbeat) {
                $lastSeen = Carbon::parse($lastHeartbeat);
                $diff = $lastSeen->diffInMinutes(now());
                $online = $diff <= 3; // Within 3 minutes is considered online
                $ago = $lastSeen->diffForHumans();
            }

            return response()->json([
                'online' => $online,
                'last_seen_ago' => $ago,
                'timestamp' => $lastHeartbeat,
                'ip' => $ip,
                'rssi' => (int)$rssi
            ]);
        } catch (\Exception $e) {
            return response()->json(['online' => false, 'error' => $e->getMessage()]);
        }
    }

    public function heartbeat(Request $request): JsonResponse
    {
        Setting::updateOrCreate(['key' => 'last_scanner_heartbeat'], ['value' => now()->toDateTimeString()]);
        if($request->ip)   Setting::updateOrCreate(['key' => 'last_scanner_ip'],   ['value' => $request->ip]);
        if($request->rssi) Setting::updateOrCreate(['key' => 'last_scanner_rssi'], ['value' => $request->rssi]);

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        // Update heartbeat & telemetry on every scan attempt
        Setting::updateOrCreate(['key' => 'last_scanner_heartbeat'], ['value' => now()->toDateTimeString()]);
        if($request->ip)   Setting::updateOrCreate(['key' => 'last_scanner_ip'],   ['value' => $request->ip]);
        if($request->rssi) Setting::updateOrCreate(['key' => 'last_scanner_rssi'], ['value' => $request->rssi]);

        $ipAddress = $request->ip();
        $uid = strtoupper($request->uid ?? '');

        $request->validate([
            'uid' => 'required|string',
        ]);

        $teacher = Teacher::whereHas('rfidCard', function ($query) use ($uid) {
            $query->where('uid', $uid);
        })->first();

        // Always store the last scanned UID in cache so the RFID management page can pick it up
        \Illuminate\Support\Facades\Cache::put('pending_rfid_uid', [
            'uid' => $uid,
            'scanned_at' => now()->toDateTimeString()
        ], 60);

        if (!$teacher) {
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'action'     => 'failed_scan',
                'target'     => $uid,
                'details'    => 'Card UID not registered.',
                'ip_address' => $ipAddress,
                'timestamp'  => now(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Card UID ' . $uid . ' not registered.',
                'action'  => 'register_pending',
            ], 404);
        }

        if ($teacher->status !== 'active' && !is_null($teacher->status)) {
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'action'     => 'failed_scan',
                'target'     => $uid,
                'details'    => 'Teacher account is inactive.',
                'ip_address' => $ipAddress,
                'timestamp'  => now(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Teacher account is inactive.',
                'teacher_name' => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'photo'   => $teacher->photo ? url($teacher->photo) : null,
                'action'  => null,
            ], 403);
        }

        // --- Maintenance Check ---
        if (Setting::getValue('maintenance_mode', 'off') === 'on') {
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'action'     => 'failed_scan',
                'target'     => $uid,
                'details'    => 'System is under Maintenance.',
                'ip_address' => $ipAddress,
                'timestamp'  => now(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'System is under Maintenance.',
                'teacher_name' => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'photo'   => $teacher->photo ? url($teacher->photo) : null,
                'action'  => null,
            ], 503);
        }

        // --- IP Restriction Check ---
        $authorizedIp = Setting::getValue('authorized_ip', '');
        if (!empty($authorizedIp) && $request->ip() !== $authorizedIp) {
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'action'     => 'failed_scan',
                'target'     => $uid,
                'details'    => 'Unauthorized Scanner Device (IP: ' . $request->ip() . ')',
                'ip_address' => $ipAddress,
                'timestamp'  => now(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized Scanner Device (IP: ' . $request->ip() . ')',
                'teacher_name' => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'photo'   => $teacher->photo ? url($teacher->photo) : null,
                'action'  => null,
            ], 403);
        }

        // --- System Operating Hours ---
        $openTime  = Setting::getValue('system_open_time', '06:30');
        $closeTime = Setting::getValue('system_close_time', '18:30');
        $now       = Carbon::now();
        $timeString = $now->format('H:i:s');

        if ($timeString < $openTime || $timeString > $closeTime) {
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'action'     => 'failed_scan',
                'target'     => $uid,
                'details'    => 'System is currently Closed. (Open: ' . $openTime . '-' . $closeTime . ')',
                'ip_address' => $ipAddress,
                'timestamp'  => now(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'System is currently Closed. (Open: ' . $openTime . '-' . $closeTime . ')',
                'teacher_name' => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'photo'   => $teacher->photo ? url($teacher->photo) : null,
                'action'  => null,
            ], 403);
        }

        $today = $now->toDateString();
        $record = Attendance::firstOrCreate(
            ['teacher_id' => $teacher->id, 'date' => $today],
            ['rfid_uid' => $uid] // Set UID on initial creation
        );

        // Ensure UID is saved even if record existed (e.g. if it was created manually earlier)
        if (empty($record->rfid_uid)) {
            $record->update(['rfid_uid' => $uid]);
        }

        // --- 1. Check if there's an open check-in that needs checking out ---
        if (!empty($record->morning_in) && empty($record->morning_out)) {
            $record->update(['morning_out' => $timeString]);

            $checkIn  = Carbon::parse($record->date->format('Y-m-d') . ' ' . $record->morning_in);
            $workMins = $checkIn->diffInMinutes($now);
            $workHrs  = floor($workMins / 60);
            $workRem  = $workMins % 60;

            $this->sendTelegramNotification($teacher, 'check-out', 'Morning', $now->format('h:i:s A'));

            return response()->json([
                'status'        => 'success',
                'action'        => 'check-out',
                'teacher_name'  => $teacher->name,
                'teacher_name_kh'=> $teacher->name_kh,
                'photo'         => $teacher->photo ? url($teacher->photo) : null,
                'employee_id'   => $teacher->employee_id,
                'department'    => $teacher->department,
                'shift'         => 'Morning',
                'time'          => $now->format('h:i:s A'),
                'working_hours' => "{$workHrs}h {$workRem}m",
                'message'       => 'Morning Check-out recorded',
            ]);
        }

        if (!empty($record->afternoon_in) && empty($record->afternoon_out)) {
            $record->update(['afternoon_out' => $timeString]);

            $checkIn  = Carbon::parse($record->date->format('Y-m-d') . ' ' . $record->afternoon_in);
            $workMins = $checkIn->diffInMinutes($now);
            $workHrs  = floor($workMins / 60);
            $workRem  = $workMins % 60;

            $this->sendTelegramNotification($teacher, 'check-out', 'Afternoon', $now->format('h:i:s A'));

            return response()->json([
                'status'        => 'success',
                'action'        => 'check-out',
                'teacher_name'  => $teacher->name,
                'teacher_name_kh'=> $teacher->name_kh,
                'photo'         => $teacher->photo ? url($teacher->photo) : null,
                'employee_id'   => $teacher->employee_id,
                'department'    => $teacher->department,
                'shift'         => 'Afternoon',
                'time'          => $now->format('h:i:s A'),
                'working_hours' => "{$workHrs}h {$workRem}m",
                'message'       => 'Afternoon Check-out recorded',
            ]);
        }

        // --- 2. If NO open shift, determine Shift based on current time for CHECK-IN ---
        $hourFloat = $now->hour + ($now->minute / 60);
        
        $morningStartStr   = Setting::getValue('morning_shift_start', '05:00');
        $morningEndStr     = Setting::getValue('morning_shift_end', '12:00');
        $afternoonStartStr = Setting::getValue('afternoon_shift_start', '12:00');
        $afternoonEndStr   = Setting::getValue('afternoon_shift_end', '17:30');

        $morningStart   = explode(':', $morningStartStr);
        $morningEnd     = explode(':', $morningEndStr);
        $afternoonStart = explode(':', $afternoonStartStr);
        $afternoonEnd   = explode(':', $afternoonEndStr);

        $mStartFloat = ($morningStart[0] ?? 5) + (($morningStart[1] ?? 0) / 60);
        $mEndFloat   = ($morningEnd[0] ?? 12) + (($morningEnd[1] ?? 0) / 60);
        $aStartFloat = ($afternoonStart[0] ?? 12) + (($afternoonStart[1] ?? 0) / 60);
        $aEndFloat   = ($afternoonEnd[0] ?? 17.5) + (($afternoonEnd[1] ?? 30) / 60);

        $shiftType = null;
        $inCol = null;
        $outCol = null;
        $statusCol = null;
        $lateCutoff = null;

        if ($hourFloat >= $mStartFloat && $hourFloat < $mEndFloat) {
            $shiftType = 'Morning';
            $inCol = 'morning_in';
            $outCol = 'morning_out';
            $statusCol = 'morning_status';
            
            $morningLate = Setting::getValue('morning_late_cutoff', '07:45');
            $parts = explode(':', $morningLate);
            $lateCutoff = Carbon::today()->setTime($parts[0] ?? 7, $parts[1] ?? 45);
        } elseif ($hourFloat >= $aStartFloat && $hourFloat < $aEndFloat) {
            $shiftType = 'Afternoon';
            $inCol = 'afternoon_in';
            $outCol = 'afternoon_out';
            $statusCol = 'afternoon_status';
            
            $afternoonLate = Setting::getValue('afternoon_late_cutoff', '14:15');
            $parts = explode(':', $afternoonLate);
            $lateCutoff = Carbon::today()->setTime($parts[0] ?? 14, $parts[1] ?? 15);
        } else {
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'action'     => 'failed_scan',
                'target'     => $uid,
                'details'    => 'No active shift at ' . $timeString,
                'ip_address' => $ipAddress,
                'timestamp'  => now(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'No active shift for scanning at this time.',
                'teacher_name' => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'photo'   => $teacher->photo ? url($teacher->photo) : null,
                'action'  => null,
            ], 400);
        }

        if (!is_null($record->$outCol)) {
            $record->touch(); // Force update for live monitor
            return response()->json([
                'status'  => 'info',
                'message' => 'You have already completed the ' . $shiftType . ' shift.',
                'teacher_name' => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'photo'   => $teacher->photo ? url($teacher->photo) : null,
                'action'  => 'already-scanned',
            ]);
        }

        // Perform Check-In
        $status = $now->greaterThan($lateCutoff) ? 'late' : 'present';
        $record->update([
            $inCol     => $timeString,
            $statusCol => $status,
        ]);

        $this->sendTelegramNotification($teacher, 'check-in', $shiftType, $now->format('h:i:s A'), $status);

        return response()->json([
            'status'           => 'success',
            'action'           => 'check-in',
            'teacher_name'     => $teacher->name,
            'teacher_name_kh'  => $teacher->name_kh,
            'photo'            => $teacher->photo ? url($teacher->photo) : null,
            'employee_id'      => $teacher->employee_id,
            'department'       => $teacher->department,
            'shift'            => $shiftType,
            'time'             => $now->format('h:i:s A'),
            'attendance_status'=> $status,
            'message'          => $shiftType . ' Check-in recorded',
        ]);
    }

    /**
     * Handle manual scan from the Admin Dashboard (Search & Scan).
     */
    public function adminScan(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $teacher = Teacher::find($request->teacher_id);
        $now = Carbon::now();
        $today = $now->toDateString();
        $timeString = $now->format('H:i:s');

        $record = Attendance::firstOrCreate(
            ['teacher_id' => $teacher->id, 'date' => $today]
        );

        // --- 1. Check if there's an open check-in that needs checking out ---
        if (!empty($record->morning_in) && empty($record->morning_out)) {
            $record->update(['morning_out' => $timeString]);

            $checkIn  = Carbon::parse($record->date->format('Y-m-d') . ' ' . $record->morning_in);
            $workMins = $checkIn->diffInMinutes($now);
            $workHrs  = floor($workMins / 60);
            $workRem  = $workMins % 60;

            $this->sendTelegramNotification($teacher, 'check-out', 'Morning', $now->format('h:i:s A'));

            return response()->json([
                'status'        => 'success',
                'action'        => 'check-out',
                'teacher_name'  => $teacher->name,
                'teacher_name_kh'=> $teacher->name_kh,
                'photo'         => $teacher->photo ? url($teacher->photo) : null,
                'employee_id'   => $teacher->employee_id,
                'department'    => $teacher->department,
                'shift'         => 'Morning',
                'time'          => $now->format('h:i:s A'),
                'working_hours' => "{$workHrs}h {$workRem}m",
                'message'       => "Morning Check-out recorded for {$teacher->name}",
            ]);
        }

        if (!empty($record->afternoon_in) && empty($record->afternoon_out)) {
            $record->update(['afternoon_out' => $timeString]);

            $checkIn  = Carbon::parse($record->date->format('Y-m-d') . ' ' . $record->afternoon_in);
            $workMins = $checkIn->diffInMinutes($now);
            $workHrs  = floor($workMins / 60);
            $workRem  = $workMins % 60;

            $this->sendTelegramNotification($teacher, 'check-out', 'Afternoon', $now->format('h:i:s A'));

            return response()->json([
                'status'        => 'success',
                'action'        => 'check-out',
                'teacher_name'  => $teacher->name,
                'teacher_name_kh'=> $teacher->name_kh,
                'photo'         => $teacher->photo ? url($teacher->photo) : null,
                'employee_id'   => $teacher->employee_id,
                'department'    => $teacher->department,
                'shift'         => 'Afternoon',
                'time'          => $now->format('h:i:s A'),
                'working_hours' => "{$workHrs}h {$workRem}m",
                'message'       => "Afternoon Check-out recorded for {$teacher->name}",
            ]);
        }

        // --- 2. If NO open shift, determine current shift for CHECK-IN ---
        $hourFloat = $now->hour + ($now->minute / 60);
        
        $morningStartStr   = Setting::getValue('morning_shift_start', '05:00');
        $morningEndStr     = Setting::getValue('morning_shift_end', '12:00');
        $afternoonStartStr = Setting::getValue('afternoon_shift_start', '12:00');
        $afternoonEndStr   = Setting::getValue('afternoon_shift_end', '17:30');

        $morningStart   = explode(':', $morningStartStr);
        $morningEnd     = explode(':', $morningEndStr);
        $afternoonStart = explode(':', $afternoonStartStr);
        $afternoonEnd   = explode(':', $afternoonEndStr);

        $mStartFloat = ($morningStart[0] ?? 5) + (($morningStart[1] ?? 0) / 60);
        $mEndFloat   = ($morningEnd[0] ?? 12) + (($morningEnd[1] ?? 0) / 60);
        $aStartFloat = ($afternoonStart[0] ?? 12) + (($afternoonStart[1] ?? 0) / 60);
        $aEndFloat   = ($afternoonEnd[0] ?? 17.5) + (($afternoonEnd[1] ?? 30) / 60);

        $shiftType  = null;
        $inCol      = null;
        $outCol     = null;
        $statusCol  = null;
        $lateCutoff = null;

        if ($hourFloat >= $mStartFloat && $hourFloat < $mEndFloat) {
            $shiftType  = 'Morning';
            $inCol      = 'morning_in';
            $outCol     = 'morning_out';
            $statusCol  = 'morning_status';
            
            $morningLate = Setting::getValue('morning_late_cutoff', '07:45');
            $parts = explode(':', $morningLate);
            $lateCutoff = Carbon::today()->setTime($parts[0] ?? 7, $parts[1] ?? 45);
        } elseif ($hourFloat >= $aStartFloat && $hourFloat < $aEndFloat) {
            $shiftType  = 'Afternoon';
            $inCol      = 'afternoon_in';
            $outCol     = 'afternoon_out';
            $statusCol  = 'afternoon_status';
            
            $afternoonLate = Setting::getValue('afternoon_late_cutoff', '14:15');
            $parts = explode(':', $afternoonLate);
            $lateCutoff = Carbon::today()->setTime($parts[0] ?? 14, $parts[1] ?? 15);
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'No active shift at this time.',
                'shift'   => null,
            ], 400);
        }

        if (!is_null($record->$outCol)) {
            return response()->json([
                'status'       => 'info',
                'action'       => 'already-checked-out',
                'teacher_name' => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'photo'        => $teacher->photo ? url($teacher->photo) : null,
                'shift'        => $shiftType,
                'message'      => "{$teacher->name} already checked out for {$shiftType} shift",
            ]);
        }

        // Perform Check-In
        $status = $now->greaterThan($lateCutoff) ? 'late' : 'present';
        $record->update([
            $inCol    => $timeString,
            $statusCol => $status,
        ]);

        $this->sendTelegramNotification($teacher, 'check-in', $shiftType, $now->format('h:i:s A'), $status);

        return response()->json([
            'status'           => 'success',
            'action'           => 'check-in',
            'teacher_name'     => $teacher->name,
            'teacher_name_kh'  => $teacher->name_kh,
            'photo'            => $teacher->photo ? url($teacher->photo) : null,
            'employee_id'      => $teacher->employee_id,
            'department'       => $teacher->department,
            'shift'            => $shiftType,
            'time'             => $now->format('h:i:s A'),
            'attendance_status'=> $status,
            'message'          => "{$shiftType} Check-in recorded for {$teacher->name}",
        ]);
    }

    public function scanPage()
    {
        $teachers = Teacher::where(function($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->has('rfidCard')
            ->orderBy('name')
            ->get();
        $today = today();
        
        $morningIn = Attendance::whereDate('date', $today)->whereNotNull('morning_in')->count();
        $morningOut = Attendance::whereDate('date', $today)->whereNotNull('morning_out')->count();
        $morningScans = $morningIn + $morningOut;

        $afternoonIn = Attendance::whereDate('date', $today)->whereNotNull('afternoon_in')->count();
        $afternoonOut = Attendance::whereDate('date', $today)->whereNotNull('afternoon_out')->count();
        $afternoonScans = $afternoonIn + $afternoonOut;

        $totalScans = $morningScans + $afternoonScans;

        $departments = Department::all();

        // Attendance rules from Settings
        $systemOpen     = Setting::getValue('system_open_time', '06:30');
        $systemClose    = Setting::getValue('system_close_time', '18:30');
        $morningStart   = Setting::getValue('morning_shift_start', '05:00');
        $morningEnd     = Setting::getValue('morning_shift_end', '12:00');
        $afternoonStart = Setting::getValue('afternoon_shift_start', '12:00');
        $afternoonEnd   = Setting::getValue('afternoon_shift_end', '17:30');

        return view('scan', compact(
            'teachers', 'totalScans', 'morningScans', 'afternoonScans', 'departments',
            'systemOpen', 'systemClose', 'morningStart', 'morningEnd', 'afternoonStart', 'afternoonEnd'
        ));
    }

    public function list(Request $request): JsonResponse
    {
        $query = Attendance::with('teacher')
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->when($request->teacher_id, fn($q) => $q->where('teacher_id', $request->teacher_id));

        return response()->json($query->orderBy('date', 'desc')->orderBy('morning_in')->get());
    }

    public function liveMonitor()
    {
        $departments = Department::all();
        return view('live', compact('departments'));
    }

    public function latest(Request $request): JsonResponse
    {
        $today = today()->toDateString();
        
        $query = Attendance::with('teacher')
            ->whereDate('date', $today);

        if ($request->filled('last_updated_at')) {
            $query->where('updated_at', '>=', Carbon::parse($request->last_updated_at));
        }

        $latestScans = $query->orderBy('updated_at', 'desc')->take(10)->get();
        $serverTime = Carbon::now()->format('Y-m-d H:i:s');

        // Stats for live monitor
        $presentCount = (int)Attendance::whereDate('date', $today)->count();
        $lateCount = (int)Attendance::whereDate('date', $today)
            ->where(function($q) {
                $q->where('morning_status', 'late')->orWhere('afternoon_status', 'late');
            })->count();

        $totalTeachers = (int)Teacher::where(function($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->count();

        // New: Department-wise stats
        $deptStats = [];
        $depts = Department::withCount(['teachers' => function($q) {
            $q->where(function($sq) {
                $sq->where('status', 'active')->orWhereNull('status');
            });
        }])->get();

        foreach ($depts as $d) {
            $presentInDept = Attendance::whereDate('date', $today)
                ->whereHas('teacher', function($q) use ($d) {
                    $q->where('department', $d->name);
                })->count();
            
            $deptStats[] = [
                'name' => $d->name,
                'name_kh' => $d->name_kh ?? $d->name,
                'total' => $d->teachers_count,
                'present' => $presentInDept,
                'percentage' => $d->teachers_count > 0 ? round(($presentInDept / $d->teachers_count) * 100) : 0
            ];
        }

        // New: Shift Information
        $hourFloat = Carbon::now()->hour + (Carbon::now()->minute / 60);
        $mStart = explode(':', Setting::getValue('morning_shift_start', '05:00'));
        $mEnd   = explode(':', Setting::getValue('morning_shift_end', '12:00'));
        $aStart = explode(':', Setting::getValue('afternoon_shift_start', '12:00'));
        $aEnd   = explode(':', Setting::getValue('afternoon_shift_end', '17:30'));

        $mStartF = ($mStart[0] ?? 5) + (($mStart[1] ?? 0) / 60);
        $mEndF   = ($mEnd[0] ?? 12) + (($mEnd[1] ?? 0) / 60);
        $aStartF = ($aStart[0] ?? 12) + (($aStart[1] ?? 0) / 60);
        $aEndF   = ($aEnd[0] ?? 17.5) + (($aEnd[1] ?? 30) / 60);

        $currentShift = 'Out of Shift';
        $cutoff = '--:--';
        if ($hourFloat >= $mStartF && $hourFloat < $mEndF) {
            $currentShift = 'Morning Shift';
            $cutoff = Setting::getValue('morning_late_cutoff', '07:45');
        } elseif ($hourFloat >= $aStartF && $hourFloat < $aEndF) {
            $currentShift = 'Afternoon Shift';
            $cutoff = Setting::getValue('afternoon_late_cutoff', '14:15');
        }

        $data = $latestScans->map(function($a) {
            $times = [
                ['type' => 'Morning In', 'val' => $a->morning_in, 'is_in' => true],
                ['type' => 'Morning Out', 'val' => $a->morning_out, 'is_in' => false],
                ['type' => 'Afternoon In', 'val' => $a->afternoon_in, 'is_in' => true],
                ['type' => 'Afternoon Out', 'val' => $a->afternoon_out, 'is_in' => false],
            ];
            
            $validTimes = array_filter($times, fn($t) => !is_null($t['val']));
            usort($validTimes, fn($t1, $t2) => strcmp($t2['val'], $t1['val']));
            
            $latestScan = count($validTimes) > 0 ? $validTimes[0] : ['type' => 'Unknown', 'val' => '', 'is_in' => true];
            
            return [
                'id' => 'att_' . $a->id,
                'teacher_name' => $a->teacher->name ?? 'Unknown',
                'teacher_name_kh' => $a->teacher->name_kh ?? '',
                'department' => $a->teacher->department ?? '',
                'photo' => $a->teacher->photo ? url($a->teacher->photo) : null,
                'type' => $latestScan['is_in'] ? 'check-in' : 'check-out',
                'shift_label' => $latestScan['type'],
                'time' => $latestScan['val'],
                'status' => 'success',
                'updated_at' => $a->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        // Also fetch failed scans from security_logs
        $failedLogs = \Illuminate\Support\Facades\DB::table('security_logs')
            ->where('action', 'failed_scan')
            ->where('timestamp', '>=', Carbon::today())
            ->when($request->filled('last_updated_at'), function($q) use ($request) {
                $q->where('timestamp', '>=', Carbon::parse($request->last_updated_at));
            })
            ->orderBy('timestamp', 'desc')
            ->take(5)
            ->get();

        foreach ($failedLogs as $log) {
            $uid = $log->target;
            // Lookup teacher by card UID
            $teacher = Teacher::whereHas('rfidCard', function ($query) use ($uid) {
                $query->where('uid', $uid);
            })->first();

            // Filter out if card is unregistered or empty
            if (!$teacher) {
                continue;
            }

            $data[] = [
                'id'              => 'log_' . $log->id,
                'teacher_name'    => $teacher->name,
                'teacher_name_kh' => $teacher->name_kh,
                'department'      => $teacher->department ?: $uid, // fallback
                'photo'           => $teacher->photo ? url($teacher->photo) : null,
                'type'            => 'error',
                'shift_label'     => 'ERROR',
                'time'            => Carbon::parse($log->timestamp)->format('h:i:s A'),
                'status'          => 'error',
                'message'         => $log->details,
                'updated_at'      => Carbon::parse($log->timestamp)->format('Y-m-d H:i:s'),
            ];
        }

        // Sort by updated_at desc
        usort($data, function($a, $b) {
            return strcmp($b['updated_at'], $a['updated_at']);
        });

        return response()->json([
            'server_time' => $serverTime,
            'settings_updated_at' => (int)Setting::getValue('settings_updated_at', 0),
            'scan_alert_duration' => (int)Setting::getValue('scan_alert_duration', 15),
            'stats' => [
                'present' => $presentCount,
                'late' => $lateCount,
                'total' => $totalTeachers,
                'remaining' => max(0, $totalTeachers - $presentCount),
                'rate' => $totalTeachers > 0 ? round(($presentCount / $totalTeachers) * 100) : 0
            ],
            'shift' => [
                'label' => $currentShift,
                'cutoff' => $cutoff,
                'open_time' => Setting::getValue('system_open_time', '06:30'),
                'close_time' => Setting::getValue('system_close_time', '18:30'),
            ],
            'departments' => $deptStats,
            'scans' => array_slice($data, 0, 10),
        ]);
    }

    /**
     * Manual adjustment from Admin Panel.
     */
    /**
     * Automatically check out teachers who forgot to scan.
     */
    protected function performAutoCheckout()
    {
        // Check if Auto Check-Out is enabled
        if (Setting::getValue('enable_auto_checkout', 'on') !== 'on') {
            return;
        }

        $mEnd = Setting::getValue('morning_shift_end', '12:00');
        $aEnd = Setting::getValue('afternoon_shift_end', '17:30');
        $delay = (int)Setting::getValue('auto_checkout_delay', '30');
        
        $now = now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        $records = Attendance::where(function($q) use ($today, $yesterday) {
                $q->whereDate('date', $today)->orWhereDate('date', $yesterday);
            })
            ->where(function($q) {
                $q->where(function($sq) {
                    $sq->whereNotNull('morning_in')->whereNull('morning_out');
                })->orWhere(function($sq) {
                    $sq->whereNotNull('afternoon_in')->whereNull('afternoon_out');
                });
            })
            ->get();

        foreach ($records as $record) {
            $updated = false;
            $recordDateStr = $record->date->format('Y-m-d');
            
            // Check Morning Shift
            if ($record->morning_in && !$record->morning_out) {
                $shiftEndTime = Carbon::parse($recordDateStr . ' ' . $mEnd);
                if ($now->greaterThan($shiftEndTime->addMinutes($delay))) {
                    $record->morning_out = $mEnd;
                    $record->manual_note = trim(($record->manual_note ?? '') . ' [Auto Morning Checkout]');
                    $updated = true;
                }
            }

            // Check Afternoon Shift
            if ($record->afternoon_in && !$record->afternoon_out) {
                $shiftEndTime = Carbon::parse($recordDateStr . ' ' . $aEnd);
                if ($now->greaterThan($shiftEndTime->addMinutes($delay))) {
                    $record->afternoon_out = $aEnd;
                    $record->manual_note = trim(($record->manual_note ?? '') . ' [Auto Afternoon Checkout]');
                    $updated = true;
                }
            }

            if ($updated) {
                $record->save();
            }
        }
    }

    public function manualAdjustment(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:attendance,id',
            'morning_in' => 'nullable|string',
            'morning_out' => 'nullable|string',
            'afternoon_in' => 'nullable|string',
            'afternoon_out' => 'nullable|string',
            'morning_status' => 'nullable|string|in:present,late',
            'afternoon_status' => 'nullable|string|in:present,late',
            'note' => 'nullable|string|max:255',
        ]);

        $attendance = Attendance::with('teacher')->findOrFail($request->id);
        
        $oldData = $attendance->toArray();
        
        $attendance->update([
            'morning_in' => $request->morning_in ?: null,
            'morning_out' => $request->morning_out ?: null,
            'afternoon_in' => $request->afternoon_in ?: null,
            'afternoon_out' => $request->afternoon_out ?: null,
            'morning_status' => $request->morning_status ?: 'present',
            'afternoon_status' => $request->afternoon_status ?: 'present',
            'manual_note' => $request->note,
        ]);

        SecurityLog::record('Manual Adjustment', $attendance->teacher->name, "Record ID: {$attendance->id}. Note: " . ($request->note ?? 'None'));

        return response()->json(['status' => 'success', 'message' => 'Attendance record adjusted manually.']);
    }
    private function sendTelegramNotification($teacher, $action, $shift, $time, $status = 'present')
    {
        if (empty($teacher->telegram_chat_id)) return;
        
        $token = Setting::getValue('telegram_bot_token');
        if (empty($token)) return;

        $icon = $action === 'check-in' ? '✅' : '👋';
        $lateIcon = $status === 'late' ? ' (⚠️ Late)' : '';
        $message = "{$icon} *{$teacher->name}*\n{$shift} {$action} recorded at {$time}{$lateIcon}";

        try {
            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $teacher->telegram_chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            \Log::error('Telegram Notification Error: ' . $e->getMessage());
        }
    }
}
