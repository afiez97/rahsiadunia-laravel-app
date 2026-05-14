<div class="nude-card p-5 mb-6" x-data="{ open: false }">
    <button type="button" @click="open = !open"
        class="w-full flex items-center justify-between text-left">
        <h3 class="font-semibold text-nude-text">Log Bayaran Baru</h3>
        <svg class="w-5 h-5 text-nude-accent transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-transition class="mt-4">
        <form method="POST"
              action="{{ route('hutang.payment.store', $debt) }}"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Jumlah (RM)</label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                        class="nude-input w-full" placeholder="0.00" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Tarikh Bayaran</label>
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}"
                        class="nude-input w-full" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Kaedah Bayaran</label>
                <select name="payment_method" class="nude-input w-full" required>
                    @foreach(['cash'=>'Tunai','maybank'=>'Maybank','tng'=>'Touch n Go','duitnow'=>'DuitNow','splitwise'=>'Splitwise','other'=>'Lain-lain'] as $val => $label)
                        <option value="{{ $val }}" @selected($debt->payment_method === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Nota</label>
                <input type="text" name="notes" class="nude-input w-full" placeholder="Rujukan, nombor resit, dll.">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Bukti Bayaran (pilihan)</label>
                <input type="file" name="proof" accept="image/*,.pdf" class="nude-input w-full text-sm">
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, atau PDF — maks 5MB. Atau hantar gambar ke Telegram bot.</p>
            </div>

            <button type="submit" class="nude-button">Rekod Bayaran</button>
        </form>
    </div>
</div>
