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
            get: fn() => $this->id ? Hashids::encode($this->id) : null,
        );
    }

    /**
     * Accessor: Mendapatkan URL lengkap untuk gambar sampul.
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::make(
            get: function () {

                if ($this->cover && file_exists(public_path($this->cover))) {
                    return asset($this->cover);
                }

                return "https://ui-avatars.com/api/?name="
                    . urlencode($this->title)
                    . "&background=4a2c1d&color=fbbf24&size=512&bold=true";
            }
        );
    }


    /**
     * Accessor: Mendapatkan URL lengkap untuk file PDF.
     */
    protected function pdfUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->pdf_file && file_exists(public_path($this->pdf_file))) {
                    return asset($this->pdf_file);
                }

                return null;
            }
        );
    }

    /**
     * Scope: Pencarian buku yang lebih cerdas.
     */
    public function scopeSearch($query, $term)
    {
        return $query->when($term, function ($q) use ($term) {
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', "%{$term}%")
                    ->orWhere('author', 'like', "%{$term}%")
                    ->orWhereHas('category', function ($cat) use ($term) {
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
        static::updating(function ($book) {

            if ($book->isDirty('cover') && $book->getOriginal('cover')) {

                $oldCover = public_path($book->getOriginal('cover'));

                if (file_exists($oldCover)) {
                    unlink($oldCover);
                }
            }

            if ($book->isDirty('pdf_file') && $book->getOriginal('pdf_file')) {

                $oldPdf = public_path($book->getOriginal('pdf_file'));

                if (file_exists($oldPdf)) {
                    unlink($oldPdf);
                }
            }
        });

        static::deleting(function ($book) {

            if ($book->cover) {
                $coverPath = public_path($book->cover);
                if (file_exists($coverPath)) unlink($coverPath);
            }

            if ($book->pdf_file) {
                $pdfPath = public_path($book->pdf_file);
                if (file_exists($pdfPath)) unlink($pdfPath);
            }
        });
    }
}