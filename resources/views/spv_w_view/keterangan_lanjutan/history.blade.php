<x-app-layout>
    @php
        $spvwClientId = request('client_id', session('spvw.selected_client_id'));
        $appendClient = static fn(string $url) => $spvwClientId
            ? $url . (str_contains($url, '?') ? '&' : '?') . 'client_id=' . $spvwClientId
            : $url;
    @endphp
    <x-main-div>
        <div class="w-full max-w-6xl px-3 py-4 mx-auto sm:px-5 lg:px-6">
            <div class="p-4 mb-4 bg-white border rounded-lg shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center min-w-0 gap-3">
                        <a href="{{ route('spvw.rekap.index', array_filter(['client_id' => $spvwClientId])) }}"
                            class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg shrink-0 sm:ml-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            aria-label="Kembali ke rekapitulasi">
                            <i class="text-xl ri-arrow-left-line"></i>
                        </a>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                            <h1 class="text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                                Riwayat Keterangan Lanjutan
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Riwayat keterangan lanjutan yang sudah tersimpan.
                            </p>
                        </div>
                    </div>
                    <a href="{{ $appendClient(route('spvw.keterangan-lanjutan.index', array_filter(['client_id' => $spvwClientId]))) }}"
                        class="items-center hidden gap-2 px-3 text-sm font-semibold transition bg-white border rounded-lg min-h-10 shrink-0 border-slate-200 text-slate-700 hover:bg-slate-50 sm:inline-flex">
                        <i class="ri-add-line"></i>
                        Pengajuan
                    </a>
                </div>
            </div>

            <div class="p-4 mb-4 bg-white border rounded-lg shadow-xs border-slate-200">
                <form action="{{ route('spvw.keterangan-lanjutan.history', array_filter(['client_id' => $spvwClientId])) }}" method="GET"
                    class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">User</label>
                        <select name="user_id"
                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">Semua User</option>
                            @foreach ($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Periode</label>
                        <input type="text" name="periode" value="{{ request('periode') }}" placeholder="Contoh: Mei 2026"
                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, periode, judul, atau isi"
                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </div>
                    <div class="flex gap-2 lg:self-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold text-white transition rounded-lg min-h-11 bg-sky-600 hover:bg-sky-700 lg:flex-none">
                            <i class="ri-filter-3-line"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('spvw.keterangan-lanjutan.history', array_filter(['client_id' => $spvwClientId])) }}"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-11 border-slate-300 text-slate-700 hover:bg-slate-50 lg:flex-none">
                            <i class="ri-refresh-line"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                    @if ($spvwClientId)
                        <input type="hidden" name="client_id" value="{{ $spvwClientId }}">
                    @endif
                </form>
            </div>

            <div class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <table class="w-full text-sm text-left">
                    <thead class="border-b bg-slate-50 border-slate-200 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase">User</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase">Keterangan</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($keteranganLanjutans as $item)
                            <tr class="align-top">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $item->user->nama_lengkap ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="space-y-2">
                                        @foreach (($item->keterangan ?? []) as $row)
                                            <div class="p-2 border rounded-md border-slate-200 bg-slate-50">
                                                <p class="text-xs text-slate-500">
                                                    {{ is_array($row) ? ($row['periode'] ?? '-') : '-' }}
                                                </p>
                                                <p class="text-xs font-semibold uppercase text-slate-500">
                                                    {{ is_array($row) ? ($row['judul'] ?? '-') : '-' }}
                                                </p>
                                                <p class="mt-1 text-sm text-slate-700">
                                                    {{ is_array($row) ? ($row['keterangan'] ?? '-') : $row }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $item->createdBy->nama_lengkap ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $keteranganLanjutans->links() }}
            </div>
        </div>
    </x-main-div>
</x-app-layout>
