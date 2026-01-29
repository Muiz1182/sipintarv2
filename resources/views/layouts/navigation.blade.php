<nav x-data="{ open: false, profileOpen: false }" class="bg-white border-b border-amber-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    {{-- Perbaikan: Gunakan check() secara eksplisit --}}
                    <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center">
                        <img src="{{ asset('img/SIPINTAR TRANS.png') }}" class="h-12 w-auto" alt="Logo SIPINTAR">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="font-bold">
                            {{ __('Dashboard Admin') }}
                        </x-nav-link>
                    @endauth
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Lihat Rak Buku') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <div class="relative">
                        <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-amber-900 focus:outline-none transition ease-in-out duration-150">
                            <div class="font-bold flex items-center space-x-2">
                                <span class="text-amber-900 bg-amber-50 px-3 py-1 rounded-lg border border-amber-100">
                                    {{-- Perbaikan: Pengecekan Null-Safe yang lebih kuat --}}
                                    <span class="opacity-60 text-[10px] uppercase tracking-tighter mr-1">
                                        {{ (auth()->user() && auth()->user()->email === 'admin@sipintar.com') ? 'Super Admin' : 'Petugas' }}:
                                    </span>
                                    {{ auth()->user()->name ?? 'User' }}
                                </span>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-amber-900" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <div x-show="profileOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-cloak 
                             class="absolute right-0 z-50 mt-2 w-48 rounded-xl shadow-xl py-1 bg-white ring-1 ring-black ring-opacity-5 origin-top-right border border-amber-50">
                            
                            <div class="px-4 py-2 text-[10px] font-black text-amber-900/40 uppercase tracking-widest">Kelola Akun</div>
                            
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Edit Profile') }}</x-dropdown-link>
                            
                            <div class="border-t border-amber-50"></div>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" 
                                    onclick="event.preventDefault(); this.closest('form').submit();" 
                                    class="text-red-600 font-bold hover:bg-red-50">
                                    {{ __('Keluar Sistem') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Tampilan Tombol Masuk untuk Guest --}}
                    <a href="{{ route('login', ['token' => 'speland-2026-aman']) }}" class="text-[11px] font-black text-amber-800 hover:text-white hover:bg-amber-800 uppercase tracking-[0.15em] border-2 border-amber-800 px-5 py-2.5 rounded-2xl transition-all duration-300">
                        Masuk Petugas
                    </a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-amber-900 hover:bg-amber-50 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak class="sm:hidden bg-white border-t border-amber-50">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard Admin') }}
                </x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Lihat Rak Buku') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-amber-100">
            @auth
                <div class="px-4">
                    <div class="font-black text-xs text-amber-900 uppercase tracking-widest">
                        {{ (auth()->user() && auth()->user()->email === 'admin@sipintar.com') ? 'Super Admin' : 'Petugas' }}
                    </div>
                    <div class="font-bold text-sm text-gray-500">{{ auth()->user()->name ?? 'User' }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 font-bold">
                            {{ __('Keluar Sistem') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="px-4 py-4">
                    <a href="{{ route('login', ['token' => 'speland-2026-aman']) }}" class="block text-center font-black text-amber-800 border-2 border-amber-800 py-3 rounded-xl uppercase tracking-widest text-xs">
                        Masuk Petugas
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>