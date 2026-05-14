{{-- Warning Settings Partial — requires Alpine x-data parent --}}
<div class="mb-6">
    <h3 class="font-semibold text-nude-text border-b border-nude-border pb-2 mb-4">Tetapan Due Date & Peringatan</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Due date setiap bulan (hari)
                <span class="font-normal text-gray-400">— cth: 28 untuk Shopee PayLater</span>
            </label>
            <input type="number" name="due_day_of_month" min="1" max="31"
                value="{{ old('due_day_of_month', isset($debt) ? $debt->due_day_of_month : '') }}"
                class="nude-input w-full" placeholder="1-31"
                x-model.number="dueDayOfMonth"
                @input="calcNextDue()">
        </div>

        <div class="flex items-end">
            <div x-show="nextDuePreview" x-transition class="text-sm text-nude-text p-3 bg-nude-bg rounded-lg border border-nude-border w-full">
                <p class="text-xs text-gray-400 mb-0.5">Due date seterusnya</p>
                <p class="font-semibold" x-text="nextDuePreview"></p>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-600 mb-2">Bila hantar peringatan?</label>
        <div class="flex flex-wrap gap-3">
            @foreach([14 => '14 hari', 7 => '7 hari', 3 => '3 hari', 1 => '1 hari'] as $day => $label)
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="warning_days[]" value="{{ $day }}"
                        class="w-4 h-4 accent-nude-accent"
                        {{ in_array($day, old('warning_days', isset($debt) ? ($debt->warning_days ?? [7,3,1]) : [7,3,1])) ? 'checked' : '' }}>
                    {{ $label }} sebelum
                </label>
            @endforeach

            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="warn_on_due_date" value="1"
                    class="w-4 h-4 accent-nude-accent"
                    {{ old('warn_on_due_date', isset($debt) ? $debt->warn_on_due_date : true) ? 'checked' : '' }}>
                Pada hari due date
            </label>

            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="warn_if_overdue" value="1"
                    class="w-4 h-4 accent-nude-accent"
                    {{ old('warn_if_overdue', isset($debt) ? $debt->warn_if_overdue : true) ? 'checked' : '' }}>
                Sehari selepas jika belum bayar
            </label>
        </div>
    </div>
</div>

@once
<script>
// Alpine extension — tambah ke dalam hutangForm() atau sebagai mixin
function warningSettingsMixin() {
    return {
        dueDayOfMonth: {{ old('due_day_of_month', isset($debt) ? ($debt->due_day_of_month ?? 0) : 0) }},
        nextDuePreview: '',

        calcNextDue() {
            const day = parseInt(this.dueDayOfMonth);
            if (!day || day < 1 || day > 31) { this.nextDuePreview = ''; return; }

            const today    = new Date();
            let year       = today.getFullYear();
            let month      = today.getMonth(); // 0-indexed

            // Last day of current month
            const lastDay  = new Date(year, month + 1, 0).getDate();
            const actualDay= Math.min(day, lastDay);
            let candidate  = new Date(year, month, actualDay);

            if (candidate < today) {
                month++;
                if (month > 11) { month = 0; year++; }
                const ld2  = new Date(year, month + 1, 0).getDate();
                candidate  = new Date(year, month, Math.min(day, ld2));
            }

            const diffMs   = candidate - today;
            const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
            const fmt      = candidate.toLocaleDateString('ms-MY', {day:'2-digit', month:'long', year:'numeric'});
            this.nextDuePreview = `${fmt} (dalam ${diffDays} hari)`;
        },
    };
}
</script>
@endonce
