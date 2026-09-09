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

        $isTrue = function($key, $default = 'true') {
            $val = Setting::getValue($key, $default);
            return in_array(strtolower((string)$val), ['1', 'true', 'on', 'yes'], true);
        };

        // Smart Kiosk Settings
        $kioskStationName      = Setting::getValue('kiosk_station_name', 'SMART ATTENDANCE KIOSK STATION · ស្ថានីយស្កេនវៃឆ្លាត');
        $kioskDefaultTab        = Setting::getValue('kiosk_default_tab', 'camera');
        $kioskQrRotation        = Setting::getValue('kiosk_qr_rotation', '20');
        $kioskVoiceEnabled      = $isTrue('kiosk_voice_enabled', 'true');
        $kioskVoiceSpeed        = Setting::getValue('kiosk_voice_speed', '1.0');
        $kioskConfetti          = $isTrue('kiosk_confetti', 'true');
        $kioskShowAnnouncements = $isTrue('kiosk_show_announcements', 'true');

        // GPS Geofencing Settings
        $campusLatitude         = Setting::getValue('campus_latitude', '11.5564');
        $campusLongitude        = Setting::getValue('campus_longitude', '104.8885');
        $campusGpsRadius        = Setting::getValue('campus_gps_radius', '300');
        $enforceGpsGeofence     = $isTrue('enforce_gps_geofence', 'false');

        // Telegram Ecosystem & Triggers
        $telegramChatId         = Setting::getValue('telegram_chat_id', '');
        $telegramChannelId      = Setting::getValue('telegram_channel_id', '');
        $telegramNotifyCheckin  = $isTrue('telegram_notify_checkin', 'true');
        $telegramNotifyCheckout = $isTrue('telegram_notify_checkout', 'true');
        $telegramNotifyLate     = $isTrue('telegram_notify_late', 'true');
        $telegramNotifyLeave    = $isTrue('telegram_notify_leave', 'true');

        // Academic Term & Shift Grace Period
        $academicYear           = Setting::getValue('academic_year', '2025-2026');
        $academicSemester       = Setting::getValue('academic_semester', 'Semester 1');
        $lateGracePeriod        = Setting::getValue('late_grace_period', '5');
        $earlyCheckinWindow     = Setting::getValue('early_checkin_window', '30');

        return view('settings.index', compact(
            'universityName', 'universityLogo', 'primaryColor', 'defaultTheme', 
            'admins', 'morningLate', 'afternoonLate', 'workingDays', 'maintenanceMode', 'authorizedIp',
            'systemOpen', 'systemClose', 'loginBg',
            'morningStart', 'morningEnd', 'afternoonStart', 'afternoonEnd', 'scanAlertDuration',
            'fontSize', 'iconSize', 'fontFamily', 'borderRadius', 'enableGlassmorphism',
            'glassBlur', 'glassOpacity', 'glassBorder', 'glassNoise',
            'universityWebsite', 'universityFacebook', 'corrections',
            'enableAutoCheckout', 'autoCheckoutDelay', 'telegramBotToken', 'holidays',
            'liveRadarSize',
            'kioskStationName', 'kioskDefaultTab', 'kioskQrRotation', 'kioskVoiceEnabled', 'kioskVoiceSpeed', 'kioskConfetti', 'kioskShowAnnouncements',
            'campusLatitude', 'campusLongitude', 'campusGpsRadius', 'enforceGpsGeofence',
            'telegramChatId', 'telegramChannelId', 'telegramNotifyCheckin', 'telegramNotifyCheckout', 'telegramNotifyLate', 'telegramNotifyLeave',
            'academicYear', 'academicSemester', 'lateGracePeriod', 'earlyCheckinWindow'
        ));
    }

    public function update(Request $request)
    {
        $section = $request->input('settings_section');

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
            'university_website' => 'nullable|string|max:255',
            'university_facebook' => 'nullable|string|max:255',
            'enable_auto_checkout' => 'nullable|string',
            'auto_checkout_delay' => 'nullable|integer|min:0|max:1440',
            'telegram_bot_token' => 'nullable|string',
            'campus_latitude' => 'nullable|numeric',
            'campus_longitude' => 'nullable|numeric',
            'campus_gps_radius' => 'nullable|numeric|min:10',
            'kiosk_station_name' => 'nullable|string|max:255',
            'kiosk_default_tab' => 'nullable|string|in:camera,rfid,screen_qr',
            'kiosk_qr_rotation' => 'nullable|integer|min:5|max:120',
            'kiosk_voice_speed' => 'nullable|string',
            'academic_year' => 'nullable|string|max:50',
            'academic_semester' => 'nullable|string|max:50',
            'late_grace_period' => 'nullable|integer|min:0|max:60',
            'early_checkin_window' => 'nullable|integer|min:0|max:180',
        ]);

        try {
            // ── SECTION: System Identity ──
            if (!$section || $section === 'identity') {
                if ($request->has('university_name')) {
                    Setting::updateOrCreate(['key' => 'university_name'], ['value' => $request->university_name]);
                }
                if ($request->has('university_website')) {
                    Setting::updateOrCreate(['key' => 'university_website'], ['value' => $request->university_website ?? '']);
                }
                if ($request->has('university_facebook')) {
                    Setting::updateOrCreate(['key' => 'university_facebook'], ['value' => $request->university_facebook ?? '']);
                }
                if ($request->hasFile('university_logo')) {
                    $file = $request->file('university_logo');
                    if ($file->isValid()) {
                        $mime = $file->getMimeType();
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
                        Setting::updateOrCreate(['key' => 'university_logo'], ['value' => $base64]);
                    }
                }
                if ($request->hasFile('login_bg')) {
                    $file = $request->file('login_bg');
                    if ($file->isValid()) {
                        $mime = $file->getMimeType();
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
                        Setting::updateOrCreate(['key' => 'login_bg'], ['value' => $base64]);
                    }
                }
            }

            // ── SECTION: Attendance Rules & Terms ──
            if (!$section || $section === 'rules') {
                if ($request->has('morning_late_cutoff')) {
                    Setting::updateOrCreate(['key' => 'morning_late_cutoff'], ['value' => $request->morning_late_cutoff]);
                }
                if ($request->has('afternoon_late_cutoff')) {
                    Setting::updateOrCreate(['key' => 'afternoon_late_cutoff'], ['value' => $request->afternoon_late_cutoff]);
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
                if ($request->has('academic_year')) {
                    Setting::updateOrCreate(['key' => 'academic_year'], ['value' => $request->academic_year]);
                }
                if ($request->has('academic_semester')) {
                    Setting::updateOrCreate(['key' => 'academic_semester'], ['value' => $request->academic_semester]);
                }
                if ($request->has('late_grace_period')) {
                    Setting::updateOrCreate(['key' => 'late_grace_period'], ['value' => $request->late_grace_period]);
                }
                if ($request->has('early_checkin_window')) {
                    Setting::updateOrCreate(['key' => 'early_checkin_window'], ['value' => $request->early_checkin_window]);
                }
                if ($section === 'rules' || $request->has('enable_auto_checkout')) {
                    $autoCheck = $request->input('enable_auto_checkout');
                    Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => ($autoCheck === 'on' || $autoCheck === 'true' || $autoCheck === '1') ? 'on' : 'off']);
                }
                if ($request->has('auto_checkout_delay')) {
                    Setting::updateOrCreate(['key' => 'auto_checkout_delay'], ['value' => $request->auto_checkout_delay]);
                }
                if ($request->has('working_days')) {
                    Setting::updateOrCreate(['key' => 'working_days'], ['value' => json_encode($request->working_days ?? [])]);
                }
            }

            // ── SECTION: Smart Kiosk Terminal ──
            if (!$section || $section === 'kiosk') {
                if ($request->has('kiosk_station_name')) {
                    Setting::updateOrCreate(['key' => 'kiosk_station_name'], ['value' => $request->kiosk_station_name]);
                }
                if ($request->has('kiosk_default_tab')) {
                    Setting::updateOrCreate(['key' => 'kiosk_default_tab'], ['value' => $request->kiosk_default_tab]);
                }
                if ($request->has('kiosk_qr_rotation')) {
                    Setting::updateOrCreate(['key' => 'kiosk_qr_rotation'], ['value' => $request->kiosk_qr_rotation]);
                }
                if ($request->has('kiosk_voice_speed')) {
                    Setting::updateOrCreate(['key' => 'kiosk_voice_speed'], ['value' => $request->kiosk_voice_speed]);
                }
                if ($section === 'kiosk' || $request->has('kiosk_voice_enabled')) {
                    Setting::updateOrCreate(['key' => 'kiosk_voice_enabled'], ['value' => $request->has('kiosk_voice_enabled') ? 'true' : 'false']);
                }
                if ($section === 'kiosk' || $request->has('kiosk_confetti')) {
                    Setting::updateOrCreate(['key' => 'kiosk_confetti'], ['value' => $request->has('kiosk_confetti') ? 'true' : 'false']);
                }
                if ($section === 'kiosk' || $request->has('kiosk_show_announcements')) {
                    Setting::updateOrCreate(['key' => 'kiosk_show_announcements'], ['value' => $request->has('kiosk_show_announcements') ? 'true' : 'false']);
                }
            }

            // ── SECTION: Campus GPS Geofence ──
            if (!$section || $section === 'geofence') {
                if ($request->has('campus_latitude')) {
                    Setting::updateOrCreate(['key' => 'campus_latitude'], ['value' => $request->campus_latitude]);
                }
                if ($request->has('campus_longitude')) {
                    Setting::updateOrCreate(['key' => 'campus_longitude'], ['value' => $request->campus_longitude ?? '104.8885']);
                }
                if ($request->has('campus_gps_radius')) {
                    Setting::updateOrCreate(['key' => 'campus_gps_radius'], ['value' => $request->campus_gps_radius ?? '300']);
                }
                if ($section === 'geofence' || $request->has('enforce_gps_geofence')) {
                    Setting::updateOrCreate(['key' => 'enforce_gps_geofence'], ['value' => $request->has('enforce_gps_geofence') ? 'true' : 'false']);
                }
            }

            // ── SECTION: Telegram Ecosystem & Alerts ──
            if (!$section || $section === 'telegram') {
                if ($request->has('telegram_chat_id')) {
                    Setting::updateOrCreate(['key' => 'telegram_chat_id'], ['value' => $request->telegram_chat_id ?? '']);
                }
                if ($request->has('telegram_channel_id')) {
                    Setting::updateOrCreate(['key' => 'telegram_channel_id'], ['value' => $request->telegram_channel_id ?? '']);
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
                if ($section === 'telegram' || $request->has('telegram_notify_checkin')) {
                    Setting::updateOrCreate(['key' => 'telegram_notify_checkin'], ['value' => $request->has('telegram_notify_checkin') ? 'true' : 'false']);
                }
                if ($section === 'telegram' || $request->has('telegram_notify_checkout')) {
                    Setting::updateOrCreate(['key' => 'telegram_notify_checkout'], ['value' => $request->has('telegram_notify_checkout') ? 'true' : 'false']);
                }
                if ($section === 'telegram' || $request->has('telegram_notify_late')) {
                    Setting::updateOrCreate(['key' => 'telegram_notify_late'], ['value' => $request->has('telegram_notify_late') ? 'true' : 'false']);
                }
                if ($section === 'telegram' || $request->has('telegram_notify_leave')) {
                    Setting::updateOrCreate(['key' => 'telegram_notify_leave'], ['value' => $request->has('telegram_notify_leave') ? 'true' : 'false']);
                }
            }

            // ── SECTION: Security & Hardware ──
            if (!$section || $section === 'security') {
                if ($request->has('authorized_ip')) {
                    Setting::updateOrCreate(['key' => 'authorized_ip'], ['value' => $request->authorized_ip ?? '']);
                }
                if ($request->has('maintenance_mode')) {
                    Setting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => $request->maintenance_mode]);
                }
                if ($request->has('system_open_time')) {
                    Setting::updateOrCreate(['key' => 'system_open_time'], ['value' => $request->system_open_time]);
                }
                if ($request->has('system_close_time')) {
                    Setting::updateOrCreate(['key' => 'system_close_time'], ['value' => $request->system_close_time]);
                }
                if ($request->has('scan_alert_duration')) {
                    Setting::updateOrCreate(['key' => 'scan_alert_duration'], ['value' => $request->scan_alert_duration]);
                }
            }

            Setting::updateOrCreate(['key' => 'settings_updated_at'], ['value' => time()]);

            SecurityLog::record('Updated System Settings' . ($section ? ' (' . ucfirst($section) . ')' : ''), 'Configuration');

            $targetTab = $section ? 'section-' . $section : ($request->input('active_tab') ?: 'section-identity');
            return redirect()->route('settings.index', ['tab' => $targetTab])->with('success', __('Settings updated successfully.'));
        } catch (\Exception $e) {
            \Log::error('Settings update error: ' . $e->getMessage());
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
        return redirect()->route('settings.index', ['tab' => 'section-appearance'])->with('success', __('Appearance settings saved successfully.'));
    }

    public function updateAdmin(Request $request, User $user)
    {
        if (auth()->id() !== 1 && auth()->id() !== $user->id) {
            return back()->with('error', 'You do not have permission to edit this admin account.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telegram_chat_id' => 'nullable|string|max:255',
            'two_factor_enabled' => 'nullable|string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->telegram_chat_id = $request->telegram_chat_id;
        
        $twoFactor = $request->has('two_factor_enabled') && ($request->two_factor_enabled === 'true' || $request->two_factor_enabled === '1' || $request->two_factor_enabled === 'on');
        $user->two_factor_enabled = $twoFactor;
        if (!$twoFactor) {
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
        }

        $user->save();

        SecurityLog::record('Updated Admin Account', $user->name . ' (' . $user->email . ')');

        return redirect()->route('settings.index', ['tab' => 'section-admins'])->with('success', __('Admin account updated successfully.'));
    }

    public function storeAdmin(Request $request)
    {
        if (auth()->id() !== 1) {
            return back()->with('error', 'Only ROOT ADMIN can perform this action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'telegram_chat_id' => 'nullable|string|max:255',
            'two_factor_enabled' => 'nullable|string',
        ]);

        $twoFactor = $request->has('two_factor_enabled') && ($request->two_factor_enabled === 'true' || $request->two_factor_enabled === '1' || $request->two_factor_enabled === 'on');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telegram_chat_id' => $request->telegram_chat_id,
            'two_factor_enabled' => $twoFactor,
        ]);

        SecurityLog::record('Created Admin User', $user->name);

        return redirect()->route('settings.index', ['tab' => 'section-admins'])->with('success', __('Admin created successfully.'));
    }

    public function resetAdminPassword(Request $request, User $user)
    {
        if (auth()->id() !== 1) {
            return back()->with('error', 'Only ROOT ADMIN can perform this action.');
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings.index', ['tab' => 'section-admins'])->with('success', __('Admin password reset successfully.'));
    }

    public function destroyAdmin(User $user)
    {
        if (auth()->id() !== 1) {
            return back()->with('error', 'Only ROOT ADMIN can perform this action.');
        }

        if ($user->id === 1) {
            return back()->with('error', 'Cannot delete the ROOT ADMIN account.');
        }

        SecurityLog::record('Deleted Admin User', $user->name);
        $user->delete();

        return redirect()->route('settings.index', ['tab' => 'section-admins'])->with('success', __('Administrator deleted successfully.'));
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
        return redirect()->route('settings.index', ['tab' => 'section-corrections'])->with('success', __('Correction request ' . $request->action . 'd successfully.'));
    }

    public function fetchTelegramChats()
    {
        try {
            $token = \App\Models\Setting::getValue('telegram_bot_token');
            if ($token) {
                // Check if webhook is returning 503 or not set, fallback to polling
                $webhookInfo = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getWebhookInfo")->json();
                $hasWebhookError = isset($webhookInfo['result']['last_error_date']) || empty($webhookInfo['result']['url']);
                
                if ($hasWebhookError) {
                    // Temporarily delete webhook to allow getUpdates
                    if (!empty($webhookInfo['result']['url'])) {
                        \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/deleteWebhook");
                    }
                    
                    $updates = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getUpdates")->json();
                    
                    if (!empty($updates['result'])) {
                        $highestUpdateId = 0;
                        foreach ($updates['result'] as $update) {
                            $highestUpdateId = max($highestUpdateId, $update['update_id']);
                            // Simulate webhook payload
                            $request = new \Illuminate\Http\Request();
                            $request->replace($update);
                            app(\App\Http\Controllers\TelegramWebhookController::class)->handle($request);
                        }
                        // Acknowledge updates
                        if ($highestUpdateId > 0) {
                            \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getUpdates?offset=" . ($highestUpdateId + 1));
                        }
                    }
                }
            }

            // We fetch recent chats from our local telegram_messages database table.
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
        $driver = \DB::connection()->getDriverName();
        $dateStr = now()->format('Y-m-d_H-i-s');

        if ($driver === 'mysql') {
            $tables = ['users', 'teachers', 'attendance', 'departments', 'rfid_cards', 'schedules', 'leave_requests', 'security_logs', 'attendance_corrections', 'holidays', 'settings', 'migrations'];
            $sqlDump = "-- NTTI Attendance Database Backup\n";
            $sqlDump .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $sqlDump .= "-- Database Driver: MySQL\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            $pdo = \DB::connection()->getPdo();

            foreach ($tables as $table) {
                if (!\Schema::hasTable($table)) continue;

                $sqlDump .= "-- --------------------------------------------------------\n";
                $sqlDump .= "-- Table structure for `{$table}`\n";
                $sqlDump .= "-- --------------------------------------------------------\n\n";

                try {
                    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                    if (isset($createStmt['Create Table'])) {
                        $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                        $sqlDump .= $createStmt['Create Table'] . ";\n\n";
                    }
                } catch (\Throwable $e) {}

                $rows = \DB::table($table)->get();
                if ($rows->count() > 0) {
                    $sqlDump .= "-- Dumping data for table `{$table}`\n\n";
                    foreach ($rows as $row) {
                        $rowArray = (array)$row;
                        $columns = array_keys($rowArray);
                        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                        
                        $values = array_map(function($val) use ($pdo) {
                            if (is_null($val)) return 'NULL';
                            return $pdo->quote($val);
                        }, array_values($rowArray));

                        $sqlDump .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sqlDump .= "\n";
                }
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

            try { SecurityLog::record('Downloaded Database Backup (.sql)', 'Database'); } catch (\Throwable $sEx) {}
            $filename = 'ntti_attendance_backup_' . $dateStr . '.sql';

            return response($sqlDump, 200, [
                'Content-Type' => 'text/x-sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // SQLite connection driver fallback
        $dbPath = config('database.connections.sqlite.database', database_path('database.sqlite'));
        if (!file_exists($dbPath)) {
            $dbPath = database_path('database.sqlite');
        }

        if (!file_exists($dbPath)) {
            return back()->with('error', 'Database file not found at: ' . $dbPath);
        }

        $filename = 'ntti_attendance_db_' . $dateStr . '.sqlite';
        try { SecurityLog::record('Downloaded Database Backup (.sqlite)', 'Database'); } catch (\Throwable $sEx) {}

        return response()->download($dbPath, $filename);
    }

    /**
     * Send a test message via Telegram to verify bot configuration.
     */
    public function sendTelegramTestMessage(Request $request)
    {
        $targetChat = $request->input('chat_id') ?: Setting::getValue('telegram_channel_id') ?: Setting::getValue('telegram_chat_id');
        if (empty($targetChat)) {
            return response()->json(['status' => 'error', 'message' => 'No Telegram Chat ID or Channel ID provided.'], 400);
        }

        $botToken = Setting::getValue('telegram_bot_token');
        if (empty($botToken)) {
            return response()->json(['status' => 'error', 'message' => 'Telegram Bot Token is not configured.'], 400);
        }

        $text = "🔔 *NTTI Attendance — System Test Message*\n\n"
              . "✅ Telegram Bot integration is connected and working successfully!\n"
              . "⏰ Server Time: `" . now()->format('Y-m-d h:i:s A') . "`\n"
              . "🏫 Institution: *" . Setting::getValue('university_name', 'NTTI') . "*\n"
              . "🚀 Host: `66.42.61.106`";

        $sent = \App\Services\TelegramService::sendMessage($targetChat, $text);

        if ($sent) {
            return response()->json(['status' => 'success', 'message' => "Test message sent successfully to Chat ID: {$targetChat}!"]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Failed to send message. Please verify your Bot Token and Chat ID.'], 500);
        }
    }

    /**
     * Send or test dispatch of Daily Telegram Executive Briefing on demand.
     */
    public function sendTelegramExecutiveBriefing(Request $request)
    {
        $shift = $request->input('shift', 'auto');
        $res = \App\Services\TelegramService::sendDailyExecutiveBriefing($shift);

        if ($res['success']) {
            return response()->json([
                'status'  => 'success',
                'message' => "Executive Briefing successfully dispatched to {$res['recipients']} recipient(s)!",
                'data'    => $res,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $res['message'] ?? 'Failed to send Executive Briefing. Check Telegram configuration.',
            'data'    => $res,
        ], 400);
    }


    /**
     * One-click clear application and view caches.
     */
    public function clearSystemCache()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            SecurityLog::record('System Cache Cleared', 'Settings Hub');

            return response()->json(['success' => true, 'message' => 'All system caches (views, application cache, routes) have been cleared successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Cache clearing error: ' . $e->getMessage()], 500);
        }
    }
}
