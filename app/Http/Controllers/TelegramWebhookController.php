<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Models\DebtPayment;
use App\Models\User;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramWebhookController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    public function handle(Request $request)
    {
        // Validate webhook secret header
        $secret = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if (config('services.telegram.webhook_secret') && $secret !== config('services.telegram.webhook_secret')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $update = $request->all();
        Log::debug('Telegram update', $update);

        try {
            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            } elseif (isset($update['message'])) {
                $this->handleMessage($update['message']);
            }
        } catch (\Throwable $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return response()->json(['ok' => true]);
    }

    // ---------------------------------------------------------------
    // Handle incoming messages
    // ---------------------------------------------------------------
    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $user   = User::where('telegram_chat_id', $chatId)->first();

        // Handle photo/document (proof upload)
        if (isset($message['photo']) || isset($message['document'])) {
            $this->handleProofUpload($message, $chatId, $user);
            return;
        }

        $text = trim($message['text'] ?? '');

        // /myid — always available without auth
        if ($text === '/myid') {
            $this->telegram->sendMessage($chatId, "Chat ID anda: `{$chatId}`\n\nSalin dan tampal dalam tetapan profil anda.");
            return;
        }

        if (!$user) {
            $this->telegram->sendMessage($chatId, "⚠️ Telegram anda belum dipautkan ke akaun Rahsia Dunia.\n\nLog masuk dan masukkan Chat ID `{$chatId}` dalam Tetapan Profil anda.");
            return;
        }

        // Route commands
        match (true) {
            str_starts_with($text, '/start')   => $this->cmdStart($chatId, $user),
            str_starts_with($text, '/hutang')  => $this->cmdHutang($chatId, $user),
            str_starts_with($text, '/bayar')   => $this->cmdBayar($chatId, $user, $text),
            str_starts_with($text, '/ansuran') => $this->cmdAnsuran($chatId, $user, $text),
            str_starts_with($text, '/skip')    => $this->cmdSkip($chatId, $user),
            default                            => $this->telegram->sendMessage($chatId, "Taip /hutang untuk lihat senarai hutang aktif, atau /myid untuk dapat Chat ID anda."),
        };
    }

    // ---------------------------------------------------------------
    // Handle callback query (inline buttons)
    // ---------------------------------------------------------------
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId   = $callbackQuery['message']['chat']['id'];
        $msgId    = $callbackQuery['message']['message_id'];
        $data     = $callbackQuery['data'] ?? '';
        $user     = User::where('telegram_chat_id', $chatId)->first();

        $this->telegram->answerCallbackQuery($callbackQuery['id']);

        if (!$user) {
            $this->telegram->sendMessage($chatId, "Akaun tidak dipautkan.");
            return;
        }

        // paid:installment:{id}
        if (str_starts_with($data, 'paid:installment:')) {
            $installmentId = (int) str_replace('paid:installment:', '', $data);
            $this->handleInstallmentPaid($chatId, $user, $installmentId);
            return;
        }

        // snooze:installment:{id}
        if (str_starts_with($data, 'snooze:installment:')) {
            $installmentId = (int) str_replace('snooze:installment:', '', $data);
            $this->handleSnooze($chatId, $user, $installmentId);
            return;
        }

        // proof_for:debt:{id}  — user memilih hutang untuk bukti
        if (str_starts_with($data, 'proof_for:debt:')) {
            $debtId = (int) str_replace('proof_for:debt:', '', $data);
            Cache::put("telegram_pending_proof:{$chatId}", ['debt_id' => $debtId], 300);
            $this->telegram->sendMessage($chatId, "Hantar gambar/PDF bukti bayaran untuk hutang yang dipilih.");
            return;
        }

        // view_all
        if ($data === 'view_all') {
            $this->cmdHutang($chatId, $user);
            return;
        }
    }

    // ---------------------------------------------------------------
    // Handle proof photo/document upload
    // ---------------------------------------------------------------
    private function handleProofUpload(array $message, int $chatId, ?User $user): void
    {
        if (!$user) {
            $this->telegram->sendMessage($chatId, "Akaun tidak dipautkan. Hantar /myid untuk dapat Chat ID anda.");
            return;
        }

        // Check pending state (dari tekan "Dah Bayar")
        $pending = Cache::get("telegram_pending_proof:{$chatId}");

        if (!$pending) {
            // Tanya user ini bukti untuk hutang mana
            $debts = $user->debts()->active()->get();
            if ($debts->isEmpty()) {
                $this->telegram->sendMessage($chatId, "Tiada hutang aktif.");
                return;
            }

            $buttons = $debts->map(fn($d) => [[
                'text'          => "{$d->contact_name} (RM " . number_format($d->balance, 2) . ")",
                'callback_data' => "proof_for:debt:{$d->id}",
            ]])->toArray();

            $this->telegram->sendMessageWithButtons($chatId, "Ini bukti untuk hutang mana?", $buttons);
            return;
        }

        // Download file from Telegram
        $fileId = isset($message['photo'])
            ? collect($message['photo'])->last()['file_id']
            : $message['document']['file_id'];

        $filePath = $this->telegram->downloadFile($fileId);

        if (!$filePath) {
            $this->telegram->sendMessage($chatId, "Gagal muat turun fail. Cuba lagi.");
            return;
        }

        // Attach proof to debt or installment
        $debtId       = $pending['debt_id'] ?? null;
        $installmentId= $pending['installment_id'] ?? null;

        if ($installmentId) {
            $installment = DebtInstallment::find($installmentId);
            if ($installment && $installment->debt->user_id === $user->id) {
                $installment->update([
                    'proof_path'        => $filePath,
                    'proof_source'      => 'telegram',
                    'telegram_message_id' => (string) $message['message_id'],
                    'status'            => 'paid',
                    'paid_at'           => now(),
                ]);

                $debt = $installment->debt;
                $debt->paid_amount = $debt->payments()->sum('amount');
                $debt->recalculateStatus();
                $debt->save();

                $balance = number_format($debt->balance, 2);
                $this->telegram->sendMessage($chatId, "✅ Bukti ansuran {$installment->installment_number} berjaya disimpan.\nBaki: RM{$balance}");
            }
        } elseif ($debtId) {
            $debt = Debt::find($debtId);
            if ($debt && $debt->user_id === $user->id) {
                DebtPayment::create([
                    'debt_id'           => $debt->id,
                    'amount'            => 0, // user perlu update jumlah manual atau via command
                    'payment_date'      => now()->toDateString(),
                    'payment_method'    => $debt->payment_method,
                    'notes'             => 'Bukti dari Telegram',
                    'proof_path'        => $filePath,
                    'proof_source'      => 'telegram',
                    'telegram_message_id' => (string) $message['message_id'],
                ]);

                $this->telegram->sendMessage($chatId, "✅ Bukti bayaran disimpan.\nLog ke sistem untuk kemaskini jumlah bayaran.");
            }
        }

        Cache::forget("telegram_pending_proof:{$chatId}");
    }

    // ---------------------------------------------------------------
    // Commands
    // ---------------------------------------------------------------
    private function cmdStart(int $chatId, User $user): void
    {
        $this->telegram->sendMessage($chatId,
            "👋 Salam, {$user->name}!\n\n" .
            "Bot Hutang Tracker aktif.\n\n" .
            "Arahan yang tersedia:\n" .
            "/hutang — senarai hutang aktif\n" .
            "/bayar [id] [jumlah] — log bayaran\n" .
            "/ansuran [id] — jadual ansuran\n" .
            "/myid — dapatkan Chat ID anda\n" .
            "/skip — skip upload bukti"
        );
    }

    private function cmdHutang(int $chatId, User $user): void
    {
        $debts = $user->debts()->active()->get();

        if ($debts->isEmpty()) {
            $this->telegram->sendMessage($chatId, "Tiada hutang aktif. 🎉");
            return;
        }

        $lines = ["📋 *Hutang Aktif:*\n"];
        foreach ($debts as $debt) {
            $dir    = $debt->direction === 'i_owe' ? '➡️ Saya hutang' : '⬅️ Dia hutang';
            $status = match($debt->status) {
                'partial' => '🔶',
                default   => '🔴',
            };
            $lines[] = "{$status} *{$debt->contact_name}* [{$dir}]\n"
                     . "   Baki: RM " . number_format($debt->balance, 2)
                     . " (ID: `{$debt->id}`)";
        }

        $this->telegram->sendMessage($chatId, implode("\n", $lines));
    }

    private function cmdBayar(int $chatId, User $user, string $text): void
    {
        // /bayar {debt_id} {amount}
        $parts = explode(' ', $text);
        if (count($parts) < 3 || !is_numeric($parts[1]) || !is_numeric($parts[2])) {
            $this->telegram->sendMessage($chatId, "Format: /bayar {id_hutang} {jumlah}\nContoh: /bayar 5 150.00");
            return;
        }

        $debt = Debt::find((int) $parts[1]);
        if (!$debt || $debt->user_id !== $user->id) {
            $this->telegram->sendMessage($chatId, "Hutang tidak dijumpai.");
            return;
        }

        $amount = (float) $parts[2];
        DebtPayment::create([
            'debt_id'        => $debt->id,
            'amount'         => $amount,
            'payment_date'   => now()->toDateString(),
            'payment_method' => $debt->payment_method,
            'notes'          => 'Log via Telegram',
            'proof_source'   => 'telegram',
        ]);

        $debt->paid_amount = $debt->payments()->sum('amount');
        $debt->recalculateStatus();
        $debt->save();

        $this->telegram->sendMessage($chatId,
            "✅ Bayaran RM" . number_format($amount, 2) . " direkod.\n" .
            "Baki: RM" . number_format($debt->balance, 2) . "\n\n" .
            "Hantar gambar bukti atau taip /skip."
        );

        Cache::put("telegram_pending_proof:{$chatId}", ['debt_id' => $debt->id], 300);
    }

    private function cmdAnsuran(int $chatId, User $user, string $text): void
    {
        $parts  = explode(' ', $text);
        $debtId = isset($parts[1]) ? (int) $parts[1] : null;

        if (!$debtId) {
            $this->telegram->sendMessage($chatId, "Format: /ansuran {id_hutang}");
            return;
        }

        $debt = Debt::with('installments')->find($debtId);
        if (!$debt || $debt->user_id !== $user->id) {
            $this->telegram->sendMessage($chatId, "Hutang tidak dijumpai.");
            return;
        }

        if ($debt->installments->isEmpty()) {
            $this->telegram->sendMessage($chatId, "Hutang ini tiada jadual ansuran.");
            return;
        }

        $paid  = $debt->installments->where('status', 'paid')->count();
        $total = $debt->installments->count();
        $lines = ["📅 *Ansuran {$debt->contact_name}* ({$paid}/{$total} bayar)\n"];

        foreach ($debt->installments as $ins) {
            $icon = match($ins->status) {
                'paid'    => '✅',
                'overdue' => '🚨',
                default   => '🔲',
            };
            $lines[] = "{$icon} #{$ins->installment_number} — RM" . number_format($ins->amount, 2)
                     . " | Due: " . $ins->due_date->format('d M Y');
        }

        $this->telegram->sendMessage($chatId, implode("\n", $lines));
    }

    private function cmdSkip(int $chatId, User $user): void
    {
        Cache::forget("telegram_pending_proof:{$chatId}");
        $this->telegram->sendMessage($chatId, "OK, skip bukti. Anda boleh hantar kemudian.");
    }

    // ---------------------------------------------------------------
    // Callback handlers
    // ---------------------------------------------------------------
    private function handleInstallmentPaid(int $chatId, User $user, int $installmentId): void
    {
        $installment = DebtInstallment::with('debt')->find($installmentId);
        if (!$installment || $installment->debt->user_id !== $user->id) {
            $this->telegram->sendMessage($chatId, "Rekod tidak dijumpai.");
            return;
        }

        // Set pending state — tunggu bukti
        Cache::put("telegram_pending_proof:{$chatId}", [
            'installment_id' => $installmentId,
            'debt_id'        => $installment->debt_id,
        ], 600);

        $this->telegram->sendMessage($chatId,
            "Hantar gambar bukti bayaran ansuran {$installment->installment_number} atau taip /skip."
        );
    }

    private function handleSnooze(int $chatId, User $user, int $installmentId): void
    {
        $installment = DebtInstallment::with('debt')->find($installmentId);
        if (!$installment || $installment->debt->user_id !== $user->id) {
            $this->telegram->sendMessage($chatId, "Rekod tidak dijumpai.");
            return;
        }

        $installment->update(['snooze_until' => Carbon::tomorrow()->toDateString()]);
        $this->telegram->sendMessage($chatId, "⏰ Peringatan ditangguhkan hingga esok.");
    }
}
