<x-admin-layout :fullWidth="true">
	{{-- @push('scripts')
		<link rel="stylesheet"
		href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	@endpush --}}
		@section('title', 'Data Absen Sholat')

		<div class="w-full px-2 py-6 mx-auto space-y-4 max-w-screen-2xl sm:px-3 lg:px-4">
			<section class="px-0.5">
				<h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Absen Sholat</h1>
				<p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Filter data absensi sholat, lalu unduh report berdasarkan mitra, divisi, dan rentang tanggal.</p>
			</section>

			<section class="grid gap-4 lg:grid-cols-12">
				<div class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5 lg:col-span-8">
					<h2 class="text-sm font-semibold text-gray-800">Filter Data</h2>
					<form id="filterForm" action="{{ route('admin.report-sholat.index') }}" method="GET" class="grid gap-3 mt-3 md:grid-cols-2">
						<div>
							<label for="filterKerjasama" class="block mb-1 text-xs font-semibold tracking-wide text-gray-500 uppercase">Nama Klien</label>
							<select
								name="filterKerjasama"
								id="filterKerjasama"
								class="w-full h-10 px-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
							>
								<option selected disabled>~ Nama Klien ~</option>
								@foreach ($absenSi as $i)
									<option value="{{ $i->id }}" {{ $filter == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
								@endforeach
							</select>
						</div>
						<div>
							<label for="filterDevisi" class="block mb-1 text-xs font-semibold tracking-wide text-gray-500 uppercase">Divisi</label>
							<select
								name="filterDevisi"
								id="filterDevisi"
								class="w-full h-10 px-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
							>
								<option selected disabled>~ Devisi ~</option>
								@foreach ($divisi as $i)
									<option value="{{ $i->id }}" {{ $filterDivisi == $i->id ? 'selected' : '' }}>{{ $i->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="flex flex-wrap items-center gap-2 md:col-span-2">
							<button
								type="submit"
								class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700"
							>
								Filter
							</button>
							<a
								href="{{ route('admin.index') }}"
								class="inline-flex items-center h-10 px-4 text-sm font-semibold text-red-700 transition border border-red-200 rounded-xl bg-red-50 hover:bg-red-100"
							>
								Kembali
							</a>
						</div>
					</form>
				</div>
				<div class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5 lg:col-span-4">
					<h2 class="text-sm font-semibold text-gray-800">Pencarian Cepat</h2>
					<label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari Sesuatu..." />
                    </label>
				</div>
			</section>

			<section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
				<h2 class="text-sm font-semibold text-gray-800">Download Report</h2>
				<form method="GET" action="{{ route('admin.report-sholat.download') }}" class="grid gap-3 mt-3 lg:grid-cols-12">
					<div class="lg:col-span-3">
						<label for="selectInput" class="block mb-1 text-xs font-semibold tracking-wide text-gray-500 uppercase">Mitra</label>
						<select
							name="kerjasama_id"
							id="selectInput"
							class="w-full h-10 px-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
						>
							<option disabled selected>Pilih Mitra</option>
							@forelse ($absenSi as $i)
								<option value="{{ $i->id }}">{{ $i->client->name }}</option>
							@empty
								<option>~Kosong~</option>
							@endforelse
						</select>
					</div>
					<div class="lg:col-span-3">
						<label for="str1" class="block mb-1 text-xs font-semibold tracking-wide text-gray-500 uppercase">Tanggal Mulai</label>
						<input
							type="date"
							name="str1"
							id="str1"
							class="w-full h-10 px-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
						>
					</div>
					<div class="lg:col-span-3">
						<label for="end1" class="block mb-1 text-xs font-semibold tracking-wide text-gray-500 uppercase">Tanggal Selesai</label>
						<input
							type="date"
							name="end1"
							id="end1"
							class="w-full h-10 px-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
						>
					</div>
					<div class="lg:col-span-2">
						<label for="divisi_id" class="block mb-1 text-xs font-semibold tracking-wide text-gray-500 uppercase">Divisi</label>
						<select
							name="divisi_id"
							id="divisi_id"
							class="w-full h-10 px-3 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
						>
							<option disabled selected>Pilih Divisi</option>
							@forelse($divisi as $i)
								<option value="{{ $i->id }}">{{ $i->name }}</option>
							@empty
								<option>~Kosong~</option>
							@endforelse
						</select>
					</div>
					<div class="flex items-end lg:col-span-1">
						<button
							type="submit"
							class="inline-flex items-center justify-center w-full h-10 px-3 text-sm font-semibold text-gray-800 transition rounded-xl bg-amber-400 hover:bg-amber-500"
							title="Download report"
						>
							<i class="text-base ri-file-download-line"></i>
						</button>
					</div>
				</form>
			</section>

			<div class="hidden">
				<form method="GET" action="{{ route('admin.export') }}">
					<div class="flex items-center">
						<div>
							<input type="text" name="libur" class="input input-bordered" placeholder="Hari libur untuk semua.."/>
						</div>
						<div class="flex justify-end mx-10 mb-2 ">
							<button type="submit" class="flex flex-col items-center px-4 py-2 bg-yellow-400 rounded-md shadow">
								<i class="text-2xl ri-file-download-line"></i>
								<span class="text-sm">All</span>
							</button>
						</div>
					</div>
				</form>
			</div>

			<section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
				<div class="w-full overflow-x-auto">
					<table class="table w-full table-zebra bg-slate-50" id="searchTable">
					<thead>
						<tr>
							<th class="bg-slate-300 rounded-tl-2xl">#</th>
							<th class="bg-slate-300 ">Photo</th>
							<th class="bg-slate-300 ">Nama User</th>
							<th class="px-10 bg-slate-300">Tanggal</th>
							<th class="bg-slate-300 ">Shift</th>
							<th class="bg-slate-300 ">Client</th>
							<th class="bg-slate-300 ">Status Sholat</th>
							<th class="bg-slate-300 rounded-tr-2xl">Detail</th>
						</tr>
					</thead>
					<tbody>
						@php
							$no = 1;
						@endphp
					@forelse ($absen as $arr)
							<tr>
								<td>{{ $no++ }}</td>
								<td>
								    @if($arr->image == "no-image.jpg")
								        <img loading="lazy" class="lazy lazy-image" srcset="{{ URL::asset('/logo/no-image.jpg') }}" data-src="{{asset('storage/images/'.$arr->image)}}" alt="data-absensi-image" width="120px"/>
								    @else
								        <img loading="lazy" class="lazy lazy-image" src="{{asset('storage/images/'.$arr->image)}}" data-src="{{asset('storage/images/'.$arr->image)}}" alt="data-absensi-image" width="120px"/>
								    @endif
								</td>
								<td class="break-words whitespace-pre-line">{{ $arr->user?$arr->user->nama_lengkap : 'user_id'. ' : '. $arr->user_id . 'AKU KOSONG' }}</td>
								<td>{{ $arr->tanggal_absen }}</td>
								@if($arr->shift != null)
								<td id="mitra">{{ $arr->shift->shift_name . " | " . $arr->shift->jam_start . ' - ' . $arr->shift->jam_end }}</td>
					            @else
					            <td class="font-semibold text-red-500 break-words whitespace-pre-wrap">Shift Kosong</td>
					            @endif
								<td class="break-words whitespace-pre-line">{{ $arr->kerjasama->client->name }}</td>
								<td class="break-words whitespace-pre-line">
								    @if($arr->subuh == 1)
								        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Subuh</span>
								    @endif
								    @if($arr->dzuhur == 1)
								        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Dzuhur</span>
								    @endif
								    @if($arr->asar == 1)
								        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Ashar</span>
								    @endif
								    @if($arr->magrib == 1)
								        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Maghrib</span>
								    @endif
								    @if($arr->isya == 1)
								        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Isya</span>
								    @endif
								</td>
								<td>
									<button class="btn btn-sm btn-primary" onclick="showDetail({{ $arr->id }})">Detail</button>
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
				<div class="px-4 py-3 border-t border-gray-100 sm:px-5">
    					{{ $absen->links()}}
				</div>
			</section>
		</div>
		<div id="detailModal"
			class="fixed inset-0 z-[9999] hidden items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-md px-0 sm:px-4">
			<div class="relative w-full sm:max-w-6xl max-h-[95dvh] overflow-hidden rounded-t-[28px] sm:rounded-3xl bg-white shadow-2xl ring-1 ring-white/80 flex flex-col">

				<div class="absolute inset-x-0 top-0 h-1.5 bg-[var(--md-sys-color-primary)]"></div>

				<div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 sm:px-6 py-4">
					<div class="min-w-0">
						<h3 id="detailTitle" class="truncate text-lg font-normal leading-7 text-[var(--md-sys-color-on-surface)] sm:text-2xl">
							Nama User
						</h3>
						<p id="detailSubtitle" class="mt-1 text-sm text-slate-500">
							Tanggal • Client • Shift
						</p>
					</div>

					<button type="button" onclick="closeDetailModal()"
						class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-900">
						<i class="ri-close-line text-2xl"></i>
					</button>
				</div>

				<div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 pb-6">
					<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
						<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
							<p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Nama</p>
							<p id="detailNama" class="mt-1 text-sm font-bold text-slate-900">-</p>
						</div>
						<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
							<p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Tanggal</p>
							<p id="detailTanggal" class="mt-1 text-sm font-bold text-slate-900">-</p>
						</div>
						<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
							<p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Client</p>
							<p id="detailClient" class="mt-1 text-sm font-bold text-slate-900">-</p>
						</div>
						<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
							<p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Shift</p>
							<p id="detailShift" class="mt-1 text-sm font-bold text-slate-900">-</p>
						</div>
					</div>

					<div class="mt-5 space-y-4">
						<div id="detailPrayerCards"
							class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
							<!-- cards sholat diisi dari JS -->
						</div>
					</div>
				</div>
			</div>
		</div>
	<script>
		$(document).ready(function () {
		// Saat halaman dimuat, ambil semua elemen dengan class "lazy-image"
		var lazyImages = $('.lazy-image');
	
		// Fungsi untuk memuat gambar ketika mendekati jendela pandangan pengguna
		function lazyLoad() {
			lazyImages.each(function () {
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
	<script>
		function showDetail(id) {
			$.get(`/admin/report-sholat/${id}/detail`)
				.done(openDetailModal)
				.fail(() => {
					alert('Gagal mengambil detail data.');
				});
		}

		function openDetailModal(data) {

			$('#detailNama').text(data.nama || '-');
			$('#detailTanggal').text(data.tanggal || '-');
			$('#detailClient').text(data.client || '-');
			$('#detailShift').text(data.shift || '-');

			$('#detailTitle').text(data.nama || 'Detail Absen Sholat');

			$('#detailSubtitle').text(
				`${data.tanggal || '-'} • ${data.client || '-'} • ${data.shift || '-'}`
			);

			const prayers = [
				{ key: 'subuh', label: 'Subuh' },
				{ key: 'zuhur', label: 'Zuhur' },
				{ key: 'ashar', label: 'Ashar' },
				{ key: 'maghrib', label: 'Maghrib' },
				{ key: 'isya', label: 'Isya' }
			];

			let html = '';

			prayers.forEach((prayer) => {

				const item = data[prayer.key];

				if (!item || Number(item.status) !== 1) {
					return;
				}

				html += `
					<div class="overflow-hidden transition bg-white border border-green-200 shadow-sm rounded-3xl hover:shadow-lg">

						<div class="px-4 py-3 border-b border-green-100 bg-green-50">
							<div class="flex items-center justify-between">
								<div>
									<p class="text-xs font-bold tracking-widest text-green-600 uppercase">
										${prayer.label}
									</p>

									<h5 class="font-black text-slate-900">
										Sudah Absen
									</h5>
								</div>

								<div class="flex items-center justify-center w-10 h-10 text-white bg-green-500 rounded-2xl">
									<i class="ri-check-line"></i>
								</div>
							</div>
						</div>

						${
							item.foto
							? `
								<img
									loading="lazy"
									src="${item.foto}"
									alt="${prayer.label}"
									class="object-cover w-full h-52">
							`
							: `
								<div class="flex items-center justify-center h-52 text-slate-400 bg-slate-100">
									Tidak ada foto
								</div>
							`
						}

						<div class="p-4">

							<div class="grid grid-cols-2 gap-2 mb-3 text-xs">
								<div class="p-2 rounded-xl bg-slate-50">
									<b>Lat</b><br>
									${item.lat || '-'}
								</div>

								<div class="p-2 rounded-xl bg-slate-50">
									<b>Lng</b><br>
									${item.lng || '-'}
								</div>
							</div>

							${
								item.lat && item.lng
								? `
									<div
										class="map-container overflow-hidden border border-slate-200 rounded-2xl"
										data-lat="${item.lat}"
										data-lng="${item.lng}">
										
										<div class="flex items-center justify-center h-40 text-sm bg-slate-100 text-slate-500">
											Memuat peta...
										</div>
									</div>

									<a
										href="https://maps.google.com/?q=${item.lat},${item.lng}"
										target="_blank"
										class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 mt-3 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">

										<i class="ri-map-pin-line"></i>
										Buka Maps
									</a>
								`
								: `
									<div class="flex items-center justify-center h-40 rounded-2xl bg-slate-100 text-slate-400">
										Lokasi tidak tersedia
									</div>
								`
							}
						</div>
					</div>
				`;
			});

			if (!html) {
				html = `
					<div class="col-span-full">
						<div class="p-10 text-center border border-dashed rounded-3xl border-slate-300 bg-slate-50">
							<i class="text-5xl ri-inbox-line text-slate-300"></i>

							<p class="mt-3 font-semibold text-slate-500">
								Belum ada data absen sholat
							</p>
						</div>
					</div>
				`;
			}

			$('#detailPrayerCards').html(html);

			$('#detailModal')
				.removeClass('hidden')
				.addClass('flex');

			// Load map setelah modal tampil
			setTimeout(loadMaps, 250);
		}

		function loadMaps() {

			document.querySelectorAll('.map-container').forEach(container => {

				const lat = container.dataset.lat;
				const lng = container.dataset.lng;

				if (!lat || !lng) return;

				container.innerHTML = `
					<iframe
						loading="lazy"
						class="w-full h-40"
						src="https://maps.google.com/maps?q=${lat},${lng}&z=16&output=embed">
					</iframe>
				`;
			});
		}

		function closeDetailModal() {
			$('#detailModal')
				.removeClass('flex')
				.addClass('hidden');

			// bersihkan iframe supaya RAM browser tidak menumpuk
			$('.map-container').html('');
		}
	</script>
</x-admin-layout>
