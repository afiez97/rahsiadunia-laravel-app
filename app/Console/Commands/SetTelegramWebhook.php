<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class SetTelegramWebhook extends Command
{
    protected $signature   = 'telegram:set-webhook {--info : Tunjuk maklumat webhook semasa}';
    protected $description = 'Daftarkan atau semak webhook Telegram';

    public function __construct(private TelegramService $telegram)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('info')) {
            $info = $this->telegram->getWebhookInfo();
            $this->line(json_encode($info, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $url    = rtrim(config('app.url'), '/') . '/api/telegram/webhook';
        $secret = config('services.telegram.webhook_secret');

        $this->info("Mendaftar webhook: {$url}");

        $result = $this->telegram->setWebhook($url, $secret);

        if ($result['ok'] ?? false) {
            $this->info('✅ Webhook berjaya didaftarkan.');
        } else {
            $this->error('❌ Gagal: ' . ($result['description'] ?? 'Unknown error'));
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
