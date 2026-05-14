<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-nude-text leading-tight">
            {{ __('Edit Account Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="nude-card p-8">
                <form action="{{ route('accounts.update', $account) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-6">
                        <label for="service" class="block text-sm font-medium text-nude-text mb-2">Service / Website</label>
                        <input type="text" name="service" id="service" class="w-full nude-input" value="{{ $account->service }}" required>
                        @error('service') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="email" class="block text-sm font-medium text-nude-text mb-2">Email / Username</label>
                            <input type="text" name="email" id="email" class="w-full nude-input" value="{{ $account->email }}" required>
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-nude-text mb-2">Password</label>
                            <input type="password" name="password" id="password" class="w-full nude-input" value="{{ $account->password }}" required>
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('accounts.index') }}" class="text-sm text-gray-500 hover:text-nude-text">Cancel</a>
                        <button type="submit" class="nude-button">
                            Update Credentials
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
