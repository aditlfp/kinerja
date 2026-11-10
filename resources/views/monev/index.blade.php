<x-app-layout>
    <x-main-div>
		<div class="py-10 px-5">
			<p class="text-center text-2xl font-bold  uppercase">Data Monev Bulan Ini</p>
			<div class="flex justify-end ">
				<div class="input flex items-center w-fit input-bordered my-10">
					<i class="ri-search-2-line"></i>
					<input type="search" id="searchInput" class="border-none rounded-sm ml-1" placeholder="Search..." required>
				</div>
			</div>
			<div class="flex justify-center overflow-x-auto mx-10 pb-10">
				<table class="table w-full shadow-md bg-slate-50" id="searchTable">
					<thead>
						<tr>
							<th class="bg-slate-300 rounded-tl-2xl">#</th>
							<th class="bg-slate-300 ">User</th>
							<th class="bg-slate-300 ">Lokasi</th>
							<th class="bg-slate-300 ">Foto</th>
							<th class="bg-slate-300 ">TGL</th>
							<th class="bg-slate-300 rounded-tr-2xl">action</th>
						</tr>
					</thead>
					<tbody class="text-sm my-10">
						@php
							$no = 1;
						@endphp
						@forelse ($monev as $i)
							<tr>
								<td>{{ $i->user->nama_lengkap }}</td>
								<td>{{ $i->kerjasama }}</td>
								<td class="flex gap-2">
									<x-btn-edit>#</x-btn-edit>
									<form action="#" method="POST">
										@csrf
										@method('DELETE')
										<x-btn-submit/>
									</form>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="10" class="text-center">~ Data Kosong ~</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="flex justify-end gap-2 mx-16 py-3">
				<a href="{{ route('dashboard.index') }}" class="btn btn-error">Kembali</a>
				<a href="{{ route('spvw-monev.create') }}" class="btn btn-primary">+ Monev</a>
			</div>
		</div>

	</x-main-div>
</x-app-layout>