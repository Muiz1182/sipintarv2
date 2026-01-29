<?php

namespace App\Http\Controllers; // Pastikan namespace sesuai lokasi file Anda

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Boot function untuk menangani logika otomatis.
     * Membuat slug otomatis saat nama kategori diinput.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    /**
     * Relasi ke Model Book.
     * * CATATAN: Karena tabel 'books' Anda menggunakan string 'category' 
     * untuk menyimpan nama kategori, maka foreign key-nya adalah 'category' 
     * dan local key-nya adalah 'name'.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category', 'name');
    }

    /**
     * Helper: Mendapatkan jumlah buku dalam kategori ini.
     */
    public function getBooksCountAttribute()
    {
        return $this->books()->count();
    }
}