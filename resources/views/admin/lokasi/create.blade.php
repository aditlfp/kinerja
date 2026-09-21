<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Lokasi')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Lokasi</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Tentukan koordinat lokasi client dan radius absensi.</p>
                </div>
                <a href="{{ route('admin.lokasi.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.lokasi.store') }}" class="space-y-4" id="form">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="client_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Client</label>
                        <select name="client_id" id="client_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Client ~</option>
                            @forelse ($client as $cli)
                                <option value="{{ $cli->id }}">{{ $cli->name }}</option>
                            @empty
                                <option disabled>~ Data Kosong ~</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label for="latitude" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Latitude</label>
                        <input name="latitude" id="latitude" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Input Latitude..."/>
                    </div>
                    <div>
                        <label for="longtitude" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Longitude</label>
                        <input name="longtitude" id="longtitude" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Input Longitude..."/>
                    </div>
                    <div class="md:col-span-2">
                        <label for="radius" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Radius (meter)</label>
                        <input id="radius" name="radius" type="number" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Input radius min 50..."/>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
                    <div id="map" class="h-80 w-full"></div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.lokasi.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Lokasi</button>
                </div>
            </section>
        </form>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            (function() {
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longtitude');
                const map = L.map('map').setView([-7.865, 111.466], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                let marker;
                function setPoint(lat, lng) {
                    latInput.value = lat.toFixed(6);
                    lngInput.value = lng.toFixed(6);
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);
                }

                map.on('click', function(e) {
                    setPoint(e.latlng.lat, e.latlng.lng);
                });

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        map.setView([lat, lng], 15);
                        setPoint(lat, lng);
                    });
                }
            })();
        </script>
    @endpush
</x-admin-layout>
