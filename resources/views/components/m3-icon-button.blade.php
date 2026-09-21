@props([
    'icon',
    'label', // wajib: tombol ikon-saja harus punya nama aksesibel
    'variant' => null, // null | danger
    'size' => null, // null | sm
    'href' => null,
    'type' => 'button',
])

@php
    $classes = collect([
        'm3-icon-btn',
        $variant ? 'm3-icon-btn--' . $variant : null,
        $size ? 'm3-icon-btn--' . $size : null,
    ])
        ->filter()
        ->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}"
        {{ $attributes->merge(['class' => $classes]) }}>
        <md-ripple></md-ripple>
        <span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>
    </a>
@else
    <button type="{{ $type }}" aria-label="{{ $label }}" title="{{ $label }}"
        {{ $attributes->merge(['class' => $classes]) }}>
        <md-ripple></md-ripple>
        <span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>
    </button>
@endif
