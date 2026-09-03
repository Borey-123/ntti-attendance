<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25);

        if ($request->wantsJson()) {
            return response()->json($logs);
        }

        return view('security.audit_logs', compact('logs'));
    }

    public function clear(Request $request)
    {
        AuditLog::truncate();
        AuditLog::log('audit_logs_cleared', 'Administrator cleared all system audit logs.');

        return response()->json(['success' => true, 'message' => __('Audit logs cleared successfully.')]);
    }
}
