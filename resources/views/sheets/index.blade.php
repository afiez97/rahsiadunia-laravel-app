<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-nude-text leading-tight">
            {{ __('Google Sheets Integration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Add New Sheet Form -->
                <div class="md:col-span-1">
                    <div class="nude-card p-6 sticky top-8">
                        <h3 class="text-lg font-bold text-nude-accent mb-4">Link New Sheet</h3>
                        <form action="{{ route('sheets.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-nude-text mb-1">Sheet Name</label>
                                <input type="text" name="name" id="name" class="w-full nude-input" placeholder="e.g. Budget 2024" required>
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="mb-6">
                                <label for="url" class="block text-sm font-medium text-nude-text mb-1">Google Sheet URL</label>
                                <input type="url" name="url" id="url" class="w-full nude-input" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                                <p class="text-[10px] text-gray-400 mt-1">Make sure the sheet is shared or accessible.</p>
                                @error('url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="w-full nude-button">
                                Link Sheet
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sheets List -->
                <div class="md:col-span-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse($sheets as $sheet)
                            <div class="nude-card p-4 flex flex-col justify-between hover:shadow-md transition-nude">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                        </div>
                                        <h4 class="font-bold text-nude-text truncate">{{ $sheet->name }}</h4>
                                    </div>
                                    <form action="{{ route('sheets.destroy', $sheet) }}" method="POST" onsubmit="return confirm('Remove this link?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                                <a href="{{ route('sheets.show', $sheet) }}" class="nude-button-outline text-center text-sm">
                                    View Sheet
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full nude-card p-12 text-center">
                                <p class="text-gray-400">No Google Sheets linked yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
