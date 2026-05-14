<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('hutang.index') }}" class="text-sm text-gray-400 hover:text-nude-accent">← Semua Hutang</a>
                <h2 class="font-semibold text-xl text-nude-text mt-1">{{ $debt->contact_name }}</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('hutang.edit', $debt) }}" class="text-sm text-nude-accent border border-nude-accent px-3 py-1.5 rounded-lg hover:bg-nude-secondary">Edit</a>
                <form method="POST" action="{{ route('hutang.destroy', $debt) }}" onsubmit="return confirm('Padam rekod hutang ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-400 border border-red-200 px-3 py-1.5 rounded-lg hover:bg-red-50">Padam</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        {{-- Summary Card --}}
        <div class="nude-card p-6 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-400">Arah</p>
                    <span class="text-sm font-medium px-2 py-1 rounded-full
                        {{ $debt->direction === 'i_owe' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                        {{ $debt->direction === 'i_owe' ? '➡️ Saya hutang' : '⬅️ Dia hutang' }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Status</p>
                    @include('hutang.partials._status_badge', ['status' => $debt->status])
                </div>
                <div>
                    <p class="text-xs text-gray-400">Kaedah</p>
                    <p class="text-sm font-medium">{{ ucfirst($debt->payment_method) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Due Date</p>
                    <p class="text-sm font-medium">
                        @if($debt->due_day_of_month)
                            Setiap {{ $debt->due_day_of_month }}hb
                            @if($debt->next_due_date)
                                <br><span class="text-xs text-gray-400">{{ $debt->next_due_date->format('d M Y') }}</span>
                            @endif
                        @else
                            —
                        @endif
                    </p>
                </div>
            </div>

            {{-- Amount Summary --}}
            <div class="flex items-center gap-6 p-4 bg-nude-bg rounded-lg">
                <div>
                    <p class="text-xs text-gray-400">Jumlah</p>
                    <p class="text-2xl font-bold text-nude-text">RM {{ number_format($debt->total_amount, 2) }}</p>
                </div>
                <div class="text-gray-300 text-2xl">→</div>
                <div>
                    <p class="text-xs text-gray-400">Dibayar</p>
                    <p class="text-2xl font-bold text-green-600">RM {{ number_format($debt->paid_amount, 2) }}</p>
                </div>
                <div class="text-gray-300 text-2xl">=</div>
                <div>
                    <p class="text-xs text-gray-400">Baki</p>
                    <p class="text-2xl font-bold {{ $debt->direction === 'i_owe' ? 'text-red-500' : 'text-green-600' }}">
                        RM {{ number_format($debt->balance, 2) }}
                    </p>
                </div>
            </div>

            {{-- Progress --}}
            <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>Progress</span>
                    <span>{{ $debt->progress_percent }}%</span>
                </div>
                <div class="bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full bg-nude-accent" style="width: {{ $debt->progress_percent }}%"></div>
                </div>
            </div>

            @if($debt->description)
                <p class="mt-3 text-sm text-gray-500 italic">{{ $debt->description }}</p>
            @endif
        </div>

        {{-- Panel Jemputan Contact --}}
        @include('hutang.partials._contact_invite')

        {{-- Installment Table --}}
        @if($debt->is_installment && $debt->installments->isNotEmpty())
            @include('hutang.partials._installment_table')
        @endif

        {{-- Payment Form --}}
        @if($debt->status !== 'settled')
            @include('hutang.partials._payment_form')
        @endif

        {{-- Payment History --}}
        @if($debt->payments->isNotEmpty())
            <div class="nude-card p-5">
                <h3 class="font-semibold text-nude-text mb-4">Sejarah Bayaran</h3>
                <div class="space-y-3">
                    @foreach($debt->payments->sortByDesc('payment_date') as $payment)
                        <div class="flex items-center justify-between p-3 bg-nude-bg rounded-lg">
                            <div>
                                <p class="font-medium text-sm">RM {{ number_format($payment->amount, 2) }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $payment->payment_date->format('d M Y') }} · {{ ucfirst($payment->payment_method) }}
                                    @if($payment->notes) · {{ $payment->notes }} @endif
                                </p>
                            </div>
                            <div class="text-right">
                                @if($payment->proof_path)
                                    <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank"
                                       class="text-nude-accent text-xs underline">
                                        Bukti
                                        @if($payment->proof_source === 'telegram')
                                            📱
                                        @endif
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
