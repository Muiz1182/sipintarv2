<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk memperbaiki struktur tabel.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // 1. Hapus kolom 'category' (string) yang menyebabkan error
            if (Schema::hasColumn('books', 'category')) {
                $table->dropColumn('category');
            }

            // 2. Tambah kolom 'category_id' sebagai Foreign Key
            // after('title') memastikan posisi kolom tetap rapi di database
            $table->foreignId('category_id')
                  ->after('title') 
                  ->nullable() // Boleh kosong jika rak belum ditentukan
                  ->constrained('categories') // Merujuk ke tabel categories
                  ->onDelete('set null'); // Jika rak dihapus, buku tidak ikut terhapus (status jadi UMUM)
        });
    }

    /**
     * Batalkan migrasi jika diperlukan.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Hapus constraint dan kolom
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            // Kembalikan ke struktur awal (string) jika rollback
            $table->string('category')->nullable()->after('title');
        });
    }
};