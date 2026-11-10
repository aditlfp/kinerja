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
<!-- TOP APP BAR (Material 3) -->
{{-- Catatan hierarki heading: topbar tidak lagi memakai elemen h1 karena setiap
     halaman punya judulnya sendiri lewat komponen admin-page-header. Ini
     menghapus h1 "Dashboard Admin" yang sebelumnya muncul di semua halaman. --}}
<header
    :class="mobileSidebarOpen ? 'z-30' : 'z-40'"
    class="sticky top-0 border-b border-[var(--md-sys-color-outline-variant)] bg-[var(--md-sys-color-surface-container-low)]/95 backdrop-blur">
    <div class="flex h-16 items-center gap-2 px-3 sm:gap-3 sm:px-4 lg:px-6">

        {{-- Satu tombol navigasi: drawer overlay di bawah lg, rail <-> drawer di desktop. --}}
        <button type="button"
            @click="window.matchMedia('(min-width: 1024px)').matches ? toggleSidebar() : mobileSidebarOpen = true"
            class="m3-icon-btn" aria-label="Buka menu navigasi">
            <span class="material-symbols-outlined" aria-hidden="true">menu</span>
        </button>

        {{-- Desktop menampilkan status sidebar melalui tombol yang sama; mobile selalu membuka drawer. --}}
        <span class="sr-only" x-text="sidebarOpen ? 'Menu terbuka' : 'Menu diciutkan'"></span>

        {{-- Konteks + metadata lingkungan. Modul yang sedang dibuka diambil dari
             menu aktif; kalau tidak ada (mis. halaman di luar daftar menu), baris
             metadata tetap tampil tanpa judul palsu. --}}
        <div class="min-w-0 flex-1">
            @if ($headerTitle)
                <p class="truncate text-sm font-semibold text-(--md-sys-color-on-surface)">
                    {{ $headerTitle }}
                </p>
            @endif
            <p
                class="{{ $headerTitle ? 'mt-0.5 hidden sm:block' : '' }} truncate text-xs text-(--md-sys-color-on-surface-variant)">
                <span translate="no">{{ request()->root() }}</span>
                <span aria-hidden="true">&middot;</span>
                IP <span class="font-mono">{{ request()->ip() }}</span>
            </p>
        </div>

        {{-- Pengguna yang sedang login --}}
        <div class="flex shrink-0 items-center gap-2" title="{{ $userName }}">
            <span
                class="grid h-8 w-8 place-items-center rounded-full bg-(--md-sys-color-primary-container) text-xs font-bold text-(--md-sys-color-on-primary-container)"
                aria-hidden="true">{{ $initials }}</span>
            <span
                class="hidden max-w-40 truncate text-xs font-semibold text-(--md-sys-color-on-surface) md:block">
                {{ $userName }}
            </span>
        </div>
    </div>
</header>
