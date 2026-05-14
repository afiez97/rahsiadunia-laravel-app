<?php

namespace App\Console\Commands;

use App\Models\Debt;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestTelegramWarning extends Command
{
    protected $signature   = 'telegram:test-warning {debt_id : ID hutang untuk diuji}';
    protected $description = 'Hantar mesej ujian Telegram untuk hutang tertentu';

    public function __construct(private TelegramService $telegram)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $debt = Debt::with(['user', 'installments'])->find($this->argument('debt_id'));

        if (!$debt) {
            $this->error('Hutang tidak dijumpai.');
            return self::FAILURE;
        }

        $user = $debt->user;

        if (!$user->telegram_chat_id) {
            $this->error("Pengguna {$user->name} belum set telegram_chat_id.");
            return self::FAILURE;
        }

        $chatId = $user->telegram_chat_id;

        if ($debt->installments->isNotEmpty()) {
            $installment = $debt->installments->where('status', 'pending')->first()
                        ?? $debt->installments->first();

            $daysLeft = Carbon::today()->diffInDays($installment->due_date, false);
            $total    = $debt->installments->count();

            $text = $this->telegram->buildWarningText(
                $debt->contact_name,
                $installment->installment_number,
                $total,
                (float) $installment->amount,
                $installment->due_date->format('d M Y'),
                $daysLeft,
                (float) $debt->balance,
            );

            $this->telegram->sendInstallmentWarning($chatId, "[TEST] {$text}", $installment->id);
        } else {
            $text = "⚠️ [TEST] *Peringatan Hutang*\n"
                  . "{$debt->contact_name}\n"
                  . "Baki: RM" . number_format($debt->balance, 2);
            $this->telegram->sendMessage($chatId, $text);
        }

        $this->info("✅ Mesej ujian dihantar ke {$user->name} ({$chatId}).");
        return self::SUCCESS;
    }
}
