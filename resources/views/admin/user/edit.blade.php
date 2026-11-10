<x-admin-layout :fullWidth="true">
    <div class="mx-10 rounded-sm bg-slate-500">
		<p class="my-10 text-2xl font-bold text-center">Edit User</p>
        <div class="px-10 py-5 mx-10 my-10 rounded-sm shadow-sm bg-slate-100">
            <span class="flex justify-end py-3 overflow-hidden">
                <button id="deleteUser" class="overflow-hidden btn btn-error">Delete</button>
            </span>
        <form action="{{ url('users/'.$dataUser->id) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        	<!-- Name -->
			<div>
				<x-input-label for="name" :value="__('Nama')" />
				<x-text-input id="name" class="block w-full mt-1" type="text" name="name" value="{{ $user->name }}" required
					autofocus autocomplete="name" />
				<x-input-error :messages="$errors->get('name')" class="mt-2" />
			</div>
			<!-- Password -->
			<div class="mt-4">
				<x-input-label for="password" :value="__('Password')" />

				<x-text-input id="password" class="block w-full mt-1" type="password" name="password"
					autocomplete="new-password" placeholder="Masukkan password user..."/>

				<x-input-error :messages="$errors->get('password')" class="mt-2" />
			</div>
        	<!-- Nama lengkap -->
			<div>
				<x-input-label for="nama_lengkap" :value="__('Nama Lengkap')" />
				<x-text-input id="nama_lengkap" class="block w-full mt-1" type="text" name="nama_lengkap" value="{{ $user->nama_lengkap }}" required
					autofocus autocomplete="nama_lengkap" />
				<x-input-error :messages="$errors->get('nama_lengkap')" class="mt-2" />
			</div>

			<!-- Email Address -->
			<div class="mt-4">
				<x-input-label for="email" :value="__('Email')" />
				<x-text-input id="email" class="block w-full mt-1" type="email" name="email" value="{{ $user->email }}" required
					autocomplete="username" />
				<x-input-error :messages="$errors->get('email')" class="mt-2" />
			</div>
			<!-- NIK -->
			<div class="mt-4">
				<x-input-label for="NIK" :value="__('NIK')" />
				<x-text-input id="NIK" class="block w-full mt-1" type="text" name="nik"
					value="{{ $user->nik ? \Illuminate\Support\Facades\Crypt::decryptString($user->nik) : ''}}" placeholder="Nik..." maxlength="16"  pattern="[0-9]*" autocomplete="nik" />
				<x-input-error :messages="$errors->get('nik')" class="mt-2" />
			</div>
			<!-- No HP -->
			<div class="mt-4">
				<x-input-label for="no_hp" :value="__('No. HP Aktif')" />
				<x-text-input id="no_hp" class="block w-full mt-1" type="text" name="no_hp"
					value="{{ $user->no_hp }}" placeholder="No hp aktif..." maxlength="14" autocomplete="no_hp" />
				<x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
			</div>
			<!-- client -->
			<div class="mt-4">
				<x-input-label for="client" :value="__('Client')" />
				<select name="kerjasama_id" id="kerjasama_id" class="w-full mt-1 select select-bordered">
					<option disabled>~ Pilih Client ~</option>
					@foreach ($kerjasama as $i)
						<option name="kerjasama_id" {{ $dataUser->kerjasama_id == $i->id ? 'selected' : '' }}  value="{{ $i->id }}" class="py-2">{{ $i?->client?->name }}</option>
					@endforeach
				</select>
			</div>
			<!-- client -->
			<div class="mt-4">
				<x-input-label for="divisi" :value="__('Divisi')" />
				<select name="devisi_id" id="devisi_id" class="w-full mt-1 select select-bordered">
					<option selected disabled>~ Pilih Devisi ~</option>
					@foreach ($dev as $i)
						<option name="devisi_id" {{ $dataUser->devisi_id == $i->id ? 'selected' : '' }} value="{{ $i->id }}" class="py-2">{{ $i->name }}</option>
					@endforeach
				</select>
			</div>
			<!-- jabatan -->
			<div class="mt-4">
				<x-input-label for="jabatan_id" :value="__('Jabatan')" />
				<select name="jabatan_id" id="" class="w-full mt-1 select select-bordered">
					<option selected disabled>~ Pilih Jabatan ~</option>
					@foreach ($jabatan as $i)
						<option name="jabatan_id" {{ $dataUser?->jabatan_id == $i->id ? 'selected' : '' }} value="{{ $i->id }}" class="py-2">{{ $i->name_jabatan }}</option>
					@endforeach
				</select>
			</div>
			{{-- foto Profile --}}
			<div class="p-4 mt-5 bg-white border border-gray-200 rounded-xl">
				<x-input-label for="img">Foto Profil</x-input-label>
				<input type="text" name="oldimage" value="{{ $user->image }}" class="hidden" />
				<div class="flex flex-col gap-3 mt-2 sm:flex-row sm:items-center">
					<div class="w-20 h-20 overflow-hidden border border-gray-200 rounded-xl bg-gray-50">
						<img
							id="imgPreview"
							src="{{ asset('storage/images/' . ($user->image ?? 'no-image.jpg')) }}"
							alt="Preview foto profil"
							class="object-cover object-center w-full h-full" />
					</div>
					<div class="flex-1">
						<input type="file" class="w-full file-input file-input-bordered" id="img" name="image" accept="image/*" />
						<p class="mt-1 text-xs text-gray-500">Format JPG/PNG. Kosongkan jika tidak ingin mengganti foto.</p>
					</div>
				</div>
				<x-input-error class="mt-2" :messages="$errors->get('image')" />
			</div>
			<div class="flex flex-wrap justify-end gap-2 mt-8">
				<a href="{{ route('admin.user.index')}}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50">
					Kembali
				</a>
				<button type="submit" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
					Simpan Perubahan
				</button>
			</div>
        </form>
    </div>
</div>
<div
	class="fixed inset-0 hidden transition-all duration-300 ease-in-out modalDeleteUser bg-slate-500/10 backdrop-blur-xs">
	<div class="p-5 mx-2 rounded-md shadow-sm bg-slate-200 w-fit">
		<div class="flex justify-end mb-3">
			<button id="close" class="scale-90 btn btn-error">&times;</button>
		</div>
		<form action="{{ route('admin.user.destroy', $dataUser->id) }}" method="POST"
			class="flex items-center justify-center ">
			@csrf
			@method('DELETE')
			<div class="flex flex-col justify-center gap-2">
				<div class="flex flex-col gap-2">
					<p class="text-lg font-semibold text-center">Apakah Anda Yakin Ingin Menghapus User {{ $dataUser->nama_lengkap }}?</p>
				</div>
				<div class="flex items-center justify-center overflow-hidden">
					<button type="submit"
						class="overflow-hidden btn btn-error"><span class="overflow-hidden font-bold">Hapus User</span>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
    $(document).ready(function() {
        // console.log({!! json_encode(Auth::user()->jabatan) !!}, {!! json_encode($dev) !!});
        $('#deleteUser').click(function(){
		    $('.modalDeleteUser').removeClass('hidden')
			.addClass('flex justify-center items-center opacity-100');
		});
		
		$('#close').click(function() {
		    $('.modalDeleteUser').addClass('hidden').removeClass('flex justify-center items-center opacity-100');
		});

		$('#img').on('change', function(e) {
			const file = e.target.files && e.target.files[0];
			if (!file) return;
			const reader = new FileReader();
			reader.onload = function(ev) {
				$('#imgPreview').attr('src', ev.target.result);
			};
			reader.readAsDataURL(file);
		});
    })
</script>
</x-admin-layout>
