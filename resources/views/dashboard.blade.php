<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-nude-text leading-tight">
            {{ __('Welcome, ') }} {{ Auth::user()->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Notes Summary -->
                <div class="nude-card p-8 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-nude-secondary rounded-full flex items-center justify-center mb-4 text-nude-accent">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-nude-text mb-2">Secure Notes</h3>
                    <p class="text-gray-500 mb-6">You have {{ Auth::user()->notes()->count() }} encrypted notes saved.</p>
                    <a href="{{ route('notes.index') }}" class="nude-button w-full">View Notes</a>
                </div>

                <!-- Vault Summary -->
                <div class="nude-card p-8 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-nude-secondary rounded-full flex items-center justify-center mb-4 text-nude-accent">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-nude-text mb-2">Account Vault</h3>
                    <p class="text-gray-500 mb-6">You have {{ Auth::user()->accounts()->count() }} sets of credentials secured.</p>
                    <a href="{{ route('accounts.index') }}" class="nude-button w-full">Access Vault</a>
                </div>
            </div>

            <!-- Hutang Summary -->
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
                <div class="nude-card p-8 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-nude-text mb-2">Hutang Tracker</h3>
                    <p class="text-gray-500 mb-6">Track hutang keluar & masuk dengan mudah.</p>
                    <a href="{{ route('hutang.index') }}" class="nude-button w-full">Buka Hutang</a>
                </div>
            </div>

            <div class="mt-12 nude-card p-8 bg-nude-secondary border-none">
                <div class="flex items-center">
                    <div class="p-3 bg-white rounded-lg mr-4">
                        <svg class="w-6 h-6 text-nude-accent" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L10 .303l7.834 4.597C18.15 5.163 18.5 5.56 18.5 6v7.712c0 1.226-.41 2.42-1.168 3.387l-.025.031c-.347.433-.767.804-1.246 1.104l-5.632 3.52a1 1 0 01-1.058 0l-5.632-3.52A4.945 4.945 0 012.668 17.1l-.025-.031A4.274 4.274 0 011.5 13.712V6c0-.44.35-.837.834-1.1zM10 11.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-nude-text">Security Tip</h4>
                        <p class="text-sm text-nude-text opacity-75">Your data is encrypted using AES-256-GCM. Never share your master password with anyone.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
