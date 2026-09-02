<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Run long polling worker for Telegram Bot updates';

    public function handle()
    {
        $this->info('Starting NTTI Telegram Long Polling Worker...');

        $token = Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$token) {
            $this->error('Telegram Bot Token not configured!');
            return 1;
        }

        // Delete any existing webhook to enable long polling mode
        Http::post("https://api.telegram.org/bot{$token}/deleteWebhook");
        $this->info('Cleared webhook. Switched to Long Polling mode.');

        $offset = 0;

        while (true) {
            try {
                // Fetch new updates with 20 second long poll timeout
                $res = Http::timeout(30)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset'  => $offset,
                    'timeout' => 20,
                ]);

                if ($res->successful() && $res->json('ok')) {
                    $updates = $res->json('result') ?? [];
                    foreach ($updates as $update) {
                        $offset = $update['update_id'] + 1;
                        $controller = new TelegramWebhookController();

                        // Regular text/command message
                        if (isset($update['message'])) {
                            $controller->processIncomingMessage($update['message']);
                        }

                        // Inline keyboard button tap
                        if (isset($update['callback_query'])) {
                            $controller->processCallbackQuery($update['callback_query']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Telegram Polling Exception: ' . $e->getMessage());
                sleep(3);
            }
            usleep(200000); // 0.2s pause between polls
        }
    }
}
