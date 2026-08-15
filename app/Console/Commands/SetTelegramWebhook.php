<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the Telegram Webhook URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $token = Setting::getValue('telegram_bot_token');

        if (!$token) {
            $this->error('Telegram Bot Token is not configured in settings.');
            return 1;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url
        ]);

        if ($response->successful() && $response->json('ok')) {
            $this->info("Webhook successfully set to: {$url}");
            return 0;
        } else {
            $this->error("Failed to set webhook: " . $response->json('description'));
            return 1;
        }
    }
}

