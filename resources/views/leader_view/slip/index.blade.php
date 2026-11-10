<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
            </div>
            <div class="rounded-2xl bg-white py-6 shadow-xs ring-1 ring-slate-900/5 sm:py-10">
                <p class="border-l-4 border-sky-500 text-lg font-bold text-center uppercase sm:text-2xl">List Gaji Karyawan, <br>{{ auth()->user()->id == 175 ? "Semua Mitra" : Auth::user()->kerjasama->client->name }}</p>
            <div class="flex flex-col items-center justify-start mx-2 my-2 sm:justify-center">

                <div class="flex flex-col md:flex-row items-center justify-center w-full gap-2 mt-5 md:justify-between">
                    <div class="flex justify-end mt-5" style="min-width: 50%;">
                        <form action="{{ Auth::user()->kerjasama_id == 1 ? route('slip-karyawan') : (Auth::user()->jabatan_id == 11 ? route('danru-slip') : route('leader-slip')) }}" method="GET" class="flex flex-col gap-2 p-2 mb-5 rounded-sm bg-slate-100" style="">
                            @if(Auth::user()->kerjasama_id == 1)
                            <div style="width: 100%">
                                <label class="text-xs label">Pilih Mitra</label>
                                <select name="penempatan" class="w-full text-xs text-black select select-bordered select-sm">
                                    <option class="disabled" disabled selected>~Pilih Mitra~</option>
                                    @forelse($mitra as $i)
                                        @if(Auth::user()->devisi_id === 26 && $i->id !== 1)
                                        <option value="{{ $i->id }}" {{ $penempatan == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                                        @elseif(Auth::user()->devisi_id !== 26)
                                        <option value="{{ $i->id }}" {{ $penempatan == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                                        @endif
                                    @empty
                                        <option class="disabled">~Mitra Kosong~</option>
                                    @endforelse
                                </select>
                            </div>
                            @endif
                            <div class="join w-full">
                                <input type="month" name="bulan" value="{{ $bulan ? $bulan : Carbon\Carbon::now()->subMonth()->format('Y-m') }}" max="{{ Carbon\Carbon::now()->addMonth()->format('Y-m') }}" class="input input-sm input-bordered join-item text-xs" style="width: 75%;" />
                                <button type="submit" class="btn btn-sm btn-info join-item" style="width: 25%;" ><i class="ri-search-2-line"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-scroll w-full min-w-0 pb-10 sm:mx-10">
                    <table id="searchTable" class="table w-full min-w-[700px] text-xs font-semibold table-xs table-zebra sm:table-md bg-slate-50 sm:text-md">
                        <thead>
							<tr class="text-center">
                                <th class="p-1 py-2 bg-slate-300 rounded-tl-2xl">#</th>
                                <th class="p-1 py-2 bg-slate-300 text-start">Nama Lengkap</th>
                                <th class="p-1 py-2 bg-slate-300 text-start">Penempatan</th>
                                <th class="p-1 py-2 bg-slate-300">Bulan Tahun</th>
                                <th class="p-1 py-2 bg-slate-300">Penghasilan</th>
                                <th class="p-1 py-2 bg-slate-300">Potongan</th>
                                <th class="p-1 py-2 bg-slate-300 rounded-tr-2xl">Total Penerimaan</th>
							</tr>
						</thead>
                        <tbody>
                            @php
                                $n = 1;
                            @endphp
                            @forelse ($slip as $i)
                                @php
                                    $totalPenghasilan = $i?->gaji_pokok + $i?->gaji_lembur + $i?->tj_jabatan + $i?->tj_kehadiran + $i?->tj_kinerja + $i?->tj_lain;
                                    $totalPotongan = $i?->bpjs + $i?->pinjaman + $i?->absen + $i?->lain_lain;
                                    $totalBersih = 0;
                                    if($totalPotongan > 0){
                                        $totalBersih = $totalPenghasilan - $totalPotongan;
                                    }else{
                                        $totalBersih = $totalPenghasilan + $totalPotongan;
                                    }

                                    $mitraName = '';
                                    $name = $i?->user->kerjasama->client->name ?? '';
                                    $words = preg_split('/\s+/', $name);
                                    $prefixes = ['PT.', 'CV', 'MTS'];
                                    $locPrefix = ['Ponorogo', 'Madiun', 'Lamongan', 'Surabaya', 'babat', 'gresik', '(rusunawa)', 'ngabar', 'ngabar)'];

                                    $firstWord = $words[0] ?? '';
                                    $endWord = end($words) ?? '';

                                    $hasPrefix = in_array(strtoupper(rtrim($firstWord, '.')), array_map(fn($p) => rtrim($p, '.'), $prefixes));
                                    $hasEndPrefix = in_array(strtolower(rtrim($endWord, '.')), array_map(fn($p) => strtolower(rtrim($p, '.')), $locPrefix));

                                    $initials = collect($words)
                                        ->skip($hasPrefix ? 1 : 0)
                                        ->map(fn($word) => strtoupper(mb_substr(preg_replace('/[^A-Za-z]/u', '', $word), 0, 1)))
                                        ->implode('');
                                    $closing = collect($words)
                                        ->take($hasEndPrefix ? count($words) - 1 : 0)
                                        ->map(fn($word) => strtoupper(mb_substr(preg_replace('/[^A-Za-z]/u', '', $word), 0, 1)))
                                        ->implode('');
                                    $both = collect($words)
                                        ->skip(1)
                                        ->take(1)
                                        ->map(fn($word) => strtoupper(mb_substr(preg_replace('/[^A-Za-z]/u', '', $word), 0, 1)))
                                        ->implode('');

                                    if ($hasPrefix && $hasEndPrefix && count($words) > 2) {
                                        $mitraName = "$firstWord $both $endWord";
                                    } else if ($hasPrefix && !$hasEndPrefix && count($words) > 2) {
                                        $mitraName = "$firstWord $initials";
                                    } else if (!$hasPrefix && $hasEndPrefix && count($words) > 2) {
                                        $mitraName = "$closing $endWord";
                                    } else if (count($words) > 2) {
                                        $mitraName = $initials;
                                    } else {
                                        $mitraName = $name;
                                    }
                                @endphp
                                <tr class="text-center">
                                    <td>{{ $n++ }}.</td>
                                    <td class="text-start">{{ \Illuminate\Support\Str::title(Str::lower($i?->karyawan)) }}</td>
                                    <td class="text-start">{{ $mitraName }}</td>
                                    <td>{{ Carbon\Carbon::createFromFormat('Y-m', $i?->bulan_tahun)->isoFormat('MMMM Y') }}</td>
                                    <td>{{ toRupiah($i?->gaji_pokok + $i?->gaji_lembur + $i?->tj_jabatan + $i?->tj_kehadiran + $i?->tj_kinerja + $i?->tj_lain) }}</td>
                                    <td>{{ toRupiah($i?->bpjs + $i?->pinjaman + $i?->absen + $i?->lain_lain) }}</td>
                                    <td>{{ toRupiah($totalBersih) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        ~ Data Kosong ~
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
