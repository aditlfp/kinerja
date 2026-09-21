<x-admin-layout :fullWidth="true">
    @section('title', 'Data Ruangan')

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Ruangan</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola daftar ruangan berdasarkan mitra/client.</p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex h-10 w-full items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 sm:w-72">
                        <i class="ri-search-2-line text-base text-gray-500"></i>
                        <input type="search" id="searchInput" class="w-full border-none bg-transparent text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none" placeholder="Cari ruangan atau client..." />
                    </label>
                    <a href="{{ route('admin.ruangan.create') }}" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700"><i class="ri-add-line mr-1.5"></i>Ruangan</a>
                    <a href="{{ route('admin.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Dashboard</a>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
            <form action="{{ route('admin.ruangan.import') }}" method="POST" class="flex flex-wrap items-center gap-2" enctype="multipart/form-data">
                @csrf
                <label for="iCP" class="inline-flex h-10 cursor-pointer items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 hover:bg-emerald-100"><i class="ri-file-excel-2-line mr-1.5 text-base"></i><span id="importLabel">Import Ruangan</span></label>
                <input id="iCP" name="file" type="file" class="hidden" accept=".csv"/>
                <button class="hidden inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700" type="submit" id="btnImport">Import</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[700px] divide-y divide-gray-100" id="searchTable">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Client</th>
                            <th class="px-4 py-3 sm:px-5">Nama Ruangan</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @php $no = 1; @endphp
                        @forelse ($ruangan as $i)
                            <tr class="transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->kerjasama ? $i->kerjasama->client->name : 'Belum Ada Client' }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800 sm:px-5">{{ $i->nama_ruangan}}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-1.5">
                                        <x-btn-edit>{{ url('ruangan/' . $i->id . '/edit') }}</x-btn-edit>
                                        <form action="{{ url('ruangan/' . $i->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <x-btn-submit></x-btn-submit>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 sm:px-5">Data ruangan kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 sm:px-5">{{ $ruangan->links()}}</div>
        </section>
    </div>

    @push('scripts')
        <script>
            $(function() {
                $('#iCP').on('change', function() {
                    const hasValue = !!$(this).val();
                    $('#importLabel').text(hasValue ? 'Klik import' : 'Import Ruangan');
                    $('#btnImport').toggleClass('hidden', !hasValue);
                });
            });
        </script>
    @endpush
</x-admin-layout>
