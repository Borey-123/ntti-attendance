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

            // Store the incoming message
            TelegramMessage::create([
                'teacher_id' => $teacher ? $teacher->id : null,
                'chat_id' => (string)$chatId,
                'username' => $username,
                'message' => $text,
                'is_incoming' => true,
            ]);

            // Reply back to the user acknowledging receipt
            $this->sendReply($chatId, "Thank you. Your message has been received and recorded in the system.");

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
