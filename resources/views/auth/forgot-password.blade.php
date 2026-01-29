<x-guest-layout>
    <div class="mb-8 text-center">
        <div class="inline-flex p-3 bg-amber-100 rounded-2xl mb-4 shadow-inner">
            <span class="text-3xl">🔑</span>
        </div>
        <h2 class="text-2xl font-black text-amber-950 uppercase tracking-tighter leading-none">Lupa Sandi?</h2>
        <p class="text-[9px] mt-2 text-amber-700 font-bold uppercase tracking-[0.2em] opacity-80">SIPINTAR Digital Library 2026</p>
    </div>

    <div class="mb-6 text-[11px] font-bold text-amber-900/70 uppercase tracking-wider text-center leading-relaxed">
        {{ __('Masukkan email Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi melalui email.') }}
    </div>

    <x-auth-session-status class="mb-4 font-bold text-center text-xs bg-green-50 text-green-700 p-3 rounded-xl border border-green-200" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div class="group">
            <label for="email" class="block font-black text-[10px] text-amber-900 uppercase tracking-widest mb-1.5 ml-1">
                Email Terdaftar
            </label>
            <input id="email" 
                   class="w-full border-2 border-amber-100 rounded-2xl focus:border-amber-600 focus:ring-0 bg-amber-50/20 font-bold text-amber-950 py-3.5 px-4 text-sm" 
                   type="email" 
                   name="email" 
                   :value="old('email')" 
                   required 
                   autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2 ml-1 text-[9px] font-black uppercase text-red-600" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl transition-all active:scale-[0.98]">
                {{ __('Kirim Tautan Reset') }}
            </button>
        </div>

        <div class="mt-8 text-center border-t border-amber-100 pt-6">
            <a href="{{ route('login') }}" class="text-[10px] font-black text-amber-700 hover:text-amber-950 uppercase tracking-widest transition-all">
                 Kembali ke Login
            </a>
        </div>
    </form>
</x-guest-layout>