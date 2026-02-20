<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class BookController extends Controller
{
    /**
     * Menampilkan koleksi buku untuk pengunjung (Landing Page)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        $query = Book::whereHas('category')->with('category')->latest();

        // Filter Search
        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter Category
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
            $books = $query->paginate(100)->withQueryString();
            $booksByCategory = $books->groupBy(function ($book) {
                return $book->category->name ?? 'TANPA RAK';
            });
        } else {
            $books = $query->paginate(100)->withQueryString();
            $booksByCategory = $books->groupBy(function () {
                return  'TANPA RAK';
            });
        }

        $categories = Category::pluck('name');

        return view('welcome', compact('booksByCategory', 'books', 'categories'));
    }

    /**
     * Menampilkan dashboard manajemen buku (Admin Only)
     */
    public function adminIndex(Request $request)
    {
        $search = $request->input('search');

        // Gunakan paginate agar method $books->links() dan $books->total() di Blade tidak error
        $books = Book::with('category')
            ->latest()
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->paginate(10);

        $petugas = User::orderBy('name', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();

        // Sinkronisasi pengecekan admin dengan RegisteredUserController
        $isAdmin = Auth::check() && Auth::user()->email === 'adminsipintar@gmail.com';

        return view('dashboard', compact('books', 'petugas', 'categories', 'isAdmin'));
    }

    /**
     * Menyimpan buku baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'pdf_file'        => 'required|mimes:pdf|max:102400',
            'generated_cover' => 'nullable|string',
            'author'          => 'nullable|string|max:255',
            'description'     => 'nullable|string|max:1000',
        ]);

        try {
            // 1. Simpan PDF langsung ke public/pdfs
            $coverPath = null;

            if ($request->hasFile('pdf_file')) {
                $file = $request->file('pdf_file');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('pdfs'), $fileName);
                $pdfPath = 'pdfs/' . $fileName; // simpan ke database
            }

            // 2. Handle Auto-generated Cover (Base64 dari PDF.js di frontend)
            if ($request->filled('generated_cover')) {
                $base64Image = $request->generated_cover;
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image)) {
                    $data = substr($base64Image, strpos($base64Image, ',') + 1);
                    $data = base64_decode($data);
                    $fileName = 'cv_' . time() . '_' . uniqid() . '.jpg';
                    file_put_contents(public_path('covers/' . $fileName), $data);
                    $coverPath = 'covers/' . $fileName; // simpan ke databas
                }
            }

            // 3. Simpan ke Database
            Book::create([
                'title'       => strtoupper($request->title),
                'category_id' => $request->category_id,
                'author'      => strtoupper($request->author ?? 'PERPUSTAKAAN'),
                'description' => $request->description ?? 'Koleksi digital SIPINTAR.',
                'cover'       => $coverPath,
                'pdf_file'    => $pdfPath,
                'views'       => 0,
            ]);

            return redirect()->route('dashboard')->with('success', "Buku <b>{$request->title}</b> berhasil diarsipkan.");
        } catch (\Exception $e) {
            Log::error('Upload Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memproses buku: ' . $e->getMessage());
        }
    }

    /**
     * Method untuk menyimpan kategori/rak baru
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name'
        ], [
            'name.required' => 'Nama rak tidak boleh kosong.',
            'name.unique'   => 'Nama rak sudah ada.'
        ]);

        Category::create([
            'name' => strtoupper(trim($request->name))
        ]);

        return redirect()->back()->with('success', "Rak baru <b>{$request->name}</b> berhasil ditambahkan.");
    }

    /**
     * Baca PDF menggunakan HashID untuk keamanan URL
     */
    public function read($hashid)
    {
        $decoded = Hashids::decode($hashid);
        if (empty($decoded)) {
            abort(404, 'Tautan tidak valid.');
        }

        $book = Book::findOrFail($decoded[0]);

        // Update jumlah baca tanpa menyentuh field 'updated_at'
        $book->timestamps = false;
        $book->increment('views');

        return view('books.read', compact('book'));
    }

    /**
     * Hapus Buku (Beserta File Fisiknya)
     */
    public function destroy(Book $book)
    {
        try {

            $this->deleteFile($book->pdf_file);
            $this->deleteFile($book->cover);

            $book->delete();

            return redirect()->back()->with('success', 'Buku telah dihapus dari sistem.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus buku.');
        }
    }

    private function deleteFile($path)
    {
        if (!$path) return;

        $publicPath = public_path($path);

        if (file_exists($publicPath)) {
            unlink($publicPath);
        }

        // OPTIONAL: kalau masih ada sisa di storage lama
        $storagePath = storage_path('app/public/' . $path);

        if (file_exists($storagePath)) {
            unlink($storagePath);
        }
    }


    /**
     * Kosongkan Seluruh Rak (Hanya Super Admin)
     */
    public function truncate()
    {
        // Proteksi Tambahan: Cek Email Admin
        if (Auth::user()->email !== 'adminsipintar@gmail.com') {
            return redirect()->back()->with('error', 'Hanya Super Admin yang diizinkan melakukan ini.');
        }

        try {
            $books = Book::all();
            foreach ($books as $book) {
                // Gunakan fungsi destroy agar file fisik ikut terhapus satu per satu
                $this->destroy($book);
            }
            return redirect()->back()->with('success', 'Seluruh koleksi buku telah dibersihkan secara permanen.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengosongkan data.');
        }
    }

    public function cleanupUnusedFiles()
    {
        $deleted = [];

        // ===== PDF CLEANUP =====
        $storagePdfs = Storage::disk('public')->files('pdfs');
        $dbPdfs = collect(Book::pluck('pdf_file'))->filter()->flip();
        foreach ($storagePdfs as $file) {
            if (!isset($dbPdfs[$file])) {
                Storage::disk('public')->delete($file);
                $deleted[] = $file;
            }
        }

        // ===== COVER CLEANUP =====
        $storageCovers = Storage::disk('public')->files('covers');
        $dbCovers = collect(Book::pluck('cover'))->filter()->flip();
        foreach ($storageCovers as $file) {
            // if (!in_array($file, $dbCovers)) {
            if (!isset($dbCovers[$file])) {
                Storage::disk('public')->delete($file);
                $deleted[] = $file;
            }
        }

        return response()->json([
            'deleted_files' => $deleted,
            'total_deleted' => count($deleted),
        ]);
    }


    public function moveFilesToPublic()
    {
        $moved = [];
        $deleted = [];

        $folders = ['pdfs', 'covers'];

        foreach ($folders as $folder) {

            $files = Storage::disk('public')->files($folder);

            foreach ($files as $file) {

                $filename = basename($file);

                $sourcePath = storage_path('app/public/' . $file);
                $destinationPath = public_path($folder . '/' . $filename);

                // Kalau file sudah ada di public → hapus dari storage
                if (file_exists($destinationPath)) {
                    unlink($sourcePath);
                    $deleted[] = $file;
                    continue;
                }

                // Pastikan folder public ada
                if (!file_exists(public_path($folder))) {
                    mkdir(public_path($folder), 0755, true);
                }

                // Pindahin file
                if (rename($sourcePath, $destinationPath)) {
                    $moved[] = $file;
                }
            }
        }

        return response()->json([
            'moved_files' => $moved,
            'deleted_duplicates' => $deleted,
            'total_moved' => count($moved),
            'total_deleted_duplicates' => count($deleted),
        ]);
    }
}
