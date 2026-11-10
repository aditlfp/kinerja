<x-app-layout>
    <x-main-div>
        <div class="p-5 py-10">
            <p class="text-center font-bold text-xl sm:text-2xl uppercase">Buat Laporan Harian</p>
            <form method="POST" action="{{ route('laporan.store') }}" class=" my-10" id="form-laporan"
                enctype="multipart/form-data">
                @csrf
                <div class="bg-slate-100 px-5 py-5 rounded-sm shadow-sm">
                    {{-- client --}}
                    <div class="my-5">
                        <x-input-label for="client_id" :value="__('Mitra')" />
                        <x-text-input id="client_id" class=" mt-1 w-full hidden" type="text" name="client_id"
                            value="{{ $kerjasama->client->id }}" />
                        <x-text-input id="user_id" class=" mt-1 w-full hidden" type="text" name="user_id"
                            value="{{ Auth::user()->id }}" />
                        <x-text-input readonly class="block mt-1 w-full text-sm sm:text-base input input-bordered"
                            disabled type="text" value="{{ $kerjasama->client->name }}" />
                    </div>
                    {{-- ruangan --}}

                    <div class="mt-4">
                        <x-input-label for="ruangan" :value="__('Ruangan')" />
                        <x-text-input id="ruangan_id" class=" mt-1 w-full hidden" type="text" name="ruangan_id"
                            value="{{ $ruangan?->id }}" />
                        <x-text-input readonly class="block mt-1 w-full text-sm sm:text-base input input-bordered"
                            disabled type="text" value="{{ $ruangan?->nama_ruangan }}" />
                        <x-input-error :messages="$errors->get('ruangan_id')" class="mt-2" />
                    </div>

                    {{-- belum --}}
                    <div class="my-5 p-1">
                        <x-input-label for="sebelum" :value="__('Foto')" />
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
                                <input id="img"
                                    class="hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                    type="file" name="image1" :value="old('image1')" autofocus autocomplete="img"
                                    accept=".gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic" />
                            </span>
                        </label>
                        <x-input-error :messages="$errors->get('image1')" class="mt-2" />
                    </div>

                    {{-- proses --}}
                    <div class="my-5 p-1">
                        <x-input-label for="proses" :value="__('Foto')" />
                        <div class="preview2 hidden w-full">
                            <span class="flex justify-center items-center">
                                <label for="img2" class="p-1">
                                    <img class="img2 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                        src="" alt="" srcset="" height="120px" width="120px">

                                </label>
                            </span>
                        </div>
                        <label for="img2"
                            class="w-full iImage2 flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                            <span class="p-3 flex justify-center flex-col items-center">
                                <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                <span class="text-xs font-semibold text-slate-700/70">+ Gambar</span>
                                <input id="img2"
                                    class="hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                    type="file" name="image2" :value="old('image2')" autofocus autocomplete="img2"
                                    accept=".gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic" />
                            </span>
                        </label>
                    </div class="my-5">

                    {{-- sudah --}}
                    <div class="p-1">
                        <x-input-label for="sesudah" :value="__('Foto')" />
                        <div class="preview3 hidden w-full">
                            <span class="flex justify-center items-center">
                                <label for="img3" class="p-1">
                                    <img class="img3 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                        src="" alt="" srcset="" height="120px" width="120px">

                                </label>
                            </span>
                        </div>
                        <label for="img3"
                            class="w-full iImage3 flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                            <span class="p-3 flex justify-center flex-col items-center">
                                <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                <span class="text-xs font-semibold text-slate-700/70">+ Gambar</span>
                                <input id="img3"
                                    class="hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                    type="file" name="image3" :value="old('image3')" autofocus
                                    autocomplete="img3"
                                    accept=".gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic" />
                            </span>
                        </label>
                        <x-input-error :messages="$errors->get('image3')" class="mt-2" />
                    </div>
                    {{-- 4 --}}
                    <div class="p-1 my-5">
                        <x-input-label for="sesudah" :value="__('Foto')" />
                        <div class="preview4 hidden w-full">
                            <span class="flex justify-center items-center">
                                <label for="img4" class="p-1">
                                    <img class="img4 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                        src="" alt="" srcset="" height="120px" width="120px">

                                </label>
                            </span>
                        </div>
                        <label for="img4"
                            class="w-full iImage4 flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                            <span class="p-3 flex justify-center flex-col items-center">
                                <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                <span class="text-xs font-semibold text-slate-700/70">+ Gambar</span>
                                <input id="img4"
                                    class="hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                    type="file" name="image4" :value="old('image4')" autofocus
                                    autocomplete="img4"
                                    accept=".gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic" />
                            </span>
                        </label>
                        <x-input-error :messages="$errors->get('image4')" class="mt-2" />
                    </div>
                    {{-- 5 --}}
                    <div class="p-1">
                        <x-input-label for="sesudah" :value="__('Foto')" />
                        <div class="preview5 hidden w-full">
                            <span class="flex justify-center items-center">
                                <label for="img5" class="p-1">
                                    <img class="img5 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                        src="" alt="" srcset="" height="120px" width="120px">

                                </label>
                            </span>
                        </div>
                        <label for="img5"
                            class="w-full iImage5 flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                            <span class="p-3 flex justify-center flex-col items-center">
                                <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                <span class="text-xs font-semibold text-slate-700/70">+ Gambar</span>
                                <input id="img5"
                                    class="hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                    type="file" name="image5" :value="old('image5')" autofocus
                                    autocomplete="img5"
                                    accept=".gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic" />
                            </span>
                        </label>
                        <x-input-error :messages="$errors->get('image5')" class="mt-2" />
                    </div>
                    {{-- pekerjaan --}}
                    <div class="my-5">
                        <x-input-label for="pekerjaan" :value="__('Pekerjaan')" />
                        <div class="grid grid-cols-1">
                            @forelse($listPekerjaan as $lis)
                                @php
                                    // Decode the JSON string to get an array
                                    $pekerjaanArray = json_decode($lis->name);
                                @endphp
                                @if (!empty($pekerjaanArray))
                                    @foreach ($pekerjaanArray as $value)
                                        <div>
                                            <input type="checkbox" name="pekerjaan[]"
                                                id="pekerjaan{{ $loop->index + 1 }}" value="{{ $value }}"
                                                class="checkbox checkbox-sm m-2">
                                            <label for="pekerjaan{{ $loop->index + 1 }}">{{ $value }}</label>
                                        </div>
                                    @endforeach
                                @endif
                            @empty
                                <div>
                                    <p class="text-center text-sm">~ Data Masih Kosong ~</p>
                                </div>
                            @endforelse
                        </div>
                        @php
                            $errPekerjaan = $errors->get('pekerjaan') ? 'Pekerjaan Setidaknya terisi 1' : '';
                        @endphp
                        <x-input-error :messages="$errPekerjaan" class="mt-2" />
                    </div>
                    {{-- nilai --}}
                    <div class="my-5 font-semibold capitalize">
                        <x-input-label for="nilai" :value="__('Nilai')" />
                        <div>
                            <input type="radio" id="baik" name="nilai" value="baik"
                                class="radio radio-sm m-2">
                            <label for="baik" class="badge badge-success overflow-hidden">baik</label>
                        </div>
                        <div>
                            <input type="radio" id="cukup" name="nilai" value="cukup"
                                class="radio radio-sm m-2">
                            <label for="cukup" class="badge badge-info overflow-hidden">cukup</label>
                        </div>
                        <div>
                            <input type="radio" id="kurang" name="nilai" value="kurang"
                                class="radio radio-sm m-2">
                            <label for="kurang" class="badge badge-error overflow-hidden">kurang</label>
                        </div>
                        <x-input-error :messages="$errors->get('nilai')" class="mt-2" />
                    </div>
                    {{-- keterangan --}}
                    <div class="my-5">
                        <x-input-label for="keterangan" :value="__('Keterangan')" />
                        <textarea name="keterangan" id="keterangan" rows="3" class="textarea textarea-bordered w-full"
                            placeholder="Keterangan laporan..."></textarea>
                        <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                    </div>
                    <div class="flex justify-center sm:justify-end gap-2">
                        <button type="button" id="submitLaporan" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('laporan.index') }}"
                            class="btn btn-error hover:bg-red-500 transition-all ease-linear .2s">
                            Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <script>
            $(document).ready(function() {
                //  console.log($('#submitLaporan'));
                $('#submitLaporan').click(function() {
                    $(this).prop('disabled', true);
                    $(this).text('Tunggu...');
                    $(this).css('background-color: rgb(96 165 250 / 0.5);');
                    $('#form-laporan').submit();
                })
            })
        </script>
    </x-main-div>
</x-app-layout>
