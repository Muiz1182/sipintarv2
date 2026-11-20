<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 bg-amber-800 hover:bg-amber-900 active:scale-95 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-wider shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 transition-all duration-150 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
