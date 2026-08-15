<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\SecurityLog;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use Carbon\Carbon;

class SettingController extends Controller
{
    public function index()
    {
        $universityName = Setting::getValue('university_name', 'NTTI System');
        $universityLogo = Setting::getAssetUrl('university_logo', '/images/ntti_logo.png');
        $primaryColor   = Setting::getValue('primary_color', '#00d4a0');
        $defaultTheme   = Setting::getValue('default_theme', 'dark');
        $admins         = User::all();
        $morningLate    = Setting::getValue('morning_late_cutoff', '07:45');
        $afternoonLate  = Setting::getValue('afternoon_late_cutoff', '14:15');
        $workingDays    = json_decode(Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]'));
        $maintenanceMode = Setting::getValue('maintenance_mode', 'off');
        $authorizedIp   = Setting::getValue('authorized_ip', '');
        $systemOpen     = Setting::getValue('system_open_time', '06:30');
        $systemClose    = Setting::getValue('system_close_time', '18:30');
        $loginBg        = Setting::getAssetUrl('login_bg', '/images/bg-login.jpg');
        $morningStart   = Setting::getValue('morning_shift_start', '05:00');
        $morningEnd     = Setting::getValue('morning_shift_end', '12:00');
        $afternoonStart = Setting::getValue('afternoon_shift_start', '12:00');
        $afternoonEnd   = Setting::getValue('afternoon_shift_end', '17:30');
        $scanAlertDuration = Setting::getValue('scan_alert_duration', '15');
        $fontSize       = Setting::getValue('font_size', '14');
        $iconSize       = Setting::getValue('global_icon_size', '1.1');
        $universityWebsite = Setting::getValue('university_website', '');
        $universityFacebook = Setting::getValue('university_facebook', '');
        $corrections = AttendanceCorrection::with('teacher')->orderBy('created_at', 'desc')->get();
        $enableAutoCheckout = Setting::getValue('enable_auto_checkout', 'on');
        $autoCheckoutDelay  = Setting::getValue('auto_checkout_delay', '30');
        $telegramBotToken   = Setting::getValue('telegram_bot_token', '');
        $holidays           = \App\Models\Holiday::orderBy('date', 'desc')->get();
        $fontFamily         = Setting::getValue('font_family', 'Inter');
        $borderRadius       = Setting::getValue('border_radius', '0.5rem');
        $enableGlassmorphism = Setting::getValue('enable_glassmorphism', 'on');
        $glassBlur          = Setting::getValue('glass_blur', '24');
        $glassOpacity       = Setting::getValue('glass_opacity', '0.25');
        $glassBorder        = Setting::getValue('glass_border', 'subtle');
        $glassNoise         = Setting::getValue('glass_noise', 'on');
        $liveRadarSize      = Setting::getValue('live_radar_size', '360');

        return view('settings.index', compact(
            'universityName', 'universityLogo', 'primaryColor', 'defaultTheme', 
            'admins', 'morningLate', 'afternoonLate', 'workingDays', 'maintenanceMode', 'authorizedIp',
            'systemOpen', 'systemClose', 'loginBg',
            'morningStart', 'morningEnd', 'afternoonStart', 'afternoonEnd', 'scanAlertDuration',
            'fontSize', 'iconSize', 'fontFamily', 'borderRadius', 'enableGlassmorphism',
            'glassBlur', 'glassOpacity', 'glassBorder', 'glassNoise',
            'universityWebsite', 'universityFacebook', 'corrections',
            'enableAutoCheckout', 'autoCheckoutDelay', 'telegramBotToken', 'holidays',
            'liveRadarSize'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'university_logo' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'login_bg' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'morning_late_cutoff' => 'nullable|string',
            'afternoon_late_cutoff' => 'nullable|string',
            'authorized_ip' => 'nullable|string|max:45',
            'working_days' => 'nullable|array',
            'maintenance_mode' => 'nullable|string|in:on,off',
            'system_open_time' => 'nullable|string',
            'system_close_time' => 'nullable|string',
            'morning_shift_start' => 'nullable|string',
            'morning_shift_end' => 'nullable|string',
            'afternoon_shift_start' => 'nullable|string',
            'afternoon_shift_end' => 'nullable|string',
            'scan_alert_duration' => 'nullable|integer|min:1|max:60',
            'university_website' => 'nullable|url|max:255',
            'university_facebook' => 'nullable|url|max:255',
            'enable_auto_checkout' => 'nullable|string|in:on,off',
            'auto_checkout_delay' => 'nullable|integer|min:0|max:1440',
            'telegram_bot_token' => 'nullable|string',
        ]);

        try {
            if ($request->has('university_name')) {
                Setting::updateOrCreate(['key' => 'university_name'], ['value' => $request->university_name]);
            }
            if ($request->has('morning_late_cutoff')) {
                Setting::updateOrCreate(['key' => 'morning_late_cutoff'], ['value' => $request->morning_late_cutoff]);
            }
            if ($request->has('afternoon_late_cutoff')) {
                Setting::updateOrCreate(['key' => 'afternoon_late_cutoff'], ['value' => $request->afternoon_late_cutoff]);
            }
            
            if ($request->has('authorized_ip')) {
                Setting::updateOrCreate(['key' => 'authorized_ip'], ['value' => $request->authorized_ip ?? '']);
            }
            
            if ($request->has('maintenance_mode')) {
                Setting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => $request->maintenance_mode]);
            }
            
            if ($request->has('working_days')) {
                Setting::updateOrCreate(['key' => 'working_days'], ['value' => json_encode($request->working_days ?? [])]);
            }

            if ($request->has('system_open_time')) {
                Setting::updateOrCreate(['key' => 'system_open_time'], ['value' => $request->system_open_time]);
            }
            if ($request->has('system_close_time')) {
                Setting::updateOrCreate(['key' => 'system_close_time'], ['value' => $request->system_close_time]);
            }
            if ($request->has('morning_shift_start')) {
                Setting::updateOrCreate(['key' => 'morning_shift_start'], ['value' => $request->morning_shift_start]);
            }
            if ($request->has('morning_shift_end')) {
                Setting::updateOrCreate(['key' => 'morning_shift_end'], ['value' => $request->morning_shift_end]);
            }
            if ($request->has('afternoon_shift_start')) {
                Setting::updateOrCreate(['key' => 'afternoon_shift_start'], ['value' => $request->afternoon_shift_start]);
            }
            if ($request->has('afternoon_shift_end')) {
                Setting::updateOrCreate(['key' => 'afternoon_shift_end'], ['value' => $request->afternoon_shift_end]);
            }
            if ($request->has('scan_alert_duration')) {
                Setting::updateOrCreate(['key' => 'scan_alert_duration'], ['value' => $request->scan_alert_duration]);
            }

            if ($request->has('university_website')) {
                Setting::updateOrCreate(['key' => 'university_website'], ['value' => $request->university_website ?? '']);
            }

            if ($request->has('university_facebook')) {
                Setting::updateOrCreate(['key' => 'university_facebook'], ['value' => $request->university_facebook ?? '']);
            }

            if ($request->has('enable_auto_checkout')) {
                Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => $request->enable_auto_checkout]);
            }

            if ($request->has('auto_checkout_delay')) {
                Setting::updateOrCreate(['key' => 'auto_checkout_delay'], ['value' => $request->auto_checkout_delay]);
            }

            if ($request->has('telegram_bot_token')) {
                $token = $request->telegram_bot_token;
                Setting::updateOrCreate(['key' => 'telegram_bot_token'], ['value' => $token]);
                if (!empty($token)) {
                    try {
                        $response = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getMe");
                        if ($response->successful() && $response->json('ok')) {
                            $username = $response->json('result.username');
                            if ($username) {
                                Setting::updateOrCreate(['key' => 'telegram_bot_username'], ['value' => $username]);
                            }
                        }
                    } catch (\Exception $e) {
                        // Suppress network errors
                    }
                } else {
                    Setting::updateOrCreate(['key' => 'telegram_bot_username'], ['value' => '']);
                }
            }


            if ($request->hasFile('university_logo')) {
                $file = $request->file('university_logo');
                if ($file->isValid()) {
                    $mime = $file->getMimeType();
                    $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
                    Setting::updateOrCreate(
                        ['key' => 'university_logo'],
                        ['value' => $base64]
                    );
                }
            }

            if ($request->hasFile('login_bg')) {
                $file = $request->file('login_bg');
                if ($file->isValid()) {
                    $mime = $file->getMimeType();
                    $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
                    Setting::updateOrCreate(
                        ['key' => 'login_bg'],
                        ['value' => $base64]
                    );
                }
            }

            Setting::updateOrCreate(['key' => 'settings_updated_at'], ['value' => time()]);

            SecurityLog::record('Updated System Settings', 'Configuration');

            return back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Logo upload error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while saving settings: ' . $e->getMessage());
        }
    }

    public function updateAppearance(Request $request)
    {
        $request->validate([
            'primary_color' => 'required|string|max:7',
            'default_theme' => 'required|in:dark,light',
            'font_size' => 'required|integer|min:11|max:20',
            'global_icon_size' => 'required|numeric|min:0.8|max:2.0',
            'font_family' => 'nullable|string|max:100',
            'border_radius' => 'nullable|string|max:20',
            'enable_glassmorphism' => 'nullable|in:on,off',
            'live_radar_size' => 'nullable|integer|min:200|max:800',
        ]);

        Setting::updateOrCreate(['key' => 'primary_color'], ['value' => $request->primary_color]);
        Setting::updateOrCreate(['key' => 'default_theme'], ['value' => $request->default_theme]);
        Setting::updateOrCreate(['key' => 'font_size'], ['value' => $request->font_size]);
        Setting::updateOrCreate(['key' => 'global_icon_size'], ['value' => $request->global_icon_size]);
        
        if ($request->has('font_family')) {
            Setting::updateOrCreate(['key' => 'font_family'], ['value' => $request->font_family]);
        }
        if ($request->has('border_radius')) {
            Setting::updateOrCreate(['key' => 'border_radius'], ['value' => $request->border_radius]);
        }
        if ($request->has('live_radar_size')) {
            Setting::updateOrCreate(['key' => 'live_radar_size'], ['value' => $request->live_radar_size]);
        }
        // Checkbox: if not submitted it means OFF
        $glassValue = $request->has('enable_glassmorphism') ? 'on' : 'off';
        Setting::updateOrCreate(['key' => 'enable_glassmorphism'], ['value' => $glassValue]);

        Setting::updateOrCreate(['key' => 'settings_updated_at'], ['value' => time()]);

        SecurityLog::record('Updated System Appearance', 'Theme');

        // Also clear the user's localStorage theme so server default takes effect
        return back()->with('success', 'Appearance settings saved. Refresh any open pages to apply the new theme.');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        SecurityLog::record('Created Admin User', $user->name);

        return back()->with('success', 'Admin created successfully.');
    }

    public function resetAdminPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Admin password reset successfully.');
    }

    public function downloadBackup()
    {
        $filename = "ntti_attendance_backup_" . now()->format('Y-m-d_H-i-s') . ".xls";

        return response()->streamDownload(function () {
            $uName = \App\Models\Setting::getValue('university_name', 'NTTI System');
            $totalRecords = \App\Models\Attendance::count();
            $dateRange = \App\Models\Attendance::selectRaw('MIN(date) as min_date, MAX(date) as max_date')->first();
            $minDate = $dateRange->min_date ? \Carbon\Carbon::parse($dateRange->min_date)->format('d-m-Y') : 'N/A';
            $maxDate = $dateRange->max_date ? \Carbon\Carbon::parse($dateRange->max_date)->format('d-m-Y') : 'N/A';
            $colCount = 15;

            // HTML-based XLS with borders and styling
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Attendance Backup</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
            echo '<body>';
            $fontFamily = \App\Models\Setting::getValue('font_family', 'Inter');
            echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse; font-family:\'' . $fontFamily . '\', \'Khmer OS Battambang\', \'Khmer OS Siemreap\', Calibri, Arial, sans-serif; font-size:11pt;">';

            // School name row
            echo '<tr><td colspan="' . $colCount . '" style="border:none; padding:12px 10px 4px; font-size:16pt; font-weight:bold; text-align:center; color:#1a1a2e;">' . htmlspecialchars($uName) . '</td></tr>';

            // Document title row
            echo '<tr><td colspan="' . $colCount . '" style="border:none; padding:2px 10px 4px; font-size:12pt; text-align:center; color:#555;">Attendance Database Backup</td></tr>';

            // Info row
            echo '<tr><td colspan="' . $colCount . '" style="border:none; padding:2px 10px 2px; font-size:9pt; text-align:center; color:#888;">'
                . 'Export Date: ' . now()->format('d-m-Y H:i:s')
                . '  |  Total Records: ' . number_format($totalRecords)
                . '  |  Date Range: ' . $minDate . ' to ' . $maxDate
                . '</td></tr>';

            // Spacer row
            echo '<tr><td colspan="' . $colCount . '" style="border:none; padding:0; height:8px;"></td></tr>';

            // Header row
            $headers = [
                'No', 'Employee ID', 'Teacher Name', 'Teacher Name (KH)', 'Department',
                'Date', 'Day', 'Month', 'Year',
                'Morning In', 'Morning Out', 'Morning Status',
                'Afternoon In', 'Afternoon Out', 'Afternoon Status',
            ];
            echo '<tr>';
            foreach ($headers as $h) {
                echo '<th style="background-color:#1a1a2e; color:#ffffff; font-weight:bold; border:1px solid #333; padding:6px 10px; text-align:center; white-space:nowrap;">' . $h . '</th>';
            }
            echo '</tr>';

            // Data rows
            $rowNum = 0;
            \App\Models\Attendance::with('teacher')->orderBy('date', 'desc')->chunk(200, function ($records) use (&$rowNum) {
                foreach ($records as $r) {
                    $rowNum++;
                    $dateObj = $r->date instanceof \Carbon\Carbon ? $r->date : \Carbon\Carbon::parse($r->date);
                    $bgColor = $rowNum % 2 === 0 ? '#f8f9fa' : '#ffffff';

                    $morningStatus = ucfirst($r->morning_status ?? 'absent');
                    $afternoonStatus = ucfirst($r->afternoon_status ?? 'absent');

                    // Status color coding
                    $mStatusStyle = $this->getStatusStyle($morningStatus);
                    $aStatusStyle = $this->getStatusStyle($afternoonStatus);

                    $cellStyle = "border:1px solid #dee2e6; padding:5px 8px; background-color:{$bgColor};";

                    echo '<tr>';
                    echo "<td style=\"{$cellStyle} text-align:center;\">{$rowNum}</td>";
                    echo "<td style=\"{$cellStyle}\">" . ($r->teacher->employee_id ?? 'N/A') . "</td>";
                    echo "<td style=\"{$cellStyle}\">" . htmlspecialchars($r->teacher->name ?? 'N/A') . "</td>";
                    echo "<td style=\"{$cellStyle}\">" . htmlspecialchars($r->teacher->name_kh ?? '') . "</td>";
                    echo "<td style=\"{$cellStyle}\">" . htmlspecialchars($r->teacher->department ?? 'N/A') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center; white-space:nowrap;\">" . $dateObj->format('d-m-Y') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center;\">" . $dateObj->format('d') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center;\">" . $dateObj->format('m') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center;\">" . $dateObj->format('Y') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center;\">" . ($r->morning_in ? \Carbon\Carbon::parse($r->morning_in)->format('H:i') : '') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center;\">" . ($r->morning_out ? \Carbon\Carbon::parse($r->morning_out)->format('H:i') : '') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center; {$mStatusStyle}\">{$morningStatus}</td>";
                    echo "<td style=\"{$cellStyle} text-align:center;\">" . ($r->afternoon_in ? \Carbon\Carbon::parse($r->afternoon_in)->format('H:i') : '') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center;\">" . ($r->afternoon_out ? \Carbon\Carbon::parse($r->afternoon_out)->format('H:i') : '') . "</td>";
                    echo "<td style=\"{$cellStyle} text-align:center; {$aStatusStyle}\">{$afternoonStatus}</td>";
                    echo '</tr>';
                }
            });

            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getStatusStyle(string $status): string
    {
        return match (strtolower($status)) {
            'present' => 'background-color:#d4edda; color:#155724; font-weight:bold;',
            'late'    => 'background-color:#fff3cd; color:#856404; font-weight:bold;',
            'absent'  => 'background-color:#f8d7da; color:#721c24; font-weight:bold;',
            default   => '',
        };
    }

    public function handleCorrection(Request $request, AttendanceCorrection $correction)
    {
        $request->validate([
            'action' => 'required|in:approve,reject'
        ]);

        if ($request->action === 'approve') {
            $correction->status = 'approved';
            
            // Create or update attendance record manually
            $att = Attendance::firstOrCreate(
                ['teacher_id' => $correction->teacher_id, 'date' => $correction->date],
                ['rfid_uid' => 'MANUAL']
            );
            
            // Just marking them present for the requested shift as a simple correction
            if ($correction->shift === 'morning' || $correction->shift === 'both') {
                $att->morning_in = '08:00:00';
                $att->morning_out = '12:00:00';
                $att->morning_status = 'present';
            }
            if ($correction->shift === 'afternoon' || $correction->shift === 'both') {
                $att->afternoon_in = '13:00:00';
                $att->afternoon_out = '17:00:00';
                $att->afternoon_status = 'present';
            }
            $att->save();
            SecurityLog::record('Approved Attendance Correction', "Teacher ID: {$correction->teacher_id}, Date: {$correction->date}");
        } else {
            $correction->status = 'rejected';
            SecurityLog::record('Rejected Attendance Correction', "Teacher ID: {$correction->teacher_id}, Date: {$correction->date}");
        }
        
        $correction->save();
        return back()->with('success', 'Correction request ' . $request->action . 'd successfully.');
    }

    public function fetchTelegramChats()
    {
        try {
            // Since Webhooks are enabled, Telegram's getUpdates API is blocked (Error 409 Conflict).
            // We must now fetch recent chats from our local telegram_messages database table.
            $recentMessages = \App\Models\TelegramMessage::orderBy('created_at', 'desc')
                ->get()
                ->unique('chat_id');

            $chats = [];
            foreach ($recentMessages as $msg) {
                $chats[] = [
                    'id' => $msg->chat_id,
                    'name' => $msg->teacher ? $msg->teacher->name : 'Unknown User',
                    'username' => $msg->username ? '@' . str_replace('@', '', $msg->username) : 'No username',
                    'last_message' => $msg->message ?? '(Media/Other)',
                    'timestamp' => $msg->created_at->timestamp,
                    'date' => $msg->created_at->diffForHumans(),
                ];
            }

            return response()->json(['status' => 'success', 'chats' => array_values($chats)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function runSystemCleanup()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('system:cleanup');
            return back()->with('success', 'System data cleanup completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }

    public function downloadDatabaseSqlite()
    {
        $dbPath = database_path('database.sqlite');
        if (!file_exists($dbPath)) {
            return back()->with('error', 'Database file not found.');
        }

        $filename = 'ntti_attendance_db_' . now()->format('Y-m-d_H-i-s') . '.sqlite';
        SecurityLog::record('Downloaded Database Backup', 'Database');

        return response()->download($dbPath, $filename);
    }

    public function importDatabaseSqlite(Request $request)
    {
        $request->validate([
            'db_file' => 'required|file'
        ]);

        $dbPath = database_path('database.sqlite');
        $bakPath = database_path('database.sqlite.bak');

        try {
            $file = $request->file('db_file');
            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, ['sqlite', 'db', 'sqlite3', 'sql'])) {
                return back()->with('error', 'Invalid file format. Please upload a .sqlite, .db, or .sql database file.');
            }

            $realPath = $file->getRealPath();
            $content = file_get_contents($realPath);

            // 1. Always back up current database first
            if (file_exists($dbPath)) {
                @copy($dbPath, $bakPath);
            }

            // 2. Check if binary SQLite file vs SQL script
            if (str_starts_with($content, 'SQLite format 3')) {
                // Direct binary copy
                copy($realPath, $dbPath);
                @chmod($dbPath, 0777);
            } else {
                // It's a text SQL dump (from phpMyAdmin / MySQL or SQLite dump)
                $sql = $content;
                // Sanitize MySQL-specific syntax to prevent SQLite crashes
                $sql = preg_replace('/ENGINE\s*=\s*\w+/i', '', $sql);
                $sql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql);
                $sql = preg_replace('/COLLATE\s*=\s*\w+/i', '', $sql);
                $sql = preg_replace('/AUTO_INCREMENT\s*=\s*\d+/i', '', $sql);
                $sql = preg_replace('/LOCK\s+TABLES.*?;/is', '', $sql);
                $sql = preg_replace('/UNLOCK\s+TABLES;/i', '', $sql);
                $sql = preg_replace('/SET\s+[\w_]+\s*=\s*.*?;/i', '', $sql);

                // Run SQL queries inside PDO connection
                \DB::connection()->getPdo()->exec($sql);
            }

            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            SecurityLog::record('Imported Database', 'Database');

            return back()->with('success', 'Database imported successfully! System records and settings have been restored.');
        } catch (\Exception $e) {
            // Restore previous database backup on error so system remains functional
            if (file_exists($bakPath)) {
                @copy($bakPath, $dbPath);
            }
            return back()->with('error', 'Failed to import database: ' . $e->getMessage() . '. Restored previous database backup.');
        }
    }
}
