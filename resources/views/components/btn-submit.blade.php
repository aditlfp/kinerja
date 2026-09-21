@props(['id' => null, 'data' => null, 'type' => 'submit', 'label' => 'Hapus'])

<button type="{{ $type }}" id="{{ $id ?? '' }}" data-data="{{ $data ?? '' }}" aria-label="{{ $label }}"
    title="{{ $label }}" {{ $attributes->merge(['class' => 'm3-icon-btn m3-icon-btn--danger']) }}>
    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
</button>
