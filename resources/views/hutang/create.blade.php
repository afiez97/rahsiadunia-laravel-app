<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-nude-text">Tambah Hutang Baru</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6">
        <div class="nude-card p-6"
             x-data="hutangForm()"
             x-init="init()">

            <form method="POST" action="{{ route('hutang.store') }}">
                @csrf

                {{-- Maklumat Asas --}}
                <div class="space-y-4 mb-6">
                    <h3 class="font-semibold text-nude-text border-b border-nude-border pb-2">Maklumat Hutang</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Orang</label>
                            <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                                class="nude-input w-full" placeholder="cth: Azri, Shopee PayLater" required>
                            @error('contact_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Arah Hutang</label>
                            <select name="direction" class="nude-input w-full" required x-model="direction">
                                <option value="i_owe"    @selected(old('direction') === 'i_owe')>➡️ Saya berhutang</option>
                                <option value="they_owe" @selected(old('direction') === 'they_owe')>⬅️ Dia berhutang</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Jumlah (RM)</label>
                            <input type="number" name="total_amount" value="{{ old('total_amount') }}"
                                step="0.01" min="0.01" class="nude-input w-full" placeholder="0.00" required
                                x-model.number="totalAmount" @input="recalcInstallments()">
                            @error('total_amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Kaedah Bayaran</label>
                            <select name="payment_method" class="nude-input w-full" required>
                                @foreach(['cash'=>'Tunai','maybank'=>'Maybank','tng'=>'Touch n Go','duitnow'=>'DuitNow','splitwise'=>'Splitwise','other'=>'Lain-lain'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('payment_method') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Nota (pilihan)</label>
                        <textarea name="description" rows="2" class="nude-input w-full"
                            placeholder="Tujuan hutang, rujukan, dll.">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Due Date & Warning --}}
                @include('hutang.partials._warning_settings')

                {{-- Installment Toggle --}}
                <div class="mb-6">
                    <h3 class="font-semibold text-nude-text border-b border-nude-border pb-2 mb-4">Bayaran Ansuran</h3>

                    <label class="flex items-center gap-2 cursor-pointer mb-4">
                        <input type="checkbox" name="is_installment" value="1"
                            class="w-4 h-4 accent-nude-accent"
                            x-model="isInstallment"
                            @change="recalcInstallments()"
                            {{ old('is_installment') ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-nude-text">Bayar secara ansuran?</span>
                    </label>

                    <div x-show="isInstallment" x-transition class="space-y-4 p-4 bg-nude-bg rounded-lg border border-nude-border">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Bilangan Ansuran</label>
                                <select name="installment_count" class="nude-input w-full"
                                    x-model.number="installmentCount"
                                    @change="recalcInstallments()">
                                    @foreach([1,2,3,4,5,6,8,10,12,24,36] as $n)
                                        <option value="{{ $n }}" @selected(old('installment_count') == $n)>{{ $n }}x</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kekerapan</label>
                                <select name="installment_frequency" class="nude-input w-full"
                                    x-model="installmentFrequency"
                                    @change="recalcInstallments()">
                                    <option value="monthly" @selected(old('installment_frequency','monthly') === 'monthly')>Bulanan</option>
                                    <option value="weekly"  @selected(old('installment_frequency') === 'weekly')>Mingguan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Ansuran Pertama</label>
                                <input type="date" name="first_installment_date"
                                    value="{{ old('first_installment_date', now()->format('Y-m-d')) }}"
                                    class="nude-input w-full"
                                    x-model="firstDate"
                                    @change="recalcInstallments()">
                            </div>
                        </div>

                        {{-- Live Preview --}}
                        <div x-show="schedule.length > 0" x-transition>
                            <p class="text-sm font-medium text-gray-600 mb-2">Preview Jadual Ansuran:</p>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-xs text-gray-400 border-b border-nude-border">
                                            <th class="text-left pb-2">#</th>
                                            <th class="text-left pb-2">Jumlah</th>
                                            <th class="text-left pb-2">Tarikh Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, i) in schedule" :key="i">
                                            <tr class="border-b border-nude-border">
                                                <td class="py-1.5 text-gray-500" x-text="item.number"></td>
                                                <td class="py-1.5 font-medium" x-text="'RM ' + item.amount.toFixed(2)"></td>
                                                <td class="py-1.5 text-gray-500" x-text="item.due_date"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="nude-button">Simpan Hutang</button>
                    <a href="{{ route('hutang.index') }}" class="text-sm text-gray-400 self-center">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function hutangForm() {
        return {
            direction: '{{ old('direction', 'i_owe') }}',
            totalAmount: {{ old('total_amount', 0) }},
            isInstallment: {{ old('is_installment') ? 'true' : 'false' }},
            installmentCount: {{ old('installment_count', 3) }},
            installmentFrequency: '{{ old('installment_frequency', 'monthly') }}',
            firstDate: '{{ old('first_installment_date', now()->format('Y-m-d')) }}',
            schedule: [],

            init() {
                this.recalcInstallments();
            },

            recalcInstallments() {
                if (!this.isInstallment || !this.totalAmount || !this.firstDate) {
                    this.schedule = [];
                    return;
                }

                const count  = parseInt(this.installmentCount) || 1;
                const base   = Math.floor((this.totalAmount / count) * 100) / 100;
                const remainder = Math.round((this.totalAmount - base * count) * 100) / 100;

                this.schedule = [];
                let date = new Date(this.firstDate);

                for (let i = 1; i <= count; i++) {
                    const amount = i === count ? Math.round((base + remainder) * 100) / 100 : base;
                    this.schedule.push({
                        number: i,
                        amount: amount,
                        due_date: date.toLocaleDateString('ms-MY', {day:'2-digit',month:'short',year:'numeric'}),
                    });

                    // Advance date
                    if (this.installmentFrequency === 'weekly') {
                        date.setDate(date.getDate() + 7);
                    } else {
                        // Month-safe addition
                        const d = date.getDate();
                        date.setMonth(date.getMonth() + 1);
                        if (date.getDate() !== d) date.setDate(0); // last day of prev month
                    }
                }
            },
        };
    }
    </script>
</x-app-layout>
