<x-admin-layout :fullWidth="true">
    @section('title', 'Data Client')

    <div x-data="{ delOpen: false, deleteId: null, deleteName: '' }" class="w-full px-2 mx-auto space-y-4 max-w-screen-2xl sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Client</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola profil client dan informasi alamat kontak perusahaan.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari nama, email, alamat..." />
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.client.create') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
                            <i class="ri-add-line mr-1.5 text-base"></i> Tambah Client
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="flex flex-wrap items-center gap-2 px-4 py-3 text-xs text-gray-500 border-b border-gray-100 sm:px-5">
                <span class="rounded-full bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">Total: {{ $client->total() }}</span>
                <span>Data client aktif.</span>
            </div>
            <div class="w-full max-w-full overflow-x-auto">
                <table class="w-full min-w-[980px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Logo</th>
                            <th class="px-4 py-3 sm:px-5">Client</th>
                            <th class="hidden px-4 py-3 lg:table-cell sm:px-5">Alamat</th>
                            <th class="hidden px-4 py-3 xl:table-cell sm:px-5">Provinsi</th>
                            <th class="hidden px-4 py-3 xl:table-cell sm:px-5">Kabupaten</th>
                            <th class="px-4 py-3 sm:px-5">Kontak</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $no = 1; @endphp
                        @forelse ($client as $i)
                            <tr class="align-top transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    @if ($i->logo == 'no-image.jpg')
                                        <x-no-img />
                                    @elseif(Storage::disk('public')->exists('images/' . $i->logo))
                                        <img class="object-cover w-16 h-10 rounded-md" loading="lazy" src="{{ asset('storage/images/' . $i->logo) }}" alt="{{ $i->name }}">
                                    @else
                                        <x-no-img />
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="font-semibold text-gray-800">{{ $i->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $i->email }}</p>
                                    <p class="text-xs text-gray-500 md:hidden">{{ $i->phone }}</p>
                                </td>
                                <td class="hidden px-4 py-3 lg:table-cell sm:px-5">{{ $i->address }}</td>
                                <td class="hidden px-4 py-3 xl:table-cell sm:px-5">{{ $i->province }}</td>
                                <td class="hidden px-4 py-3 xl:table-cell sm:px-5">{{ $i->kabupaten }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="break-all">{{ $i->email }}</p>
                                    <p class="text-xs text-gray-500">{{ $i->phone }}</p>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ url('client/data-client/' . $i->id . '/edit') }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100"
                                        >
                                            <i class="text-xs ri-edit-line"></i>
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            @click="delOpen = true; deleteId = {{ $i->id }}; deleteName = '{{ addslashes($i->name) }}'"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 text-[11px] font-semibold text-red-700 transition hover:bg-red-100"
                                        >
                                            <i class="text-xs ri-delete-bin-6-line"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data client masih kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-100 sm:px-5">
                {{ $client->links() }}
            </div>
        </section>

        <div x-show="delOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/35 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden bg-white shadow-xl rounded-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Konfirmasi Hapus Client</h3>
                    <button @click="delOpen = false" type="button" class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50">Tutup</button>
                </div>
                <form :action="`{{ url('client/data-client') }}/${deleteId}`" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus client <span class="font-semibold text-gray-800" x-text="deleteName"></span>?</p>
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
