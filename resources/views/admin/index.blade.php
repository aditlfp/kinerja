<x-admin-layout>
    @section('title', 'Halaman Dashboard')

    {{-- Material Design 3 theme: fonts + design tokens for the Material Web components below. --}}
    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
            rel="stylesheet">
        <style>
            /* Token warna, elevation, shape, dan motion kini hidup di SATU tempat:
                   :root pada components/admin-layout/head.blade.php. Blok token lokal
                   dashboard sengaja dihapus supaya palet turunan seed ikut berlaku di
                   halaman ini (kalau tidak, nilai lama di sini akan menang). */

            /* ---- surfaces, color roles & elevation ---- */
            .md-dashboard .m3-surface {
                background: var(--md-sys-color-surface-container-low);
            }

            .md-dashboard .m3-hero {
                background-image: var(--grad-hero);
            }

            .md-dashboard .m3-elev-1 {
                box-shadow: var(--md-elevation-1);
            }

            .md-dashboard .m3-card-hover {
                transition: box-shadow 150ms ease;
            }

            .md-dashboard .m3-card-hover:hover {
                box-shadow: var(--md-elevation-2);
            }

            .md-dashboard .m3-text {
                color: var(--md-sys-color-on-surface);
            }

            .md-dashboard .m3-text-variant {
                color: var(--md-sys-color-on-surface-variant);
            }

            .md-dashboard .m3-text-error {
                color: var(--md-sys-color-error);
            }

            .md-dashboard .m3-on-primary-fixed {
                color: var(--md-sys-color-on-primary-fixed);
            }

            .md-dashboard .m3-on-primary-fixed-variant {
                color: var(--md-sys-color-on-primary-fixed-variant);
            }

            .md-dashboard .m3-outline {
                border: 1px solid var(--md-sys-color-outline-variant);
            }

            .md-dashboard .m3-outline>div:not(:first-child) {
                border-top: 1px solid var(--md-sys-color-outline-variant);
            }

            .md-dashboard .m3-select-all {
                background: var(--md-sys-color-surface-container);
            }

            .md-dashboard .m3-tone-primary {
                background: var(--md-sys-color-primary-container);
                color: var(--md-sys-color-on-primary-container);
            }

            .md-dashboard .m3-tone-secondary {
                background: var(--md-sys-color-secondary-container);
                color: var(--md-sys-color-on-secondary-container);
            }

            .md-dashboard .m3-tone-tertiary {
                background: var(--md-sys-color-tertiary-container);
                color: var(--md-sys-color-on-tertiary-container);
            }

            .md-dashboard .m3-tone-error {
                background: var(--md-sys-color-error-container);
                color: var(--md-sys-color-on-error-container);
            }

            .md-dashboard .m3-tone-warning {
                background: var(--app-warning-container);
                color: var(--app-on-warning-container);
            }

            /* ---- link styled as a Material 3 filled / tonal button ----
                   Navigation stays a real <a> (middle-click, keyboard, status bar);
                   md-ripple supplies the Material state layer. */
            .md-dashboard .m3-link-filled {
                background: var(--md-sys-color-primary);
                color: var(--md-sys-color-on-primary);
            }

            .md-dashboard .m3-link-tonal {
                background: var(--app-warning-container);
                color: var(--app-on-warning-container);
            }

            /* ---- Material Web component token overrides ---- */
            .md-dashboard .m3-btn-danger {
                --md-filled-tonal-icon-button-container-height: 32px;
                --md-filled-tonal-icon-button-container-width: 32px;
                --md-filled-tonal-icon-button-container-color: var(--md-sys-color-error-container);
                --md-filled-tonal-icon-button-icon-color: var(--md-sys-color-on-error-container);
                --md-filled-tonal-icon-button-icon-size: 18px;
            }

            .md-dashboard .m3-btn-danger-filled {
                --md-filled-button-container-color: var(--md-sys-color-error);
                --md-filled-button-label-text-color: var(--md-sys-color-on-error);
                --md-filled-button-container-height: 36px;
                --md-filled-button-label-text-size: 13px;
            }

            .md-dashboard md-outlined-text-field {
                width: 100%;
                --md-outlined-text-field-container-shape: 28px;
                --md-outlined-text-field-top-space: 12px;
                --md-outlined-text-field-bottom-space: 12px;
            }

            /* ---- Material list tweaks ---- */
            .md-dashboard md-list {
                background: transparent;
            }

            .md-dashboard md-list-item [slot="headline"] {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

        </style>
    @endpush

    @php
        $expiringContracts = $expiringContractsCount ?? $expert->count();
        $inactiveUsers = $inactiveUsersCount ?? $notActiveUsers->count();

        $statCards = [
            [
                'label' => 'Pending Izin',
                'value' => $izin ?? 0,
                'hint' => 'Perlu approval admin',
                'icon' => 'notifications',
                'tone' => 'm3-tone-primary',
                'href' => route('admin.izin.index'),
            ],
            [
                'label' => 'Kontrak Berakhir',
                'value' => $expiringContracts,
                'hint' => 'Butuh tindak lanjut',
                'icon' => 'schedule',
                'tone' => 'm3-tone-tertiary',
                'href' => route('admin.kerjasama.index'),
            ],
            [
                'label' => 'User Tidak Aktif',
                'value' => $inactiveUsers,
                'hint' => 'Tidak aktif 1 bulan+',
                'icon' => 'person',
                'tone' => 'm3-tone-secondary',
                'href' => route('admin.user.index'),
            ],
        ];
    @endphp

    {{-- Material Web (Material Design 3) components from CDN --}}
    <script type="module" src="https://esm.run/@material/web@2.5.0/all.js"></script>
    <script type="module">
        // Optional: Material 3 typescale styles. Isolated so a CDN hiccup can't break the components above.
        try {
            const {
                styles
            } = await import('https://esm.run/@material/web@2.5.0/typography/md-typescale-styles.js');
            if (styles && styles.styleSheet) {
                document.adoptedStyleSheets = [...(document.adoptedStyleSheets || []), styles.styleSheet];
            }
        } catch (error) {
            console.warn('Material Web typescale tidak tersedia:', error);
        }
    </script>

    <div class="md-dashboard pb-10 space-y-4" x-data="{
        // --- raw data from server ---
        allUsers: @js(
    $notActiveUsers
        ->map(
            fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'nama_lengkap' => $u->nama_lengkap ?? '',
                'last_attendance' => $u->last_attendance,
            ],
        )
        ->values(),
),
    
        // --- search & pagination ---
        search: '',
        perPage: 10,
        page: 1,
    
        get filtered() {
            const q = this.search.toLowerCase().trim();
            if (!q) return this.allUsers;
            return this.allUsers.filter(u =>
                u.name.toLowerCase().includes(q) ||
                u.nama_lengkap.toLowerCase().includes(q)
            );
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
        get paginated() {
            const start = (this.page - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },
        prevPage() { if (this.page > 1) this.page--; },
        nextPage() { if (this.page < this.totalPages) this.page++; },
        resetPage() { this.page = 1; },
    
        // --- checkbox ---
        selected: [],
        get allChecked() { return this.paginated.length > 0 && this.paginated.every(u => this.selected.includes(u.id)); },
        toggleAll() {
            const ids = this.paginated.map(u => u.id);
            if (this.allChecked) this.selected = this.selected.filter(id => !ids.includes(id));
            else this.selected = [...new Set([...this.selected, ...ids])];
        },
        toggle(id) {
            if (this.selected.includes(id)) this.selected = this.selected.filter(i => i !== id);
            else this.selected.push(id);
        },
    
        // --- routes ---
        editRoute: '{{ route('admin.user.edit', '__ID__') }}',
        checkRoute: '{{ route('admin.user.check-relations', '__ID__') }}',
        deleteRoute: '{{ route('admin.user.hard-delete', '__ID__') }}',
    
        // --- Material dialogs ---
        showDialog(ref) {
            const dialog = this.$refs[ref];
            // showModal() menaruh dialog di TOP LAYER, jadi dialog dan
            // ::backdrop-nya berada di atas seluruh chrome (sidebar z-10, topbar
            // z-40) apa pun z-index-nya. md-dialog tidak bisa memberi itu: scrim
            // internalnya hidup di shadow DOM dengan z-index 1, sehingga selalu
            // kalah dari chrome dan backdrop-nya tidak menutupi sidebar.
            if (dialog && typeof dialog.showModal === 'function') dialog.showModal();
        },
    
        // --- single hard delete modal ---
        loading: false,
        deleting: false,
        userId: null,
        userName: '',
        relations: null,
        openModal(id, name) {
            this.userId = id;
            this.userName = name;
            this.relations = null;
            this.loading = true;
            this.deleting = false;
            this.showDialog('deleteDialog');
            fetch(this.checkRoute.replace('__ID__', id), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => { this.relations = data;
                    this.loading = false; })
                .catch(() => { this.loading = false; });
        },
    
        // --- bulk delete modal ---
        bulkDeleting: false,
        bulkDeleteRoute: '{{ route('admin.user.bulk-hard-delete') }}',
    
        diffForHumans(dateStr) {
            if (!dateStr) return 'Belum pernah absen';
            const diff = Date.now() - new Date(dateStr).getTime();
            const days = Math.floor(diff / 86400000);
            if (days < 30) return days + ' hari yang lalu';
            const months = Math.floor(days / 30);
            if (months < 12) return months + ' bulan yang lalu';
            return Math.floor(months / 12) + ' tahun yang lalu';
        }
    }">

        {{-- ==================== Hero / Admin Overview ==================== --}}
        <section class="m3-hero relative overflow-hidden rounded-[28px] px-6 py-7">
            {{-- Pita diagonal tipis ala M3: satu-satunya elemen dekoratif di halaman ini. --}}
            <span class="m3-hero-band" aria-hidden="true"></span>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="m3-on-primary-fixed-variant text-[11px] font-medium uppercase tracking-[0.16em]">
                        Admin Overview
                    </p>
                    <h1 class="m3-on-primary-fixed mt-1.5 text-[32px] font-normal leading-tight">
                        Dashboard Admin
                    </h1>
                    <p class="m3-on-primary-fixed-variant mt-1 text-sm">
                        Ringkasan status operasional hari ini.
                    </p>
                </div>

                <a href="{{ route('admin.izin.index') }}"
                    class="m3-link-filled relative inline-flex h-10 shrink-0 items-center gap-2 self-start overflow-hidden rounded-full px-6 text-sm font-medium transition lg:self-auto">
                    <md-ripple></md-ripple>
                    <md-icon>refresh</md-icon>
                    Review Izin
                </a>
            </div>
        </section>

        {{-- ==================== Stat tiles ==================== --}}
        <section class="grid gap-4 md:grid-cols-3">
            @foreach ($statCards as $stat)
                <a href="{{ $stat['href'] }}"
                    class="m3-surface m3-elev-1 m3-card-hover relative flex items-center gap-4 overflow-hidden rounded-2xl p-5">
                    <md-ripple></md-ripple>
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $stat['tone'] }}">
                        <md-icon>{{ $stat['icon'] }}</md-icon>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="m3-text-variant text-sm font-medium">{{ $stat['label'] }}</p>
                        <p class="m3-text mt-1 text-[28px] leading-none">{{ $stat['value'] }}</p>
                        <p class="m3-text-variant mt-1.5 text-xs">{{ $stat['hint'] }}</p>
                    </div>
                    <md-icon class="m3-text-variant">chevron_right</md-icon>
                </a>
            @endforeach
        </section>

        {{-- ==================== Detail cards ==================== --}}
        <section class="grid gap-4 lg:grid-cols-2">
            {{-- ---------- Kontrak akan berakhir ---------- --}}
            <article class="m3-surface m3-elev-1 flex flex-col overflow-hidden rounded-2xl">
                <header class="flex items-center justify-between gap-3 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="m3-tone-primary flex h-10 w-10 items-center justify-center rounded-xl">
                            <md-icon>description</md-icon>
                        </span>
                        <div>
                            <h2 class="m3-text text-sm font-medium">Kontrak</h2>
                            <p class="m3-text-variant text-xs">Daftar kontrak prioritas</p>
                        </div>
                    </div>
                    <span
                        class="m3-tone-error rounded-full px-2.5 py-1 text-xs font-medium">{{ $expiringContracts }}</span>
                </header>

                @if ($expiringContracts > 0)
                    <md-list>
                        @foreach ($expert as $ex)
                            @if (!$loop->first)
                                <md-divider></md-divider>
                            @endif
                            <md-list-item>
                                <div slot="headline">{{ $ex->client->name ?? 'Client tidak ditemukan' }}</div>
                                <div slot="supporting-text">
                                    Berakhir:
                                    {{ Carbon\Carbon::createFromFormat('Y-m-d', $ex->experied)->isoFormat('DD MMMM YYYY') }}
                                </div>
                                <div slot="end" class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.kerjasama.edit', $ex->id) }}"
                                        class="m3-link-tonal relative inline-flex h-8 items-center overflow-hidden rounded-full px-4 text-xs font-medium">
                                        <md-ripple></md-ripple>
                                        Update
                                    </a>
                                    <a href="{{ route('admin.kerjasama.index') }}" title="Lihat semua kontrak"
                                        class="m3-text-variant relative inline-flex h-8 w-8 items-center justify-center overflow-hidden rounded-full">
                                        <md-ripple></md-ripple>
                                        <md-icon>more_vert</md-icon>
                                    </a>
                                </div>
                            </md-list-item>
                        @endforeach
                    </md-list>
                @else
                    <div class="flex flex-col items-center gap-3 px-5 py-10 text-center">
                        <span class="m3-tone-secondary flex h-11 w-11 items-center justify-center rounded-full">
                            <md-icon>inbox</md-icon>
                        </span>
                        <p class="m3-text-variant text-sm">Tidak ada kontrak yang mendekati masa berakhir.</p>
                    </div>
                @endif
            </article>

            {{-- ---------- User tidak aktif ---------- --}}
            <article class="m3-surface m3-elev-1 flex flex-col overflow-hidden rounded-2xl">
                <header class="flex items-center justify-between gap-3 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="m3-tone-secondary flex h-10 w-10 items-center justify-center rounded-xl">
                            <md-icon>person_off</md-icon>
                        </span>
                        <div>
                            <h2 class="m3-text text-sm font-medium">User Tidak Aktif</h2>
                            <p class="m3-text-variant text-xs">
                                <span x-text="filtered.length"></span> / {{ $inactiveUsers }} user
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <span x-show="selected.length > 0" x-cloak>
                            <md-filled-button class="m3-btn-danger-filled"
                                @click="bulkDeleting = false; showDialog('bulkDialog')">
                                <md-icon slot="icon">delete</md-icon>
                                Hapus (<span x-text="selected.length"></span>)
                            </md-filled-button>
                        </span>
                        <span
                            class="m3-tone-secondary rounded-full px-2.5 py-1 text-xs font-medium">{{ $inactiveUsers }}</span>
                    </div>
                </header>

                {{-- Search --}}
                <div class="px-5 pb-3">
                    <md-outlined-text-field x-model="search" @input="resetPage()" label="Cari nama atau username">
                        <md-icon slot="leading-icon">search</md-icon>
                    </md-outlined-text-field>
                </div>

                {{-- Select all --}}
                <div class="m3-select-all flex items-center gap-3 px-5 py-1.5">
                    <md-checkbox :checked="allChecked" @change="toggleAll()" touch-target="wrapper"></md-checkbox>
                    <span class="m3-text-variant text-xs">Pilih semua di halaman ini</span>
                </div>

                {{-- List --}}
                <md-list>
                    <template x-for="user in paginated" :key="user.id">
                        <md-list-item>
                            <md-checkbox slot="start" :checked="selected.includes(user.id)" @change="toggle(user.id)"
                                touch-target="wrapper"></md-checkbox>
                            <div slot="headline"
                                x-text="user.name + (user.nama_lengkap ? ' | ' + user.nama_lengkap : '')"></div>
                            <div slot="supporting-text"
                                x-text="'Terakhir aktif: ' + diffForHumans(user.last_attendance)"></div>
                            <div slot="end" class="flex items-center gap-1.5">
                                <a :href="editRoute.replace('__ID__', user.id)"
                                    class="m3-link-tonal relative inline-flex h-8 items-center overflow-hidden rounded-full px-4 text-xs font-medium">
                                    <md-ripple></md-ripple>
                                    Update
                                </a>
                                <md-filled-tonal-icon-button class="m3-btn-danger btnDelete"
                                    @click="openModal(user.id, user.name)">
                                    <md-icon>delete</md-icon>
                                </md-filled-tonal-icon-button>
                            </div>
                        </md-list-item>
                    </template>
                </md-list>

                <div x-show="paginated.length === 0" class="flex flex-col items-center gap-3 px-5 py-10 text-center">
                    <span class="m3-tone-secondary flex h-11 w-11 items-center justify-center rounded-full">
                        <md-icon>search_off</md-icon>
                    </span>
                    <p class="m3-text-variant text-sm"
                        x-text="search ? 'Tidak ada hasil untuk &quot;' + search + '&quot;' : 'Tidak ada user tidak aktif.'">
                    </p>
                </div>

                {{-- Pagination --}}
                <div x-show="totalPages > 1" class="mt-auto flex items-center justify-between px-5 py-3">
                    <p class="m3-text-variant text-xs">
                        Halaman <span x-text="page"></span> / <span x-text="totalPages"></span>
                        &nbsp;·&nbsp;<span x-text="filtered.length"></span> user
                    </p>
                    <div class="flex items-center gap-1">
                        <md-outlined-icon-button @click="prevPage()" :disabled="page === 1">
                            <md-icon>chevron_left</md-icon>
                        </md-outlined-icon-button>
                        <md-outlined-icon-button @click="nextPage()" :disabled="page === totalPages">
                            <md-icon>chevron_right</md-icon>
                        </md-outlined-icon-button>
                    </div>
                </div>
            </article>
        </section>

        {{-- ==================== Dialog: single hard delete ==================== --}}
        <dialog class="m3-dialog" x-ref="deleteDialog" @close="loading = false; deleting = false">
            <div class="m3-dialog__headline">Hapus Selamanya Karyawan Ini ?</div>
            <div class="m3-dialog__content space-y-3">
                <p class="m3-text-variant text-sm">
                    Hapus permanen: <strong x-text="userName"></strong>
                </p>

                <div x-show="loading" class="m3-text-variant text-sm">
                    <i class="ri-loader-4-line mr-1 animate-spin"></i> Mengecek relasi data...
                </div>

                <div x-show="!loading && relations !== null" class="space-y-3">
                    <p class="m3-text-variant text-xs font-medium uppercase tracking-wide">Data ditemukan</p>
                    <div class="m3-outline overflow-hidden rounded-2xl">
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="m3-text-variant">Data Absensi</span>
                            <span class="font-medium" :class="relations?.absensi > 0 ? 'm3-text-error' : ''"
                                x-text="(relations?.absensi ?? 0) + ' record'"></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="m3-text-variant">Data Karyawan (ID)</span>
                            <span class="font-medium" :class="relations?.employes_user_id > 0 ? 'm3-text-error' : ''"
                                x-text="(relations?.employes_user_id ?? 0) + ' record'"></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="m3-text-variant">Data Karyawan (Nama)</span>
                            <span class="font-medium" :class="relations?.employes_by_name > 0 ? 'm3-text-error' : ''"
                                x-text="(relations?.employes_by_name ?? 0) + ' record'"></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="m3-text-variant">Slip Gaji</span>
                            <span class="font-medium" :class="relations?.slip_gaji > 0 ? 'm3-text-error' : ''"
                                x-text="(relations?.slip_gaji ?? 0) + ' record'"></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="m3-text-variant">Data Rekap</span>
                            <span class="font-medium" :class="relations?.rekap_total > 0 ? 'm3-text-error' : ''"
                                x-text="(relations?.rekap_total ?? 0) + ' record'"></span>
                        </div>
                    </div>
                    <p class="m3-tone-warning rounded-2xl px-3 py-2 text-xs">
                        Semua data di atas akan <strong>dihapus permanen</strong> dan tidak bisa dikembalikan.
                    </p>
                </div>
            </div>
            <form class="m3-dialog__actions" :action="deleteRoute.replace('__ID__', userId)" method="POST"
                @submit="deleting = true">
                @csrf
                @method('DELETE')
                <md-text-button type="button" @click="$refs.deleteDialog.close()">Batal</md-text-button>
                <md-filled-button class="m3-btn-danger-filled" type="submit" :disabled="loading || deleting">
                    <md-icon slot="icon">delete</md-icon>
                    <span x-text="deleting ? 'Menghapus...' : 'Hapus Permanen'"></span>
                </md-filled-button>
            </form>
        </dialog>

        {{-- ==================== Dialog: bulk hard delete ==================== --}}
        <dialog class="m3-dialog" x-ref="bulkDialog" @close="bulkDeleting = false">
            <div class="m3-dialog__headline">Bulk Hard Delete</div>
            <div class="m3-dialog__content space-y-3">
                <p class="m3-text-variant text-sm">
                    Hapus permanen <strong x-text="selected.length"></strong> user yang dipilih.
                </p>

                <div class="m3-tone-error max-h-40 space-y-1 overflow-y-auto rounded-2xl px-4 py-3 text-sm">
                    <template x-for="id in selected" :key="id">
                        <div
                            x-text="allUsers.find(u => u.id === id)?.name + ' — ' + (allUsers.find(u => u.id === id)?.nama_lengkap || '-')">
                        </div>
                    </template>
                </div>

                <p class="m3-tone-warning rounded-2xl px-3 py-2 text-xs">
                    Semua data terkait tiap user akan <strong>dihapus permanen</strong> dan tidak bisa dikembalikan.
                </p>
            </div>
            <div class="m3-dialog__actions">
                <md-text-button @click="$refs.bulkDialog.close()">Batal</md-text-button>
                <md-filled-button class="m3-btn-danger-filled" :disabled="bulkDeleting"
                    @click="
                        bulkDeleting = true;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = bulkDeleteRoute;
                        form.innerHTML = '<input name=_token value=\'{{ csrf_token() }}\'>' + selected.map(id => '<input name=ids[] value=\'' + id + '\'>').join('');
                        document.body.appendChild(form);
                        form.submit();
                    ">
                    <md-icon slot="icon">delete</md-icon>
                    <span x-text="bulkDeleting ? 'Menghapus...' : 'Hapus Semua'"></span>
                </md-filled-button>
            </div>
        </dialog>
    </div>


</x-admin-layout>
