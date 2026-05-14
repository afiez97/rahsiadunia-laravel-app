<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-nude-text leading-tight">
                {{ $sheet->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ $sheet->url }}" target="_blank" class="nude-button-outline text-xs flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Open Original
                </a>
                <a href="{{ route('sheets.index') }}" class="nude-button text-xs">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 h-[calc(100vh-160px)]">
        <div class="max-w-[95%] mx-auto h-full">
            <div class="nude-card h-full overflow-hidden p-0 border-none">
                <iframe src="{{ $embedUrl }}" class="w-full h-full border-none" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</x-app-layout>
