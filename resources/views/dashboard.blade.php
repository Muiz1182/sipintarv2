@php
    $isAdmin = auth()->user() && auth()->user()->isSuperAdmin();
    $totalBooks = $totalBooks ?? \App\Models\Book::count();
    $totalViews = $totalViews ?? \App\Models\Book::sum('views');
    $totalCategories = $totalCategories ?? $categories->count();
    $totalPetugas = $totalPetugas ?? $petugas->count();
@endphp

<x-app-layout>
    {{-- Header Slot: Greeting & Quick Toolbar --}}
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            {{-- Profil & Identitas Peran --}}
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-sm {{ $isAdmin ? 'bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-amber-900/20' : 'bg-gradient-to-br from-amber-700 to-amber-900 text-white shadow-amber-950/20' }}">
                    {{ $isAdmin ? '👑' : '🛡️' }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-black text-xl sm:text-2xl text-amber-950 uppercase tracking-tight leading-none">
                            {{ $isAdmin ? 'Pusat Kendali Super Admin' : 'Portal Operasional Petugas' }}
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $isAdmin ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-amber-100 text-amber-900 border border-amber-200' }}">
                            {{ $isAdmin ? 'Super Admin' : 'Petugas' }}
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-semibold mt-1 flex items-center gap-2">
                        <span>Halo, <strong class="text-amber-900">{{ auth()->user()->name }}</strong></span>
                        <span class="text-stone-300">&bull;</span>
                        <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
                    </p>
                </div>
            </div>
            
            {{-- Quick Action Toolbar --}}
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-2.5 w-full lg:w-auto mt-2 lg:mt-0">
                <a href="{{ route('home') }}" target="_blank" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider text-amber-900 bg-white hover:bg-amber-50 border border-amber-200/80 shadow-sm transition-all active:scale-95">
                    <span>📖</span>
                    <span>Lihat Rak Buku</span>
                </a>

                @if($isAdmin)
                    <button type="button" 
                            @click="openStorageModal = true; if(storageLogs.length === 0) fetchStorageLogs(1)" 
                            class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider text-white bg-amber-900 hover:bg-amber-950 shadow-sm transition-all active:scale-95">
                        <span>📦</span>
                        <span>Log Storage</span>
                    </button>

                    <form action="{{ route('admin.books.compress-existing') }}" method="POST" 
                          class="w-full sm:w-auto"
                          x-data 
                          @submit.prevent="if(confirm('Mulai optimasi seluruh berkas PDF di atas 1MB yang tersimpan di disk?')) $el.submit()">
                        @csrf 
                        <button type="submit" 
                                class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider text-amber-950 bg-amber-100 hover:bg-amber-200 border border-amber-300 shadow-sm transition-all active:scale-95">
                            <span>⚡</span>
                            <span>Kompresi &gt; 1MB</span>
                        </button>
                    </form>

                    <form action="{{ route('admin.books.truncate') }}" method="POST" 
                          class="w-full sm:w-auto"
                          x-data 
                          @submit.prevent="if(confirm('⚠️ PERINGATAN KRITIS: Anda akan menghapus SELURUH koleksi buku beserta berkas fisik cover dan PDF secara permanen. Lanjutkan?')) $el.submit()">
                        @csrf 
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider text-white bg-rose-700 hover:bg-rose-800 shadow-sm transition-all active:scale-95">
                            <span>🗑️</span>
                            <span>Kosongkan Rak</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.9/pdf-lib.min.js"></script>

    {{-- Main Workspace Controller --}}
    <div x-data="{ 
            activeTab: '{{ (request('search') || request('page')) ? 'inventory' : 'upload' }}',
            openModal: false, 
            openEditModal: false,
            openStorageModal: false,
            storageLogs: [],
            storageLoading: false,
            storageSearch: '',
            storageCategory: '',
            storageFilter: 'all',
            storagePagination: { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 },
            async fetchStorageLogs(page = 1) {
                this.storageLoading = true;
                try {
                    const params = new URLSearchParams({
                        page: page,
                        search: this.storageSearch,
                        category_id: this.storageCategory,
                        filter: this.storageFilter,
                    });
                    const res = await fetch(`{{ route('admin.storage.logs') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const json = await res.json();
                    this.storageLogs = json.items || [];
                    this.storagePagination = json.pagination || { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
                } catch (e) {
                    console.error('Gagal memuat log storage:', e);
                } finally {
                    this.storageLoading = false;
                }
            },
            selectedAdmin: {id: '', name: ''},
            selectedBook: {id: '', title: '', category_id: ''},
            formAction: '',
            editAction: '',
            triggerModal(id, name) {
                this.selectedAdmin = {id: id, name: name};
                this.formAction = `{{ url('/admin/petugas') }}/${id}/password`;
                this.openModal = true;
            },
            triggerEditModal(id, title, category_id) {
                this.selectedBook = {id: id, title: title, category_id: category_id};
                this.editAction = `{{ url('/admin/books') }}/${id}`;
                this.openEditModal = true;
            }
         }" 
         class="py-6 sm:py-8 bg-[#FAF7F2] min-h-screen">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Notifikasi Sistem --}}
            <div class="space-y-3">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" 
                         x-transition.out.opacity.duration.1000ms
                         class="bg-emerald-700 text-white p-4 rounded-2xl shadow-md flex justify-between items-center border border-emerald-600">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-lg">✅</span>
                            <span class="font-bold text-xs sm:text-sm">{!! session('success') !!}</span>
                        </div>
                        <button @click="show = false" class="text-2xl text-emerald-200 hover:text-white leading-none p-1">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-rose-700 text-white p-4 rounded-2xl shadow-md border border-rose-600 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-lg">⚠️</span>
                            <span class="font-bold text-xs sm:text-sm">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div x-data="{ show: true }" x-show="show" class="bg-rose-700 text-white p-4 rounded-2xl shadow-md border border-rose-600">
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <p class="font-bold text-xs sm:text-sm flex items-center gap-2">
                                        <span>⚠️</span>
                                        <span>{{ $error }}</span>
                                    </p>
                                @endforeach
                            </div>
                            <button @click="show = false" class="text-2xl text-rose-200 hover:text-white leading-none p-1">&times;</button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Ringkasan Metrik / KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Koleksi Buku --}}
                <div class="bg-white p-5 rounded-3xl border border-amber-200/70 shadow-sm relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black uppercase tracking-wider text-stone-500">Total Koleksi</span>
                        <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-sm">📚</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-950 tracking-tight">{{ number_format($totalBooks) }}</div>
                    <p class="text-[11px] text-amber-800/80 font-bold mt-1">Koleksi terbit di SIPINTAR</p>
                </div>

                {{-- Total Pembaca Siswa --}}
                <div class="bg-white p-5 rounded-3xl border border-amber-200/70 shadow-sm relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black uppercase tracking-wider text-stone-500">Total Pembaca</span>
                        <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-sm">👁️</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-950 tracking-tight">{{ number_format($totalViews) }}</div>
                    <p class="text-[11px] text-amber-800/80 font-bold mt-1">Aktivitas baca flipbook</p>
                </div>

                @if($isAdmin && isset($storageStats))
                    {{-- Kapasitas Storage (Super Admin) --}}
                    <div class="bg-white p-5 rounded-3xl border border-amber-200/70 shadow-sm relative overflow-hidden group cursor-pointer hover:border-amber-400 transition-colors" @click="activeTab = 'storage'">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black uppercase tracking-wider text-stone-500">Storage Disk</span>
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-sm">💾</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-amber-950 tracking-tight">{{ $storageStats['total_formatted'] }}</div>
                        <p class="text-[11px] text-stone-500 font-semibold mt-1">
                            PDF: {{ $storageStats['pdf_formatted'] }} &bull; Sampul: {{ $storageStats['cover_formatted'] }}
                        </p>
                    </div>

                    {{-- Integritas Berkas (Super Admin) --}}
                    <div class="bg-white p-5 rounded-3xl border border-amber-200/70 shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black uppercase tracking-wider text-stone-500">Integritas Berkas</span>
                            <span class="w-2.5 h-2.5 rounded-full {{ $storageStats['missing_total'] == 0 ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black {{ $storageStats['missing_total'] == 0 ? 'text-emerald-700' : 'text-rose-600' }} tracking-tight">
                            {{ $storageStats['missing_total'] == 0 ? '100% Valid' : 'Peringatan' }}
                        </div>
                        <p class="text-[11px] text-stone-500 font-semibold mt-1 truncate">
                            {{ $storageStats['missing_total'] == 0 ? 'Semua file sinkron di disk' : $storageStats['missing_total'] . ' berkas hilang di disk' }}
                        </p>
                    </div>
                @else
                    {{-- Rak & Kategori (Petugas) --}}
                    <div class="bg-white p-5 rounded-3xl border border-amber-200/70 shadow-sm relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black uppercase tracking-wider text-stone-500">Rak Tersedia</span>
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-sm">📂</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-amber-950 tracking-tight">{{ number_format($totalCategories) }} Rak</div>
                        <p class="text-[11px] text-amber-800/80 font-bold mt-1">Kategori literasi aktif</p>
                    </div>

                    {{-- Kompresi Otomatis (Petugas) --}}
                    <div class="bg-white p-5 rounded-3xl border border-amber-200/70 shadow-sm relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black uppercase tracking-wider text-stone-500">Kompresi PDF</span>
                            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm">⚡</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-700 tracking-tight">&le; 1 MB</div>
                        <p class="text-[11px] text-stone-500 font-semibold mt-1">Target otomatis aktif</p>
                    </div>
                @endif
            </div>

            {{-- Segmented Tab Bar Navigation --}}
            <div class="bg-white p-1.5 rounded-2xl border border-amber-200/70 shadow-sm flex items-center gap-1.5 overflow-x-auto scrollbar-none touch-pan-x">
                <button type="button" 
                        @click="activeTab = 'upload'" 
                        :class="activeTab === 'upload' ? 'bg-amber-800 text-white shadow-sm' : 'text-stone-600 hover:text-amber-950 hover:bg-stone-50'"
                        class="px-4 py-2.5 min-h-[44px] rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 shrink-0">
                    <span>➕</span>
                    <span>Unggah Koleksi Baru</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'inventory'" 
                        :class="activeTab === 'inventory' ? 'bg-amber-800 text-white shadow-sm' : 'text-stone-600 hover:text-amber-950 hover:bg-stone-50'"
                        class="px-4 py-2.5 min-h-[44px] rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 shrink-0">
                    <span>📚</span>
                    <span>Inventaris Buku ({{ method_exists($books, 'total') ? $books->total() : $books->count() }})</span>
                </button>

                @if($isAdmin)
                    <button type="button" 
                            @click="activeTab = 'categories'" 
                            :class="activeTab === 'categories' ? 'bg-amber-800 text-white shadow-sm' : 'text-stone-600 hover:text-amber-950 hover:bg-stone-50'"
                            class="px-4 py-2.5 min-h-[44px] rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 shrink-0">
                        <span>📂</span>
                        <span>Manajemen Rak ({{ $categories->count() }})</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'team'" 
                            :class="activeTab === 'team' ? 'bg-amber-800 text-white shadow-sm' : 'text-stone-600 hover:text-amber-950 hover:bg-stone-50'"
                            class="px-4 py-2.5 min-h-[44px] rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 shrink-0">
                        <span>👥</span>
                        <span>Tim Petugas ({{ $petugas->count() }})</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'storage'" 
                            :class="activeTab === 'storage' ? 'bg-amber-800 text-white shadow-sm' : 'text-stone-600 hover:text-amber-950 hover:bg-stone-50'"
                            class="px-4 py-2.5 min-h-[44px] rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 shrink-0">
                        <span>📊</span>
                        <span>Storage &amp; Integritas</span>
                    </button>
                @endif
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 1: UNGGAH BUKU BARU                                  --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'upload'" x-transition class="space-y-6">
                <div class="bg-white p-6 sm:p-10 rounded-3xl shadow-sm border border-amber-200/70 max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-8 pb-5 border-b border-stone-100">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-900 text-lg font-black shadow-inner">
                                📖
                            </span>
                            <div>
                                <h3 class="font-black text-amber-950 text-base sm:text-lg uppercase tracking-tight">Formulir Unggah Koleksi Baru</h3>
                                <p class="text-xs text-stone-500 font-semibold">Dokumen PDF otomatis terkompresi &le; 1MB dan sampul visual ter-generate instan</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 bg-amber-50 px-3 py-1.5 rounded-full border border-amber-200">
                            SIPINTAR Smart Intake
                        </span>
                    </div>

                    <form id="uploadForm" method="post" action="{{ route('admin.books.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="generated_cover" id="generated_cover">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Judul Buku --}}
                            <div>
                                <label class="block font-black text-amber-950 text-xs uppercase tracking-wider mb-2">
                                    Judul Buku / Literatur
                                </label>
                                <input type="text" 
                                       name="title" 
                                       value="{{ old('title') }}" 
                                       class="w-full border-2 border-stone-200 bg-stone-50/50 rounded-2xl focus:border-amber-600 focus:bg-white focus:ring-2 focus:ring-amber-500/20 text-xs font-bold text-amber-950 py-3.5 px-4 transition-all" 
                                       placeholder="Ketik judul buku lengkap..." 
                                       required>
                            </div>

                            {{-- Pilih Rak / Kategori --}}
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block font-black text-amber-950 text-xs uppercase tracking-wider">
                                        Pilih Rak Buku
                                    </label>
                                    @if($isAdmin)
                                        <button type="button" 
                                                id="btnDeleteCategory"
                                                onclick="deleteSelectedCategory()"
                                                class="hidden text-[10px] font-black text-rose-600 hover:text-rose-800 uppercase tracking-wider items-center gap-1">
                                            <span>🗑️ Hapus Kategori Ini</span>
                                        </button>
                                    @endif
                                </div>
                                <select id="category_select" 
                                        name="category_id" 
                                        class="w-full border-2 border-stone-200 bg-stone-50/50 rounded-2xl focus:border-amber-600 focus:bg-white focus:ring-2 focus:ring-amber-500/20 text-xs font-bold text-amber-950 py-3.5 px-4 transition-all" 
                                        required 
                                        onchange="handleCategoryChange(this)">
                                    <option value="" disabled selected>-- Pilih Rak Buku --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" data-name="{{ $cat->name }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Dropzone Berkas PDF & Preview Cover --}}
                        <div class="bg-[#FAF7F2] p-6 sm:p-8 rounded-3xl border-2 border-dashed border-amber-300/80">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                <div class="flex-1 w-full">
                                    <label class="block font-black text-amber-950 text-xs uppercase tracking-wider mb-2">
                                        Berkas Dokumen PDF
                                    </label>
                                    <input type="file" 
                                           id="pdf_input" 
                                           name="pdf_file" 
                                           class="w-full text-xs text-stone-600 file:bg-amber-800 file:hover:bg-amber-900 file:text-white file:rounded-xl file:border-0 file:px-5 file:py-3 file:font-black file:text-xs file:uppercase file:tracking-wider file:cursor-pointer cursor-pointer" 
                                           accept="application/pdf" 
                                           required>
                                    <p class="mt-2 text-xs text-amber-800/80 font-bold flex items-center gap-1.5">
                                        <span>⚡</span>
                                        <span>Berkas &gt; 1MB otomatis dikompresi ke target &le; 1MB sebelum diunggah.</span>
                                    </p>

                                    {{-- Status & Progress Kompresi Otomatis --}}
                                    <div id="compression_status_container" class="hidden mt-4 p-4 rounded-2xl border bg-amber-50 border-amber-300">
                                        <div class="flex items-center justify-between text-xs font-bold text-amber-900 mb-2">
                                            <span id="compression_status_text" class="flex items-center gap-2">
                                                <span class="animate-spin inline-block">⚡</span> Mengompres PDF ke target &le; 1MB...
                                            </span>
                                            <span id="compression_percentage" class="text-amber-800 font-black">0%</span>
                                        </div>
                                        <div class="w-full bg-amber-200/80 rounded-full h-2.5 overflow-hidden">
                                            <div id="compression_progress_bar" class="bg-amber-600 h-2.5 rounded-full transition-all duration-200" style="width: 0%"></div>
                                        </div>
                                        <p id="compression_detail" class="mt-2 text-[11px] text-amber-800/80 font-semibold"></p>
                                    </div>
                                </div>

                                {{-- Kartu Pratinjau Sampul Buku --}}
                                <div id="cover_preview_container" class="relative w-32 h-44 bg-white rounded-2xl border-2 border-amber-300/80 flex items-center justify-center overflow-hidden shadow-md shrink-0 bg-amber-950">
                                    <canvas id="pdf_canvas" class="hidden"></canvas>
                                    <img id="preview_img" class="w-full h-full object-cover hidden" alt="Pratinjau Sampul">
                                    <span id="placeholder_text" class="text-[10px] text-amber-700/60 font-black text-center px-3 uppercase leading-relaxed">
                                        Pratinjau Sampul Otomatis
                                    </span>
                                    <div id="loading_spinner" class="hidden absolute inset-0 bg-white/90 flex items-center justify-center">
                                        <div class="w-7 h-7 border-3 border-amber-600 border-t-transparent rounded-full animate-spin"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Submit --}}
                        <button type="submit" 
                                id="submitBtn" 
                                class="w-full py-4 bg-gradient-to-r from-amber-800 to-amber-900 hover:from-amber-700 hover:to-amber-800 text-white rounded-2xl font-black uppercase tracking-widest text-xs sm:text-sm shadow-xl shadow-amber-950/20 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2">
                            <span>Simpan Koleksi Ke SIPINTAR</span>
                            <span>&rarr;</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: INVENTARIS BUKU                                   --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'inventory'" x-transition class="space-y-6">
                <div class="bg-white rounded-3xl shadow-sm overflow-hidden border border-amber-200/70">
                    
                    {{-- Toolbar Inventaris --}}
                    <div class="bg-[#FAF7F2] px-4 sm:px-8 py-5 border-b border-amber-200/60 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center justify-between md:justify-start w-full md:w-auto gap-3">
                            <span class="font-black text-amber-950 uppercase text-xs sm:text-sm tracking-wider">
                                Daftar Inventaris Buku
                            </span>
                            <span class="bg-amber-800 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shrink-0">
                                {{ method_exists($books, 'total') ? $books->total() : $books->count() }} Koleksi
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full md:w-auto">
                            <div class="grid grid-cols-2 sm:flex gap-2 w-full sm:w-auto">
                                {{-- Salin Semua Judul --}}
                                <button type="button" 
                                        onclick="copyAllTitles(this)" 
                                        class="w-full sm:w-auto min-h-[44px] bg-amber-700 hover:bg-amber-800 text-white px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all active:scale-95 flex items-center justify-center gap-1.5">
                                    <span>📋</span>
                                    <span>Salin Semua</span>
                                </button>

                                {{-- Unduh Terpilih (.zip) --}}
                                <button type="button" 
                                        onclick="document.getElementById('form-download-selected').submit()" 
                                        class="w-full sm:w-auto min-h-[44px] bg-emerald-700 hover:bg-emerald-800 text-white px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all active:scale-95 flex items-center justify-center gap-1.5">
                                    <span>📥</span>
                                    <span>Unduh (.zip)</span>
                                </button>
                            </div>

                            {{-- Search Bar --}}
                            <form action="{{ route('dashboard') }}" method="GET" class="relative w-full sm:w-60">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Cari judul buku..." 
                                       class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-stone-200 rounded-xl text-xs font-bold text-amber-950 focus:border-amber-600 focus:ring-0 placeholder-stone-400 min-h-[44px]">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Form Download Terpilih & Tabel / Kartu Mobile --}}
                    <form id="form-download-selected" action="{{ route('admin.books.download-selected') }}" method="POST">
                        @csrf
                        
                        {{-- Tampilan Mobile: Kartu Responsif (Bebas Scroll Horizontal) --}}
                        <div class="block md:hidden">
                            @if($books->count() > 0)
                                <div class="bg-stone-50/90 px-4 py-2.5 border-b border-stone-200 flex items-center justify-between">
                                    <label class="flex items-center gap-2 cursor-pointer text-[11px] font-black uppercase text-amber-950 select-none">
                                        <input type="checkbox" id="select_all_books_mobile" class="rounded border-stone-300 text-amber-700 focus:ring-amber-500 cursor-pointer w-4 h-4">
                                        <span>Pilih Semua Koleksi</span>
                                    </label>
                                    <span class="text-[10px] text-stone-500 font-bold uppercase">Halaman {{ method_exists($books, 'currentPage') ? $books->currentPage() : 1 }}</span>
                                </div>
                            @endif

                            <div class="p-3 sm:p-4 space-y-3 bg-[#FAF7F2]/50">
                                @forelse($books as $book)
                                    <div class="bg-white rounded-2xl p-4 border border-amber-200/70 shadow-sm space-y-3">
                                        {{-- Header: Checkbox & Judul & Tombol Salin --}}
                                        <div class="flex items-start justify-between gap-2.5">
                                            <div class="flex items-start gap-2.5 min-w-0">
                                                <input type="checkbox" name="selected_books[]" value="{{ $book->id }}" class="book-checkbox mt-0.5 rounded border-stone-300 text-amber-700 focus:ring-amber-500 cursor-pointer w-4 h-4 shrink-0">
                                                <div class="font-black text-amber-950 uppercase text-xs book-title-text leading-snug break-words">
                                                    {{ $book->title }}
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    onclick="copyToClipboard('{{ addslashes($book->title) }}', this)" 
                                                    class="p-2 -mr-1 -mt-1 hover:bg-amber-100 rounded-xl transition-all text-amber-800 shrink-0 min-w-[36px] min-h-[36px] flex items-center justify-center"
                                                    title="Salin Judul">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Body: Sampul, Kategori Rak, Info Ukuran & Pembaca --}}
                                        <div class="flex items-center gap-3 bg-[#FAF7F2] p-2.5 rounded-xl border border-amber-100">
                                            <div class="w-12 h-16 rounded-lg shadow-sm border border-amber-200/80 overflow-hidden relative shrink-0 bg-amber-950">
                                                <img src="{{ asset($book->cover) }}" 
                                                     alt="{{ $book->title }}"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Book&background=2b170c&color=fbbf24'">
                                            </div>
                                            <div class="min-w-0 space-y-1.5 flex-1">
                                                <div class="inline-flex items-center gap-1 bg-white text-amber-950 px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-amber-200/80">
                                                    <span>📌</span>
                                                    <span class="truncate max-w-[140px]">{{ $book->category->name ?? 'Tanpa Rak' }}</span>
                                                </div>
                                                <div class="text-[10px] text-stone-500 font-bold flex items-center flex-wrap gap-2">
                                                    <span>👁️ {{ number_format($book->views ?? 0) }} dilihat</span>
                                                    @php
                                                        $pdfPath = $book->pdf_file ? public_path($book->pdf_file) : null;
                                                        $pdfSize = ($pdfPath && file_exists($pdfPath)) ? filesize($pdfPath) : null;
                                                    @endphp
                                                    @if($pdfSize !== null)
                                                        <span class="px-2 py-0.5 rounded-full font-black text-[9px] {{ $pdfSize > 1048576 ? 'bg-amber-100 text-amber-950 border border-amber-300' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                                            💾 {{ round($pdfSize / 1048576, 2) }} MB
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Actions: 3 Tombol Aksi Nyaman (Min 44px) --}}
                                        <div class="grid grid-cols-3 gap-2 pt-1">
                                            <a href="{{ route('books.read', $book->hashid ?? $book->id) }}" target="_blank" class="bg-amber-800 hover:bg-amber-900 text-white min-h-[44px] rounded-xl font-black text-xs uppercase tracking-wider shadow-sm transition-all active:scale-95 flex items-center justify-center gap-1">
                                                <span>📖</span> Baca
                                            </a>
                                            
                                            <button type="button" @click="triggerEditModal('{{ $book->id }}', '{{ addslashes($book->title) }}', '{{ $book->category_id }}')" 
                                                    class="bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200 min-h-[44px] rounded-xl font-black text-xs uppercase tracking-wider transition-all active:scale-95 flex items-center justify-center gap-1">
                                                <span>✏️</span> Edit
                                            </button>

                                            <button type="button" 
                                                    onclick="if(confirm('Hapus buku ini secara permanen dari database dan disk?')) { document.getElementById('delete-form-{{ $book->id }}').submit(); }"
                                                    class="bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 min-h-[44px] rounded-xl font-black text-xs uppercase tracking-wider transition-all active:scale-95 border border-rose-200 flex items-center justify-center gap-1">
                                                <span>🗑️</span> Hapus
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-12 text-center bg-white rounded-2xl border border-stone-200">
                                        <span class="text-4xl block mb-2">📂</span>
                                        <p class="text-stone-400 font-black uppercase text-xs">
                                            {{ request('search') ? 'Buku dengan kata kunci "'.request('search').'" tidak ditemukan' : 'Belum ada koleksi buku terdaftar' }}
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Tampilan Desktop & Tablet: Data Table Klasik --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left text-xs" id="inventory-table">
                                <thead class="bg-stone-50 text-[10px] font-black uppercase text-stone-500 tracking-wider border-b border-stone-200">
                                    <tr>
                                        <th class="px-4 py-4 text-center w-12">
                                            <input type="checkbox" id="select_all_books" class="rounded border-stone-300 text-amber-700 focus:ring-amber-500 cursor-pointer">
                                        </th>
                                        <th class="px-4 py-4 w-16">Sampul</th>
                                        <th class="px-6 py-4">Judul Koleksi &amp; Ukuran</th>
                                        <th class="px-6 py-4">Lokasi Rak</th>
                                        <th class="px-6 py-4 text-center">Aksi Cepat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    @forelse($books as $book)
                                        <tr class="hover:bg-amber-50/40 transition-colors group">
                                            <td class="px-4 py-4 text-center">
                                                <input type="checkbox" name="selected_books[]" value="{{ $book->id }}" class="book-checkbox rounded border-stone-300 text-amber-700 focus:ring-amber-500 cursor-pointer">
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="w-12 h-16 rounded-lg shadow-sm border border-amber-200/80 overflow-hidden relative group-hover:scale-105 transition-transform bg-amber-950">
                                                    <img src="{{ asset($book->cover) }}" 
                                                         alt="{{ $book->title }}"
                                                         class="w-full h-full object-cover"
                                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Book&background=2b170c&color=fbbf24'">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2 group/title">
                                                    <div class="font-black text-amber-950 uppercase text-xs book-title-text leading-snug">
                                                        {{ $book->title }}
                                                    </div>
                                                    <button type="button" 
                                                            onclick="copyToClipboard('{{ addslashes($book->title) }}', this)" 
                                                            class="opacity-0 group-hover/title:opacity-100 p-1 hover:bg-amber-100 rounded transition-all text-amber-700"
                                                            title="Salin Judul">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="text-[10px] text-stone-500 font-bold flex items-center flex-wrap gap-2.5 mt-1.5">
                                                    <span>👁️ {{ number_format($book->views ?? 0) }} dilihat</span>
                                                    @php
                                                        $pdfPath = $book->pdf_file ? public_path($book->pdf_file) : null;
                                                        $pdfSize = ($pdfPath && file_exists($pdfPath)) ? filesize($pdfPath) : null;
                                                    @endphp
                                                    @if($pdfSize !== null)
                                                        <span class="px-2 py-0.5 rounded-full font-black text-[9px] {{ $pdfSize > 1048576 ? 'bg-amber-100 text-amber-950 border border-amber-300' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                                            💾 {{ round($pdfSize / 1048576, 2) }} MB
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center gap-1.5 bg-[#FAF7F2] text-amber-950 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border border-amber-200/80">
                                                    <span>📌</span>
                                                    <span>{{ $book->category->name ?? 'Tanpa Rak' }}</span>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex justify-center items-center gap-1.5">
                                                    <a href="{{ route('books.read', $book->hashid ?? $book->id) }}" target="_blank" class="bg-amber-800 hover:bg-amber-900 text-white px-3 py-1.5 rounded-lg font-black text-[10px] uppercase tracking-wider shadow-sm transition-all active:scale-95">
                                                        Baca
                                                    </a>
                                                    
                                                    <button type="button" @click="triggerEditModal('{{ $book->id }}', '{{ addslashes($book->title) }}', '{{ $book->category_id }}')" 
                                                            class="bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200 px-3 py-1.5 rounded-lg font-black text-[10px] uppercase tracking-wider transition-all active:scale-95">
                                                        Edit
                                                    </button>

                                                    <button type="button" 
                                                            onclick="if(confirm('Hapus buku ini secara permanen dari database dan disk?')) { document.getElementById('delete-form-{{ $book->id }}').submit(); }"
                                                            class="bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 px-3 py-1.5 rounded-lg font-black text-[10px] uppercase tracking-wider transition-all active:scale-95 border border-rose-200">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-20 text-center">
                                                <div class="flex flex-col items-center">
                                                    <span class="text-4xl mb-3">📂</span>
                                                    <p class="text-stone-400 font-black uppercase text-xs">
                                                        {{ request('search') ? 'Buku dengan kata kunci "'.request('search').'" tidak ditemukan' : 'Belum ada koleksi buku terdaftar' }}
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    {{-- Pagination --}}
                    @if(method_exists($books, 'links'))
                    <div class="p-4 bg-[#FAF7F2] border-t border-amber-200/60">
                        {{ $books->appends(['search' => request('search')])->links() }}
                    </div>
                    @endif
                </div>
            </div>

            @if($isAdmin)
                {{-- ======================================================== --}}
                {{-- TAB 3: MANAJEMEN RAK (Super Admin)                       --}}
                {{-- ======================================================== --}}
                <div x-show="activeTab === 'categories'" x-transition class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        {{-- Form Tambah Rak --}}
                        <div class="lg:col-span-4 bg-white p-6 sm:p-7 rounded-3xl shadow-sm border border-amber-200/70 h-fit">
                            <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-stone-100">
                                <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-sm">📂</span>
                                <div>
                                    <h4 class="font-black text-amber-950 uppercase text-xs sm:text-sm tracking-tight">Tambah Rak Baru</h4>
                                    <p class="text-[10px] text-stone-500 font-semibold">Buat kategori rak untuk klasifikasi buku</p>
                                </div>
                            </div>

                            <form method="post" action="{{ route('admin.category.store') }}" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block font-black text-amber-950 text-[10px] uppercase tracking-wider mb-1.5">Nama Rak / Kategori</label>
                                    <input type="text" 
                                           name="name" 
                                           placeholder="Contoh: Novel Remaja, Karya Guru..." 
                                           class="w-full border-2 border-stone-200 bg-stone-50/50 rounded-xl text-xs font-bold focus:border-amber-600 focus:bg-white py-3 px-3.5" 
                                           required>
                                </div>
                                <button type="submit" class="w-full py-3 bg-amber-800 hover:bg-amber-900 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all active:scale-95">
                                    Simpan Rak Baru
                                </button>
                            </form>
                        </div>

                        {{-- Daftar Kartu Rak yang Tersedia --}}
                        <div class="lg:col-span-8 bg-white p-6 sm:p-7 rounded-3xl shadow-sm border border-amber-200/70">
                            <div class="flex items-center justify-between mb-5 pb-3 border-b border-stone-100">
                                <h4 class="font-black text-amber-950 uppercase text-xs sm:text-sm tracking-tight">Daftar Rak Koleksi Terdaftar</h4>
                                <span class="bg-amber-100 text-amber-900 text-[10px] font-black px-2.5 py-1 rounded-full uppercase">
                                    {{ $categories->count() }} Kategori Aktif
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @forelse($categories as $cat)
                                    <div class="p-4 bg-[#FAF7F2] rounded-2xl border border-amber-200/80 flex items-center justify-between shadow-sm hover:border-amber-300 transition-all">
                                        <div class="flex items-center gap-3">
                                            <span class="w-10 h-10 rounded-xl bg-white border border-amber-200 flex items-center justify-center text-base">📌</span>
                                            <div>
                                                <h5 class="font-black text-amber-950 uppercase text-xs">{{ $cat->name }}</h5>
                                                <p class="text-[10px] text-stone-500 font-bold mt-0.5">
                                                    {{ $cat->books_count ?? \App\Models\Book::where('category_id', $cat->id)->count() }} Koleksi Terhubung
                                                </p>
                                            </div>
                                        </div>
                                        <form action="{{ route('admin.category.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $cat->name }} secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Kategori">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="col-span-2 py-10 text-center text-stone-400 text-xs italic">
                                        Belum ada kategori rak terdaftar.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- TAB 4: TIM PETUGAS (Super Admin)                         --}}
                {{-- ======================================================== --}}
                <div x-show="activeTab === 'team'" x-transition class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        {{-- Registrasi Petugas Baru --}}
                        <div class="lg:col-span-5 bg-white p-6 sm:p-7 rounded-3xl shadow-sm border border-amber-200/70 h-fit">
                            <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-stone-100">
                                <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-sm">🔐</span>
                                <div>
                                    <h4 class="font-black text-amber-950 uppercase text-xs sm:text-sm tracking-tight">Registrasi Petugas Baru</h4>
                                    <p class="text-[10px] text-stone-500 font-semibold">Buat akun untuk staf pengelola literasi</p>
                                </div>
                            </div>

                            <form method="post" action="{{ route('admin.petugas.store') }}" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block font-black text-amber-950 text-[10px] uppercase tracking-wider mb-1">Nama Lengkap</label>
                                    <input type="text" name="name" placeholder="Nama lengkap petugas..." class="w-full border-2 border-stone-200 bg-stone-50/50 rounded-xl text-xs font-bold py-2.5 px-3.5" required>
                                </div>
                                <div>
                                    <label class="block font-black text-amber-950 text-[10px] uppercase tracking-wider mb-1">Alamat Email</label>
                                    <input type="email" name="email" placeholder="nama@sipintar.com..." class="w-full border-2 border-stone-200 bg-stone-50/50 rounded-xl text-xs font-bold py-2.5 px-3.5" required>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-black text-amber-950 text-[10px] uppercase tracking-wider mb-1">Kata Sandi</label>
                                        <input type="password" name="password" placeholder="••••••••" class="w-full border-2 border-stone-200 bg-stone-50/50 rounded-xl text-xs font-bold py-2.5 px-3.5" required>
                                    </div>
                                    <div>
                                        <label class="block font-black text-amber-950 text-[10px] uppercase tracking-wider mb-1">Konfirmasi</label>
                                        <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full border-2 border-stone-200 bg-stone-50/50 rounded-xl text-xs font-bold py-2.5 px-3.5" required>
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-3 bg-amber-800 hover:bg-amber-900 text-white rounded-xl font-black uppercase tracking-wider text-xs shadow-sm transition-all active:scale-95">
                                    Daftarkan Petugas
                                </button>
                            </form>
                        </div>

                        {{-- Daftar Tim Petugas Aktif --}}
                        <div class="lg:col-span-7 bg-white p-6 sm:p-7 rounded-3xl shadow-sm border border-amber-200/70">
                            <div class="flex items-center justify-between mb-5 pb-3 border-b border-stone-100">
                                <h4 class="font-black text-amber-950 uppercase text-xs sm:text-sm tracking-tight">Daftar Tim &amp; Staf Perpustakaan</h4>
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2.5 py-1 rounded-full uppercase">
                                    {{ $petugas->count() }} Anggota Terdaftar
                                </span>
                            </div>

                            <div class="space-y-3">
                                @foreach($petugas as $p)
                                    <div class="p-4 bg-stone-50/70 rounded-2xl border border-stone-200/70 flex items-center justify-between hover:border-amber-300 transition-all">
                                        <div class="flex items-center gap-3.5 min-w-0">
                                            <div class="w-10 h-10 bg-amber-800 text-white rounded-xl flex items-center justify-center font-black text-xs uppercase shrink-0 shadow-sm">
                                                {{ substr($p->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <h5 class="font-black text-amber-950 uppercase text-xs truncate">{{ $p->name }}</h5>
                                                    @if($p->isSuperAdmin())
                                                        <span class="text-[9px] bg-amber-200 text-amber-950 px-2 py-0.5 rounded-full font-black uppercase border border-amber-300 shrink-0">Master</span>
                                                    @endif
                                                </div>
                                                <p class="text-[10px] text-stone-500 font-semibold truncate">{{ $p->email }}</p>
                                                <p class="text-[10px] font-bold mt-1">
                                                    @if($p->last_seen && \Carbon\Carbon::parse($p->last_seen)->diffInMinutes(now()) < 5)
                                                        <span class="text-emerald-700 flex items-center gap-1">
                                                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                                            Online Sekarang
                                                        </span>
                                                    @else
                                                        <span class="text-stone-400">
                                                            🕒 {{ $p->last_seen ? \Carbon\Carbon::parse($p->last_seen)->diffForHumans() : 'Belum pernah login' }}
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        @if(!$p->isSuperAdmin())
                                            <div x-data="{ menuOpen: false }" class="relative shrink-0">
                                                <button type="button" @click="menuOpen = !menuOpen" class="text-stone-400 hover:text-amber-800 p-2 rounded-lg hover:bg-stone-100 transition-colors">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                                                </button>
                                                <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak 
                                                     x-transition
                                                     class="absolute right-0 mt-2 w-44 bg-white border border-stone-200 rounded-2xl shadow-xl z-[60] py-1.5">
                                                    <button type="button" @click="triggerModal('{{ $p->id }}', '{{ $p->name }}'); menuOpen = false" 
                                                            class="w-full text-left px-4 py-2 text-[10px] font-black text-stone-700 hover:bg-amber-50 uppercase flex items-center gap-2">
                                                        <span>🔑</span>
                                                        <span>Reset Sandi</span>
                                                    </button>
                                                    <button type="button" 
                                                            onclick="if(confirm('Cabut akses dan hapus akun petugas ini?')) { document.getElementById('delete-petugas-{{ $p->id }}').submit(); }"
                                                            class="w-full text-left px-4 py-2 text-[10px] font-black text-rose-600 hover:bg-rose-50 uppercase flex items-center gap-2 border-t border-stone-100">
                                                        <span>🗑️</span>
                                                        <span>Hapus Akun</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- TAB 5: STORAGE & FILE AUDIT (Super Admin)                --}}
                {{-- ======================================================== --}}
                <div x-show="activeTab === 'storage'" x-transition class="space-y-6">
                    <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-amber-200/70">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-5 border-b border-stone-100">
                            <div>
                                <h3 class="font-black text-amber-950 text-base sm:text-lg uppercase tracking-tight">Audit Kapasitas &amp; Log Penyimpanan</h3>
                                <p class="text-xs text-stone-500 font-semibold mt-1">Detail status ukuran berkas PDF, sampul gambar, dan konsistensi file fisik pada server</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="openStorageModal = true; if(storageLogs.length === 0) fetchStorageLogs(1)" 
                                        class="bg-amber-800 hover:bg-amber-900 text-white px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all active:scale-95 flex items-center gap-2">
                                    <span>📋</span>
                                    <span>Buka Modal Log Rinci</span>
                                </button>
                                <form action="{{ route('admin.storage.refresh') }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Segarkan data storage" class="bg-stone-100 hover:bg-stone-200 text-amber-900 px-3 py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center border border-stone-200">
                                        <span>🔄</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Kartu Detail Metrik Storage --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div class="p-5 bg-[#FAF7F2] rounded-2xl border border-amber-200/60">
                                <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 block mb-1">Total Penyimpanan</span>
                                <div class="text-2xl font-black text-amber-950">{{ $storageStats['total_formatted'] }}</div>
                                <span class="text-[11px] text-stone-500 font-semibold mt-1 block">{{ number_format($storageStats['total_books']) }} berkas buku</span>
                            </div>
                            <div class="p-5 bg-[#FAF7F2] rounded-2xl border border-amber-200/60">
                                <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 block mb-1">Ukuran Berkas PDF</span>
                                <div class="text-2xl font-black text-amber-950">{{ $storageStats['pdf_formatted'] }}</div>
                                <span class="text-[11px] text-stone-500 font-semibold mt-1 block">Rerata {{ $storageStats['avg_book_size'] }}/buku</span>
                            </div>
                            <div class="p-5 bg-[#FAF7F2] rounded-2xl border border-amber-200/60">
                                <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 block mb-1">Ukuran Sampul Gambar</span>
                                <div class="text-2xl font-black text-amber-950">{{ $storageStats['cover_formatted'] }}</div>
                                <span class="text-[11px] text-emerald-800 font-semibold mt-1 block">{{ number_format($storageStats['total_books']) }} berkas cover</span>
                            </div>
                            <div class="p-5 bg-[#FAF7F2] rounded-2xl border border-amber-200/60">
                                <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 block mb-1">Kesehatan Integritas</span>
                                <div class="text-2xl font-black {{ $storageStats['missing_total'] == 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $storageStats['missing_total'] == 0 ? '100% Valid' : 'Peringatan' }}
                                </div>
                                <span class="text-[11px] text-stone-500 font-semibold mt-1 block truncate">
                                    {{ $storageStats['missing_total'] == 0 ? 'Semua berkas sinkron di disk' : $storageStats['missing_total'] . ' berkas hilang' }}
                                </span>
                            </div>
                        </div>

                        {{-- Panduan Audit Storage --}}
                        <div class="p-5 bg-amber-50/60 rounded-2xl border border-amber-200 text-xs text-amber-900 leading-relaxed">
                            <p class="font-bold flex items-center gap-1.5 mb-1.5">
                                <span>💡</span>
                                <span>Petunjuk Pengelolaan Kapasitas Server:</span>
                            </p>
                            <ul class="list-disc list-inside space-y-1 text-amber-800/90 font-medium">
                                <li>Seluruh berkas PDF baru otomatis dikompresi ke target &le; 1MB pada saat diunggah.</li>
                                <li>Untuk berkas lawas yang ukurannya masih di atas 1MB, gunakan tombol <strong>Kompresi &gt; 1MB</strong> di bilah atas untuk mengoptimasi secara massal.</li>
                                <li>Klik tombol <strong>Buka Modal Log Rinci</strong> untuk mencari dan menyortir file berdasarkan ukuran atau mendeteksi file yang hilang di disk.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Form Tersembunyi untuk Delete Buku --}}
        @foreach($books as $book)
            <form id="delete-form-{{ $book->id }}" action="{{ route('admin.books.destroy', $book->id) }}" method="POST" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
        
        @if($isAdmin)
            @foreach($petugas as $p)
                <form id="delete-petugas-{{ $p->id }}" action="{{ route('admin.petugas.destroy', $p->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            @endforeach

            <form id="deleteCategoryForm" method="POST" action="" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

        {{-- MODAL GANTI PASSWORD PETUGAS --}}
        <template x-teleport="body">
            <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 py-6">
                    <div class="fixed inset-0 bg-stone-950/70 backdrop-blur-sm" @click="openModal = false"></div>
                    <div class="bg-white rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl max-w-md w-full z-50 border border-stone-200 relative max-h-[90vh] overflow-y-auto"
                         x-show="openModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                        
                        <form :action="formAction" method="POST">
                            @csrf 
                            @method('PATCH')
                            <h3 class="text-xl sm:text-2xl font-black text-amber-950 uppercase tracking-tight mb-1">Ganti Sandi</h3>
                            <p class="text-xs text-stone-500 mb-6 font-semibold uppercase">Petugas: <span x-text="selectedAdmin.name" class="text-amber-800 font-black"></span></p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black text-amber-950 uppercase tracking-wider block mb-1">Kata Sandi Baru</label>
                                    <input type="password" name="password" class="w-full border-2 border-stone-200 bg-stone-50 rounded-2xl text-xs font-bold py-3 px-4 focus:border-amber-600 focus:bg-white" placeholder="••••••••" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-amber-950 uppercase tracking-wider block mb-1">Konfirmasi Kata Sandi</label>
                                    <input type="password" name="password_confirmation" class="w-full border-2 border-stone-200 bg-stone-50 rounded-2xl text-xs font-bold py-3 px-4 focus:border-amber-600 focus:bg-white" placeholder="••••••••" required>
                                </div>
                                <div class="pt-2">
                                    <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-white min-h-[44px] py-3 rounded-2xl font-black text-xs uppercase tracking-wider shadow-lg transition-all active:scale-95">
                                        Simpan Sandi Baru
                                    </button>
                                    <button type="button" @click="openModal = false" class="w-full text-stone-400 hover:text-stone-600 text-[10px] font-black uppercase mt-3 py-2 min-h-[44px] flex items-center justify-center">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL EDIT BUKU --}}
        <template x-teleport="body">
            <div x-show="openEditModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 py-6">
                    <div class="fixed inset-0 bg-stone-950/70 backdrop-blur-sm" @click="openEditModal = false"></div>
                    <div class="bg-white rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl max-w-md w-full z-50 border border-stone-200 relative max-h-[90vh] overflow-y-auto"
                         x-show="openEditModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                        
                        <form :action="editAction" method="POST">
                            @csrf 
                            @method('PATCH')
                            <h3 class="text-xl sm:text-2xl font-black text-amber-950 uppercase tracking-tight mb-2">Edit Data Koleksi</h3>
                            <p class="text-xs text-stone-500 mb-6 font-semibold">Perbarui judul atau lokasi rak buku</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black text-amber-950 uppercase tracking-wider block mb-1">Judul Buku</label>
                                    <input type="text" name="title" x-model="selectedBook.title" class="w-full border-2 border-stone-200 bg-stone-50 rounded-2xl text-xs font-bold py-3 px-4 focus:border-amber-600 focus:bg-white" required>
                                </div>
                                
                                <div>
                                    <label class="text-[10px] font-black text-amber-950 uppercase tracking-wider block mb-1">Lokasi Rak Buku</label>
                                    <select name="category_id" x-model="selectedBook.category_id" class="w-full border-2 border-stone-200 bg-stone-50 rounded-2xl text-xs font-bold py-3 px-4 focus:border-amber-600 focus:bg-white text-amber-950" required>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-white min-h-[44px] py-3 rounded-2xl font-black text-xs uppercase tracking-wider shadow-lg transition-all active:scale-95">
                                        Perbarui Inventaris
                                    </button>
                                    <button type="button" @click="openEditModal = false" class="w-full text-stone-400 hover:text-stone-600 text-[10px] font-black uppercase mt-3 py-2 min-h-[44px] flex items-center justify-center">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL LOG STORAGE DATABASE & BERKAS (Super Admin) --}}
        @if($isAdmin)
        <template x-teleport="body">
            <div x-show="openStorageModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-3 md:px-6 py-6">
                    <div class="fixed inset-0 bg-stone-950/75 backdrop-blur-sm" @click="openStorageModal = false"></div>
                    
                    <div class="bg-white rounded-[32px] p-6 md:p-8 shadow-2xl max-w-5xl w-full z-50 border border-stone-200 relative max-h-[92vh] flex flex-col"
                         x-show="openStorageModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                         
                        <!-- Modal Header -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 pb-4 border-b border-stone-100 shrink-0">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">📦</span>
                                    <h3 class="text-lg md:text-xl font-black text-amber-950 uppercase tracking-tight">Log Berkas &amp; Storage Database</h3>
                                    <span class="bg-amber-100 text-amber-900 text-[10px] font-black px-2.5 py-0.5 rounded-lg uppercase tracking-wider border border-amber-200">
                                        Super Admin
                                    </span>
                                </div>
                                <p class="text-xs text-stone-500 mt-1 font-medium">Audit ukuran berkas PDF dan sampul visual yang tersimpan di disk lokal.</p>
                            </div>
                            <button @click="openStorageModal = false" class="text-stone-400 hover:text-stone-600 text-2xl font-bold p-1 self-end md:self-auto leading-none">&times;</button>
                        </div>

                        <!-- Modal Filter & Pencarian -->
                        <div class="py-4 grid grid-cols-1 md:grid-cols-3 gap-3 shrink-0">
                            <div>
                                <input type="text" 
                                       x-model="storageSearch" 
                                       @input.debounce.400ms="fetchStorageLogs(1)"
                                       placeholder="Cari judul buku atau nama file..." 
                                       class="w-full text-xs font-bold border-2 border-stone-200 bg-stone-50 rounded-xl focus:border-amber-600 focus:bg-white focus:ring-0 py-2.5 px-3.5">
                            </div>
                            <div>
                                <select x-model="storageCategory" 
                                        @change="fetchStorageLogs(1)"
                                        class="w-full text-xs font-bold text-amber-950 border-2 border-stone-200 bg-stone-50 rounded-xl focus:border-amber-600 focus:bg-white focus:ring-0 py-2.5 px-3.5">
                                    <option value="">-- Semua Rak Koleksi --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <select x-model="storageFilter" 
                                        @change="fetchStorageLogs(1)"
                                        class="w-full text-xs font-bold text-amber-950 border-2 border-stone-200 bg-stone-50 rounded-xl focus:border-amber-600 focus:bg-white focus:ring-0 py-2.5 px-3.5">
                                    <option value="all">Semua Ukuran Berkas</option>
                                    <option value="over_1mb">Khusus Berkas &gt; 1MB</option>
                                    <option value="missing">Khusus Berkas Hilang di Disk</option>
                                </select>
                                <button type="button" @click="fetchStorageLogs(storagePagination.current_page)" class="bg-amber-100 hover:bg-amber-200 text-amber-950 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0" title="Segarkan Data">
                                    🔄
                                </button>
                            </div>
                        </div>

                        <!-- Modal Table Content -->
                        <div class="overflow-y-auto flex-1 min-h-[300px] border border-stone-200 rounded-2xl relative">
                            <!-- Loading Overlay -->
                            <div x-show="storageLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[2px] flex items-center justify-center z-10" x-cloak>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-8 h-8 border-3 border-amber-800 border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-xs font-bold text-amber-950">Memuat log storage...</span>
                                </div>
                            </div>

                            <table class="w-full text-left text-xs">
                                <thead class="bg-stone-50 text-[10px] font-black uppercase text-stone-500 tracking-wider sticky top-0 border-b border-stone-200">
                                    <tr>
                                        <th class="px-4 py-3">Buku &amp; Rak</th>
                                        <th class="px-4 py-3">Berkas PDF</th>
                                        <th class="px-4 py-3">Berkas Sampul</th>
                                        <th class="px-4 py-3">Total Ukuran</th>
                                        <th class="px-4 py-3">Waktu Upload</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    <template x-for="item in storageLogs" :key="item.id">
                                        <tr class="hover:bg-amber-50/40 transition-colors">
                                            <td class="px-4 py-3 font-semibold text-amber-950">
                                                <div class="font-black text-xs uppercase" x-text="item.title"></div>
                                                <div class="text-[10px] text-amber-800 font-bold mt-0.5" x-text="'📌 ' + item.category"></div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold" x-text="item.pdf_size"></span>
                                                    <span x-show="item.is_over_1mb" class="text-[9px] bg-amber-100 text-amber-950 font-black px-1.5 py-0.5 rounded border border-amber-300">
                                                        &gt;1MB
                                                    </span>
                                                    <span x-show="!item.is_over_1mb && item.pdf_exists" class="text-[9px] bg-emerald-50 text-emerald-800 font-black px-1.5 py-0.5 rounded">
                                                        &le;1MB
                                                    </span>
                                                </div>
                                                <div class="text-[9px] text-stone-400 truncate max-w-[180px]" :title="item.pdf_file" x-text="item.pdf_file"></div>
                                                <span x-show="!item.pdf_exists" class="text-[9px] font-black text-rose-600 block mt-0.5">❌ Berkas fisik hilang di disk</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-bold" x-text="item.cover_size"></div>
                                                <div class="text-[9px] text-stone-400 truncate max-w-[180px]" :title="item.cover" x-text="item.cover"></div>
                                                <span x-show="!item.cover_exists" class="text-[9px] font-black text-rose-600 block mt-0.5">❌ Sampul fisik hilang di disk</span>
                                            </td>
                                            <td class="px-4 py-3 font-black text-amber-950">
                                                <span x-text="item.total_size"></span>
                                            </td>
                                            <td class="px-4 py-3 text-stone-500 text-[10px]" x-text="item.created_at"></td>
                                            <td class="px-4 py-3 text-center">
                                                <a :href="item.read_url" target="_blank" class="inline-block bg-amber-800 hover:bg-amber-900 text-white px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider shadow-sm transition-colors">
                                                    Buka
                                                </a>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!storageLoading && storageLogs.length === 0">
                                        <td colspan="6" class="px-4 py-12 text-center text-stone-400 text-xs italic">
                                            Tidak ditemukan catatan berkas sesuai filter yang dipilih.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Modal Footer with Pagination -->
                        <div class="pt-4 border-t border-stone-100 flex flex-col sm:flex-row justify-between items-center gap-3 shrink-0 text-xs">
                            <div class="text-stone-500 font-medium">
                                Menampilkan <span class="font-bold text-amber-950" x-text="storagePagination.from || 0"></span> - 
                                <span class="font-bold text-amber-950" x-text="storagePagination.to || 0"></span> dari 
                                <span class="font-bold text-amber-950" x-text="storagePagination.total || 0"></span> koleksi terdaftar
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        :disabled="storagePagination.current_page <= 1"
                                        @click="fetchStorageLogs(storagePagination.current_page - 1)" 
                                        class="px-3.5 py-1.5 rounded-xl border bg-white font-bold text-amber-950 hover:bg-amber-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                                    &laquo; Prev
                                </button>
                                <span class="px-3 py-1.5 font-black text-amber-950">
                                    Hal <span x-text="storagePagination.current_page"></span> / <span x-text="storagePagination.last_page || 1"></span>
                                </span>
                                <button type="button" 
                                        :disabled="storagePagination.current_page >= storagePagination.last_page"
                                        @click="fetchStorageLogs(storagePagination.current_page + 1)" 
                                        class="px-3.5 py-1.5 rounded-xl border bg-white font-bold text-amber-950 hover:bg-amber-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                                    Next &raquo;
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        @endif
    </div>

    <script>
        // Fitur Salin Judul Satu Per Satu
        function copyToClipboard(text, element) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHTML = element.innerHTML;
                element.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                     </svg>`;
                setTimeout(() => {
                    element.innerHTML = originalHTML;
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin: ', err);
            });
        }

        // Fitur Salin Seluruh Judul Buku di Halaman Ini (Deduplikasi Mobile + Desktop)
        function copyAllTitles(button) {
            const rawTitles = Array.from(document.querySelectorAll('.book-title-text'))
                                  .map(el => el.innerText.trim())
                                  .filter(title => title.length > 0);
            const titles = Array.from(new Set(rawTitles)).join('\n');
            
            if (titles.length === 0) {
                alert('Tidak ada judul buku untuk disalin.');
                return;
            }

            navigator.clipboard.writeText(titles).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '<span>✅</span><span>Tersalin!</span>';
                button.classList.replace('bg-amber-700', 'bg-emerald-700');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.replace('bg-emerald-700', 'bg-amber-700');
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin semua: ', err);
            });
        }

        @if($isAdmin)
        function handleCategoryChange(select) {
            const btn = document.getElementById('btnDeleteCategory');
            if (btn) {
                if (select.value) {
                    btn.classList.remove('hidden');
                    btn.classList.add('inline-flex');
                } else {
                    btn.classList.add('hidden');
                    btn.classList.remove('inline-flex');
                }
            }
        }

        function deleteSelectedCategory() {
            const select = document.getElementById('category_select');
            if (!select || !select.value) {
                alert('Silakan pilih kategori yang ingin dihapus terlebih dahulu.');
                return;
            }

            const selectedOption = select.options[select.selectedIndex];
            const categoryName = selectedOption ? (selectedOption.getAttribute('data-name') || selectedOption.text) : '';
            const categoryId = select.value;

            if (confirm(`Apakah Anda yakin ingin menghapus kategori "${categoryName}" secara permanen dari database?`)) {
                const form = document.getElementById('deleteCategoryForm');
                form.action = `{{ url('/admin/category') }}/${categoryId}`;
                form.submit();
            }
        }
        @endif

        document.addEventListener('DOMContentLoaded', () => {
            // -- LOGIKA SELECT ALL CHECKBOX (DESKTOP & MOBILE) --
            const selectAll = document.getElementById('select_all_books');
            const selectAllMobile = document.getElementById('select_all_books_mobile');
            const checkboxes = document.querySelectorAll('.book-checkbox');

            function syncCheckboxes(checkedState) {
                checkboxes.forEach(cb => {
                    cb.checked = checkedState;
                });
                if (selectAll) selectAll.checked = checkedState;
                if (selectAllMobile) selectAllMobile.checked = checkedState;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    syncCheckboxes(selectAll.checked);
                });
            }

            if (selectAllMobile) {
                selectAllMobile.addEventListener('change', function() {
                    syncCheckboxes(selectAllMobile.checked);
                });
            }

            // -- LOGIKA KOMPRESI PDF OTOMATIS KE <= 1MB --
            async function compressPdfTo1MB(file) {
                const targetMaxBytes = 1024 * 1024; // 1 MB
                if (file.size <= targetMaxBytes) {
                    return file;
                }

                const PDFLibDoc = window.PDFLib ? window.PDFLib.PDFDocument : (typeof PDFDocument !== 'undefined' ? PDFDocument : null);
                if (!PDFLibDoc || !pdfjsLib) {
                    console.warn('PDF-Lib atau PDF.js belum siap, melewati kompresi otomatis.');
                    return file;
                }

                const container = document.getElementById('compression_status_container');
                const statusText = document.getElementById('compression_status_text');
                const percentage = document.getElementById('compression_percentage');
                const progressBar = document.getElementById('compression_progress_bar');
                const detail = document.getElementById('compression_detail');
                const submitBtn = document.getElementById('submitBtn');

                if (container) {
                    container.className = 'mt-4 p-4 rounded-2xl border bg-amber-50 border-amber-300';
                    container.classList.remove('hidden');
                }
                if (submitBtn) submitBtn.disabled = true;

                const originalMB = (file.size / (1024 * 1024)).toFixed(2);
                if (detail) detail.textContent = `Ukuran awal: ${originalMB} MB. Target kompresi: ≤ 1 MB.`;

                try {
                    const arrayBuffer = await file.arrayBuffer();
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
                    const loadingTask = pdfjsLib.getDocument(new Uint8Array(arrayBuffer));
                    const pdfDocOriginal = await loadingTask.promise;
                    const numPages = pdfDocOriginal.numPages;

                    let scale = 1.0;
                    let quality = 0.70;
                    if (numPages > 50) {
                        scale = 0.70;
                        quality = 0.45;
                    } else if (numPages > 25) {
                        scale = 0.80;
                        quality = 0.55;
                    } else if (numPages > 10) {
                        scale = 0.90;
                        quality = 0.65;
                    }

                    const newPdfDoc = await PDFLibDoc.create();

                    for (let i = 1; i <= numPages; i++) {
                        const progress = Math.round((i / numPages) * 100);
                        if (percentage) percentage.textContent = `${progress}%`;
                        if (progressBar) progressBar.style.width = `${progress}%`;
                        if (statusText) statusText.innerHTML = `<span class="animate-spin inline-block">⚡</span> Mengompres halaman ${i} dari ${numPages}...`;

                        const page = await pdfDocOriginal.getPage(i);
                        const viewport = page.getViewport({ scale: scale });

                        const canvasTemp = document.createElement('canvas');
                        canvasTemp.width = viewport.width;
                        canvasTemp.height = viewport.height;
                        const ctx = canvasTemp.getContext('2d', { alpha: false });

                        await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                        const imgDataUrl = canvasTemp.toDataURL('image/jpeg', quality);
                        const imgBytes = await fetch(imgDataUrl).then(res => res.arrayBuffer());
                        const embeddedImg = await newPdfDoc.embedJpg(imgBytes);

                        const originalViewport = page.getViewport({ scale: 1.0 });
                        const newPage = newPdfDoc.addPage([originalViewport.width, originalViewport.height]);
                        newPage.drawImage(embeddedImg, {
                            x: 0,
                            y: 0,
                            width: originalViewport.width,
                            height: originalViewport.height,
                        });
                    }

                    const compressedPdfBytes = await newPdfDoc.save();
                    const compressedMB = (compressedPdfBytes.length / (1024 * 1024)).toFixed(2);
                    const compressedKB = Math.round(compressedPdfBytes.length / 1024);

                    if (compressedPdfBytes.length < file.size) {
                        const compressedFile = new File([compressedPdfBytes], file.name, { type: 'application/pdf' });
                        
                        if (container) container.className = 'mt-4 p-4 rounded-2xl border bg-emerald-50 border-emerald-300';
                        if (statusText) statusText.innerHTML = `✅ Berhasil dikompresi: ${originalMB} MB ➔ ${compressedKB < 1024 ? compressedKB + ' KB' : compressedMB + ' MB'}`;
                        if (percentage) percentage.textContent = '100%';
                        if (progressBar) {
                            progressBar.className = 'bg-emerald-600 h-2.5 rounded-full';
                            progressBar.style.width = '100%';
                        }
                        if (detail) detail.textContent = 'Berkas terkompresi optimal dan siap disimpan ke sistem.';

                        return compressedFile;
                    } else {
                        if (statusText) statusText.textContent = `ℹ️ Ukuran berkas optimal (${originalMB} MB).`;
                        return file;
                    }
                } catch (err) {
                    console.error('Kompresi PDF Error:', err);
                    if (statusText) statusText.textContent = '⚠️ Kompresi dilewati (menggunakan berkas asli).';
                    return file;
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            }

            // -- LOGIKA PDF PREVIEW & KOMPRESI INPUT --
            const pdfInput = document.getElementById('pdf_input');
            const generatedCoverInput = document.getElementById('generated_cover');
            const canvas = document.getElementById('pdf_canvas');
            const previewImg = document.getElementById('preview_img');
            const placeholderText = document.getElementById('placeholder_text');
            const loadingSpinner = document.getElementById('loading_spinner');
            const submitBtn = document.getElementById('submitBtn');

            const pdfjsLib = window['pdfjs-dist/build/pdf'] || window.pdfjsLib;

            if (pdfInput) {
                pdfInput.addEventListener('change', async function() {
                    let file = this.files[0];
                    if (!file) return;

                    if (file.size > 100 * 1024 * 1024) {
                        alert("Ukuran file terlalu besar! Maksimal 100MB.");
                        this.value = "";
                        return;
                    }

                    if (file.type === "application/pdf") {
                        // 1. Generate Cover Preview
                        loadingSpinner.classList.remove('hidden');
                        placeholderText.classList.add('hidden');
                        previewImg.classList.add('hidden');
                        submitBtn.disabled = true;

                        try {
                            const arrayBuffer = await file.arrayBuffer();
                            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
                            const loadingTask = pdfjsLib.getDocument(new Uint8Array(arrayBuffer));
                            const pdf = await loadingTask.promise;
                            const page = await pdf.getPage(1);
                            const viewport = page.getViewport({ scale: 1.0 });
                            const context = canvas.getContext('2d', { alpha: false });
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            const renderContext = { canvasContext: context, viewport: viewport };
                            await page.render(renderContext).promise;
                            const imageData = canvas.toDataURL('image/jpeg', 0.7); 
                            generatedCoverInput.value = imageData;
                            previewImg.src = imageData;
                            previewImg.classList.remove('hidden');
                        } catch (error) {
                            console.error("PDF Preview Error:", error);
                            alert("Gagal memproses pratinjau PDF.");
                            placeholderText.classList.remove('hidden');
                        } finally {
                            loadingSpinner.classList.add('hidden');
                            submitBtn.disabled = false;
                        }

                        // 2. Jika ukuran file > 1MB, otomatis kompresi ke target <= 1MB
                        if (file.size > 1024 * 1024) {
                            const compressed = await compressPdfTo1MB(file);
                            if (compressed && compressed !== file && typeof DataTransfer !== 'undefined') {
                                const dt = new DataTransfer();
                                dt.items.add(compressed);
                                this.files = dt.files;
                            }
                        }
                    }
                });
            }
        });
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .overflow-x-auto::-webkit-scrollbar { height: 6px; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background: #b45309; border-radius: 20px; }
        #preview_img { transition: opacity 0.3s ease; }
        .book-checkbox { width: 1.15rem; height: 1.15rem; transition: all 0.2s ease; }
    </style>
</x-app-layout>
