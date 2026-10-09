<x-app-layout>
    <x-main-div>
    <div class="bg-slate-500 shadow-md rounded-md mt-5">
		<p class="text-center text-2xl uppercase font-bold">Validasi Monev</p>
		<form method="POST" action="{{ route('admin.jabatan.store') }}" class="m-5" id="form">
			@csrf
			<div class="bg-slate-100 px-10 py-5 rounded-sm shadow-sm">
			    <!-- foto -->
			    <div class="flex flex-col">
			        <div class="flex flex-col  sm:m-0 items-center  justify-center">
					    <div class="relative">
						    <video id="video" style="scale: 100%; padding: 0.5rem;" class="bg-slate-200 rounded-md square-video" autoplay></video>
					    </div>
						<canvas id="canvas"  style="display:none;"></canvas>
						<div id="results" class=" sm:mt-0 rounded-sm mb-3"></div>
						
						@if($errors->image)
						    <!--<p class=" font-bold bg-white text-start p-1 rounded-lg" style="color: red">Foto Tidak Boleh Kosong</p>-->
						@endif
					</div>

					<div class="flex justify-center">
						<button type=button id="snapButton" class="p-2 my-2 px-3 mb-5 text-white bg-blue-400 rounded-full"><i
								class="ri-camera-fill"></i></button>
					</div>
			    </div>
				<!-- jabatan -->
				<div class="flex flex-col">
					<label for="code_jabatan" class="label">Nama Lengkap</label>
                    <input type="hidden" name="user_id" value="{{ Auth::user()->id }}" >
                    <input type="text" value="{{ Auth::user()->nama_lengkap }}" class="input input-bordered disabled" readonly >
				</div>
				<!-- tipe jab -->
				<div class="flex flex-col">
					<label for="code_jabatan" class="label">Lokasi Monev</label>
                    <input type="hidden" id="kerjasama_id" name="kerjasama_id" value="" >
                    <input type="text" id="kerjasama_name" value="" class="input input-bordered disabled" readonly >
				</div>
				<div class="flex gap-2 my-5 justify-end">
					<button><a href="{{ route('admin.shift.index') }}" class="btn btn-error">Back</a></button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</div>
		</form>
	</div>
	<!-- Configure a few settings and attach camera -->
        <script defer>
        	$(document).ready(function() {
            // Mendapatkan elemen video
        	var video = document.getElementById('video');
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d', { willReadFrequently: true });
            var isLeadr = {!! json_encode(Route::currentRouteName() == 'absensi-karyawan-co-cs.index' || Route::currentRouteName() == 'absensi-karyawan-co-scr.index') !!};
            // console.log(isLeadr);
            
            // Mengatur ukuran canvas sesuai opsi
            canvas.width = 320;
            canvas.height = 240;
        
            // Mengonfigurasi constraints untuk mendapatkan akses kamera
            var constraints = {
                audio: false,
                video: { facingMode: isLeadr ? 'environment' : 'user', width: 450, height: 450 }
            };
        	   //console.log(navigator.mediaDevices.getUserMedia(constraints));
        
            // Mengambil akses kamera
            navigator.mediaDevices.getUserMedia(constraints)
            .then(function(mediaStream) {
                // Menampilkan video dari kamera ke elemen video
                video.srcObject = mediaStream;
                video.onloadedmetadata = function(e) {
                    // $('.svg-icon-foto').show();
        			canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
        			//console.log(canvas.width)
                    video.play();
                    checkVideoStatus();
                    // Memeriksa status video setiap beberapa detik
                    setInterval(function() {
                        checkVideoStatus();
                    }, 1); // Memeriksa setiap 2 detik, sesuaikan jika diperlukan
                };
        
            })
            .catch(function(err) {
                console.log('Gagal mengambil akses kamera: ' + err);
            });
        	
        	 function detectColor(data, colorThreshold) {
                var colorPixels = 0;
                for (var i = 0; i < data.length; i += 4) {
                    var red = data[i];
                    var green = data[i + 1];
                    var blue = data[i + 2];
        
                    // Periksa apakah warna piksel sesuai dengan warna yang ditetapkan
                    if (red > colorThreshold.red && green < colorThreshold.green && blue < colorThreshold.blue) {
                        colorPixels++;
                    }
                }
                return colorPixels;
            }
        	
        	// Fungsi untuk mengambil snapshot
            function takeSnapshot() {
        
                // Menggunakan ukuran yang sama dengan elemen video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
        
                // Menggambar video pada canvas
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
                // Mengubah gambar menjadi URL data
                var dataURL = canvas.toDataURL('image/jpeg', 0.8);
                $('.image-tag').val(dataURL)
        
                // Mengirim dataURL ke backend atau melakukan hal lain sesuai kebutuhan Anda
                //console.log(dataURL);
        		document.getElementById('results').innerHTML = '<img id="imgprev" width="200" height="200" class="rounded-md" src="' + dataURL + '"/>';
            }
        	
        	$('#snapButton').click(function() {
        		takeSnapshot();
        	});
        	 
        
            // Fungsi untuk memeriksa status video
            function checkVideoStatus() {
                // Membuat elemen canvas untuk memproses gambar dari video
                    
                    // canvas.width = video.videoWidth;
                    // canvas.height = video.videoHeight;
                    
                    canvas.width = 450;
                    canvas.height = 450;
                    
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    // console.log(video);           
            
                    // Mengambil data piksel dari gambar
                    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    var data = imageData.data;
            
                    // Menghitung jumlah piksel yang berwarna hitam (gelap)
                    var blackPixels = 0;
            		var redPixels = 0;
                    var purplePixels = 0;
                    var darkBluePixels = 0;
                    for (var i = 0; i < data.length; i += 4) {
                        // Mengecek apakah nilai rata-rata warna piksel cukup rendah (mungkin warna hitam)
                        var avgColor = (data[i] + data[i + 1] + data[i + 2]) / 3;
                        if (avgColor < 20) { // Sesuaikan nilai ambang batas sesuai kebutuhan
                            blackPixels++;
                        }
                    }
            		
            		var redPixels = 0;
                    var purplePixels = 0;
                    var darkBluePixels = 0;
            
                    // Ambang batas warna
                    var colorThresholds = {
                        red: 150,
                        green: 100,
                        blue: 100
                    };
                    // Memanggil fungsi detectColor untuk warna merah
                    redPixels = detectColor(data, colorThresholds);
            
                    // Mengganti ambang batas warna untuk warna ungu
                    colorThresholds.red = 150;
                    colorThresholds.green = 100;
                    colorThresholds.blue = 150;
            
                    // Memanggil fungsi detectColor untuk warna ungu
                    purplePixels = detectColor(data, colorThresholds);
            
                    // Mengganti ambang batas warna untuk warna biru tua
                    colorThresholds.red = 100;
                    colorThresholds.green = 100;
                    colorThresholds.blue = 150;
                    // Memanggil fungsi detectColor untuk warna biru tua
                    darkBluePixels = detectColor(data, colorThresholds);
            
                    // Memeriksa apakah terlalu banyak warna yang terdeteksi
                    if (redPixels / (canvas.width * canvas.height) > 0.2 ||
                        purplePixels / (canvas.width * canvas.height) > 0.2 ||
                        darkBluePixels / (canvas.width * canvas.height) > 0.2) {
                        alert('Terlalu banyak warna terdeteksi!');
            			$('#snapButton').hide()
                    }else{
            			$('#snapButton').show()
            		}
                    // Jika sebagian besar piksel adalah hitam, mungkin output kamera hitam
                    if (blackPixels > (canvas.width * canvas.height * 0.9)) { // 90% piksel hitam, sesuaikan jika diperlukan
                        alert('Output kamera hitam!');
            			$('#snapButton').hide();
            // 			$('#snapButton').prop('disabled', true);
                    }else{
            			$('#snapButton').show()
            		}
            		
            
                    // Menutup elemen canvas
                    canvas.remove();
            }
        });
        
        </script>
    	<!--Camera-->
</x-main-div>
</x-app-layout>
