{{--
    Per-day checkpoint status indicator.
    Props:
      counts  array{accept:int, denied:int, process:int}
      size    'sm' | 'md'   (menentukan batas atas ukuran)
--}}
@props([
    'counts' => ['accept' => 0, 'denied' => 0, 'process' => 0],
    'size' => 'sm',
    'hasData' => false,
])

@php
    $accept = (int) ($counts['accept'] ?? 0);
    $denied = (int) ($counts['denied'] ?? 0);
    $process = (int) ($counts['process'] ?? 0);
    $total = $accept + $denied + $process;

    // Basis ukuran = persen lebar container (sel), dibatasi min/max px.
    // 3 chip butuh ~8-9em; 11cqw x 8.5em ≈ 93cqw → selalu masuk 100cqw.
    $base = $size === 'md'
        ? 'text-[clamp(8px,11cqw,11px)]'
        : 'text-[clamp(12px,10cqw,9px)]';

    // Dalam `em`, jadi ikut skala basis.
    $icon = 'text-[1.35em]';
    $gap = $size === 'md' ? 'gap-[0.3em]' : 'gap-[0.25em]';
@endphp

@if ($total > 0)
    @once
        <style>
            /* Sel kalender sangat sempit: tumpuk chip vertikal agar rapi,
               bukan wrap horizontal yang berantakan. Container = wrapper di bawah. */
            @container (max-width: 50px) {
                .cp-status-row {
                    flex-direction: column;
                    align-items: center;
                    border-radius: 0.5rem;
                }
            }
        </style>
    @endonce

    <span class="block w-full min-w-0 [container-type:inline-size] py-1">
        <span
            {{ $attributes->merge([
                'class' => "cp-status-row flex w-full min-w-0 flex-wrap items-center justify-center {$base} {$gap} leading-none",
            ]) }}
        >
            @if ($accept > 0)
                <span
                    class="inline-flex min-w-0 shrink-0 items-center gap-[0.15em] leading-none text-white"
                    title="{{ $accept }} disetujui"
                >
                    <i class="ri-checkbox-circle-fill {{ $icon }} shrink-0 leading-none"></i>
                    <span class="font-semibold leading-none tabular-nums overflow-hidden">{{ $accept }}</span>
                </span>
            @endif

            @if ($denied > 0)
                <span
                    class="inline-flex min-w-0 shrink-0 items-center gap-[0.15em] leading-none"
                    title="{{ $denied }} ditolak"
                >
                    <i class="ri-close-circle-fill {{ $icon }} shrink-0 leading-none"></i>
                    <span class="font-semibold leading-none tabular-nums overflow-hidden">{{ $denied }}</span>
                </span>
            @endif

            @if ($process > 0)
                <span
                    class="inline-flex min-w-0 shrink-0 items-center gap-[0.15em] leading-none"
                    title="{{ $process }} menunggu verifikasi"
                >
                    <i class="ri-time-line {{ $icon }} shrink-0 leading-none"></i>
                    <span class="font-semibold leading-none tabular-nums overflow-hidden">{{ $process }}</span>
                </span>
            @endif
        </span>
    </span>
@elseif ($hasData)
    {{-- A batch with no items still counts as filled. --}}
    <span class="block w-full min-w-0 text-center [container-type:inline-size]">
        <i class="ri-check-line text-[clamp(6px,12cqw,13px)] leading-none opacity-90"></i>
    </span>
@endif
