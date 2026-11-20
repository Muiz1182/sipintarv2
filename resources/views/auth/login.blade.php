<x-guest-layout>
    <div class="w-full max-w-4xl bg-[#FAF7F2] rounded-3xl sm:rounded-[36px] shadow-[0_30px_90px_rgba(0,0,0,0.65)] border border-amber-900/40 overflow-hidden grid grid-cols-1 lg:grid-cols-12 my-auto">
        
        {{-- Sisi Kiri: Panel Editorial Branding & Identitas --}}
        <div class="lg:col-span-5 bg-gradient-to-br from-[#24130a] via-[#331a0e] to-[#1c0d06] p-6 sm:p-10 flex flex-col justify-between text-amber-100 relative overflow-hidden border-b lg:border-b-0 lg:border-r border-amber-900/50">
            {{-- Aksen Cahaya Ambience --}}
            <div class="absolute -top-16 -left-16 w-48 h-48 bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            {{-- Bagian Atas: Logo & Badge --}}
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <img src="{{ asset('img/SIPINTAR TRANS.png') }}" alt="Logo SIPINTAR" class="h-12 w-auto drop-shadow-lg">
                    <img src="{{ asset('img/LOGO SMP 9 TRANS.png') }}" alt="Logo SMPN 9" class="h-12 w-auto drop-shadow-lg">
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-[10px] font-black uppercase tracking-widest text-amber-300 mb-3">
                    <span>🏛️</span>
                    <span>Speland Digital Library</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-amber-50 uppercase tracking-tight leading-none mb-3">
                    SIPINTAR
                </h1>
                <p class="text-xs text-amber-200/75 leading-relaxed font-medium">
                    Pusat kendali operasional literasi digital dan manajemen perpustakaan SMP Negeri 9 Probolinggo.
                </p>
            </div>

            {{-- Bagian Tengah: Fitur Unggulan Sistem --}}
            <div class="py-8 space-y-3.5 relative z-10 hidden sm:block">
                <div class="flex items-start gap-3 p-3 rounded-2xl bg-black/25 border border-amber-800/30">
                    <span class="text-base">⚡</span>
                    <div>
                        <h4 class="text-xs font-black uppercase text-amber-200">Kompresi Otomatis &le; 1MB</h4>
                        <p class="text-[11px] text-amber-300/70 leading-snug mt-0.5">Berkas PDF besar otomatis dikompresi agar muat cepat dan hemat ruang.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-2xl bg-black/25 border border-amber-800/30">
                    <span class="text-base">📖</span>
                    <div>
                        <h4 class="text-xs font-black uppercase text-amber-200">Katalog Flipbook Realistis</h4>
                        <p class="text-[11px] text-amber-300/70 leading-snug mt-0.5">Pengalaman membaca interaktif layaknya membuka buku cetak fisik.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-2xl bg-black/25 border border-amber-800/30">
                    <span class="text-base">📊</span>
                    <div>
                        <h4 class="text-xs font-black uppercase text-amber-200">Monitoring Kapasitas Cerdas</h4>
                        <p class="text-[11px] text-amber-300/70 leading-snug mt-0.5">Audit kapasitas penyimpanan dan verifikasi integritas berkas di disk.</p>
                    </div>
                </div>
            </div>

            {{-- Bagian Bawah: Sekolah & Hak Cipta --}}
            <div class="relative z-10 pt-4 border-t border-amber-900/40 text-[10px] text-amber-400/60 font-semibold uppercase tracking-wider flex items-center justify-between">
                <span>SMPN 9 Probolinggo</span>
                <span>Tahun {{ date('Y') }}</span>
            </div>
        </div>

        {{-- Sisi Kanan: Formulir Login Petugas --}}
        <div class="lg:col-span-7 p-6 sm:p-10 md:p-12 flex flex-col justify-between bg-[#FDFBF7]" x-data="{ showPassword: false }">
            <div>
                {{-- Header Form --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-100 text-amber-900 text-[10px] font-black uppercase tracking-wider border border-amber-200/60">
                            <span>🔐</span> Autentikasi Pengguna
                        </span>
                        <span class="text-[10px] text-amber-800/60 font-bold uppercase tracking-wider">SIPINTAR v2.6</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-amber-950 uppercase tracking-tight">
                        Masuk Petugas
                    </h2>
                    <p class="text-xs text-amber-800/70 mt-1 font-medium">
                        Gunakan akun petugas atau super admin yang telah terdaftar pada sistem.
                    </p>
                </div>

                {{-- Status Session --}}
                @if (session('status'))
                    <div class="mb-6 font-bold text-xs bg-emerald-50 text-emerald-800 p-3.5 rounded-2xl border border-emerald-200 flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login', ['token' => request()->route('token')]) }}" class="space-y-5">
                    @csrf

                    {{-- Email Petugas --}}
                    <div>
                        <label for="email" class="block font-black text-[10px] text-amber-950 uppercase tracking-wider mb-2">
                            Alamat Email
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-amber-700/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input id="email" 
                                   type="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username"
                                   placeholder="nama@sipintar.com"
                                   class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-2 border-amber-200/80 bg-white font-bold text-amber-950 text-xs placeholder-amber-900/30 focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 transition-all shadow-sm">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-[10px] font-black uppercase text-rose-600 tracking-wide" />
                    </div>

                    {{-- Kata Sandi --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block font-black text-[10px] text-amber-950 uppercase tracking-wider">
                                Kata Sandi
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[10px] font-black text-amber-700 hover:text-amber-900 uppercase tracking-wider transition-colors">
                                    Lupa Sandi?
                                </a>
                            @endif
                        </div>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-amber-700/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input id="password" 
                                   :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="Masukkan kata sandi..."
                                   class="w-full pl-11 pr-11 py-3.5 rounded-2xl border-2 border-amber-200/80 bg-white font-bold text-amber-950 text-xs placeholder-amber-900/30 focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 transition-all shadow-sm">
                            
                            {{-- Toggle Show/Hide Password --}}
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute right-3.5 p-1 text-amber-700/60 hover:text-amber-900 transition-colors focus:outline-none"
                                    title="Tampilkan / Sembunyikan Sandi">
                                <template x-if="!showPassword">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </template>
                                <template x-if="showPassword">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </template>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-[10px] font-black uppercase text-rose-600 tracking-wide" />
                    </div>

                    {{-- Ingat Saya --}}
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input id="remember_me" 
                                   type="checkbox" 
                                   name="remember" 
                                   class="w-4 h-4 rounded-lg border-2 border-amber-300 text-amber-700 shadow-sm focus:ring-amber-500 bg-white cursor-pointer">
                            <span class="ms-2 text-xs font-bold text-amber-900 group-hover:text-amber-700 transition-colors">
                                Ingat sesi login saya
                            </span>
                        </label>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-amber-800 to-amber-900 hover:from-amber-700 hover:to-amber-800 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-amber-950/25 transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-2 group">
                            <span>Masuk ke Panel Kendali</span>
                            <span class="transform transition-transform group-hover:translate-x-1">&rarr;</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tautan Kembali ke Beranda --}}
            <div class="mt-8 pt-6 border-t border-amber-200/60 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-black text-amber-800 hover:text-amber-950 uppercase tracking-wider transition-colors group">
                    <span class="transform transition-transform group-hover:-translate-x-1">&larr;</span>
                    <span>Kembali ke Rak Buku SIPINTAR</span>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
