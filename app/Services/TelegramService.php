<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function enabled(): bool
    {
        return (bool) config('services.telegram.enabled', false)
            && filled(config('services.telegram.bot_token'))
            && filled(config('services.telegram.chat_id'));
    }

    public function send(string $message): bool
    {
        if (!$this->enabled()) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->post(
                    'https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage',
                    [
                        'chat_id' => config('services.telegram.chat_id'),
                        'text' => $message,
                        'disable_web_page_preview' => true,
                    ]
                );

            if (!$response->successful() || !$response->json('ok')) {
                Log::error('Telegram send failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Telegram exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
