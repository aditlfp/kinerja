<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<title>{{ env('APP_NAME', 'KINERJA SAC-PONOROGO') }}</title>
		<link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">

		<link rel="preconnect" href="https://fonts.bunny.net">
		<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
			integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
		<script src="{{ URL::asset('src/js/jquery-min.js') }}"></script>
		<!-- Scripts -->
		@vite(['resources/css/app.css', 'resources/js/app.js'])

		{{-- Leaflet --}}
		<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
			integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
			integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

		<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
		<link href="https://cdn.jsdelivr.net/npm/remixicon@2.2.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        #map {
            height: 240px;
        }
        
    </style>
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-800">
    <div class="min-h-screen pb-24">
        @include('../layouts/navbar')
        <div class="mx-3 rounded-2xl bg-white shadow-xs ring-1 ring-slate-900/5 sm:mx-10">
            <main>
                <div class="px-5 py-5">
                    <p class="p-2 rounded-full bg-white text-center mx-10 my-5 font-semibold">Lokasi Absen Masuk & Pulang {{ $absen?->user->nama_lengkap }}</p>
                    <div class="flex justify-end">
                        <form method="get" action="{{ $absen ? Auth::user()->role_id == 2 ? route('admin.absen.map', $absen?->id) : route('mitra-lihatMap', $absen?->id) : '' }}" class="form-control bg-base-100 p-5 rounded-md join">
                            <input type="date" value="{{ $tgl ? $tgl : $absen?->tanggal_absen }}" name="tgl" class="join-item input input-bordered input-sm" />
                            <input type="hidden" name="user" value="{{ $us ? $us : $absen?->user_id }}"/>
                            <button class="btn btn-sm" style="border-radius: 0 10px 10px 0;">Submit</button>
                        </form>
                    </div>
                    @if($absen)
                        <span id="data" data-latitude="{{ $absen->msk_lat }}" data-lat-pulang="{{ $absen->plg_lat }}" data-long-pulang="{{ $absen->plg_long }}" data-longtitude="{{ $absen->msk_long }}" data-user="{{ $absen->user->nama_lengkap }}"></span>
                    @endif
                    @if($absen?->msk_lat && $absen?->msk_long)
                        <div id="map" class="rounded-lg"></div>
                    @else
                        <div class="rounded-lg bg-white flex items-center justify-center font-semibold" style="height: 240px;"><span>~ Tidak Ada Koordinat ~</span></div>
                    @endif
                    <div class="flex justify-start ikiKet font-medium {{ $absen ? '' : 'hidden' }}">
                        <div class="bg-slate-200 mt-1" style="padding: 4px 1rem 4px 1rem;">
                            <p class="font-semibold">#Keterangan</p>
                            <table>
                                <tbody>
                                    <tr>
                                        <td>
                                            <img src="{{ URL::asset('logo/pin-biru.png') }}" />
                                        </td>
                                        <td>
                                            <p>: Lokasi Masuk</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ URL::asset('logo/pin-oren.png') }}" />
                                        </td>
                                        <td>
                                            <p>: Lokasi Pulang</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p>Jarak</p>
                                        </td>
                                        <td>
                                            <p id="jarak"></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-center gap-2 sm:justify-end mx-10 my-5">
                        
                            <a href="{{ Auth::user()->role_id == 2 ? route('admin.index') : route('dashboard.index') }}" class="btn btn-error">Kembali</a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    



    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            function initMap() {
                var lat = document.getElementById("data").getAttribute('data-latitude');
                var long = document.getElementById("data").getAttribute('data-longtitude');
                var latPulang = parseFloat(document.getElementById("data").getAttribute('data-lat-pulang'));
                var longPulang = parseFloat(document.getElementById("data").getAttribute('data-long-pulang'));
                var user = document.getElementById("data").getAttribute('data-user');
                
                var lokMitra = @json($lokMitra);
                
                // Initialize map
                var map = L.map('map').setView([lat, long], 15);
    
                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                var circleRadius = L.circle([lokMitra.latitude, lokMitra.longtitude], {
                    color: 'crimson',
					fillColor: '#f09',
					fillOpacity: 0.5,
                    radius: lokMitra.radius             // Radius in meters
                }).addTo(map).bindPopup("Lokasi Mitra ").openPopup();
                
                // Custom red marker icon for start point
                var redIcon = L.icon({
                    iconUrl: @json(URL::asset('logo/pin-oren.png')),
                    iconSize: [25, 27], // size of the icon
                    iconAnchor: [14, 24], // point of the icon which will correspond to marker's location
                    popupAnchor: [1, -34] // point from which the popup should open relative to the iconAnchor
                });
        
                // Custom blue marker icon for end point
                var blueIcon = L.icon({
                    iconUrl: @json(URL::asset('logo/pin-biru.png')),
                    iconSize: [25, 27], // size of the icon
                    iconAnchor: [14, 24], // point of the icon which will correspond to marker's location
                    popupAnchor: [1, -34] // point from which the popup should open relative to the iconAnchor
                });
    
                // Add markers for start and end points
                var markerStart = L.marker([lat, long], {icon: blueIcon}).addTo(map).bindPopup("Lokasi Masuk ");
                var markerEnd = L.marker([latPulang, longPulang], {icon: redIcon}).addTo(map).bindPopup("Lokasi Pulang ");
    
                // Add path (polyline)
                var pathCoordinates = [[lat, long], [latPulang, longPulang]];
                var polyline = L.polyline(pathCoordinates, {
                    color: 'blue',
                    weight: 5,
                    opacity: 0.7
                }).addTo(map);
    
                // Zoom the map to fit the path with padding to zoom out
                map.fitBounds(polyline.getBounds(), {
                    padding: [55, 55] // Adjust padding in pixels (horizontal, vertical)
                });

    
                // Calculate distance in meters
                var distance = map.distance([lat, long], [latPulang, longPulang]);
    
                // Display distance below the map
                var distanceElement = document.getElementById("jarak");
                if(distance >= 1000) {
                    distance = distance / 1000;
                    distanceElement.textContent = `: ${distance.toFixed(2)} KM`;
                }else{
                    distanceElement.textContent = `: ${distance.toFixed(2)} meter`;
                }
            }
            initMap();
        })
        </script>
</body>

</html>
