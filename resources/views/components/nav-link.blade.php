@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider text-amber-950 bg-amber-100/90 border border-amber-300/80 shadow-sm transition-all duration-150'
            : 'inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider text-amber-900/70 hover:text-amber-950 hover:bg-amber-50 transition-all duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
