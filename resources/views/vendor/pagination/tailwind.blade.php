@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 px-2">
        {{-- Info Total Data --}}
        <div class="text-xs text-amber-900/80 font-medium">
            Menampilkan 
            @if ($paginator->firstItem())
                <span class="font-bold text-amber-950">{{ $paginator->firstItem() }}</span>
                sampai
                <span class="font-bold text-amber-950">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            dari
            <span class="font-bold text-amber-950">{{ $paginator->total() }}</span>
            koleksi
        </div>

        {{-- Navigasi Tombol Halaman --}}
        <div class="inline-flex items-center gap-1.5 bg-white/80 p-1.5 rounded-2xl shadow-sm border border-amber-200/80">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-amber-900/30 bg-gray-50 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-amber-900 bg-white hover:bg-amber-100 hover:text-amber-950 shadow-sm border border-amber-200/60 transition-all active:scale-95" title="Halaman Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
            @endif

            {{-- Nomor Halaman (Pagination Elements) --}}
            @foreach ($elements as $element)
                {{-- String "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 text-xs font-black text-amber-900/40">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-black text-white bg-amber-800 shadow-md">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold text-amber-950 bg-white hover:bg-amber-100 border border-amber-200/50 shadow-sm transition-all active:scale-95">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-amber-900 bg-white hover:bg-amber-100 hover:text-amber-950 shadow-sm border border-amber-200/60 transition-all active:scale-95" title="Halaman Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-amber-900/30 bg-gray-50 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
