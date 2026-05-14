@php
    $config = match($status) {
        'settled' => ['bg-green-100 text-green-700', 'Selesai'],
        'partial'  => ['bg-yellow-100 text-yellow-700', 'Sebahagian'],
        'overdue'  => ['bg-red-100 text-red-700', 'Overdue'],
        default    => ['bg-gray-100 text-gray-600', 'Belum Bayar'],
    };
@endphp
<span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $config[0] }}">{{ $config[1] }}</span>
