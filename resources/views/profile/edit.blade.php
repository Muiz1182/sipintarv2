<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-amber-900 leading-tight">
            {{ __('Pengaturan Profil Petugas') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-l-4 border-amber-600">
                <div class="max-w-xl">
                    <h3 class="text-lg font-bold text-amber-900 mb-4">{{ __('Informasi Profil') }}</h3>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-l-4 border-amber-800">
                <div class="max-w-xl">
                    <h3 class="text-lg font-bold text-amber-900 mb-4">{{ __('Perbarui Kata Sandi') }}</h3>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- if user is admin, dont show  --}}
            @if(!Auth::user()->is_admin)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-l-4 border-red-600">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-bold text-red-800 mb-4">{{ __('Hapus Akun') }}</h3>
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>