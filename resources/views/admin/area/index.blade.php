<x-admin-layout :fullWidth="true">
    @section('title', 'Data Area')

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Area</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola area dan sub area pada masing-masing client.</p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex h-10 w-full items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 sm:w-72">
                        <i class="ri-search-2-line text-base text-gray-500"></i>
                        <input type="search" id="searchInput" class="w-full border-none bg-transparent text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none" placeholder="Cari client atau nama area..." />
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Dashboard</a>
                        <a href="{{ route('area.create') }}" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700"><i class="ri-add-line mr-1.5"></i>Area</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[700px] divide-y divide-gray-100" id="searchTable">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Client</th>
                            <th class="px-4 py-3 sm:px-5">Nama Area</th>
                            <th class="px-4 py-3 sm:px-5">Sub Area</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @php $no = 1; @endphp
                        @forelse ($area as $i)
                            <tr class="transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->kerjasama ? $i->kerjasama->client->name : 'Kosong' }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800 sm:px-5">{{ $i->nama_area }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    @forelse($i->subarea as $sub)
                                        <span class="mb-1 inline-block rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ $sub->name }}</span>
                                    @empty
                                        <a href="{{ route('edit.subarea', $i->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100"><i class="ri-add-circle-fill"></i>Tambah</a>
                                    @endforelse
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-1.5">
                                        <x-btn-edit>{{ url('area/' . $i->id . '/edit') }}</x-btn-edit>
                                        <form action="{{ url('area/' . $i->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-btn-submit></x-btn-submit>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 sm:px-5">Data area masih kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
