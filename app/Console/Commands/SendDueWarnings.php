<?php

namespace App\Console\Commands;

use App\Models\DebtInstallment;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDueWarnings extends Command
{
    protected $signature   = 'telegram:send-due-warnings';
    protected $description = 'Hantar peringatan hutang kepada pengguna melalui Telegram';

    public function __construct(private TelegramService $telegram)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $sent  = 0;

        // Load all pending installments with their debts and user's telegram_chat_id
        $installments = DebtInstallment::with('debt.user')
            ->where('status', 'pending')
            ->get();

        foreach ($installments as $installment) {
            $debt = $installment->debt;
            $user = $debt->user;

            if (!$user->telegram_chat_id) continue;

            // Check snooze
            if ($installment->isSnoozedToday()) continue;

            $chatId    = $user->telegram_chat_id;
            $daysLeft  = $today->diffInDays($installment->due_date, false);
            $warningDays = $debt->warning_days ?? [7, 3, 1];
            $total       = $debt->installments()->count();

            // Overdue warning
            if ($daysLeft < 0 && $debt->warn_if_overdue) {
                $key = 'overdue_' . $today->toDateString();
                if (!$installment->warningAlreadySent($key)) {
                    $this->dispatchWarning($chatId, $installment, $debt, $user, $daysLeft, $total);
                    $installment->markWarningSent($key);
                    $installment->update(['status' => 'overdue']);
                    $sent++;
                }
                continue;
            }

            // Due date warning
            if ($daysLeft === 0 && $debt->warn_on_due_date) {
                $key = 'due_date';
                if (!$installment->warningAlreadySent($key)) {
                    $this->dispatchWarning($chatId, $installment, $debt, $user, 0, $total);
                    $installment->markWarningSent($key);
                    $sent++;
                }
                continue;
            }

            // N-days-before warning
            foreach ($warningDays as $day) {
                if ($daysLeft === (int) $day) {
                    $key = "{$day}_days";
                    if (!$installment->warningAlreadySent($key)) {
                        $this->dispatchWarning($chatId, $installment, $debt, $user, $daysLeft, $total);
                        $installment->markWarningSent($key);
                        $sent++;
                    }
                }
            }
        }

        $this->info("Selesai. {$sent} peringatan dihantar.");
        return self::SUCCESS;
    }

    private function dispatchWarning($chatId, DebtInstallment $ins, $debt, $user, int $daysLeft, int $total): void
    {
        $text = $this->telegram->buildWarningText(
            $debt->contact_name,
            $ins->installment_number,
            $total,
            (float) $ins->amount,
            $ins->due_date->format('d M Y'),
            $daysLeft,
            (float) $debt->balance,
        );

        $this->telegram->sendInstallmentWarning($chatId, $text, $ins->id);

        $this->line("Hantar ke {$user->name} ({$chatId}): Ansuran #{$ins->installment_number}");
    }
}
