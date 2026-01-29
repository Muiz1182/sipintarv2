<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">

        <title>{{ config('app.name', 'SIPINTAR') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800&display=swap" rel="stylesheet" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        
        <style>
            [x-cloak] { display: none !important; }
            /* Custom Scrollbar Tema Amber */
            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: #f1f1f1; }
            ::-webkit-scrollbar-thumb { background: #92400e; border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: #78350f; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-white border-b border-amber-100 shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{-- Perbaikan: Menggunakan null coalescing operator untuk mencegah error undefined --}}
                {{ $slot ?? '' }}
            </main>
        </div>

        <footer class="py-6 text-center text-gray-400 text-xs">
            &copy; {{ date('Y') }} SIPINTAR - Speland Digital Library. All rights reserved.
        </footer>
    </body>
</html>