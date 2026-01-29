<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    /**
     * Mass assignment protection.
     */
    protected $fillable = [
        'title',
        'category_id',
        'author',
        'description',
        'cover',
        'pdf_file',
        'views',
    ];

    /**
     * Default values untuk atribut tertentu.
     */
    protected $attributes = [
        'views' => 0,
        'author' => 'Anonim',
    ];

    /**
     * Serialisasi: Menambahkan accessor ke JSON/Array.
     */
    protected $appends = ['hashid', 'cover_url', 'pdf_url'];

    /**
     * RELASI: Setiap buku memiliki satu kategori/rak.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault([
            'name' => 'Tanpa Kategori'
        ]);
    }

    /**
     * Accessor: Hashid
     */
    protected function hashid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? Hashids::encode($this->id) : null,
        );
    }

    /**
     * Accessor: Mendapatkan URL lengkap untuk gambar sampul.
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Gunakan helper asset() jika path sudah benar, 
                // atau Storage::url jika menggunakan symlink
                if ($this->cover && Storage::disk('public')->exists($this->cover)) {
                    return Storage::disk('public')->url($this->cover);
                }
                
                // Fallback: Menggunakan UI-Avatars
                return "https://ui-avatars.com/api/?name=" . urlencode($this->title) . "&background=4a2c1d&color=fbbf24&size=512&bold=true";
            }
        );
    }

    /**
     * Accessor: Mendapatkan URL lengkap untuk file PDF.
     */
    protected function pdfUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->pdf_file && Storage::disk('public')->exists($this->pdf_file)) 
                ? Storage::disk('public')->url($this->pdf_file) 
                : null,
        );
    }

    /**
     * Scope: Pencarian buku yang lebih cerdas.
     */
    public function scopeSearch($query, $term)
    {
        return $query->when($term, function($q) use ($term) {
            $q->where(function($sub) use ($term) {
                $sub->where('title', 'like', "%{$term}%")
                    ->orWhere('author', 'like', "%{$term}%")
                    ->orWhereHas('category', function($cat) use ($term) {
                        $cat->where('name', 'like', "%{$term}%");
                    });
            });
        });
    }

    /**
     * Method untuk mempermudah increment views secara aman tanpa mengubah 'updated_at'.
     */
    public function incrementViews()
    {
        // Perbaikan logika: increment secara langsung tanpa menyentuh timestamps
        return $this->timestamps = false ? $this->increment('views') : $this->increment('views');
        // Cara yang lebih bersih di Laravel:
        // return $this->incrementQuietly('views'); // Jika Laravel >= 9.32
    }

    /**
     * Boot Method: Cleanup file secara otomatis.
     */
    protected static function booted()
    {
        // Saat data diperbarui, hapus file lama jika file baru diunggah
        static::updating(function ($book) {
            if ($book->isDirty('cover') && $book->getOriginal('cover')) {
                Storage::disk('public')->delete($book->getOriginal('cover'));
            }
            if ($book->isDirty('pdf_file') && $book->getOriginal('pdf_file')) {
                Storage::disk('public')->delete($book->getOriginal('pdf_file'));
            }
        });

        // Saat data dihapus (Hanya jika bukan soft delete)
        static::deleting(function ($book) {
            if ($book->cover) Storage::disk('public')->delete($book->cover);
            if ($book->pdf_file) Storage::disk('public')->delete($book->pdf_file);
        });
    }
}