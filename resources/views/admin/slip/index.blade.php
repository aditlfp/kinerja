<x-admin-layout :fullWidth="true">
    @section('title', 'Slip Gaji')

    <div class="w-full px-2 py-6 mx-auto space-y-4 max-w-screen-2xl sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">List Gaji Karyawan</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $penempatan ? $mitra->firstWhere('id', $penempatan)->client->name : 'Semua Mitra' }},
                        {{ $bulan ? Carbon\Carbon::parse($bulan)->isoFormat('MMMM Y') : Carbon\Carbon::now()->subMonth()->isoFormat('MMMM Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.index') }}" class="inline-flex h-10 items-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">Kembali</a>
            </div>
        </section>

        <section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
            <form method="GET" class="grid gap-3 md:grid-cols-12">
                <div class="md:col-span-6">
                    <label for="penempatan" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Pilih Mitra</label>
                    <select id="penempatan" name="penempatan" class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                        <option disabled selected>~ Pilih Mitra ~</option>
                        <option value="semua" selected>Semua Mitra</option>
                        @forelse($mitra as $i)
                            <option value="{{ $i->id }}" {{ $penempatan == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                        @empty
                            <option disabled>~ Mitra Kosong ~</option>
                        @endforelse
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label for="bulan" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bulan</label>
                    <input id="bulan" type="month" name="bulan" value="{{ $bulan ? $bulan : Carbon\Carbon::now()->subMonth()->format('Y-m') }}" max="{{ Carbon\Carbon::now()->addMonth()->format('Y-m') }}" class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" />
                </div>
                <div class="flex items-end md:col-span-2">
                    <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="ri-search-2-line mr-1.5"></i> Filter
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="w-full overflow-x-auto">
                <table id="searchTable" class="w-full min-w-[1280px] divide-y divide-gray-100 text-xs">
                    <thead class="text-xs font-semibold tracking-wide text-center text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-2 py-3 text-center">#</th>
                            <th class="px-2 py-3 text-left">Nama Lengkap</th>
                            <th class="px-2 py-3 text-center">Gj. Pokok</th>
                            <th class="px-2 py-3 text-center">Gj. Lembur</th>
                            <th class="px-2 py-3 text-center">Tj. Jabatan</th>
                            <th class="px-2 py-3 text-center">Tj. Kehadiran</th>
                            <th class="px-2 py-3 text-center">Tj. Kinerja</th>
                            <th class="px-2 py-3 text-center">Tj. Lain-Lain</th>
                            <th class="px-2 py-3 text-center">Pot. Bpjs</th>
                            <th class="px-2 py-3 text-center">Pot. Pinjaman</th>
                            <th class="px-2 py-3 text-center">Pot. Absen</th>
                            <th class="px-2 py-3 text-center">Pot. Lain-Lain</th>
                            <th class="px-2 py-3 text-center">Penghasilan</th>
                            <th class="px-2 py-3 text-center">Potongan</th>
                            <th class="px-2 py-3 text-center">Total Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @php $n = 1; @endphp
                        @forelse ($slip as $i)
                            @php
                                $totalPenghasilan = $i?->gaji_pokok + $i?->gaji_lembur + $i?->tj_jabatan + $i?->tj_kehadiran + $i?->tj_kinerja + $i?->tj_lain;
                                $totalPotongan = $i?->bpjs + $i?->pinjaman + $i?->absen + $i?->lain_lain;
                                $totalBersih = 0;
                                if ($totalPotongan > 0) {
                                    $totalBersih = $totalPenghasilan - $totalPotongan;
                                } else {
                                    $totalBersih = $totalPenghasilan + $totalPotongan;
                                }
                            @endphp
                            <tr class="text-center transition-colors hover:bg-blue-50/40">
                                <td class="px-2 py-3">{{ $n++ }}.</td>
                                <td class="px-2 py-3 text-left">{{ $i?->karyawan }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->gaji_pokok ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->gaji_lembur ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->tj_jabatan ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->tj_kehadiran ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->tj_kinerja ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->tj_lain ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->bpjs ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->pinjaman ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->absen ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($i->lain_lain ?: '-') }}</td>
                                <td class="px-2 py-3">{{ toRupiah($totalPenghasilan) }}</td>
                                <td class="px-2 py-3">{{ toRupiah($totalPotongan) }}</td>
                                <td class="px-2 py-3 font-semibold text-gray-900">{{ toRupiah($totalBersih) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-4 py-8 text-sm text-center text-gray-500">Data gaji kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
