<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
            </div>
            <div class="p-4 mb-4 bg-white border rounded-2xl shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center min-w-0 gap-3">
                        <a href="{{ route('index.rekap.data.leader') }}"
                            class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg shrink-0 sm:ml-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            aria-label="Kembali ke rekapitulasi">
                            <i class="text-xl ri-arrow-left-line"></i>
                        </a>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                            <h1 class="border-l-4 border-sky-500 pl-3 text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                                Riwayat Keterangan Lanjutan
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Riwayat keterangan lanjutan yang sudah tersimpan.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('keterangan-lanjutan.index') }}"
                        class="items-center hidden gap-2 px-3 text-sm font-semibold transition bg-white border rounded-lg min-h-10 shrink-0 border-slate-200 text-slate-700 hover:bg-slate-50 sm:inline-flex">
                        <i class="ri-add-line"></i>
                        Pengajuan
                    </a>
                </div>
            </div>

            <div class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <table class="w-full text-sm text-left">
                    <thead class="border-b bg-slate-50 border-slate-200 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-xs font-semibold uppercase">User</th>
                            <th class="px-3 py-2 text-xs font-semibold uppercase">Keterangan</th>
                            <th class="px-3 py-2 text-xs font-semibold uppercase">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($keteranganLanjutans as $item)
                            <tr class="align-top">
                                <td class="px-3 py-2 font-medium text-slate-800">{{ $item->user->nama_lengkap ?? '-' }}</td>
                                <td class="px-3 py-2">
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
                                <td class="px-3 py-2 text-slate-700">
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
    </div>
</x-app-layout>
