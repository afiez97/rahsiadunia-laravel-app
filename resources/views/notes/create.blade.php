<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-nude-text leading-tight">
            {{ __('Create New Note') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="nude-card p-8">
                <form action="{{ route('notes.store') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-nude-text mb-2">Title</label>
                        <input type="text" name="title" id="title" class="w-full nude-input" placeholder="Note Title" required>
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-8">
                        <label for="content" class="block text-sm font-medium text-nude-text mb-2">Content</label>
                        <textarea name="content" id="content" rows="10" class="w-full nude-input" placeholder="Start typing your secure note..." required></textarea>
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('notes.index') }}" class="text-sm text-gray-500 hover:text-nude-text">Cancel</a>
                        <button type="submit" class="nude-button">
                            Save Note
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-4 flex items-center text-xs text-nude-accent">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                This note will be encrypted before being saved to the database.
            </div>
        </div>
    </div>
</x-app-layout>
