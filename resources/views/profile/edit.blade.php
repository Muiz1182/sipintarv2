<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2.5">
            <span class="text-xl">⚙️</span>
            <h2 class="font-black text-xl md:text-2xl text-amber-950 uppercase tracking-tight leading-tight">
                {{ __('Pengaturan Profil Petugas') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FAF8F5] min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="p-6 sm:p-8 bg-white shadow-xl rounded-3xl border border-amber-200/80">
                <div class="max-w-xl">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <span class="text-lg">👤</span>
                        <h3 class="text-base font-black text-amber-950 uppercase tracking-tight">{{ __('Informasi Akun & Identitas') }}</h3>
                    </div>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-xl rounded-3xl border border-amber-200/80">
                <div class="max-w-xl">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <span class="text-lg">🔑</span>
                        <h3 class="text-base font-black text-amber-950 uppercase tracking-tight">{{ __('Perbarui Kata Sandi') }}</h3>
                    </div>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @if(!Auth::user()->isSuperAdmin())
                <div class="p-6 sm:p-8 bg-white shadow-xl rounded-3xl border border-rose-200/80">
                    <div class="max-w-xl">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-rose-100">
                            <span class="text-lg">⚠️</span>
                            <h3 class="text-base font-black text-rose-800 uppercase tracking-tight">{{ __('Hapus Akun') }}</h3>
                        </div>
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>