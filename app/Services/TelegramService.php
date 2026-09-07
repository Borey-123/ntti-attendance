<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramService
{
    /**
     * Send a raw markdown message to a specific Telegram Chat ID.
     */
    public static function sendMessage(int|string $chatId, string $text, array $replyMarkup = []): bool
    {
        $botToken = Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) {
            Log::error('TelegramService: Bot token not configured.');
            return false;
        }

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $res = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);
            Log::info('TelegramService sendMessage result:', ['chat_id' => $chatId, 'status' => $res->status(), 'response' => $res->json()]);
            return $res->successful() && ($res->json('ok') === true);
        } catch (\Throwable $e) {
            Log::error("TelegramService Exception sending to {$chatId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send 2FA OTP security verification code to an Admin via Telegram.
     */
    public static function sendAdminOtp(\App\Models\User $user, string $code): bool
    {
        // 1. If user has a specific telegram_chat_id set
        $targetChatId = $user->telegram_chat_id;

        // 2. Fallback to broadcast/system telegram_chat_id setting if user's chat ID isn't set yet
        if (empty($targetChatId)) {
            $targetChatId = Setting::getValue('telegram_chat_id');
        }

        if (empty($targetChatId)) {
            Log::warning('TelegramService: No admin telegram_chat_id available to send 2FA OTP.');
            return false;
        }

        $msg = "🔒 *NTTI Security Verification*\n\n"
             . "Hello *{$user->name}*,\n"
             . "Your 2FA Login OTP Code is: `{$code}`\n\n"
             . "⏰ This code expires in *10 minutes*.\n"
             . "If you did not request this login attempt, please change your password immediately.";

        return self::sendMessage($targetChatId, $msg);
    }

    /**
     * Broadcast a message to configured channel ID or all teachers with a telegram_chat_id.
     */
    public static function broadcastMessage(string $text, array $replyMarkup = []): int
    {
        $sentCount = 0;

        // Try setting channel ID first
        $channelId = Setting::getValue('telegram_chat_id');
        if ($channelId) {
            if (self::sendMessage($channelId, $text, $replyMarkup)) {
                $sentCount++;
            }
        }

        // Also broadcast to all teachers registered with Telegram
        $teachers = Teacher::whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '')->get();
        foreach ($teachers as $teacher) {
            if ($teacher->telegram_chat_id != $channelId) {
                if (self::sendMessage($teacher->telegram_chat_id, $text, $replyMarkup)) {
                    $sentCount++;
                }
            }
        }

        return $sentCount;
    }

    /**
     * Send message to a Teacher model if telegram_chat_id is present.
     */
    public static function sendToTeacher(Teacher $teacher, string $text, array $replyMarkup = []): bool
    {
        if (empty($teacher->telegram_chat_id)) {
            return false;
        }
        return self::sendMessage($teacher->telegram_chat_id, $text, $replyMarkup);
    }

    /**
     * Generate & send monthly summary to a teacher (used by monthly broadcast & /monthly command).
     */
    public static function sendMonthlyReport(Teacher $teacher, ?Carbon $dateObj = null): bool
    {
        if (empty($teacher->telegram_chat_id)) {
            return false;
        }

        $now        = $dateObj ? $dateObj->copy() : Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();
        $monthName  = $now->format('F Y');

        $records = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $abbrs = json_decode(Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]'), true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        $totalWorkingDays = 0;
        $c = Carbon::parse($monthStart);
        $targetEnd = $now->isCurrentMonth() ? Carbon::today() : Carbon::parse($monthEnd);
        while ($c->lte($targetEnd)) {
            if (in_array($c->format('D'), $abbrs)) {
                $totalWorkingDays++;
            }
            $c->addDay();
        }

        $workedDays = $records->filter(fn($r) => !empty($r->morning_in) || !empty($r->afternoon_in) || !empty($r->evening_in))->count();
        $lateDays   = $records->filter(fn($r) => $r->morning_status === 'late' || $r->afternoon_status === 'late' || $r->evening_status === 'late')->count();
        $onTimeDays = $records->filter(function ($r) {
            $p = in_array($r->morning_status, ['present']) || in_array($r->afternoon_status, ['present']) || in_array($r->evening_status, ['present']);
            $l = in_array($r->morning_status, ['late'])    || in_array($r->afternoon_status, ['late'])    || in_array($r->evening_status, ['late']);
            return $p && !$l;
        })->count();

        $leaveDays = (int) LeaveRequest::where('teacher_id', $teacher->id)
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereBetween('start_date', [$monthStart, $monthEnd])->orWhereBetween('end_date', [$monthStart, $monthEnd]))
            ->get()->sum(function ($l) use ($monthStart, $monthEnd) {
                $s = Carbon::parse(max($l->start_date, $monthStart));
                $e = Carbon::parse(min($l->end_date,   $monthEnd));
                return max(0, $s->diffInDays($e) + 1);
            });

        $absentDays = max(0, $totalWorkingDays - $workedDays - $leaveDays);
        $pct        = $totalWorkingDays > 0 ? round(($workedDays / $totalWorkingDays) * 100) : 0;
        $bar        = str_repeat('█', (int)($pct / 10)) . str_repeat('░', 10 - (int)($pct / 10));

        $msg = "📊 *Monthly Attendance Summary*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n"
             . "🗓️ *{$monthName}*\n\n"
             . "✅  Worked    →  *{$workedDays} / {$totalWorkingDays} days*\n"
             . "🕐  On-Time  →  *{$onTimeDays} days*\n"
             . "⚠️  Late       →  *{$lateDays} days*\n"
             . "📝  Leave     →  *{$leaveDays} days*\n"
             . "❌  Absent   →  *{$absentDays} days*\n\n"
             . "`{$bar}` *{$pct}%*";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🏠 Back to Menu', 'callback_data' => 'menu']],
            ],
        ];

        return self::sendMessage($teacher->telegram_chat_id, $msg, $keyboard);
    }

    /**
     * Edit an existing Telegram message in-place (e.g. after tapping inline buttons).
     */
    public static function editMessageText(int|string $chatId, int $messageId, string $text, array $replyMarkup = []): bool
    {
        $botToken = Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) return false;

        $payload = [
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $res = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/editMessageText", $payload);
            return $res->successful() && ($res->json('ok') === true);
        } catch (\Throwable $e) {
            Log::error("TelegramService editMessageText error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send rich bilingual attendance check-in/out slip to teacher & channel.
     */
    public static function sendAttendanceSlip(Teacher $teacher, string $action, string $shift, string $time, string $status = 'present', ?string $method = null): bool
    {
        $botToken = Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) return false;

        $targetChatId = $teacher->telegram_chat_id;
        $channelChatId = Setting::getValue('telegram_chat_id');

        if (empty($targetChatId) && empty($channelChatId)) return false;

        $nameKh = $teacher->name_kh ? trim($teacher->name_kh) : '';
        $nameEn = $teacher->name ? trim($teacher->name) : '';
        $displayName = $nameKh ? "{$nameKh} ({$nameEn})" : $nameEn;

        $actionKh = $action === 'check-in' ? 'ចូលបង្រៀន (Check-In)' : 'ចេញពីបង្រៀន (Check-Out)';
        $icon = $action === 'check-in' ? '✅' : '👋';

        $shiftKh = match(strtolower($shift)) {
            'morning' => 'វេនព្រឹក / Morning',
            'afternoon' => 'វេនរសៀល / Afternoon',
            default => $shift,
        };

        $statusBadge = ($status === 'late') ? '⚠️ យឺត (Late)' : '🟢 ទាន់ពេល (On Time)';

        $methodLabel = match(strtolower($method ?? '')) {
            'rfid' => '💳 កាត RFID (RFID Card)',
            'face' => '👤 ស្កេនផ្ទៃមុខ (Face Scan)',
            'dynamic_qr', 'screen_qr' => '📲 QR លើអេក្រង់ (Screen QR)',
            'qr' => '📷 កូដ QR (QR Camera)',
            default => '✍️ ដោយដៃ (Manual / Admin)',
        };

        $msg = "{$icon} *ការចុះវត្តមានជោគជ័យ / Attendance Slip*\n"
             . "━━━━━━━━━━━━━━━━━━━━\n"
             . "👤 *គ្រូបង្រៀន / Teacher:* {$displayName}\n"
             . "🆔 *ID:* `{$teacher->employee_id}`\n"
             . "🏢 *ដេប៉ាតឺម៉ង់ / Dept:* {$teacher->department}\n"
             . "📌 *សកម្មភាព / Action:* *{$actionKh}*\n"
             . "⏰ *ពេលវេលា / Time:* `{$time}` · {$shiftKh}\n"
             . "📊 *ស្ថានភាព / Status:* {$statusBadge}\n"
             . "📍 *វិធីសាស្ត្រ / Method:* {$methodLabel}\n"
             . "━━━━━━━━━━━━━━━━━━━━\n"
             . "🏛️ *វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស (NTTI)*";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📋 ពិនិត្យវត្តមានថ្ងៃនេះ', 'callback_data' => 'status'],
                    ['text' => '📊 ប្រវត្តិវត្តមាន ៧ថ្ងៃ', 'callback_data' => 'history'],
                ]
            ]
        ];

        $sent = false;
        if (!empty($targetChatId)) {
            $sent = self::sendMessage($targetChatId, $msg, $keyboard);
        }

        if (!empty($channelChatId) && $channelChatId !== $targetChatId) {
            self::sendMessage($channelChatId, $msg);
        }

        return $sent;
    }

    /**
     * Send interactive Leave Request Alert with 1-Tap [Approve] / [Reject] buttons to Admin.
     */
    public static function sendAdminLeaveAlert(LeaveRequest $leave): int
    {
        $teacher = $leave->teacher;
        $nameKh = $teacher->name_kh ?? '';
        $nameEn = $teacher->name ?? 'Teacher';
        $teacherDisplay = $nameKh ? "{$nameKh} ({$nameEn})" : $nameEn;
        $empId = $teacher->employee_id ?? 'N/A';
        $dept = $teacher->department ?? 'General';
        
        $typeMap = [
            'sick' => 'ឈឺ / Sick Leave',
            'personal' => 'ផ្ទាល់ខ្លួន / Personal Leave',
            'annual' => 'ប្រចាំឆ្នាំ / Annual Leave',
            'maternity' => 'លំហែមាតុភាព / Maternity Leave',
            'other' => 'ផ្សេងៗ / Other',
        ];
        $typeLabel = $typeMap[$leave->leave_type] ?? ucfirst(str_replace('_', ' ', $leave->leave_type));

        $startDate = Carbon::parse($leave->start_date)->format('d/m/Y');
        $endDate = Carbon::parse($leave->end_date)->format('d/m/Y');
        $diffDays = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;

        $msg = "📝 *ពាក្យស្នើសុំច្បាប់ថ្មី / New Leave Request*\n"
             . "━━━━━━━━━━━━━━━━━━━━\n"
             . "👤 *គ្រូបង្រៀន / Teacher:* {$teacherDisplay}\n"
             . "🆔 *ID:* `{$empId}`\n"
             . "🏢 *ដេប៉ាតឺម៉ង់ / Dept:* {$dept}\n"
             . "📋 *ប្រភេទច្បាប់ / Type:* {$typeLabel}\n"
             . "📅 *រយៈពេល / Dates:* {$startDate} → {$endDate} (*{$diffDays} ថ្ងៃ / days*)\n"
             . "💬 *មូលហេតុ / Reason:* \"{$leave->reason}\"\n"
             . "🕒 *ម៉ោងស្នើសុំ / Time:* " . now()->format('h:i A, d M Y') . "\n\n"
             . "👇 _សូមជ្រើសរើសសកម្មភាពខាងក្រោម (Please choose action):_";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ យល់ព្រម (Approve)', 'callback_data' => "leave_approve_{$leave->id}"],
                    ['text' => '❌ បដិសេធ (Reject)', 'callback_data' => "leave_reject_{$leave->id}"],
                ]
            ]
        ];

        $sentCount = 0;
        $channelId = Setting::getValue('telegram_chat_id');
        if ($channelId) {
            if (self::sendMessage($channelId, $msg, $keyboard)) $sentCount++;
        }

        // Also alert any admin users with telegram_chat_id
        $adminUsers = \App\Models\User::whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '')->get();
        foreach ($adminUsers as $admin) {
            if ($admin->telegram_chat_id != $channelId) {
                if (self::sendMessage($admin->telegram_chat_id, $msg, $keyboard)) $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Send bilingual morning check-in reminder.
     */
    public static function sendMorningReminder(Teacher $teacher): bool
    {
        $today = Carbon::today()->format('l, d M Y');
        $nameKh = $teacher->name_kh ? " {$teacher->name_kh}" : '';
        $nameEn = $teacher->name;

        $msg = "🌅 *អរុណសួស្តី / Good Morning{$nameKh} ({$nameEn})!*\n"
             . "━━━━━━━━━━━━━━━━━━━━\n"
             . "📅 *កាលបរិច្ឆេទ / Date:* {$today}\n\n"
             . "⏰ សូមកុំភ្លេចចុះវត្តមានសម្រាប់វេនព្រឹកនេះ (RFID, Face, ឬ QR Code)។\n"
             . "_Don't forget to check in for your morning shift today!_\n\n"
             . "🏛️ *NTTI Attendance System*";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📋 ពិនិត្យវត្តមាន (Check Status)', 'callback_data' => 'status'],
                    ['text' => '📨 ស្នើសុំច្បាប់ (Request Leave)', 'callback_data' => 'request'],
                ],
            ],
        ];

        return self::sendToTeacher($teacher, $msg, $keyboard);
    }

    /**
     * Send absent alert at end of day.
     */
    public static function sendAbsentAlert(Teacher $teacher): bool
    {
        $today = Carbon::today()->format('l, d M Y');
        $msg   = "🚨 *End-of-Day Attendance Notice*\n"
               . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
               . "👤 {$teacher->name}\n"
               . "📅 {$today}\n\n"
               . "❌ No attendance scan recorded for you today. If you were on duty or submitted a leave request, please contact Administration.";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📝 Leave Requests', 'callback_data' => 'leave']],
            ],
        ];

        return self::sendToTeacher($teacher, $msg, $keyboard);
    }

    /**
     * Send late warning if late 3+ times.
     */
    public static function sendLateWarning(Teacher $teacher, int $lateCount): bool
    {
        $monthName = Carbon::now()->format('F Y');
        $msg       = "⚠️ *Punctuality Advisory*\n"
                   . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
                   . "👤 {$teacher->name}\n"
                   . "🗓️ {$monthName}\n\n"
                   . "You have been marked late *{$lateCount} times* this month.\n"
                   . "Please ensure timely arrival to maintain institution standards.";

        return self::sendToTeacher($teacher, $msg);
    }

    /**
     * Send leave request decision notification to teacher.
     */
    public static function sendLeaveNotification(LeaveRequest $leave): bool
    {
        if (!$leave->teacher || empty($leave->teacher->telegram_chat_id)) {
            return false;
        }

        $icon      = $leave->status === 'approved' ? '✅' : '❌';
        $statusKh  = $leave->status === 'approved' ? 'ទទួលបានការយល់ព្រម (APPROVED)' : 'ត្រូវបានបដិសេធ (REJECTED)';
        $start     = Carbon::parse($leave->start_date)->format('d/m/Y');
        $end       = Carbon::parse($leave->end_date)->format('d/m/Y');
        $type      = ucfirst(str_replace('_', ' ', $leave->leave_type ?? 'Leave'));

        $msg = "{$icon} *ដំណឹងស្តីពីពាក្យស្នើសុំច្បាប់ / Leave Request Update*\n"
             . "━━━━━━━━━━━━━━━━━━━━\n"
             . "👤 *គ្រូបង្រៀន / Teacher:* {$leave->teacher->name}\n"
             . "📋 *ប្រភេទ / Type:* {$type}\n"
             . "📅 *កាលបរិច្ឆេទ / Dates:* {$start} → {$end}\n"
             . "📌 *ស្ថានភាព / Status:* *{$statusKh}*\n";

        if (!empty($leave->admin_note)) {
            $msg .= "💬 *ចំណាំ / Note:* {$leave->admin_note}\n";
        }

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📝 មើលពាក្យស្នើសុំទាំងអស់', 'callback_data' => 'leave']],
            ],
        ];

        return self::sendMessage($leave->teacher->telegram_chat_id, $msg, $keyboard);
    }
}
