<x-app-layout>
        <div class="min-h-screen bg-slate-50 px-4 py-5 text-slate-800 sm:-mx-10">
            <div class="mx-auto flex w-full max-w-2xl items-center justify-between">
                <a href="{{ route('dashboard.index') }}"><img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32"></a>
                <span class="font-semibold text-sm text-slate-500/50">Halaman Izin</span>
            </div>
            <div class="mx-auto w-full max-w-2xl pt-8">
                <p class="mb-5 border-l-4 border-sky-500 pl-3 text-xl font-black uppercase">Halaman Izin</p>
            <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data" id="form-izin">
            @csrf
            @method('POST')
            <div class="mt-4">
                <x-input-label for="nama_lengkap" :value="__('Nama Lengkap')" />
                <input type="text" name="user_id" id="user_id" value="{{ Auth::user()->id }}" hidden>
                <x-text-input id="nama_lengkap" class="block mt-1 w-full"
                                type="text"
                                value="{{ Auth::user()->nama_lengkap }}"
                                readonly
                                required autocomplete="nama_lengkap" />
    
                <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="kerjasama_id" :value="__('Bermitra dengan')" />
                <input type="text" name="kerjasama_id" id="kerjasama_id" value="{{ Auth::user()->kerjasama_id }}" hidden>
                <x-text-input id="kerjasama_id" class="block mt-1 w-full"
                                type="text"
                                value="{{ Auth::user()->kerjasama->client->name }}"
                                readonly
                                required autocomplete="kerjasama_id" />
                <x-input-error :messages="$errors->get('kerjasama_id')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="shift_id" :value="__('Pilih shift')" />
                <select name="shift_id" id="shift_id" class="select select-bordered w-full font-thin">
                    <option  disabled selected>-- Pilih Shift --</option>
                    @forelse ($shift as $i)
                    @if(Auth::user()->kerjasama->client_id == $i->client_id )
                    @if(Auth::user()->devisi_id == $i->jabatan->divisi_id)
                        <option value="{{ $i->id }}" > {{$i->jabatan->name_jabatan}} | {{ $i->shift_name }} | {{ $i->jam_start }}</option>
                        @else
                        @endif
                    @else
                    @endif
                    @empty
                        <option>~ Tidak ad Shift ! ~</option>
                    @endforelse
                </select>
                <x-input-error :messages="$errors->get('shift_id')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="alasan_izin" :value="__('Alasan izin')" />
                <textarea name="alasan_izin" id="alasan_izin"  rows="3" class="textarea textarea-bordered w-full" placeholder="Masukkan Alasan izin..."></textarea>
                <x-input-error :messages="$errors->get('alasan_izin')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="img" :value="__('Bukti izin')" />
                <div class="preview hidden w-full ">
                    <span class="flex justify-center items-center">
                        <label for="img" class="p-1">
                            <img class="img1 ring-2 ring-slate-500/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                src="" alt="" srcset="" height="120px" width="120px">
                        </label>
                    </span>
                </div>
                    <label for="img"
                        class="w-full iImage1 flex flex-col items-center justify-center rounded-md bg-slate-50  ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-200 transition ease-in-out .2s">
                        <span class="p-3 flex justify-center flex-col items-center">
                            <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                            <span class="text-xs font-semibold text-slate-700/70">+ Gambar</span>
                            <input id="img" class="hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                type="file" name="img" :value="old('image1')" autofocus autocomplete="img" accept="image/*"/>
                        </span>
                    </label>
                <x-input-error :messages="$errors->get('img')" class="mt-2" />
            </div>
            <div class="flex justify-center sm:justify-end gap-2 mt-4">
				<button class="my-2 rounded-lg bg-sky-500 p-2 px-4 font-semibold text-white transition hover:bg-sky-600" id="btnIzin">Izin</button>
                <a href="{{ route('dashboard.index') }}" class="my-2 rounded-lg bg-slate-200 p-2 px-4 font-semibold text-slate-700 transition hover:bg-slate-300">
                    Kembali
                </a>
            </div>
            </form>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#btnIzin').click(function(){
    		    $(this).prop('disabled', true);
    		    $(this).text('Tunggu..');
    		    $(this).css('background-color: rgb(96 165 250 / 0.5);');
    		    $('#form-izin').submit();
    		})
            })
        </script>
</x-app-layout>