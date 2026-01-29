<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Cek apakah pengguna sudah login
        if (Auth::check()) {
            $user = Auth::user();

            /**
             * 2. OPTIMASI: Hanya update jika aktivitas terakhir lebih dari 5 menit yang lalu.
             * Ini mencegah database melakukan query 'update' setiap kali halaman di-refresh.
             */
            if (empty($user->last_seen) || Carbon::parse($user->last_seen)->diffInMinutes(now()) >= 5) {
                
                // Gunakan update quiet agar tidak memicu event 'updated' atau mengubah 'updated_at' jika tidak diinginkan
                $user->timestamps = false; // Matikan timestamps jika hanya ingin mengubah last_seen
                $user->last_seen = now();
                $user->save();
            }
        }

        return $next($request);
    }
}