<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-nude-text leading-tight">
            {{ __('Add New Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="nude-card p-8">
                <form action="{{ route('accounts.store') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="service" class="block text-sm font-medium text-nude-text mb-2">Service / Website</label>
                        <input type="text" name="service" id="service" class="w-full nude-input" placeholder="e.g. Google, GitHub, Bank" required>
                        @error('service') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="email" class="block text-sm font-medium text-nude-text mb-2">Email / Username</label>
                            <input type="text" name="email" id="email" class="w-full nude-input" placeholder="user@example.com" required>
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-nude-text mb-2">Password</label>
                            <input type="password" name="password" id="password" class="w-full nude-input" placeholder="••••••••" required>
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('accounts.index') }}" class="text-sm text-gray-500 hover:text-nude-text">Cancel</a>
                        <button type="submit" class="nude-button">
                            Securely Save
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-4 flex items-center text-xs text-nude-accent">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L10 .303l7.834 4.597C18.15 5.163 18.5 5.56 18.5 6v7.712c0 1.226-.41 2.42-1.168 3.387l-.025.031c-.347.433-.767.804-1.246 1.104l-5.632 3.52a1 1 0 01-1.058 0l-5.632-3.52A4.945 4.945 0 012.668 17.1l-.025-.031A4.274 4.274 0 011.5 13.712V6c0-.44.35-.837.834-1.1zM10 11.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd"></path></svg>
                Encryption active. Your credentials are safe.
            </div>
        </div>
    </div>
</x-app-layout>
