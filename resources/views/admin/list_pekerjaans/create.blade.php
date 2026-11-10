<x-admin-layout :fullWidth="true">
    <div class="bg-slate-500 p-4 mx-36 shadow-md rounded-md">
		<p class="text-center text-2xl uppercase font-bold">Tambah Pekerjaan</p>
		<form method="POST" action="{{ route('admin.list-pekerjaan.store') }}" class="mx-[25%] my-10" id="form">
			@csrf
			<div id="formDiv" class="bg-slate-100 px-10 py-5 rounded-sm shadow-sm">
				<!-- ruangan -->
				<div class="flex flex-col">
					<label for="ruangan_id" class="label">Pilih Ruangan</label>
                    <select name="ruangan_id" id="user_id" class="select-bordered select" required>
                        <option disabled selected>~ Pilih Ruangan ~</option>
                        @forelse ($ruangan as $ru)
                            @if(!in_array($ru->id, array_column($listPekerjaans->toArray(), 'ruangan_id')))
                                    <option value="{{ $ru->id }}">{{ $ru->nama_ruangan }}</option>
                                @endif
                        @empty
                            <option disabled>~ Data Kosong ~</option>
                        @endforelse
                    </select>
				</div>
			    <!-- nama -->
				<div id="divName" class="flex flex-col">
					<label for="name" class="label">Nama Pekerjaan</label>
                        <div id="grid" class="grid gap-2" style="grid-template-columns: repeat(2, 1fr);">
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan1" value="Sweeping"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan1">Sweeping</label>
							</div>
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan2" value="Mopping"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan2">Mopping</label>
							</div>
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan3" value="Dusting"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan3">Dusting</label>
							</div>
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan3" value="Glass Cleaning"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan3">Glass Cleaning</label>
							</div>
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan3" value="Toilet Cleaning"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan3">Toilet Cleaning</label>
							</div>
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan3" value="Ceiling"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan3">Ceiling</label>
							</div>
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan3" value="Tempat Sampah"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan3">Tempat Sampah</label>
							</div>
							<div>
								<input type="checkbox" name="name[]" id="pekerjaan3" value="Periodik"
									class="checkbox checkbox-sm m-2">
								<label for="pekerjaan3">Periodik</label>
							</div>
							<p id="text_tambahan" class="text-center font-semibold" style="display: none; grid-column: span 2;">~ Lainnya ~</p>
						</div>
				</div>

				<div class="flex gap-2 my-5 justify-end">
				    <button id="duplicateField" type="button" class="btn btn-info">+ Input</button>
					<button><a href="{{ route('admin.list-pekerjaan.index') }}" class="btn btn-error">Kembali</a></button>
					<button type="submit" class="btn btn-primary">Simpan</button>
				</div>
			</div>
		</form>
	</div>
	<script>
        $(document).ready(function() {
            
            // Event listener for the duplicate button
            $('#duplicateField').click(function() {
                $('#text_tambahan').show();
                var lastCheckbox = $('#grid input[type="checkbox"]:last');
                    lastCheckbox.val('');
                    // lastCheckbox.prop('checked', true);
                    var clonedCheckbox = lastCheckbox.clone();
                    clonedCheckbox.prop('checked', true).prop('name', '');

                    // Create a new text input
                    var newTextInput = $('<div id="divinput" class="flex items-center w-full">' +
                                '<input type="text" name="name[]" style="width: 85%; border-radius: 10px 0 0 10px;" class=" input input-bordered" placeholder="Lainnya..." style="height: 36px;"/>' +
                                '<button type="button" class="btn-remove btn btn-error text-white text-lg" style="width: 15%; border-radius: 0 10px 10px 0;">&times;</button>' +
                                '</div>');
                    // Append the cloned checkbox and new text input to the grid
                    $('#grid').append(newTextInput);
            });
            $('#grid').on('click', '.btn-remove', function () {
                $(this).parent().remove(); // Remove the corresponding div
                updateCheckboxes(); // Update checkboxes after removal
            });
        });
    </script>
</x-admin-layout>
