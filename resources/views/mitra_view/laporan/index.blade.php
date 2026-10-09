<x-mitra-layout title="Riwayat Laporan">
    @php
        $namaClient = auth()->user()->kerjasama->client->name ?? '-';
    @endphp

    <div class="p-6 rounded-3xl mitra-panel mitra-mobile-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em]" style="color: var(--mitra-success);">Laporan Aktivitas</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight mitra-text-strong">Riwayat Laporan {{ $namaClient }}</h1>
                <p class="mt-1 text-sm mitra-text-soft">Pantau dokumentasi progres pekerjaan tim secara berkala.</p>
            </div>
            <div class="w-full md:w-72">
                <input
                    id="searchInput"
                    type="text"
                    placeholder="Cari keterangan..."
                    class="w-full text-sm input input-sm input-bordered mitra-input"
                />
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl mitra-panel-soft">
        <div class="p-4 border-b border-slate-600/50">
            <h2 class="text-xs font-black tracking-[0.3em] uppercase mitra-section-title">Daftar Laporan</h2>
        </div>
        <div class="p-4">
            @if($laporan->isEmpty())
                <p class="text-sm italic mitra-empty-state">Belum ada laporan yang tersedia.</p>
            @else
                <div class="space-y-3 md:hidden" data-viewport-content="mobile">
                    @foreach ($laporan as $item)
                        <article class="p-4 mitra-mobile-list-card mitra-deferred-card laporan-search-item">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.2em] mitra-meta-label">
                                        Laporan {{ $laporan->firstItem() + $loop->index }}
                                    </p>
                                    <p class="mt-1 text-sm mitra-text-soft">
                                        {{ $item->created_at?->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mt-4">
                                @foreach ([
                                    ['label' => 'Before', 'src' => $item->img_before],
                                    ['label' => 'Process', 'src' => $item->img_proccess],
                                    ['label' => 'Final', 'src' => $item->img_final],
                                ] as $image)
                                    <div>
                                        <p class="mb-2 text-[11px] font-semibold uppercase mitra-meta-label">{{ $image['label'] }}</p>
                                        @if ($image['src'])
                                            <img
                                                data-responsive-src="http://laporan-sac.sac-po.com/storage/{{ $image['src'] }}"
                                                data-preview-src="http://laporan-sac.sac-po.com/storage/{{ $image['src'] }}"
                                                alt="Foto {{ $image['label'] }}"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                                width="160"
                                                height="96"
                                                class="object-cover w-full h-24 rounded-lg cursor-pointer imeg"
                                            >
                                        @else
                                            <x-no-img class="w-full h-24 rounded-lg" />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <p class="text-[11px] font-semibold uppercase mitra-meta-label">Keterangan</p>
                                <p class="mt-1 text-sm mitra-text-strong">
                                    @if(auth()->user()->devisi_id == 8)
                                        {{ $item->note }}
                                    @else
                                        {{ $item->keterangan }}
                                    @endif
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="hidden px-4 -mx-4 md:block md:mx-0 md:px-0 mitra-table-wrap" data-viewport-content="desktop">
                    <table id="searchTable" class="w-full text-sm text-left mitra-table mitra-mobile-table-wide">
                        <thead class="text-xs uppercase bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3 text-center" colspan="3">Foto Progres</th>
                                <th class="px-4 py-3">Keterangan</th>
                                <th class="px-8 py-3 text-center">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($laporan as $item)
                                <tr class="border-b mitra-table-row hover:bg-slate-800/30">
                                    <td class="px-4 py-3">{{ $laporan->firstItem() + $loop->index }}.</td>

                                    <td class="px-4 py-3">
                                        @if ($item->img_before)
                                            <img
                                                data-responsive-src="http://laporan-sac.sac-po.com/storage/{{ $item->img_before }}"
                                                data-preview-src="http://laporan-sac.sac-po.com/storage/{{ $item->img_before }}"
                                                alt="Foto Before"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                                width="80"
                                                height="80"
                                                class="object-cover rounded-sm cursor-pointer imeg w-14 h-14 md:w-20 md:h-20"
                                                style="min-width: 3.5rem; min-height: 3.5rem;"
                                            >
                                        @else
                                            <x-no-img class="w-14 h-14 md:w-20 md:h-20" />
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($item->img_proccess)
                                            <img
                                                data-responsive-src="http://laporan-sac.sac-po.com/storage/{{ $item->img_proccess }}"
                                                data-preview-src="http://laporan-sac.sac-po.com/storage/{{ $item->img_proccess }}"
                                                alt="Foto Process"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                                width="80"
                                                height="80"
                                                class="object-cover rounded-sm cursor-pointer imeg w-14 h-14 md:w-20 md:h-20"
                                                style="min-width: 3.5rem; min-height: 3.5rem;"
                                            >
                                        @else
                                            <x-no-img class="w-14 h-14 md:w-20 md:h-20" />
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($item->img_final)
                                            <img
                                                data-responsive-src="http://laporan-sac.sac-po.com/storage/{{ $item->img_final }}"
                                                data-preview-src="http://laporan-sac.sac-po.com/storage/{{ $item->img_final }}"
                                                alt="Foto Final"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                                width="80"
                                                height="80"
                                                class="object-cover rounded-sm cursor-pointer imeg w-14 h-14 md:w-20 md:h-20"
                                                style="min-width: 3.5rem; min-height: 3.5rem;"
                                            >
                                        @else
                                            <x-no-img class="w-14 h-14 md:w-20 md:h-20" />
                                        @endif
                                    </td>

                                    @if(auth()->user()->devisi_id == 8)
                                        <td class="px-4 py-3">{{ $item->note }}</td>
                                    @else
                                        <td class="px-4 py-3">{{ $item->keterangan }}</td>
                                    @endif
                                    <td class="px-4 py-3 text-center">{{ $item->created_at?->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">
                    {{ $laporan->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="imageModal" class="fixed inset-0 z-50 hidden bg-slate-950/70 backdrop-blur-xs">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative p-3 rounded-2xl mitra-modal-surface">
                <button
                    id="closeImageModal"
                    class="absolute text-lg leading-none btn btn-sm btn-error -top-3 -right-3"
                    type="button"
                >
                    &times;
                </button>
                <img id="modalImagePreview" src="" alt="Preview Laporan" class="object-contain w-full max-w-xs rounded-sm md:max-w-sm">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const rows = document.querySelectorAll('#searchTable tbody tr');
            const cards = document.querySelectorAll('.laporan-search-item');

            if (searchInput && (rows.length || cards.length)) {
                searchInput.addEventListener('input', function () {
                    const keyword = this.value.toLowerCase().trim();
                    rows.forEach(function (row) {
                        const content = row.textContent.toLowerCase();
                        row.style.display = content.includes(keyword) ? '' : 'none';
                    });
                    document.querySelectorAll('.laporan-search-item').forEach(function (card) {
                        const content = card.textContent.toLowerCase();
                        card.style.display = content.includes(keyword) ? '' : 'none';
                    });
                });
            }

            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImagePreview');
            const closeBtn = document.getElementById('closeImageModal');

            document.addEventListener('click', function (event) {
                const previewImage = event.target.closest('.imeg');
                if (!previewImage || !modal || !modalImage) return;

                const src = previewImage.getAttribute('data-preview-src');
                if (!src) return;

                modalImage.src = src;
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });

            function closeModal() {
                if (!modal || !modalImage) return;
                modal.classList.add('hidden');
                modalImage.removeAttribute('src');
                document.body.classList.remove('overflow-hidden');
            }

            if (closeBtn && modal) {
                closeBtn.addEventListener('click', function () {
                    closeModal();
                });
            }

            if (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            }
        });
    </script>
</x-mitra-layout>
