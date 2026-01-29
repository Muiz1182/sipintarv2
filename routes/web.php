<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIPINTAR (SPELAND DIGITAL LIBRARY)
|--------------------------------------------------------------------------
*/

/**
 * 1. AKSES PENGUNJUNG (PUBLIC)
 */
Route::get('/', [BookController::class, 'index'])->name('home');

// Route membaca buku menggunakan HashID
Route::get('/read/{hashid}', [BookController::class, 'read'])->name('books.read');

/**
 * 2. AKSES TERPROTEKSI (AUTH & VERIFIED)
 */
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Utama Admin/Petugas
    Route::get('/dashboard', [BookController::class, 'adminIndex'])->name('dashboard');

    /**
     * Manajemen Koleksi & Petugas (Prefix: admin)
     */
    Route::prefix('admin')->as('admin.')->group(function () {
        
        // Kontrol Buku & Kategori
        Route::controller(BookController::class)->group(function () {
            // Operasi Create
            Route::post('/books', 'store')->name('books.store');
            Route::post('/category', 'storeCategory')->name('category.store'); 
            
            // Operasi Delete 
            // Urutan Penting: Truncate harus di atas route dengan parameter {book} 
            // agar kata 'truncate' tidak dianggap sebagai ID buku.
            Route::delete('/books/truncate', 'truncate')->name('books.truncate');
            Route::delete('/books/{book}', 'destroy')->name('books.destroy');
        });

        /**
         * Kontrol Akun Petugas
         * Hanya dapat diakses secara fungsional jika email adalah adminsipintar@gmail.com
         */
        Route::controller(RegisteredUserController::class)->group(function () {
            Route::post('/petugas', 'store')->name('petugas.store');
            Route::delete('/petugas/{user}', 'destroyPetugas')->name('petugas.destroy');
            Route::patch('/petugas/{user}/password', 'updatePetugasPassword')->name('petugas.password.update');
        });
    });

    /**
     * Fitur Chat Real-time
     */
    Route::prefix('chat')->as('chat.')->controller(ChatController::class)->group(function () {
        Route::get('/unread-count', 'getUnreadCount')->name('unread'); 
        Route::get('/messages/{receiverId}', 'getMessages')->name('get');
        Route::post('/send', 'sendMessage')->name('send');
    });

    /**
     * Profil Pengguna
     */
    Route::prefix('profile')->as('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });
});

require __DIR__.'/auth.php';