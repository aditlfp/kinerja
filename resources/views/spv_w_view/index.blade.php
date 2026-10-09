<x-app-layout>
    @push('scripts')
        <script src={{ URL::asset('js/rekap-export.js') }}></script>
    @endpush

    @php
        $spvwClientId = request('client_id', session('spvw.selected_client_id'));
    @endphp

    @php
        $selectedClientId = (int) ($selectedClientId ?? 0);
        $selectedClientName =
            $selectedClientId > 0 ? optional(collect($clients ?? [])->firstWhere('id', $selectedClientId))->name : null;
        $appendClient = function (string $url) use ($selectedClientId) {
            if ($selectedClientId <= 0) {
                return $url;
            }

            // Remove existing client_id parameter if present to avoid duplicates
            $clean = preg_replace('/([?&])client_id=\d+(&?)/', '$1', $url);
            $clean = rtrim($clean, '?&');

            return $clean . (str_contains($clean, '?') ? '&' : '?') . 'client_id=' . $selectedClientId;
        };
        $rekapMenus = [
            [
                'key' => 'lembur',
                'title' => 'Lembur',
                'description' => 'Catat lembur personil dan pantau status pengajuannya.',
                'icon' => 'ri-time-line',
                'icon_bg' => 'bg-sky-100 ring-sky-200',
                'icon_color' => 'text-sky-700',
                'pengajuan_url' => $appendClient(
                    route('spvw.overtime-application.create', array_filter(['client_id' => $spvwClientId])),
                ),
                'riwayat_url' => $appendClient(
                    route('spvw.overtime-application.history', array_filter(['client_id' => $spvwClientId])),
                ),
            ],
            [
                'key' => 'personil-keluar',
                'title' => 'Personil Keluar',
                'description' => 'Ajukan personil keluar dari site atau area kerja.',
                'icon' => 'ri-user-unfollow-line',
                'icon_bg' => 'bg-amber-100 ring-amber-200',
                'icon_color' => 'text-amber-700',
                'pengajuan_url' => $appendClient(
                    route('spvw.person-is-out.create', array_filter(['client_id' => $spvwClientId])),
                ),
                'riwayat_url' => $appendClient(
                    route('spvw.person-is-out.history', array_filter(['client_id' => $spvwClientId])),
                ),
            ],
            [
                'key' => 'personil-masuk',
                'title' => 'Personil Masuk',
                'description' => 'Input personil baru atau perpindahan masuk.',
                'icon' => 'ri-user-follow-line',
                'icon_bg' => 'bg-emerald-100 ring-emerald-200',
                'icon_color' => 'text-emerald-700',
                'pengajuan_url' => $appendClient(
                    route('spvw.person-in.index', array_filter(['client_id' => $spvwClientId])),
                ),
                'riwayat_url' => $appendClient(
                    route('spvw.person.in.history', array_filter(['client_id' => $spvwClientId])),
                ),
            ],
            [
                'key' => 'potongan',
                'title' => 'Cutting',
                'description' => 'Kelola potongan performa dengan riwayat yang mudah dicek.',
                'icon' => 'ri-auction-fill',
                'icon_bg' => 'bg-red-100 ring-red-200',
                'icon_color' => 'text-red-700',
                'pengajuan_url' => $appendClient(
                    route('spvw.cutting.index', array_filter(['client_id' => $spvwClientId])),
                ),
                'riwayat_url' => $appendClient(
                    route('spvw.cutting.history', array_filter(['client_id' => $spvwClientId])),
                ),
            ],
            [
                'key' => 'lepas-training',
                'title' => 'Lepas Training',
                'description' => 'Rekap personil yang selesai masa training.',
                'icon' => 'ri-footprint-fill',
                'icon_bg' => 'bg-indigo-100 ring-indigo-200',
                'icon_color' => 'text-indigo-700',
                'pengajuan_url' => $appendClient(
                    route('spvw.finished-training.index', array_filter(['client_id' => $spvwClientId])),
                ),
                'riwayat_url' => $appendClient(
                    route('spvw.finished-training.history', array_filter(['client_id' => $spvwClientId])),
                ),
            ],
            [
                'key' => 'keterangan-lanjutan',
                'title' => 'Keterangan Lanjutan',
                'description' => 'Catat periode, judul, dan isi keterangan lanjutan.',
                'icon' => 'ri-file-text-line',
                'icon_bg' => 'bg-violet-100 ring-violet-200',
                'icon_color' => 'text-violet-700',
                'pengajuan_url' => $appendClient(
                    route('spvw.keterangan-lanjutan.index', array_filter(['client_id' => $spvwClientId])),
                ),
                'riwayat_url' => $appendClient(
                    route('spvw.keterangan-lanjutan.history', array_filter(['client_id' => $spvwClientId])),
                ),
            ],
        ];

        $dueDateLabel =
            $dueDate ?? null
                ? 'Setiap tgl ' .
                    (int) \Carbon\Carbon::parse($dueDate->due_date)->day .
                    ' pukul ' .
                    \Carbon\Carbon::parse($dueDate->due_date)->format('H:i')
                : null;
        $selectedMode = in_array($selectedMode ?? 'pengajuan', ['pengajuan', 'riwayat'], true)
            ? $selectedMode
            : 'pengajuan';
        $codeJabatan = strtoupper(
            auth()->user()->jabatan->code_jabatan ?? (auth()->user()->divisi?->jabatan?->code_jabatan ?? ''),
        );
    @endphp

    @push('styles')
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush

    <x-main-div>
        <div class="w-full px-3 py-4 mx-auto max-w-7xl sm:px-5 lg:px-6">
            <div
                class="mb-4 overflow-hidden bg-white border rounded-lg shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="max-w-2xl">
                                <div
                                    class="mb-2 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                    <i class="text-sm ri-stack-line text-sky-700"></i>
                                    Data Rekap
                                </div>
                                <h1 class="text-xl font-bold leading-tight text-slate-900 sm:text-2xl">
                                    Rekapitulasi
                                </h1>
                                <p class="mt-1 text-sm leading-5 text-slate-500">
                                    Buat pengajuan dan cek riwayat rekap dalam satu halaman.
                                </p>
                            </div>

                            <div class="hidden grid-cols-2 gap-2 sm:grid sm:w-52">
                                <div class="px-3 py-2 border rounded-lg border-slate-200 bg-slate-50">
                                    <p class="text-xs font-medium text-slate-500">Menu</p>
                                    <p class="text-xl font-semibold text-slate-900">{{ count($rekapMenus) }}</p>
                                </div>
                                <div class="px-3 py-2 border rounded-lg border-slate-200 bg-slate-50">
                                    <p class="text-xs font-medium text-slate-500">Akses</p>
                                    <p class="text-xl font-semibold text-slate-900">{{ $codeJabatan }}</p>
                                </div>
                            </div>
                        </div>

                        <form method="GET"
                            action="{{ route('spvw.rekap.index', array_filter(['client_id' => $spvwClientId])) }}"
                            id="spvw-client-filter-form"
                            class="mt-4 rounded-xl border border-slate-200 bg-white/90 p-2.5 shadow-xs ring-1 ring-slate-900/5">
                            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                                <div
                                    class="inline-flex items-center gap-2 px-3 text-xs font-semibold tracking-wide uppercase border rounded-lg min-h-11 border-slate-200 bg-slate-50 text-slate-600 sm:w-auto">
                                    <i class="text-sm ri-filter-3-line text-slate-500"></i>
                                    Mitra
                                </div>

                                <div class="flex-1 min-w-0">
                                    <label for="client_id" class="sr-only">Pilih mitra</label>
                                    <select name="client_id" id="client_id"
                                        onchange="window.handleSpvwClientFilterChange && window.handleSpvwClientFilterChange()"
                                        class="w-full text-sm font-medium bg-white rounded-lg min-h-11 border-slate-300 text-slate-700 focus:border-2 focus:border-sky-500 focus:ring-sky-500">
                                        <option value="">Semua Mitra</option>
                                        @foreach ($clients ?? collect() as $client)
                                            <option value="{{ $client->id }}" @selected($selectedClientId === (int) $client->id)>
                                                {{ capitalizeWords($client->name) ?: 'Mitra #' . $client->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                                    <button type="submit"
                                        class="hidden min-h-11 items-center justify-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                        <i class="text-base ri-search-line"></i>
                                        <span>Pakai</span>
                                    </button>
                                    <a href="{{ route('spvw.rekap.index', ['reset_filter' => 1]) }}"
                                        id="spvw-reset-filter"
                                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <i class="text-base ri-refresh-line"></i>
                                        <span>Reset Filter</span>
                                    </a>
                                </div>
                            </div>

                            <div
                                class="mt-2 flex min-h-7 items-center justify-between rounded-lg bg-slate-50 px-2.5 py-1.5">
                                <p id="spvw-filter-hint" class="text-[11px] text-slate-500">
                                    {{ $selectedClientId > 0 ? 'Filter mitra tetap aktif sampai logout.' : 'Mode cepat: filter langsung aktif saat mitra dipilih.' }}
                                </p>
                                <div id="spvw-filter-active"
                                    class="flex items-center gap-1.5 {{ $selectedClientId > 0 ? '' : 'hidden' }}">
                                    <span id="spvw-filter-name"
                                        class="hidden sm:inline-flex items-center gap-1 rounded-md bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600 ring-1 ring-slate-200">
                                        <i class="ri-building-line"></i>
                                        {{ capitalizeWords($selectedClientName ?: 'Mitra #' . $selectedClientId) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-md bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-700">
                                        <i class="ri-check-line"></i>
                                        Aktif
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-4 border-t border-slate-200 bg-slate-50/80 lg:border-l lg:border-t-0 sm:p-5">
                        <div class="flex flex-col justify-center h-full gap-2">
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center justify-center bg-white rounded-lg shadow-xs h-9 w-9 shrink-0 text-slate-700 ring-1 ring-slate-200">
                                    <i class="text-lg ri-calendar-check-line"></i>
                                </span>
                                <div>
                                    <p class="text-xs font-medium text-slate-500">Batas rekap
                                    </p>
                                    <p class="text-base font-semibold text-slate-900">
                                        {{ $dueDateLabel ?? 'Belum diatur' }}
                                    </p>
                                </div>
                            </div>

                            @if (($isAfterDueDate ?? false) === true)
                                <div
                                    class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                    <i class="ri-lock-line"></i>
                                    Rekap dikunci
                                </div>
                            @elseif ($dueDateLabel)
                                <div
                                    class="inline-flex w-fit items-center gap-2 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800">
                                    <i class="ri-time-line"></i>
                                    Masih terbuka
                                </div>
                            @else
                                <div
                                    class="inline-flex w-fit items-center gap-2 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                    <i class="ri-error-warning-line"></i>
                                    Belum diatur (buka semua)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            <form id="spvw-mode-form" method="GET"
                action="{{ route('spvw.rekap.index', array_filter(['client_id' => $spvwClientId])) }}" class="hidden">
                <input type="hidden" name="mode" id="spvw-mode-input" value="{{ $selectedMode }}">
                <input type="hidden" name="client_id" value="{{ $selectedClientId }}">
            </form>

            <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-xs">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Export Data Keseluruhan</h2>
                        <p class="text-xs text-slate-500">Export seluruh rekap bulanan semua mitra.</p>
                    </div>
                    <div class="flex flex-wrap items-end gap-2">
                        <label class="text-xs font-medium text-slate-600">
                            Bulan tgl(26-25)
                            <input id="bulanRekap" type="month" value="{{ now()->format('Y-m') }}"
                                onchange="bulanRekap = this.value"
                                class="mt-1 block rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                        </label>
                        <label class="text-xs font-medium text-slate-600">
                            Dari
                            <input id="tanggalDariRekap" type="date" value="{{ now()->subMonth()->format('Y-m-d') }}"
                                class="mt-1 block rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                        </label>
                        <label class="text-xs font-medium text-slate-600">
                            Sampai
                            <input id="tanggalSampaiRekap" type="date" value="{{ now()->format('Y-m-d') }}"
                                class="mt-1 block rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                        </label>
                        <button type="button" onclick="exportGlobalToExcel()"
                            class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            <i class="ri-file-excel-2-line"></i> Excel
                        </button>
                        <button type="button" onclick="exportGlobalToPDF()"
                            class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            <i class="ri-file-pdf-2-line"></i> PDF
                        </button>
                    </div>
                </div>
            </div>

            <section x-data="{
                mode: '{{ $selectedMode }}',
                setMode(nextMode) {
                    this.mode = nextMode;
                    const input = document.getElementById('spvw-mode-input');
                    if (!input) return;
                    input.value = nextMode;
                }
            }"
                class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <div class="p-3 border-b border-slate-200 bg-slate-50 sm:p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 text-white rounded-lg bg-slate-900">
                                <i class="ri-command-line"></i>
                            </span>
                            <div>
                                <h2 class="text-base font-semibold text-slate-900">Aksi Rekap</h2>
                                <p class="text-xs leading-4 text-slate-500">Pilih jenis rekap sesuai kebutuhan.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 p-1 bg-white rounded-lg shadow-xs ring-1 ring-slate-200 sm:w-72">
                            <button type="button" data-mode="pengajuan" onclick="setMode('pengajuan')"
                                class="inline-flex items-center justify-center gap-2 px-3 text-sm font-semibold transition rounded-md min-h-9">
                                <i class="ri-file-add-line"></i>
                                Pengajuan
                            </button>
                            <button type="button" data-mode="riwayat" onclick="setMode('riwayat')"
                                class="inline-flex items-center justify-center gap-2 px-3 text-sm font-semibold transition rounded-md min-h-9">
                                <i class="ri-history-line"></i>
                                Riwayat
                            </button>
                        </div>
                    </div>

                    @if ($selectedClientId <= 0)
                        <div
                            class="mt-3 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                            <i class="ri-error-warning-line mt-0.5 text-sm min-w-4"></i>
                            <p>Pilih mitra terlebih dahulu. Setelah itu menu pengajuan dan riwayat baru bisa dipakai.
                            </p>
                        </div>
                    @endif
                </div>

                <div data-mode-section="pengajuan">
                    @if ($isAfterDueDate ?? false)
                        <div class="px-4 py-3 text-sm text-slate-600 bg-slate-50 border-b border-slate-100">
                            <i class="ri-lock-line"></i> Pengajuan rekap dikunci karena melewati batas waktu.
                        </div>
                    @endif
                    <div class="divide-y divide-slate-100">
                        @foreach ($rekapMenus as $menu)
                            @php
                                $locked = ($isAfterDueDate ?? false) === true;
                                $needsClient = $selectedClientId <= 0;
                                $disabled = $locked || $needsClient;
                            @endphp
                            <a href="{{ $disabled ? '#' : $menu['pengajuan_url'] }}" data-menu-link
                                data-base-url="{{ preg_replace('/([&?])client_id=\d+/', '$1', $menu['pengajuan_url']) }}"
                                @if ($disabled) aria-disabled="true" @endif
                                class="flex items-center justify-between gap-3 px-4 py-3 transition group min-h-16 {{ $disabled ? 'cursor-not-allowed bg-slate-50/70 opacity-70' : 'hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-inset active:bg-slate-100' }}">
                                <span class="flex items-center min-w-0 gap-3">
                                    <span
                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 {{ $menu['icon_bg'] }}">
                                        <i class="{{ $menu['icon'] }} {{ $menu['icon_color'] }} text-lg"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold truncate text-slate-900">
                                            {{ $menu['title'] }}
                                        </span>
                                        <span class="mt-0.5 block text-xs leading-4 text-slate-500">
                                            {{ $menu['description'] }}
                                        </span>
                                    </span>
                                </span>
                                <span data-menu-cta
                                    class="inline-flex h-8 shrink-0 items-center gap-1 rounded-lg px-2.5 text-xs font-semibold ring-1 {{ $disabled ? 'bg-slate-100 text-slate-500 ring-slate-200' : 'bg-emerald-100 text-emerald-800 ring-emerald-200 shadow-xs transition' }}">
                                    {{ $locked ? 'Terkunci' : ($needsClient ? 'Pilih Mitra' : 'Buat') }}
                                    <i class="{{ $locked ? 'ri-lock-line' : 'ri-arrow-right-line' }}"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div data-mode-section="riwayat">
                    <div class="divide-y divide-slate-100">
                        @foreach ($rekapMenus as $menu)
                            <a href="{{ $selectedClientId > 0 ? $menu['riwayat_url'] : '#' }}" data-menu-link
                                data-base-url="{{ preg_replace('/([&?])client_id=\d+/', '$1', $menu['riwayat_url']) }}"
                                @if ($selectedClientId <= 0) aria-disabled="true" @endif
                                class="flex items-center justify-between gap-3 px-4 py-3 transition group min-h-16 {{ $selectedClientId > 0 ? 'hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-inset active:bg-slate-100' : 'cursor-not-allowed bg-slate-50/70 opacity-70' }}">
                                <span class="flex items-center min-w-0 gap-3">
                                    <span
                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 {{ $menu['icon_bg'] }}">
                                        <i class="{{ $menu['icon'] }} {{ $menu['icon_color'] }} text-lg"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold truncate text-slate-900">
                                            {{ $menu['title'] }}
                                        </span>
                                        <span class="mt-0.5 block text-xs leading-4 text-slate-500">
                                            {{ $menu['description'] }}
                                        </span>
                                    </span>
                                </span>
                                <span data-menu-cta
                                    class="inline-flex h-8 shrink-0 items-center gap-1 rounded-lg px-2.5 text-xs font-semibold ring-1 {{ $selectedClientId > 0 ? 'bg-indigo-100 text-indigo-800 ring-indigo-200 shadow-xs transition' : 'bg-slate-100 text-slate-500 ring-slate-200' }}">
                                    {{ $selectedClientId > 0 ? 'Lihat' : 'Pilih Mitra' }}
                                    <i class="ri-arrow-right-line"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </x-main-div>

    <script>
        const SPVW_MODE_STORAGE_KEY = 'spvw_rekap_mode';
        const SPVW_CLIENT_STORAGE_KEY = 'spvw_selected_client_id';
        const REKAP_LOCKED = {{ $isAfterDueDate ?? false ? 'true' : 'false' }};
        let bulanRekap = document.getElementById('bulanRekap')?.value || new Date().toISOString().slice(0, 7);

        async function exportGlobal(type) {
            try {
                const params = new URLSearchParams({ month: bulanRekap });
                const startDate = document.getElementById('tanggalDariRekap')?.value;
                const endDate = document.getElementById('tanggalSampaiRekap')?.value;
                if (startDate && endDate) {
                    params.set('start_date', startDate);
                    params.set('end_date', endDate);
                }

                const response = await fetch(`/SPVW/api/v1/all-rekap-export-global?${params}`);
                if (!response.ok) throw new Error('Failed to fetch data');
                const result = await response.json();
                if (!result.success) throw new Error(result.message);
                // result.data.overtimes[1].tgl.map((i, index) => {
                //     console.log(i);
                // })
                // console.log(result.data)
                new RekapExporter(null, bulanRekap)[type](result.data);
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        function exportGlobalToExcel() {
            exportGlobal('exportGlobalToExcel');
        }

        function exportGlobalToPDF() {
            exportGlobal('exportGlobalToPDF');
        }

        // Tab switching tanpa reload (seamless)
        function setMode(nextMode) {
            const sections = document.querySelectorAll('[data-mode-section]');
            const buttons = document.querySelectorAll('[data-mode]');
            const select = document.getElementById('client_id');
            const modeInput = document.getElementById('spvw-mode-input');

            const hasClient = Boolean(select && select.value);

            // Update hidden input so other code can read current mode
            if (modeInput) modeInput.value = nextMode;

            // Persist mode in current browser session
            try {
                sessionStorage.setItem(SPVW_MODE_STORAGE_KEY, nextMode);
            } catch (_) {
                // ignore browser storage restrictions
            }

            // Update buttons' visual state
            buttons.forEach(btn => {
                const mode = btn.getAttribute('data-mode');
                if (mode === nextMode) {
                    btn.classList.remove('text-slate-600', 'hover:bg-slate-50');
                    if (mode === 'pengajuan') {
                        btn.classList.add('bg-emerald-600', 'text-white', 'shadow-xs');
                        btn.classList.remove('bg-indigo-600', 'ring-indigo-300/50');
                    } else {
                        btn.classList.add('bg-indigo-600', 'text-white', 'shadow-xs');
                        btn.classList.remove('bg-emerald-600', 'ring-emerald-300/50');
                    }
                } else {
                    btn.classList.remove('bg-emerald-600', 'bg-indigo-600', 'text-white', 'shadow-xs');
                    btn.classList.add('text-slate-600', 'hover:bg-slate-50');
                }
            });

            // Show/hide sections
            sections.forEach(section => {
                if (section.getAttribute('data-mode-section') === nextMode) {
                    section.style.display = 'block';
                    setTimeout(() => section.style.opacity = '1', 10);
                } else {
                    section.style.display = 'none';
                    section.style.opacity = '0';
                }
            });

            // Update CTA texts and classes
            document.querySelectorAll('[data-menu-cta]').forEach((el) => {
                // Text
                const label = hasClient ? (nextMode === 'pengajuan' ? 'Buat' : 'Lihat') : 'Pilih Mitra';
                // replace first text node content if present
                const firstText = el.childNodes[0];
                if (firstText && firstText.nodeType === Node.TEXT_NODE) {
                    firstText.textContent = label + ' ';
                } else {
                    el.insertAdjacentText('afterbegin', label + ' ');
                }

                // Classes
                if (!hasClient) {
                    el.className =
                        'inline-flex h-8 shrink-0 items-center gap-1 rounded-lg px-2.5 text-xs font-semibold ring-1 bg-slate-100 text-slate-500 ring-slate-200';
                } else if (nextMode === 'pengajuan') {
                    el.className =
                        'inline-flex h-8 shrink-0 items-center gap-1 rounded-lg px-2.5 text-xs font-semibold ring-1 bg-emerald-100 text-emerald-800 ring-emerald-200 shadow-xs transition';
                } else {
                    el.className =
                        'inline-flex h-8 shrink-0 items-center gap-1 rounded-lg px-2.5 text-xs font-semibold ring-1 bg-indigo-100 text-indigo-800 ring-indigo-200 shadow-xs transition';
                }
            });
        }

        // Initialize tab switching on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            let storedMode = null;
            try {
                storedMode = sessionStorage.getItem(SPVW_MODE_STORAGE_KEY);
            } catch (_) {
                storedMode = null;
            }

            const fallbackMode = document.getElementById('spvw-mode-input')?.value || '{{ $selectedMode }}';
            const currentMode = ['pengajuan', 'riwayat'].includes(storedMode) ? storedMode : fallbackMode;
            setMode(currentMode);
        });

        window.handleSpvwClientFilterChange = async function() {
            const form = document.getElementById('spvw-client-filter-form');
            const select = document.getElementById('client_id');
            if (!form || !select) return;

            const clientId = select.value;
            const params = new URLSearchParams();
            if (clientId) params.set('client_id', clientId);
            const targetUrl = `${form.action.split('?')[0]}${params.toString() ? `?${params.toString()}` : ''}`;

            window.history.replaceState({}, '', targetUrl);
            try {
                sessionStorage.setItem(SPVW_CLIENT_STORAGE_KEY, clientId);
            } catch (_) {
                // ignore browser storage restrictions
            }
            const selectedText = select.options[select.selectedIndex]?.text?.trim() || '';
            const hasClient = Boolean(clientId);

            document.querySelectorAll('[data-menu-link]').forEach((el) => {
                const base = el.getAttribute('data-base-url') || '#';
                const section = el.closest('[data-mode-section]')?.getAttribute('data-mode-section');
                const sectionLocked = REKAP_LOCKED && section === 'pengajuan';
                const enabled = hasClient && !sectionLocked;
                el.href = enabled ? `${base}${base.includes('?') ? '&' : '?'}client_id=${clientId}` : '#';
                if (enabled) {
                    el.removeAttribute('aria-disabled');
                    el.classList.remove('cursor-not-allowed', 'bg-slate-50/70', 'opacity-70');
                    el.classList.add('hover:bg-slate-50', 'focus:outline-hidden', 'focus:ring-2',
                        'focus:ring-slate-400', 'focus:ring-inset', 'active:bg-slate-100');
                } else {
                    el.setAttribute('aria-disabled', 'true');
                    el.classList.add('cursor-not-allowed', 'bg-slate-50/70', 'opacity-70');
                }
                const cta = el.querySelector('[data-menu-cta]');
                if (cta && section === 'pengajuan') {
                    cta.textContent = '';
                    const label = sectionLocked ? 'Terkunci' : (hasClient ? 'Buat' : 'Pilih Mitra');
                    const icon = sectionLocked ? 'ri-lock-line' : 'ri-arrow-right-line';
                    cta.append(document.createTextNode(label + ' '));
                    const i = document.createElement('i');
                    i.className = icon;
                    cta.appendChild(i);
                }
            });
            // Re-apply current mode to update CTA texts and classes
            const currentMode = document.getElementById('spvw-mode-input')?.value || '{{ $selectedMode }}';
            if (typeof setMode === 'function') setMode(currentMode);

            const hint = document.getElementById('spvw-filter-hint');
            if (hint) hint.textContent = hasClient ? 'Filter mitra tetap aktif sampai logout.' :
                'Mode cepat: filter langsung aktif saat mitra dipilih.';
            const active = document.getElementById('spvw-filter-active');
            if (active) active.classList.toggle('hidden', !hasClient);
            const name = document.getElementById('spvw-filter-name');
            if (name) name.innerHTML = `<i class="ri-building-line"></i> ${selectedText || `Mitra #${clientId}`}`;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const resetBtn = document.getElementById('spvw-reset-filter');
            const select = document.getElementById('client_id');
            if (!resetBtn || !select) return;

            resetBtn.addEventListener('click', async (event) => {
                event.preventDefault();

                // First call the reset URL so server middleware clears the session
                try {
                    await fetch(resetBtn.href, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                } catch (_) {
                    // ignore network errors and continue to update UI client-side
                }

                // Then clear the select and update client-side UI (and URL)
                select.value = '';
                await window.handleSpvwClientFilterChange();
            });
        });
    </script>
</x-app-layout>
