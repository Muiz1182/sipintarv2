<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Vinkla\Hashids\Facades\Hashids;
use ZipArchive;

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

    public function downloadSelected(Request $request)
    {
        $ids = $request->input('selected_books');

        if (empty($ids)) {
            return back()->with('error', 'Silakan pilih setidaknya satu buku untuk diunduh.');
        }

        $books = Book::whereIn('id', $ids)->get();

        $zip = new ZipArchive;
        $zipFileName = 'SIPINTAR_Batch_' . date('Ymd_His') . '.zip';
        $zipFilePath = public_path('temp/' . $zipFileName);

        if (!file_exists(public_path('temp'))) {
            mkdir(public_path('temp'), 0755, true);
        }

        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($books as $book) {
                $pathToFile = public_path($book->pdf_file);

                if (file_exists($pathToFile)) {
                    // Tambahkan ID di depan judul untuk memastikan tidak ada nama yang sama
                    $cleanTitle = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $book->title);
                    $fileNameInZip = $book->id . '_' . $cleanTitle . '.pdf';

                    $zip->addFile($pathToFile, $fileNameInZip);
                }
            }
            $zip->close();
        }

        if (!file_exists($zipFilePath) || filesize($zipFilePath) == 0) {
            return back()->with('error', 'Gagal membuat file ZIP atau file fisik tidak ditemukan.');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function leaderboard()
    {
        // Mengambil buku yang paling banyak dilihat
        $topBooks = Book::with('category')
            ->orderBy('views', 'desc')
            ->take(50)
            ->get();

        return view('books.leaderboard', compact('topBooks'));
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
            ->paginate(100);

        $petugas = User::orderBy('name', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();

        // Sinkronisasi pengecekan admin dengan RegisteredUserController
        $isAdmin = Auth::check() && Auth::user()->isSuperAdmin();
        $storageStats = $isAdmin ? $this->calculateStorageStats() : null;

        $totalViews = Book::sum('views');
        $totalBooks = Book::count();
        $totalCategories = $categories->count();
        $totalPetugas = $petugas->count();

        return view('dashboard', compact(
            'books', 
            'petugas', 
            'categories', 
            'isAdmin', 
            'storageStats', 
            'totalViews', 
            'totalBooks', 
            'totalCategories', 
            'totalPetugas'
        ));
    }

    /**
     * Mengambil data log storage database (JSON untuk AJAX / Audit)
     */
    public function storageLogs(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses terbatas untuk Super Admin.');
        }

        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $filter = $request->input('filter', 'all'); // 'all', 'over_1mb', 'missing'

        $query = Book::with('category')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('pdf_file', 'like', "%{$search}%")
                  ->orWhere('cover', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 100) $perPage = 25;
        $page = max(1, (int) $request->input('page', 1));

        $mapBook = function ($book) {
            $pdfPath = $book->pdf_file ? public_path($book->pdf_file) : null;
            $pdfExists = $pdfPath && file_exists($pdfPath);
            $pdfBytes = $pdfExists ? filesize($pdfPath) : 0;

            $coverPath = $book->cover ? public_path($book->cover) : null;
            $coverExists = $coverPath && file_exists($coverPath);
            $coverBytes = $coverExists ? filesize($coverPath) : 0;

            $totalBytes = $pdfBytes + $coverBytes;

            return [
                'id' => $book->id,
                'title' => $book->title,
                'category' => $book->category ? $book->category->name : 'Tanpa Rak',
                'pdf_file' => $book->pdf_file,
                'pdf_exists' => $pdfExists,
                'pdf_bytes' => $pdfBytes,
                'pdf_size' => $pdfExists ? $this->formatBytes($pdfBytes) : 'Tidak Ditemukan',
                'is_over_1mb' => $pdfBytes > 1048576,
                'cover' => $book->cover,
                'cover_exists' => $coverExists,
                'cover_bytes' => $coverBytes,
                'cover_size' => $coverExists ? $this->formatBytes($coverBytes) : 'Tidak Ditemukan',
                'total_bytes' => $totalBytes,
                'total_size' => $this->formatBytes($totalBytes),
                'created_at' => $book->created_at ? $book->created_at->format('d M Y, H:i') : '-',
                'read_url' => route('books.read', $book->hashid ?? $book->id),
            ];
        };

        if ($filter === 'over_1mb' || $filter === 'missing') {
            $allBooks = $query->get();
            $filtered = $allBooks->map($mapBook)->filter(function ($item) use ($filter) {
                if ($filter === 'over_1mb') return $item['is_over_1mb'];
                if ($filter === 'missing') return !$item['pdf_exists'] || !$item['cover_exists'];
                return true;
            })->values();

            $total = $filtered->count();
            $items = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'stats' => $this->calculateStorageStats(),
                'items' => $items,
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage) ?: 1,
                    'total' => $total,
                    'per_page' => $perPage,
                    'from' => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
                    'to' => min($page * $perPage, $total),
                ]
            ]);
        }

        $books = $query->paginate($perPage);
        $items = $books->getCollection()->map($mapBook);

        return response()->json([
            'stats' => $this->calculateStorageStats(),
            'items' => $items,
            'pagination' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'total' => $books->total(),
                'per_page' => $books->perPage(),
                'from' => $books->firstItem() ?? 0,
                'to' => $books->lastItem() ?? 0,
            ]
        ]);
    }

    /**
     * Memperbarui cache statistik penyimpanan
     */
    public function refreshStorageStats()
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403);
        }
        Cache::forget('sipintar_storage_stats');
        $this->calculateStorageStats();
        return back()->with('success', 'Statistik dan ringkasan penyimpanan berhasil diperbarui.');
    }

    /**
     * Menghitung ringkasan kapasitas berkas di disk dan keterkaitannya dengan database
     */
    public function calculateStorageStats()
    {
        return Cache::remember('sipintar_storage_stats', 300, function () {
            $totalBooks = Book::count();
            $pdfSize = 0;
            $coverSize = 0;
            $missingPdfs = 0;
            $missingCovers = 0;
            $over1MbPdfs = 0;

            $books = Book::select('id', 'pdf_file', 'cover')->get();
            foreach ($books as $b) {
                $p = $b->pdf_file ? public_path($b->pdf_file) : null;
                if ($p && file_exists($p)) {
                    $sz = filesize($p);
                    $pdfSize += $sz;
                    if ($sz > 1048576) {
                        $over1MbPdfs++;
                    }
                } else {
                    $missingPdfs++;
                }

                $c = $b->cover ? public_path($b->cover) : null;
                if ($c && file_exists($c)) {
                    $coverSize += filesize($c);
                } else {
                    $missingCovers++;
                }
            }

            $totalBytes = $pdfSize + $coverSize;

            return [
                'total_books' => $totalBooks,
                'total_bytes' => $totalBytes,
                'total_formatted' => $this->formatBytes($totalBytes),
                'pdf_bytes' => $pdfSize,
                'pdf_formatted' => $this->formatBytes($pdfSize),
                'cover_bytes' => $coverSize,
                'cover_formatted' => $this->formatBytes($coverSize),
                'missing_pdfs' => $missingPdfs,
                'missing_covers' => $missingCovers,
                'missing_total' => $missingPdfs + $missingCovers,
                'over_1mb_pdfs' => $over1MbPdfs,
                'avg_book_size' => $totalBooks > 0 ? $this->formatBytes($totalBytes / $totalBooks) : '0 B',
            ];
        });
    }

    /**
     * Format byte ke satuan yang mudah dibaca manusia (B, KB, MB, GB)
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = min(floor(log($bytes) / log(1024)), count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    public function update(Request $request, $id)
    {
        // 1. Validasi input
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        try {
            // 2. Cari buku berdasarkan ID
            $book = Book::findOrFail($id);

            // 3. Update data
            $book->update([
                'title' => strtoupper($request->title),
                'category_id' => $request->category_id,
            ]);

            Cache::forget('sipintar_storage_stats');

            // 4. Kembali dengan pesan sukses
            return back()->with('success', "Buku <b>{$book->title}</b> berhasil diperbarui!");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui buku: ' . $e->getMessage()]);
        }
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

            // 4. Kompresi otomatis di backend jika ukuran berkas masih > 1MB
            $this->compressPdfIfNeeded(public_path($pdfPath));

            Cache::forget('sipintar_storage_stats');

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
     * Method untuk menghapus kategori/rak secara permanen (Hanya Super Admin)
     */
    public function destroyCategory(Category $category)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan menghapus kategori.');
        }

        $categoryName = $category->name;

        // Set category_id buku terkait menjadi null (jika ada) sebelum kategori dihapus
        Book::where('category_id', $category->id)->update(['category_id' => null]);

        // Hapus permanen dari database
        $category->delete();

        return redirect()->back()->with('success', "Kategori <b>{$categoryName}</b> berhasil dihapus secara permanen dari database.");
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
     * Hapus Buku (Beserta File Fisiknya dari Folder dan Database)
     */
    public function destroy(Book $book)
    {
        try {
            $title = $book->title;

            // Hapus file fisik secara permanen dari folder
            $this->deleteFile($book->pdf_file);
            $this->deleteFile($book->cover);

            // Hapus permanen dari database
            $book->delete();

            Cache::forget('sipintar_storage_stats');

            return redirect()->back()->with('success', "Buku <b>{$title}</b> beserta berkas fisiknya berhasil dihapus permanen.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus buku: ' . $e->getMessage());
        }
    }

    private function deleteFile($path)
    {
        if (!$path) return;

        // 1. Cek dan hapus dari direktori public/ (public/pdfs/..., public/covers/...)
        $publicPath = public_path($path);
        if (file_exists($publicPath) && is_file($publicPath)) {
            @unlink($publicPath);
        }

        // 2. Cek dan hapus dari direktori storage/app/public/
        $storagePath = storage_path('app/public/' . $path);
        if (file_exists($storagePath) && is_file($storagePath)) {
            @unlink($storagePath);
        }

        // 3. Cek dan hapus melalui Storage disk public
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }


    /**
     * Kosongkan Seluruh Rak (Hanya Super Admin)
     */
    public function truncate()
    {
        // Proteksi Tambahan: Cek Hak Super Admin
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang diizinkan melakukan ini.');
        }

        try {
            $books = Book::all();
            foreach ($books as $book) {
                // Hapus file fisik dan record satu per satu
                $this->deleteFile($book->pdf_file);
                $this->deleteFile($book->cover);
                $book->delete();
            }

            // Bersihkan sisa file di folder public/pdfs dan public/covers jika ada file yatim
            $this->cleanDirectoryFiles(public_path('pdfs'));
            $this->cleanDirectoryFiles(public_path('covers'));

            Cache::forget('sipintar_storage_stats');

            return redirect()->back()->with('success', 'Seluruh koleksi buku beserta berkas fisiknya di folder telah dibersihkan secara permanen.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengosongkan data: ' . $e->getMessage());
        }
    }

    private function cleanDirectoryFiles($dir)
    {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Kompres berkas PDF jika ukuran melebihi 1MB (Server-Side)
     */
    public function compressPdfIfNeeded($filePath)
    {
        if (!file_exists($filePath) || filesize($filePath) <= 1024 * 1024) {
            return;
        }

        $gsBinary = null;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            foreach (['gswin64c.exe', 'gswin32c.exe', 'gs.exe'] as $bin) {
                if (@exec("where $bin 2>nul")) {
                    $gsBinary = $bin;
                    break;
                }
            }
        } else {
            foreach (['/usr/bin/gs', '/usr/local/bin/gs', 'gs'] as $bin) {
                if (@exec("which $bin 2>/dev/null")) {
                    $gsBinary = $bin;
                    break;
                }
            }
        }

        if ($gsBinary) {
            $tempOutput = $filePath . '.comp.pdf';
            $cmd = sprintf(
                '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
                escapeshellcmd($gsBinary),
                escapeshellarg($tempOutput),
                escapeshellarg($filePath)
            );

            @exec($cmd);

            if (file_exists($tempOutput) && filesize($tempOutput) > 0 && filesize($tempOutput) < filesize($filePath)) {
                @unlink($filePath);
                rename($tempOutput, $filePath);
            } elseif (file_exists($tempOutput)) {
                @unlink($tempOutput);
            }
        }
    }

    /**
     * Kompresi massal berkas PDF yang sudah ada (> 1MB)
     */
    public function compressExistingPdfs()
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan menjalankan kompresi massal.');
        }

        $pdfFiles = glob(public_path('pdfs/*.pdf'));
        $largeCount = 0;
        $compressed = 0;

        foreach ($pdfFiles as $file) {
            if (is_file($file) && filesize($file) > 1024 * 1024) {
                $largeCount++;
                $oldSize = filesize($file);
                $this->compressPdfIfNeeded($file);
                if (filesize($file) < $oldSize) {
                    $compressed++;
                }
            }
        }

        Cache::forget('sipintar_storage_stats');

        if ($compressed > 0) {
            return redirect()->back()->with('success', "Berhasil mengompresi <b>{$compressed}</b> dari {$largeCount} berkas PDF.");
        } else {
            return redirect()->back()->with('success', "Pemeriksaan selesai: Terdata <b>{$largeCount}</b> berkas di atas 1MB. (Kompresi otomatis client-side aktif untuk setiap berkas baru).");
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
