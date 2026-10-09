<x-mitra-layout title="Riwayat Lembur">
    @php
        $namaClient = auth()->user()->kerjasama->client->name ?? '-';
    @endphp

    <div class="p-6 rounded-3xl mitra-panel mitra-mobile-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em]" style="color: var(--mitra-warning);">Lembur Karyawan</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight mitra-text-strong">Riwayat Lembur {{ $namaClient }}</h1>
                <p class="mt-1 text-sm mitra-text-soft">Pantau catatan jam lembur seluruh personel mitra.</p>
            </div>
            <div class="w-full md:w-72">
                <input
                    id="searchInput"
                    type="text"
                    placeholder="Cari nama..."
                    class="w-full text-sm input input-sm input-bordered mitra-input"
                />
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl mitra-panel-soft">
        <div class="p-4 border-b border-slate-600/50">
            <h2 class="text-xs font-black tracking-[0.3em] uppercase mitra-section-title">Daftar Lembur</h2>
        </div>
        <div class="p-4">
            @if($lembur->isEmpty())
                <p class="text-sm italic mitra-empty-state">Belum ada data lembur.</p>
            @else
                <div class="space-y-3 md:hidden" data-viewport-content="mobile">
                    @foreach ($lembur as $item)
                        <article class="p-4 mitra-mobile-list-card lembur-search-item">
                            <div class="flex items-start gap-3">
                                @if($item->image)
                                    <img
                                        data-responsive-src="{{ asset('storage/images/' . $item->image) }}"
                                        alt="Foto {{ $item->user->nama_lengkap ?? 'Karyawan' }}"
                                        class="object-cover w-12 h-12 rounded-sm"
                                        loading="lazy"
                                        decoding="async"
                                        fetchpriority="low"
                                    >
                                @else
                                    <div class="flex items-center justify-center w-12 h-12 text-xs font-bold rounded-sm mitra-avatar-fallback">
                                        {{ strtoupper(substr($item->user->nama_lengkap ?? 'K', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold mitra-text-strong">{{ capitalizeWords($item->user->nama_lengkap ?? '-') }}</p>
                                    <p class="mt-1 text-sm mitra-text-soft">{{ $item->created_at?->format('Y-m-d') }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 mt-4 text-sm">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase mitra-meta-label">Lama Lembur</p>
                                    <div class="mt-1 mitra-text-strong">
                                        @if ($item->jam_selesai == null)
                                            Belum Selesai Lembur
                                        @else
                                            @php
                                                $masuk = strtotime($item->jam_mulai);
                                                $keluar = strtotime($item->jam_selesai);
                                                $msk = date('H', $masuk);
                                                $klr = date('H', $keluar);
                                                $tot = $klr - $msk;
                                            @endphp
                                            @if($tot <= 0)
                                                <span style="color: var(--mitra-danger);">0 Jam</span>
                                            @else
                                                {{ $tot . ' Jam' }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="hidden -mx-4 px-4 md:block md:mx-0 md:px-0 mitra-table-wrap" data-viewport-content="desktop">
                    <table id="searchTable" class="w-full text-sm text-left mitra-table mitra-mobile-table">
                        <thead class="text-xs uppercase bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Foto</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Lama Lembur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lembur as $item)
                                <tr class="border-b mitra-table-row hover:bg-slate-800/30">
                                    <td class="px-4 py-3">{{ $lembur->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3">
                                        @if($item->image)
                                            <img
                                                data-responsive-src="{{ asset('storage/images/' . $item->image) }}"
                                                alt="Foto {{ $item->user->nama_lengkap ?? 'Karyawan' }}"
                                                class="object-cover w-12 h-12 rounded-sm"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                            >
                                        @else
                                            <div class="flex items-center justify-center w-12 h-12 text-xs font-bold rounded-sm mitra-avatar-fallback">
                                                {{ strtoupper(substr($item->user->nama_lengkap ?? 'K', 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ capitalizeWords($item->user->nama_lengkap ?? '-') }}</td>
                                    <td class="px-4 py-3">{{ $item->created_at?->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        @if ($item->jam_selesai == null)
                                            Belum Selesai Lembur
                                        @else
                                            @php
                                                $masuk = strtotime($item->jam_mulai);
                                                $keluar = strtotime($item->jam_selesai);
                                                $msk = date('H', $masuk);
                                                $klr = date('H', $keluar);
                                                $tot = $klr - $msk;
                                            @endphp
                                            @if($tot <= 0)
                                                <span style="color: var(--mitra-danger);">0 Jam</span>
                                            @else
                                                {{ $tot . ' Jam' }}
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">
                    {{ $lembur->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const rows = document.querySelectorAll('#searchTable tbody tr');
            const cards = document.querySelectorAll('.lembur-search-item');

            if (!searchInput || (!rows.length && !cards.length)) return;

            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(function (row) {
                    const content = row.textContent.toLowerCase();
                    row.style.display = content.includes(keyword) ? '' : 'none';
                });
                cards.forEach(function (card) {
                    const content = card.textContent.toLowerCase();
                    card.style.display = content.includes(keyword) ? '' : 'none';
                });
            });
        });
    </script>
</x-mitra-layout>
