<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-nude-text leading-tight">
                {{ __('My Secure Notes') }}
            </h2>
            <a href="{{ route('notes.create') }}" class="nude-button text-sm">
                + New Note
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex justify-end space-x-2">
                <a href="{{ route('notes.index', ['view' => 'list']) }}" class="px-4 py-2 rounded-lg text-sm transition-nude {{ $viewType === 'list' ? 'bg-nude-accent text-white' : 'bg-white text-nude-text hover:bg-nude-primary/10' }}">
                    List All
                </a>
                <a href="{{ route('notes.index', ['view' => 'grouped']) }}" class="px-4 py-2 rounded-lg text-sm transition-nude {{ $viewType === 'grouped' ? 'bg-nude-accent text-white' : 'bg-white text-nude-text hover:bg-nude-primary/10' }}">
                    Group by Label
                </a>
            </div>

            @if($viewType === 'list')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($notes as $note)
                        <div class="nude-card p-6 transition-nude hover:shadow-lg flex flex-col h-full">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-lg text-nude-accent truncate">{{ $note->title }}</h3>
                                    @if($note->label)
                                        <span class="text-[10px] uppercase tracking-wider bg-nude-primary/20 text-nude-accent px-2 py-0.5 rounded-full">{{ $note->label }}</span>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('notes.edit', $note) }}" class="text-nude-primary hover:text-nude-text">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Delete this note?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-300 hover:text-red-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 line-clamp-3 mb-4 flex-grow">
                                {{ $note->content }}
                            </p>
                            <div class="text-xs text-gray-400 mt-auto pt-4 border-t border-nude-primary/10">
                                Last updated {{ $note->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <div class="text-nude-primary mb-4">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-medium text-nude-text">No notes yet</h3>
                            <p class="text-gray-400">Capture your first secure note today.</p>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="space-y-12">
                    @forelse($notes as $label => $group)
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-nude-accent flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                {{ $label }} ({{ count($group) }})
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($group as $note)
                                    <div class="nude-card p-6 transition-nude hover:shadow-lg flex flex-col h-full border-l-4 border-nude-accent">
                                        <div class="flex justify-between items-start mb-4">
                                            <h3 class="font-bold text-lg text-nude-accent truncate">{{ $note->title }}</h3>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('notes.edit', $note) }}" class="text-nude-primary hover:text-nude-text">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                                <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Delete this note?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-300 hover:text-red-500">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500 line-clamp-3 mb-4 flex-grow">
                                            {{ $note->content }}
                                        </p>
                                        <div class="text-xs text-gray-400 mt-auto pt-4 border-t border-nude-primary/10">
                                            Last updated {{ $note->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-400">No notes found.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
