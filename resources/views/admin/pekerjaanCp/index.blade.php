<x-admin-layout :fullWidth="true">
		<div class="px-5 py-10">
			<div class="mb-4 mx-10">
				<h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Index Rencana Kerja</h1>
				<p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Monitor rencana dan pekerjaan checkpoint karyawan.</p>
			</div>
			<div class="flex flex-col justify-end ">
				<div class="flex flex-col items-end justify-end ">
				<x-search />

			</div>
				<div class="flex justify-between mx-10 gap-2 py-3 items-center">
                    <div class="flex  gap-x-2">
	    			    <form action="{{ route('admin.pekerjaan-cp.import') }}" method="POST" class="flex items-center gap-2 overflow-hidden" enctype="multipart/form-data">
	    				    @csrf
	    				    <label for="iCP" class="inline-flex h-10 cursor-pointer items-center gap-1.5 rounded-sm border border-amber-200 bg-amber-50 px-4 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
									<i class="text-base ri-file-excel-2-line"></i>
									<span id="importLabel" class="overflow-hidden">Import Pekerjaan</span>
								</label>
	    				    <input id="iCP" name="file" type="file" class="hidden" accept=".xlsx"/>
	    				    <button class="inline-flex items-center hidden h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700" type="submit" id="btnImport">Import</button>
	    				</form>
                        <div class="inline-flex h-10 cursor-pointer items-center gap-1.5 rounded-sm border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 transition hover:bg-green-100">
                            <i class="ri-download-cloud-2-line text-lg"></i>
                            <a
                                href="{{ route('admin.pekerjaan.template') }}"
                            >
                                Download Template
                            </a>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 py-3">
                        <a href="{{ route('admin.index') }}" class="inline-flex h-10 cursor-pointer items-center gap-1.5 rounded-sm border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100"><i class="ri-arrow-left-s-line"></i>Back</a>
	    				<a href="{{ route('admin.pekerjaan-cp.create') }}" class="inline-flex h-10 cursor-pointer items-center gap-1.5 rounded-sm border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">+ Tambah Pekerjaan</a>
                    </div>
	    			</div>
			</div>
			<!--Handle error-->
			    @if(session('failures'))
			    <div class="mx-10 mb-5">
                    <div class="text-white border-none alert alert-error" style="background-color: #b50404">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 stroke-current shrink-0" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div class="flex mx-10">
                            <strong>Duplicate Data : </strong>
                            <ul class="ml-2">
                                <li>{{ session('failures')}}</li>
                            </ul>
                        </div>
                    </div>
			    </div>
                @endif
			<!--End Handle-->
			<div class="flex justify-center pb-10 mx-10 overflow-x-auto">
				<table class="table w-full shadow-md table-auto bg-slate-50" id="searchTable">
					<thead>
						<tr>
							<th class="bg-slate-300 rounded-tl-2xl">#</th>
							<th class="bg-slate-300 ">Nama User</th>
							<th class="bg-slate-300 ">Nama Devisi</th>
							<th class="bg-slate-300 ">Nama CLient</th>
							<th class="bg-slate-300 ">Nama Pekerjaan</th>
							<th class="bg-slate-300 ">Jenis Pekerjaan</th>
							<th class="bg-slate-300 rounded-tr-2xl">Action</th>
						</tr>
					</thead>
					<tbody class="my-10 text-sm">
						@php
							$no = 1;
						@endphp
						@forelse ($pcp as $i)
							<tr>
								<td class="max-w-[20px]">{{ $no++ }}</td>
								<td>@if(!$i->user?->nama_lengkap)
                                        <span class="text-red-500 italic line-through">Kosong</span>
                                    @else
                                        <span>{{ $i->user->nama_lengkap }}</span>
                                    @endif
                                </td>
								<td>
                                    @if(!$i->divisi?->jabatan?->name_jabatan)
                                        <span class="text-red-500 italic line-through">Kosong</span>
                                    @else
                                        <span>{{ $i->divisi?->jabatan?->name_jabatan }}</span>
                                    @endif
                                </td>
								<td>
                                    @if(!$i->kerjasama?->client?->panggilan)
                                        <span class="text-red-500 italic line-through">Kosong</span>
                                    @else
                                        <span>{{ $i->kerjasama?->client?->panggilan }}</span>
                                    @endif
                                </td>
								<td>{{ $i->name }}</td>
								<td>
                                    @if (!$i->type_check )
                                        <span class="text-red-500 italic line-through">Kosong</span>
                                    @else
                                        <span>{{ $i->type_check }}</span>
                                    @endif
                                </td>
								<td class="flex gap-1">
									<a
										href="{{ route('admin.pekerjaan-cp.edit', [$i->id]) }}"
										class="inline-flex h-8 items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100"
									>
										<i class="text-xs ri-pencil-line"></i>
									</a>
									@if(Auth::user()->role_id == 2)
										<form action="{{ route('admin.pekerjaan-cp.destroy', [$i->id]) }}" method="POST">
											@csrf
											@method('DELETE')
											<button
												type="submit"
												class="inline-flex h-8 items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-2.5 text-xs font-semibold text-red-700 transition hover:bg-red-100"
											>
												<i class="text-xs ri-delete-bin-6-line"></i>
											</button>
										</form>
									@endif
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="4" class="text-center">~ Data Kosong ~</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
				<div class="mx-10 mt-5">
					{{ $pcp->links() }}
				</div>

		</div>
    <script>
    $(document).ready(function () {
        $('#iCP').on('change', function () {
            var fileInput = $(this);
            var importLabel = $('#importLabel');
            var submitButton = $('#btnImport');

            if (fileInput.val()) {
                importLabel.text('Klik import');
                submitButton.removeClass('hidden');
            } else {
                importLabel.text('Import Pekerjaan');
                submitButton.addClass('hidden');
            }
        });
    });
</script>
</x-admin-layout>
