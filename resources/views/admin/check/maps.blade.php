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

<body class="font-sans antialiased  bg-slate-400">
    <div class="min-h-screen pb-50">
        @include('../layouts/navbar')
        <div class="sm:mx-10 mx-5 bg-slate-500 rounded-md shadow-md">
            <main>
                <div class="px-5 py-5">
                    <div class="p-2 rounded-lg bg-white text-center">
                        @php
                            $items = $cex->items;
                            $firstItem = $items->first();
                            $latitude = $firstItem?->latitude;
                            $longitude = $firstItem?->longtitude;
                            $jobNames = $items->map(fn ($it) => $it->pekerjaanCp?->name ?? $it->input_manual)->filter()->unique()->implode(', ');
                        @endphp
                        <p class=" mx-10 my-5 font-semibold">Lokasi Pembuatan CP {{ $jobNames }}</p>
                        @if($latitude && $longitude)
                            <div id="map" class="rounded-lg" style="height: 140px;"></div>
                        @else
                            <div class="rounded-lg bg-white flex items-center justify-center font-semibold" style="height: 240px;"><span>~ Tidak Ada Koordinat ~</span></div>
                        @endif
                        @foreach ($items as $item)
                        <span class="flex flex-col sm:hidden">
                            @php $firstImage = $item->images->first()->path ?? null; @endphp
                            <p class=" mx-10 my-5 font-semibold">Bukti</p>
                            <div class="flex justify-center items-center">
                                @if (!$firstImage || $firstImage == 'no-image.jpg')
                                    <x-no-img />
                                @elseif(Storage::disk('public')->exists('images/' . $firstImage))
                                    <img class="lazy lazy-image" loading="lazy" src="" alt="" srcset="{{ asset('storage/images/' . $firstImage) }}" width="90px">
                                @else
                                    <x-no-img />
                                @endif
                            </div>
                            <p class=" mx-10 my-5 font-semibold text-xs">{{ $item->deskripsi }}</p>
                            <div class="flex justify-center items-center">
                                @if($item->approve_status == "proccess")
                                    <span class="badge bg-amber-500 px-2 text-xs text-white overflow-hidden">{{ $item->approve_status }}</span> 
                                @elseif($item->approve_status == "accept")
                                    <span class="badge bg-emerald-700 px-2 text-xs text-white overflow-hidden">{{ $item->approve_status }}</span> 
                                @else
                                    <span class="badge bg-red-500 px-2 text-xs text-white overflow-hidden">{{ $item->approve_status }}</span> 
                                @endif
                            </div>
                            
                            <div class="mt-5 flex flex-col gap-2 items-center justify-center mb-5">
                                @if ($item->approve_status == 'proccess')
                                    <div class="flex flex-col gap-2 justify-center items-center mt-2">
                                        <div class="flex flex-col w-full">
                            		        <label class="font-semibold text-sm">Note</label>
                            		        <textarea id="notes" value="" name="note" class="textarea textarea-bordered" placeholder="notes..."></textarea>
                            		    </div>
                                    </div>
                                    
                                    <div class="flex justify-center gap-1 items-center text-center overflow-hidden"  style="width: 14rem;">
                                        <div class="overflow-hidden">
                                            <button class="btn btn-success btn-xs rounded-btn flex items-center overflow-hidden" onclick="approveRequest('{{ route('direksi.approveCP', $cex->id) }}', 'accept')">
                                                <i class="ri-check-double-line"></i>
                                                <p class="overflow-hidden">accept</p>
                                            </button>
                                        </div>
                                        <div class="overflow-hidden">
                                            <button class="btn btn-error btn-xs rounded-btn flex items-center overflow-hidden" onclick="approveRequest('{{ route('direksi.approveCP', $cex->id) }}', 'denied')">
                                                <i class="ri-close-line"></i>
                                                <p class="overflow-hidden">denied</p>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    
                                @else
                                    {{-- Your hidden form code --}}
                                @endif
                            </div>
                        </span>
                        @endforeach
                    </div>
                    <div class="flex justify-center gap-2 sm:justify-end mx-10 my-5">
                        @if(Auth::user()->role_id == 2)
                            <a href="{{ route('admin.cp.show', $cex->user_id) }}" class="btn btn-error">Kembali</a>
                        @else
                            <a href="{{ route('direksi.cp.show', $cex->user_id) }}" class="btn btn-error">Kembali</a>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <span id="data" data-latitude="{{ $latitude }}" data-longtitude="{{ $longitude }}" data-user="{{ $cex->user->nama_lengkap }}"></span>



    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('button[id^="note"]').on('click', function(event) {
                event.preventDefault();
                var id = $(this).attr('id').substring(4);
                $('#notes' + id).toggle();
                $('#note' + id).toggle();
            });
        });
        function approveRequest(route, status) {
            var id = $('button[id^="note"]').attr('id').substring(4);
            var inputValue = $('#notes').val();
            $.ajax({
                url: route,
                type: 'POST',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    '_method': 'PATCH',
                    'approve_status': status,
                    'note': inputValue,
                },
                success: function (response) {
                    // Handle success, e.g., update UI
                    console.log(response);
                    location.reload();
                },
                error: function (xhr, status, error) {
                    // Handle error, e.g., show error message
                    console.error(error);
                }
            });
        }
    </script>
    <script>
        var lat = document.getElementById("data").getAttribute('data-latitude');
        var long = document.getElementById("data").getAttribute('data-longtitude');
        var user = document.getElementById("data").getAttribute('data-user');


        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        }

        function showPosition(position) {

            var map = L.map('map').setView([lat, long], 16); // 10 adalah zoom level

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([lat, long]).addTo(map);

            var circle = L.circle([lat, long], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: 50
            }).addTo(map).bindPopup("Lokasi " + user)
				.openPopup();
        }
    </script>
</body>

</html>
