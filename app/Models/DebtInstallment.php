<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DebtInstallment extends Model
{
    protected $fillable = [
        'debt_id', 'installment_number', 'amount', 'due_date',
        'paid_at', 'status', 'proof_path', 'proof_source',
        'telegram_message_id', 'notes', 'warning_sent_at', 'snooze_until',
    ];

    protected function casts(): array
    {
        return [
            'due_date'        => 'date',
            'paid_at'         => 'datetime',
            'snooze_until'    => 'date',
            'warning_sent_at' => 'array',
            'amount'          => 'decimal:2',
        ];
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? asset('storage/' . $this->proof_path) : null;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function isSnoozedToday(): bool
    {
        return $this->snooze_until && $this->snooze_until->gte(Carbon::today());
    }

    // Check if a specific warning key was already sent (e.g. "7_days")
    public function warningAlreadySent(string $key): bool
    {
        return isset(($this->warning_sent_at ?? [])[$key]);
    }

    public function markWarningSent(string $key): void
    {
        $sent        = $this->warning_sent_at ?? [];
        $sent[$key]  = now()->toDateTimeString();
        $this->warning_sent_at = $sent;
        $this->save();
    }

    // Scope: due within N days and not yet paid
    public function scopeDueWithinDays($query, int $days)
    {
        return $query->where('status', 'pending')
            ->whereDate('due_date', '<=', Carbon::today()->addDays($days))
            ->whereDate('due_date', '>=', Carbon::today());
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->whereDate('due_date', '<', Carbon::today());
    }
}
