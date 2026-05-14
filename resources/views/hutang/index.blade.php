<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-nude-text leading-tight">Hutang Tracker</h2>
            <a href="{{ route('hutang.create') }}" class="nude-button text-sm">+ Tambah Hutang</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <div class="nude-card p-6">
                <p class="text-sm text-gray-400 mb-1">Saya Berhutang</p>
                <p class="text-3xl font-bold text-red-500">RM {{ number_format($totalIOwe, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Baki yang perlu dibayar</p>
            </div>
            <div class="nude-card p-6">
                <p class="text-sm text-gray-400 mb-1">Orang Lain Berhutang</p>
                <p class="text-3xl font-bold text-green-600">RM {{ number_format($totalTheyOwe, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Baki yang perlu diterima</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('hutang.index') }}" class="nude-card p-4 mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                class="nude-input text-sm flex-1 min-w-[150px]">

            <select name="direction" class="nude-input text-sm">
                <option value="">Semua Arah</option>
                <option value="i_owe"    @selected(request('direction') === 'i_owe')>Saya Hutang</option>
                <option value="they_owe" @selected(request('direction') === 'they_owe')>Dia Hutang</option>
            </select>

            <select name="status" class="nude-input text-sm">
                <option value="">Semua Status</option>
                <option value="pending"  @selected(request('status') === 'pending')>Belum Bayar</option>
                <option value="partial"  @selected(request('status') === 'partial')>Bayar Sebahagian</option>
                <option value="settled"  @selected(request('status') === 'settled')>Selesai</option>
            </select>

            <button type="submit" class="nude-button text-sm px-4 py-2">Tapis</button>
            <a href="{{ route('hutang.index') }}" class="text-sm text-gray-400 self-center ml-1">Reset</a>
        </form>

        {{-- Debt List --}}
        @if($debts->isEmpty())
            <div class="nude-card p-12 text-center text-gray-400">
                <p class="text-4xl mb-3">💰</p>
                <p class="font-medium">Tiada rekod hutang. Tambah yang pertama!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($debts as $debt)
                    <a href="{{ route('hutang.show', $debt) }}" class="nude-card p-5 block hover:shadow-md transition-nude">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-bold text-nude-text text-lg">{{ $debt->contact_name }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                    {{ $debt->direction === 'i_owe' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $debt->direction === 'i_owe' ? '➡️ Saya hutang' : '⬅️ Dia hutang' }}
                                </span>
                            </div>
                            <div class="text-right">
                                @include('hutang.partials._status_badge', ['status' => $debt->status])
                                @if($debt->is_installment)
                                    @php
                                        $paidCount = $debt->installments->where('status','paid')->count();
                                        $totalCount = $debt->installments->count();
                                    @endphp
                                    <span class="block text-xs text-gray-400 mt-1">{{ $paidCount }}/{{ $totalCount }} ansuran</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Baki</p>
                                <p class="text-2xl font-bold {{ $debt->direction === 'i_owe' ? 'text-red-500' : 'text-green-600' }}">
                                    RM {{ number_format($debt->balance, 2) }}
                                </p>
                                <p class="text-xs text-gray-400">dari RM {{ number_format($debt->total_amount, 2) }}</p>
                            </div>
                            <div class="text-right text-xs text-gray-400">
                                <p>{{ ucfirst($debt->payment_method) }}</p>
                                @if($debt->next_due_date)
                                    <p class="mt-1">Due: {{ $debt->next_due_date->format('d M') }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="mt-3 bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-nude-accent" style="width: {{ $debt->progress_percent }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
