<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// tes upload using cloud storage
Route::post('/upload', [BookController::class, 'uploadBook']);

Route::post('/cleanup-storage', [BookController::class, 'cleanupUnusedFiles']);
Route::post('/move-to-public', [BookController::class, 'moveFilesToPublic']);
