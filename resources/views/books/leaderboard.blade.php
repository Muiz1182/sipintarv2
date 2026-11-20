<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leaderboard Buku Terpopuler - SIPINTAR</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        warm: {
                            50: '#FDFBF7',
                            100: '#F7F3EA',
                            200: '#EFE7D8',
                            300: '#DFD2BC',
                            400: '#CBB799',
                            500: '#B09574',
                            600: '#8E7353',
                            700: '#6E563E',
                            800: '#4A3727',
                            900: '#2E2218',
                            950: '#1D140E',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #FAF8F5;
            color: #2E2218;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 10% 20%, rgba(217, 119, 6, 0.04) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(120, 53, 15, 0.04) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .book-spine-crease {
            box-shadow: inset 4px 0 6px -2px rgba(0, 0, 0, 0.35), inset -2px 0 4px -1px rgba(255, 255, 255, 0.2);
        }

        .book-shadow {
            box-shadow: 0 10px 25px -5px rgba(46, 34, 24, 0.25), 0 8px 10px -6px rgba(46, 34, 24, 0.2);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-amber-200 selection:text-amber-950">

    {{-- Top Navigation Bar --}}
    <nav class="sticky top-0 z-50 bg-[#FAF8F5]/90 backdrop-blur-md border-b border-warm-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3.5 group">
                    <img src="{{ asset('img/SIPINTAR TRANS.png') }}" alt="Logo SIPINTAR" class="h-10 sm:h-12 w-auto transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ asset('img/LOGO SMP 9 TRANS.png') }}" alt="Logo SMP 9" class="h-9 w-auto sm:hidden">
                    <div class="hidden sm:block leading-tight">
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-black text-warm-900 tracking-tight leading-none">SIPINTAR</span>
                        </div>
                        <p class="text-[11px] text-warm-700 font-bold tracking-wide">Speland Digital Library</p>
                    </div>
                </a>

                <div class="hidden md:flex items-center justify-center">
                    <img src="{{ asset('img/LOGO SMP 9 TRANS.png') }}" alt="Logo SMPN 9" class="h-12 w-auto opacity-90">
                </div>

                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 min-h-[44px] rounded-xl text-xs font-black text-warm-900 bg-white hover:bg-warm-100 border border-warm-300 shadow-sm transition-all active:scale-95">
                    <span>←</span>
                    <span class="hidden sm:inline">Kembali ke Rak</span>
                    <span class="sm:hidden">Rak Buku</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Header Section --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">
        <div class="text-center max-w-xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-100/80 border border-amber-300/60 text-amber-900 text-[11px] font-black uppercase tracking-wider mb-3">
                <span>🏆</span>
                <span>Peringkat Buku Favorit Siswa</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-warm-950 tracking-tight mb-2">
                Leaderboard Buku Terpopuler
            </h1>
            <p class="text-xs md:text-sm text-warm-600 font-medium">
                Daftar buku dengan frekuensi baca tertinggi yang paling diminati oleh siswa Speland.
            </p>
        </div>

        {{-- Podium Top 3 --}}
        @if($topBooks->count() >= 3)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 items-end">
                
                {{-- Juara 2 (Perak) --}}
                @php $b2 = $topBooks->slice(1, 1)->first(); @endphp
                @if($b2)
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-lg flex flex-col items-center text-center order-2 md:order-1 relative">
                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-black flex items-center justify-center text-sm mb-3">
                        2
                    </div>
                    <a href="{{ route('books.read', Vinkla\Hashids\Facades\Hashids::encode($b2->id)) }}" class="block w-28 aspect-[2/3] rounded-r-lg rounded-l-sm overflow-hidden book-shadow hover:scale-105 transition-transform mb-4 relative">
                        <div class="absolute inset-y-0 left-0 w-2.5 bg-gradient-to-r from-black/35 to-transparent z-10 pointer-events-none book-spine-crease"></div>
                        <img src="{{ asset($b2->cover) }}" alt="{{ $b2->title }}" class="w-full h-full object-cover">
                    </a>
                    <h3 class="text-xs font-black text-warm-950 uppercase leading-snug mb-1" title="{{ $b2->title }}">{{ $b2->title }}</h3>
                    <span class="text-[10px] text-warm-500 font-bold uppercase mb-3">{{ $b2->category->name ?? 'Koleksi' }}</span>
                    <div class="w-full pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-[10px] font-semibold text-slate-500">Total Pembaca</span>
                        <span class="font-black text-warm-900">{{ number_format($b2->views) }} views</span>
                    </div>
                </div>
                @endif

                {{-- Juara 1 (Emas / Champion) --}}
                @php $b1 = $topBooks->first(); @endphp
                @if($b1)
                <div class="bg-gradient-to-b from-amber-50 to-white rounded-3xl p-7 border-2 border-amber-400 shadow-xl flex flex-col items-center text-center order-1 md:order-2 relative md:-translate-y-4">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-xs font-black uppercase px-4 py-1 rounded-full shadow-md flex items-center gap-1.5">
                        <span>👑</span> Juara 1
                    </div>
                    <div class="w-10 h-10 rounded-full bg-amber-400 text-amber-950 font-black flex items-center justify-center text-base mb-3 shadow">
                        1
                    </div>
                    <a href="{{ route('books.read', Vinkla\Hashids\Facades\Hashids::encode($b1->id)) }}" class="block w-36 aspect-[2/3] rounded-r-lg rounded-l-sm overflow-hidden book-shadow hover:scale-105 transition-transform mb-4 relative">
                        <div class="absolute inset-y-0 left-0 w-3 bg-gradient-to-r from-black/35 to-transparent z-10 pointer-events-none book-spine-crease"></div>
                        <img src="{{ asset($b1->cover) }}" alt="{{ $b1->title }}" class="w-full h-full object-cover">
                    </a>
                    <h3 class="text-sm font-black text-warm-950 uppercase leading-snug mb-1" title="{{ $b1->title }}">{{ $b1->title }}</h3>
                    <span class="text-[11px] text-amber-800 font-bold uppercase mb-4">{{ $b1->category->name ?? 'Koleksi' }}</span>
                    <div class="w-full pt-3 border-t border-amber-200/60 flex items-center justify-between text-xs">
                        <span class="text-[11px] font-semibold text-warm-600">Total Pembaca</span>
                        <span class="font-black text-amber-900 text-sm">{{ number_format($b1->views) }} views</span>
                    </div>
                </div>
                @endif

                {{-- Juara 3 (Perunggu) --}}
                @php $b3 = $topBooks->slice(2, 1)->first(); @endphp
                @if($b3)
                <div class="bg-white rounded-3xl p-6 border border-amber-200/80 shadow-lg flex flex-col items-center text-center order-3 relative">
                    <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-900 font-black flex items-center justify-center text-sm mb-3">
                        3
                    </div>
                    <a href="{{ route('books.read', Vinkla\Hashids\Facades\Hashids::encode($b3->id)) }}" class="block w-28 aspect-[2/3] rounded-r-lg rounded-l-sm overflow-hidden book-shadow hover:scale-105 transition-transform mb-4 relative">
                        <div class="absolute inset-y-0 left-0 w-2.5 bg-gradient-to-r from-black/35 to-transparent z-10 pointer-events-none book-spine-crease"></div>
                        <img src="{{ asset($b3->cover) }}" alt="{{ $b3->title }}" class="w-full h-full object-cover">
                    </a>
                    <h3 class="text-xs font-black text-warm-950 uppercase leading-snug mb-1" title="{{ $b3->title }}">{{ $b3->title }}</h3>
                    <span class="text-[10px] text-warm-500 font-bold uppercase mb-3">{{ $b3->category->name ?? 'Koleksi' }}</span>
                    <div class="w-full pt-3 border-t border-amber-100 flex items-center justify-between text-xs">
                        <span class="text-[10px] font-semibold text-warm-500">Total Pembaca</span>
                        <span class="font-black text-warm-900">{{ number_format($b3->views) }} views</span>
                    </div>
                </div>
                @endif
            </div>
        @endif

        {{-- Tabel Leaderboard Lengkap --}}
        <div class="bg-white rounded-3xl border border-warm-200 shadow-xl overflow-hidden">
            <div class="px-6 py-5 bg-warm-50/60 border-b border-warm-200 flex justify-between items-center">
                <span class="text-xs font-black text-warm-900 uppercase tracking-wider">Semua Peringkat Koleksi</span>
                <span class="text-[11px] font-bold text-warm-600 bg-white px-3 py-1 rounded-full border border-warm-200">
                    {{ $topBooks->count() }} Buku Terpopuler
                </span>
            </div>

            {{-- Tampilan Mobile: Kartu Peringkat (Bebas Geser Horizontal) --}}
            <div class="block sm:hidden divide-y divide-warm-100 p-3 space-y-3 bg-warm-50/40">
                @forelse($topBooks as $index => $book)
                    <div class="bg-white rounded-2xl p-4 border border-warm-200/80 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs
                                    {{ $index == 0 ? 'bg-amber-400 text-amber-950 shadow-sm' : 
                                       ($index == 1 ? 'bg-slate-200 text-slate-800' : 
                                       ($index == 2 ? 'bg-amber-200 text-amber-900' : 'bg-warm-100 text-warm-700')) }}">
                                    {{ $index + 1 }}
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-wider {{ $index < 3 ? 'text-amber-900' : 'text-warm-500' }}">
                                    {{ $index == 0 ? '👑 Juara 1' : ($index == 1 ? '🥈 Juara 2' : ($index == 2 ? '🥉 Juara 3' : 'Peringkat ' . ($index + 1))) }}
                                </span>
                            </div>
                            <span class="text-xs font-black text-amber-950 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                👁️ {{ number_format($book->views) }} views
                            </span>
                        </div>

                        <div class="flex items-center gap-3 bg-[#FAF8F5] p-2.5 rounded-xl border border-warm-200/60">
                            <div class="w-11 h-15 rounded overflow-hidden book-shadow shrink-0 relative">
                                <img src="{{ asset($book->cover) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-black text-warm-950 uppercase text-xs leading-snug break-words" title="{{ $book->title }}">{{ $book->title }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="bg-white text-warm-800 px-2 py-0.5 rounded-md text-[9px] font-bold uppercase border border-warm-200 truncate max-w-[140px]">
                                        {{ $book->category->name ?? 'Tanpa Rak' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('books.read', Vinkla\Hashids\Facades\Hashids::encode($book->id)) }}" 
                           class="w-full min-h-[44px] bg-amber-800 hover:bg-amber-900 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all active:scale-95 flex items-center justify-center gap-1.5">
                            <span>📖</span>
                            <span>Buka &amp; Baca Buku</span>
                        </a>
                    </div>
                @empty
                    <div class="py-12 text-center text-warm-400 font-medium italic">
                        Belum ada riwayat aktivitas baca pada sistem.
                    </div>
                @endforelse
            </div>

            {{-- Tampilan Desktop & Tablet: Tabel --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-warm-100/60 text-[10px] font-black uppercase text-warm-700 tracking-wider border-b border-warm-200">
                        <tr>
                            <th class="px-6 py-4 text-center w-20">Peringkat</th>
                            <th class="px-6 py-4">Buku &amp; Penulis</th>
                            <th class="px-6 py-4">Rak</th>
                            <th class="px-6 py-4 text-right">Frekuensi Baca</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm-100">
                        @forelse($topBooks as $index => $book)
                        <tr class="hover:bg-warm-50/50 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <div class="w-8 h-8 mx-auto rounded-xl flex items-center justify-center font-black text-xs
                                    {{ $index == 0 ? 'bg-amber-400 text-amber-950 font-black' : 
                                       ($index == 1 ? 'bg-slate-200 text-slate-800' : 
                                       ($index == 2 ? 'bg-amber-200 text-amber-900' : 'bg-warm-100 text-warm-700')) }}">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-14 rounded overflow-hidden book-shadow shrink-0 relative">
                                        <img src="{{ asset($book->cover) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-black text-warm-950 uppercase text-xs leading-normal max-w-md break-words" title="{{ $book->title }}">{{ $book->title }}</h4>
                                        <p class="text-[10px] text-warm-500 font-semibold uppercase mt-0.5">{{ $book->author ?? 'SIPINTAR' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-warm-100 text-warm-800 px-3 py-1 rounded-lg text-[10px] font-bold uppercase border border-warm-200">
                                    {{ $book->category->name ?? 'Tanpa Rak' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-black text-warm-950 text-sm">{{ number_format($book->views) }}</div>
                                <div class="text-[9px] text-warm-500 font-semibold uppercase">kali dibaca</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('books.read', Vinkla\Hashids\Facades\Hashids::encode($book->id)) }}" 
                                   class="inline-flex items-center gap-1 bg-amber-800 hover:bg-amber-900 text-white px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm transition-all active:scale-95">
                                    <span>📖</span> Baca
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-warm-400 font-medium italic">
                                Belum ada riwayat aktivitas baca pada sistem.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-warm-950 text-warm-300 py-8 border-t border-warm-900 mt-auto text-center text-xs">
        <div class="max-w-7xl mx-auto px-4">
            <p class="text-[10px] text-warm-500 font-bold uppercase tracking-widest">
                &copy; {{ date('Y') }} SIPINTAR &bull; SMPN 9 PROBOLINGGO &bull; LEADERBOARD LITERASI
            </p>
        </div>
    </footer>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const duration = 2.5 * 1000;
            const end = Date.now() + duration;

            (function frame() {
                confetti({
                    particleCount: 3,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0 },
                    colors: ['#fbbf24', '#78350f', '#fde68a']
                });
                confetti({
                    particleCount: 3,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1 },
                    colors: ['#fbbf24', '#78350f', '#fde68a']
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            }());
        });
    </script>
</body>
</html>
