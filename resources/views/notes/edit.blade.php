<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-nude-text leading-tight">
            {{ __('Edit Note') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="nude-card p-8">
                <form action="{{ route('notes.update', $note) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-nude-text mb-2">Title</label>
                        <input type="text" name="title" id="title" class="w-full nude-input" value="{{ $note->title }}" required>
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label for="label" class="block text-sm font-medium text-nude-text mb-2">Label (Folder/Group)</label>
                        <input type="text" name="label" id="label" class="w-full nude-input" value="{{ $note->label }}" placeholder="e.g. Work, Personal, Finance">
                        @error('label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-8">
                        <label for="content" class="block text-sm font-medium text-nude-text mb-2">Content</label>
                        <textarea name="content" id="content" rows="10" class="w-full nude-input" required>{{ $note->content }}</textarea>
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('notes.index') }}" class="text-sm text-gray-500 hover:text-nude-text">Cancel</a>
                        <button type="submit" class="nude-button">
                            Update Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
