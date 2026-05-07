<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-nude-text leading-tight">
                {{ __('Account Vault') }}
            </h2>
            <a href="{{ route('accounts.create') }}" class="nude-button text-sm">
                + Add Account
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="nude-card overflow-hidden">
                <table class="min-w-full divide-y divide-nude-border">
                    <thead class="bg-nude-secondary">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-nude-text uppercase tracking-wider">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-nude-text uppercase tracking-wider">Email/Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-nude-text uppercase tracking-wider">Password</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-nude-text uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-nude-border">
                        @forelse($accounts as $account)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-nude-accent">{{ $account->service }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">{{ $account->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <code class="text-xs bg-gray-50 px-2 py-1 rounded text-gray-400">••••••••</code>
                                        <button onclick="copyToClipboard('{{ $account->password }}')" class="text-nude-primary hover:text-nude-text">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('accounts.edit', $account) }}" class="text-nude-primary hover:text-nude-text">Edit</a>
                                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Remove this account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-300 hover:text-red-500">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    Your vault is empty.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Password copied to clipboard!');
            });
        }
    </script>
</x-app-layout>
