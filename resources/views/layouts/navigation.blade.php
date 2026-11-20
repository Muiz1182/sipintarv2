<nav x-data="{ open: false, profileOpen: false }" class="sticky top-0 z-50 bg-[#FAF8F5]/90 backdrop-blur-md border-b border-amber-900/10 transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20 gap-4">
            
            {{-- Brand Logo & Title --}}
            <div class="flex items-center gap-6">
                <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center gap-2.5 sm:gap-3.5 group">
                    <img src="{{ asset('img/SIPINTAR TRANS.png') }}" class="h-10 sm:h-12 w-auto transition-transform duration-300 group-hover:scale-105" alt="Logo SIPINTAR">
                    <img src="{{ asset('img/LOGO SMP 9 TRANS.png') }}" class="h-9 w-auto sm:hidden" alt="Logo SMP 9">
                    <div class="hidden sm:block leading-tight">
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-black text-amber-950 tracking-tight leading-none">SIPINTAR</span>
                        </div>
                        <p class="text-[11px] text-amber-800/80 font-bold tracking-wide">Speland Digital Library</p>
                    </div>
                </a>

                {{-- Desktop Navigation Links --}}
                <div class="hidden md:flex items-center gap-2">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <span>🚀</span>
                            <span>{{ __('Dashboard') }}</span>
                        </x-nav-link>
                    @endauth
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        <span>📚</span>
                        <span>{{ __('Lihat Rak Buku') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('books.leaderboard')" :active="request()->routeIs('books.leaderboard')">
                        <span>🏆</span>
                        <span>{{ __('Leaderboard') }}</span>
                    </x-nav-link>
                </div>
            </div>

            {{-- Right: User Menu & Mobile Trigger --}}
            <div class="flex items-center gap-3">
                @auth
                    {{-- User Profile Pill & Dropdown --}}
                    <div class="relative">
                        <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" 
                                class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-2xl bg-white border border-amber-200/80 shadow-sm hover:border-amber-300 transition-all text-left">
                            <div class="w-8 h-8 rounded-xl bg-amber-800 text-white font-black text-xs flex items-center justify-center uppercase shrink-0 shadow-sm">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>

                            <div class="hidden sm:block leading-tight pr-1">
                                <div class="text-[9px] font-black uppercase tracking-wider text-amber-700">
                                    {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Petugas' }}
                                </div>
                                <div class="text-xs font-bold text-amber-950 truncate max-w-[120px]">
                                    {{ auth()->user()->name ?? 'User' }}
                                </div>
                            </div>

                            <svg class="h-4 w-4 text-amber-700 transition-transform duration-200" :class="{ 'rotate-180': profileOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        {{-- Dropdown Card --}}
                        <div x-show="profileOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95 -translate-y-2"
                             x-cloak 
                             class="absolute right-0 z-50 mt-3 w-56 rounded-2xl shadow-xl py-2 bg-white border border-amber-100">
                            
                            <div class="px-4 py-2 border-b border-amber-50">
                                <p class="text-[10px] font-black uppercase tracking-wider text-amber-900/50">Akun Masuk</p>
                                <p class="text-xs font-bold text-amber-950 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            
                            <div class="py-1">
                                <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                                    <span>⚙️</span>
                                    <span>{{ __('Pengaturan Profil') }}</span>
                                </x-dropdown-link>
                            </div>
                            
                            <div class="border-t border-amber-50 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-start px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2 rounded-lg transition-colors">
                                        <span>🚪</span>
                                        <span>{{ __('Keluar Sistem') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Guest Login Button --}}
                    <a href="{{ route('login', ['token' => 'speland-2026-aman']) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider text-amber-900 bg-white hover:bg-amber-50 border border-amber-300 shadow-sm transition-all active:scale-95">
                        <span>🔐</span>
                        <span>Masuk Petugas</span>
                    </a>
                @endauth

                {{-- Mobile Hamburger Menu --}}
                <div class="md:hidden">
                    <button @click="open = !open" 
                            class="p-2.5 min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-amber-900 hover:bg-amber-100 transition-colors"
                            aria-label="Buka Menu Navigasi">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Navigation Drawer --}}
    <div x-show="open" x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden bg-[#FAF8F5] border-t border-amber-200/60 px-4 py-3 space-y-2 shadow-lg">
        @auth
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="min-h-[44px] flex items-center">
                {{ auth()->user()->isSuperAdmin() ? '🚀 Pusat Kendali Super Admin' : '📖 Dashboard Petugas' }}
            </x-responsive-nav-link>
        @endauth
        <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" class="min-h-[44px] flex items-center">
            📚 {{ __('Lihat Rak Buku') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('books.leaderboard')" :active="request()->routeIs('books.leaderboard')" class="min-h-[44px] flex items-center">
            🏆 {{ __('Leaderboard') }}
        </x-responsive-nav-link>

        @auth
            <div class="pt-3 border-t border-amber-200/80 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="min-h-[44px] flex items-center">
                    ⚙️ {{ __('Pengaturan Profil') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-start px-3.5 py-3 min-h-[44px] text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition-colors flex items-center">
                        🚪 {{ __('Keluar Sistem') }}
                    </button>
                </form>
            </div>
        @endauth
    </div>
</nav>
