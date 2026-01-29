<x-guest-layout>
    {{-- 
        INFO: Header dengan identitas SIPINTAR
    --}}
    <div class="mb-8 text-center">
        <div class="inline-flex p-3 bg-amber-100 rounded-2xl mb-4 shadow-inner">
            <span class="text-3xl">🔐</span>
        </div>
        <h2 class="text-2xl font-black text-amber-950 uppercase tracking-tighter leading-none">Login Petugas</h2>
        <p class="text-[9px] mt-2 text-amber-700 font-bold uppercase tracking-[0.2em] opacity-80">SIPINTAR Digital Library 2026</p>
    </div>

    {{-- Alert Status --}}
    @if (session('status'))
        <div class="mb-4 font-bold text-center text-xs bg-amber-50 text-amber-800 p-3 rounded-xl border border-amber-200">
            {{ session('status') }}
        </div>
    @endif

    {{-- 
        PERBAIKAN UTAMA: 
        Menangkap token dari rute agar form POST dikirim ke URL yang valid (domain.com/masuk/.../token)
    --}}
    <form method="POST" action="{{ route('login', ['token' => request()->route('token')]) }}" class="space-y-5">
        @csrf

        {{-- Email Field --}}
        <div class="group">
            <label for="email" class="block font-black text-[10px] text-amber-900 uppercase tracking-widest mb-1.5 ml-1 transition-colors group-focus-within:text-amber-600">
                Email Petugas
            </label>
            <div class="relative">
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username"
                       class="w-full border-2 border-amber-100 rounded-2xl focus:border-amber-600 focus:ring-0 bg-amber-50/20 font-bold text-amber-950 placeholder-amber-900/20 transition-all py-3.5 px-4 text-sm"
                       placeholder="nama@sipintar.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 ml-1 text-[9px] font-black uppercase tracking-tight text-red-600" />
        </div>

        {{-- Password Field --}}
        <div class="group">
            <div class="flex justify-between items-center mb-1.5 ml-1">
                <label for="password" class="block font-black text-[10px] text-amber-900 uppercase tracking-widest transition-colors group-focus-within:text-amber-600">
                    Kata Sandi
                </label>
                @if (Route::has('password.request'))
                    {{-- Navigasi Lupa Sandi tetap aman karena lupa-sandi bersifat public --}}
                    <a class="text-[9px] font-black text-amber-700 hover:text-amber-600 uppercase tracking-tighter transition" href="{{ route('password.request') }}">
                        Lupa Sandi?
                    </a>
                @endif
            </div>
            <div class="relative">
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       class="w-full border-2 border-amber-100 rounded-2xl focus:border-amber-600 focus:ring-0 bg-amber-50/20 font-bold text-amber-950 transition-all py-3.5 px-4 text-sm"
                       placeholder="••••••••">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 ml-1 text-[9px] font-black uppercase tracking-tight text-red-600" />
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center justify-between px-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <div class="relative flex items-center">
                    <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-amber-300 text-amber-800 shadow-sm focus:ring-amber-500 bg-amber-50 transition-all" name="remember">
                </div>
                <span class="ms-2 text-[10px] font-black text-amber-900 uppercase tracking-widest group-hover:text-amber-600 transition">Ingat Saya</span>
            </label>
        </div>

        {{-- Submit Button --}}
        <div class="pt-2">
            <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-amber-900/20 transform transition-all active:scale-[0.98] hover:shadow-2xl">
                Masuk ke Panel Kendali
            </button>
        </div>
        
        {{-- Back Link --}}
        <div class="mt-8 text-center border-t border-amber-100 pt-6">
            <a href="{{ route('home') }}" class="group text-[10px] font-black text-amber-700 hover:text-amber-950 uppercase tracking-widest transition-all inline-flex items-center gap-2">
                <svg class="w-3.5 h-3.5 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke SIPINTAR
            </a>
        </div>
    </form>
</x-guest-layout>