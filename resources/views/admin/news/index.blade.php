<x-admin-layout :fullWidth="true">
    @section('title', 'Data News')

    <div class="w-full max-w-screen-xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data News SAC</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola berita aktif untuk ditampilkan ke user.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari berita..." />
                    </label>
                    <a href="{{ route('admin.news.create') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700"><i class="ri-add-line mr-1.5"></i>Tambah Berita</a>
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[680px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Foto Berita</th>
                            <th class="px-4 py-3 sm:px-5">Tanggal Berlaku</th>
                            <th class="px-4 py-3 sm:px-5">Muncul Tanggal</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $no = 1; @endphp
                        @forelse ($news as $n)
                            <tr class="transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    @if ($n->image == 'no-image.jpg')
                                        <x-no-img />
                                    @else
                                        <img src="{{ asset('storage/images/' . $n->image) }}" alt="news" width="100" class="object-cover w-24 rounded-md h-14" loading="lazy">
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-5">{{ $n->tanggal_lihat }} - {{ $n->tanggal_tutup }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex max-w-xs flex-wrap gap-1">
                                        @foreach (($n->tanggal_muncul ?? []) as $tanggal)
                                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg bg-blue-50 px-2 text-xs font-semibold text-blue-700">{{ $tanggal }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-1.5">
                                        <a
                                            href="{{ route('admin.news.edit', $n->id) }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                                        >
                                            <i class="ri-edit-line text-xs"></i>
                                            Edit
                                        </a>
                                        <form action="{{ url('news/' . $n->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 text-xs font-semibold text-red-700 transition hover:bg-red-100"
                                            >
                                                <i class="ri-delete-bin-6-line text-xs"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Berita saat ini kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
