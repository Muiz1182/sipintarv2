<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menambah kolom kategori.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Menambahkan kolom category setelah kolom title
            // Kita beri default 'Umum' agar data buku yang sudah ada tidak error (null)
            $table->string('category')->default('Umum')->after('title');
        });
    }

    /**
     * Batalkan migrasi (Rollback).
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Menghapus kolom category jika migrasi dibatalkan
            $table->dropColumn('category');
        });
    }
};