<x-admin-layout :fullWidth="true">
		<div class="py-10 px-5">
			<h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data QR Code</h1>
			<div class="flex justify-end ">
				<div class="input flex items-center w-fit input-bordered my-10">
					<i class="ri-search-2-line"></i>
					<input type="search" id="searchInput" class="border-none rounded ml-1" placeholder="Search..." required>
				</div>
			</div>
			
			<!--Form Export-->
			<form action="{{ route('qrcode.export')}}" id="exportForm" method="POST" class="flex justify-between items-center">
			    @csrf
				<div class="flex justify-end gap-2 mx-16 py-3">
					<a href="{{ route('admin.index') }}" class="btn btn-error">Kembali</a>
					<a href="{{ url('admin/qrcode/create') }}" class="btn btn-primary">+ NEW QR</a>
				</div>
				<div class="flex items-end justify-end mx-10 mb-2 gap-x-3">
    			    <div class="flex flex-col gap-2">
    			        <span class="text-white text-sm font-semibold">Filter Mitra</span>
                        <select name="kerjasama_id" id="kerjasama_id" class="select-bordered select">
                            <option selected disabled>~ Select Kerjasama~</option>
                            @forelse ($kerjasama as $ke)
                             <option name="kerjasama_id" value="{{ $ke->id }}">{{ $ke->client?->name }}</option>
                            @empty
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('kerjasama_id')" class="mt-2" />
                    </div>
                        <div class="flex flex-col gap-2">
                            <span class="text-white text-sm font-semibold">Metode Export</span>
                            <select name="type_export" class="select-bordered select">
                                <option value="0" selected>Export Per Mitra</option>
                                <option value="1">Export Per Ceklist</option>
                            </select>
                        </div>
				    <button type="submit" class="bg-yellow-400 px-3 flex gap-x-2 items-center py-2 h-fit shadow rounded-md text-2xl">
						<i class="ri-file-download-line"></i>
						<span class="text-sm font-semibold">Export PDF</span>
				    </button>
				</div>
			</form>
			
			<div class="flex justify-center overflow-x-auto mx-10 pb-10">
				<table class="table table-fixed table-zebra w-full shadow-md bg-slate-50" id="searchTable">
					<thead>
						<tr>
						    <th class="bg-slate-300 rounded-tl-2xl" style="width: 136px;">
						        <div class="flex items-center flex-row gap-x-3">
						            <input id="selectAllCheckbox" type="checkbox" class="checkbox checkbox-bordered"/><span>Pilih Semua</span>
						        </div>
						    </th>
							<th class="bg-slate-300" style="width: 20px;">#</th>
							<th class="bg-slate-300 text-center">QR Code</th>
							<th class="bg-slate-300">Mitra</th>
							<th class="bg-slate-300">Ruangan</th>
							<th class="bg-slate-300 rounded-tr-2xl" style="width: 108px;">Action</th>
						</tr>
					</thead>
					<tbody class=" text-sm my-10">
						@php
							$no = 1;
						@endphp
						@forelse ($qr as $i)
							<tr>
							    <td>
							        <input type="checkbox" name="selected_items[]" class="checkbox checkbox-bordered" value="{{ $i->id }}">
							    </td>
							    
							    <td>{{ $no++ }}.</td>
							    <td class="flex justify-center"> <img src="{{asset('storage/images/'. $i->qr_code )}}"/></td>
							    
							    <td>{{ $i->kerjasama ? $i->kerjasama->client->name : 'Kosong' }}</td>
							    <td>{{ $i->ruangan?->nama_ruangan }}</td>
							    <td>
                                <form action="{{ url('admin/qrcode/' . $i->id) }}" method="POST" class="h-9">
									@csrf
									@method('DELETE')
									<x-btn-edit>{{ url('admin/qrcode/' . $i->id . '/edit') }}</x-btn-edit>
									<x-btn-submit></x-btn-submit>
								</form>
                            </td>
							</tr>
						@empty
							<tr>
								<td colspan="5" class="text-center">~ Data Kosong ~</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
				<div class="mt-5 mx-10">
					{{ $qr->links() }}
				</div>
			
		</div>
    	<script>
        $(document).ready(function () {
            var selectedItems = []; // Variable to store selected items
        
            // Listen for changes in the checkboxes
            $('input[name="selected_items[]"]').on('change', function () {
                if (this.checked) {
                    // If checkbox is checked, add the value to the array
                    selectedItems.push($(this).val());
                } else {
                    // If checkbox is unchecked, remove the value from the array
                    selectedItems = $.grep(selectedItems, function (value) {
                        return value !== $(this).val();
                    }.bind(this));
                }
        
                // Log or use selectedItems as needed
                console.log(selectedItems);
            });
            
             // Listen for changes in the "Select All" checkbox
                $('#selectAllCheckbox').on('change', function () {
                    var isChecked = $(this).prop('checked');
                    
                    // Update the state of all checkboxes
                    $('input[name="selected_items[]"]').prop('checked', isChecked);
            
                    // If "Select All" is checked, add all values to the selectedItems array
                    if (isChecked) {
                        selectedItems = $('input[name="selected_items[]"]').map(function () {
                            return $(this).val();
                        }).get();
                    } else {
                        selectedItems = [];
                    }
            
                    // Log or use selectedItems as needed
                    console.log(selectedItems);
                });
        
            // You can use the selectedItems array as needed, for example, send it with a form submission
            $('#exportForm').on('submit', function () {
                // Remove the value attribute from the existing hidden input, if it exists
                $('input[name="selected_items"]').removeAttr('value');
        
                // Create hidden inputs for each selected item
                selectedItems.forEach(function (value) {
                    var hiddenInput = $('<input>').attr({
                        type: 'hidden',
                        name: 'selected_items[]',
                        value: value
                    });
                    $(this).append(hiddenInput);
                }.bind(this));
            });
        });

    </script>

</x-admin-layout>