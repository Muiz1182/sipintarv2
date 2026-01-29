@php
    $isAdmin = auth()->user()->email === 'adminsipintar@gmail.com';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-bold text-xl md:text-2xl text-amber-900 leading-tight flex items-center">
                <span class="mr-2">{{ $isAdmin ? '🛡️' : '📖' }}</span> 
                {{ $isAdmin ? __('Pusat Kendali Super Admin') : __('Dashboard Petugas SIPINTAR') }}
            </h2>
            
            @if($isAdmin) 
            <form action="{{ route('admin.books.truncate') }}" method="POST" 
                  x-data 
                  @submit.prevent="if(confirm('⚠️ PERINGATAN KRITIS: Hapus SELURUH koleksi buku secara permanen?')) $el.submit()">
                @csrf 
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl text-[10px] md:text-xs font-black shadow-lg transition-all uppercase tracking-widest active:scale-95">
                    Kosongkan Rak Buku
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>

    <div x-data="{ 
            openModal: false, 
            selectedAdmin: {id: '', name: ''},
            formAction: '',
            triggerModal(id, name) {
                this.selectedAdmin = {id: id, name: name};
                this.formAction = `{{ url('/admin/petugas') }}/${id}/password`;
                this.openModal = true;
            }
         }" 
         class="py-6 md:py-12 bg-[#FDFBF7] min-h-screen">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Notifikasi --}}
            <div class="space-y-4">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                         x-transition.out.opacity.duration.1000ms
                         class="bg-emerald-600 text-white p-4 rounded-2xl shadow-lg flex justify-between items-center border-b-4 border-emerald-800">
                        <span class="font-bold flex items-center text-sm"><span class="mr-2 text-xl">✨</span> {!! session('success') !!}</span>
                        <button @click="show = false" class="text-2xl opacity-50 hover:opacity-100">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" x-show="show" class="bg-rose-600 text-white p-4 rounded-2xl shadow-lg border-b-4 border-rose-800">
                        <div class="flex justify-between items-start">
                            <div>
                                @foreach ($errors->all() as $error)
                                    <p class="font-bold text-sm flex items-center mb-1"><span class="mr-2">🚫</span> {{ $error }}</p>
                                @endforeach
                            </div>
                            <button @click="show = false" class="text-2xl opacity-50 hover:opacity-100">&times;</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 {{ $isAdmin ? 'lg:grid-cols-2' : '' }} gap-8">
                {{-- Form Unggah --}}
                <div class="p-6 md:p-8 bg-white shadow-xl rounded-3xl border border-amber-100">
                    <header class="mb-8 font-black text-amber-900 flex items-center uppercase tracking-tighter">
                        <span class="bg-amber-100 p-2 rounded-xl mr-3">📖</span> Unggah Buku Baru
                    </header>

                    <form id="uploadForm" method="post" action="{{ route('admin.books.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="generated_cover" id="generated_cover">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-black text-amber-900/50 mb-2 text-[10px] uppercase">Judul Buku</label>
                                <input type="text" name="title" value="{{ old('title') }}" class="w-full border-gray-200 bg-gray-50 rounded-xl focus:ring-amber-500 text-sm" placeholder="Masukan judul..." required>
                            </div>
                            <div>
                                <label class="block font-black text-amber-900/50 mb-2 text-[10px] uppercase">Pilih Rak</label>
                                <select name="category_id" class="w-full border-gray-200 bg-gray-50 rounded-xl text-sm font-bold text-amber-900" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="bg-amber-50/50 p-6 rounded-2xl border-2 border-dashed border-amber-200">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                <div class="flex-1 w-full">
                                    <label class="block font-black text-amber-900/50 mb-2 text-[10px] uppercase">Berkas PDF</label>
                                    <input type="file" id="pdf_input" name="pdf_file" class="w-full text-xs text-gray-500 file:bg-amber-800 file:text-white file:rounded-xl file:border-0 file:px-4 file:py-2 cursor-pointer" accept="application/pdf" required>
                                    <p class="mt-2 text-[9px] text-amber-600 font-bold">* Sampul akan dihasilkan otomatis dari halaman pertama</p>
                                </div>
                                <div id="cover_preview_container" class="relative w-28 h-36 bg-white rounded-xl border-2 border-amber-200 flex items-center justify-center overflow-hidden shadow-md shrink-0">
                                    <canvas id="pdf_canvas" class="hidden"></canvas>
                                    <img id="preview_img" class="w-full h-full object-cover hidden" alt="Preview">
                                    <span id="placeholder_text" class="text-[8px] text-amber-400 font-black text-center px-2 uppercase">Preview</span>
                                    <div id="loading_spinner" class="hidden absolute inset-0 bg-white/90 flex items-center justify-center">
                                        <div class="w-6 h-6 border-4 border-amber-600 border-t-transparent rounded-full animate-spin"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="w-full py-4 bg-amber-800 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg hover:bg-amber-900 transition-all active:scale-95 disabled:opacity-50">
                            Simpan Ke SIPINTAR
                        </button>
                    </form>
                </div>

                @if($isAdmin)
                <div class="space-y-8">
                    {{-- Manajemen Rak --}}
                    <div class="p-6 md:p-8 bg-white shadow-xl rounded-3xl border-l-8 border-amber-500">
                        <header class="mb-4 font-black text-amber-900 flex items-center uppercase text-xs tracking-widest">
                            <span class="bg-amber-100 p-2 rounded-lg mr-3">📂</span> Manajemen Rak
                        </header>
                        <form method="post" action="{{ route('admin.category.store') }}" class="flex gap-3">
                            @csrf
                            {{-- PERBAIKAN: Name harus 'name' agar sinkron dengan Controller --}}
                            <input type="text" name="name" placeholder="Nama Rak Baru..." class="flex-1 border-gray-200 bg-gray-50 rounded-xl text-sm" required>
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase">Tambah</button>
                        </form>
                    </div>

                    {{-- Registrasi Petugas --}}
                    <div class="p-6 md:p-8 bg-white shadow-xl rounded-3xl border-t-8 border-blue-800">
                        <header class="mb-6 font-black text-blue-900 flex items-center uppercase text-xs tracking-widest">
                            <span class="bg-blue-100 p-2 rounded-lg mr-3">🔐</span> Registrasi Petugas
                        </header>
                        <form method="post" action="{{ route('admin.petugas.store') }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" name="name" placeholder="Nama Lengkap" class="w-full border-gray-200 bg-gray-50 rounded-xl text-sm" required>
                                <input type="email" name="email" placeholder="Email@sipintar.com" class="w-full border-gray-200 bg-gray-50 rounded-xl text-sm" required>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="password" name="password" placeholder="Sandi" class="w-full border-gray-200 bg-gray-50 rounded-xl text-sm" required>
                                <input type="password" name="password_confirmation" placeholder="Konfirmasi" class="w-full border-gray-200 bg-gray-50 rounded-xl text-sm" required>
                            </div>
                            <button type="submit" class="w-full py-4 bg-blue-800 text-white rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-blue-900 active:scale-95 transition-all">Daftarkan Petugas</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            {{-- Tabel Data --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
                <div class="{{ $isAdmin ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="bg-gray-50/50 px-8 py-5 border-b flex justify-between items-center font-black text-amber-900 uppercase text-[11px] tracking-widest">
                        <span>Daftar Inventaris</span>
                        {{-- PERBAIKAN ERROR total() --}}
                        <span class="bg-amber-800 text-white px-4 py-1.5 rounded-full text-[10px]">
                            {{ method_exists($books, 'total') ? $books->total() : $books->count() }} BUKU
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-[10px] font-black uppercase text-gray-400 tracking-widest border-b">
                                <tr>
                                    <th class="px-8 py-4">Fisik</th>
                                    <th class="px-8 py-4">Informasi Buku</th>
                                    <th class="px-8 py-4">Lokasi Rak</th>
                                    <th class="px-8 py-4 text-center">Opsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($books as $book)
                                    <tr class="hover:bg-amber-50/30 transition-colors group">
                                        <td class="px-8 py-5">
                                            <img src="{{ asset('storage/' . $book->cover) }}" 
                                                 alt="{{ $book->title }}"
                                                 class="w-12 h-16 object-cover rounded-lg shadow-md border-2 border-white transition-transform group-hover:scale-110"
                                                 onerror="this.src='https://ui-avatars.com/api/?name=Book&background=FDE68A&color=92400E'">
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="font-black text-amber-900 uppercase text-xs">{{ $book->title }}</div>
                                            <div class="text-[9px] text-amber-600 font-black flex items-center mt-1">
                                                <span class="mr-1">👁️</span> {{ number_format($book->views) }} BACA
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-[9px] font-black uppercase">
                                                {{ $book->category->name ?? 'UMUM' }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('books.read', $book->hashid) }}" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-black text-[9px] uppercase hover:bg-blue-700 transition-colors">Lihat</a>
                                                <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Hapus buku ini dari rak?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-lg font-black text-[9px] uppercase hover:bg-rose-600 hover:text-white transition-colors">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-24 text-center text-gray-400 font-black uppercase text-xs italic">Belum ada koleksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination (Hanya muncul jika menggunakan ->paginate()) --}}
                    @if(method_exists($books, 'links'))
                    <div class="p-4 bg-gray-50 border-t">
                        {{ $books->links() }}
                    </div>
                    @endif
                </div>

                @if($isAdmin)
                {{-- Daftar Petugas --}}
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden sticky top-6 self-start">
                    <div class="bg-amber-900 px-6 py-5 font-black text-white uppercase text-[11px] tracking-widest flex justify-between items-center">
                        <span>Tim Petugas</span>
                        <span class="text-[8px] bg-green-500 text-white px-2 py-0.5 rounded-full">Aktif</span>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($petugas as $p)
                        <div class="flex items-center p-4 bg-gray-50 rounded-2xl border hover:border-amber-200 transition-all">
                            <div class="w-10 h-10 bg-white border-2 border-amber-200 rounded-xl flex items-center justify-center text-amber-900 font-black text-xs uppercase shrink-0">
                                {{ substr($p->name, 0, 1) }}
                            </div>
                            <div class="flex-1 ml-3 min-w-0">
                                <p class="text-[10px] font-black truncate uppercase text-amber-900">{{ $p->name }}</p>
                                {{-- PERBAIKAN: Indikator Online via last_seen --}}
                                <p class="text-[8px] font-bold {{ $p->last_seen && \Carbon\Carbon::parse($p->last_seen)->diffInMinutes(now()) < 5 ? 'text-green-600' : 'text-gray-400' }}">
                                    ● {{ $p->last_seen ? \Carbon\Carbon::parse($p->last_seen)->diffForHumans() : 'Belum pernah aktif' }}
                                </p>
                            </div>
                            @if($p->email !== 'adminsipintar@gmail.com')
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-gray-300 hover:text-amber-800 p-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak 
                                         x-transition
                                         class="absolute right-0 mt-2 w-40 bg-white border rounded-xl shadow-xl z-[60] py-2">
                                        <button @click="triggerModal('{{ $p->id }}', '{{ $p->name }}'); open = false" 
                                                class="w-full text-left px-4 py-2 text-[10px] font-black text-gray-700 hover:bg-amber-50 uppercase flex items-center">
                                            <span class="mr-2">🔑</span> Sandi
                                        </button>
                                        <form action="{{ route('admin.petugas.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus akses petugas ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full text-left px-4 py-2 text-[10px] font-black text-rose-600 hover:bg-rose-50 uppercase flex items-center">
                                                <span class="mr-2">🗑️</span> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="text-[8px] bg-amber-200 text-amber-800 px-2 py-1 rounded font-black">MASTER</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- MODAL GANTI PASSWORD --}}
        <template x-teleport="body">
            <div x-show="openModal" 
                 class="fixed inset-0 z-[100] overflow-y-auto" 
                 x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 py-6">
                    <div class="fixed inset-0 bg-amber-950/60 backdrop-blur-sm" @click="openModal = false"></div>
                    
                    <div class="bg-white rounded-[32px] p-8 md:p-10 shadow-2xl max-w-md w-full z-50 border border-amber-100 relative"
                         x-show="openModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                        
                        <form :action="formAction" method="POST">
                            @csrf 
                            @method('PATCH')
                            
                            <h3 class="text-2xl font-black text-amber-900 uppercase tracking-tighter mb-2">Ganti Sandi</h3>
                            <p class="text-[10px] text-gray-400 mb-6 font-bold uppercase">USER: <span x-text="selectedAdmin.name" class="text-amber-600"></span></p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[9px] font-black text-amber-900 uppercase ml-2">Sandi Baru</label>
                                    <input type="password" name="password" class="w-full border-gray-200 bg-gray-50 rounded-2xl text-sm py-4 mt-1" placeholder="••••••••" required>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-amber-900 uppercase ml-2">Konfirmasi Sandi</label>
                                    <input type="password" name="password_confirmation" class="w-full border-gray-200 bg-gray-50 rounded-2xl text-sm py-4 mt-1" placeholder="••••••••" required>
                                </div>
                                
                                <button type="submit" class="w-full bg-amber-800 text-white py-4 rounded-2xl font-black text-xs uppercase shadow-lg hover:bg-amber-900 transition-all mt-4">
                                    Simpan Perubahan
                                </button>
                                <button type="button" @click="openModal = false" class="w-full text-gray-400 text-[10px] font-black uppercase mt-2">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Script Preview PDF (Tidak Berubah) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pdfInput = document.getElementById('pdf_input');
            const generatedCoverInput = document.getElementById('generated_cover');
            const canvas = document.getElementById('pdf_canvas');
            const previewImg = document.getElementById('preview_img');
            const placeholderText = document.getElementById('placeholder_text');
            const loadingSpinner = document.getElementById('loading_spinner');
            const submitBtn = document.getElementById('submitBtn');

            pdfInput.addEventListener('change', async function() {
                const file = this.files[0];
                if (file && file.type === "application/pdf") {
                    loadingSpinner.classList.remove('hidden');
                    placeholderText.classList.add('hidden');
                    previewImg.classList.add('hidden');
                    submitBtn.disabled = true;

                    const fileReader = new FileReader();
                    fileReader.onload = async function() {
                        try {
                            const typedarray = new Uint8Array(this.result);
                            const pdfjsLib = window['pdfjs-dist/build/pdf'];
                            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

                            const pdf = await pdfjsLib.getDocument(typedarray).promise;
                            const page = await pdf.getPage(1);
                            
                            const viewport = page.getViewport({ scale: 1.5 });
                            const context = canvas.getContext('2d');
                            
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            
                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };

                            await page.render(renderContext).promise;
                            const imageData = canvas.toDataURL('image/jpeg', 0.8); 
                            
                            generatedCoverInput.value = imageData;
                            previewImg.src = imageData;
                            previewImg.classList.remove('hidden');
                        } catch (error) {
                            console.error(error);
                            alert("Gagal memproses preview PDF.");
                            placeholderText.classList.remove('hidden');
                        } finally {
                            loadingSpinner.classList.add('hidden');
                            submitBtn.disabled = false;
                        }
                    };
                    fileReader.readAsArrayBuffer(file);
                }
            });
        });
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .overflow-x-auto::-webkit-scrollbar { height: 6px; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background: #d97706; border-radius: 20px; }
    </style>
</x-app-layout>