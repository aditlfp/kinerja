<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8fafd">
    <title>@yield('title', 'Admin') - Admin Kinerja SAC-PONOROGO</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
        rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="text/javascript" src="{{ URL::asset('js/jqueryNew.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.lazy.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.lazy.plugins.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    {{-- Material Design 3 — Material Web (https://m3.material.io/develop/web) via CDN.
         esm.run diperlukan karena all.js memakai bare import (lit). Module script bersifat
         deferred sehingga aman diletakkan di head. --}}
    <script type="module" src="https://esm.run/@material/web@2.5.0/all.js"></script>

    @stack('scripts')

    <style>
        /* =====================================================================
           Material Design 3 — fondasi area admin
           Token warna/elevation/shape + typescale M3, lalu layer komponen bersama
           yang dipakai seluruh halaman di dalam komponen admin-layout.
           ===================================================================== */

        :root {
            color-scheme: light;

            /* ---- Satu seed menggerakkan seluruh palet lembut ----
               Semua permukaan, container, dan gradient diturunkan dari seed ini,
               jadi menukar tema = menukar satu nilai. Nilai turunan memakai
               color-mix supaya hue tetap satu keluarga; blok @supports di bawah
               mengembalikan nilai lama untuk browser tanpa color-mix. */
            --md-seed: #0b57d0;

            /* --- Peran primer --- */
            --md-sys-color-primary: var(--md-seed);
            --md-sys-color-on-primary: #ffffff;
            --md-sys-color-primary-container: color-mix(in srgb, var(--md-seed) 20%, #ffffff);
            --md-sys-color-on-primary-container: color-mix(in srgb, var(--md-seed) 64%, #000000);
            --md-sys-color-primary-fixed: color-mix(in srgb, var(--md-seed) 16%, #ffffff);
            --md-sys-color-primary-fixed-dim: color-mix(in srgb, var(--md-seed) 32%, #ffffff);
            --md-sys-color-on-primary-fixed: color-mix(in srgb, var(--md-seed) 70%, #000000);
            --md-sys-color-on-primary-fixed-variant: color-mix(in srgb, var(--md-seed) 84%, #000000);

            /* --- Peran pendukung: basis terharmonisasi, container diturunkan lembut --- */
            --md-sys-color-secondary: #00639b;
            --md-sys-color-on-secondary: #ffffff;
            --md-sys-color-secondary-container: color-mix(in srgb, #00639b 14%, #ffffff);
            --md-sys-color-on-secondary-container: color-mix(in srgb, #00639b 70%, #000000);
            --md-sys-color-tertiary: #146c2e;
            --md-sys-color-on-tertiary: #ffffff;
            --md-sys-color-tertiary-container: color-mix(in srgb, #146c2e 12%, #ffffff);
            --md-sys-color-on-tertiary-container: color-mix(in srgb, #146c2e 68%, #000000);
            --md-sys-color-error: #b3261e;
            --md-sys-color-on-error: #ffffff;
            --md-sys-color-error-container: color-mix(in srgb, #b3261e 12%, #ffffff);
            --md-sys-color-on-error-container: color-mix(in srgb, #b3261e 70%, #000000);
            --md-sys-color-warning-container: color-mix(in srgb, #7a5c00 16%, #ffffff);
            --md-sys-color-on-warning-container: #5c4200;

            /* Alias warisan: halaman lama masih memakai nama --app-*. */
            --app-warning-container: var(--md-sys-color-warning-container);
            --app-on-warning-container: var(--md-sys-color-on-warning-container);

            /* State layer di atas permukaan (hover baris, item menu). */
            --app-state-hover: color-mix(in srgb, var(--md-seed) 6%, transparent);

            /* --- Peran permukaan diturunkan dari seed, bukan abu-abu netral --- */
            --md-sys-color-surface: #ffffff;
            --md-sys-color-surface-container-lowest: #ffffff;
            --md-sys-color-surface-container-low: color-mix(in srgb, var(--md-seed) 3%, #ffffff);
            --md-sys-color-surface-container: color-mix(in srgb, var(--md-seed) 6%, #ffffff);
            --md-sys-color-surface-container-high: color-mix(in srgb, var(--md-seed) 9%, #ffffff);
            --md-sys-color-surface-container-highest: color-mix(in srgb, var(--md-seed) 12%, #ffffff);
            --md-sys-color-surface-dim: color-mix(in srgb, var(--md-seed) 10%, #ffffff);
            --md-sys-color-surface-bright: color-mix(in srgb, var(--md-seed) 2%, #ffffff);
            --md-sys-color-surface-tint: var(--md-seed);
            --md-sys-color-on-surface: #1f1f1f;
            --md-sys-color-on-surface-variant: #444746;
            --md-sys-color-outline: #747775;
            --md-sys-color-outline-variant: #c4c7c5;
            --md-sys-color-inverse-surface: #303030;
            --md-sys-color-inverse-on-surface: #f2f2f2;
            --md-sys-color-inverse-primary: color-mix(in srgb, var(--md-seed) 32%, #ffffff);
            --md-sys-color-scrim: rgb(31 31 31 / .42);
            --md-sys-color-shadow: #000000;

            /* ---- Gradient M3: tone-on-tone, hue tetap, hanya lightness bergeser ---- */
            --grad-hero: linear-gradient(135deg,
                    var(--md-sys-color-surface-container-lowest) 0%,
                    color-mix(in srgb, var(--md-seed) 8%, #ffffff) 48%,
                    color-mix(in srgb, var(--md-seed) 15%, #ffffff) 100%);
            --grad-page: linear-gradient(180deg,
                    color-mix(in srgb, var(--md-seed) 4%, #ffffff) 0%,
                    var(--md-sys-color-surface-container-low) 38%);
            --grad-sheen: linear-gradient(180deg, rgb(255 255 255 / .5), rgb(255 255 255 / 0));
            --grad-band: linear-gradient(112deg, transparent 34%, rgb(255 255 255 / .34) 44%, transparent 56%);
            --grad-banner: linear-gradient(90deg,
                    color-mix(in srgb, var(--md-seed) 10%, #ffffff),
                    color-mix(in srgb, var(--md-seed) 4%, #ffffff));

            /* --- Typescale: Material Web memakai Figtree, bukan Roboto --- */
            --md-ref-typeface-plain: "Figtree", ui-sans-serif, system-ui, sans-serif;
            --md-ref-typeface-brand: "Figtree", ui-sans-serif, system-ui, sans-serif;

            /* --- Shape scale (satu aturan untuk seluruh admin) --- */
            --md-shape-field: 12px;
            --md-shape-card: 16px;
            --md-shape-dialog: 28px;
            --md-shape-pill: 9999px;

            /* --- Elevation M3 --- */
            --md-elevation-1: 0 1px 2px 0 rgb(0 0 0 / .30), 0 1px 3px 1px rgb(0 0 0 / .15);
            --md-elevation-2: 0 1px 2px 0 rgb(0 0 0 / .30), 0 2px 6px 2px rgb(0 0 0 / .15);
            --md-elevation-3: 0 4px 8px 3px rgb(0 0 0 / .15), 0 1px 3px 0 rgb(0 0 0 / .30);

            /* --- State layer M3 --- */
            --md-state-hover: .08;
            --md-state-focus: .12;
            --md-state-press: .12;

            /* ---- Motion M3: sys duration + easing ----
               Semua transisi di layer ini mengacu ke token ini, bukan angka ad-hoc,
               supaya seluruh admin bergerak dengan tempo dan kurva yang sama. */
            --md-sys-motion-duration-short1: 50ms;
            --md-sys-motion-duration-short2: 100ms;
            --md-sys-motion-duration-short3: 150ms;
            --md-sys-motion-duration-short4: 200ms;
            --md-sys-motion-duration-medium1: 250ms;
            --md-sys-motion-duration-medium2: 300ms;
            --md-sys-motion-duration-medium3: 350ms;
            --md-sys-motion-duration-medium4: 400ms;
            --md-sys-motion-duration-long1: 450ms;
            --md-sys-motion-duration-long2: 500ms;

            --md-sys-motion-easing-linear: linear;
            --md-sys-motion-easing-standard: cubic-bezier(.2, 0, 0, 1);
            --md-sys-motion-easing-standard-decelerate: cubic-bezier(0, 0, 0, 1);
            --md-sys-motion-easing-standard-accelerate: cubic-bezier(.3, 0, 1, 1);
            --md-sys-motion-easing-emphasized: cubic-bezier(.2, 0, 0, 1);
            --md-sys-motion-easing-emphasized-decelerate: cubic-bezier(.05, .7, .1, 1);
            --md-sys-motion-easing-emphasized-accelerate: cubic-bezier(.3, 0, .8, .15);
            /* Hanya untuk indikator navigasi (satu-satunya efek pegas). */
            --md-sys-motion-easing-spring: cubic-bezier(.34, 1.28, .64, 1);
        }

        /* Fallback: browser tanpa color-mix() tetap mendapat nilai sebelumnya,
           sehingga tidak ada regresi tampilan di lingkungan lama. */
        @supports not (background: color-mix(in srgb, red 10%, white)) {
            :root {
                --md-sys-color-primary-container: #d3e3fd;
                --md-sys-color-on-primary-container: #041e49;
                --md-sys-color-primary-fixed: #d3e3fd;
                --md-sys-color-primary-fixed-dim: #a8c7fa;
                --md-sys-color-on-primary-fixed: #041e49;
                --md-sys-color-on-primary-fixed-variant: #0842a0;
                --md-sys-color-secondary-container: #c2e7ff;
                --md-sys-color-on-secondary-container: #001d35;
                --md-sys-color-tertiary-container: #c4eed0;
                --md-sys-color-on-tertiary-container: #072711;
                --md-sys-color-error-container: #f9dedc;
                --md-sys-color-on-error-container: #410e0b;
                --md-sys-color-warning-container: #ffe08a;
                --md-sys-color-surface-container-low: #f8fafd;
                --md-sys-color-surface-container: #f0f4f9;
                --md-sys-color-surface-container-high: #e9eef6;
                --md-sys-color-surface-container-highest: #e1e6ee;
                --md-sys-color-surface-dim: #f0f4f9;
                --md-sys-color-surface-bright: #f8fafd;
                --md-sys-color-inverse-primary: #a8c7fa;
                --app-state-hover: rgb(11 87 208 / .06);
                --grad-hero: linear-gradient(135deg, #ffffff 0%, #e8f0fe 48%, #d3e3fd 100%);
                --grad-page: linear-gradient(180deg, #f4f8fe 0%, #f8fafd 38%);
                --grad-banner: linear-gradient(90deg, #e8f0fe, #f4f8fe);
            }
        }

        /* Drawer admin: di layar sempit selalu tertutup selama Alpine belum
           menandai `data-drawer-open`, sehingga konten tidak pernah tertutup
           walaupun JavaScript gagal dimuat. */
        @media (max-width: 1023.98px) {
            .admin-drawer:not([data-drawer-open]) {
                transform: translateX(-100%);
            }
        }

        /* Drawer dan offset konten bergerak dengan kurva emphasized milik M3,
           bukan utility transisi ad-hoc, supaya rail dan kolom konten selalu
           tampak sebagai satu gerakan. */
        .admin-drawer {
            transition:
                width var(--md-sys-motion-duration-medium2) var(--md-sys-motion-easing-emphasized),
                transform var(--md-sys-motion-duration-medium1) var(--md-sys-motion-easing-emphasized-decelerate);
        }

        .admin-content {
            transition: margin-left var(--md-sys-motion-duration-medium2) var(--md-sys-motion-easing-emphasized);
        }

        /* Rail (6rem) vs drawer (15rem): lebar drawer dan offset konten dibaca
           dari satu atribut yang sama, jadi keduanya mustahil tidak sinkron. */
        @media (min-width: 1024px) {
            .admin-shell[data-sidebar-collapsed] .admin-drawer {
                width: 6rem;
            }

            .admin-shell[data-sidebar-collapsed] .admin-content {
                margin-left: 6rem;
            }
        }

        /* Ikon Material Symbols (ligature font, tanpa dependensi JS). */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 1.25rem;
            line-height: 1;
            user-select: none;
            flex-shrink: 0;
        }

        /* Sembunyikan elemen Alpine sampai Alpine siap, supaya tidak ada flash. */
        [x-cloak] {
            display: none !important;
        }

        /* ===================== 1. Kerangka halaman ===================== */

        .legacy-admin {
            color: var(--md-sys-color-on-surface);
        }

        .legacy-admin :is(h1, h2, h3, h4) {
            text-wrap: balance;
        }

        /* Angka rapi berkolom (tabel data, nominal, timer). */
        .legacy-admin :is(table td, table th) {
            font-variant-numeric: tabular-nums;
        }

        .legacy-admin :is(a, button, [role="button"], label[for]) {
            touch-action: manipulation;
        }

        /* Permukaan bergradasi M3: dibatasi ke dua tempat (wash halaman dan hero)
           supaya tidak jadi dekorasi berlebihan dan tetap murah digambar. */
        .m3-wash {
            background-image: var(--grad-page);
            background-repeat: no-repeat;
            background-size: 100% 28rem;
        }

        .m3-sheen {
            background-image: var(--grad-sheen);
        }

        .m3-hero {
            background-image: var(--grad-hero);
        }

        .m3-hero-band {
            position: absolute;
            inset: 0;
            background-image: var(--grad-band);
            pointer-events: none;
        }

        /* Lewati navigasi ke konten utama. */
        .m3-skip-link {
            position: fixed;
            top: -100px;
            left: 1rem;
            z-index: 100;
            padding: .625rem 1.25rem;
            border-radius: var(--md-shape-pill);
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            font-size: .875rem;
            font-weight: 600;
            transition: top var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard);
        }

        .m3-skip-link:focus {
            top: 1rem;
        }

        /* ===================== 2. Indikator fokus ===================== */

        .legacy-admin :is(a, button, input, select, textarea, summary, [tabindex]):focus-visible {
            outline: 2px solid var(--md-sys-color-primary);
            outline-offset: 2px;
        }

        .legacy-admin .m3-field:focus-visible,
        .legacy-admin .m3-select:focus-visible {
            outline-offset: -1px;
        }

        /* ===================== 3. Tombol Material 3 ===================== */

        .m3-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 2.5rem;
            padding-inline: 1.25rem;
            border: 1px solid transparent;
            border-radius: var(--md-shape-pill);
            font-size: .875rem;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            cursor: pointer;
            transition:
                box-shadow var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                background-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                transform var(--md-sys-motion-duration-short2) var(--md-sys-motion-easing-emphasized);
        }

        .m3-btn:active {
            transform: scale(.98);
            transition-duration: var(--md-sys-motion-duration-short1);
        }

        .m3-btn:disabled,
        .m3-btn[disabled],
        .m3-btn[aria-disabled="true"] {
            cursor: not-allowed;
            opacity: .38;
            transform: none;
        }

        .m3-btn .material-symbols-outlined {
            font-size: 1.25rem;
        }

        /* State layer M3: lapisan warna DI ATAS permukaan tombol, bukan warna baru.
           ::after dipakai supaya hover/press tidak mengubah latar aslinya — jadi
           satu aturan ini benar untuk filled, tonal, outlined, dan text sekaligus.
           md-ripple (Material Web) menambahkan riak di titik sentuh. */
        .m3-btn,
        .m3-icon-btn {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .m3-btn::after,
        .m3-icon-btn::after {
            content: "";
            position: absolute;
            inset: 0;
            background: currentColor;
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--md-sys-motion-duration-short2) var(--md-sys-motion-easing-standard);
        }

        .m3-btn:hover::after,
        .m3-icon-btn:hover::after {
            opacity: var(--md-state-hover);
        }

        .m3-btn:focus-visible::after,
        .m3-icon-btn:focus-visible::after {
            opacity: var(--md-state-focus);
        }

        .m3-btn:active::after,
        .m3-icon-btn:active::after {
            opacity: var(--md-state-press);
        }

        /* Ripple Material Web harus tetap di bawah label dan ikon. */
        .m3-btn>md-ripple,
        .m3-icon-btn>md-ripple,
        .admin-drawer :is(a, button)>md-ripple {
            position: absolute;
            inset: 0;
        }

        /* Item navigasi: state layer M3, bukan transisi warna ad-hoc per elemen. */
        .admin-drawer :is(a, button) {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            transition:
                background-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard);
        }

        .admin-drawer :is(a, button)::after {
            content: "";
            position: absolute;
            inset: 0;
            background: currentColor;
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--md-sys-motion-duration-short2) var(--md-sys-motion-easing-standard);
        }

        .admin-drawer :is(a, button):hover::after {
            opacity: var(--md-state-hover);
        }

        .admin-drawer :is(a, button):active::after {
            opacity: var(--md-state-press);
        }

        /* Satu-satunya tempat pegas dipakai: indikator halaman aktif. Perpindahan
           antar-menu terasa seperti karet, sementara sisanya tetap standard M3. */
        .admin-drawer :is(a, button)[aria-current="page"] {
            transition: background-color var(--md-sys-motion-duration-medium1) var(--md-sys-motion-easing-spring),
                color var(--md-sys-motion-duration-medium1) var(--md-sys-motion-easing-spring);
        }

        /* Filled — aksi utama. */
        .m3-btn--filled {
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
        }

        .m3-btn--filled:hover {
            box-shadow: var(--md-elevation-1);
        }

        /* Filled tonal — aksi sekunder yang tetap terlihat. */
        .m3-btn--tonal {
            background: var(--md-sys-color-secondary-container);
            color: var(--md-sys-color-on-secondary-container);
        }

        .m3-btn--tonal:hover {
            box-shadow: var(--md-elevation-1);
        }

        /* Outlined — aksi tersier. */
        .m3-btn--outlined {
            background: transparent;
            border-color: var(--md-sys-color-outline);
            color: var(--md-sys-color-primary);
        }

        .m3-btn--outlined:hover {
            background: color-mix(in srgb, var(--md-sys-color-primary) 8%, transparent);
        }

        /* Text — aksi paling ringan (batal, kembali). */
        .m3-btn--text {
            background: transparent;
            color: var(--md-sys-color-primary);
            padding-inline: 1rem;
        }

        .m3-btn--text:hover {
            background: color-mix(in srgb, var(--md-sys-color-primary) 8%, transparent);
        }

        /* Destruktif — pakai warna error semantik, bukan merah acak. */
        .m3-btn--danger {
            background: var(--md-sys-color-error);
            color: var(--md-sys-color-on-error);
        }

        .m3-btn--danger:hover {
            box-shadow: var(--md-elevation-1);
        }

        .m3-btn--danger-tonal {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        /* Peringatan — "butuh tindak lanjut". */
        .m3-btn--warning {
            background: var(--md-sys-color-warning-container);
            color: var(--md-sys-color-on-warning-container);
        }

        .m3-btn--success {
            background: var(--md-sys-color-tertiary-container);
            color: var(--md-sys-color-on-tertiary-container);
        }

        /* Ukuran. */
        .m3-btn--sm {
            min-height: 2rem;
            padding-inline: .875rem;
            font-size: .8125rem;
        }

        .m3-btn--xs {
            min-height: 1.75rem;
            padding-inline: .75rem;
            font-size: .75rem;
        }

        .m3-btn--block {
            width: 100%;
        }

        /* Tombol ikon-saja: selalu sertakan aria-label di markup. */
        .m3-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--md-shape-pill);
            border: 1px solid transparent;
            color: var(--md-sys-color-on-surface-variant);
            background: transparent;
            cursor: pointer;
            transition:
                background-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                transform var(--md-sys-motion-duration-short2) var(--md-sys-motion-easing-emphasized);
        }

        .m3-icon-btn:hover {
            background: color-mix(in srgb, var(--md-sys-color-on-surface) 8%, transparent);
        }

        .m3-icon-btn:active {
            transform: scale(.94);
            transition-duration: var(--md-sys-motion-duration-short1);
        }

        .m3-icon-btn--sm {
            width: 2rem;
            height: 2rem;
            font-size: 1.125rem;
        }

        .m3-icon-btn--danger {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        /* Chip aksi/segmented — pengganti grup tombol daisyUI. */
        .m3-segmented {
            display: inline-flex;
            gap: .25rem;
            padding: .25rem;
            border-radius: var(--md-shape-pill);
            background: var(--md-sys-color-surface-container);
        }

        .m3-segmented>a,
        .m3-segmented>button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            min-height: 2rem;
            padding-inline: .875rem;
            border-radius: var(--md-shape-pill);
            font-size: .8125rem;
            font-weight: 600;
            color: var(--md-sys-color-on-surface-variant);
        }

        .m3-segmented>[aria-current="page"],
        .m3-segmented>.is-active {
            background: var(--md-sys-color-surface);
            color: var(--md-sys-color-primary);
            box-shadow: var(--md-elevation-1);
        }

        /* ===================== 4. Field & select ===================== */

        .m3-field,
        .m3-select {
            width: 100%;
            min-height: 2.5rem;
            padding: .5rem .875rem;
            border: 1px solid var(--md-sys-color-outline-variant);
            border-radius: var(--md-shape-field);
            background: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
            font-size: .875rem;
            line-height: 1.25rem;
            transition:
                border-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                background-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard);
        }

        .m3-field::placeholder {
            color: color-mix(in srgb, var(--md-sys-color-on-surface-variant) 70%, transparent);
        }

        .m3-field:hover,
        .m3-select:hover {
            border-color: var(--md-sys-color-on-surface-variant);
        }

        .m3-field:focus,
        .m3-select:focus {
            border-color: var(--md-sys-color-primary);
            outline: 2px solid var(--md-sys-color-primary);
            outline-offset: -1px;
        }

        .m3-field[aria-invalid="true"],
        .m3-field.is-invalid {
            border-color: var(--md-sys-color-error);
        }

        .m3-field-help {
            font-size: .75rem;
            line-height: 1rem;
            color: var(--md-sys-color-on-surface-variant);
        }

        .m3-field-error {
            font-size: .75rem;
            line-height: 1rem;
            color: var(--md-sys-color-error);
        }

        .m3-label {
            display: block;
            margin-bottom: .375rem;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .01em;
            color: var(--md-sys-color-on-surface-variant);
        }

        /* Field dengan ikon di dalamnya. */
        .m3-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .m3-input-group>.material-symbols-outlined:first-child {
            position: absolute;
            left: .75rem;
            font-size: 1.125rem;
            color: var(--md-sys-color-on-surface-variant);
            pointer-events: none;
        }

        .m3-input-group>.material-symbols-outlined:first-child~.m3-field {
            padding-left: 2.5rem;
        }

        /* ===================== 5. Tabel data ===================== */

        .m3-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: .8125rem;
            background: var(--md-sys-color-surface-container-low);
        }

        .m3-table :where(thead th) {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: .625rem .75rem;
            background: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface-variant);
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            text-align: left;
            white-space: nowrap;
        }

        .m3-table :where(tbody td) {
            padding: .625rem .75rem;
            color: var(--md-sys-color-on-surface);
            vertical-align: top;
            border-top: 1px solid var(--md-sys-color-outline-variant);
        }

        .m3-table :where(tbody tr:hover td) {
            background: color-mix(in srgb, var(--md-sys-color-primary) 6%, transparent);
        }

        .m3-table :where(tbody tr:last-child td) {
            border-bottom: 1px solid var(--md-sys-color-outline-variant);
        }

        /* ===================== 6. Chip, banner, skeleton ===================== */

        .m3-chip {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .1875rem .625rem;
            border-radius: var(--md-shape-pill);
            font-size: .6875rem;
            font-weight: 600;
            line-height: 1rem;
            white-space: nowrap;
        }

        .m3-chip .material-symbols-outlined {
            font-size: .875rem;
        }

        .m3-chip--success {
            background: var(--md-sys-color-tertiary-container);
            color: var(--md-sys-color-on-tertiary-container);
        }

        .m3-chip--info {
            background: var(--md-sys-color-secondary-container);
            color: var(--md-sys-color-on-secondary-container);
        }

        .m3-chip--warning {
            background: var(--md-sys-color-warning-container);
            color: var(--md-sys-color-on-warning-container);
        }

        .m3-chip--error {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        .m3-chip--neutral {
            background: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface-variant);
        }

        .m3-banner {
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            padding: .75rem 1rem;
            border-radius: var(--md-shape-field);
            font-size: .8125rem;
            line-height: 1.25rem;
        }

        .m3-banner--error {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        .m3-banner--warning {
            background: var(--md-sys-color-warning-container);
            color: var(--md-sys-color-on-warning-container);
        }

        .m3-banner--info {
            background: var(--md-sys-color-secondary-container);
            color: var(--md-sys-color-on-secondary-container);
        }

        /* Skeleton menggantikan spinner berputar untuk pemuatan > 300ms. */
        .m3-skeleton {
            border-radius: var(--md-shape-field);
            background: linear-gradient(90deg,
                    var(--md-sys-color-surface-container-high) 25%,
                    var(--md-sys-color-surface-container-highest) 37%,
                    var(--md-sys-color-surface-container-high) 63%);
            background-size: 400% 100%;
            animation: m3-skeleton-shimmer 1.4s var(--md-sys-motion-easing-linear) infinite;
        }

        .m3-skeleton--text {
            height: .75rem;
            border-radius: var(--md-shape-pill);
        }

        @keyframes m3-skeleton-shimmer {
            0% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0 50%;
            }
        }

        /* Keadaan kosong yang terkomposisi, bukan tabel kosong. */
        .m3-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .75rem;
            padding: 2.5rem 1.25rem;
            text-align: center;
            color: var(--md-sys-color-on-surface-variant);
        }

        .m3-empty>.material-symbols-outlined {
            display: grid;
            place-items: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: var(--md-shape-pill);
            background: var(--md-sys-color-surface-container-high);
            font-size: 1.5rem;
        }

        /* Tab yang bisa di-scroll horizontal di layar sempit. */
        .m3-scroll-x {
            overflow-x: auto;
            overscroll-behavior-inline: contain;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--md-sys-color-outline-variant);
            border-radius: var(--md-shape-pill);
        }

        /* ===================== 7. Shim legacy =====================
           Blok di bawah menormalkan halaman lama (daisyUI bumblebee + utility
           Tailwind yang tidak konsisten) ke nilai Material 3. Ini jembatan
           sementara: setiap kelas yang dimigrasikan ke komponen M3 di atas
           mengurangi kebutuhan shim ini sampai bisa dihapus. */

        .legacy-admin .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            height: auto;
            min-height: 2.5rem;
            padding: .5rem 1.125rem;
            border: 1px solid transparent;
            border-radius: var(--md-shape-pill);
            font-size: .8125rem;
            font-weight: 600;
            text-transform: none;
            animation: none;
            box-shadow: none;
            transition:
                box-shadow var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                background-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                transform var(--md-sys-motion-duration-short2) var(--md-sys-motion-easing-emphasized);
        }

        .legacy-admin .btn:active {
            transform: scale(.98);
        }

        .legacy-admin .btn-primary {
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
        }

        .legacy-admin .btn-error {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        .legacy-admin .btn-info {
            background: var(--md-sys-color-secondary-container);
            color: var(--md-sys-color-on-secondary-container);
        }

        .legacy-admin .btn-success {
            background: var(--md-sys-color-tertiary-container);
            color: var(--md-sys-color-on-tertiary-container);
        }

        .legacy-admin .btn-warning {
            background: var(--md-sys-color-warning-container);
            color: var(--md-sys-color-on-warning-container);
        }

        .legacy-admin .btn-ghost {
            background: transparent;
            color: var(--md-sys-color-on-surface-variant);
        }

        .legacy-admin .btn-xs {
            min-height: 1.75rem;
            padding: .25rem .625rem;
            font-size: .75rem;
        }

        .legacy-admin .btn-sm {
            min-height: 2rem;
            padding: .375rem .875rem;
            font-size: .8125rem;
        }

        .legacy-admin .input,
        .legacy-admin .select,
        .legacy-admin .file-input,
        .legacy-admin textarea {
            height: auto;
            min-height: 2.5rem;
            padding: .5rem .875rem;
            border: 1px solid var(--md-sys-color-outline-variant);
            border-radius: var(--md-shape-field);
            background: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
            font-size: .8125rem;
            box-shadow: none;
        }

        .legacy-admin .input:focus,
        .legacy-admin .select:focus,
        .legacy-admin textarea:focus {
            border-color: var(--md-sys-color-primary);
            outline: 2px solid var(--md-sys-color-primary);
            outline-offset: -1px;
        }

        .legacy-admin textarea {
            min-height: 6rem;
            padding: .625rem .875rem;
        }

        .legacy-admin .badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            height: auto;
            padding: .1875rem .625rem;
            border: 0;
            border-radius: var(--md-shape-pill);
            font-size: .6875rem;
            font-weight: 600;
            color: var(--md-sys-color-on-secondary-container);
            background: var(--md-sys-color-secondary-container);
        }

        .legacy-admin .badge-success {
            background: var(--md-sys-color-tertiary-container);
            color: var(--md-sys-color-on-tertiary-container);
        }

        .legacy-admin .badge-warning {
            background: var(--md-sys-color-warning-container);
            color: var(--md-sys-color-on-warning-container);
        }

        .legacy-admin .badge-error {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        .legacy-admin .alert {
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            padding: .75rem 1rem;
            border: 0;
            border-radius: var(--md-shape-field);
            font-size: .8125rem;
        }

        .legacy-admin .alert-error {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        .legacy-admin .alert-warning {
            background: var(--md-sys-color-warning-container);
            color: var(--md-sys-color-on-warning-container);
        }

        .legacy-admin .alert-info {
            background: var(--md-sys-color-secondary-container);
            color: var(--md-sys-color-on-secondary-container);
        }

        .legacy-admin .loading {
            border-width: 2px;
            color: var(--md-sys-color-primary);
        }

        .legacy-admin .table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: var(--md-shape-card);
            overflow: hidden;
            border: 1px solid var(--md-sys-color-outline-variant);
            background: var(--md-sys-color-surface-container-low);
        }

        .legacy-admin .table :where(thead th) {
            background: var(--md-sys-color-surface-container-high) !important;
            color: var(--md-sys-color-on-surface-variant);
            font-size: .6875rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .legacy-admin .table :where(tbody td) {
            font-size: .8125rem;
            color: var(--md-sys-color-on-surface);
            vertical-align: top;
        }

        .legacy-admin .table tr:hover td {
            background: color-mix(in srgb, var(--md-sys-color-primary) 6%, transparent);
        }

        .legacy-admin .shadow,
        .legacy-admin .shadow-md {
            box-shadow: var(--md-elevation-1) !important;
        }

        /* ===================== 8. Jembatan palet =====================
           Halaman lama memakai belasan warna Tailwind sekaligus (biru, indigo,
           sky, emerald, amber, merah). Material 3 hanya punya SATU aksen, jadi
           kelas warna lama dipetakan ke peran M3 yang setara di dalam area admin.
           Aturan "satu aksen" ini yang paling terasa membuat halaman terlihat
           satu sistem, bukan tempelan. Hapus blok ini setelah migrasi markup
           ke komponen M3 selesai. */

        /* Aksi utama -> primary */
        .legacy-admin :is(.bg-blue-700, .bg-blue-600, .bg-blue-500) {
            background-color: var(--md-sys-color-primary) !important;
        }

        /* Informasi sekunder -> secondary */
        .legacy-admin :is(.bg-indigo-700, .bg-indigo-600, .bg-indigo-500, .bg-sky-600, .bg-sky-500) {
            background-color: var(--md-sys-color-secondary) !important;
        }

        /* Destruktif -> error */
        .legacy-admin :is(.bg-red-700, .bg-red-600, .bg-red-500, .bg-rose-600, .bg-rose-500) {
            background-color: var(--md-sys-color-error) !important;
        }

        /* Sukses -> tertiary */
        .legacy-admin :is(.bg-emerald-700, .bg-emerald-600, .bg-emerald-500, .bg-green-700, .bg-green-600, .bg-green-500, .bg-teal-600) {
            background-color: var(--md-sys-color-tertiary) !important;
        }

        /* Permukaan bertinta (50/100/200) -> container M3.
           Hanya shade terang: shade gelap biasanya berpasangan dengan teks
           putih, jadi sengaja tidak diubah agar kontrasnya tetap aman. */
        .legacy-admin :is(.bg-blue-50, .bg-blue-100, .bg-blue-200, .bg-indigo-50, .bg-indigo-100, .bg-sky-50, .bg-sky-100) {
            background-color: var(--md-sys-color-primary-container) !important;
        }

        .legacy-admin :is(.bg-emerald-50, .bg-emerald-100, .bg-emerald-200, .bg-green-50, .bg-green-100, .bg-teal-50, .bg-teal-100) {
            background-color: var(--md-sys-color-tertiary-container) !important;
        }

        .legacy-admin :is(.bg-red-50, .bg-red-100, .bg-rose-50, .bg-rose-100, .bg-pink-50, .bg-pink-100) {
            background-color: var(--md-sys-color-error-container) !important;
        }

        .legacy-admin :is(.bg-amber-50, .bg-amber-100, .bg-amber-200, .bg-yellow-50, .bg-yellow-100, .bg-yellow-200, .bg-orange-50, .bg-orange-100) {
            background-color: var(--md-sys-color-warning-container) !important;
        }

        .legacy-admin :is(.bg-purple-50, .bg-purple-100, .bg-violet-50, .bg-violet-100, .bg-fuchsia-50, .bg-fuchsia-100) {
            background-color: var(--md-sys-color-secondary-container) !important;
        }

        /* Permukaan netral -> surface container M3. */
        .legacy-admin :is(.bg-gray-50, .bg-gray-100, .bg-slate-50, .bg-slate-100, .bg-zinc-50, .bg-zinc-100) {
            background-color: var(--md-sys-color-surface-container) !important;
        }

        .legacy-admin :is(.bg-gray-200, .bg-slate-200, .bg-zinc-200) {
            background-color: var(--md-sys-color-surface-container-high) !important;
        }

        /* Abu gelap warisan (kerangka kartu, kepala tabel lama) -> permukaan M3. */
        .legacy-admin :is(.bg-gray-300, .bg-slate-300, .bg-slate-400) {
            background-color: var(--md-sys-color-surface-container-highest) !important;
        }

        .legacy-admin .bg-slate-500 {
            background-color: var(--md-sys-color-surface-container-high) !important;
        }

        /* Hover: state layer M3 di atas warnanya sendiri, bukan warna baru. */
        .legacy-admin :is(.hover\:bg-gray-50, .hover\:bg-gray-100, .hover\:bg-slate-50, .hover\:bg-slate-100):hover {
            background-color: var(--app-state-hover) !important;
        }

        .legacy-admin :is(.hover\:bg-blue-700, .hover\:bg-blue-600, .hover\:bg-indigo-700, .hover\:bg-sky-600):hover {
            background-color: color-mix(in srgb, var(--md-sys-color-primary) 88%, #ffffff) !important;
        }

        .legacy-admin :is(.hover\:bg-red-700, .hover\:bg-red-600, .hover\:bg-red-500):hover {
            background-color: color-mix(in srgb, var(--md-sys-color-error) 88%, #ffffff) !important;
        }

        .legacy-admin :is(.hover\:bg-emerald-600, .hover\:bg-emerald-700, .hover\:bg-green-700, .hover\:bg-teal-600):hover {
            background-color: color-mix(in srgb, var(--md-sys-color-tertiary) 88%, #ffffff) !important;
        }

        .legacy-admin :is(.hover\:bg-blue-50, .hover\:bg-blue-100, .hover\:bg-indigo-50, .hover\:bg-indigo-100):hover {
            background-color: color-mix(in srgb, var(--md-sys-color-primary-container) 80%, var(--md-sys-color-primary)) !important;
        }

        .legacy-admin :is(.hover\:bg-red-100, .hover\:bg-red-200, .hover\:bg-rose-100):hover {
            background-color: color-mix(in srgb, var(--md-sys-color-error-container) 80%, var(--md-sys-color-error)) !important;
        }

        .legacy-admin :is(.hover\:bg-amber-100, .hover\:bg-amber-200, .hover\:bg-yellow-100, .hover\:bg-yellow-200):hover {
            background-color: color-mix(in srgb, var(--md-sys-color-warning-container) 82%, var(--md-sys-color-on-warning-container)) !important;
        }

        /* Teks netral -> on-surface / on-surface-variant (satu suhu netral). */
        .legacy-admin :is(.text-gray-900, .text-gray-800, .text-slate-900, .text-slate-800) {
            color: var(--md-sys-color-on-surface) !important;
        }

        .legacy-admin :is(.text-gray-700, .text-gray-600, .text-gray-500, .text-slate-700, .text-slate-600, .text-slate-500) {
            color: var(--md-sys-color-on-surface-variant) !important;
        }

        .legacy-admin :is(.text-blue-800, .text-blue-700, .text-blue-600, .text-blue-500, .text-indigo-600, .text-indigo-700, .text-sky-600, .text-sky-700) {
            color: var(--md-sys-color-primary) !important;
        }

        .legacy-admin :is(.text-red-800, .text-red-700, .text-red-600, .text-red-500, .text-rose-600) {
            color: var(--md-sys-color-error) !important;
        }

        .legacy-admin :is(.text-emerald-800, .text-emerald-700, .text-emerald-600, .text-green-700, .text-green-600, .text-teal-700, .text-teal-600) {
            color: var(--md-sys-color-tertiary) !important;
        }

        .legacy-admin :is(.text-amber-800, .text-amber-700, .text-amber-600, .text-yellow-800, .text-yellow-700, .text-yellow-600) {
            color: var(--md-sys-color-on-warning-container) !important;
        }

        /* Bentuk tombol: Material 3 memakai pil penuh untuk aksi. Tombol lama
           memakai rounded-md/lg/xl (8-12px), sehingga satu halaman bisa punya
           tiga radius berbeda. Aturan ini hanya menyentuh elemen aksi
           (a/button dengan inline-flex/inline-block), bukan kartu atau tile. */
        .legacy-admin :is(a, button):is(.inline-flex, .inline-block):is(.rounded-md, .rounded-lg, .rounded-xl) {
            border-radius: var(--md-shape-pill);
        }

        /* Label field lama dicetak huruf kapital semua. Material 3 memakai
           sentence case, dan teksnya sudah ditulis normal di markup, jadi
           hanya tampilannya yang perlu dinormalkan. */
        .legacy-admin label.uppercase {
            text-transform: none;
            letter-spacing: .01em;
        }

        /* gray-400 ke bawah gagal kontras AA untuk teks; outline M3 lolos. */
        .legacy-admin :is(.text-gray-400, .text-slate-400, .text-zinc-400) {
            color: var(--md-sys-color-outline) !important;
        }

        .legacy-admin :is(input, textarea)::placeholder {
            color: var(--md-sys-color-outline);
            opacity: 1;
        }

        .legacy-admin .rounded-md {
            border-radius: var(--md-shape-field) !important;
        }

        .legacy-admin .rounded {
            border-radius: var(--md-shape-field) !important;
        }

        /* --- Kontrol pilihan (checkbox/radio/toggle) ---
           Tema daisyUI "bumblebee" mewarnai kontrol dengan kuning-amber, yang
           bertabrakan dengan aksen biru M3. Kontrol di normalkan ke spesifikasi
           Material 3: kotak 18px radius 2px, tercentang = primary + tanda centang putih. */
        /* `:not(.toggle)` penting: daisyUI memakai <input type=checkbox class=toggle>,
           dan tanpa pengecualian ini aturan checkbox (spesifisitas menang) akan
           menimpa bentuk switch-nya sehingga toggle tampil sebagai kotak 18px. */
        .legacy-admin :is(.checkbox, input[type="checkbox"]):not(.sr-only):not(.toggle) {
            appearance: none;
            -webkit-appearance: none;
            flex-shrink: 0;
            width: 1.125rem;
            height: 1.125rem;
            margin: 0;
            border: 2px solid var(--md-sys-color-on-surface-variant);
            border-radius: 2px;
            background-color: transparent;
            background-position: center;
            background-repeat: no-repeat;
            /* Tanda centang tumbuh dari 0 -> 100%: background-size animatable,
               berbeda dari background-image yang tidak bisa ditransisikan. */
            background-size: 0 0;
            cursor: pointer;
            transition:
                background-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                border-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                background-size var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-emphasized-decelerate);
        }

        .legacy-admin :is(.checkbox, input[type="checkbox"]):not(.sr-only):not(.toggle):hover {
            border-color: var(--md-sys-color-on-surface);
        }

        .legacy-admin :is(.checkbox, input[type="checkbox"]):not(.sr-only):not(.toggle):checked,
        .legacy-admin :is(.checkbox, input[type="checkbox"]):not(.sr-only):not(.toggle):indeterminate {
            border-color: var(--md-sys-color-primary);
            background-color: var(--md-sys-color-primary);
        }

        .legacy-admin :is(.checkbox, input[type="checkbox"]):not(.sr-only):not(.toggle):checked {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffffff'%3E%3Cpath d='M9.55 17.6 4 12.05l1.4-1.4 4.15 4.15L18.6 5.75 20 7.15z'/%3E%3C/svg%3E");
            background-size: 100% 100%;
        }

        .legacy-admin :is(.checkbox, input[type="checkbox"]):not(.sr-only):not(.toggle):indeterminate {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffffff'%3E%3Crect x='5' y='10.75' width='14' height='2.5'/%3E%3C/svg%3E");
            background-size: 100% 100%;
        }

        .legacy-admin :is(.checkbox, input[type="checkbox"]):not(.sr-only):not(.toggle):disabled {
            border-color: var(--md-sys-color-outline-variant);
            cursor: not-allowed;
        }

        .legacy-admin .checkbox-sm,
        .legacy-admin input[type="checkbox"].checkbox-sm {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }

        .legacy-admin .checkbox-xs,
        .legacy-admin input[type="checkbox"].checkbox-xs {
            width: .875rem;
            height: .875rem;
            border-width: 1.5px;
        }

        .legacy-admin :is(.radio, input[type="radio"]):not(.sr-only) {
            appearance: none;
            -webkit-appearance: none;
            flex-shrink: 0;
            width: 1.125rem;
            height: 1.125rem;
            margin: 0;
            border: 2px solid var(--md-sys-color-on-surface-variant);
            border-radius: var(--md-shape-pill);
            background-color: transparent;
            cursor: pointer;
            transition:
                border-color var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-standard),
                box-shadow var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-emphasized-decelerate);
        }

        .legacy-admin :is(.radio, input[type="radio"]):not(.sr-only):checked {
            border-color: var(--md-sys-color-primary);
            box-shadow: inset 0 0 0 .3rem var(--md-sys-color-primary);
        }

        /* Toggle: thumb digambar dengan background agar tetap tampil di <input>.
           Thumb digeser lewat background-position dan warnanya lewat custom
           property terdaftar — dua-duanya animatable, sedangkan menukar
           background-image seperti sebelumnya tidak bisa dianimasikan. */
        @property --md-toggle-thumb {
            syntax: "<color>";
            inherits: false;
            initial-value: transparent;
        }

        .legacy-admin .toggle {
            appearance: none;
            -webkit-appearance: none;
            flex-shrink: 0;
            width: 3rem;
            height: 1.75rem;
            --md-toggle-thumb: var(--md-sys-color-outline);
            border: 2px solid var(--md-sys-color-outline);
            border-radius: var(--md-shape-pill);
            background-color: var(--md-sys-color-surface-container-highest);
            background-image: radial-gradient(circle, var(--md-toggle-thumb) 0 0.45rem, transparent 0.5rem);
            background-position: 0.5rem 50%;
            background-repeat: no-repeat;
            background-size: 1.5rem 1.5rem;
            cursor: pointer;
            transition:
                --md-toggle-thumb var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-standard),
                background-color var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-emphasized),
                background-position var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-emphasized),
                border-color var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-emphasized);
        }

        .legacy-admin .toggle:checked {
            --md-toggle-thumb: #ffffff;
            border-color: var(--md-sys-color-primary);
            background-color: var(--md-sys-color-primary);
            background-position: calc(100% - 0.5rem) 50%;
        }


        .legacy-admin .toggle-sm {
            width: 2.5rem;
            height: 1.5rem;
        }

        /* --- Dialog daisyUI --- */
        .legacy-admin .modal-backdrop,
        .legacy-admin .modal::backdrop {
            background: var(--md-sys-color-scrim);
            backdrop-filter: blur(2px);
        }

        .legacy-admin .modal-box {
            border-radius: var(--md-shape-dialog);
            background: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface);
            box-shadow: var(--md-elevation-3);
            scrollbar-width: thin;
        }

        /* --- Menu, dropdown, link, kartu, progress, tooltip --- */
        .legacy-admin :is(.menu, .dropdown-content) :where(li > a, li > button, a, button) {
            border-radius: var(--md-shape-pill);
            font-weight: 500;
            color: var(--md-sys-color-on-surface-variant);
        }

        .legacy-admin :is(.menu, .dropdown-content) :where(li > a:hover, li > button:hover) {
            background: color-mix(in srgb, var(--md-sys-color-primary) 10%, transparent);
            color: var(--md-sys-color-primary);
        }

        .legacy-admin :is(.menu, .dropdown-content) {
            border: 1px solid var(--md-sys-color-outline-variant);
            border-radius: var(--md-shape-card);
            background: var(--md-sys-color-surface);
            box-shadow: var(--md-elevation-2);
        }

        .legacy-admin .link {
            color: var(--md-sys-color-primary);
            text-underline-offset: .2em;
        }

        .legacy-admin :is(.card, .card-side) {
            border-radius: var(--md-shape-card);
            background: var(--md-sys-color-surface);
            box-shadow: var(--md-elevation-1);
        }

        .legacy-admin .progress {
            border-radius: var(--md-shape-pill);
            background-color: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-primary);
        }

        .legacy-admin .progress::-webkit-progress-value {
            background-color: var(--md-sys-color-primary);
        }

        .legacy-admin .progress::-moz-progress-bar {
            background-color: var(--md-sys-color-primary);
        }

        .legacy-admin .tooltip::before,
        .legacy-admin .tooltip::after {
            background-color: var(--md-sys-color-inverse-surface);
            color: var(--md-sys-color-inverse-on-surface);
        }

        /* Zebra table daisyUI memakai warna cream tema; diganti tint permukaan M3. */
        .legacy-admin .table-zebra :where(tbody tr:nth-child(even) td) {
            background-color: color-mix(in srgb, var(--md-sys-color-primary) 4%, transparent);
        }

        .legacy-admin .table-zebra :where(tbody tr:hover td) {
            background-color: color-mix(in srgb, var(--md-sys-color-primary) 8%, transparent);
        }

        /* Material Web: jangan tampilkan komponen sebelum CDN menaikkannya. */
        md-dialog:not(:defined) {
            display: none !important;
        }

        /* ===================== 9. Transisi masuk halaman & dialog =====================
           Fade-through M3: konten masuk sambil naik 8px, bertahap 40ms, tetapi
           langkah-nya dibatasi sampai anak keempat — halaman yang punya 12 blok
           tidak boleh membuat pembaca menunggu lebih dari ~450ms. Semua properti
           yang dianimasikan transform/opacity, jadi tidak menggeser layout (CLS 0). */

        @keyframes m3-fade-through {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            /* `none`, bukan translateY(0): transform non-none apa pun membuat
               elemen jadi containing block untuk keturunan position:fixed. */
            to {
                opacity: 1;
                transform: none;
            }
        }

        .legacy-admin>* {
            animation: m3-fade-through var(--md-sys-motion-duration-medium2) var(--md-sys-motion-easing-emphasized-decelerate) both;
        }

        .legacy-admin>*:nth-child(2) {
            animation-delay: 40ms;
        }

        .legacy-admin>*:nth-child(3) {
            animation-delay: 80ms;
        }

        .legacy-admin>*:nth-child(4) {
            animation-delay: 120ms;
        }

        .legacy-admin>*:nth-child(n+5) {
            animation-delay: 140ms;
        }

        /* Scrim bertoken — dipakai dialog M3, backdrop daisyUI, dan panel apa pun
           yang perlu meredupkan halaman di belakangnya. */
        .m3-scrim {
            background: var(--md-sys-color-scrim);
            backdrop-filter: blur(2px);
        }

        /* Dialog M3: masuk 200 ms emphasized-decelerate, keluar 150 ms
           emphasized-accelerate. Kelas enter/leave membawa kurva + durasinya,
           kelas -start/-end membawa keadaannya. */
        .m3-dialog-enter {
            transition-property: opacity, transform;
            transition-duration: var(--md-sys-motion-duration-short4);
            transition-timing-function: var(--md-sys-motion-easing-emphasized-decelerate);
        }

        .m3-dialog-enter-start {
            opacity: 0;
            transform: scale(.96) translateY(12px);
        }

        .m3-dialog-enter-end {
            opacity: 1;
            transform: none;
        }

        .m3-dialog-leave {
            transition-property: opacity, transform;
            transition-duration: var(--md-sys-motion-duration-short3);
            transition-timing-function: var(--md-sys-motion-easing-emphasized-accelerate);
        }

        .m3-dialog-leave-start {
            opacity: 1;
            transform: none;
        }

        .m3-dialog-leave-end {
            opacity: 0;
            transform: scale(.96) translateY(12px);
        }

        /* Fade polos untuk scrim: 200 ms masuk, 150 ms keluar. */
        .m3-scrim-fade-enter {
            transition: opacity var(--md-sys-motion-duration-short4) var(--md-sys-motion-easing-emphasized-decelerate);
        }

        .m3-scrim-fade-leave {
            transition: opacity var(--md-sys-motion-duration-short3) var(--md-sys-motion-easing-emphasized-accelerate);
        }

        /* ===================== 10. Reduced motion ===================== */

        @media (prefers-reduced-motion: reduce) {

            .m3-skeleton {
                animation: none;
            }

            .legacy-admin .btn,
            .m3-btn,
            .m3-icon-btn,
            .m3-skip-link,
            .m3-dialog-enter,
            .m3-dialog-leave,
            .m3-scrim-fade-enter,
            .m3-scrim-fade-leave {
                transition: none;
            }

            .legacy-admin .btn:active,
            .m3-btn:active,
            .m3-icon-btn:active {
                transform: none;
            }

            /* Masuk halaman dimatikan sepenuhnya: konten langsung terlihat. */
            .legacy-admin>* {
                animation: none;
            }

            /* Dorongan 8px tetap dihapus walau transisi warna dimatikan. */
            .m3-dialog-enter-start,
            .m3-dialog-leave-end {
                transform: none;
            }
        }
    </style>

    @stack('styles')

</head>
