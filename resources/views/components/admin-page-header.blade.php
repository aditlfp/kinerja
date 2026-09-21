@props([
    'title',
    'description' => null,
    'icon' => null,
])

{{-- Judul halaman Material 3.
     Sengaja TANPA label uppercase kecil di atas judul: pola itu terulang identik di
     puluhan halaman dan kategorinya sudah diwakili menu sidebar. Satu <h1> per halaman. --}}
<header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex min-w-0 items-start gap-3">
        @if ($icon)
            <span
                class="mt-0.5 grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-[var(--md-sys-color-primary-container)] text-[var(--md-sys-color-on-primary-container)]">
                <span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>
            </span>
        @endif

        <div class="min-w-0">
            <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">
                {{ $title }}
            </h1>

            @if ($description)
                <p class="mt-1 max-w-[65ch] text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>

    @if (trim((string) ($actions ?? '')) !== '')
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endif
</header>
