{{--
    Verdict badge for one checkpoint item.
    The icon is the same solid Remix Icon the approval chips use, so the badge
    and the modal read as one set. Only two colours exist here: green for
    accepted, red for denied.
    Props:
      status  'accept' | 'denied' | anything else (renders nothing)
      label   optional override, e.g. 'Disetujui'
      size    'sm' | 'md'
--}}
@props([
    'status',
    'label' => null,
    'size' => 'sm',
])

@php
    $accepted = $status === 'accept';
    $denied = $status === 'denied';
@endphp

@if ($accepted || $denied)
    <span {{ $attributes->class([
        'inline-flex items-center gap-1.5 rounded-md font-bold uppercase',
        'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' => $accepted,
        'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200' => $denied,
        'px-2 py-0.5 text-[10px]' => $size === 'sm',
        'px-2.5 py-1 text-xs' => $size === 'md',
    ]) }}>
        <i class="{{ $accepted ? 'ri-checkbox-circle-fill text-emerald-600' : 'ri-close-circle-fill text-rose-600' }} {{ $size === 'sm' ? 'text-sm' : 'text-base' }} shrink-0 leading-none"
            aria-hidden="true"></i>
        {{ $label ?? ($accepted ? 'Diterima' : 'Ditolak') }}
    </span>
@endif
