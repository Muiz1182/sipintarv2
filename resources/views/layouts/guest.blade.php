<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login Petugas - {{ config('app.name', 'SIPINTAR') }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            [x-cloak] { display: none !important; }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #1a0e07;
                background-image: 
                    radial-gradient(circle at 15% 15%, rgba(180, 83, 9, 0.22) 0%, transparent 45%),
                    radial-gradient(circle at 85% 85%, rgba(120, 53, 15, 0.28) 0%, transparent 55%),
                    url('https://www.transparenttextures.com/patterns/wood-pattern.png');
            }
        </style>
    </head>
    <body class="text-amber-950 antialiased selection:bg-amber-500 selection:text-white min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8">
        {{ $slot }}
    </body>
</html>
