<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">

        <title>{{ config('app.name', 'SIPINTAR') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900|figtree:400,500,600,800&display=swap" rel="stylesheet" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
            /* Custom Scrollbar Tema Amber */
            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: #f7f3ea; }
            ::-webkit-scrollbar-thumb { background: #b45309; border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: #92400e; }
        </style>
    </head>
    <body class="antialiased bg-[#FAF8F5] text-warm-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-white/80 backdrop-blur-md border-b border-amber-900/10">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{-- Menggunakan null coalescing untuk mencegah error 'Undefined variable $slot' --}}
                {{ $slot ?? '' }}
            </main>
        </div>

        <footer class="py-6 text-center text-gray-400 text-xs">
            &copy; {{ date('Y') }} SIPINTAR - Speland Digital Library. All rights reserved.
        </footer>
    </body>
</html>