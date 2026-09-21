<x-admin-layout :fullWidth="true">
    @section('title', 'Data Kerjasama')

    <div x-data="{ delOpen: false, deleteId: null, deleteName: '' }" class="w-full max-w-screen-xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Kerjasama</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola kontrak kerjasama dengan client dan masa berlaku dokumen.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari client, approval, expired..." />
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" id="exportPdfBtn" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-red-600 rounded-xl hover:bg-red-700">
                            <i class="ri-file-pdf-2-line mr-1.5 text-base"></i> Export PDF
                        </button>
                        <a href="{{ route('admin.kerjasama.create') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
                            <i class="ri-add-line mr-1.5 text-base"></i> Tambah Kerjasama
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="flex flex-wrap items-center gap-2 px-4 py-3 text-xs text-gray-500 border-b border-gray-100 sm:px-5">
                <span class="rounded-full bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">Total: {{ $kerjasama->total() }}</span>
                <span>Kontrak kerjasama client.</span>
            </div>
            <div class="w-full max-w-full overflow-x-auto">
                <table class="w-full min-w-[760px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Client</th>
                            <th class="px-4 py-3 sm:px-5">Value</th>
                            <th class="px-4 py-3 sm:px-5">Expired</th>
                            <th class="hidden px-4 py-3 md:table-cell sm:px-5">Approved 1</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php
                            $no = 1;
                            $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        @endphp
                        @forelse ($kerjasama as $i)
                            @php
                                $expired = \Carbon\Carbon::parse($i->experied);
                                $daysLeft = now()->startOfDay()->diffInDays($expired, false);
                                $statusClass = $daysLeft < 30 ? 'border-l-4 border-red-500 bg-red-50/70' : ($daysLeft <= 90 ? 'border-l-4 border-yellow-500 bg-yellow-50/70' : 'border-l-4 border-green-500 bg-green-50/70');
                            @endphp
                            <tr class="{{ $statusClass }} transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="font-semibold text-gray-800">{{ mb_convert_case(mb_strtolower($i->client->name ?? '-', 'UTF-8'), MB_CASE_TITLE, 'UTF-8') }}</p>
                                    <p class="text-xs text-gray-500 md:hidden">{{ $i->approve1 }}</p>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-800 sm:px-5">{{ toRupiah($i->value) }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $expired->format('d') }} {{ $bulan[(int) $expired->format('n')] }} {{ $expired->format('Y') }}</td>
                                <td class="hidden px-4 py-3 md:table-cell sm:px-5">{{ $i->approve1 }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ url('kerjasama/' . $i->id . '/edit') }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100"
                                        >
                                            <i class="ri-edit-line text-xs"></i>
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            @click="delOpen = true; deleteId = {{ $i->id }}; deleteName = '{{ addslashes($i->client->name) }}'"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 text-[11px] font-semibold text-red-700 transition hover:bg-red-100"
                                        >
                                            <i class="ri-delete-bin-6-line text-xs"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data kerjasama masih kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 sm:px-5">
                {{ $kerjasama->links() }}
            </div>
        </section>

        <div x-show="delOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/35 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden bg-white shadow-xl rounded-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Konfirmasi Hapus Kerjasama</h3>
                    <button @click="delOpen = false" type="button" class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50">Tutup</button>
                </div>
                <form :action="`{{ url('kerjasama') }}/${deleteId}`" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus kerjasama milik client <span class="font-semibold text-gray-800" x-text="deleteName"></span>?</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="delOpen = false" class="px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-3 py-2 text-xs font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            [x-cloak] { display: none !important; }
        </style>
        @php
            $exportRows = $allKerjasama->values()->map(function ($i, $index) use ($bulan) {
                $expired = \Carbon\Carbon::parse($i->experied);
                $daysLeft = now()->startOfDay()->diffInDays($expired, false);

                return [
                    'data' => [
                        $index + 1,
                        $i->client?->name ?? '-',
                        toRupiah($i->value),
                        $expired->format('d') . ' ' . $bulan[(int) $expired->format('n')] . ' ' . $expired->format('Y'),
                        $i->approve1,
                    ],
                    'color' => $daysLeft < 30 ? [254, 226, 226] : ($daysLeft <= 90 ? [254, 249, 195] : [220, 252, 231]),
                ];
            });
        @endphp
        <script>
            (() => {
                const rows = @json($exportRows);

                document.addEventListener('DOMContentLoaded', () => {
                    document.getElementById('exportPdfBtn')?.addEventListener('click', () => {
                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF('l', 'mm', 'a4');

                        doc.text('Data Kerjasama', 14, 14);
                        doc.autoTable({
                            head: [['#', 'Client', 'Value', 'Expired', 'Approved 1']],
                            body: rows.map(row => row.data),
                            startY: 26,
                            styles: { fontSize: 8 },
                            headStyles: { fillColor: [37, 99, 235] },
                            didParseCell: ({ section, row, cell }) => {
                                if (section === 'body') cell.styles.fillColor = rows[row.index].color;
                            },
                        });
                        window.open(doc.output('bloburl'), '_blank');
                    });
                });
            })();
        </script>
    @endpush
</x-admin-layout>
