<x-app-layout>
    @push('scripts')
        <script src="{{ URL::asset('js/rekap-export.js') }}"></script>
        <script>
            async function exportLeader(format) {
                try {
                    const month = document.getElementById('bulanRekap').value;
                    const startDate = document.getElementById('tanggalDariRekap')?.value;
                    const endDate = document.getElementById('tanggalSampaiRekap')?.value;
                    const fetcher = new RekapExporter({{ auth()->user()->kerjasama_id }}, month);
                    fetcher.apiUrl += '&include_all_status=1&with_status=1';
                    if (startDate && endDate) {
                        fetcher.apiUrl += `&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
                    }
                    const result = await fetcher.fetchAllData();
                    if (!result.success) throw new Error(result.message);

                    const exporter = new RekapExporter(null, month);
                    format === 'pdf' ? exporter.exportGlobalToPDF(result.data) : exporter.exportGlobalToExcel(result.data);
                } catch (e) {
                    alert('Error: ' + e.message);
                }
            }
        </script>
    @endpush

    @php
        $rekapMenus = [
            [
                'key' => 'lembur',
                'title' => 'Lembur',
                'description' => 'Catat lembur personil dan pantau status pengajuannya.',
                'icon' => 'ri-time-line',
                'icon_bg' => 'bg-sky-100 ring-sky-200',
                'icon_color' => 'text-sky-700',
                'pengajuan_url' => route('overtime-application.create'),
                'riwayat_url' => route('overtime-application.history'),
            ],
            [
                'key' => 'personil-keluar',
                'title' => 'Personil Keluar',
                'description' => 'Ajukan personil keluar dari site atau area kerja.',
                'icon' => 'ri-user-unfollow-line',
                'icon_bg' => 'bg-amber-100 ring-amber-200',
                'icon_color' => 'text-amber-700',
                'pengajuan_url' => route('person-is-out.create'),
                'riwayat_url' => route('person-is-out.history'),
            ],
            [
                'key' => 'personil-masuk',
                'title' => 'Personil Masuk',
                'description' => 'Input personil baru atau perpindahan masuk.',
                'icon' => 'ri-user-follow-line',
                'icon_bg' => 'bg-emerald-100 ring-emerald-200',
                'icon_color' => 'text-emerald-700',
                'pengajuan_url' => route('person-in.index'),
                'riwayat_url' => route('person.in.history'),
            ],
            [
                'key' => 'potongan',
                'title' => 'Cutting',
                'description' => 'Kelola potongan performa dengan riwayat yang mudah dicek.',
                'icon' => 'ri-auction-fill',
                'icon_bg' => 'bg-red-100 ring-red-200',
                'icon_color' => 'text-red-700',
                'pengajuan_url' => route('cutting.index'),
                'riwayat_url' => route('cutting.history'),
            ],
            [
                'key' => 'lepas-training',
                'title' => 'Lepas Training',
                'description' => 'Rekap personil yang selesai masa training.',
                'icon' => 'ri-footprint-fill',
                'icon_bg' => 'bg-indigo-100 ring-indigo-200',
                'icon_color' => 'text-indigo-700',
                'pengajuan_url' => route('finished-training.index'),
                'riwayat_url' => route('finished-training.history'),
            ],
            [
                'key' => 'keterangan-lanjutan',
                'title' => 'Keterangan Lanjutan',
                'description' => 'Lengkapi keterangan tambahan untuk data rekap.',
                'icon' => 'ri-file-text-line',
                'icon_bg' => 'bg-violet-100 ring-violet-200',
                'icon_color' => 'text-violet-700',
                'pengajuan_url' => route('keterangan-lanjutan.index'),
                'riwayat_url' => route('keterangan-lanjutan.history'),
            ],
        ];

        $dueDateLabel = ($dueDate ?? null)
            ? 'Setiap tgl ' . (int) \Carbon\Carbon::parse($dueDate->due_date)->day . ' pukul ' . \Carbon\Carbon::parse($dueDate->due_date)->format('H:i')
            : null;
    @endphp

    @push('styles')
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush

        <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                    <a href="{{ route('dashboard.index') }}"
                        class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 transition hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-sky-400">
                        <i class="ri-home-4-line"></i>
                        Dashboard
                    </a>
                </div>

                <div
                    class="mb-4 overflow-hidden rounded-2xl border border-white/60 bg-white shadow-xs ring-1 ring-slate-900/5">
                <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="max-w-2xl">
                                <div
                                    class="mb-2 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                    <i class="text-sm ri-stack-line text-sky-700"></i>
                                    Data Rekap
                                </div>
                                <h1 class="border-l-4 border-sky-500 pl-3 text-xl font-bold leading-tight text-slate-900 sm:text-2xl">
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
                                    <p class="text-xl font-semibold text-slate-900">CO</p>
                                </div>
                            </div>
                        </div>
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
                                    <p class="text-base font-semibold text-slate-900">{{ $dueDateLabel ?? 'Belum diatur' }}
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


            <section x-data="{
                mode: 'pengajuan',
                key: 'leaderRekapMode',
                init() { this.mode = sessionStorage.getItem(this.key) || 'pengajuan' },
                setMode(mode) { this.mode = mode; sessionStorage.setItem(this.key, mode) },
            }" class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <div class="border-b border-slate-200 bg-slate-50 p-3 sm:p-4">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white">
                                <i class="ri-command-line text-lg"></i>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-base font-semibold text-slate-900">Aksi Rekap</h2>
                                <p class="text-xs leading-4 text-slate-500">Pilih jenis rekap sesuai kebutuhan.</p>
                            </div>
                        </div>

                        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
                            <button type="button" @click="setMode(mode === 'exportAll' ? 'pengajuan' : 'exportAll')"
                                class="flex w-full items-center gap-3 rounded-xl border px-4 py-3 text-left shadow-xs transition duration-200 focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                :class="mode === 'exportAll'
                                    ? 'border-emerald-600 bg-emerald-600 text-white shadow-md'
                                    : 'border-emerald-200 bg-white text-slate-800 hover:border-emerald-400 hover:bg-emerald-50'">
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg"
                                    :class="mode === 'exportAll' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">
                                    <i class="text-xl" :class="mode === 'exportAll' ? 'ri-close-line' : 'ri-download-2-line'"></i>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-bold">Export Semua Data</span>
                                    <span class="block text-xs" :class="mode === 'exportAll' ? 'text-emerald-50' : 'text-slate-500'">
                                        Download seluruh rekap bulanan ke Excel / PDF
                                    </span>
                                </span>
                            </button>

                            <div class="grid grid-cols-2 rounded-xl bg-white p-1 shadow-xs ring-1 ring-slate-200">
                                <button type="button" @click="setMode('pengajuan')"
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg px-3 text-sm font-semibold transition"
                                    :class="mode === 'pengajuan' ? 'bg-emerald-600 text-white shadow-xs' :
                                        'text-slate-600 hover:bg-slate-50'">
                                    <i class="ri-file-add-line"></i>
                                    Pengajuan
                                </button>
                                <button type="button" @click="setMode('riwayat')"
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg px-3 text-sm font-semibold transition"
                                    :class="mode === 'riwayat' ? 'bg-indigo-600 text-white shadow-xs' :
                                        'text-slate-600 hover:bg-slate-50'">
                                    <i class="ri-history-line"></i>
                                    Riwayat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="mode === 'pengajuan'" x-cloak>
                    @if ($isAfterDueDate ?? false)
                        <div class="px-4 py-3 text-sm text-slate-600 bg-slate-50 border-b border-slate-100">
                            <i class="ri-lock-line"></i> Pengajuan rekap dikunci karena melewati batas waktu.
                        </div>
                    @endif
                    <div class="divide-y divide-slate-100">
                        @foreach ($rekapMenus as $menu)
                            @if ($isAfterDueDate ?? false)
                            <div
                                class="flex items-center justify-between gap-3 px-4 py-3 min-h-16 opacity-60 cursor-not-allowed bg-slate-50">
                            @else
                            <a href="{{ $menu['pengajuan_url'] }}"
                                class="flex items-center justify-between gap-3 px-4 py-3 transition group min-h-16 hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-inset active:bg-slate-100">
                            @endif
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
                                <span
                                    class="inline-flex h-8 shrink-0 items-center gap-1 rounded-lg px-2.5 text-xs font-semibold ring-1 {{ ($isAfterDueDate ?? false) ? 'bg-slate-100 text-slate-500 ring-slate-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-100 transition group-hover:bg-emerald-100' }}">
                                    {{ ($isAfterDueDate ?? false) ? 'Terkunci' : 'Buat' }}
                                    <i class="{{ ($isAfterDueDate ?? false) ? 'ri-lock-line' : 'ri-arrow-right-line' }}"></i>
                                </span>
                            @if ($isAfterDueDate ?? false)
                            </div>
                            @else
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div x-show="mode === 'exportAll'" x-cloak>
                    <div class="p-4 sm:p-5">
                        <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-xs ring-1 ring-slate-900/5">
                            <div class="border-b border-emerald-100 bg-linear-to-r from-emerald-50 to-teal-50 p-4 sm:p-5">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs">
                                        <i class="ri-download-cloud-2-line text-xl"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-bold text-slate-900">Export Data Keseluruhan</h3>
                                        <p class="mt-1 text-sm leading-5 text-slate-600">
                                            Pilih periode, lalu download seluruh rekap bulanan.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 p-4 sm:p-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-end">
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <label class="block text-sm font-semibold text-slate-700">
                                        Periode rekap <span class="text-xs font-normal text-slate-500">(tgl 26-25)</span>
                                        <input id="bulanRekap" type="month" value="{{ now()->format('Y-m') }}"
                                            class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                                    </label>
                                    <label class="block text-sm font-semibold text-slate-700">
                                        Dari
                                        <input id="tanggalDariRekap" type="date" value="{{ now()->subMonth()->format('Y-m-d') }}"
                                            class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                                    </label>
                                    <label class="block text-sm font-semibold text-slate-700">
                                        Sampai
                                        <input id="tanggalSampaiRekap" type="date" value="{{ now()->format('Y-m-d') }}"
                                            class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                                    </label>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <button type="button" onclick="exportLeader('excel')"
                                        class="group flex min-h-14 items-center justify-center gap-3 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-xs transition hover:bg-emerald-700 focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 group-hover:bg-white/20">
                                            <i class="ri-file-excel-2-line text-xl"></i>
                                        </span>
                                        Download Excel
                                    </button>
                                    <button type="button" onclick="exportLeader('pdf')"
                                        class="group flex min-h-14 items-center justify-center gap-3 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-xs transition hover:bg-red-700 focus:outline-hidden focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 group-hover:bg-white/20">
                                            <i class="ri-file-pdf-2-line text-xl"></i>
                                        </span>
                                        Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="mode === 'riwayat'" x-cloak>
                    <div class="divide-y divide-slate-100">
                        @foreach ($rekapMenus as $menu)
                            <a href="{{ $menu['riwayat_url'] }}"
                                class="flex items-center justify-between gap-3 px-4 py-3 transition group min-h-16 hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-inset active:bg-slate-100">
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
                                <span
                                    class="inline-flex h-8 shrink-0 items-center gap-1 rounded-lg bg-indigo-50 px-2.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100 transition group-hover:bg-indigo-100">
                                    Lihat
                                    <i class="ri-arrow-right-line"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
            </div>
        </div>
</x-app-layout>
