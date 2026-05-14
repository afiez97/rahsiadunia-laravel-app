<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    private string $baseUrl;

    public function __construct()
    {
        $token         = config('services.telegram.bot_token');
        $this->baseUrl = "https://api.telegram.org/bot{$token}";
    }

    // ---------------------------------------------------------------
    // Send plain message (Markdown v2 parse mode)
    // ---------------------------------------------------------------
    public function sendMessage(int|string $chatId, string $text, array $extra = []): ?array
    {
        return $this->post('sendMessage', array_merge([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ], $extra));
    }

    // ---------------------------------------------------------------
    // Send message with inline keyboard buttons
    // $buttons = [[['text'=>'label','callback_data'=>'data']], ...]
    // ---------------------------------------------------------------
    public function sendMessageWithButtons(int|string $chatId, string $text, array $buttons): ?array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'inline_keyboard' => $buttons,
            ]),
        ]);
    }

    // ---------------------------------------------------------------
    // Send warning with standard inline buttons (Dah Bayar / Snooze / Lihat Semua)
    // ---------------------------------------------------------------
    public function sendInstallmentWarning(
        int|string $chatId,
        string $text,
        int $installmentId
    ): ?array {
        $buttons = [
            [
                ['text' => '✅ Dah Bayar',        'callback_data' => "paid:installment:{$installmentId}"],
                ['text' => '⏰ Snooze 1 hari',    'callback_data' => "snooze:installment:{$installmentId}"],
            ],
            [
                ['text' => '📋 Lihat Semua Hutang', 'callback_data' => 'view_all'],
            ],
        ];

        return $this->sendMessageWithButtons($chatId, $text, $buttons);
    }

    // ---------------------------------------------------------------
    // Answer callback query (required to remove loading spinner)
    // ---------------------------------------------------------------
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): void
    {
        $this->post('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
        ]);
    }

    // ---------------------------------------------------------------
    // Download a file from Telegram and store locally
    // Returns stored path relative to storage/app/public, or null on failure
    // ---------------------------------------------------------------
    public function downloadFile(string $fileId): ?string
    {
        $response = $this->post('getFile', ['file_id' => $fileId]);

        if (!$response || !isset($response['result']['file_path'])) {
            return null;
        }

        $remotePath = $response['result']['file_path'];
        $token      = config('services.telegram.bot_token');
        $url        = "https://api.telegram.org/file/bot{$token}/{$remotePath}";

        try {
            $contents = Http::get($url)->body();
            $ext      = pathinfo($remotePath, PATHINFO_EXTENSION);
            $filename = 'proofs/' . uniqid('tg_') . '.' . $ext;

            Storage::disk('public')->put($filename, $contents);

            return $filename;
        } catch (\Throwable $e) {
            Log::error('Telegram downloadFile failed: ' . $e->getMessage());
            return null;
        }
    }

    // ---------------------------------------------------------------
    // Register webhook with Telegram
    // ---------------------------------------------------------------
    public function setWebhook(string $url, ?string $secret = null): array
    {
        $payload = ['url' => $url];

        if ($secret) {
            $payload['secret_token'] = $secret;
        }

        return $this->post('setWebhook', $payload) ?? [];
    }

    // ---------------------------------------------------------------
    // Get webhook info
    // ---------------------------------------------------------------
    public function getWebhookInfo(): array
    {
        return $this->post('getWebhookInfo', []) ?? [];
    }

    // ---------------------------------------------------------------
    // Build warning message text based on days until due
    // ---------------------------------------------------------------
    public function buildWarningText(
        string $contactName,
        int $installmentNumber,
        int $totalInstallments,
        float $amount,
        string $dueDateFormatted,
        int $daysLeft,
        float $balance
    ): string {
        if ($daysLeft < 0) {
            // Overdue
            return "🚨 *OVERDUE - BELUM BAYAR*\n"
                 . "{$contactName} — Ansuran {$installmentNumber}/{$totalInstallments}\n"
                 . "Jumlah: RM" . number_format($amount, 2) . "\n"
                 . "Due date: {$dueDateFormatted} (dah lepas!)\n"
                 . "Sila bayar segera.";
        }

        if ($daysLeft === 0) {
            return "🔴 *HARI INI DUE DATE!*\n"
                 . "{$contactName} — Ansuran {$installmentNumber}/{$totalInstallments}\n"
                 . "Jumlah: RM" . number_format($amount, 2) . "\n"
                 . "Sila bayar sekarang untuk elak penalti.";
        }

        if ($daysLeft <= 3) {
            return "⚠️ *Peringatan Segera!*\n"
                 . "{$contactName} — Ansuran {$installmentNumber}/{$totalInstallments}\n"
                 . "Jumlah: RM" . number_format($amount, 2) . "\n"
                 . "Due date: {$dueDateFormatted} ({$daysLeft} hari lagi!)\n"
                 . "Hantar bukti bayaran ke sini selepas bayar.";
        }

        // 7+ days — gentle reminder
        return "⚠️ *Peringatan Hutang*\n"
             . "{$contactName} — Ansuran {$installmentNumber}/{$totalInstallments}\n"
             . "Jumlah: RM" . number_format($amount, 2) . "\n"
             . "Due date: {$dueDateFormatted} ({$daysLeft} hari lagi)\n"
             . "Baki keseluruhan: RM" . number_format($balance, 2);
    }

    // ---------------------------------------------------------------
    // Internal HTTP helper
    // ---------------------------------------------------------------
    private function post(string $method, array $payload): ?array
    {
        try {
            $response = Http::post("{$this->baseUrl}/{$method}", $payload);
            return $response->json();
        } catch (\Throwable $e) {
            Log::error("Telegram API [{$method}] failed: " . $e->getMessage());
            return null;
        }
    }
}
