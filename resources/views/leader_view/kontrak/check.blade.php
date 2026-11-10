<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
            </div>
            <div class="rounded-2xl bg-white py-6 shadow-xs ring-1 ring-slate-900/5 sm:py-10">
                <p class="border-l-4 border-sky-500 text-center text-lg font-bold uppercase sm:text-2xl">
                List Kontrak,<br>
                {{ count($kontrak) > 0 ? $kontrak[0]->unit_pk_kda : 'PT. Surya Amanah Cendikia' }}
            </p>

            <div class="flex flex-col items-center mx-2 my-2 sm:justify-center justify-start">
                {{-- Top Controls --}}
                <div class="flex items-center justify-center sm:justify-between gap-2 w-full mt-5">
                    <div style="width: 44.44%;">
                        <a href="{{ route('dashboard.index') }}" class="btn btn-error">Kembali</a>
                    </div>

                    <div style="width: 55.55%;" class="mt-5">
                        <x-search />
                    </div>
                </div>

                <form action="{{ route('direksi-cekKontrak') }}" method="GET"
                    class="flex flex-col justify-between gap-2 bg-slate-100 rounded-sm w-full px-2 py-3 mb-5">
                    <div>
                        <label class="label text-xs sm:text-base">Pilih Mitra</label>
                        <select name="mitra" class="select select-bordered text-black select-sm text-xs w-full">
                            <option disabled selected>~Pilih Mitra~</option>
                            @forelse($mitra as $i)
                                <option value="{{ $i->id }}" {{ $filterMitra == $i->id ? 'selected' : '' }}>
                                    {{ $i->client->name }}</option>
                            @empty
                                <option disabled>~Mitra Kosong~</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="flex items-end justify-end w-full">
                        <button class="btn btn-info btn-sm sm:btn-md" style="min-width: 100px;">Filter</button>
                    </div>
                </form>

                <div style="overflow-x: auto;" class="w-full">
                    @php
                        $validKontrakIDs = $kontrak
                            ->filter(fn($k) => $k->send_to_atasan == 1 && !$k->ttd_atasan && $k->ttd)
                            ->pluck('id');
                    @endphp
                    <form x-data="{
                        selected: [],
                        selectAll: false,
                        acc: false,
                        allIds: @json($validKontrakIDs),
                        toggleAll() {
                            this.selected.splice(0, this.selected.length); // clear existing selected
                            if (this.selectAll) {
                                this.selected.push(...this.allIds); // reaktif push
                                console.log(this.allIds);
                            }
                        }
                    }" method="POST" action="{{ route('direksi-accKontrak') }}"
                        style="overflow-x: auto;" class="overflow-x-auto mx-2">
                        @csrf
                        @method('PUT')
                        {{-- Table --}}

                        <div class="flex justify-end my-5 px-4 gap-2">
                            <template x-if="selectAll || selected.length > 0">
                                <div class="flex gap-2">
                                    <button type="submit" @click="acc = true"
                                        class="btn btn-success btn-sm sm:btn-md">Terima Terpilih</button>
                                    <button type="submit" @click="acc = false"
                                        class="btn btn-error btn-sm sm:btn-md">Tolak Terpilih</button>
                                </div>
                            </template>
                        </div>

                        {{-- HIDDEN INPUT untuk ID Kontrak terpilih --}}
                        <input type="hidden" name="acc" :value="acc">
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="kontrak_ids[]" :value="id">
                        </template>

                        <div class="overflow-x-auto w-full">
                            <table id="searchTable"
                                class="table table-xs table-zebra sm:table-md text-xs bg-slate-50 font-semibold sm:text-md">
                                <thead>
                                    <tr class="text-center">
                                        <th
                                            class="p-1 py-2 bg-slate-300 rounded-tl-2xl flex justify-center items-center gap-1">
                                            <template x-if="allIds.length > 0">
                                                <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                    class="checkbox checkbox-sm">
                                            </template>
                                            <label>Pilih</label>
                                        </th>
                                        <th class="p-1 py-2 bg-slate-300">Nama</th>
                                        <th class="p-1 py-2 bg-slate-300">Jabatan</th>
                                        <th class="p-1 py-2 bg-slate-300">Status</th>
                                        <th class="p-1 py-2 bg-slate-300" style="padding: 0 60px;">Waktu Kontrak</th>
                                        <th class="p-1 py-2 bg-slate-300" style="padding: 0 40px;">Gaji Pokok</th>
                                        <th class="p-1 py-2 bg-slate-300" style="padding: 0 25px;">Tj. Kehadiran</th>
                                        <th class="p-1 py-2 bg-slate-300" style="padding: 0 25px;">Tj. Kinerja</th>
                                        <th class="p-1 py-2 bg-slate-300" style="padding: 0 25px;">Lihat</th>
                                        <th class="p-1 py-2 bg-slate-300 rounded-tr-2xl">Terima / Tolak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($kontrak as $i)
                                        <tr>
                                            <td class="text-center">
                                                @if ($i->send_to_atasan == 1 && !$i->ttd_atasan && $i->ttd)
                                                    <input type="checkbox" :value="{{ $i->id }}"
                                                        x-model="selected" class="checkbox checkbox-sm">
                                                @endif
                                            </td>
                                            <td>{{ ucwords(strtolower($i->nama_pk_kda)) }}</td>
                                            <td>{{ $i->jabatan_pk_kda }}</td>
                                            <td>{{ $i->status_pk_kda }}</td>
                                            <td class="text-center">{{ $i->tgl_mulai_kontrak }} -
                                                {{ $i->tgl_selesai_kontrak }}</td>
                                            <td class="text-center">{{ toRupiah($i->g_pok) }}</td>
                                            <td class="text-center">{{ toRupiah($i->tj_hadir) }}</td>
                                            <td class="text-center">{{ toRupiah($i->kinerja) }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('form-kontrak-preview', ['token' => \Illuminate\Support\Facades\Crypt::encryptString($i->id)]) }}"
                                                    class="btn btn-sm btn-info text-white text-lg"><i
                                                        class="ri-eye-line"></i></a>
                                            </td>
                                            <td class="overflow-hidden text-center">
                                                @if ($i->send_to_atasan == 0 && $i->ttd_atasan && $i->ttd)
                                                    <p class="badge badge-success overflow-hidden">Acc</p>
                                                @elseif($i->send_to_atasan == 0 && !$i->ttd_atasan && $i->ttd)
                                                    <p class="badge badge-error overflow-hidden">Tolak</p>
                                                @else
                                                    <p class="badge badge-warning overflow-hidden w-[80px] py-3">
                                                        {{ $i->ttd ? 'Proses P1' : 'Proses' }}</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">~ Data Kosong ~</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                    </form>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
</x-app-layout>
