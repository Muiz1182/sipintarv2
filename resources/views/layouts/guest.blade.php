<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login Petugas - {{ config('app.name', 'SIPINTAR') }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800,900&display=swap" rel="stylesheet" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            body {
                /* Background halus dengan nuansa hangat perpustakaan */
                background-color: #fdfaf6;
                background-image: radial-gradient(#e5e7eb 0.5px, transparent 0.5px);
                background-size: 24px 24px;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased selection:bg-amber-200 selection:text-amber-900">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            
            <div class="mb-2 transform transition hover:scale-105 duration-300">
                <a href="/">
                    <img src="{{ asset('img/SIPINTAR TRANS.png') }}" alt="Logo SIPINTAR" class="w-28 h-28 drop-shadow-2xl">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-4 px-8 py-10 bg-white/80 backdrop-blur-md shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-white overflow-hidden sm:rounded-3xl relative">
                
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 via-amber-700 to-amber-900"></div>

                {{ $slot }}
            </div>

            <div class="mt-8 text-center">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em]">
                    &copy; 2026 SIPINTAR - SMP Negeri 9 Probolinggo
                </p>
            </div>
        </div>
    </body>
</html>