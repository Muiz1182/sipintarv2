<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Book;
use Illuminate\Foundation\Console\ClosureCommand;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| File ini mendefinisikan perintah artisan berbasis closure. Perintah ini
| sangat berguna untuk tugas-tugas administratif cepat melalui terminal.
|
*/

// Perintah bawaan: Menampilkan kutipan inspiratif
Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Menampilkan kutipan inspiratif');

/**
 * Perintah Khusus SIPINTAR: Statistik Perpustakaan
 * Cara pakai: php artisan sipintar:stats
 */
Artisan::command('sipintar:stats', function () {
    /** @var ClosureCommand $this */
    $totalBooks = Book::count();
    $totalViews = Book::sum('views');
    
    $this->info("=== STATISTIK SIPINTAR ===");
    $this->line("Total Koleksi Buku : <fg=yellow>{$totalBooks}</>");
    $this->line("Total Buku Dibaca  : <fg=cyan>{$totalViews}</> kali");
    $this->info("==========================");
})->purpose('Menampilkan statistik singkat perpustakaan digital');

/**
 * Perintah Khusus: Bersihkan Cache & Optimasi
 * Cara pakai: php artisan sipintar:clean
 */
Artisan::command('sipintar:clean', function () {
    /** @var ClosureCommand $this */
    if ($this->confirm('Apakah Anda ingin membersihkan cache dan mengoptimalkan aplikasi?', true)) {
        $this->warn('Sedang membersihkan...');

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        $this->info('Cache SIPINTAR berhasil dibersihkan dan sistem telah disegarkan!');
    }
})->purpose('Membersihkan cache aplikasi untuk optimasi performa');