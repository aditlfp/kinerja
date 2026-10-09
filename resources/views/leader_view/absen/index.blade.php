<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
            </div>
            <div class="rounded-2xl bg-white py-6 shadow-xs ring-1 ring-slate-900/5 sm:py-10">
                <p class="border-l-4 border-sky-500 text-lg font-bold text-center uppercase sm:text-2xl">Riwayat Absensi, <br>{{ auth()->user()->kerjasama->client->name }}</p>
            <div class="flex flex-col items-center justify-start mx-2 my-2 sm:justify-center">
                <div class="flex items-center justify-center my-5 sm:justify-between">
                    <div class="flex flex-col items-center justify-center w-full gap-2 sm:flex-row sm:gap-5 sm:justify-between">
                        <div class="w-full p-4 mx-5 rounded-md bg-slate-100">
                            @php
                                $isMitra = Auth::user()->divisi->jabatan->code_jabatan === 'MITRA';
                                $isCoScr = Auth::user()->divisi->jabatan->code_jabatan === 'CO-SCR';
                                $isDireksi = Auth::user()->devisi_id == 18;
                                $isSpvW = Auth::user()->jabatan->code_jabatan == 'SPV-W';

                                if ($isDireksi) {
                                    $now = Carbon\Carbon::now()->format('Y-m-d');
                                } else {
                                    $now = Carbon\Carbon::now()->format('Y-m');
                                }
                                $defaultMonth = $filter ?? $now;
                            @endphp

                            @if($isMitra)
                                <form action="{{ route('mitra_absensi') }}" method="GET" class="sm:flex sm:justify-start">
                                    <div class="join sm:ml-10">
                                        <input type="month" name="search" id="search" value="{{ $defaultMonth }}" max="{{ $now }}" placeholder="pilih bulan..." class="join-item input input-bordered" />
                                        <button type="submit" class="btn btn-info join-item">FILTER</button>
                                    </div>
                                </form>

                            @elseif($isCoScr)
                                <form action="{{ route('danru_absensi') }}" method="GET" class="sm:flex sm:justify-start">
                                    <div class="w-full join sm:ml-10">
                                        <div style="width: 75%;" class="flex flex-col w-3/4 join-item">
                                            <input type="month" name="search" id="search" value="{{ $defaultMonth }}" max="{{ $now }}" placeholder="pilih bulan..." class="w-full text-sm rounded-none input input-sm input-bordered" />
                                            @if(Auth::user()->jabatan->code_jabatan == 'SPV-W')
                                                <select name="mitra" class="w-full text-xs text-black rounded-none select select-bordered select-sm">
                                                    <option disabled selected>~Pilih Mitra~</option>
                                                    @forelse($mitra as $i)
                                                        <option value="{{ $i->id }}" {{ $filterMitra == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                                                    @empty
                                                        <option disabled>~Mitra Kosong~</option>
                                                    @endforelse
                                                </select>
                                            @endif
                                        </div>
                                        <div style="width: 25%;" class="w-1/4 join-item">
                                            <button type="submit" class="w-full h-full rounded-none btn btn-info">FILTER</button>
                                        </div>
                                    </div>
                                </form>

                            @elseif($isDireksi)
                                <form action="{{ route('direksi_absensi') }}" method="GET" class="w-full gap-2 sm:flex sm:justify-start">
                                    <div class="flex flex-col items-center justify-center w-full gap-2">
                                        <select name="mitra" class="w-full text-xs text-black select select-bordered">
                                            <option disabled selected>~Pilih Mitra~</option>
                                            @forelse($mitra as $i)
                                                <option value="{{ $i->id }}" {{ $filterMitra == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                                            @empty
                                                <option disabled>~Mitra Kosong~</option>
                                            @endforelse
                                        </select>
                                        <input type="date" name="search" id="search" value="{{ $defaultMonth }}" max="{{ $now }}" placeholder="pilih bulan..." class="w-full text-sm input input-bordered" />
                                    </div>
                                    <div class="flex justify-end mt-2">
                                        <button type="submit" class="w-full h-full btn btn-info">FILTER</button>
                                    </div>
                                </form>
                            @elseif($isSpvW)
                                <form method="GET" class="flex flex-wrap items-end gap-3 p-4 bg-base-100 rounded-xl shadow-xs border border-base-200">
                                    <div class="form-control w-full sm:w-auto">
                                        <label class="label pb-1"><span class="label-text text-xs font-semibold">Bulan</span></label>
                                        <input type="month" name="search" id="search" value="{{ $defaultMonth }}" max="{{ $now }}"
                                            class="input input-bordered input-sm focus:input-primary text-xs" />
                                    </div>

                                    <div class="form-control w-full sm:w-48">
                                        <label class="label pb-1"><span class="label-text text-xs font-semibold">Mitra</span></label>
                                        <select name="mitra" class="select select-bordered select-sm focus:select-primary text-xs">
                                            <option disabled selected>Pilih Mitra</option>
                                            @forelse($mitra as $i)
                                                <option value="{{ $i->id }}" {{ $filterMitra == $i->id ? 'selected' : '' }}>
                                                    {{ ucwords(strtolower($i->client->panggilan ?: $i->client->name)) }}
                                                </option>
                                            @empty
                                                <option disabled>Mitra Kosong</option>
                                            @endforelse
                                        </select>
                                    </div>

                                    <div class="form-control w-full sm:w-48">
                                        <label class="label pb-1"><span class="label-text text-xs font-semibold">User</span></label>
                                        <select name="user" id="user" {{ $filterMitra ? '' : 'disabled' }}
                                                class="select select-bordered select-sm focus:select-primary text-xs">
                                            <option disabled selected>Pilih User</option>
                                            @forelse($users as $u)
                                                <option value="{{ $u->id }}" {{ request('user') == $u->id ? 'selected' : '' }}>
                                                    {{ ucwords(strtolower($u->nama_lengkap)) }}
                                                </option>
                                            @empty
                                                <option disabled>User Kosong</option>
                                            @endforelse
                                        </select>
                                    </div>

                                    <div class="w-full sm:w-auto">
                                        <button type="submit" class="btn btn-info btn-sm w-full px-8 text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                            </svg>
                                            FILTER
                                        </button>
                                    </div>
                                </form>
                            @else
                                <form action="{{ url('LEADER/leader-absensi') }}" method="GET" class="sm:flex sm:justify-start">
                                    <div class="join sm:ml-10 min-w-full">
                                        <input type="month" name="search" id="search" value="{{ $defaultMonth }}" max="{{ $now }}" placeholder="pilih bulan..." class="text-sm join-item input input-bordered" />
                                        <button type="submit" class="btn btn-info join-item">FILTER</button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        @if (!$isSpvW)
                            <div class="flex items-center mt-5">
                                <x-search />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="w-full mx-2 overflow-x-auto md:overflow-hidden sm:mx-10">
                    <table id="searchTable" class="table text-xs font-semibold table-xs table-zebra sm:table-md bg-slate-50 sm:text-md ">
                        <thead>
							<tr class="text-center">
								<th class="p-1 py-2 bg-slate-300 rounded-tl-2xl px-8">Foto</th>
                                <th class="p-1 py-2 bg-slate-300">Nama</th>
								<th class="p-1 py-2 bg-slate-300 px-12">Shift</th>
								@if(auth()->user()->id == 175)
								<th class="p-1 py-2 bg-slate-300">Penempatan</th>
								@endif
								<th class="p-1 py-2 bg-slate-300 px-6">Tanggal</th>
								<th class="p-1 px-5 py-2 bg-slate-300">Masuk - pulang</th>
								@if(Auth::user()->devisi_id == 8)
								    <th class="p-1 py-2 bg-slate-300">Keterangan</th>
								    <th class="p-1 py-2 bg-slate-300 rounded-tr-2xl">Lokasi Absen</th>
								@else
								    <th class="p-1 py-2 bg-slate-300 rounded-tr-2xl">Keterangan</th>
								@endif
							</tr>
						</thead>
                        <tbody>
                            @forelse ($absen as $i)
                            <tr>
                                @if ($i->image == 'no-image.jpg')
									<td>
										<x-no-img />
									</td>
								@elseif(Storage::disk('public')->exists('images/' . $i->image))
									<td><img  class="rounded-md lazy lazy-image" loading="lazy" src="{{ asset('storage/images/' . $i->image) }}" data-src="{{ asset('storage/images/' . $i->image) }}" alt="" srcset="" width="120px"></td>
								@else
								    <td>
										<x-no-img />
									</td>
								@endif
                                <td class="p-1 wrap-break-word whitespace-pre-wrap line-clamp-3">{{ ucwords(strtolower($i->user->nama_lengkap)) }}</td>
                                <td class="p-1 text-center">{{ $i->shift?->shift_name }}, <br> {{ Carbon\Carbon::parse($i->shift?->jam_start)->format('H:i') }} - {{ Carbon\Carbon::parse($i->shift?->jam_end)->subHour(1)->format('H:i') }}</td>
                                @if(auth()->user()->id == 175)
								<td class="p-1 text-center">
								    {{ $i->kerjasama->client->panggilan ?: $i->kerjasama->client->name }}
								</td>
								@endif
                                <td class="p-1 text-center"><p>{{ $i->tanggal_absen }}</p></td>
                                <td class="p-1 ">
                                    <span class="flex flex-col justify-center text-center">
                                        <span>{{ Carbon\Carbon::parse($i->absensi_type_masuk)->format('H:i') }}</span>
                                        <span> - </span>
                                        @if($i->absensi_type_pulang == null)
                                            <span class="font-semibold text-red-500 capitalize">kosong</span>
                                        @elseif($i->absensi_type_pulang == 'Tidak Absen Pulang')
                                            <span class="font-semibold text-yellow-500 capitalize">{{ Carbon\Carbon::parse($i->absensi_type_pulang)->format('H:i') }}</span>
                                        @else
                                            {{ Carbon\Carbon::parse($i->absensi_type_pulang)->format('H:i') }}
                                        @endif
                                    </span>
                                </td>
                                @php
                                    $jamAbs = $i->absensi_type_masuk ?? '00:00:00';
                                    $jamStr = $i->shift?->jam_start ?? '00:00:00';

                                    // Determine time format
                                    $formatAbs = strlen($jamAbs) === 5 ? 'H:i' : 'H:i:s';
                                    $formatStr = strlen($jamStr) === 5 ? 'H:i' : 'H:i:s';

                                    // Parse times using Carbon
                                    $jAbs = Carbon\Carbon::createFromFormat($formatAbs, $jamAbs);
                                    $jJad = Carbon\Carbon::createFromFormat($formatStr, $jamStr);

                                    // Calculate the difference
                                    $jDiff = $jAbs->diff($jJad);

                                    // Build readable difference
                                    $diffHasil = '';
                                    if ($jDiff->h > 0) {
                                        $diffHasil .= $jDiff->format('%h Jam ');
                                    }
                                    if ($jDiff->i > 0) {
                                        $diffHasil .= $jDiff->format('%i Menit ');
                                    }
                                    if ($jDiff->s > 0) {
                                        $diffHasil .= $jDiff->format('%s Detik');
                                    }

                                    $diffHasil = trim($diffHasil);
                                @endphp

                                @if(Auth::user()->devisi_id == 8)
                                    <td>
                                        <div class="flex items-center justify-center">
                                            @if ($i->keterangan == 'masuk')
                                            <span class="gap-2 overflow-hidden badge badge-success">{{ $i->keterangan }}</span>
                                            @elseif($i->keterangan == 'telat')
                                            <span class="gap-2 overflow-hidden text-xs text-center rounded-md badge badge-error" style="height: 50pt; width: 60pt;">Terlambat,<br>{{ $diffHasil }}</span>
                                            @else
                                            <span class="gap-2 overflow-hidden badge badge-warning">{{ $i->keterangan }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="overflow-hidden">
                                        <a href="{{ route('mitra-lihatMap', $i->id) }}" class="overflow-hidden text-xs btn btn-sm btn-info">Lihat Koordinat</a>
                                    </td>
                                @else
                                <td>
                                    <div class="flex items-center justify-center">
                                        @if ($i->keterangan == 'masuk')
                                        <span class="gap-2 overflow-hidden badge badge-success">{{ $i->keterangan }}</span>
                                        @elseif($i->keterangan == 'telat')
                                        <span class="gap-2 overflow-hidden text-xs text-center rounded-md badge badge-error" style="height: 50pt; width: 60pt;">{{ $i->keterangan }},<br>{{ $diffHasil }}</span>
                                        @else
                                        <span class="gap-2 overflow-hidden badge badge-warning">{{ $i->keterangan }}</span>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="7">
                                        ~ Data Kosong ~
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="pag-1" class="mx-10 my-2">
                    {{ $absen->links() }}
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
