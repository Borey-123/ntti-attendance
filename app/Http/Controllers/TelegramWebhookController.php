<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\TeacherSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        Log::info('Telegram Webhook Received:', $data);

        if (isset($data['message'])) {
            $this->processIncomingMessage($data['message']);
        }

        return response()->json(['status' => 'ok']);
    }

    public function processIncomingMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chatId || !$text) {
            return;
        }

        // Find teacher by Telegram Chat ID
        $teacher = Teacher::where('telegram_chat_id', (string)$chatId)->first();

        $cleanText = strtolower(ltrim($text, '/'));

        if (str_starts_with(strtolower($text), '/start') || $cleanText === 'start') {
            $parts = explode(' ', $text);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $teacherId = $parts[1];
                $t = Teacher::find($teacherId);
                if ($t) {
                    $t->update(['telegram_chat_id' => (string)$chatId]);
                    $this->sendMessage($chatId, "✅ Hello {$t->name}! Your Telegram account has been linked to the NTTI Teacher Attendance System.");
                    return;
                }
            }

            if ($teacher) {
                $this->sendMessage($chatId, "👋 Welcome back, {$teacher->name}!\n\nAvailable commands:\n• `/status` - Check today's check-in/out status\n• `/schedule` - View today's class schedule\n• `/help` - Command help");
            } else {
                $this->sendMessage($chatId, "👋 Welcome to NTTI Attendance Bot!\nTo link your profile, please copy your personal link from your Teacher Portal.");
            }
            return;
        }

        if (!$teacher) {
            $this->sendMessage($chatId, "⚠️ Account not linked. Please link your Telegram account from the NTTI Teacher Portal.");
            return;
        }

        // Handle commands for linked teachers
        switch ($cleanText) {
            case 'status':
                $today = date('Y-m-d');
                $att = Attendance::where('teacher_id', $teacher->id)->where('date', $today)->first();
                if ($att) {
                    $msg = "📋 *Today's Attendance Status*\n"
                         . "👤 *Teacher:* {$teacher->name}\n"
                         . "📅 *Date:* {$today}\n"
                         . "⏰ *Check-In:* " . ($att->time_in ?? 'Not scanned') . "\n"
                         . "🚪 *Check-Out:* " . ($att->time_out ?? 'Not scanned');
                } else {
                    $msg = "📋 *Today's Attendance Status*\n👤 *Teacher:* {$teacher->name}\n⚠️ No check-in recorded for today yet.";
                }
                $this->sendMessage($chatId, $msg);
                break;

            case 'schedule':
                $dayOfWeek = date('N'); // 1 (Mon) to 7 (Sun)
                $schedules = TeacherSchedule::where('teacher_id', $teacher->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->orderBy('start_time')
                    ->get();

                if ($schedules->count() > 0) {
                    $msg = "📅 *Today's Teaching Schedule*\n";
                    foreach ($schedules as $s) {
                        $msg .= "• *{$s->subject_name}*: {$s->start_time} - {$s->end_time} (Room: {$s->room_number})\n";
                    }
                } else {
                    $msg = "📅 *Today's Teaching Schedule*\nNo classes scheduled for today!";
                }
                $this->sendMessage($chatId, $msg);
                break;

            case 'help':
            default:
                $msg = "ℹ️ *Available Commands*\n"
                     . "• `/status` - Check today's check-in/out times\n"
                     . "• `/schedule` - View today's class schedule\n"
                     . "• `/help` - Show command help";
                $this->sendMessage($chatId, $msg);
                break;
        }
    }

    private function sendMessage($chatId, $text)
    {
        $botToken = \App\Models\Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) {
            Log::error('Telegram Webhook: Bot Token not found in settings or env.');
            return;
        }

        $res = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);

        Log::info('Telegram Webhook Send Message Result:', [
            'chat_id' => $chatId,
            'text' => $text,
            'status' => $res->status(),
            'response' => $res->json()
        ]);
    }
}
