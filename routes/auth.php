<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    /** * 1. HALAMAN LOGIN (HIDDEN URL + TOKEN)
     * Menggunakan {token?} opsional agar route('login') bisa dipanggil 
     * dari halaman mana pun tanpa menyebabkan UrlGenerationException.
     */
    Route::get('masuk-perpustakaan-speland/{token?}', [AuthenticatedSessionController::class, 'create'])
        ->middleware('check.login.token') 
        ->name('login');
        
    Route::post('masuk-perpustakaan-speland/{token?}', [AuthenticatedSessionController::class, 'store'])
        ->middleware('check.login.token');

    /**
     * 2. LUPA PASSWORD (PUBLIC)
     */
    Route::get('lupa-sandi', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('lupa-sandi', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Token di sini berbeda dengan token login, ini adalah token reset dari email
    Route::get('atur-ulang-sandi/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('atur-ulang-sandi', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    /**
     * 3. REGISTRASI PETUGAS BARU
     */
    Route::post('register-petugas', [RegisteredUserController::class, 'store'])
        ->name('register.store');

    /**
     * 4. VERIFIKASI EMAIL
     */
    Route::get('verifikasi-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verifikasi-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verifikasi-notifikasi', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    /**
     * 5. KONFIRMASI & UPDATE PASSWORD
     */
    Route::get('konfirmasi-sandi', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('konfirmasi-sandi', [ConfirmablePasswordController::class, 'store']);

    Route::put('update-sandi', [PasswordController::class, 'update'])
        ->name('password.update');

    /**
     * 6. LOGOUT
     */
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});