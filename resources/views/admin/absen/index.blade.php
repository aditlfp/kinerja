<x-admin-layout :fullWidth="true">
    @section('title', 'Data Absensi')
        <div class="w-full px-2 py-4 mx-auto space-y-4 max-w-[90vw] sm:px-3 lg:px-4">
            <section class="p-4 bg-white border border-gray-100 shadow-xs rounded-2xl sm:p-5">
                <p class="text-2xl font-bold tracking-tight text-gray-900">Data Absensi</p>
                <p class="mt-1 text-sm text-gray-600">Kelola absensi harian, filter, export, dan validasi data.</p>
            </section>

            <section class="p-4 bg-white border border-gray-100 shadow-xs rounded-2xl sm:p-5">
                <div class="flex flex-col gap-3">
                    <form id="filterForm" action="{{ route('admin.absen') }}" method="GET" data-user-search-url="{{ route('admin.absen.users.search') }}" class="grid w-full gap-2 sm:grid-cols-2 xl:max-w-6xl xl:grid-cols-5">
                        <select name="filterKerjasama" id="filterKerjasama" class="w-full text-sm select select-bordered">
                            <option value="" {{ $filter ? '' : 'selected' }}>~ Nama Klien ~</option>
                            @foreach ($absenSi as $i)
                                <option value="{{ $i->id }}" {{ $filter == $i->id ? 'selected' : '' }}>{{ $i?->client?->panggilan ?? $i?->client?->name }}</option>
                            @endforeach
                        </select>
                        <select name="filterDevisi" id="filterDevisi" class="w-full text-sm select select-bordered">
                            <option value="" {{ $filterDivisi ? '' : 'selected' }}>~ Devisi ~</option>
                            @foreach ($divisi as $i)
                                <option value="{{ $i->id }}" {{ $filterDivisi == $i->id ? 'selected' : '' }}>{{ $i?->name }}</option>
                            @endforeach
                        </select>
                        <div id="searchUserWrapper" class="{{ $filter ? '' : 'hidden' }}">
                            <div class="relative">
                                <input
                                    type="hidden"
                                    name="searchUserId"
                                    id="searchUserId"
                                    value="{{ $searchUserId }}">
                                <input
                                    type="text"
                                    name="searchUser"
                                    id="searchUser"
                                    value="{{ $searchUser }}"
                                    autocomplete="off"
                                    class="w-full pr-10 text-sm input input-bordered"
                                    placeholder="Cari user di klien ini..." />
                                <span class="absolute text-gray-400 -translate-y-1/2 pointer-events-none right-3 top-1/2">
                                    <i class="ri-search-line"></i>
                                </span>
                                <div id="searchUserResults" class="absolute left-0 right-0 z-20 hidden mt-1 overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
            </section>

            <section class="p-4 bg-white border border-gray-100 shadow-xs rounded-2xl sm:p-5">
                <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6 xl:items-end">
                    <select name="kerjasama_id" id="selectInput" class="h-10 px-3 text-sm bg-white border border-gray-300 shadow-xs rounded-xl">
                        <option disabled selected>Pilih Mitra</option>
                        @forelse ($absenSi as $i)
                            <option value="{{ $i->id }}">{{ $i?->client?->panggilan ?? $i?->client?->name }}</option>
                        @empty
                            <option>~Kosong~</option>
                        @endforelse
                    </select>
                    <input type="date" name="str1" id="str1" class="h-10 px-3 text-sm bg-white border border-gray-300 shadow-xs rounded-xl">
                    <input type="date" name="end1" id="end1" class="h-10 px-3 text-sm bg-white border border-gray-300 shadow-xs rounded-xl">
                    <select name="divisi_id" class="h-10 px-3 text-sm bg-white border border-gray-300 shadow-xs rounded-xl">
                        <option disabled selected>Pilih Divisi</option>
                        @forelse($divisi as $i)
                            <option value="{{ $i->id }}">{{ $i->name }}</option>
                        @empty
                            <option>~Kosong~</option>
                        @endforelse
                    </select>
                    <div class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-xl bg-gray-50">
                        <input type="checkbox" name="jadwal" id="jadwal" value="1" class="checkbox">
                        <label for="jadwal" class="text-xs font-semibold text-gray-600">Jadwal</label>
                    </div>
                    <input type="text" name="libur" class="h-10 px-3 text-xs bg-white border border-gray-300 shadow-xs rounded-xl" placeholder="Masukkan hari libur..">

                    <div class="flex justify-end gap-2 md:col-span-2 xl:col-span-6">
                        <button type="submit" formaction="{{ route('admin.exportV2') }}" class="text-yellow-900 border-0 btn bg-yellow-400/80 hover:bg-yellow-500">
                            <i class="ri-file-pdf-2-fill"></i><span class="text-sm">pdf</span>
                        </button>
                        <button type="submit" formaction="{{ route('admin.attendance.report') }}" class="border-0 btn bg-emerald-400/80 text-emerald-900 hover:bg-emerald-500">
                            <i class="ri-file-excel-fill"></i><span class="text-sm">exc</span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="p-4 bg-white border border-gray-100 shadow-xs rounded-2xl sm:p-5">
                <p class="font-semibold">*Hapus Foto Absen</p>
                <form action="{{ route('absen.hapusFotoAbsen') }}" method="post" class="mt-2">
                    @csrf
                    <div class="flex flex-wrap items-end gap-2">
                        <span>
                            <label class="label">Mulai: </label>
                            <input type="date" min="{{ $min }}" max="{{ $max }}" name="mulai" class="input input-bordered input-sm" />
                        </span>
                        <span>
                            <label class="label">Selesai: </label>
                            <input type="date" min="{{ $min }}" max="{{ $max }}" name="selesai" class="input input-bordered input-sm" />
                        </span>
                        <span>
                            <button type="submit" class="btn btn-sm btn-warning">Hapus</button>
                        </span>
                    </div>
                </form>
            </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-xs rounded-2xl">
        <div class="my-1 overflow-x-auto">
            <table class="table w-full table-zebra bg-slate-50" id="searchTable">
                <thead>
                    <tr>
                        <th class="bg-slate-300 rounded-tl-2xl">#</th>
                        <th class="bg-slate-300 " style="padding: 0 24px;">Photo</th>
                        <th class="bg-slate-300 ">Nama User</th>
                        <th class="bg-slate-300" style="padding: 0 25px;">Tanggal</th>
                        <th class="text-center bg-slate-300" style="padding: 0 58px">Shift</th>
                        <th class="bg-slate-300 ">Jam Masuk</th>
                        <th class="bg-slate-300 ">Jam Pulang</th>
                        <th class="bg-slate-300 ">Lokasi Presensi</th>
                        <th class="bg-slate-300 ">Keterangan</th>
                        <th class="bg-slate-300 ">Ibadah</th>
                        <th class="bg-slate-300 rounded-tr-2xl">Client</th>
                        <th class="hidden bg-slate-300">Point</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @forelse ($absen as $arr)
                        <tr>
                            <td>{{ $no++ }}.</td>
                            <td>
                                @if ($arr->image == 'no-image.jpg')
                                    <img class="lazy lazy-image" loading="lazy"
                                        srcset="{{ URL::asset('/logo/no-image.jpg') }}"
                                        data-src="{{ asset('storage/images/' . $arr->image) }}"
                                        alt="data-absensi-image" width="120px" />
                                @else
                                    <img class="lazy lazy-image" loading="lazy"
                                        src="{{ asset('storage/images/' . $arr->image) }}"
                                        data-src="{{ asset('storage/images/' . $arr->image) }}"
                                        alt="data-absensi-image" width="120px" />
                                @endif
                            </td>
                            <td class="wrap-break-word whitespace-pre-line">
                                {{ $arr->user ? ucwords(strtolower($arr->user->nama_lengkap)) : 'user_id' . ' : ' . $arr->user_id . 'AKU KOSONG' }}
                            </td>
                            <td class="text-center">{{ $arr->tanggal_absen }}</td>
                            @if ($arr->shift != null)
                                <td id="mitra" class="text-center">{{ $arr->shift->shift_name }} <br /> <span
                                        style="font-size: 10pt;">{{ Carbon\Carbon::parse($arr->shift->jam_start)->format('H:i') }} -
                                        {{ Carbon\Carbon::parse($arr->shift->jam_end)->subHour()->format('H:i') }}</span></td>
                            @else
                                <td class="font-semibold text-red-500 wrap-break-word whitespace-pre-wrap">Shift Kosong
                                </td>
                            @endif
                            
                            <td class="text-center">{{ $arr->absensi_type_masuk }}</td>
                            {{-- Handle Absensi Type Pulang --}}
                            <td class="text-center">
                                @if ($arr->absensi_type_pulang == null)
                                    <span class="font-bold text-red-500 underline">Belum</span>
                                @else
                                    {{ $arr->absensi_type_pulang }}
                                @endif
                            </td>
                            @php
                                $msLat = strlen($arr->msk_lat);
                                $msLong = strlen($arr->msk_long);

                                $plgLat = strlen($arr->plg_lat);
                                $plgLong = strlen($arr->plg_long);
                            @endphp


                            <td class="flex items-center justify-center">
                                <a class="flex items-center justify-center text-center btn btn-sm btn-primary"
                                    href="{{ route('admin.absen.map', $arr->id) }}"><i class="ri-map-pin-2-line"></i>
                                    Lihat</a>
                            </td>
                            {{-- End Handle Absensi Type Pulang --}}

                            {{-- Handle Keterangan --}}
                            <td>
                                @php
                                    $jamAbs = $arr->absensi_type_masuk ?? '00:00:00';
                                    $jamStr = $arr->shift?->jam_start ?? '00:00:00';
                                    $jamPulang = $arr->absensi_type_pulang;
                                    $jamEndShift = $arr->shift?->jam_end;

                                    // Determine time format
                                    $formatAbs = strlen($jamAbs) === 5 ? 'H:i' : 'H:i:s';
                                    $formatStr = strlen($jamStr) === 5 ? 'H:i' : 'H:i:s';

                                    // Parse times using Carbon
                                    $jAbs = Carbon\Carbon::createFromFormat($formatAbs, $jamAbs);
                                    $jJad = Carbon\Carbon::createFromFormat($formatStr, $jamStr);

                                    // Calculate the difference
                                    $jDiff = $jAbs->diff($jJad);

                                    $diffHasil = $jDiff->format('%h:%I:%S');

                                    $lebihPulangHasil = '';
                                    $isPulangLebih = false;

                                    if ($jamPulang && $jamEndShift) {
                                        $formatPulang = strlen($jamPulang) === 5 ? 'H:i' : 'H:i:s';
                                        $formatEndShift = strlen($jamEndShift) === 5 ? 'H:i' : 'H:i:s';

                                        $jPulang = Carbon\Carbon::createFromFormat($formatPulang, $jamPulang);
                                        $jEndShift = Carbon\Carbon::createFromFormat($formatEndShift, $jamEndShift)->subHour();

                                        if ($jPulang->gt($jEndShift)) {
                                            $isPulangLebih = true;
                                            $lebihDiff = $jPulang->diff($jEndShift);
                                            $lebihPulangHasil = $lebihDiff->format('%h:%I:%S');
                                        }
                                    }
                                @endphp
                                <div class="flex flex-col gap-2">
                                    @if ($arr->keterangan == 'masuk')
                                        <div class="gap-2 overflow-hidden badge badge-success">
                                            {{ $arr->keterangan }}
                                        </div>
                                    @elseif ($arr->keterangan == 'izin')
                                        <div class="gap-2 overflow-hidden badge badge-warning">
                                            {{ $arr->keterangan }}
                                        </div>
                                    @else
                                        <div class="gap-2 overflow-hidden text-xs text-center rounded-md badge badge-error">
                                            - {{ $diffHasil }}
                                        </div>
                                    @endif

                                    @if ($isPulangLebih)
                                        <div class="gap-2 overflow-hidden text-xs text-center text-white rounded-md badge bg-emerald-500">
                                            + {{ $lebihPulangHasil }}
                                        </div>
                                    @endif

                                    @if ($arr->tipe_id)
                                        @if ($arr->tipe_id == 1 && !$arr->terus)
                                            <span
                                                class="bg-[#02c9cc] text-black px-[.45rem] rounded-[5px] font-medium text-center">
                                                {{ $arr->tipeAbsensi->name }}</span>
                                        @elseif($arr->tipe_id == 1 && $arr->terus)
                                            <span
                                                class="bg-[#02c9cc] text-black px-[.45rem] rounded-[5px] font-medium text-center">
                                                Nerus</span>
                                        @else
                                            <span
                                                class="bg-[#cc0213] text-white px-[.45rem] rounded-[5px] font-medium text-center">
                                                {{ $arr->tipeAbsensi->name }}</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            {{-- EndHandle Keterangan --}}

                            <td class="wrap-break-word whitespace-pre-line">
                                @if ($arr->subuh != 0 || $arr->dzuhur != 0 || $arr->asar != 0 || $arr->magrib != 0 || $arr->isya != 0)
                                    <span>{{ $arr->subuh ? 'subuh,' : '' }} {{ $arr->dzuhur ? 'dzuhur,' : '' }}
                                        {{ $arr->asar ? 'asar,' : '' }} {{ $arr->magrib ? 'magrib,' : '' }}
                                        {{ $arr->isya ? 'isya' : '' }}</span>
                                @endif
                            </td>

                            <td class="wrap-break-word whitespace-pre-line">
                                {{ $arr?->kerjasama?->client?->panggilan ? $arr?->kerjasama?->client?->panggilan : $arr?->kerjasama?->client?->name }}
                            </td>

                            <td class="hidden">
                                @if ($arr->keterangan != 'telat' && $arr->keterangan != 'izin')
                                    <form action="{{ route('claim.point', $arr->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @forelse ($point as $item)
                                            @if ($arr?->kerjasama?->client_id == $item?->client_id && $arr?->point_id != null)
                                                {{ $arr->point->sac_point }}
                                            @endif
                                            @if ($arr->point_id == null)
                                                <input type="text" name="point_id" id="point_id"
                                                    value="{{ $item->id }}" class="hidden">
                                                <button class="w-16 px-2 py-1 rounded-sm bg-amber-400" type="submit">+
                                                    Point</button>
                                                @break
                                            @endif

                                        @empty
                                            ~
                                        @endforelse

                                    </form>
                                @else
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">~ Kosong ~</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $absen->links() }}
        </div>
        </section>
        </div>
    <script>
        $(document).ready(function() {
            const filterForm = $('#filterForm');
            const filterKerjasama = $('#filterKerjasama');
            const searchUserWrapper = $('#searchUserWrapper');
            const searchUserInput = $('#searchUser');
            const searchUserIdInput = $('#searchUserId');
            const searchUserResults = $('#searchUserResults');
            const userSearchUrl = filterForm.data('userSearchUrl');
            let searchTimeout = null;
            let activeSearchRequest = null;
            let selectedUserLabel = (searchUserInput.val() || '').trim();

            function toggleSearchUser() {
                if (filterKerjasama.val()) {
                    searchUserWrapper.removeClass('hidden');
                    searchUserInput.prop('disabled', false);
                    return;
                }

                searchUserWrapper.addClass('hidden');
                searchUserInput.prop('disabled', true).val('');
                searchUserIdInput.val('');
                hideSearchResults();
            }

            function renderSearchResults(items) {
                if (!Array.isArray(items) || items.length === 0) {
                    searchUserResults
                        .removeClass('hidden')
                        .html('<div class="px-3 py-2 text-sm text-gray-500">User tidak ditemukan.</div>');
                    return;
                }

                const rows = items.map(function(user) {
                    const label = user.nama_lengkap || user.name || 'Tanpa Nama';
                    const secondary = [user.name, user.email].filter(Boolean).join(' | ');

                    return `
                        <button
                            type="button"
                            class="flex w-full flex-col items-start gap-0.5 px-3 py-2 text-left hover:bg-slate-50"
                            data-user-id="${user.id}"
                            data-user-label="${$('<div>').text(label).html()}">
                            <span class="text-sm font-medium text-gray-800">${$('<div>').text(label).html()}</span>
                            <span class="text-xs text-gray-500">${$('<div>').text(secondary).html()}</span>
                        </button>
                    `;
                }).join('');

                searchUserResults.removeClass('hidden').html(rows);
            }

            function hideSearchResults() {
                searchUserResults.addClass('hidden').empty();
            }

            function searchUsers() {
                const keyword = (searchUserInput.val() || '').trim();
                const kerjasamaId = filterKerjasama.val();

                if (activeSearchRequest) {
                    activeSearchRequest.abort();
                }

                if (!kerjasamaId || keyword.length < 2) {
                    hideSearchResults();
                    return;
                }

                searchUserResults
                    .removeClass('hidden')
                    .html('<div class="px-3 py-2 text-sm text-gray-500">Mencari user...</div>');

                activeSearchRequest = $.ajax({
                    url: userSearchUrl,
                    method: 'GET',
                    data: {
                        kerjasama_id: kerjasamaId,
                        q: keyword
                    },
                    success: function(response) {
                        renderSearchResults(response.data || []);
                    },
                    error: function(xhr, textStatus) {
                        if (textStatus === 'abort') {
                            return;
                        }

                        searchUserResults
                            .removeClass('hidden')
                            .html('<div class="px-3 py-2 text-sm text-red-500">Gagal mencari user.</div>');
                    },
                    complete: function() {
                        activeSearchRequest = null;
                    }
                });
            }

            toggleSearchUser();
            filterKerjasama.on('change', function() {
                searchUserInput.val('');
                searchUserIdInput.val('');
                selectedUserLabel = '';
                toggleSearchUser();
            });

            searchUserInput.on('input', function() {
                const currentValue = (this.value || '').trim();

                if (currentValue !== selectedUserLabel) {
                    searchUserIdInput.val('');
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(searchUsers, 300);
            });

            searchUserInput.on('focus', function() {
                if ((this.value || '').trim().length >= 2) {
                    searchUsers();
                }
            });

            $(document).on('click', '#searchUserResults button', function() {
                const label = $(this).data('userLabel');
                searchUserInput.val(label);
                searchUserIdInput.val($(this).data('userId'));
                selectedUserLabel = label;
                hideSearchResults();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#searchUserWrapper').length) {
                    hideSearchResults();
                }
            });

            // Saat halaman dimuat, ambil semua elemen dengan class "lazy-image"
            var lazyImages = $('.lazy-image');

            // Fungsi untuk memuat gambar ketika mendekati jendela pandangan pengguna
            function lazyLoad() {
                lazyImages.each(function() {
                    var image = $(this);
                    if (image.is(':visible') && !image.attr('src')) {
                        image.attr('src', image.attr('data-src'));
                    }
                });
            }

            // Panggil fungsi lazyLoad saat halaman dimuat dan saat pengguna menggulir
            lazyLoad();
            $(window).on('scroll', lazyLoad);
        });
    </script>
</x-admin-layout>
