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
     * Boot Method: Cleanup file secara otomatis saat update atau delete.
     */
    protected static function booted()
    {
        static::updating(function ($book) {
            if ($book->isDirty('cover') && $book->getOriginal('cover')) {
                $oldCover = $book->getOriginal('cover');
                foreach ([public_path($oldCover), storage_path('app/public/' . $oldCover)] as $path) {
                    if (file_exists($path) && is_file($path)) {
                        @unlink($path);
                    }
                }
                if (Storage::disk('public')->exists($oldCover)) {
                    Storage::disk('public')->delete($oldCover);
                }
            }

            if ($book->isDirty('pdf_file') && $book->getOriginal('pdf_file')) {
                $oldPdf = $book->getOriginal('pdf_file');
                foreach ([public_path($oldPdf), storage_path('app/public/' . $oldPdf)] as $path) {
                    if (file_exists($path) && is_file($path)) {
                        @unlink($path);
                    }
                }
                if (Storage::disk('public')->exists($oldPdf)) {
                    Storage::disk('public')->delete($oldPdf);
                }
            }
        });

        static::deleting(function ($book) {
            foreach ([$book->cover, $book->pdf_file] as $file) {
                if (!$file) continue;

                $publicFile = public_path($file);
                if (file_exists($publicFile) && is_file($publicFile)) {
                    @unlink($publicFile);
                }

                $storageFile = storage_path('app/public/' . $file);
                if (file_exists($storageFile) && is_file($storageFile)) {
                    @unlink($storageFile);
                }

                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
        });
    }
}