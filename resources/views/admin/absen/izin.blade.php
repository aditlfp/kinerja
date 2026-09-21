<x-admin-layout :fullWidth="true">
    @section('title', 'Absensi Izin')

    <div class="w-full max-w-screen-xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Absensi Izin</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola permintaan izin, persetujuan, dan penghapusan data izin.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari nama, mitra, alasan..." />
                    </label>
                </div>
            </div>

            <form action="{{ route('admin.export-izin') }}" method="GET" class="flex flex-wrap items-center gap-2 mt-4">
                @csrf
                <select name="kerjasama_id" id="filterKerjasama" class="h-10 min-w-[220px] rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-none">
                    <option selected disabled>~ Nama Klien ~</option>
                    @foreach ($kerja as $i)
                        <option value="{{ $i->id }}">{{ $i->client->name }}</option>
                    @endforeach
                </select>
                <button class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white rounded-xl bg-amber-500 hover:bg-amber-600"><i class="ri-file-download-line mr-1.5"></i>Export</button>
            </form>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[980px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Nama Lengkap</th>
                            <th class="px-4 py-3 sm:px-5">Shift</th>
                            <th class="px-4 py-3 sm:px-5">Mitra</th>
                            <th class="px-4 py-3 sm:px-5">Alasan Izin</th>
                            <th class="px-4 py-3 sm:px-10">Tanggal</th>
                            <th class="px-4 py-3 sm:px-5">Status</th>
                            <th class="px-4 py-3 text-center sm:px-5">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $no = 1; @endphp
                        @forelse ($izin as $i)
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 sm:px-5">{{ $no++ }}.</td>
                                <td class="px-4 py-3 sm:px-5" style="color: {{ $i->user ? 'inherit' : 'red' }}">{{ $i->user ? ucwords(strtolower($i->user->nama_lengkap)) : 'User Tidak Ditemukan' }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->shift?->shift_name }}</td>
                                <td class="px-4 py-3 sm:px-5" style="color: {{ $i->kerjasama ? 'inherit' : 'red' }}">{{ $i->kerjasama ? ($i->kerjasama->client->panggilan ?? $i->kerjasama->client->name ?? 'KOSONG') : 'KOSONG'}}</td>
                                <td class="px-4 py-3 sm:px-5 text-start line-clamp-2">{{ $i->alasan_izin }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    @if ($i->approve_status == 'process')
                                        <span class="rounded-md bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $i->approve_status }}</span>
                                    @elseif($i->approve_status == 'accept')
                                        <span class="rounded-md bg-emerald-700 px-2 py-0.5 text-xs font-semibold text-white">{{ $i->approve_status }}</span>
                                    @else
                                        <span class="rounded-md bg-red-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $i->approve_status }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    @if ($i->approve_status == 'process')
                                        <div class="flex items-center justify-center gap-1 text-center">
                                            <form action="{{ route('admin.izin.approve', $i->id) }}" method="POST">@csrf @method('PATCH')<button type="submit" class="btn btn-success btn-xs rounded-btn"><i class="ri-check-double-line"></i></button></form>
                                            <form action="{{ route('admin.izin.deny', $i->id) }}" method="POST">@csrf @method('PATCH')<button type="submit" class="btn btn-error btn-xs rounded-btn"><i class="ri-close-line"></i></button></form>
                                            <a href="{{ route('izin.show', $i->id) }}" class="text-xl transition text-sky-400 hover:text-sky-500"><i class="ri-eye-fill"></i></a>
                                            <form action="{{ route('admin.izin.destroy', $i->id) }}" method="POST">@csrf @method('DELETE')<button class="text-xl text-red-400 transition hover:text-red-500"><i class="ri-delete-bin-5-line"></i></button></form>
                                        </div>
                                    @else
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('izin.show', $i->id) }}" class="text-xl transition text-sky-400 hover:text-sky-500"><i class="ri-eye-fill"></i></a>
                                            <form action="{{ route('admin.izin.destroy', $i->id) }}" method="POST">@csrf @method('DELETE')<button class="text-xl text-red-400 transition hover:text-red-500"><i class="ri-delete-bin-5-line"></i></button></form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data izin kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">{{ $izin->links()}}</div>
        </section>
    </div>
</x-admin-layout>
