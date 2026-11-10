<x-admin-layout :fullWidth="true">
    <style>
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
    
        .animate-pulse {
            animation: pulse 0.7s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

        <div class="py-10 px-5">
            <div class="bg-slate-50 rounded-lg p-2">
                <p class="text-lg font-semibold text-center">Scan QR Laporan</p>
                <div id="reader" width="600px" height="600px"></div>
                <input id="#result" class="hidden" />
                <button id="openScannerBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-10 px-4 w-full rounded-sm mt-3 flex flex-col gap-2 items-center">
                    <i class="ri-camera-line"></i>
                    <p>Klik Untuk Scan</p>
                </button>
                <span class="flex flex-col justify-center items-center">
                    <button id="switchCameraBtn" class="bg-green-500 hover:bg-green-700 text-xs text-white font-bold py-2 px-4 rounded-sm mt-3 ml-3">Ubah kamera (depan)</button>
                    <a href="{{ route('dashboard.index') }}" class="btn btn-error text-white font-bold py-2 px-4 rounded-sm mt-3 ml-3">Kembali</a>
                </span>
            </div>
        </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
    $(document).ready(function() {
        const html5QrCode = new Html5Qrcode("reader");
        let currentCameraIndex = 1;
        let scannerRunning = false;
        let switchClick = 0;
        const openScannerBtn = $('#openScannerBtn');
        const switchCameraBtn = $('#switchCameraBtn');

        openScannerBtn.on('click', function() {
            if (!scannerRunning) {
                startScanner(currentCameraIndex);
                openScannerBtn.html('<span class="animate-pulse">Tunggu...</span>');
            }
        });

        
        switchCameraBtn.on('click', function() {
            
            if (scannerRunning) {
                stopScanning();
            }
            switchClick = 1 - switchClick;
            currentCameraIndex = 1 - currentCameraIndex;
            // openScannerBtn.html('<span class="animate-pulse">Loading...</span>');
            openScannerBtn.show();
            // startScanner(currentCameraIndex);
            updateCameraButtonText();
        });
        
        function updateCameraButtonText() {
            if (currentCameraIndex == 1) {
                switchCameraBtn.html('Ubah Kamera (depan)');
            } else {
                switchCameraBtn.html('Ubah Kamera (belakang)');
            }
        }

        

        function startScanner(cameraIndex) {
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length > cameraIndex) {
                    const cameraId = devices[cameraIndex].id;
                    html5QrCode.start(cameraId, {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    }, (decodedText, decodedResult) => {
                        $('#result').val(decodedText);
                        window.location.href = `/laporan/scan/${decodedText}`;
                    }, errorMessage => {
                        // console.log(`Code scan error: ${errorMessage}`);
                    });
                    scannerRunning = true;
                    setTimeout(function() {
                        openScannerBtn.toggle();
                    }, scannerRunning);
                } else {
                    console.log("No cameras available or invalid camera index.");
                }
            }).catch(err => {
                console.log(`Error getting cameras: ${err}`);
            });
        }

        function stopScanning() {
            html5QrCode.stop().then(() => {
                scannerRunning = false;
            }).catch(err => {
                console.log(`Error stopping scanning: ${err}`);
            });
        }
    });
</script>

</x-admin-layout>
