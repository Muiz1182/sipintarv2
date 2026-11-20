@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman Lemari" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2 px-3 text-amber-200">
        {{-- Info Total Data --}}
        <div class="text-xs text-amber-300/80 font-medium">
            Menampilkan 
            @if ($paginator->firstItem())
                <span class="font-bold text-amber-100">{{ $paginator->firstItem() }}</span>
                -
                <span class="font-bold text-amber-100">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            dari
            <span class="font-bold text-amber-100">{{ $paginator->total() }}</span>
            koleksi
        </div>

        {{-- Navigasi Tombol Halaman --}}
        <div class="inline-flex items-center gap-1.5 bg-[#140a05] p-1.5 rounded-xl border border-amber-900/80 shadow-inner">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] sm:w-8 sm:h-8 rounded-lg text-amber-900/40 bg-transparent cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] sm:w-8 sm:h-8 rounded-lg text-amber-300 bg-[#2b170c] hover:bg-[#3e2213] hover:text-white border border-amber-800/40 transition-all active:scale-95" title="Halaman Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
            @endif

            {{-- Nomor Halaman (Pagination Elements) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] sm:w-8 sm:h-8 text-xs font-black text-amber-600/60">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] sm:w-8 sm:h-8 rounded-lg text-xs font-black text-white bg-amber-600 shadow-md border border-amber-400">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] sm:w-8 sm:h-8 rounded-lg text-xs font-bold text-amber-200 bg-[#2b170c] hover:bg-[#3e2213] hover:text-white border border-amber-900/60 transition-all active:scale-95">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] sm:w-8 sm:h-8 rounded-lg text-amber-300 bg-[#2b170c] hover:bg-[#3e2213] hover:text-white border border-amber-800/40 transition-all active:scale-95" title="Halaman Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] sm:w-8 sm:h-8 rounded-lg text-amber-900/40 bg-transparent cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
