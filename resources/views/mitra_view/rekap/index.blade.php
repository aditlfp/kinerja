<x-mitra-layout title="Rekap Bulanan" max-width="max-w-[1400px]">
    {{-- Kepala halaman --}}
    <section class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-medium tracking-wide uppercase text-slate-500">Rekap Bulanan</p>
            <h2 class="mt-1.5 text-2xl font-bold tracking-tight text-slate-900">Laporan Mitra</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-600">
                Arsip dokumen PDF laporan mitra Anda. Pratinjau menampilkan halaman pertama, klik untuk membuka dokumen lengkap.
            </p>
        </div>

        @if ($totalLaporan > 0)
            <div class="w-full md:w-80">
                <label for="cariLaporan" class="sr-only">Cari laporan mitra</label>
                <div class="relative">
                    <i class="absolute -translate-y-1/2 pointer-events-none ri-search-line left-3.5 top-1/2 text-slate-400" aria-hidden="true"></i>
                    <input
                        type="search"
                        id="cariLaporan"
                        placeholder="Cari nama mitra atau berkas"
                        autocomplete="off"
                        class="w-full py-2.5 pl-10 pr-3 text-sm bg-white border rounded-xl border-slate-200 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                    >
                </div>
            </div>
        @endif
    </section>

    {{-- Ringkasan arsip --}}
    @if ($totalLaporan > 0)
        <section class="p-5 mt-4 bg-white border rounded-2xl border-slate-100" aria-label="Ringkasan arsip laporan">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:divide-x sm:divide-slate-100">
                <div class="sm:pr-4">
                    <p class="text-[11px] font-medium text-slate-500">Total laporan</p>
                    <p class="mt-1.5 text-2xl font-bold leading-none tracking-tight text-slate-900 tabular-nums">
                        {{ number_format($totalLaporan, 0, ',', '.') }}<span class="ml-1.5 text-xs font-semibold text-slate-500">dokumen</span>
                    </p>
                </div>
                <div class="sm:px-4">
                    <p class="text-[11px] font-medium text-slate-500">Bulan ini</p>
                    <p class="mt-1.5 text-2xl font-bold leading-none tracking-tight text-slate-900 tabular-nums">
                        {{ number_format($laporanBulanIni, 0, ',', '.') }}<span class="ml-1.5 text-xs font-semibold text-slate-500">laporan masuk</span>
                    </p>
                </div>
                <div class="sm:pl-4">
                    <p class="text-[11px] font-medium text-slate-500">Laporan terbaru</p>
                    <p class="mt-1.5 text-sm font-bold leading-tight tracking-tight text-slate-900">
                        {{ $laporanTerbaru?->period ?: '-' }}
                    </p>
                </div>
            </div>
        </section>

        <form id="filterRekap" class="flex flex-col gap-3 p-4 mt-5 bg-white border rounded-2xl border-slate-100 sm:flex-row sm:items-end" action="{{ route('mitra_rekap') }}">
            <div class="w-full min-w-0 sm:w-[28rem]">
                <div class="flex flex-col gap-3 sm:flex-row w-full">
                <div class="w-full sm:w-44">
                    <label for="bulan" class="label required">Bulan</label>
                    <select
                        id="bulan"
                        name="bulan"
                        class="w-full h-10 text-sm select select-sm select-bordered mitra-input"
                        required
                    >
                        <option value="">Pilih Bulan</option>
                        @foreach (range(1, 12) as $bulan)
                            <option value="{{ sprintf('%02d', $bulan) }}" {{ request('bulan') == sprintf('%02d', $bulan) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-36">
                    <label for="tahun" class="label required">Tahun</label>
                    <input
                        id="tahun"
                        name="tahun"
                        type="number"
                        value="{{ request('tahun', date('Y')) }}"
                        placeholder="Masukkan Tahun"
                        class="w-full h-10 text-sm input input-sm input-bordered mitra-input"
                        required
                    />
                </div>
                <div class="flex flex-col gap-y-1">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 h-9 px-4 text-xs font-semibold text-white rounded-lg bg-[var(--mitra-accent)] hover:bg-[var(--mitra-accent-strong)]">
                        <i class="ri-filter-3-line" aria-hidden="true"></i><span>Filter</span>
                    </button>
                    <a href="{{ route('mitra_rekap') }}" class="inline-flex items-center justify-center h-10 px-4 text-xs font-semibold border rounded-lg border-red-200 text-red-600 hover:bg-red-50">Reset</a>
                </div>
            </div>
        </div>
        </form>

        {{-- Bilah alat daftar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mt-6">
            <p id="hasilHitung" class="text-sm text-slate-600" aria-live="polite">
                Menampilkan {{ number_format($totalLaporan, 0, ',', '.') }} laporan
            </p>
            <button type="button" id="sortLaporan" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white border rounded-full border-slate-100 text-slate-600 hover:bg-slate-50">
                <i class="ri-sort-desc" aria-hidden="true"></i>
                <span>Terbaru dulu</span>
            </button>
        </div>

        <div class="relative mt-3">
            <div id="loadingLaporan" class="absolute inset-0 z-20 items-center justify-center hidden bg-white/70 backdrop-blur-sm rounded-2xl" aria-live="polite" aria-busy="true">
                <div class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-white border rounded-xl shadow-sm text-slate-600 border-slate-200">
                    <i class="text-base ri-loader-4-line animate-spin" aria-hidden="true"></i>
                    Memuat data...
                </div>
            </div>
            <section id="daftarLaporan" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" role="list" aria-label="Daftar laporan mitra">
            @foreach ($laporanMitra as $laporan)
                @php($formatName = $laporan['formatName'])
                @php($url = $laporan['url'])
                @php($monthYear = $laporan['monthYear'])
                <article role="listitem"
                         data-cari="{{ strtolower($client->name . ' ' . $formatName) }}"
                         data-created-at="{{ $laporan['letter']->created_at?->timestamp ?? 0 }}"
                         data-period="{{ strtolower($laporan['monthYear']) }}"
                         class="rekap-item group flex flex-col overflow-hidden transition duration-200 bg-white border rounded-2xl border-slate-100 hover:border-slate-200 hover:shadow-[0_12px_32px_-16px_rgba(15,23,42,0.25)]">
                    <div class="relative p-4 bg-slate-50">
                        <span class="absolute z-10 px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase rounded bg-white/95 text-rose-700 ring-1 ring-slate-900/5 left-6 top-6">
                            PDF
                        </span>

                        @if ($url)
                            <a href="{{ $url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="block"
                               aria-label="Buka laporan {{ $client->name }}">
                                <div class="relative mx-auto overflow-hidden bg-white rounded-lg shadow-sm ring-1 ring-slate-900/5 aspect-[3/4] max-w-[15rem]">
                                    <div class="absolute inset-0 flex items-center justify-center text-slate-300" data-pdf-thumb data-src="{{ $url }}" aria-hidden="true">
                                        <div class="absolute inset-0 bg-slate-100 animate-pulse"></div>
                                        <i class="relative text-2xl ri-file-pdf-2-line"></i>
                                    </div>
                                    <span class="absolute inset-0 flex items-end justify-center p-3 transition duration-200 opacity-0 bg-gradient-to-t from-slate-900/70 via-slate-900/5 to-transparent group-hover:opacity-100">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full shadow-sm bg-white/95 text-slate-900">
                                            <i class="ri-eye-line" aria-hidden="true"></i>
                                            Buka dokumen
                                        </span>
                                    </span>
                                </div>
                            </a>
                        @else
                            <div class="relative mx-auto overflow-hidden rounded-lg bg-white ring-1 ring-slate-900/5 aspect-[3/4] max-w-[15rem]">
                                <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 text-slate-400">
                                    <i class="text-2xl ri-file-damage-line" aria-hidden="true"></i>
                                    <span class="text-[11px]">Berkas tidak tersedia</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col flex-1 p-4 gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium tracking-wide uppercase text-slate-500">{{ $client->name }}</p>
                            <h3 class="mt-0.5 text-sm font-semibold truncate text-slate-900" title="{{ $client->name }}">
                               Laporan Bulanan
                            </h3>
                            <p class="flex items-center gap-1.5 mt-1 text-xs text-slate-500">
                                <i class="flex-shrink-0 ri-file-text-line" aria-hidden="true"></i>
                                <span class="truncate" title="{{ $formatName }}.pdf">{{ $formatName }}.pdf</span>
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-3 mt-auto text-xs border-t border-slate-100 text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <i class="ri-calendar-line" aria-hidden="true"></i>
                                {{ $monthYear }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium">
                                <i class="text-rose-600 ri-file-pdf-2-line" aria-hidden="true"></i>
                                Dokumen PDF
                            </span>
                        </div>

                        @if ($url)
                            <div class="flex gap-2">
                                <a href="{{ $url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex flex-1 items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white transition rounded-lg bg-[var(--mitra-accent)] hover:bg-[var(--mitra-accent-strong)]">
                                    <i class="ri-eye-line" aria-hidden="true"></i>
                                    Lihat
                                </a>
                                <a href="{{ route('download_laporan_bulanan', ['url' => $formatName]) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 transition bg-white border rounded-lg border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                                   aria-label="Unduh {{ $formatName }}.pdf"
                                   title="Unduh berkas">
                                    <i class="ri-download-2-line" aria-hidden="true"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
            </section>
        </div>

        <div id="hasilKosong" class="hidden mt-3 transition duration-200">
            <div class="flex flex-col items-center justify-center gap-2 px-6 py-14 text-center bg-white border border-dashed rounded-2xl border-slate-200">
                <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-slate-50 text-slate-400">
                    <i class="text-xl ri-search-eye-line" aria-hidden="true"></i>
                </span>
                <p class="text-sm font-semibold text-slate-900">Tidak ada laporan yang cocok</p>
                <p class="text-xs text-slate-600">
                    Kata kunci <span id="kataKunci" class="font-medium text-slate-900"></span> tidak ditemukan. Coba nama mitra atau nama berkas lain.
                </p>
            </div>
        </div>
    @else
        <section class="flex flex-col items-center justify-center gap-3 px-6 py-16 mt-6 text-center bg-white border border-dashed rounded-2xl border-slate-200">
            <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-50 text-slate-400">
                <i class="text-2xl ri-file-chart-line" aria-hidden="true"></i>
            </span>
            <div>
                <p class="text-base font-semibold text-slate-900">
                    {{ $hasFilter ? 'Laporan tidak ditemukan' : 'Belum ada laporan mitra' }}
                </p>
                <p class="max-w-md mx-auto mt-1 text-sm text-slate-600">
                    {{ $hasFilter
                        ? 'Tidak ada laporan untuk periode yang dipilih.'
                        : 'Dokumen PDF laporan bulanan yang diunggah manajemen akan muncul di sini.' }}
                </p>
                @if ($hasFilter)
                    <a href="{{ route('mitra_rekap') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 mt-2 text-xs font-semibold text-slate-700 bg-white border rounded-lg border-slate-200 hover:bg-slate-50">
                        <i class="ri-arrow-go-back-line" aria-hidden="true"></i>
                        Reset filter
                    </a>
                @endif
            </div>
        </section>
    @endif

    @if ($totalLaporan > 0)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const input = document.getElementById('cariLaporan');
                const filterForm = document.getElementById('filterRekap');
                const filterMonth = document.getElementById('bulan');
                const filterYear = document.getElementById('tahun');
                const filterButton = filterForm?.querySelector('button[type="submit"]');
                const filterLoading = document.getElementById('loadingLaporan');
                const emptyState = document.getElementById('hasilKosong');
                const setLoading = function (active) {
                    [list, emptyState].forEach(function (element) {
                        element?.classList.toggle('blur-sm', active);
                        element?.classList.toggle('pointer-events-none', active);
                    });
                    filterLoading?.classList.toggle('hidden', !active);
                    filterLoading?.classList.toggle('flex', active);
                };
                const items = Array.from(document.querySelectorAll('.rekap-item'));
                const counter = document.getElementById('hasilHitung');
                const keywordLabel = document.getElementById('kataKunci');
                const total = items.length;
                const sortButton = document.getElementById('sortLaporan');
                const sortLabel = sortButton?.querySelector('span');
                const sortIcon = sortButton?.querySelector('i');
                const list = document.getElementById('daftarLaporan');
                const loading = document.getElementById('loadingLaporan');
                let newestFirst = true;

                sortButton?.addEventListener('click', function () {
                    if (sortButton.disabled) return;

                    sortButton.disabled = true;
                    sortButton.setAttribute('aria-busy', 'true');
                    loading?.classList.remove('hidden');
                    loading?.classList.add('flex');

                    window.setTimeout(function () {
                        newestFirst = !newestFirst;
                        items.sort(function (a, b) {
                            const dateA = Number(a.dataset.createdAt || 0);
                            const dateB = Number(b.dataset.createdAt || 0);
                            return newestFirst ? dateB - dateA : dateA - dateB;
                        }).forEach(function (item) {
                            list.appendChild(item);
                        });

                        sortLabel.textContent = newestFirst ? 'Terbaru dulu' : 'Terlama dulu';
                        sortIcon.className = newestFirst ? 'ri-sort-desc' : 'ri-sort-asc';
                        loading?.classList.add('hidden');
                        loading?.classList.remove('flex');
                        sortButton.disabled = false;
                        sortButton.removeAttribute('aria-busy');
                    }, 250);
                });

                filterForm?.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const month = String(filterMonth?.value || '').padStart(2, '0');
                    const year = filterYear?.value || '';
                    const period = `${month}-${year}`;
                    const hasFilter = Boolean(month && year);

                    filterButton.disabled = true;
                    setLoading(true);

                    window.setTimeout(function () {
                        let visible = 0;
                        items.forEach(function (item) {
                            const match = !hasFilter || item.dataset.period === period;
                            item.classList.toggle('hidden', !match);
                            if (match) visible++;
                        });
                        counter.textContent = `Menampilkan ${visible} laporan`;
                        emptyState?.classList.toggle('hidden', visible !== 0);
                        filterButton.disabled = false;
                        setLoading(false);
                    }, 250);
                });

                if (input) {
                    input.addEventListener('input', function () {
                        const keyword = this.value.toLowerCase().trim();
                        let visible = 0;

                        items.forEach(function (item) {
                            const match = keyword === '' || (item.dataset.cari || '').includes(keyword);
                            item.classList.toggle('hidden', !match);

                            if (match) {
                                visible += 1;
                            }
                        });

                        if (counter) {
                            counter.textContent = keyword === ''
                                ? 'Menampilkan ' + total + ' laporan'
                                : 'Menampilkan ' + visible + ' dari ' + total + ' laporan';
                        }

                        if (emptyState) {
                            emptyState.classList.toggle('hidden', visible !== 0);
                        }

                        if (keywordLabel) {
                            keywordLabel.textContent = '\u201C' + this.value.trim() + '\u201D';
                        }
                    });
                }

                if (typeof pdfjsLib === 'undefined') {
                    return;
                }

                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';

                function renderThumb(container) {
                    if (container.dataset.rendered === '1') {
                        return;
                    }

                    container.dataset.rendered = '1';

                    pdfjsLib.getDocument(container.dataset.src).promise
                        .then(function (pdf) {
                            return pdf.getPage(1);
                        })
                        .then(function (page) {
                            const base = page.getViewport({ scale: 1 });
                            const width = container.clientWidth || 240;
                            const ratio = Math.min(window.devicePixelRatio || 1, 2);
                            const viewport = page.getViewport({ scale: (width / base.width) * ratio });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');

                            canvas.width = Math.floor(viewport.width);
                            canvas.height = Math.floor(viewport.height);
                            canvas.className = 'h-full w-auto';
                            canvas.setAttribute('aria-hidden', 'true');

                            container.innerHTML = '';
                            container.appendChild(canvas);
                            page.render({ canvasContext: context, viewport: viewport });
                        })
                        .catch(function () {
                            container.innerHTML = '<div class="flex flex-col items-center justify-center h-full gap-1 text-slate-400">'
                                + '<i class="text-2xl ri-file-damage-line"></i>'
                                + '<span class="text-[11px]">Pratinjau tidak tersedia</span>'
                                + '</div>';
                        });
                }

                const thumbs = Array.from(document.querySelectorAll('[data-pdf-thumb]'));

                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                renderThumb(entry.target);
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { rootMargin: '300px 0px' });

                    thumbs.forEach(function (thumb) {
                        observer.observe(thumb);
                    });
                } else {
                    thumbs.forEach(renderThumb);
                }
            });
        </script>
    @endif
</x-mitra-layout>
