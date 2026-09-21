@props(['active' => false])

<a {{ $attributes->merge(['class' => 'm3-btn ' . ($active ? 'm3-btn--danger-tonal' : 'm3-btn--text')]) }}>
    {{ $slot }}
</a>
