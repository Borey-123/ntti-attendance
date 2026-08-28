<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramMessage;
use App\Models\Teacher;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            // Telegram sends an 'update' object
            $update = $request->all();

            if (!isset($update['message'])) {
                return response()->json(['status' => 'ignored']);
            }

            $message = $update['message'];
            $chat = $message['chat'] ?? [];
            $chatId = $chat['id'] ?? null;
            $text = $message['text'] ?? '';
            $username = $chat['username'] ?? ($chat['first_name'] ?? 'Unknown');

            if (!$chatId) {
                return response()->json(['status' => 'ignored']);
            }

            // Find if this chat ID belongs to any teacher
            $teacher = Teacher::where('telegram_chat_id', (string)$chatId)->first();

            $justLinked = false;
            if (!$teacher) {
                // Check if the user sent a valid Teacher ID or Phone Number to link their account
                $inputStr = trim($text);
                
                // Find by Employee ID first
                $potentialTeacher = Teacher::where('employee_id', 'LIKE', $inputStr)->first();
                
                // If not found, try by Phone Number
                if (!$potentialTeacher) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', $inputStr);
                    if (strlen($cleanPhone) >= 8) {
                        // Strip leading 0 or +855 to match any phone format
                        $searchPhone = ltrim($cleanPhone, '0');
                        if (str_starts_with($searchPhone, '855')) {
                            $searchPhone = substr($searchPhone, 3);
                        }
                        
                        $potentialTeacher = Teacher::where('phone', 'LIKE', '%' . $searchPhone . '%')->first();
                    }
                }
                
                if ($potentialTeacher) {
                    if (!empty($potentialTeacher->telegram_chat_id)) {
                        $this->sendReply($chatId, "⚠️ This Teacher ID / Phone Number is already linked to another Telegram account. Please contact an admin if you need to reset it.");
                        return response()->json(['status' => 'success']);
                    }
                    
                    $potentialTeacher->update(['telegram_chat_id' => (string)$chatId]);
                    $teacher = $potentialTeacher;
                    $justLinked = true;
                }
            }

            // Store the incoming message
            TelegramMessage::create([
                'teacher_id' => $teacher ? $teacher->id : null,
                'chat_id' => (string)$chatId,
                'username' => $username,
                'message' => $text,
                'is_incoming' => true,
            ]);

            // Reply back to the user
            if ($justLinked) {
                $this->sendReply($chatId, "✅ Success! Your Telegram account has been securely linked to Teacher ID: {$teacher->employee_id} ({$teacher->name}). You will now receive attendance notifications here.");
            } elseif ($teacher) {
                $this->sendReply($chatId, "Thank you, {$teacher->name}. Your message has been received.");
            } else {
                $this->sendReply($chatId, "👋 Welcome to NTTI Attendance Bot! Your account is not yet linked.\n\nTo link your account, please reply with your exact Teacher ID (e.g., T0001) or your registered Phone Number.");
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Telegram Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function sendReply($chatId, $text)
    {
        $token = Setting::getValue('telegram_bot_token');
        if (!$token) return;

        try {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram reply: ' . $e->getMessage());
        }
    }
}
