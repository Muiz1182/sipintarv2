@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 bg-gray-50/50 focus:border-amber-600 focus:ring-4 focus:ring-amber-500/10 rounded-xl shadow-sm text-sm font-medium text-amber-950 transition-all']) }}>
