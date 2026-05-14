<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Debt extends Model
{
    protected $fillable = [
        'user_id', 'contact_name', 'direction', 'total_amount', 'paid_amount',
        'payment_method', 'description', 'status',
        'due_day_of_month', 'warning_days', 'warn_on_due_date', 'warn_if_overdue',
        'is_installment', 'installment_count', 'installment_frequency', 'first_installment_date',
    ];

    protected function casts(): array
    {
        return [
            'warning_days'           => 'array',
            'warn_on_due_date'       => 'boolean',
            'warn_if_overdue'        => 'boolean',
            'is_installment'         => 'boolean',
            'total_amount'           => 'decimal:2',
            'paid_amount'            => 'decimal:2',
            'first_installment_date' => 'date',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(DebtInstallment::class)->orderBy('installment_number');
    }

    // --- Accessors ---

    public function getBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_amount <= 0) return 0;
        return (int) min(100, round(($this->paid_amount / $this->total_amount) * 100));
    }

    // Next due date based on due_day_of_month
    public function getNextDueDateAttribute(): ?Carbon
    {
        if (!$this->due_day_of_month) return null;

        $today = Carbon::today();
        $day   = $this->due_day_of_month;

        // Try this month first; if already passed, go next month
        $candidate = Carbon::create($today->year, $today->month, 1)
            ->endOfMonth()
            ->min(Carbon::create($today->year, $today->month, $day));

        if ($candidate->lt($today)) {
            $next = $today->copy()->addMonthNoOverflow();
            $candidate = Carbon::create($next->year, $next->month, 1)
                ->endOfMonth()
                ->min(Carbon::create($next->year, $next->month, $day));
        }

        return $candidate;
    }

    // --- Scopes ---

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    public function scopeIOwe($query)
    {
        return $query->where('direction', 'i_owe');
    }

    public function scopeTheyOwe($query)
    {
        return $query->where('direction', 'they_owe');
    }

    // --- Helpers ---

    public function recalculateStatus(): void
    {
        if ($this->paid_amount <= 0) {
            $this->status = 'pending';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->status = 'settled';
        } else {
            $this->status = 'partial';
        }
    }

    /**
     * Generate installment schedule. Returns array of ['number', 'amount', 'due_date'].
     */
    public static function buildInstallmentSchedule(
        float $totalAmount,
        int $count,
        string $frequency,
        Carbon $firstDate
    ): array {
        $base      = floor(($totalAmount / $count) * 100) / 100;
        $remainder = round($totalAmount - ($base * $count), 2);

        $schedule = [];
        $date     = $firstDate->copy();

        for ($i = 1; $i <= $count; $i++) {
            $amount = $i === $count ? round($base + $remainder, 2) : $base;

            $schedule[] = [
                'number'   => $i,
                'amount'   => $amount,
                'due_date' => $date->toDateString(),
            ];

            $date = match ($frequency) {
                'weekly' => $date->copy()->addWeek(),
                default  => $date->copy()->addMonthNoOverflow(),
            };
        }

        return $schedule;
    }
}
