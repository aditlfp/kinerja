<x-admin-layout :fullWidth="true">
    <div class="bg-slate-500 p-4 mx-36 shadow-md rounded-md">
		<p class="text-center text-2xl uppercase font-bold">Edit Pekerjaan</p>
		<form method="POST" action="{{ route('admin.list-pekerjaan.update', $listPekerjaan) }}" class="mx-[25%] my-10" id="form">
		    @method('PUT')
			@csrf
			<div id="formDiv" class="bg-slate-100 px-10 py-5 rounded-sm shadow-sm">
				<!-- ruangan -->
				<div class="flex flex-col">
					<label for="ruangan_id" class="label">Pilih Ruangan</label>
                    <select name="ruangan_id" id="user_id" class="select-bordered select" required>
                        <option disabled>~ Pilih Ruangan ~</option>
                        @forelse ($ruangans as $ru)
                            @if(!in_array($ru->id, array_column($listPekerjaan->toArray(), 'ruangan_id')))
                                    <option {{ $listPekerjaan->ruangan_id == $ru->id ? 'selected' : '' }} value="{{ $ru->id }}">{{ $ru->nama_ruangan }}</option>
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
		@php
            $lisA = $listPekerjaan->toJson(); // Convert the model to JSON
        @endphp
	</div>
	<script>
        $(document).ready(function() {
            
            var lisB = JSON.parse('{!! addslashes($lisA) !!}');

            // Assuming name is a JSON-encoded string representing an array
            var names = lisB.name ? JSON.parse(lisB.name) : [];
        
            // Iterate over the array using forEach
            function updateCheckboxes() {
                $('#grid input[type="checkbox"]').each(function(index) {
                    var checkboxValue = $(this).val();
                    // console.log(checkboxValue, names);
                    if (names?.includes(checkboxValue)) {
                        // If the value is in names, check the checkbox
                        $(this).prop('checked', true);
                    } else {
                        // If the value is not in names, uncheck the checkbox
                        $(this).prop('checked', false);
            
                        var correspondingTextInput = $(this).closest('.flex').find('input[type="text"]');
                        if (!correspondingTextInput.length) {
                        // Find values in names that are not in checkbox values
                        var nonMatchingValues = names?.filter(value => !$('#grid input[type="checkbox"]').map(function() {
                            return $(this).val();
                        }).get().includes(value));
        
                        // Create a new text input for each non-matching value
                        nonMatchingValues?.forEach(function(nonMatchingValue) {
                            // var newTextInput = $('<input type="text" name="name[]" class="input input-bordered" value="' + nonMatchingValue + '" placeholder="Lainnya..." style="height: 36px;"/>');
                            var newTextInput = $('<div id="divinput" class="flex items-center w-full">' +
                                '<input type="text" name="name[]" style="width: 85%; border-radius: 10px 0 0 10px;" class=" input input-bordered" value="' + nonMatchingValue + '" placeholder="Lainnya..." style="height: 36px;"/>' +
                                '<button type="button" class="btn-remove btn btn-error text-white text-lg" style="width: 15%; border-radius: 0 10px 10px 0;">&times;</button>' +
                                '</div>');
                            $('#grid').append(newTextInput);
                            $('#text_tambahan').show();
                        }.bind(this));
                    }
                    }
                });
            }
        
            // Initial update of checkboxes
            updateCheckboxes();
            
            $('#duplicateField').click(function() {
                $('#text_tambahan').show();
                
                // Clone the last checkbox
                var lastCheckbox = $('#grid input[type="checkbox"]:last');
                var clonedCheckbox = lastCheckbox.clone();
                clonedCheckbox.prop('checked', true);
        
               var newTextInput = $('<div id="divinput" class="flex items-center w-full">' +
                                '<input type="text" name="name[]" style="width: 85%; border-radius: 10px 0 0 10px;" class=" input input-bordered" placeholder="Lainnya..." style="height: 36px;"/>' +
                                '<button type="button" class="btn-remove btn btn-error text-white text-lg" style="width: 15%; border-radius: 0 10px 10px 0;">&times;</button>' +
                                '</div>');
                // Append the cloned checkbox and new text input to the grid
                $('#grid').append(newTextInput);
        
                names.push(''); // Add the value of the new checkbox

                // Update the checkboxes based on the updated names array
                updateCheckboxes();
            });
            $('#grid').on('click', '.btn-remove', function () {
                $(this).parent().remove(); // Remove the corresponding div
                // updateCheckboxes(); // Update checkboxes after removal
            });
        });
    </script>
</x-admin-layout>
