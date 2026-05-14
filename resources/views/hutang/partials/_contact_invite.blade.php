{{-- Panel Jemputan Contact ke Telegram Bot --}}
<div class="nude-card p-5 mb-6" x-data="{ showLink: false }">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <h3 class="font-semibold text-nude-text">Jemputan Contact</h3>
            @if($debt->contact_linked)
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">
                    ✅ Telegram Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">
                    ⏳ Belum Join Bot
                </span>
            @endif
        </div>
    </div>

    {{-- Maklumat contact --}}
    <div class="flex items-center gap-3 mb-4 p-3 bg-nude-bg rounded-lg">
        <div class="w-10 h-10 rounded-full bg-nude-secondary flex items-center justify-center text-nude-accent font-bold text-lg">
            {{ strtoupper(substr($debt->contact_name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <p class="font-medium text-nude-text">{{ $debt->contact_name }}</p>
            @if($debt->contact_phone)
                <p class="text-sm text-gray-400">📱 {{ $debt->contact_phone }}</p>
            @else
                <p class="text-sm text-gray-300 italic">Tiada nombor telefon</p>
            @endif
        </div>
        @if($debt->contact_linked)
            <div class="text-right text-xs text-gray-400">
                <p class="text-green-600 font-medium">📱 Telegram linked</p>
                <p>{{ $debt->contact_linked_at?->format('d M Y') }}</p>
            </div>
        @endif
    </div>

    @if($debt->contact_linked)
        {{-- Contact dah join — tunjuk pilihan --}}
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="showLink = !showLink"
                class="text-sm text-nude-accent border border-nude-border px-3 py-1.5 rounded-lg hover:bg-nude-secondary">
                Lihat Link Jemputan
            </button>

            <form method="POST" action="{{ route('hutang.contact.unlink', $debt) }}"
                  onsubmit="return confirm('Nyahpaut Telegram contact ini?')">
                @csrf
                <button type="submit"
                    class="text-sm text-red-400 border border-red-200 px-3 py-1.5 rounded-lg hover:bg-red-50">
                    Nyahpaut Telegram
                </button>
            </form>

            <form method="POST" action="{{ route('hutang.invite.regenerate', $debt) }}"
                  onsubmit="return confirm('Jana link baru? Link lama akan tidak sah.')">
                @csrf
                <button type="submit"
                    class="text-sm text-gray-400 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50">
                    Jana Link Baru
                </button>
            </form>
        </div>

        <div x-show="showLink" x-transition class="mt-3 p-3 bg-nude-bg rounded-lg border border-nude-border">
            @if($debt->invite_link)
                <p class="text-xs text-gray-400 mb-1">Link jemputan:</p>
                <div class="flex items-center gap-2">
                    <code class="text-xs bg-white border border-nude-border rounded px-2 py-1 flex-1 truncate">
                        {{ $debt->invite_link }}
                    </code>
                    <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $debt->invite_link }}'); this.textContent='✓ Disalin!'; setTimeout(()=>this.textContent='Salin',2000)"
                        class="text-xs text-nude-accent border border-nude-border px-2 py-1 rounded whitespace-nowrap">
                        Salin
                    </button>
                </div>
            @endif
        </div>

    @else
        {{-- Contact belum join — tunjuk pilihan hantar jemputan --}}

        @if($debt->invite_link)
            <p class="text-sm text-gray-500 mb-3">
                Hantar link ini kepada <strong>{{ $debt->contact_name }}</strong> supaya mereka boleh join bot dan terima peringatan bayaran.
            </p>

            {{-- Copy link --}}
            <div class="p-3 bg-nude-bg rounded-lg border border-nude-border mb-3">
                <p class="text-xs text-gray-400 mb-1">Link jemputan Telegram:</p>
                <div class="flex items-center gap-2">
                    <code class="text-xs bg-white border border-nude-border rounded px-2 py-1 flex-1 truncate">
                        {{ $debt->invite_link }}
                    </code>
                    <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $debt->invite_link }}'); this.textContent='✓ Disalin!'; setTimeout(()=>this.textContent='Salin',2000)"
                        class="text-xs text-nude-accent border border-nude-border px-2 py-1 rounded whitespace-nowrap">
                        Salin
                    </button>
                </div>
            </div>

            {{-- Butang share --}}
            <div class="flex flex-wrap gap-2">
                {{-- WhatsApp --}}
                @if($debt->whatsapp_invite_link)
                    <a href="{{ $debt->whatsapp_invite_link }}" target="_blank"
                       class="inline-flex items-center gap-2 text-sm px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Hantar via WhatsApp
                    </a>
                @elseif($debt->contact_phone)
                    {{-- Ada phone tapi whatsapp link tak dapat generate (phone format issue) --}}
                    <a href="https://wa.me/?text={{ urlencode('Klik link ini untuk join bot hutang tracker: ' . $debt->invite_link) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 text-sm px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition">
                        WhatsApp
                    </a>
                @endif

                {{-- Telegram share --}}
                <a href="https://t.me/share/url?url={{ urlencode($debt->invite_link) }}&text={{ urlencode('Jemputan bot Hutang Tracker') }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 text-sm px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                    Hantar via Telegram
                </a>

                {{-- Jana semula link --}}
                <form method="POST" action="{{ route('hutang.invite.regenerate', $debt) }}"
                      onsubmit="return confirm('Jana link baru? Link lama akan tidak sah.')">
                    @csrf
                    <button type="submit"
                        class="text-sm text-gray-400 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50">
                        Jana Link Baru
                    </button>
                </form>
            </div>

            <p class="text-xs text-gray-400 mt-3">
                💡 Contact hanya perlu klik link sekali. Selepas itu mereka akan terima semua peringatan bayaran secara automatik.
            </p>

        @else
            <p class="text-sm text-yellow-600">Invite token tidak dijumpai. Cuba muat semula halaman.</p>
        @endif
    @endif
</div>
