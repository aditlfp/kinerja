<x-app-layout>
		<div class="min-h-screen bg-slate-50 px-4 py-5 text-slate-800">
			<div class="mx-auto flex w-full max-w-2xl items-center justify-between">
				<a href="{{ route('dashboard.index') }}">
					<img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32">
				</a>
				<span class="font-semibold text-sm text-slate-500/50">Profile Page</span>
			</div>
			<div class="mx-auto w-full max-w-2xl py-8">
				<p class="mb-5 border-l-4 border-sky-500 pl-3 text-xl font-black">Edit Profil Saya</p>
			<form action="{{ route('profile.update', $dataUser->id) }}" method="POST" enctype="multipart/form-data">
				@method('PUT')
				@csrf
				<div class="overflow-hidden rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-200">
					<div class="rounded-xl bg-slate-50 px-5 py-5">
						<!-- Nama lengkap -->
						<div>
							<x-input-label for="nama_lengkap" :value="__('Nama Lengkap')" />
							<x-text-input id="nama_lengkap" class="block mt-1 w-full" type="text" name="nama_lengkap"
								value="{{ $dataUser->nama_lengkap }}" required autofocus readonly autocomplete="nama_lengkap" />
							<x-input-error :messages="$errors->get('nama_lengkap')" class="mt-2" />
						</div>
						<!-- Email Address -->
						<div class="mt-4">
							<x-input-label for="email" :value="__('Email')" />
							<x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
								value="{{ $dataUser->email }}" required autocomplete="username" />
							<x-input-error :messages="$errors->get('email')" class="mt-2" />
						</div>
						<!-- NIK -->
						<div class="mt-4">
							<x-input-label for="NIK" :value="__('NIK')" />
							<x-text-input id="NIK" class="block mt-1 w-full" type="text" name="nik"
								value="{{ $dataUser->nik ? \Illuminate\Support\Facades\Crypt::decryptString($dataUser->nik) : ''}}" placeholder="Nik..." maxlength="16"  pattern="[0-9]*" autocomplete="nik" />
							<x-input-error :messages="$errors->get('nik')" class="mt-2" />
						</div>
						<!-- No HP -->
						<div class="mt-4">
							<x-input-label for="no_hp" :value="__('No. HP Aktif')" />
							<x-text-input id="no_hp" class="block mt-1 w-full" type="text" name="no_hp"
								value="{{ $dataUser->no_hp }}" placeholder="No hp aktif..." maxlength="14" autocomplete="no_hp" />
							<x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
						</div>
						
						{{-- foto Profile --}}
						<div class="md:mt-4 p-1">
                                <x-input-label for="foto Profil" :value="__('Foto Profil (Rekom rasio 1:1)')" />
                                <div class="preview hidden w-full">
                                    <span class="flex justify-center items-center">
                                        <label for="img" class="p-1">
                                            <img class="img1 ring-2 ring-slate-500/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                                src="" alt="" srcset="" height="120px" width="120px">
                                            
                                        </label>
                                    </span>
                                </div>
                                <label for="img"
                                    class="w-full iImage1 flex flex-col items-center justify-center rounded-md bg-slate-300/70  ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                                    <span class="p-3 flex justify-center flex-col items-center">
                                        <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                        <span class="text-xs font-semibold text-slate-700/70">+ Gambar</span>
                                        <input id="img" class="hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                            type="file" name="image" :value="old('image')" autofocus autocomplete="img" accept="image/*" />
                                    </span>
                                </label>
                                <x-input-error :messages="$errors->get('image1')" class="mt-2" />
						</div>
						<input type="text" name="oldimage" value="{{ $dataUser->image }}" class="hidden" />
					</div>
					<div class="mt-6 flex justify-center gap-2">
						<button type="submit" class="rounded-lg bg-sky-500 px-4 py-2 font-semibold text-white transition hover:bg-sky-600">Simpan</button>
						<a href="{{ route('profile.index') }}" class="rounded-lg bg-red-400 px-4 py-2 font-semibold text-white transition hover:bg-red-300 border border-red-500/20">
							Kembali
						</a>
					</div>
				</div>
			</form>
			</div>
		</div>
</x-app-layout>
