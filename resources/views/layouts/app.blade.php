<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

        <!-- Tailwind & Nude Theme Fallback -->
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
            .bg-nude-bg { background-color: var(--nude-bg); }
            .bg-nude-primary { background-color: var(--nude-primary); }
            .bg-nude-secondary { background-color: var(--nude-secondary); }
            .text-nude-primary { color: var(--nude-primary); }
            .text-nude-text { color: var(--nude-text); }
            .border-nude-border { border-color: var(--nude-border); }
            .nude-card { background: var(--nude-card); border: 1px solid var(--nude-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 1rem; }
            .nude-input { background: #fff; border: 1px solid var(--nude-border); border-radius: 0.5rem; padding: 0.75rem 1rem; outline: none; transition: border-color 0.2s; }
            .nude-input:focus { border-color: var(--nude-accent); }
            .nude-button { background: var(--nude-accent); color: #fff; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; transition: transform 0.2s, background 0.2s; }
            .nude-button:hover { transform: translateY(-2px); background: var(--nude-text); }
            .transition-nude { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
            .circular-logo { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--nude-primary); }
            
            /* Mobile Bottom Nav */
            @media (max-width: 640px) {
                .mobile-bottom-nav {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: #fff;
                    display: flex;
                    justify-content: space-around;
                    padding: 0.75rem;
                    border-top: 1px solid var(--nude-border);
                    z-index: 50;
                }
                .main-content { padding-bottom: 80px; }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-nude-bg text-nude-text">
        <div class="min-h-screen main-content">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
