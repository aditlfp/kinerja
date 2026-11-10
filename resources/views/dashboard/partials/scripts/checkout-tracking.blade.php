    @if ($shouldTrackPulang)
        <script defer>
            (function($) {
                // Cache DOM elements
                const elements = {
                    lat: $('.lat'),
                    long: $('.long'),
                    labelMap: $('#labelMap, #checkoutGpsStatus'),
                    tutor: $('#tutor'),
                    pulangBtn: $('#modalPulangBtn'),
                    pulangBtnText: $('#modalPulangBtn .checkout-button-label'),
                    checkoutForm: $('#checkoutForm'),
                    checkoutSubmitBtn: $('#checkoutSubmitBtn'),
                    checkoutLat: $('#checkoutForm input[name="lat_user"]'),
                    checkoutLong: $('#checkoutForm input[name="long_user"]'),
                    checkoutMap: $('#checkoutMap'),
                    checkoutDistanceInfo: $('#checkoutDistanceInfo')
                };

                // Get location data from server
                const lokasiMitra = @json($lokasiMitra);
                const userCoopId = @json($kerjasamaId);
                const userName = @json($user->name);
                const initialAttendancePosition = {
                    lat: parseFloat(@json($absenP?->msk_lat)),
                    lng: parseFloat(@json($absenP?->msk_long))
                };
                const canSkipRadius = Number(userCoopId) === 1 || userName === 'DIREKSI';

                // pre calculated loc
                const processedLocations = lokasiMitra.map(loc => ({
                    lat: parseFloat(loc.latitude),
                    lng: parseFloat(loc.longtitude),
                    radius: parseFloat(loc.radius)
                })).filter(loc => !Number.isNaN(loc.lat) && !Number.isNaN(loc.lng) && !Number.isNaN(loc.radius));

                let watchId = null;
                let lastPosition = null;
                let lastValidPosition = null;
                let positionCheckThrottle = null;
                let checkoutMap = null;
                let startMarker = null;
                let currentMarker = null;
                let distanceLine = null;
                let distanceLabel = null;
                let radiusLayers = [];
                let lastPrayerCheck = 0;
                let lastPrayerName = null;
                let currentLat = null;
                let currentLng = null;
                let prayerStream = null;
                let prayerPhotoCaptured = false;

                const THROTTLE_DELAY = 300;
                const MAX_GPS_ACCURACY_METERS = 100;
                const MAX_VALID_POSITION_AGE_MS = 15000;
                const hasInitialAttendancePosition = hasValidCoordinates(
                    initialAttendancePosition.lat,
                    initialAttendancePosition.lng
                );

                // Check if geolocation is supported
                if (!navigator.geolocation) {
                    handleGeolocationNotSupported();
                    return;
                }

                // Set up geolocation watching
                const watchOptions = {
                    enableHighAccuracy: true,
                    maximumAge: 5000,
                    timeout: 15000
                };

                updateButtonState(false, 'Mengambil GPS...');

                navigator.geolocation.getCurrentPosition(
                    handlePositionUpdate,
                    handleGeolocationError,
                    watchOptions
                );

                watchId = navigator.geolocation.watchPosition(
                    handlePositionUpdate,
                    handleGeolocationError,
                    watchOptions
                );

                setInterval(() => {
                    if (currentLat && currentLng) {
                        checkWaktuSholat(currentLat, currentLng);
                    }
                }, 5000);

                elements.pulangBtn.on('click', function() {
                    initializeCheckoutMap();
                    refreshCheckoutMap();
                });

                elements.checkoutForm.on('submit', function(event) {
                    if (lastValidPosition && Date.now() - lastValidPosition.timestamp <= MAX_VALID_POSITION_AGE_MS) {
                        setCoordinateInputs(lastValidPosition.lat, lastValidPosition.lng);
                        return true;
                    }

                    event.preventDefault();
                    requestCheckoutPosition();

                    return false;
                });

                $(window).on('beforeunload', function() {
                    if (watchId !== null) {
                        navigator.geolocation.clearWatch(watchId);
                    }
                });

                // Helper functions
                function handlePositionUpdate(position) {
                    if (positionCheckThrottle) {
                        clearTimeout(positionCheckThrottle);
                    };

                    positionCheckThrottle = setTimeout(function() {
                        processPositionUpdate(position);
                    }, THROTTLE_DELAY);
                }

                function processPositionUpdate(position) {
                    const {
                        latitude,
                        longitude,
                        accuracy
                    } = position.coords;

                    currentLat = latitude;
                    currentLng = longitude;

                    if (currentLat && currentLng && !lastPrayerCheck) {
                        lastPrayerCheck = Date.now();
                        checkWaktuSholat(currentLat, currentLng);
                    }

                    if (accuracy > MAX_GPS_ACCURACY_METERS) {
                        setCoordinateInputs(latitude, longitude);
                        updateCurrentMarker(latitude, longitude, accuracy);
                        elements.tutor.removeClass('hidden');
                        elements.labelMap
                            .text(`GPS belum akurat (${Math.round(accuracy)}m). Tunggu beberapa detik.`)
                            .removeClass('hidden');
                        updateButtonState(false, 'GPS Belum Akurat');
                        return;
                    }

                    elements.labelMap.addClass('hidden');

                    // Skip if position hasn't changed significantly
                    if (lastPosition) {
                        const distance = calculateDistance(
                            latitude, longitude,
                            lastPosition.lat, lastPosition.lng
                        );

                        // Only process if moved more than 5 meters
                        if (distance < 5) return;
                    }

                    // Update last position
                    lastPosition = {
                        lat: latitude,
                        lng: longitude
                    };

                    setCoordinateInputs(latitude, longitude);
                    updateCurrentMarker(latitude, longitude, accuracy);
                    elements.tutor.removeClass('hidden');

                    // Handle different coop types
                    if (canSkipRadius) {
                        // For coop 1, always enable the button without radius check
                        lastValidPosition = {
                            lat: latitude,
                            lng: longitude,
                            accuracy,
                            timestamp: Date.now()
                        };
                        updateButtonState(true, 'Waktunya Pulang');
                    } else {
                        // For other coops, check if user is within any location's radius
                        checkLocationRadius(latitude, longitude);
                    }
                }

                // Optimized distance calculation (Haversine formula)
                function calculateDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371e3; // Earth's radius in meters
                    const f1 = lat1 * Math.PI / 180;
                    const f2 = lat2 * Math.PI / 180;
                    const fi = (lat2 - lat1) * Math.PI / 180;
                    const fo = (lon2 - lon1) * Math.PI / 180;

                    const a = Math.sin(fi / 2) * Math.sin(fi / 2) +
                        Math.cos(f1) * Math.cos(f2) *
                        Math.sin(fo / 2) * Math.sin(fo / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                    return R * c; // Distance in meters
                }

                // Optimized radius checking
                function checkLocationRadius(userLat, userLng) {
                    const withinAnyRadius = isWithinAnyRadius(userLat, userLng);

                    lastValidPosition = withinAnyRadius ? {
                        lat: userLat,
                        lng: userLng,
                        timestamp: Date.now()
                    } : null;

                    updateButtonState(withinAnyRadius, withinAnyRadius ? 'Waktunya Pulang' : 'Diluar Radius!');
                }

                function isWithinAnyRadius(userLat, userLng) {
                    // Use our pre-processed locations for faster checks
                    for (const location of processedLocations) {
                        const distance = calculateDistance(
                            userLat, userLng,
                            location.lat, location.lng
                        );

                        if (distance <= location.radius) {
                            return true;
                        }
                    }

                    return false;
                }

                function hasValidCoordinates(latitude, longitude) {
                    return latitude !== '' &&
                        longitude !== '' &&
                        !Number.isNaN(parseFloat(latitude)) &&
                        !Number.isNaN(parseFloat(longitude));
                }

                function setCoordinateInputs(latitude, longitude) {
                    elements.lat.val(latitude);
                    elements.long.val(longitude);
                }

                function initializeCheckoutMap() {
                    if (checkoutMap || !elements.checkoutMap.length || typeof L === 'undefined') {
                        return;
                    }

                    const fallbackCenter = hasInitialAttendancePosition ?
                        [initialAttendancePosition.lat, initialAttendancePosition.lng] :
                        (processedLocations[0] ? [processedLocations[0].lat, processedLocations[0].lng] : [-7.868, 111.462]);

                    checkoutMap = L.map('checkoutMap', {
                        attributionControl: false,
                        zoomControl: false,
                        dragging: true,
                        scrollWheelZoom: false,
                        tap: true
                    }).setView(fallbackCenter, 16);

                    L.control.zoom({
                        position: 'bottomright'
                    }).addTo(checkoutMap);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(checkoutMap);

                    if (hasInitialAttendancePosition) {
                        startMarker = L.marker([initialAttendancePosition.lat, initialAttendancePosition.lng], {
                            icon: createCheckoutIcon('ri-login-circle-line', 'checkout-marker-start')
                        }).addTo(checkoutMap).bindPopup('Lokasi Absen Masuk');
                    }

                    radiusLayers = processedLocations.map(location => L.circle([location.lat, location.lng], {
                        color: '#d97706',
                        fillColor: '#fbbf24',
                        fillOpacity: 0.16,
                        opacity: 0.7,
                        radius: location.radius,
                        weight: 2
                    }).addTo(checkoutMap));
                }

                function createCheckoutIcon(iconClass, markerClass) {
                    return L.divIcon({
                        className: '',
                        html: `<span class="checkout-marker ${markerClass}"><i class="${iconClass}"></i></span>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                        popupAnchor: [0, -14]
                    });
                }

                function updateCurrentMarker(latitude, longitude, accuracy) {
                    initializeCheckoutMap();

                    if (!checkoutMap || !hasValidCoordinates(latitude, longitude)) {
                        return;
                    }

                    const latLng = [parseFloat(latitude), parseFloat(longitude)];

                    if (!currentMarker) {
                        currentMarker = L.marker(latLng, {
                            icon: createCheckoutIcon('ri-map-pin-user-line', 'checkout-marker-current')
                        }).addTo(checkoutMap).bindPopup('Lokasi Sekarang');
                    } else {
                        currentMarker.setLatLng(latLng);
                    }

                    currentMarker.bindPopup(`Lokasi Sekarang${accuracy ? ` (${Math.round(accuracy)}m)` : ''}`);
                    updateDistanceLine(latLng);
                    refreshCheckoutMap();
                }

                function updateDistanceLine(currentLatLng) {
                    if (!checkoutMap || !hasInitialAttendancePosition) {
                        elements.checkoutDistanceInfo.text('Jarak belum tersedia karena titik absen masuk tidak ditemukan.');
                        return;
                    }

                    const startLatLng = [initialAttendancePosition.lat, initialAttendancePosition.lng];
                    const distance = calculateDistance(
                        initialAttendancePosition.lat,
                        initialAttendancePosition.lng,
                        currentLatLng[0],
                        currentLatLng[1]
                    );
                    const distanceText = formatDistance(distance);
                    const midpoint = [
                        (initialAttendancePosition.lat + currentLatLng[0]) / 2,
                        (initialAttendancePosition.lng + currentLatLng[1]) / 2
                    ];

                    if (!distanceLine) {
                        distanceLine = L.polyline([startLatLng, currentLatLng], {
                            color: '#0f172a',
                            dashArray: '7 7',
                            opacity: 0.9,
                            weight: 3
                        }).addTo(checkoutMap);
                    } else {
                        distanceLine.setLatLngs([startLatLng, currentLatLng]);
                    }

                    if (!distanceLabel) {
                        distanceLabel = L.marker(midpoint, {
                            interactive: false,
                            icon: createDistanceIcon(distanceText)
                        }).addTo(checkoutMap);
                    } else {
                        distanceLabel.setLatLng(midpoint);
                        distanceLabel.setIcon(createDistanceIcon(distanceText));
                    }

                    elements.checkoutDistanceInfo.text(`Jarak dari lokasi absen masuk ke posisi sekarang: ${distanceText}`);
                }

                function createDistanceIcon(text) {
                    return L.divIcon({
                        className: '',
                        html: `<span class="checkout-distance-label">${text}</span>`,
                        iconSize: [1, 1],
                        iconAnchor: [0, 0]
                    });
                }

                function formatDistance(distance) {
                    if (distance >= 1000) {
                        return `${(distance / 1000).toFixed(2)} km`;
                    }

                    return `${Math.round(distance)} m`;
                }

                function refreshCheckoutMap() {
                    if (!checkoutMap) {
                        return;
                    }

                    const bounds = [];

                    if (startMarker) {
                        bounds.push(startMarker.getLatLng());
                    }

                    if (currentMarker) {
                        bounds.push(currentMarker.getLatLng());
                    }

                    if (distanceLine) {
                        bounds.push(distanceLine.getBounds().getNorthEast());
                        bounds.push(distanceLine.getBounds().getSouthWest());
                    }

                    radiusLayers.forEach(function(layer) {
                        bounds.push(layer.getBounds().getNorthEast());
                        bounds.push(layer.getBounds().getSouthWest());
                    });

                    checkoutMap.invalidateSize();

                    if (bounds.length > 1) {
                        checkoutMap.fitBounds(bounds, {
                            padding: [22, 22],
                            maxZoom: 17
                        });
                    } else if (bounds.length === 1) {
                        checkoutMap.setView(bounds[0], 16);
                    }
                }

                function requestCheckoutPosition() {
                    elements.labelMap.text('Mengambil lokasi terbaru sebelum absen pulang...').removeClass('hidden');
                    elements.checkoutSubmitBtn.prop('disabled', true).addClass('btn-disabled');

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const {
                                latitude,
                                longitude,
                                accuracy
                            } = position.coords;

                            setCoordinateInputs(latitude, longitude);
                            updateCurrentMarker(latitude, longitude, accuracy);

                            if (accuracy > MAX_GPS_ACCURACY_METERS) {
                                elements.labelMap
                                    .text(`GPS belum akurat (${Math.round(accuracy)}m). Tunggu beberapa detik lalu coba lagi.`)
                                    .removeClass('hidden');
                                elements.checkoutSubmitBtn.prop('disabled', false).removeClass('btn-disabled');
                                return;
                            }

                            if (!canSkipRadius && !isWithinAnyRadius(latitude, longitude)) {
                                elements.labelMap.text('Lokasi Anda masih di luar radius.').removeClass('hidden');
                                elements.checkoutSubmitBtn.prop('disabled', false).removeClass('btn-disabled');
                                updateButtonState(false, 'Diluar Radius!');
                                return;
                            }

                            lastValidPosition = {
                                lat: latitude,
                                lng: longitude,
                                accuracy,
                                timestamp: Date.now()
                            };

                            elements.checkoutForm[0].submit();
                        },
                        function(error) {
                            handleGeolocationError(error);
                            elements.checkoutSubmitBtn.prop('disabled', false).removeClass('btn-disabled');
                        },
                        watchOptions
                    );
                }

                function updateButtonState(isEnabled, text) {
                    if (isEnabled) {
                        elements.pulangBtn
                            .prop('disabled', false)
                            .removeClass('btn-disabled');
                        elements.pulangBtnText.html(text);
                    } else {
                        elements.pulangBtn
                            .prop('disabled', true)
                            .addClass('btn-disabled');
                        elements.pulangBtnText.html(text);
                    }
                }

                function handleGeolocationError(error) {
                    console.error("Geolocation error:", error);

                    // Show user-friendly error message based on error code
                    let errorMessage = "GPS bermasalah. ";
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += "Izinkan akses lokasi.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += "Lokasi belum tersedia.";
                            break;
                        case error.TIMEOUT:
                            errorMessage += "Mengambil lokasi terlalu lama.";
                            break;
                        default:
                            errorMessage += "Coba refresh browser.";
                    }

                    elements.labelMap.text(errorMessage).removeClass('hidden');
                    updateButtonState(false, 'GPS Tidak Tersedia');
                }

                function handleGeolocationNotSupported() {
                    alert('Browser tidak mendukung geolocation.');
                    elements.labelMap.text('Browser tidak mendukung geolocation.').removeClass('hidden');
                    updateButtonState(false, 'GPS Tidak Tersedia');
                }
                function checkWaktuSholat(lat, lng) {
                    fetch(`/api/waktu-sholat?lat=${lat}&lng=${lng}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (
                            data.sholat_sekarang &&
                            data.sholat_sekarang != 'kosong' &&
                            data.sholat_sekarang != lastPrayerName
                        ) {
                            lastPrayerName = data.sholat_sekarang;
                            $('.waktuSholat').val(data.sholat_sekarang);

                            $('#prayerText').text(
                                `Sedang memasuki waktu ${data.sholat_sekarang}`
                            );

                            $('#prayerContainer')
                                .removeClass('hidden')
                                .addClass('flex');
                        
                            startPrayerCamera();
                        }
                        if (data.sholat_sekarang === 'kosong') {
                            $('#prayerContainer')
                                .removeClass('flex')
                                .addClass('hidden');

                            stopPrayerCamera();
                            
                        }
                    })
                    .catch(error => {
                        console.error('Error waktu sholat:', error);
                    });
                }

                function startPrayerCamera() {
                    const video = document.getElementById('prayerCameraVideo');
                    if (!video || prayerStream) return;

                    navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        },
                        audio: false
                    })
                    .then(stream => {
                        prayerStream = stream;
                        video.srcObject = stream;
                        video.play();
                    })
                    .catch(err => {
                        console.error('Kamera tidak bisa diakses:', err);
                    });
                }

                function stopPrayerCamera() {
                    if (prayerStream) {
                        prayerStream.getTracks().forEach(track => track.stop());
                        prayerStream = null;
                    }
                }

                function capturePrayerPhoto() {
                    return new Promise((resolve, reject) => {
                        const video = document.getElementById('prayerCameraVideo');
                        const canvas = document.getElementById('prayerCameraCanvas');
                        const input = document.getElementById('prayerCameraInput');

                        if (!video || !canvas || !input) {
                            reject(new Error('Elemen kamera tidak ditemukan'));
                            return;
                        }

                        const width = video.videoWidth;
                        const height = video.videoHeight;

                        if (!width || !height) {
                            reject(new Error('Kamera belum siap'));
                            return;
                        }

                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, width, height);

                        canvas.toBlob((blob) => {
                            if (!blob) {
                                reject(new Error('Gagal mengambil foto'));
                                return;
                            }

                            const file = new File([blob], `sholat-${Date.now()}.jpg`, {
                                type: 'image/jpeg'
                            });

                            const dt = new DataTransfer();
                            dt.items.add(file);
                            input.files = dt.files;

                            resolve();
                        }, 'image/jpeg', 0.9);
                    });
                }

                $(document).on('submit', '#prayerForm', async function (e) {
                    e.preventDefault();

                    const btn = $(this).find('button[type="submit"]');
                    btn.prop('disabled', true);

                    try {
                        await capturePrayerPhoto();
                        stopPrayerCamera();
                        this.submit();
                    } catch (err) {
                        console.error(err);
                        btn.prop('disabled', false);
                    }
                });

                $('#closePrayerModal').click(function () {
                    $('#prayerContainer')
                        .removeClass('flex')
                        .addClass('hidden');

                    stopPrayerCamera();
                });
            })(jQuery);
        </script>
    @endif