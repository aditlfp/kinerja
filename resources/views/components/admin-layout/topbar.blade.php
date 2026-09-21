@props([
    'fullWidth' => false,
    'headerTitle' => null,
    'online' => null,
    'ip' => null,
])

@php
    // Identitas pengguna yang sedang login, ditampilkan sebagai inisial.
    $authUser = \Illuminate\Support\Facades\Auth::user();
    $userName = $authUser->name ?? 'Admin';
    $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($userName, 0, 1));
@endphp
<style>
    .xshiden {
        display: none;
    }
    @media (min-width: 640px) {
        .smhiden {
            display: none;
        }

        .smflex {
            display: inline-flex;
        }
    }

    
</style>
<!-- TOP APP BAR (Material 3) -->
{{-- Catatan hierarki heading: topbar tidak lagi memakai elemen h1 karena setiap
     halaman punya judulnya sendiri lewat komponen admin-page-header. Ini
     menghapus h1 "Dashboard Admin" yang sebelumnya muncul di semua halaman. --}}
<header
    class="sticky top-0 z-40 border-b border-[var(--md-sys-color-outline-variant)] bg-[var(--md-sys-color-surface-container-low)]/95 backdrop-blur">
    <div class="flex h-16 items-center gap-2 px-3 sm:gap-3 sm:px-4 lg:px-6">

        {{-- Mobile: buka drawer overlay --}}
        <button type="button" @click="mobileSidebarOpen = true" class="m3-icon-btn smhiden" aria-label="Buka menu navigasi">
            <span class="material-symbols-outlined" aria-hidden="true">menu</span>
        </button>

        {{-- Desktop: rail <-> drawer --}}
        <button type="button" @click="toggleSidebar()" class="m3-icon-btn xshiden smflex"
            :aria-label="sidebarOpen ? 'Ciutkan menu navigasi' : 'Bentangkan menu navigasi'">
            <span class="material-symbols-outlined" x-text="sidebarOpen ? 'menu_open' : 'menu'"
                aria-hidden="true">menu</span>
        </button>

        {{-- Konteks + metadata lingkungan. Modul yang sedang dibuka diambil dari
             menu aktif; kalau tidak ada (mis. halaman di luar daftar menu), baris
             metadata tetap tampil tanpa judul palsu. --}}
        <div class="min-w-0 flex-1">
            @if ($headerTitle)
                <p class="truncate text-sm font-semibold text-[var(--md-sys-color-on-surface)]">
                    {{ $headerTitle }}
                </p>
            @endif
            <p
                class="{{ $headerTitle ? 'mt-0.5 hidden sm:block' : '' }} truncate text-xs text-[var(--md-sys-color-on-surface-variant)]">
                <span translate="no">{{ request()->root() }}</span>
                <span aria-hidden="true">&middot;</span>
                IP <span class="font-mono">{{ request()->ip() }}</span>
            </p>
        </div>

        {{-- Pengguna yang sedang login --}}
        <div class="flex shrink-0 items-center gap-2" title="{{ $userName }}">
            <span
                class="grid h-8 w-8 place-items-center rounded-full bg-[var(--md-sys-color-primary-container)] text-xs font-bold text-[var(--md-sys-color-on-primary-container)]"
                aria-hidden="true">{{ $initials }}</span>
            <span
                class="hidden max-w-[10rem] truncate text-xs font-semibold text-[var(--md-sys-color-on-surface)] md:block">
                {{ $userName }}
            </span>
        </div>
    </div>
</header>
