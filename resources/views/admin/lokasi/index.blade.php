<x-admin-layout :fullWidth="true">
    @section('title', 'Data Lokasi')

    <div class="w-full max-w-screen-xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Lokasi</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola titik koordinat dan radius lokasi client.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari client atau koordinat..." />
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.lokasi.create') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700"><i class="ri-add-line mr-1.5"></i>Tambah Lokasi</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[760px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Logo</th>
                            <th class="px-4 py-3 sm:px-5">Client</th>
                            <th class="hidden px-4 py-3 md:table-cell sm:px-5">Latitude</th>
                            <th class="hidden px-4 py-3 md:table-cell sm:px-5">Longitude</th>
                            <th class="px-4 py-3 sm:px-5">Radius</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $n = 1; @endphp
                        @forelse ($lokasi as $i)
                            <tr class="transition-colors hover:bg-blue-50/40 {{ $i->client ?? 'bg-red-500/30' }}">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $n++ }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    @if ($i->client?->logo == 'no-image.jpg')
                                        <x-no-img />
                                    @elseif($i->client?->logo != asset('storage/images/' . $i->client?->logo))
                                        <img src="{{ asset('storage/images/' . $i->client?->logo) }}" alt="logo" class="object-cover w-16 h-10 rounded-md">
                                    @else
                                        <x-no-img />
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="font-semibold {{ $i->client ? 'text-gray-800' : 'text-red-500' }}">{{ $i->client ? ($i->client?->panggilan ?? $i->client?->name) : 'Tidak ada client' }}</p>
                                    <p class="text-xs text-gray-500 md:hidden">{{ $i->latitude }}, {{ $i->longtitude }}</p>
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell sm:px-5">{{ $i->latitude }}</td>
                                <td class="hidden px-4 py-3 md:table-cell sm:px-5">{{ $i->longtitude }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->radius }} Meter</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ url('lokasi/' . $i->id . '/edit') }}" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100">
                                            <i class="text-base ri-edit-line"></i> Edit
                                        </a>
                                        <form action="{{ url('lokasi/' . $i->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus lokasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 text-[11px] font-semibold text-red-700 transition hover:bg-red-100">
                                                <i class="text-base ri-delete-bin-line"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data lokasi masih kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 sm:px-5">{{ $lokasi->links() }}</div>
        </section>
    </div>
</x-admin-layout>
