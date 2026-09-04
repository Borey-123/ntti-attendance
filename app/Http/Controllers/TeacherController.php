<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\RfidCard;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with('rfidCard');
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('name_kh', 'like', "%{$request->search}%")
                  ->orWhere('employee_id', 'like', "%{$request->search}%")
                  ->orWhere('department', 'like', "%{$request->search}%");
        }
        if ($request->department) {
            $query->where('department', $request->department);
        }

        if ($request->filter === 'pending') {
            $query->whereDoesntHave('rfidCard');
        } elseif ($request->filter === 'assigned') {
            $query->whereHas('rfidCard');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $teachers = $query->orderBy('employee_id')->get();
        $departments = \App\Models\Department::all();

        if ($request->expectsJson()) {
            return response()->json($teachers);
        }
        return view('teachers.index', compact('teachers', 'departments'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|unique:teachers,phone',
            'position' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string|max:255',
            'is_geofence_exempt' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->has('is_geofence_exempt')) {
            $validated['is_geofence_exempt'] = filter_var($request->is_geofence_exempt, FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($file->isValid()) {
                $mime = $file->getMimeType();
                $validated['photo'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }
        }

        // Set a temporary unique employee_id because it's required and unique in DB
        $validated['employee_id'] = 'TEMP_' . uniqid();

        $teacher = Teacher::create($validated);
        
        // Update employee_id with the actual primary ID (padded)
        $teacher->update([
            'employee_id' => 'T' . str_pad($teacher->id, 4, '0', STR_PAD_LEFT)
        ]);

        SecurityLog::record('Created Teacher', $teacher->name, "ID: {$teacher->employee_id}");

        return response()->json(['status' => 'success', 'teacher' => $teacher], 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json($teacher->load('rfidCard'));
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'department' => 'string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|unique:teachers,phone,' . $teacher->id,
            'position' => 'nullable|string',
            'status' => 'in:active,inactive',
            'telegram_chat_id' => 'nullable|string|max:255',
            'is_geofence_exempt' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->has('is_geofence_exempt')) {
            $validated['is_geofence_exempt'] = filter_var($request->is_geofence_exempt, FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->remove_photo) {
            if ($teacher->photo) {
                $oldPath = str_replace('/storage/', '', $teacher->photo);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $validated['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($file->isValid()) {
                $mime = $file->getMimeType();
                $validated['photo'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }
        }

        $teacher->update($validated);
        
        SecurityLog::record('Updated Teacher', $teacher->name, "ID: {$teacher->employee_id}");
        
        return response()->json(['status' => 'success', 'teacher' => $teacher]);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $name = $teacher->name;
        $id = $teacher->employee_id;
        $teacher->delete();
        
        SecurityLog::record('Deleted Teacher', $name, "ID: $id");
        
        return response()->json(['status' => 'success', 'message' => 'Teacher deleted.']);
    }

    public function insights(Teacher $teacher): JsonResponse
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Get all attendance for this teacher this month
        $attendance = \App\Models\Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $presentDays = $attendance->count();
        $lateDays = $attendance->filter(function($a) {
            return $a->morning_status === 'late' || $a->afternoon_status === 'late';
        })->count();

        // Calculate Punctuality Score (100% base, -10% per late arrival, min 0)
        $score = $presentDays > 0 ? max(0, 100 - ($lateDays * 10)) : 100;
        if ($presentDays === 0) $score = 0;

        // Last 5 scans
        $recent = $attendance->sortByDesc('date')->take(5)->values()->map(function($a) {
            return [
                'date' => \Carbon\Carbon::parse($a->date)->format('M d, Y'),
                'morning' => $a->morning_in ? substr($a->morning_in, 0, 5) : '—',
                'afternoon' => $a->afternoon_in ? substr($a->afternoon_in, 0, 5) : '—',
                'status' => ($a->morning_status === 'late' || $a->afternoon_status === 'late') ? 'late' : 'on-time'
            ];
        });

        return response()->json([
            'teacher' => $teacher,
            'stats' => [
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'score' => round($score),
                'month' => $now->format('F Y')
            ],
            'recent' => $recent
        ]);
    }

    public function departments(): JsonResponse
    {
        return response()->json(\App\Models\Department::pluck('name'));
    }

    public function import(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return back()->with('error', 'No file uploaded or the uploaded file exceeds the PHP server upload size limit.');
        }

        try {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $realPath = $file->getRealPath();

            if (!$realPath || !file_exists($realPath)) {
                return back()->with('error', 'Unable to read uploaded file. Please try again.');
            }

            $content = file_get_contents($realPath);
            $importedCount = 0;

            if (in_array($ext, ['sql'])) {
                $driver = \DB::connection()->getDriverName();
                $pdo = \DB::connection()->getPdo();

                if ($driver === 'mysql') {
                    @$pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
                    try {
                        \DB::unprepared($content);
                        $importedCount = substr_count(strtolower($content), 'insert into');
                    } catch (\Throwable $ex) {
                        $statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/', $content)), fn($stmt) => !empty($stmt));
                        foreach ($statements as $statement) {
                            if (strlen($statement) > 2) {
                                try {
                                    $pdo->exec($statement);
                                    if (str_contains(strtolower($statement), 'insert into')) {
                                        $importedCount++;
                                    }
                                } catch (\Throwable $err) {}
                            }
                        }
                    }
                    @$pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
                } else {
                    $sql = $content;
                    $sql = preg_replace('/\/\*!\d+.*?\*\//s', '', $sql) ?? $sql;
                    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
                    $sql = preg_replace('/START\s+TRANSACTION;/i', '', $sql) ?? $sql;
                    $sql = preg_replace('/ENGINE\s*=\s*\w+/i', '', $sql) ?? $sql;
                    $sql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql) ?? $sql;
                    $sql = preg_replace('/COLLATE\s*=\s*[\w_]+/i', '', $sql) ?? $sql;
                    $sql = preg_replace('/AUTO_INCREMENT\s*=\s*\d+/i', '', $sql) ?? $sql;

                    $statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/', $sql)), fn($stmt) => !empty($stmt));

                    $pdo->exec('PRAGMA foreign_keys = OFF;');
                    foreach ($statements as $statement) {
                        if (strlen($statement) > 2) {
                            try {
                                $pdo->exec($statement);
                                if (str_contains(strtolower($statement), 'insert into')) {
                                    $importedCount++;
                                }
                            } catch (\Throwable $ex) {}
                        }
                    }
                    $pdo->exec('PRAGMA foreign_keys = ON;');
                }
            } else {
                $lines = file($realPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $index => $line) {
                    $row = str_getcsv($line);
                    if ($index === 0 && (str_contains(strtolower($row[0] ?? ''), 'name') || str_contains(strtolower($row[0] ?? ''), 'id'))) {
                        continue;
                    }
                    if (count($row) >= 2) {
                        $empId = !empty($row[0]) ? trim($row[0]) : 'T' . str_pad($index, 4, '0', STR_PAD_LEFT);
                        $name = trim($row[1] ?? 'Teacher ' . $index);
                        $nameKh = trim($row[2] ?? '');
                        $dept = trim($row[3] ?? 'General');
                        $phone = trim($row[4] ?? null);

                        Teacher::updateOrCreate(
                            ['employee_id' => $empId],
                            [
                                'name' => $name,
                                'name_kh' => $nameKh,
                                'department' => $dept,
                                'phone' => $phone,
                                'status' => 'active'
                            ]
                        );
                        $importedCount++;
                    }
                }
            }

            SecurityLog::record('Imported Teachers', "Count: {$importedCount}");

            return back()->with('success', "Teacher list imported successfully! {$importedCount} statements/records processed.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to import teacher file: ' . $e->getMessage());
        }
    }

    public function resetPin(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        $teacher->update([
            'portal_pin' => \Illuminate\Support\Facades\Hash::make($validated['pin'])
        ]);

        SecurityLog::record('Reset Teacher PIN', $teacher->name, "ID: {$teacher->employee_id}");

        return response()->json(['status' => 'success', 'message' => 'Teacher PIN has been successfully reset.']);
    }

    public function registerFace(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate([
            'face_descriptor' => 'required|string'
        ]);

        $teacher->update([
            'face_descriptor' => $validated['face_descriptor']
        ]);

        SecurityLog::record('Registered Face Data', $teacher->name, "ID: {$teacher->employee_id}");

        return response()->json(['status' => 'success', 'message' => 'Face data registered successfully.']);
    }

    public function getFaceDescriptors(): JsonResponse
    {
        $teachers = Teacher::whereNotNull('face_descriptor')
            ->where('status', 'active')
            ->select('id', 'employee_id', 'name', 'face_descriptor')
            ->get();
            
        return response()->json($teachers);
    }

    public function deleteFace(Teacher $teacher): JsonResponse
    {
        $teacher->update([
            'face_descriptor' => null
        ]);

        SecurityLog::record('Deleted Face Data', $teacher->name, "ID: {$teacher->employee_id}");

        return response()->json(['status' => 'success', 'message' => 'Face data deleted successfully.']);
    }
}
