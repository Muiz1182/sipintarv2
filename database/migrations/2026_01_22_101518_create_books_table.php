<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel books.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index(); // Tambahkan index untuk mempercepat pencarian judul
            $table->string('author')->nullable(); 
            $table->text('description')->nullable(); 
            $table->string('cover');        
            $table->string('pdf_file');     
            $table->unsignedInteger('views')->default(0); // Gunakan unsignedInteger (karena views tidak mungkin negatif)
            $table->timestamps();           
        });
    }

    /**
     * Batalkan migrasi (Rollback).
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};