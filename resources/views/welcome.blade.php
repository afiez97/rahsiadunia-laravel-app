<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>rahsiadunia - Secure Vault</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

        <!-- Tailwind & Nude Theme -->
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            :root {
                --nude-bg: #fdfaf5;
                --nude-card: #ffffff;
                --nude-primary: #d9c5b2;
                --nude-secondary: #e6dbd0;
                --nude-accent: #c4a484;
                --nude-text: #4a4238;
                --nude-border: #eee5dc;
            }
            body { background-color: var(--nude-bg); color: var(--nude-text); font-family: 'Outfit', sans-serif; }
            .circular-logo-large { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--nude-secondary); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
            .nude-button { background: var(--nude-accent); color: #fff; padding: 0.75rem 2rem; border-radius: 9999px; font-weight: 600; transition: all 0.3s; }
            .nude-button:hover { transform: translateY(-3px); background: var(--nude-text); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body class="antialiased">
        <div class="relative flex items-top justify-center min-h-screen sm:items-center py-4 sm:pt-0">
            @if (Route::has('login'))
                <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-bold text-nude-accent underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-nude-text font-semibold">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 text-sm text-nude-text font-semibold">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-center">
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('images/logo.jpg') }}" class="circular-logo-large" alt="rahsiadunia logo">
                </div>

                <h1 class="text-5xl font-black text-nude-text mb-4 tracking-tighter">rahsiadunia</h1>
                <p class="text-lg text-nude-accent mb-12 font-medium">Your personal, highly-encrypted secure vault for notes and credentials.</p>

                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="nude-button">Access My Vault</a>
                    @else
                        <a href="{{ route('register') }}" class="nude-button">Create Private Account</a>
                        <a href="{{ route('login') }}" class="px-8 py-3 rounded-full border-2 border-nude-secondary text-nude-text font-bold hover:bg-nude-secondary transition">Sign In</a>
                    @endauth
                </div>

                <div class="mt-16 text-xs text-nude-primary uppercase tracking-widest font-bold">
                    Protected by AES-256-GCM Encryption
                </div>
            </div>
        </div>
    </body>
</html>
