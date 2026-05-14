<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('hutang.show', $debt) }}" class="text-sm text-gray-400 hover:text-nude-accent">←</a>
            <h2 class="font-semibold text-xl text-nude-text">Edit Hutang — {{ $debt->contact_name }}</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6">
        <div class="nude-card p-6"
             x-data="editHutangForm()"
             x-init="init()">

            <form method="POST" action="{{ route('hutang.update', $debt) }}">
                @csrf @method('PUT')

                <div class="space-y-4 mb-6">
                    <h3 class="font-semibold text-nude-text border-b border-nude-border pb-2">Maklumat Hutang</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Orang</label>
                            <input type="text" name="contact_name" value="{{ old('contact_name', $debt->contact_name) }}"
                                class="nude-input w-full" required>
                            @error('contact_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Arah Hutang</label>
                            <select name="direction" class="nude-input w-full" required>
                                <option value="i_owe"    @selected(old('direction', $debt->direction) === 'i_owe')>➡️ Saya berhutang</option>
                                <option value="they_owe" @selected(old('direction', $debt->direction) === 'they_owe')>⬅️ Dia berhutang</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Jumlah (RM)</label>
                            <input type="number" name="total_amount"
                                value="{{ old('total_amount', $debt->total_amount) }}"
                                step="0.01" min="0.01" class="nude-input w-full" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Kaedah Bayaran</label>
                            <select name="payment_method" class="nude-input w-full" required>
                                @foreach(['cash'=>'Tunai','maybank'=>'Maybank','tng'=>'Touch n Go','duitnow'=>'DuitNow','splitwise'=>'Splitwise','other'=>'Lain-lain'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('payment_method', $debt->payment_method) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Nota</label>
                        <textarea name="description" rows="2" class="nude-input w-full">{{ old('description', $debt->description) }}</textarea>
                    </div>
                </div>

                @include('hutang.partials._warning_settings')

                <div class="flex gap-3">
                    <button type="submit" class="nude-button">Kemaskini</button>
                    <a href="{{ route('hutang.show', $debt) }}" class="text-sm text-gray-400 self-center">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function editHutangForm() {
        return {
            dueDayOfMonth: {{ $debt->due_day_of_month ?? 0 }},
            nextDuePreview: '',
            init() { this.calcNextDue(); },
            calcNextDue: warningSettingsMixin().calcNextDue,
        };
    }
    </script>
</x-app-layout>
