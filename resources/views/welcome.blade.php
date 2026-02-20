<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIPINTAR - Speland Digital Library</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background-color: #f3f4f6;
            background-image: url('https://www.transparenttextures.com/patterns/wood-pattern.png');
            font-family: 'Figtree', sans-serif;
        }
        .category-section { margin-bottom: 80px; }
        .shelf-group { margin-bottom: 60px; position: relative; }
        .shelf-container {
            background: #4a2c1d; 
            border-bottom: 25px solid #2d1a10; 
            border-left: 12px solid #3d2418;
            border-right: 12px solid #3d2418;
            padding: 50px 40px 0px 40px; 
            box-shadow: inset 0 -20px 40px rgba(0,0,0,0.6), 0 15px 25px rgba(0,0,0,0.4);
            display: grid;
            grid-template-columns: repeat(5, 1fr); 
            gap: 30px;
            justify-items: center;
            align-items: end; 
            min-height: 320px;
        }
        .shelf-label {
            position: absolute; bottom: -40px; left: 50%; transform: translateX(-50%);
            background: #2d1a10; color: #fbbf24; padding: 4px 25px; border-radius: 0 0 15px 15px;
            font-size: 0.75rem; font-weight: 900; letter-spacing: 3px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); border: 1px solid rgba(251, 191, 36, 0.2);
            white-space: nowrap; z-index: 20;
        }
        .book-wrapper { position: relative; margin-bottom: -5px; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 10; }
        .book-wrapper:hover { transform: translateY(-35px) scale(1.1); z-index: 50; }
        .book-card { width: 140px; filter: drop-shadow(8px 5px 10px rgba(0,0,0,0.5)); cursor: pointer; }
        
        .book-card img { 
            width: 100%; 
            height: 210px; 
            object-fit: cover; 
            border-radius: 3px 8px 8px 3px; 
            border-left: 6px solid rgba(255,255,255,0.3); 
            background: #3d2418;
            display: block;
        }

        .book-title {
            font-size: 0.7rem; font-weight: 800; color: #fff; text-align: center; margin-top: 8px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.9));
            padding: 5px; border-radius: 5px; height: 40px; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.2;
        }
        .download-btn { position: absolute; bottom: -35px; left: 50%; transform: translateX(-50%); opacity: 0; transition: all 0.3s ease; white-space: nowrap; }
        .book-wrapper:hover .download-btn { opacity: 1; bottom: -30px; }

        @media (max-width: 1024px) { .shelf-container { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 640px) {
            .shelf-container { grid-template-columns: repeat(2, 1fr); gap: 15px; padding: 40px 15px 0px 15px; }
            .book-card { width: 110px; } .book-card img { height: 160px; }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-amber-200 selection:text-amber-900">

    <nav class="bg-amber-950 text-white shadow-2xl p-4 sticky top-0 z-[100] border-b-4 border-amber-900">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('img/SIPINTAR TRANS.png') }}" alt="Logo" class="h-10 w-auto">
                <div>
                    <h1 class="text-xl font-black tracking-tighter leading-none">SIPINTAR</h1>
                    <p class="text-[10px] text-amber-200 uppercase tracking-widest font-bold">Speland Digital Library</p>
                </div>
            </a>
            <div class="flex items-center gap-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-amber-600 hover:bg-amber-500 px-5 py-2 rounded-full text-[10px] font-black tracking-widest transition shadow-lg uppercase">Panel Admin</a>
                @else
                    {{-- PERBAIKAN: Menambahkan token rahasia ke route login agar tidak kena filter middleware --}}
                    <a href="{{ route('login', ['token' => 'speland-2026-aman']) }}" class="text-[10px] font-black hover:text-amber-300 transition tracking-[0.2em] border-b-2 border-transparent hover:border-amber-300 uppercase">Login Petugas</a>
                @endauth
            </div>
        </div>
    </nav>

    <header class="py-5 text-center">
        <div class="max-w-xl mx-auto px-4">
            <form action="{{ route('home') }}" method="GET" id="searchForm" class="relative group">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Cari judul buku..." 
                    onkeyup="toggleClearButton()"
                    class="w-full pl-6 pr-20 py-4 rounded-full border-4 border-amber-900 focus:outline-none focus:ring-4 focus:ring-amber-600/20 bg-white shadow-2xl text-amber-950 font-bold">
                
                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                    <button type="button" id="clearBtn" onclick="clearSearch()" 
                        class="{{ request('search') ? '' : 'hidden' }} text-amber-900/50 hover:text-amber-900 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <button type="submit" class="text-amber-900 hover:scale-110 transition-transform p-1">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </header>


    {{-- FILTER CATEGORY --}}
    @if(isset($categories) && count($categories))
        <div class="items-center justify-center py-4 flex overflow-x-auto gap-3 px-6 scrollbar-hide">
            
            {{-- Display All Books --}}
            <a href="{{ route('home') }}"
            class="shrink-0 px-4 py-2 rounded-full text-xs font-black tracking-widest uppercase transition border-2 border-amber-900
            {{ !request('category') ? 'bg-amber-900 text-amber-200 shadow-lg' : 'bg-white text-amber-900  hover:bg-amber-100' }}">
                Semua
            </a>

            @foreach($categories as $category)
                <a href="{{ route('home', ['category' => $category]) }}"
                class="shrink-0 px-4 py-2 rounded-full text-xs font-black tracking-widest uppercase transition border-2 border-amber-900
                {{ request('category') == $category ? 'bg-amber-900 text-amber-200 shadow-lg' : 'bg-white text-amber-900 hover:bg-amber-100' }}">
                    <p class="hidden sm:flex">{{ $category }}</p>
                    <p class="sm:hidden">{{ last(explode(' ', $category)) }}</p>
                </a>
            @endforeach
        </div>
    @endif

    <main class="container mx-auto px-6 flex-grow pb-24">
        @php 
            $globalShelfCount = (($books->currentPage() - 1) * 1) + 1; 
        @endphp

        @forelse($booksByCategory as $category => $items)
            <section class="category-section">
                <div class="{{!request('category') ? 'hidden' : ''}} mt-5 mb-5 text-center">
                    <h3 class="text-3xl font-black text-amber-900 uppercase tracking-widest drop-shadow-sm">{{ $category }}</h3>
                    <div class="w-20 h-1 bg-amber-700 mx-auto mt-2 rounded-full opacity-50"></div>
                </div>

                @foreach($items->chunk(5) as $chunk)
                    <div class="shelf-group">
                        <div class="shelf-container">
                            @foreach($chunk as $book)
                                <div class="book-wrapper group">
                                    {{-- Menggunakan Hashids secara langsung (Pastikan Facade sudah terdaftar) --}}
                                    <a href="{{ route('books.read', Vinkla\Hashids\Facades\Hashids::encode($book->id)) }}" class="book-card block">
                                        <img src="{{ asset($book->cover) }}" 
                                             alt="{{ $book->title }}" 
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($book->title) }}&background=4a2c1d&color=fbbf24&size=210';">
                                        <div class="book-title">{{ $book->title }}</div>
                                    </a>
                                    
                                    <div class="download-btn">
                                        <a href="{{ asset($book->pdf_file) }}" 
                                           download="{{ $book->title }}.pdf" 
                                           class="bg-green-600 hover:bg-green-500 text-white text-[9px] font-black py-1.5 px-3 rounded-full flex items-center gap-1 shadow-xl transition-colors border border-green-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            UNDUH PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="shelf-label">RAK {{ $globalShelfCount++ }}</div>
                    </div>
                @endforeach
            </section>
        @empty
            <div class="py-32 text-center bg-white/40 backdrop-blur-sm rounded-3xl border-4 border-dashed border-amber-900/30">
                <div class="text-8xl mb-6">📚</div>
                <p class="text-amber-950 text-2xl font-black uppercase tracking-widest">Buku "{{ request('search') }}" Tidak Ditemukan</p>
                <a href="{{ route('home') }}" class="mt-4 inline-block text-amber-800 font-bold hover:underline">Tampilkan Semua Koleksi</a>
            </div>
        @endforelse

        @if($books->hasPages())
            <div class="mt-20 mb-10 flex justify-center">
                {{ $books->appends(request()->input())->links() }}
            </div>
        @endif
            
        {{-- Scroll to Top Button --}}
        <button id="scrollTopBtn"
            onclick="scrollToTop()"
            class="hidden fixed bottom-6 right-6 z-50 
                bg-amber-900 hover:bg-amber-800 
                text-amber-200 
                p-3 rounded-full 
                shadow-2xl 
                transition-all duration-300 
                hover:scale-110">
            
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path>
            </svg>
        </button>
    </main>

    <footer class="bg-amber-950 text-amber-200 text-center py-8 text-[10px] border-t-8 border-amber-900 mt-auto">
        <div class="container mx-auto px-4">
            <p class="font-black mb-2 tracking-[0.3em] uppercase">&copy; 2026 SIPINTAR - SMP Negeri 9 Probolinggo</p>
            <p class="opacity-50 tracking-widest uppercase">Literasi Digital untuk Generasi Hebat</p>
        </div>
    </footer>

    <script>
        function toggleClearButton() {
            const input = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearBtn');
            if(clearBtn) clearBtn.classList.toggle('hidden', input.value.length === 0);
        }
        function clearSearch() { 
            window.location.href = "{{ route('home') }}"; 
        }
        document.addEventListener('DOMContentLoaded', toggleClearButton);

        // Scroll to Top
        const scrollBtn = document.getElementById("scrollTopBtn");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                scrollBtn.classList.remove("hidden");
            } else {
                scrollBtn.classList.add("hidden");
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }
    </script>
</body>
</html>