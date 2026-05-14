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
            .nude-card { background: var(--nude-card); border: 1px solid var(--nude-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 1rem; }
            .nude-input { background: #fff; border: 1px solid var(--nude-border); border-radius: 0.5rem; padding: 0.75rem 1rem; outline: none; transition: border-color 0.2s; }
            .nude-button { background: var(--nude-accent); color: #fff; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; transition: transform 0.2s, background 0.2s; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-nude-bg text-nude-text">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
