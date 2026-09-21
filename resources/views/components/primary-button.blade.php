<button {{ $attributes->merge(['type' => 'submit', 'class' => 'm3-btn m3-btn--filled']) }}>
    {{ $slot }}
</button>
