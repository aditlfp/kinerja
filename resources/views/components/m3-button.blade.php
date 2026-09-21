@props([
    'variant' => 'filled', // filled | tonal | outlined | text | danger | danger-tonal | warning | success
    'size' => null, // null | sm | xs
    'icon' => null, // nama ikon Material Symbols
    'href' => null,
    'type' => 'button',
    'block' => false,
])

@php
    $classes = collect([
        'm3-btn',
        'm3-btn--' . $variant,
        $size ? 'm3-btn--' . $size : null,
        $block ? 'm3-btn--block' : null,
    ])
        ->filter()
        ->implode(' ');
@endphp

{{-- Ripple Material Web memberi state layer + riak di titik sentuh; aturan
     .m3-btn::after di head.blade.php menjaga lapisan hover/press tetap benar. --}}
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <md-ripple></md-ripple>
        @if ($icon)
            <span class="material-symbols-outlined text-[1.125rem]" aria-hidden="true">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        <md-ripple></md-ripple>
        @if ($icon)
            <span class="material-symbols-outlined text-[1.125rem]" aria-hidden="true">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </button>
@endif
