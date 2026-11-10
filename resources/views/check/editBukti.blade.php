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
                <form method="POST" action="{{ route('uploadBukti-checkpoint-user') }}" id="form-cp" enctype="multipart/form-data" data-max-photos="{{ \App\Services\CheckPointSyncService::MAX_IMAGES_PER_ITEM }}" class="p-4 sm:p-6">
                    @csrf
                    <div class="mb-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-medium text-slate-500">Nama</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ Auth::user()->nama_lengkap }}</p>
                            <input type="hidden" id="user_id" name="user_id" value="{{ Auth::id() }}">
                            <input type="hidden" id="latitude" name="latitude" value="">
                            <input type="hidden" id="longtitude" name="longtitude" value="">
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
                        @php
                            $pcpIds = $pcp->pluck('id')->map(fn ($id) => (string) $id);
                            $plannedJobIds = $cex->items->pluck('pekerjaan_cp_id')->filter();
                            $tambahanItems = $cex->items->reject(fn ($it) => $it->pekerjaan_cp_id === null || $pcpIds->contains((string) $it->pekerjaan_cp_id));
                        @endphp
                        @foreach (['harian', 'mingguan', 'bulanan', 'isidental'] as $type)
                            @php
                                $pcpType = $pcp->whereIn('id', $plannedJobIds)->where('type_check', $type);
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
                                                    <p class="photo-count_{{ $p->id }} text-center text-xs text-slate-500"></p>
                                                </div>
                                                <label for="img_{{ $p->id }}"
                                                    class="w-full iImage_{{ $p->id }} flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                                                    <span class="p-2 flex justify-center items-center">
                                                        <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                                        <span class="text-xs font-semibold text-slate-700/70">+ Gambar (maks. {{ \App\Services\CheckPointSyncService::MAX_IMAGES_PER_ITEM }} foto)</span>
                                                        <input id="img_{{ $p->id }}"
                                                            data-pcp_id="{{ $p->id }}"
                                                            class="input_img_{{ $p->id }} hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                                            type="file" name="img[pcp_{{ $p->id }}][]" multiple
                                                            accept=".gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic" />
                                                    </span>
                                                </label>
                                                <div class="hidden too_big_{{ $p->id }}">
                                                    <p style="color: red;">*Gambar Terlalu Besar!!! (Max 5 Mb)</p>
                                                </div>
                                                <p class="hidden photo-limit_{{ $p->id }} text-xs font-semibold text-rose-600"></p>
                                            </div>
                                            <!---->
                                            <div class="my-2">
                                                <textarea name="deskripsi[pcp_{{ $p->id }}]" id="deskripsi_{{ $p->id }}" rows="1"
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
                            @if ($tambahanItems->isNotEmpty())
                                @foreach ($tambahanItems as $i => $item)
                                    @php
                                        $label = $item->pekerjaan_cp_id ?: $item->input_manual;
                                        // Keying an ad-hoc row by its own item id keeps
                                        // it clear of the planned rows, which use
                                        // `pcp_<id>` — the two id spaces collide.
                                        $key = 'item_' . $item->id;
                                    @endphp
                                    @if ($label)
                                        <p id="tambahan_label" class="text-center font-semibold my-1">~ Tambahan ~
                                        </p>
                                        <label for="checkbox" style="padding-left: 10px;" class="lab"
                                            data-id="{{ $key }}"
                                            data-loop="{{ $i }}">{{ $i + 1 }}.
                                            {{ $label }}</label>
                                        <div class="p-1">
                                            <div class="preview_{{ $key }} hidden w-full">
                                                <span class="flex justify-center items-center">
                                                    <label for="img_{{ $key }}" class="p-1">
                                                        <img class="img_{{ $key }} ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s"
                                                            src="" alt="" srcset="" height="120px"
                                                            width="120px">
                                                    </label>
                                                </span>
                                                <p class="photo-count_{{ $key }} text-center text-xs text-slate-500"></p>
                                            </div>
                                            <label for="img_{{ $key }}"
                                                class="w-full iImage_{{ $key }} flex flex-col items-center justify-center rounded-md bg-slate-300/70 ring-2 ring-slate-400/70 hover:ring-0 hover:bg-slate-300 transition ease-in-out .2s">
                                                <span class="p-2 flex justify-center items-center">
                                                    <i class="ri-image-add-line text-xl text-slate-700/90"></i>
                                                    <span class="text-xs font-semibold text-slate-700/70">+
                                                        Gambar (maks. {{ \App\Services\CheckPointSyncService::MAX_IMAGES_PER_ITEM }} foto)</span>
                                                    <input id="img_{{ $key }}"
                                                        data-pcp_id="{{ $key }}"
                                                        class="input_img_{{ $key }} hidden mt-1 w-full file-input file-input-sm file-input-bordered shadow-none"
                                                        type="file" name="img[{{ $key }}][]" multiple
                                                        accept="image/*" />
                                                </span>
                                            </label>
                                            <div class="hidden too_big_{{ $key }}">
                                                <p style="color: red;">*Gambar Terlalu Besar!!! (Max 5 Mb)</p>
                                            </div>
                                            <p class="hidden photo-limit_{{ $key }} text-xs font-semibold text-rose-600"></p>
                                        </div>
                                        <div class="my-2">
                                            <textarea name="deskripsi[{{ $key }}]" id="deskripsi" rows="1" class="textarea textarea-bordered w-full"
                                                placeholder="Deskripsi laporan..."></textarea>
                                            <textarea name="note[{{ $key }}]" id="note" rows="1" class="textarea textarea-bordered w-full hidden"
                                                placeholder="Deskripsi laporan..."></textarea>
                                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                                        </div>
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

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        $('#latitude').val(position.coords.latitude);
                        $('#longtitude').val(position.coords.longitude);
                    });
                }

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

                const maxPhotos = Number($('#form-cp').data('max-photos')) || 7;

                $('.lab').each(function(index, element) {
                    var dataId = $(element).data('id');
                    var matchedPcp = Object.values(pcp).find(item => item.id == dataId);

                    $(`#img_${dataId}`).change(function() {
                        const input = $(this)[0];
                        const preview = $(`.preview_${dataId}`);
                        const tooBig = $('.too_big_' + dataId);
                        const limitWarning = $('.photo-limit_' + dataId);

                        if (!input.files || input.files.length === 0) {
                            tooBig.hide();
                            limitWarning.addClass('hidden').text('');
                            return;
                        }

                        // Reject oversized files outright instead of silently
                        // keeping them in the upload queue.
                        const oversized = Array.from(input.files).filter(file => file.size > 5 * 1024 * 1024);
                        if (oversized.length > 0) {
                            tooBig.show();
                            const keptFiles = new DataTransfer();
                            Array.from(input.files)
                                .filter(file => file.size <= 5 * 1024 * 1024)
                                .forEach(file => keptFiles.items.add(file));
                            input.files = keptFiles.files;
                        } else {
                            tooBig.hide();
                        }

                        // Enforce the per-job photo limit in the browser so the
                        // user is told before the request is sent, and never
                        // loses photos without an explanation.
                        const keep = new DataTransfer();
                        Array.from(input.files).slice(0, maxPhotos).forEach(file => keep.items.add(file));
                        const dropped = input.files.length - keep.files.length;
                        input.files = keep.files;

                        if (dropped > 0) {
                            limitWarning
                                .text(`Maksimal ${maxPhotos} foto per pekerjaan. ${dropped} foto terakhir tidak dipakai.`)
                                .removeClass('hidden');
                        } else {
                            limitWarning.addClass('hidden').text('');
                        }

                        if (input.files.length > 0) {
                            $('.photo-count_' + dataId)
                                .text(input.files.length + ' / ' + maxPhotos + ' foto')
                                .removeClass('hidden');
                        } else {
                            $('.photo-count_' + dataId).addClass('hidden').text('');
                        }

                        $('#deskripsi_' + dataId).attr('required', 'required');

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.show();
                            preview.find(`.img_${dataId}`).attr('src', e.target.result);
                            preview.removeClass('hidden');
                            preview.find(`.img_${dataId}`).addClass('rounded-md shadow-md my-4');
                            $(`.iImage_${dataId}`).removeClass('flex').addClass('hidden');
                        };

                        reader.readAsDataURL(input.files[0]);
                    });
                });
            });
        </script>
    </x-main-div>
</x-app-layout>
