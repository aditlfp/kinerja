<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
            </div>
            <div class="rounded-2xl bg-white py-6 shadow-xs ring-1 ring-slate-900/5 sm:py-10">
                <p class="border-l-4 border-sky-500 text-center text-lg font-bold uppercase sm:text-2xl">
                List Karyawan,<br>
                {{ auth()->id() == 175 ? 'Semua Mitra' : (Auth::user()->kerjasama->client->panggilan ?: Auth::user()->kerjasama->client->name) }}
            </p>

            <div class="flex flex-col items-center mx-2 my-2 sm:justify-center justify-start">
                {{-- Top Controls --}}
                <div class="flex items-center justify-center sm:justify-between gap-2 w-full mt-5">
                    <div style="width: 75%;" class="mt-5">
                        <x-search />
                    </div>
                </div>

                {{-- Mitra Filter (visible only for user ID 175) --}}
                @if(Auth::user()->jabatan->code_jabatan == 'SPV-W')
                    <form action="" method="GET" class="flex justify-center items-end gap-1 bg-slate-100 rounded-sm w-full px-2 py-3 mb-5">
                        <div style="width: 66.66%;" class="w-2/3">
                            <label class="label text-xs sm:text-base">Pilih Mitra</label>
                            <select name="mitra" class="select select-bordered text-black select-sm text-xs w-full">
                                <option disabled selected>~Pilih Mitra~</option>
                                @forelse($mitra as $i)
                                    <option value="{{ $i->id }}" {{ $filterMitra == $i->id ? 'selected' : '' }}>{{ ucwords(strtolower($i->client->panggilan ?: $i->client->name)) }}</option>
                                @empty
                                    <option disabled>~Mitra Kosong~</option>
                                @endforelse
                            </select>
                        </div>
                        <div style="width: 33.33%;" class="w-1/3 flex items-end">
                            <button class="btn btn-primary btn-sm sm:btn-md w-full">Filter</button>
                        </div>
                    </form>
                @endif

                {{-- Table --}}
                <div class="table-scroll w-full max-w-full min-w-0">
                    <table id="searchTable" class="table table-xs table-zebra text-xs bg-slate-50 font-semibold sm:table-md sm:text-md" style="min-width: 900px;">
                        <thead>
                            <tr class="text-center">
                                <th class="p-1 py-2 bg-slate-300 rounded-tl-2xl">#</th>
                                <th class="p-1 py-2 bg-slate-300">Profil</th>
                                <th class="p-1 py-2 bg-slate-300">Username</th>
                                <th class="p-1 py-2 bg-slate-300">Nama Lengkap</th>
                                <th class="p-1 py-2 bg-slate-300">Jabatan</th>
                                <th class="p-1 py-2 bg-slate-300">Email</th>
                                <th class="p-1 py-2 bg-slate-300 rounded-tr-2xl">Penempatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $n = 1; @endphp
                            @forelse ($user as $i)
                                @continue(in_array($i->nama_lengkap, ['admin', 'user', 'MITRA SAC']))
                                <tr>
                                    <td class="p-1">{{ $n++ }}</td>
                                    <td>
                                        @if ($i->image !== 'no-image.jpg' && Storage::disk('public')->exists('images/' . $i->image))
                                            <img class="lazy lazy-image" loading="lazy"
                                                 src="{{ asset('storage/images/' . $i->image) }}"
                                                 data-src="{{ asset('storage/images/' . $i->image) }}"
                                                 alt="" width="120px">
                                        @else
                                            <x-no-img />
                                        @endif
                                    </td>
                                    <td class="p-1">{{ $i->name }}</td>
                                    <td class="p-1 wrap-break-word whitespace-pre-wrap">{{ ucwords(strtolower($i->nama_lengkap)) }}</td>
                                    <td class="p-1 wrap-break-word whitespace-pre-wrap" style="width: 90px">
                                        {{ $i->divisi->jabatan->code_jabatan ?? 'Jabatan Kosong ?' }}
                                    </td>
                                    <td class="p-1 wrap-break-word whitespace-pre-line">{{ $i->email }}</td>
                                    <td class="p-1 wrap-break-word whitespace-pre-line">
                                        {{ $i->kerjasama->client->panggilan ?: $i->kerjasama->client->name ?? 'Penempatan Kosong ?' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">~ Data Kosong ~</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="pag-1" class="mt-5 mb-5">
                    {{ $user->links() }}
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
