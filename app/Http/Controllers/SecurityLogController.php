<?php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SecurityLogController extends Controller
{
    public function index()
    {
        $logs = SecurityLog::with('admin')
            ->orderBy('timestamp', 'desc')
            ->paginate(50);
            
        $todayCheckIns = \App\Models\Attendance::with('teacher')
            ->whereDate('date', \Carbon\Carbon::today())
            ->where(function($q) {
                $q->whereNotNull('morning_in')->orWhereNotNull('afternoon_in');
            })
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('security.index', compact('logs', 'todayCheckIns'));
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            
            $this->log('Cleared System Cache', 'System');
            
            return back()->with('success', 'System cache cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function integrityCheck()
    {
        // Simple integrity checks
        $orphanedAttendance = DB::table('attendance')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('teachers')
                    ->whereRaw('teachers.id = attendance.teacher_id');
            })->count();

        $orphanedRfid = DB::table('rfid_cards')
            ->whereNotNull('teacher_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('teachers')
                    ->whereRaw('teachers.id = rfid_cards.teacher_id');
            })->count();

        return response()->json([
            'orphaned_attendance' => $orphanedAttendance,
            'orphaned_rfid' => $orphanedRfid,
            'status' => ($orphanedAttendance + $orphanedRfid === 0) ? 'Healthy' : 'Issues Found'
        ]);
    }

    private function log($action, $target, $details = null)
    {
        SecurityLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'target' => $target,
            'ip_address' => request()->ip(),
            'details' => $details,
            'timestamp' => now()
        ]);
    }
}
