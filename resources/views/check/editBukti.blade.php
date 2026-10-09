<x-app-layout>
    <x-main-div>
        <div class="mx-auto w-full max-w-5xl px-3 py-5 sm:px-5 lg:px-6">
            <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs ring-1 ring-slate-900/5">
                <div class="border-b border-slate-200 bg-linear-to-br from-emerald-50 via-white to-sky-50 px-4 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-400 text-white shadow-xs ring-1 ring-emerald-300">
                                <i class="ri-camera-line text-2xl"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="mb-1 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                    <i class="ri-image-add-line"></i>
                                    Upload Bukti
                                </div>
                                <h1 class="text-xl font-bold leading-tight text-slate-900 sm:text-2xl">Kirim Bukti Pekerjaan</h1>
                                <p class="mt-1 text-sm leading-5 text-slate-600">Upload foto untuk setiap checkpoint yang dikerjakan.</p>
                            </div>
                        </div>
                        <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                            <i class="ri-arrow-left-line"></i>
                            Kembali
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('uploadBukti-checkpoint-user') }}" id="form-cp" enctype="multipart/form-data" class="p-4 sm:p-6">
                    @csrf
                    <div class="mb-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-medium text-slate-500">Nama</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ Auth::user()->nama_lengkap }}</p>
                            <input type="hidden" id="user_id" name="user_id" value="{{ Auth::id() }}">
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-medium text-slate-500">Bermitra Dengan</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ Auth::user()->kerjasama->client->name }}</p>
                            <input type="hidden" name="divisi_id" id="divisi_id" value="{{ Auth::user()->divisi->id }}">
                        </div>
                    </div>
                    <div class="space-y-3">
                    </div>
                    <div class="flex flex-col  justify-between mt-3">
                        <label class="font-semibold">Bermitra Dengan: </label>
                        <input type="text" name="divisi_id" id="divisi_id" hidden
                            value="{{ Auth::user()->divisi->id }}">
                        <input type="text" value="{{ Auth::user()->kerjasama->client->name }}" disabled
                            class="input input-bordered">
                    </div>
                    <div class="mt-3 flex flex-col gap-2">
                        <x-input-label for="type_check " :value="__('Check Point')" class="required text-center font-semibold" />
                        @foreach (['harian', 'mingguan', 'bulanan', 'isidental'] as $type)
                            @php
                                $pcpType = $pcp->whereIn('id', $cex->pekerjaan_cp_id)->where('type_check', $type);
                            @endphp
                            <span class="flex flex-col gap-1">

                                @if ($pcpType->count() >= 1)
                                    <label for="example_checkbox"
                                        class="label font-semibold">~{{ ucfirst($type) }}</label>
                                @endif
                                <div class="flex flex-col">
                                    @forelse ($pcpType as $p)
                                        <span class="flex flex-col justify-center gap-2 p-1 overflow-hidden">
                                            <label for="checkbox" style="padding-left: 10px;" class="lab"
                                                data-id="{{ $p->id }}"
                                                data-loop="{{ $loop->index }}">{{ $loop->index + 1 }}.
                                                {{ $p->name }}</label>
                                            <!---->
                                            <div class="p-1">
                                                <div class="preview_{{ $p->id }} hidden w-full">
                                                    <span class="flex justify-center items-center">
                                                        <label for="img_{{ $p->id }}" class="p-1">
                                                            <img class="img_{{ $p->id }} ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                                                src="" alt="" srcset=""
                                                                height="120px" width="120px">
                                                        </label>
                                                    </span>
                                                </div>
                                                <label for="img_{{ $p->id }}"
                                                    class="w-full iImage_{{ $p->id }} flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                                                    <span class="p-2 flex justify-center items-center">
                                                        <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                                        <span class="text-xs font-semibold text-slate-700/70">+
                                                            Gambar</span>
                                                        <input id="img_{{ $p->id }}"
                                                            data-pcp_id="{{ $p->id }}"
                                                            class="input_img_{{ $p->id }} hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                                            type="file" name="img[]"
                                                            accept=".gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic" />
                                                    </span>
                                                </label>
                                                <div class="hidden too_big_{{ $p->id }}">
                                                    <p style="color: red;">*Gambar Terlalu Besar!!! (Max 5 Mb)</p>
                                                </div>
                                            </div>
                                            <!---->
                                            <div class="my-2">
                                                <textarea name="deskripsi[]" id="deskripsi_{{ $p->id }}" rows="1"
                                                    class="textarea textarea-bordered w-full" placeholder="Deskripsi laporan..."></textarea>
                                                {{-- <textarea name="note[]" id="note" rows="1" class="textarea textarea-bordered w-full hidden" placeholder="Deskripsi laporan..."></textarea> --}}
                                                <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                                            </div>
                                        </span>
                                    @empty
                                        {{-- <span><p class="text-center">~Pekerjaan Tidak Tersedia~</p></span> --}}
                                    @endforelse
                                </div>
                            </span>
                        @endforeach
                        <span>
                            @if (!empty($cex->pekerjaan_cp_id))
                                @foreach ($cex->pekerjaan_cp_id as $i => $item)
                                    @php
                                        $ce = $pcp->where('id', $item)->first();
                                    @endphp
                                    @if (empty($ce))
                                        @if ($item)
                                            <p id="tambahan_label" class="text-center font-semibold my-1">~ Tambahan ~
                                            </p>
                                        @endif
                                        <label for="checkbox" style="padding-left: 10px;" class="lab"
                                            data-id="{{ str_replace(' ', '_', $item) }}"
                                            data-loop="{{ $i }}">{{ $i + 1 }}.
                                            {{ $item }}</label>
                                        <div class="p-1">
                                            <div class="preview_{{ str_replace(' ', '_', $item) }} hidden w-full">
                                                <span class="flex justify-center items-center">
                                                    <label for="img_{{ str_replace(' ', '_', $item) }}" class="p-1">
                                                        <img class="img_{{ str_replace(' ', '_', $item) }} ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                                            src="" alt="" srcset="" height="120px"
                                                            width="120px">
                                                    </label>
                                                </span>
                                            </div>
                                            <label for="img_{{ str_replace(' ', '_', $item) }}"
                                                class="w-full iImage_{{ str_replace(' ', '_', $item) }} flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                                                <span class="p-2 flex justify-center items-center">
                                                    <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                                    <span class="text-xs font-semibold text-slate-700/70">+
                                                        Gambar</span>
                                                    <input id="img_{{ str_replace(' ', '_', $item) }}"
                                                        data-pcp_id="{{ str_replace(' ', '_', $item) }}"
                                                        class="input_img_{{ $p->id }} hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                                        type="file" name="img[]" value="null" autofocus
                                                        accept="image/*" />
                                                </span>
                                            </label>
                                            <div class="hidden too_big_{{ str_replace(' ', '_', $item) }}">
                                                <p style="color: red;">*Gambar Terlalu Besar!!! (Max 5 Mb)</p>
                                            </div>
                                        </div>
                                        <div class="my-2">
                                            <textarea name="deskripsi[]" id="deskripsi" rows="1" class="textarea textarea-bordered w-full"
                                                placeholder="Deskripsi laporan..."></textarea>
                                            <textarea name="note[]" id="note" rows="1" class="textarea textarea-bordered w-full hidden"
                                                placeholder="Deskripsi laporan..."></textarea>
                                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                                        </div>
                                        {{-- <span class="flex items-center gap-2 p-1 overflow-hidden">
										<input type="checkbox" checked  name="pekerjaan_id[]" value="{{ $item }}" id="isidental" class="checkbox">
										<label for="checkbox">{{ $item }}</label>
									</span> --}}
                                    @endif
                                @endforeach
                            @endif
                        </span>
                        <x-input-error :messages="$errors->get('type_check')" class="mt-2" />
                    </div>

                    <!--<div class="flex justify-end">-->
                    <!--    <button id="addMoreCP" type="button" class="btn btn-sm btn-warning">+ Tambahan</button>-->
                    <!--</div>-->
                    <!--<div id="divMoreCP" class="flex flex-col gap-2 hidden">-->

                    <!--</div>-->

                    <span class="hidden">
                        <p class="text-center">~ Lokasi ~</p>
                        <span class="flex justify-center join lokasi">

                        </span>
                    </span>
                    <div class="hidden" id="pcp_container">
                        <input name="cpId" value="{{ $cId }}" type="hidden" />
                    </div>
                </div>
                <div class="flex justify-center sm:justify-end gap-2 mt-10">
                    <button type="button" id="btnSubmit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('dashboard.index') }}"
                        class="btn btn-error hover:bg-red-500 transition-all ease-linear .2s">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
        {{-- Leaflet --}}
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />



        <script></script>
        <script>
            $(document).ready(function() {

                $('#btnSubmit').click(function() {
                    $(this).attr('disabled', true);
                    $(this).html('Tunggu...');
                    $('#form-cp').submit();
                });

                let checkedCount = 0;
                var checkedCheckboxes = $('.lab');
                checkedCount = checkedCheckboxes.length;
                var pcp = {!! json_encode($pcp) !!};
                // console.log(pcp);

                $('.lab').each(function(index, element) {
                    var dataId = $(element).data('id');
                    var matchedPcp = Object.values(pcp).find(item => item.id == dataId);
                    // console.log($(this).data('id'));
                    // if (matchedPcp) {
                    $(`#img_${dataId}`).change(function() {
                        const input = $(this)[0];
                        const preview = $(`.preview_${dataId}`);
                        let isBig = this.files[0].size > 5 * 1024 * 1024;

                        $('#deskripsi_' + dataId).attr('required', 'required');

                        if (isBig) {
                            $('.too_big_' + dataId).show();
                            this.value = '';
                        } else {
                            $('.too_big_' + dataId).hide();
                        }

                        // 		console.log(this.files[0].size > 3 * 1024 * 1024);

                        if (input.files) {
                            const valueExists = $('.input_pcp').filter(function() {
                                return $(this).val() == $(`.input_img_${dataId}`).data(
                                    'pcp_id');
                            }).length > 0;
                            // console.log(valueExists);
                            if (!valueExists) {
                                $('#pcp_container').append(
                                    $(`<input class="input_pcp" name="pekerjaan_cp_id[]" value="${$(this).data('pcp_id')}"/>
									<input class="status" name="approve_status[]" value="proccess"/>`)
                                )
                                $('.lokasi').append(
                                    $(`<input type="text" value="" id="latitude" name="latitude[]" class="lat join-item w-fit input input-disabled text-xs text-center" readonly/>
        				<input type="text" value="" id="longitude" name="longtitude[]" class="long join-item w-fit input input-disabled text-xs text-center" readonly/>
        				<input type="text" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" id="tanggal" name="tanggal[]" class="join-item w-fit input input-disabled text-xs text-center" readonly/>
        				`)
                                );
                                var latitudeInput = $('.lat');
                                var longitudeInput = $('.long');

                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition(function(position) {
                                        showPosition(position);
                                    });
                                } else {
                                    alert('Geo Location Not Supported By This Browser !!');
                                }

                                function showPosition(position) {
                                    var latitude = position.coords.latitude;
                                    var longitude = position.coords.longitude;

                                    latitudeInput.val(latitude);
                                    longitudeInput.val(longitude);
                                }
                                // console.log($('.lokasi').clone());
                            }
                        }
                        // 		console.log(input.files, dataId, $('.input_pcp').val() == $(`.input_img_${dataId}`).data('pcp_id'));

                        if (input.files && input.files[0]) {
                            const reader = new FileReader();

                            reader.onload = function(e) {
                                preview.show();
                                preview.find(`.img_${dataId}`).attr('src', e.target.result);
                                preview.removeClass('hidden');
                                preview.find(`.img_${dataId}`).addClass(
                                    'rounded-md shadow-md my-4');
                                $(`.iImage_${dataId}`).removeClass('flex').addClass('hidden');
                            };

                            reader.readAsDataURL(input.files[0]);
                        }
                    });

                    // }
                    $(`#img_${dataId}`).change(function() {
                        var maxSizeInBytes = 3 * 1024 *
                            1024; // 5MB (change this value to your desired max size)
                        var fileSize = this.files[0].size;
                        var errorMessageLabel = $('.error-message');

                        if (fileSize > maxSizeInBytes) {
                            // Clear the input field if the file exceeds the maximum size
                            // $(this).val('');
                            // errorMessageLabel.removeClass('hidden');

                        } else {
                            errorMessageLabel.addClass('hidden');
                        }
                    });
                });





            });
        </script>
    </x-main-div>
</x-app-layout>
