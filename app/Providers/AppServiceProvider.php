<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
// Tambahkan namespace Paginator di bawah ini
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // PERBAIKAN: Memastikan tampilan tombol halaman (pagination) 
        // menggunakan library Tailwind CSS agar rapi
        Paginator::useTailwind();
    }
}