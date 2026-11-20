<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIPINTAR - Speland Digital Library</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html, body {
            max-width: 100vw;
            overflow-x: hidden;
            position: relative;
        }

        body {
            background-color: #24140b;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(138, 77, 36, 0.25) 0%, transparent 75%),
                url('https://www.transparenttextures.com/patterns/wood-pattern.png');
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #fde68a;
        }

        /* Lemari & Rak Buku Realistis (Konsep FlipHTML5 Catalog) */
        .bookcase-frame {
            background: #1c0e07;
            border: 14px solid #2e170c;
            border-top: 18px solid #3a1e10;
            border-bottom: 24px solid #1a0c06;
            border-radius: 12px;
            box-shadow: 
                0 25px 60px -15px rgba(0, 0, 0, 0.9), 
                inset 0 0 60px rgba(0, 0, 0, 0.85);
            box-sizing: border-box;
            max-width: 100%;
        }

        .shelf-bay {
            position: relative;
            background: linear-gradient(180deg, #2b170c 0%, #3e2213 45%, #4a2816 85%, #351c0f 100%);
            box-shadow: 
                inset 0 25px 35px -10px rgba(0, 0, 0, 0.7),
                inset 0 -15px 25px rgba(0, 0, 0, 0.5);
            padding: 40px 24px 20px 24px;
            min-height: 330px;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 24px;
            justify-items: center;
            align-items: start;
            box-sizing: border-box;
            max-width: 100%;
        }

        @media (max-width: 1024px) {
            .shelf-bay {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 20px;
                padding: 35px 16px 18px 16px;
            }
        }

        @media (max-width: 640px) {
            .bookcase-frame {
                border-width: 6px !important;
                border-top-width: 10px !important;
                border-bottom-width: 14px !important;
                border-radius: 8px !important;
            }
            .shelf-bay {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 10px !important;
                padding: 20px 8px 14px 8px !important;
                min-height: auto !important;
            }
            .shelf-plaque {
                font-size: 0.58rem !important;
                padding: 2px 12px !important;
                letter-spacing: 1.5px !important;
            }
            .shelf-wood-beam {
                height: 22px !important;
                margin-bottom: 24px !important;
            }
            .book-wrapper {
                width: 100% !important;
                max-width: 135px !important;
                display: flex !important;
                justify-content: center !important;
            }
            .book-card {
                width: 100% !important;
                max-width: 125px !important;
            }
            .book-cover-wrapper {
                height: 160px !important;
            }
            .book-plaque-title {
                font-size: 0.58rem !important;
                height: 42px !important;
                padding: 2px 4px !important;
                line-height: 1.2 !important;
                margin-top: 6px !important;
            }
            .badge-status {
                top: 5px !important;
                right: 5px !important;
                padding: 2px 6px !important;
                font-size: 7px !important;
            }
            .book-views {
                font-size: 9px !important;
                margin-top: 4px !important;
            }
        }

        /* Papan Kayu Penyangga Rak (Wooden Plank Beam) */
        .shelf-wood-beam {
            position: relative;
            height: 28px;
            background: linear-gradient(180deg, #5a321c 0%, #3d2112 50%, #25130a 100%);
            border-top: 3px solid rgba(255, 255, 255, 0.2);
            border-bottom: 5px solid #140904;
            box-shadow: 
                0 18px 25px rgba(0, 0, 0, 0.75),
                inset 0 2px 4px rgba(255, 255, 255, 0.15);
            margin-bottom: 50px;
        }

        /* Plat Kuningan / Plaque Nomor Rak */
        .shelf-plaque {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(180deg, #2b170c 0%, #150a04 100%);
            color: #fbbf24;
            padding: 3px 24px;
            border-radius: 0 0 12px 12px;
            font-size: 0.75rem;
            font-weight: 900;
            letter-spacing: 3px;
            border: 1px solid rgba(251, 191, 36, 0.35);
            border-top: none;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
            white-space: nowrap;
            z-index: 25;
            text-transform: uppercase;
        }

        /* Tampilan Buku Realistis Berdiri di Rak */
        .book-wrapper {
            position: relative;
            margin-bottom: 0px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 10;
        }

        .book-wrapper:hover {
            transform: translateY(-10px) scale(1.04);
            z-index: 40;
        }

        .book-card {
            width: 140px;
            cursor: pointer;
            filter: drop-shadow(0 12px 18px rgba(0, 0, 0, 0.75));
            display: flex;
            flex-direction: column;
        }

        .book-cover-wrapper {
            position: relative;
            width: 100%;
            height: 210px;
            border-radius: 3px 8px 8px 3px;
            overflow: hidden;
            background: #2b170c;
            box-shadow: inset 3px 0 5px rgba(0, 0, 0, 0.35);
        }

        .book-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-left: 6px solid rgba(255, 255, 255, 0.35);
        }

        /* Label Judul Buku di Bawah Sampul (Sejajar Rapi dan Presisi) */
        .book-plaque-title {
            font-size: 0.65rem;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            margin-top: 8px;
            background: linear-gradient(180deg, rgba(28, 14, 7, 0.95), rgba(10, 5, 2, 0.98));
            border: 1px solid rgba(251, 191, 36, 0.25);
            padding: 5px 7px;
            border-radius: 6px;
            height: 52px;
            line-height: 1.25;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            word-break: break-word;
            overflow-wrap: break-word;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .book-plaque-title span {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Indikator Pembaca (Tinggi Presisi Sejajar Horisontal) */
        .book-views {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 9.5px;
            font-weight: 800;
            color: rgba(251, 191, 36, 0.85);
            margin-top: 5px;
            height: 16px;
        }

        /* Tagline Status Viral & New (Floating Capsule Glass) */
        .badge-status {
            position: absolute;
            top: 7px;
            right: 7px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #ffffff;
            z-index: 25;
            backdrop-filter: blur(8px);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .badge-viral {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.95), rgba(220, 38, 38, 0.95));
            border: 1px solid rgba(254, 215, 170, 0.6);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.55), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .badge-new {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(185, 28, 28, 0.95));
            border: 1px solid rgba(254, 202, 202, 0.6);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.55), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .book-wrapper:hover .badge-status {
            transform: scale(1.08) translateY(-1px);
        }

        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-amber-500 selection:text-amber-950">

    {{-- Top Navigation Bar --}}
    <nav class="bg-[#1c0e07] text-white shadow-2xl sticky top-0 z-[100] border-b-4 border-[#3a1e10] w-full max-w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 w-full">
            <div class="flex items-center justify-between h-16 sm:h-20 relative w-full">
                
                {{-- Identitas SIPINTAR --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 z-10 shrink-0">
                    <img src="{{ asset('img/SIPINTAR TRANS.png') }}" alt="Logo SIPINTAR" class="h-9 sm:h-12 w-auto">
                    <img src="{{ asset('img/LOGO SMP 9 TRANS.png') }}" alt="Logo SMP 9" class="h-8 sm:h-10 w-auto sm:hidden">
                    <div class="hidden sm:block leading-tight">
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-black text-amber-100 tracking-tight leading-none">SIPINTAR</span>
                        </div>
                        <p class="text-[10px] text-amber-300/80 uppercase tracking-widest font-bold">Speland Digital Library</p>
                    </div>
                </a>

                {{-- Center Logo SMPN 9 (Desktop & Tablet) --}}
                <div class="hidden sm:block absolute left-1/2 -translate-x-1/2 z-0 pointer-events-none">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('img/LOGO SMP 9 TRANS.png') }}" 
                             alt="Logo SMP 9" 
                             class="h-12 sm:h-14 w-auto drop-shadow-xl hover:scale-105 transition-transform">
                    </a>
                </div>

                {{-- Aksi Navigasi --}}
                <div class="flex items-center gap-1.5 sm:gap-3 z-10 shrink-0">
                    <a href="{{ route('books.leaderboard') }}" 
                       class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 h-10 sm:min-h-[44px] rounded-xl text-xs font-bold text-amber-200 bg-[#3a1e10] hover:bg-[#4a2816] border border-amber-700/40 transition-all shadow-md active:scale-95"
                       title="Leaderboard">
                        <span>🏆</span>
                        <span class="hidden sm:inline">Leaderboard</span>
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 h-10 sm:min-h-[44px] rounded-xl text-xs font-black text-white bg-amber-700 hover:bg-amber-600 shadow-lg uppercase tracking-wider transition-all active:scale-95">
                            <span>🚀</span>
                            <span class="hidden min-[380px]:inline">Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login', ['token' => 'speland-2026-aman']) }}" 
                           class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 h-10 sm:min-h-[44px] rounded-xl text-xs font-black text-amber-200 hover:text-white bg-[#2e170c] hover:bg-[#3a1e10] border border-amber-700/50 shadow-md uppercase tracking-wider transition-all active:scale-95">
                            <span>🔐</span>
                            <span class="hidden sm:inline">Masuk Petugas</span>
                            <span class="sm:hidden">Masuk</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Sub-Header: Search & Kategori (Langsung Terhubung ke Rak) --}}
    <div class="bg-[#180c06]/95 backdrop-blur-sm border-b border-amber-950 py-3 sm:py-4 px-3 sm:px-4 sticky top-16 sm:top-20 z-40 shadow-xl w-full max-w-full overflow-hidden">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3 sm:gap-4 w-full min-w-0">
            
            {{-- Search Bar --}}
            <form action="{{ route('home') }}" method="GET" id="searchForm" class="w-full md:w-96 relative min-w-0">
                <div class="relative flex items-center bg-[#2b170c] border border-amber-900/60 rounded-xl overflow-hidden focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20 shadow-inner w-full">
                    <span class="pl-3.5 text-amber-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" 
                           name="search" 
                           id="searchInput" 
                           value="{{ request('search') }}" 
                           placeholder="Cari judul buku di rak..." 
                           onkeyup="toggleClearButton()" 
                           class="w-full py-2.5 pl-3 pr-20 bg-transparent border-0 focus:ring-0 text-xs font-bold text-amber-100 placeholder-amber-600/60 min-w-0">
                    
                    <div class="absolute right-2 flex items-center gap-1 shrink-0">
                        <button type="button" 
                                id="clearBtn" 
                                onclick="clearSearch()" 
                                class="{{ request('search') ? '' : 'hidden' }} p-1 text-amber-400/60 hover:text-amber-200" 
                                title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <button type="submit" 
                                class="bg-amber-600 hover:bg-amber-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider shadow">
                            Cari
                        </button>
                    </div>
                </div>
            </form>

            {{-- Kategori / Rak Tabs --}}
            @if(isset($categories) && count($categories))
            <div class="w-full md:w-auto min-w-0 max-w-full overflow-hidden">
                <div class="flex items-center gap-2 overflow-x-auto w-full scrollbar-none py-1 touch-pan-x">
                    <a href="{{ route('home') }}" 
                       class="shrink-0 px-3.5 py-2 sm:py-1.5 min-h-[38px] inline-flex items-center rounded-xl text-xs sm:text-[11px] font-black uppercase tracking-wider transition border {{ !request('category') ? 'bg-amber-600 text-white border-amber-500 shadow-md' : 'bg-[#2b170c] text-amber-200 hover:bg-[#3e2213] border-amber-900/60' }}">
                        Semua Rak
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('home', ['category' => $category]) }}" 
                           class="shrink-0 px-3.5 py-2 sm:py-1.5 min-h-[38px] inline-flex items-center rounded-xl text-xs sm:text-[11px] font-black uppercase tracking-wider transition border {{ request('category') == $category ? 'bg-amber-600 text-white border-amber-500 shadow-md' : 'bg-[#2b170c] text-amber-200 hover:bg-[#3e2213] border-amber-900/60' }}">
                            <span>📌 {{ $category }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Lemari Buku Kayu Utama (Bookcase Container) --}}
    <main class="w-full max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-5 sm:py-8 flex-grow overflow-hidden box-border">
        @php 
            $booksPerPage = $books->perPage();
            $booksPerShelf = 5;
            $shelvesPerPage = ceil($booksPerPage / $booksPerShelf);
            $globalShelfCount = (($books->currentPage() - 1) * $shelvesPerPage) + 1; 
        @endphp

        <div class="bookcase-frame p-1 sm:p-4 w-full box-border">
            @forelse($booksByCategory as $category => $items)
                <div class="category-block mb-4">
                    {{-- Judul Kategori di Dinding Lemari --}}
                    @if(request('category'))
                        <div class="text-center py-4">
                            <h2 class="text-xl sm:text-2xl font-black text-amber-300 uppercase tracking-widest drop-shadow-md">
                                {{ $category }}
                            </h2>
                            <div class="w-16 h-1 bg-amber-600 mx-auto mt-1 rounded-full opacity-70"></div>
                        </div>
                    @endif

                    {{-- Setiap Baris Rak Buku (Shelf Bay) --}}
                    @foreach($items->chunk(5) as $chunk)
                        <div class="shelf-wrapper relative">
                            {{-- Ruang Kompartemen Buku --}}
                            <div class="shelf-bay">
                                @foreach($chunk as $book)
                                    <div class="book-wrapper">
                                        <a href="{{ route('books.read', Vinkla\Hashids\Facades\Hashids::encode($book->id)) }}" 
                                           class="book-card block" 
                                           title="{{ $book->title }}">
                                            
                                            {{-- Sampul Buku & Badge Status --}}
                                            <div class="book-cover-wrapper">
                                                @if($book->created_at && $book->created_at->diffInDays(now()) <= 7)
                                                    <span class="badge-status badge-new">
                                                        <span>✨</span>
                                                        <span>Baru</span>
                                                    </span>
                                                @elseif($book->views > 100)
                                                    <span class="badge-status badge-viral">
                                                        <span>🔥</span>
                                                        <span>Viral</span>
                                                    </span>
                                                @endif

                                                <img src="{{ asset($book->cover) }}" 
                                                     alt="{{ $book->title }}" 
                                                     class="book-cover"
                                                     loading="lazy"
                                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($book->title) }}&background=2b170c&color=fbbf24&size=210';">
                                            </div>
                                            
                                            {{-- Label Judul di Bawah Sampul (Presisi & Rata Sempurna) --}}
                                            <div class="book-plaque-title">
                                                <span>{{ $book->title }}</span>
                                            </div>

                                            {{-- Indikator Pembaca --}}
                                            <div class="book-views">
                                                <span>👁️</span>
                                                <span>{{ number_format($book->views ?? 0) }}</span>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Balok Kayu Penyangga Rak (Shelf Beam) & Plakat Nomor Rak --}}
                            <div class="shelf-wood-beam">
                                <div class="shelf-plaque">
                                    RAK {{ sprintf('%02d', $globalShelfCount++) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="py-24 text-center">
                    <span class="text-5xl block mb-3">📂</span>
                    <h3 class="text-lg font-black text-amber-200 uppercase tracking-wider mb-2">Buku Tidak Ditemukan</h3>
                    <p class="text-xs text-amber-400/70 mb-6">
                        {{ request('search') ? 'Buku dengan kata kunci "' . request('search') . '" tidak ditemukan di rak.' : 'Belum ada koleksi buku yang terdaftar.' }}
                    </p>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-amber-700 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider shadow-lg transition-all active:scale-95">
                        Tampilkan Semua Rak
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($books->hasPages())
            <div class="mt-8 mb-6 flex justify-center">
                <div class="bg-[#1c0e07] p-2 rounded-2xl shadow-xl border border-amber-900/60 max-w-2xl w-full">
                    {{ $books->appends(request()->input())->links('vendor.pagination.wood') }}
                </div>
            </div>
        @endif
    </main>

    {{-- Tombol Scroll Ke Atas --}}
    <button id="scrollTopBtn" 
            onclick="scrollToTop()" 
            class="hidden fixed bottom-6 right-6 z-50 bg-amber-700 hover:bg-amber-600 text-white p-3.5 rounded-2xl shadow-2xl transition-all duration-300 hover:scale-110 active:scale-95 border border-amber-500/50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
        </svg>
    </button>

    {{-- Footer --}}
    <footer class="bg-[#150a04] text-amber-300/80 text-center py-8 text-[10px] border-t-4 border-[#2e170c] mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <p class="font-black tracking-[0.3em] uppercase mb-1">&copy; {{ date('Y') }} SIPINTAR - SMP NEGERI 9 PROBOLINGGO</p>
            <p class="text-amber-500/70 tracking-widest uppercase">Speland Digital Library</p>
        </div>
    </footer>

    <script>
        function toggleClearButton() {
            const input = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearBtn');
            if(clearBtn) clearBtn.classList.toggle('hidden', input.value.length === 0);
        }
        function clearSearch() { window.location.href = "{{ route('home') }}"; }

        const scrollBtn = document.getElementById("scrollTopBtn");
        window.onscroll = function() {
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                scrollBtn.classList.remove("hidden");
            } else {
                scrollBtn.classList.add("hidden");
            }
        };
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: "smooth" });
        }

        document.addEventListener('DOMContentLoaded', toggleClearButton);
    </script>
</body>
</html>
