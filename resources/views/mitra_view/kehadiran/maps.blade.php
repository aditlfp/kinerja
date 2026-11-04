<x-mitra-layout title="Detail Lokasi Absensi">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        #map {
            height: 430px;
        }

        .map-surface {
            background: linear-gradient(135deg, rgba(15, 23, 42, .04), rgba(255, 255, 255, .8));
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }

        @media (max-width: 768px) {
            #map {
                height: 300px;
            }
        }

        @media (max-width: 640px) {
            #map {
                height: 260px;
            }
        }
    </style>

    <div class="p-5 bg-white border rounded-3xl border-slate-200/80 shadow-[0_12px_32px_-24px_rgba(15,23,42,.35)] sm:p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="min-w-0">
                <p class="mb-2 text-xs font-semibold tracking-wide mitra-text-muted">Kehadiran / Detail lokasi</p>
                <p class="text-xs font-bold tracking-[0.18em] text-blue-700 uppercase">Detail koordinat absensi</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    Lokasi Absen {{ $absen?->user?->nama_lengkap ?? '-' }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Pantau titik masuk dan pulang pada tanggal yang dipilih.</p>
                <div class="flex flex-col items-stretch gap-2 mt-3 sm:flex-row sm:flex-wrap">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-blue-700 rounded-lg bg-blue-50"><span class="w-2 h-2 bg-blue-500 rounded-full"></span>Lokasi masuk</span>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-amber-700 rounded-lg bg-amber-50"><span class="w-2 h-2 bg-amber-500 rounded-full"></span>Lokasi pulang</span>
                </div>
            </div>
            <form method="get" action="{{ $absen ? route('mitra-lihatMap', $absen->id) : '' }}"
                class="w-full p-4 bg-slate-50/80 border border-slate-200 lg:w-auto rounded-2xl">
                <p class="mb-2 text-[11px] font-bold tracking-[0.14em] text-slate-500 uppercase">Filter tanggal</p>
                <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                    <input type="date" value="{{ $tgl ?: $absen?->tanggal_absen }}" name="tgl"
                        class="w-full text-sm input input-sm input-bordered mitra-input" />
                    <input type="hidden" name="user" value="{{ $us ?: $absen?->user_id }}" />
                    <button class="w-full text-white border-0 sm:w-auto btn btn-sm bg-blue-600 hover:bg-blue-700">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="p-4 mt-5 bg-white border rounded-3xl border-slate-200/80 shadow-[0_12px_32px_-24px_rgba(15,23,42,.3)] sm:p-5">
        <div class="grid grid-cols-1 gap-3 mb-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="p-3 border rounded-xl mitra-panel-soft">
                <p class="text-[10px] uppercase tracking-[0.2em] font-black mitra-text-muted">Tanggal</p>
                <p class="mt-1 text-sm font-semibold mitra-text-strong">{{ $tgl ?: $absen?->tanggal_absen ?: '-' }}</p>
            </div>
            <div class="p-3 border rounded-xl mitra-panel-soft">
                <p class="text-[10px] uppercase tracking-[0.2em] font-black mitra-text-muted">Latitude Masuk</p>
                <p class="mt-1 text-sm font-semibold mitra-text-strong">{{ $absen?->msk_lat ?: '-' }}</p>
            </div>
            <div class="p-3 border rounded-xl mitra-panel-soft">
                <p class="text-[10px] uppercase tracking-[0.2em] font-black mitra-text-muted">Longitude Masuk</p>
                <p class="mt-1 text-sm font-semibold mitra-text-strong">{{ $absen?->msk_long ?: '-' }}</p>
            </div>
        </div>

        @if($absen)
            <span id="data" data-latitude="{{ $absen->msk_lat }}" data-lat-pulang="{{ $absen->plg_lat }}"
                data-long-pulang="{{ $absen->plg_long }}" data-longtitude="{{ $absen->msk_long }}"
                data-user="{{ $absen->user?->nama_lengkap }}"></span>
        @endif

        @if($absen?->msk_lat && $absen?->msk_long)
            <div class="p-2 border rounded-2xl mitra-panel-soft map-surface shadow-sm">
                <div class="flex items-center justify-between px-2 py-2 mb-2 text-xs mitra-text-muted"><span class="font-semibold">Peta lokasi absensi</span><span>OpenStreetMap</span></div>
                <div id="map" class="rounded-xl"></div>
            </div>
        @else
            <div class="flex items-center justify-center font-semibold rounded-xl mitra-panel-soft mitra-text-soft"
                style="height: 360px;">
                <i class="text-3xl ri-map-pin-line" aria-hidden="true"></i>
                <span>Koordinat belum tersedia</span>
            </div>
        @endif

        <div class="mt-4 {{ $absen ? '' : 'hidden' }}">
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-900">Keterangan peta</p>
                        <p class="mt-1 text-xs text-slate-500">Legenda titik lokasi dan jarak perjalanan.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="inline-flex items-center gap-2 px-3 py-2 text-blue-700 bg-white border border-blue-100 rounded-lg"><img src="{{ URL::asset('logo/pin-biru.png') }}" alt="" class="object-contain w-4 h-5"> Lokasi masuk</span>
                        <span class="inline-flex items-center gap-2 px-3 py-2 text-amber-700 bg-white border border-amber-100 rounded-lg"><img src="{{ URL::asset('logo/pin-oren.png') }}" alt="" class="object-contain w-4 h-5"> Lokasi pulang</span>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-3 px-4 py-3 mt-4 bg-white border border-slate-200 rounded-xl">
                    <span class="flex items-center gap-2 text-xs font-semibold text-slate-500"><i class="ri-route-line text-blue-600" aria-hidden="true"></i>Jarak masuk–pulang</span>
                    <strong id="jarak" class="text-sm font-bold text-blue-700">-</strong>
                </div>
            </div>
        </div>
        <div class="flex justify-stretch mt-5 sm:justify-end">
            <a href="{{ route('mitra_absensi') }}" class="w-full text-white transition border-0 sm:w-auto btn bg-slate-700 hover:bg-slate-900 hover:-translate-y-0.5">Kembali ke daftar absensi</a>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const dataEl = document.getElementById("data");
            if (!dataEl || !document.getElementById("map")) {
                return;
            }

            const lat = parseFloat(dataEl.getAttribute('data-latitude'));
            const long = parseFloat(dataEl.getAttribute('data-longtitude'));
            const latPulang = parseFloat(dataEl.getAttribute('data-lat-pulang'));
            const longPulang = parseFloat(dataEl.getAttribute('data-long-pulang'));
            const lokMitra = @json($lokMitra);

            const map = L.map('map').setView([lat, long], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            if (lokMitra && lokMitra.latitude && lokMitra.longtitude && lokMitra.radius) {
                L.circle([lokMitra.latitude, lokMitra.longtitude], {
                    color: 'crimson',
                    fillColor: '#f09',
                    fillOpacity: 0.3,
                    radius: lokMitra.radius
                }).addTo(map).bindPopup("Lokasi Mitra");
            }

            const redIcon = L.icon({
                iconUrl: @json(URL::asset('logo/pin-oren.png')),
                iconSize: [25, 27],
                iconAnchor: [14, 24],
                popupAnchor: [1, -34]
            });

            const blueIcon = L.icon({
                iconUrl: @json(URL::asset('logo/pin-biru.png')),
                iconSize: [25, 27],
                iconAnchor: [14, 24],
                popupAnchor: [1, -34]
            });

            L.marker([lat, long], {
                icon: blueIcon
            }).addTo(map).bindPopup("Lokasi Masuk");

            const validPulang = !Number.isNaN(latPulang) && !Number.isNaN(longPulang);
            if (validPulang) {
                L.marker([latPulang, longPulang], {
                    icon: redIcon
                }).addTo(map).bindPopup("Lokasi Pulang");

                const polyline = L.polyline([
                    [lat, long],
                    [latPulang, longPulang]
                ], {
                    color: 'blue',
                    weight: 5,
                    opacity: 0.7
                }).addTo(map);

                map.fitBounds(polyline.getBounds(), {
                    padding: [55, 55]
                });

                const distance = map.distance([lat, long], [latPulang, longPulang]);
                const distanceElement = document.getElementById("jarak");
                if (distanceElement) {
                    if (distance >= 1000) {
                        distanceElement.textContent = `: ${(distance / 1000).toFixed(2)} KM`;
                    } else {
                        distanceElement.textContent = `: ${distance.toFixed(2)} meter`;
                    }
                }
            }
        });
    </script>
</x-mitra-layout>
