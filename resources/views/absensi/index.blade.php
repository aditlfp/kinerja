<!DOCTYPE html>
<html lang="id" data-theme="bumblebee">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME', 'KINERJA SAC-PONOROGO') }}</title>
    <link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.bunny.net">
    {{-- <link rel="preload" as="style" href="https://fonts.bunny.net"> --}}
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    {{-- <script src="{{ URL::asset(path: 'src/js/jquery-min.js') }}"></script> --}}
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Leaflet --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @include('dashboard.partials.styles')

    <style>
        #map {
            height: 180px;
        }

        .leaflet-popup-content {
            font-size: 9px;
            font-weight: 600;
            max-width: 100px;
            text-align: center;
        }
    </style>
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-800">
    <div class="min-h-screen pb-24">
        <div class="px-6 pt-5">
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard.index') }}">
                    <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32">
                </a>
                <span class="font-semibold text-sm text-slate-500/50">Absensi Page</span>
            </div>
        </div>
        <div class="mx-4 mt-4 rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-200 md:mx-10">
            <div class="rounded-xl bg-slate-50">
            <main>
                <div class="px-5 py-5">
                    @if ($errors->any())
                        <div class="p-2 text-red-500 rounded-md bg-slate-200">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('absensi.store') }}" method="POST" enctype="multipart/form-data"
                        id="form-absen">
                        @method('POST')
                        @csrf
                        @if (Auth::user()->kerjasama_id != 1 || !in_array(Auth::user()->devisi_id, [2, 3, 7, 8, 12, 14, 18]))
                            <div class="flex flex-col items-center justify-center sm:m-0">
                                <div class="relative">
                                    <video id="video"
                                        class="bg-slate-200 p-2.5 rounded-md square-video max-w-[60vw]" autoplay
                                        playsinline></video>
                                </div>
                                <canvas id="canvas" style="display:none;"></canvas>
                                <div id="results" class="my-3 rounded-sm sm:mt-0"></div>

                                @if ($errors->image)
                                    <!--<p class="p-1 font-bold bg-white rounded-lg text-start" style="color: red">Foto Tidak Boleh Kosong</p>-->
                                @endif
                            </div>
                            <div class="flex justify-center">
                                <button type="button" id="snapButton"
                                    class="p-2 px-3 my-2 mb-5 text-white bg-blue-400 rounded-full"><i
                                        class="ri-camera-fill"></i></button>
                            </div>

                        @endif

                        <div class="p-1 my-3 rounded-sm ">
                            <label class="required font-semibold text-slate-700">Map : </label>
                            <span id="labelMap" class="flex flex-col justify-center text-center text-slate-500">
                                <p>Pastikan map sudah muncul !!</p>
                                <p id="resolver">coba refresh browser beberapa kali jika map belum muncul</p>
                            </span>
                            <div id="map" class="rounded-sm"></div>
                            <span id="tutor"
                                class="flex flex-col justify-center hidden text-sm italic text-center text-slate-500 capitalize">
                                <p id="latlongLabel" class="text-[8px]"></p>
                                <p>Pastikan tanda biru berada dilingkaran</p>
                            </span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-col justify-between">
                                <label for="name"
                                    class="font-semibold text-slate-700">{{ Route::currentRouteName() == 'absensi-karyawan-co-cs.index' || Route::currentRouteName() == 'absensi-karyawan-co-scr.index' ? 'Pilih Nama: ' : 'Nama: ' }}</label>
                                @if (Route::currentRouteName() == 'absensi-karyawan-co-cs.index' ||
                                        Route::currentRouteName() == 'absensi-karyawan-co-scr.index')
                                    <select name="user_id" id="selectUser" class="select select-bordered">
                                        <option selected value="{{ Auth::user()->id }}" class="op"
                                            data-clien="{{ Auth::user()->kerjasama->client_id }}">
                                            {{ Auth::user()->nama_lengkap }}</option>
                                        @foreach ($userL as $us)
                                            <option value="{{ $us->id }}" class="op"
                                                data-divisi="{{ $us->devisi_id }}"
                                                data-clien="{{ $us->kerjasama->client_id }}"
                                                data-jab="{{ $us->divisi?->jabatan_id }}">
                                                {{ ucwords(strtolower($us->nama_lengkap)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" id="user_id" name="user_id" value="{{ Auth::user()->id }}"
                                        hidden>
                                    <input type="text" id="name" name="{{ Auth::user()->name }}"
                                        value="{{ Auth::user()->nama_lengkap }}" disabled class="input input-bordered">
                                @endif
                            </div>
                            <div class="flex flex-col justify-between">
                                <label for="kerjasama" class="font-semibold text-slate-700">Penempatan: </label>
                                @if (Auth::user()->id == 10)
                                    <select class="selectMitra select select-bordered" name="kerjasama_id">

                                    </select>
                                @elseif(Auth::user()->id == 7)
                                    <input type="text" name="kerjasama_id" id="kerjasama_id" hidden
                                        value="{{ Auth::user()->kerjasama_id }}">
                                    <input type="text" id="kerjasama"
                                        name="{{ Auth::user()->kerjasama->client->name }}"
                                        value="{{ Auth::user()->id == 7 ? '' : Auth::user()->kerjasama->client->name }}"
                                        disabled class="input input-bordered viewKerjasama">
                                @else
                                    <input type="text" name="kerjasama_id" id="kerjasama_id" hidden
                                        value="{{ Auth::user()->kerjasama_id }}">
                                    <input type="text" id="kerjasama"
                                        name="{{ Auth::user()->kerjasama->client->name }}"
                                        value="{{ Auth::user()->jabatan?->code_jabatan == 'SPV-W' ? '' : Auth::user()->kerjasama->client->name }}"
                                        disabled class="input input-bordered viewKerjasama">
                                @endif
                            </div>
                            @if (Auth::user()->kerjasama_id == 1)
                                <input type="text" name="masuk" value="1" class="hidden" />
                            @endif
                            @if (Auth::user()->kerjasama_id != 1)
                                @php
                                    $lanjutShift =
                                        isset($absensi[0]) &&
                                        $absensi[0]->absensi_type_pulang &&
                                        $absensi[0]->tanggal_absen == Carbon\Carbon::now()->format('Y-m-d') &&
                                        $absensi[0]->masuk;
                                @endphp
                                <div class="flex flex-col justify-start">
                                    <x-input-label for="jenis_abs" class="text-lg font-semibold text-slate-700" :value="__('Jenis Absen: ')" />
                                    <div class="flex flex-col justify-start rounded-lg bg-slate-50">
                                        <div class="flex items-center">
                                            <input type="radio" id="type_absen" data-pilih="masuk"
                                                name="jenis_abs" value="1" class="m-2 radio radio-sm"
                                                {{ $lanjutShift ? 'disabled' : 'checked' }}>
                                            <label for="masuk" class="overflow-hidden"
                                                {{ $lanjutShift ? 'disabled' : '' }}>Masuk</label>
                                        </div>
                                        @if ($lanjutShift)
                                            <div class="flex items-center">
                                                <input type="radio" id="type_absen" data-pilih="terus"
                                                    name="jenis_abs" value="1"
                                                    class="m-2 radio radio-sm disabled" checked>
                                                <label for="tukar" class="overflow-hidden" disabled>Meneruskan
                                                    Shift</label>
                                            </div>
                                        @endif
                                        <div class="flex items-center">
                                            <input type="radio" id="type_absen" data-pilih="tukar"
                                                name="jenis_abs" value="1" class="m-2 radio radio-sm disabled"
                                                disabled>
                                            <label for="tukar" class="overflow-hidden" disabled>Tukar Shift
                                                (maintenance)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="type_absen" data-pilih="lembur"
                                                name="jenis_abs" value="1" class="m-2 radio radio-sm" disabled>
                                            <label for="lembur" class="overflow-hidden">Lembur (maintenance)</label>
                                        </div>

                                        <div id="type_absen_div" class="hidden">
                                            <input type="text" name="masuk" value="1" class="hidden" />
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full" id="divPengganti" style="display: none;">
                                    <x-input-label for="pengganti" class="text-lg font-semibold text-slate-700" :value="__('Pengganti: ')" />
                                    <select name="pengganti" id="pengganti" required
                                        style="{{ $errors->any() && $errors->pengganti ? 'border: 2px solid red;' : '' }}"
                                        class="w-full font-thin select select-bordered">
                                        @if ($errors->any() && $errors->pengganti)
                                            <option selected disabled class="p-1 my-1 font-bold text-red-600">
                                                Pengganti Tidak Boleh Kosong</option>
                                        @endif
                                        <option disabled {{ $errors->any() && $errors->pengganti ? '' : 'selected' }}>
                                            -- Pilih Pengganti --</option>

                                        <option>Belum Ada Karyawan</option>

                                    </select>
                                </div>
                            @endif
                            @if (Auth::user()->divisi->jabatan->name_jabatan == 'DIREKSI')
                                <input type="hidden" name="shift_id" value="145" />
                            @elseif(Auth::user()->jabatan->code_jabatan == 'SPV-W')
                                <input type="hidden" name="shift_id" value="195" />
                            @elseif(Auth::user()->devisi_id == 12)
                                <input type="hidden" name="shift_id" value="200" />
                            @else
                                <div class="flex flex-col justify-between">
                                    <label class="required font-semibold text-slate-700" for="shift_id">Shift: </label>
                                    <select name="shift_id" id="shift_id"
                                        {{ Auth::user()->name == 'DIREKSI' ? '' : 'required' }}
                                        style="{{ $errors->any() && $errors->shift_id ? 'border: 2px solid red;' : '' }}"
                                        class="font-thin select select-bordered ">
                                        @if ($errors->any() && $errors->shift_id)
                                            <option selected disabled class="p-1 my-1 font-bold text-red-600">
                                                Shift Tidak Boleh Kosong</option>
                                        @endif
                                        <option disabled {{ $errors->any() && $errors->shift_id ? '' : 'selected' }}>--
                                            Pilih Shift --</option>

                                        @forelse ($shift as $i)
                                            @php
                                                $endA = Carbon\Carbon::parse($i->jam_end)->subHour(1)->format('H:i');
                                            @endphp
                                            <option value="{{ $i->id }}" data-shift="{{ $i?->jam_start }}">
                                                {{ ucwords(strtolower($i?->shift_name)) }} | {{ $i?->jam_start }} -
                                                {{ $endA }}
                                            </option>
                                        @empty
                                            <option readonly disabled>~ Tidak ada Shift ! ~</option>
                                        @endforelse
                                    </select>
                                    @if (Auth::user()->kerjasama->client_id == 1)
                                        <span id="absen-kantor"
                                            data-absen-kantor="{{ Auth::user()->kerjasama->client_id }}"
                                            hidden></span>
                                    @endif
                                </div>
                            @endif

                            <div>
                                <div>
                                    <label class="required font-semibold text-slate-700">Perlengkapan: </label>
                                </div>
                                <div class="p-2 bg-white rounded-lg "
                                    style="{{ $errors->any() && $errors->shift_id ? 'border: 2px solid red;' : '' }}">
                                    @if ($errors->any() && $errors->perlengkapan)
                                        <p class="p-1 my-1 font-bold text-red-600">Perlengkapan Tidak Boleh
                                            Kosong</p>
                                    @endif
                                    <div id="divPerlengkapan" class="grid grid-cols-1">
                                        @forelse ($dev as $arr)
                                            @foreach ($arr->perlengkapan as $i)
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="perlengkapan[]"
                                                        id="perlengkapan {{ $i->id }}"
                                                        value="{{ $i->name }}"
                                                        class="m-2 checkbox checkbox-sm perle">
                                                    <label
                                                        for="perlengkapan {{ $i->id }}" class="text-sm">{{ $i->name }}</label>
                                                </div>
                                            @endforeach
                                        @empty
                                            <p>~ Kosong ~</p>
                                        @endforelse
                                    </div>
                                </div>

                            </div>
                            <div>
                                <label class="font-semibold text-slate-700" for="deskripsi">Deskripsi (opsional) : </label>
                                <textarea name="deskripsi" id="deskripsi" value="" placeholder="deskripsi..."
                                    class="w-full textarea textarea-bordered"></textarea>
                            </div>

                            @if (Auth::user()->kerjasama_id != 1 || !in_array(Auth::user()->devisi_id, [2, 3, 7, 8, 12, 14, 18]))
                                <input type="file" id="image" name="image" class="image-tag"
                                    accept="image/*" hidden>
                            @endif
                            <input type="text" id="keterangan" name="keterangan" value="masuk"
                                data-authName="{{ Auth::user()->name }}" hidden>
                        </div>
                        <input type="text" class="hidden" name="absensi_type_masuk" value="1">
                        @php
                            $key = Auth::user()->id;
                            $cekRoute =
                                Route::currentRouteName() == 'absensi-karyawan-co-cs.index' ||
                                Route::currentRouteName() == 'absensi-karyawan-co-scr.index';
                        @endphp
                        <div class="flex flex-col justify-center gap-3 mt-2 mr-2 sm:justify-end">
                            <span id="labelWaktuStart"
                                class="text-center text-[10px] capitalize font-semibold hidden py-2 px-4 rounded-md bg-slate-50"></span>
                            <span class="flex justify-center gap-3">
                                @forelse ($absensi as $abs)
                                    {{-- sudah --}}
                                    @if (
                                        !$cekRoute &&
                                            $abs->tanggal_absen == Carbon\Carbon::now()->format('Y-m-d') &&
                                            $abs->absensi_type_pulang == null &&
                                            $abs->tukar == null &&
                                            !$afaLib)
                                        <button
                                            class="p-2 my-2 px-4 text-slate-100 bg-blue-300  rounded-sm transition-all ease-linear .2s disabled cursor-not-allowed"
                                            disabled>Sudah Absen</button>
                                    @elseif($afaLib && Auth::user()->divisi->jabatan_id != 35)
                                        <button
                                            class="p-2 my-2 px-4 text-slate-100 bg-blue-300  rounded-sm transition-all ease-linear .2s disabled cursor-not-allowed"
                                            disabled>Jadwal Tidak Ada</button>
                                    @elseif(!$cekRoute && $abs->tanggal_absen == Carbon\Carbon::now()->format('Y-m-d') && $abs->terus)
                                        <button
                                            class="p-2 my-2 px-4 text-slate-100 bg-blue-300  rounded-sm transition-all ease-linear .2s disabled cursor-not-allowed"
                                            disabled>Sudah Absen 2x (Mulih Lurr, ojo kerjo ae)</button>
                                    @else
                                        <button type="button"
                                            class="p-2 btnAbsen my-2 px-4 text-white bg-blue-500 hover:bg-blue-600 rounded-sm transition-all ease-linear .2s"
                                            id="btnAbsen">Absen</button>
                                    @endif
                                @break

                                @empty
                                    <button type="button"
                                        class="p-2 btnAbsen my-2 px-4 text-white bg-blue-500 hover:bg-blue-600 rounded-sm transition-all ease-linear .2s"
                                        id="btnAbsen">Absen</button>
                                @endforelse

                                <a href="{{ route('dashboard.index') }}"
                                    class="p-2 my-2 px-4 text-white bg-red-500 hover:bg-red-600 rounded-sm transition-all ease-linear .2s">
                                    Kembali
                                </a>
                            </span>
                        </div>
                        <input class="hidden" id="thisId" value="{{ Auth::user()->id }}">
                        @php
                            $mytime = Carbon\Carbon::now()->format('H:m:s');
                            $mytime2 = '10:00:00';
                            $uID = Auth::user()->divisi->jabatan->code_jabatan;
                        @endphp
                        <div class="hidden" id="HiddenOnes">
                            <input class="hidden" id="thisTime" value="{{ $mytime }}">
                            <input class="hidden" id="thisTime2" value="{{ $mytime2 }}">
                            <input class="hidden" id="isi" name="absensi_type_pulang">
                            <input type="hidden" id="lat" name="lat_user" value=""
                                class="hidden lat_user" />
                            <input type="hidden" id="long" name="long_user" value=""
                                class="hidden long_user" />

                            <input type="hidden" id="lat_mitra" name="lat_mitra" value="{{ $harLok->latitude }}"
                                class="hidden" />
                            <input type="hidden" id="long_mitra" name="long_mitra"
                                value="{{ $harLok->longtitude }}" class="hidden" />
                            <input type="hidden" id="radius_mitra" name="radius_mitra"
                                value="{{ $harLok->radius }}" class="hidden" />
                        </div>

                        <span class="hidden" id="dataUser" data-userId="{{ $uID }}"></span>
                    </form>
                </div>
            </main>
            </div>
        </div>
    </div>

    <div id="absen-submit-overlay" class="fixed inset-0 z-100000 hidden items-center justify-center bg-slate-900/60 backdrop-blur-[2px]">
        <div class="w-[min(92vw,22rem)] rounded-2xl border border-slate-200 bg-white px-5 py-5 text-center shadow-2xl">
            <div class="mx-auto mb-3 h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-sky-500"></div>
            <p class="text-sm font-semibold text-slate-900">Mengirim absen...</p>
            <p id="absen-submit-status" class="mt-1 text-xs text-slate-500">Menyiapkan data...</p>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                <div id="absen-submit-progress" class="h-full w-0 rounded-full bg-sky-500 transition-all duration-500"></div>
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('js/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('js/moment.min.js') }}"></script>
    <script>
        window.toastr = window.toastr || {
            options: {},
            success: function () {},
            error: function () {},
            info: function () {},
            warning: function () {},
            remove: function () {},
            clear: function () {}
        };

        let absenOverlayTimer = null;

        function showAbsenSubmitOverlay() {
            const overlay = document.getElementById('absen-submit-overlay');
            const status = document.getElementById('absen-submit-status');
            const progress = document.getElementById('absen-submit-progress');
            if (!overlay) return;
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            if (status) status.textContent = 'Menyiapkan data...';
            if (progress) progress.style.width = '22%';

            const steps = [
                { delay: 350, text: 'Memvalidasi input...', pct: '45%' },
                { delay: 900, text: 'Mengambil lokasi & foto...', pct: '72%' },
                { delay: 1500, text: 'Mengirim ke server...', pct: '90%' },
            ];

            if (absenOverlayTimer) clearInterval(absenOverlayTimer);
            let idx = 0;
            absenOverlayTimer = setInterval(() => {
                if (!status || !progress || idx >= steps.length) {
                    clearInterval(absenOverlayTimer);
                    return;
                }
                status.textContent = steps[idx].text;
                progress.style.width = steps[idx].pct;
                idx += 1;
            }, 450);
        }

        function hideAbsenSubmitOverlay() {
            const overlay = document.getElementById('absen-submit-overlay');
            const progress = document.getElementById('absen-submit-progress');
            if (!overlay) return;
            if (absenOverlayTimer) {
                clearInterval(absenOverlayTimer);
                absenOverlayTimer = null;
            }
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            if (progress) progress.style.width = '0';
        }
    </script>
    <x-flasher />
    <x-flasher-theme />

    @if (Auth::user()->kerjasama_id != 1 || !in_array(Auth::user()->devisi_id, [2, 3, 7, 8, 12, 14, 18]))
        <!--Camera logic moved to resources/js/absensi/camera.js -->
        <!--Camera-->
    @endif
    <!--Pershift an-->
    <script>
        $(document).ready(function() {
            var selectedClientID = $('#selectUser').find(':selected').data('clien');
            var selectedJabID = $('#selectUser').find(':selected').data('jab');
            var selectedDivID = $('#selectUser').find(':selected').data('divisi');

            $('#selectUser').change(function() {
                selectedClientID = $(this).find(':selected').data('clien');
                selectedJabID = $(this).find(':selected').data('jab');
                selectedDivID = $(this).find(':selected').data('divisi');
                // console.log(selectedClientID);
                $.ajax({
                    url: '/get-shifts/' + selectedClientID + '/' + selectedJabID,
                    type: 'GET',
                    success: function(data) {
                        var html = '';
                        var htmlP = '';

                        // console.log(data.dev);
                        if (data.shift.length > 0) {
                            html +=
                                '<option disabled {{ $errors->any() && $errors->shift_id ? '' : 'selected' }}>-- Pilih Shift --</option>';
                            data.shift.forEach(function(shift) {
                                // var endA = moment(shift.jam_end).subtract(1, 'hour').format('HH:mm');

                                html += '<option value="' + shift.id +
                                    '" data-shift="' + shift.jam_start + '">' +
                                    shift.jam_start + ' - ' + shift.jam_end + ' | ' +
                                    shift.jabatan.name_jabatan + ' | ' + shift
                                    .shift_name +
                                    '</option>';
                            });

                        } else {
                            html += '<option>~ Tidak ada Shift ! ~</option>';
                        }

                        $('#shift_id').html(html);
                        if (data.dev.length > 0) {
                            data.dev.forEach(function(divisi) {
                                if (divisi.id == selectedDivID) {
                                    // console.log(divisi);
                                    divisi.perlengkapan.forEach(function(perle) {
                                        htmlP +=
                                            '<div><input type="checkbox" name="perlengkapan[]" id="perlengkapan ' +
                                            perle.id + '" value="' + perle
                                            .name +
                                            '" class="m-2 checkbox checkbox-sm perle"><label for="perlengkapan ' +
                                            perle.id + '">' + perle.name +
                                            '</label></div>';
                                        // console.log(htmlP);
                                    })
                                }
                            })
                        } else {
                            htmlP += '<p>~ Kosong ~</p>';
                        }
                        $('#divPerlengkapan').html(htmlP);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                })

            });

            // Event listener for checkbox change
            $('.radio').change(function() {
                const selected = $(this).data('pilih');

                let inputName = selected;
                let inputHtml = `<input type="text" name="${inputName}" value="1" class="hidden"/>`;
                $('#type_absen_div').html(inputHtml);

                if (selected === "tukar") {
                    $('#divPengganti').show();
                } else {
                    $('#divPengganti').hide();
                }
            });

            // Trigger change manually on page load to reflect default checked radio
            $(document).ready(function() {
                $('.radio:checked').trigger('change');
            });
        });
    </script>
    @php
        $cameraEnabled = Auth::user()->kerjasama_id != 1 || !in_array(Auth::user()->devisi_id, [2, 3, 7, 8, 12, 14, 18]);
        $isLeaderCameraRoute = Route::currentRouteName() == 'absensi-karyawan-co-cs.index' ||
            Route::currentRouteName() == 'absensi-karyawan-co-scr.index';
    @endphp
    <!--Maps--> 
    <script>
        window.absensiPageConfig = {
            loc: @json($lokLok),
            mitra: @json($penempatan),
            defaultLocationId: "{{ Auth::user()->kerjasama_id }}",
            lati: "{{ $harLok->latitude }}",
            longi: "{{ $harLok->longtitude }}",
            radi: "{{ $harLok->radius }}",
            client: "{{ $harLok->client->name }}",
            canBypassRadius: @json(Auth::user()->jabatan->code_jabatan == 'SPV-W' || Auth::user()->devisi_id == 12),
            isSpvW: @json(Auth::user()->jabatan->code_jabatan == 'SPV-W'),
            isDivisi12: @json(Auth::user()->devisi_id == 12),
            authUserId: @json(Auth::user()->id),
            authClientId: @json(Auth::user()->kerjasama->client_id),
            routes: {
                store: "{{ route('absensi.store') }}"
            },
            camera: {
                enabled: @json($cameraEnabled),
                isLeader: @json($isLeaderCameraRoute)
            },
            time: {
                authCodeJabatan: @json(Auth::user()->jabatan?->code_jabatan),
                kerId: {{ Auth::user()->kerjasama_id }}
            }
        };

        window.dispatchEvent(new CustomEvent('absensi:ready-config'));
    </script>
    <!--Waktu-->
    <!--Waktu logic moved to resources/js/absensi/time.js -->

</body>

</html>
