<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman login (Blade).
     */
    public function create(Request $request, $token = null): View
    {
        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'token' => $token, 
        ]);
    }

    /**
     * Proses Login Petugas.
     */
    public function store(LoginRequest $request, $token = null): RedirectResponse
    {
        // 1. Jalankan proses autentikasi (Rate Limiting & Credential Check)
        $request->authenticate();

        // 2. Regenerasi sesi untuk mencegah Session Fixation attack
        $request->session()->regenerate();

        // 3. Update status 'last_seen' secara efisien
        // Menggunakan User Model langsung setelah Auth::user() untuk akurasi data
        $user = Auth::user();
        if ($user) {
            $user->update([
                'last_seen' => now()
            ]);
        }

        // 4. Redirect ke Dashboard atau halaman yang dituju sebelumnya
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Proses Logout Petugas & Set Offline.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. Paksa status menjadi offline (mundur 6 menit) sebelum logout permanen
        if ($user) {
            $user->update([
                'last_seen' => now()->subMinutes(6)
            ]);
        }

        // 2. Clear autentikasi guard
        Auth::guard('web')->logout();

        // 3. Invalidate sesi & regenerasi CSRF token untuk keamanan total
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 4. Kembali ke Landing Page SIPINTAR
        return redirect('/');
    }
}