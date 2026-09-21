<x-admin-layout :fullWidth="true">
    @section('title', 'Data Jabatan')

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Jabatan</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola struktur jabatan dan keterkaitannya dengan divisi.</p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex h-10 w-full items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 sm:w-72">
                        <i class="ri-search-2-line text-base text-gray-500"></i>
                        <input type="search" id="searchInput" class="w-full border-none bg-transparent text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none" placeholder="Cari kode, divisi, tipe, nama..." />
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Dashboard</a>
                        <a href="{{ route('admin.jabatan.create') }}" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                            <i class="ri-add-line mr-1.5 text-base"></i> Jabatan
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-4 py-3 text-xs text-gray-500 sm:px-5">
                <span class="rounded-full bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">Total: {{ $jabatan->total() }}</span>
                <span>Data jabatan aktif.</span>
            </div>
            <div class="w-full max-w-full overflow-x-auto">
                <table class="w-full min-w-[620px] divide-y divide-gray-100" id="searchTable">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Kode</th>
                            <th class="px-4 py-3 sm:px-5">Divisi</th>
                            <th class="hidden px-4 py-3 md:table-cell sm:px-5">Tipe</th>
                            <th class="hidden px-4 py-3 lg:table-cell sm:px-5">Nama Jabatan</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @php $no = 1; @endphp
                        @forelse ($jabatan as $i)
                            <tr class="transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="font-semibold text-gray-800">{{ $i->code_jabatan }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 md:hidden">{{ $i->type_jabatan }}</p>
                                    <p class="text-xs text-gray-500 lg:hidden">{{ $i->name_jabatan }}</p>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    @if ($i->divisi_id == null)
                                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">Belum Diisi</span>
                                    @else
                                        {{ $i->divisi->name }}
                                    @endif
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell sm:px-5">{{ $i->type_jabatan }}</td>
                                <td class="hidden px-4 py-3 lg:table-cell sm:px-5">{{ $i->name_jabatan }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-1.5">
                                        <x-btn-edit>{{ route('admin.jabatan.edit', [$i->id]) }}</x-btn-edit>
                                        <form action="{{ route('admin.jabatan.destroy', [$i->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <x-btn-submit />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 sm:px-5">Data jabatan masih kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-4 py-3 sm:px-5">
                {{ $jabatan->links() }}
            </div>
        </section>
    </div>
</x-admin-layout>
