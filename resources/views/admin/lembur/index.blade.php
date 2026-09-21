<x-admin-layout :fullWidth="true">
		@if (Auth::user()->divisi->jabatan->code_jabatan == "SPV-P")
		<h1 class="py-6 text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">List Lembur {{ Auth::user()->kerjasama->client->name }}</h1>
		@else
		<h1 class="py-6 text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">List Lembur</h1>
		@endif
		<div class="overflow-x-auto mx-10 flex justify-center">
			<table class="table table-zebra w-full mb-10 bg-slate-50">
				<thead>
					<tr>
						<th class="bg-slate-300 rounded-tl-xl">#</th>
						<th class="bg-slate-300 ">Photo</th>
						<th class="bg-slate-300 ">Name</th>
						<th class="bg-slate-300 ">Deskripsi</th>
						<th class="bg-slate-300 rounded-tr-xl">Lama Lembur</th>
					</tr>

				</thead>
				<tbody>
					@php
						$no = 1;
						$prevUser = null;
					@endphp
					@forelse ($lembur as $i)
						<tr>
							<td class="py-1">{{ $no++ }}</td>
							<td><img class="lazy lazy-image" loading="lazy" src="{{asset('storage/images/'.$i->image)}}" data-src="{{asset('storage/images/'.$i->image)}}" alt="data-absensi-image" width="120px"/></td>
							<td class="py-1">{{ $i->user->nama_lengkap }}</td>
							<td class="py-1">{{ $i->deskripsi }}</td>
							@if ($i->jam_selesai == null)
								<td class="py-1">Belum Selesai Lembur</td>
							@else
							@php
								$masuk = strtotime($i->jam_mulai);
								$keluar = strtotime($i->jam_selesai);

								$msk = date('H', $masuk);
								$klr = date('H' ,$keluar);
								
								$jajal = $keluar - $masuk;
								$jajal1 = date('H',$jajal);
								
								list($jam1, $menit1) = explode(':',$i->jam_mulai);
								list($jam2, $menit2) = explode(':',$i->jam_selesai);
								
								$totalMulai = $jam1+ ($menit1 / 60);
								$totalPulang = $jam2+ ($menit2 / 60);
								$total = $totalPulang - $totalMulai;
								$jam = floor($total);
								$menit = floor(($total - $jam) * 60);
								
								if($jam <= 0){
								    if($menit <= 0){
								        $totalString = "0 Jam";
								    }else{
								        $totalString = sprintf('%02d menit', $menit);
								    }
								}else if($jam <= 0 && $menit <= 0){
								    $totalString = "0 Jam";
								}else {
								    $totalString = sprintf('%02d jam %02d menit', $jam, $menit);
								}
								
								$tot =  $klr - $msk;

							@endphp
                                @if($jam <= 0 && $menit <= 0)
                                <td class="py-1 text-red-500">{{ $totalString }}</td>
                                @else
								<td class="py-1">{{ $totalString }}</td>
								@endif
							@endif
						</tr>

					@empty
						<tr>
							<td colspan="3" class="text-center py-1">Kosong</td>
						</tr>
					@endforelse
				</tbody>
			</table>
			<div id="pag-1" class=" mb-5 mx-5">
				{{ $lembur->links() }}
			</div>
		</div>
		<div class="flex justify-end py-5 mx-5 sm:pb-10">
			<a href="{{ route('admin.index') }}" class="btn btn-error mx-2 sm:mx-10">Back</a>
		</div>
</x-admin-layout>
