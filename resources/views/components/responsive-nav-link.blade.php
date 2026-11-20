@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3.5 pe-4 py-2.5 rounded-xl border-l-4 border-amber-700 text-start text-xs font-black uppercase tracking-wider text-amber-950 bg-amber-100/80 shadow-sm transition-all'
            : 'block w-full ps-3.5 pe-4 py-2.5 rounded-xl border-l-4 border-transparent text-start text-xs font-bold uppercase tracking-wider text-amber-900/70 hover:text-amber-950 hover:bg-amber-50 transition-all';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
