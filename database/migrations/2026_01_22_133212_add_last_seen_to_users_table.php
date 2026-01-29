<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan migrasi untuk menambahkan kolom last_seen.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek terlebih dahulu apakah kolom sudah ada untuk menghindari error
            if (!Schema::hasColumn('users', 'last_seen')) {
                $table->timestamp('last_seen')
                      ->nullable()
                      ->after('password')
                      ->comment('Menyimpan jejak waktu terakhir user aktif');
            }
        });
    }

    /**
     * Membatalkan migrasi (Rollback).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_seen')) {
                $table->dropColumn('last_seen');
            }
        });
    }
};