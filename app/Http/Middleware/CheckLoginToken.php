<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginToken
{
    /**
     * Menangani permintaan yang masuk.
     * Memastikan URL login hanya bisa diakses dengan token rahasia.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Tentukan token rahasia Anda di sini
        $secretToken = 'speland-2026-aman';

        // 2. Ambil parameter 'token' dari route
        $providedToken = $request->route('token');

        // 3. Validasi: Jika token tidak ada atau tidak cocok
        if (!$providedToken || $providedToken !== $secretToken) {
            // Log aktivitas mencurigakan (opsional)
            // \Illuminate\Support\Facades\Log::warning('Akses login ilegal dari IP: ' . $request->ip());

            // Tendang pengunjung ke halaman home dengan pesan error (opsional)
            return redirect('/')->with('error', 'Akses ditolak. Area terbatas.');
        }

        return $next($request);
    }
}