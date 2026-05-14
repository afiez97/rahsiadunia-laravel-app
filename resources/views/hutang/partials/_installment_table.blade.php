@php
    $paidCount  = $debt->installments->where('status', 'paid')->count();
    $totalCount = $debt->installments->count();
@endphp

<div class="nude-card p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-nude-text">Jadual Ansuran</h3>
        <span class="text-sm font-medium px-3 py-1 bg-nude-secondary rounded-full">
            {{ $paidCount }}/{{ $totalCount }} ansuran
        </span>
    </div>

    {{-- Progress Bar --}}
    <div class="mb-4 bg-gray-100 rounded-full h-2">
        @php $pct = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0; @endphp
        <div class="h-2 rounded-full bg-nude-accent transition-all" style="width: {{ $pct }}%"></div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 border-b border-nude-border">
                    <th class="text-left pb-2 pr-3">#</th>
                    <th class="text-left pb-2 pr-3">Jumlah</th>
                    <th class="text-left pb-2 pr-3">Due Date</th>
                    <th class="text-left pb-2 pr-3">Status</th>
                    <th class="text-left pb-2">Bukti</th>
                    <th class="text-right pb-2">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($debt->installments as $ins)
                    <tr class="border-b border-nude-border last:border-0">
                        <td class="py-2 pr-3 text-gray-400">{{ $ins->installment_number }}</td>
                        <td class="py-2 pr-3 font-medium">RM {{ number_format($ins->amount, 2) }}</td>
                        <td class="py-2 pr-3 text-gray-500">
                            {{ $ins->due_date->format('d M Y') }}
                            @if($ins->isOverdue())
                                <span class="ml-1 text-xs text-red-500 font-medium">Overdue!</span>
                            @endif
                        </td>
                        <td class="py-2 pr-3">
                            @include('hutang.partials._status_badge', ['status' => $ins->status])
                            @if($ins->isSnoozedToday())
                                <span class="text-xs text-gray-400 ml-1">⏰ snooze</span>
                            @endif
                        </td>
                        <td class="py-2 pr-3">
                            @if($ins->proof_path)
                                <a href="{{ asset('storage/' . $ins->proof_path) }}" target="_blank"
                                   class="text-nude-accent underline text-xs">
                                    Lihat
                                    @if($ins->proof_source === 'telegram')
                                        <span class="ml-0.5 text-blue-400">📱</span>
                                    @endif
                                </a>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            @if($ins->status !== 'paid')
                                <form method="POST"
                                      action="{{ route('hutang.installment.pay', [$debt, $ins]) }}"
                                      enctype="multipart/form-data"
                                      x-data="{ open: false }">
                                    @csrf
                                    <button type="button" @click="open = !open"
                                        class="text-xs text-nude-accent underline">
                                        Tandakan Bayar
                                    </button>
                                    <div x-show="open" x-transition class="mt-2 p-3 bg-nude-bg rounded-lg border border-nude-border text-left">
                                        <input type="file" name="proof" accept="image/*,.pdf"
                                            class="nude-input text-xs w-full mb-2">
                                        <textarea name="notes" rows="1" placeholder="Nota bayaran (pilihan)"
                                            class="nude-input text-xs w-full mb-2"></textarea>
                                        <button type="submit" class="nude-button text-xs px-3 py-1.5">Simpan</button>
                                    </div>
                                </form>
                            @else
                                <span class="text-green-500 text-xs">✅ {{ $ins->paid_at?->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
