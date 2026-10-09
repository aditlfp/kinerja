@props(['active'])

@php
    $classes = $active
        ? 'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium
           bg-slate-700 text-white shadow-sm
           transition-all duration-200 ease-in-out'
        : 'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium
           text-slate-600
           transition-all duration-200 ease-in-out
           hover:bg-slate-100 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
