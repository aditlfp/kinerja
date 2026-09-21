<x-admin-layout :fullWidth="true">
    @section('title', 'Data Poin')

    <div x-data="{ delOpen: false, deleteId: null, deleteName: '' }" class="w-full max-w-screen-xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Poin Klien</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola nilai poin per klien untuk kebutuhan perhitungan absensi.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari client atau nominal poin..." />
                    </label>
                    <a href="{{ route('admin.point.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-semibold text-white transition bg-yellow-500 rounded-xl hover:bg-yellow-600">
                        <i class="ri-add-line mr-1.5 text-base"></i> Tambah Poin
                    </a>
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="flex items-center justify-between px-4 py-3 text-xs text-gray-500 border-b border-gray-100 sm:px-5">
                <span class="rounded-full bg-yellow-50 px-2.5 py-1 font-semibold text-yellow-700">Total: {{ $point->total() }}</span>
            </div>
            <div class="w-full max-w-full overflow-x-auto">
                <table class="w-full min-w-[560px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Client</th>
                            <th class="px-4 py-3 sm:px-5">Poin</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $no = ($point->currentPage() - 1) * $point->perPage() + 1; @endphp
                        @forelse ($point as $i)
                            <tr class="transition-colors hover:bg-yellow-50/30">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800 sm:px-5">{{ $i->client?->name ?? '-' }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->sac_point }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ url('point/' . $i->id . '/edit') }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-yellow-200 bg-yellow-50 px-2.5 text-[11px] font-semibold text-yellow-700 transition hover:bg-yellow-100"
                                        >
                                            <i class="text-xs ri-edit-line"></i>
                                            Edit
                                        </a>
                                        <button
                                            @click="
                                                delOpen = true;
                                                deleteId = {{ $i->id }};
                                                deleteName = '{{ $i->client?->name ?? 'Data poin ini' }}';
                                            "
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 text-[11px] font-semibold text-red-700 transition hover:bg-red-100"
                                        >
                                            <i class="text-xs ri-delete-bin-line"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data poin belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-100 sm:px-5">
                {{ $point->links() }}
            </div>
        </section>

        <div x-show="delOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/35 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden bg-white shadow-xl rounded-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Konfirmasi Hapus Poin</h3>
                    <button @click="delOpen = false" type="button" class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50">Tutup</button>
                </div>
                <form :action="`{{ route('admin.point.index') }}/${deleteId}`" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-gray-600">Yakin ingin menghapus data poin untuk <span class="font-semibold text-gray-800" x-text="deleteName"></span>?</p>
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
    @endpush
</x-admin-layout>
