<div class="job-row relative rounded-xl border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,.04)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:shadow-[0_10px_24px_-16px_rgba(15,23,42,.2)]"
    style="animation: fadeSlide .28s cubic-bezier(.22,1,.36,1)">
    @php $maxPhotos = \App\Services\CheckPointSyncService::MAX_IMAGES_PER_ITEM; @endphp
    <div class="mb-3 flex items-center justify-between">
        <h2 class="font-bold text-slate-800">Pekerjaan <span class="job-number">{{ ($index ?? 0) + 1 }}</span></h2>
        <button type="button" class="remove-job btn btn-xs btn-ghost text-error">
            <i class="ri-delete-bin-2-line"></i>
            Hapus</button>
    </div>
    <label class="label py-0"><span class="label-text font-semibold">Nama pekerjaan</span></label>
    <div class="job-dropdown relative mt-2">

        <button type="button"
            class="job-trigger flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 text-left text-sm text-slate-700 outline-hidden transition duration-300 hover:border-sky-300 focus:border-sky-400 focus:ring-2 focus:ring-sky-100">

            <span class="job-label truncate text-slate-400">
                Pilih dari daftar pekerjaan
            </span>

            <i
                class="job-chevron shrink-0 text-lg text-slate-400 transition-transform duration-300 ri-arrow-down-s-line"></i>
        </button>

        <div
            class="job-menu fixed z-9999 hidden max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-[0_18px_40px_-18px_rgba(15,23,42,.28)]">
            {{-- Search --}}
            <div class="sticky top-0 z-10 bg-white pb-1.5">
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input type="text"
                        class="job-search h-10 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-700 outline-hidden transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-2 focus:ring-sky-100"
                        placeholder="Cari pekerjaan..." autocomplete="off">
                </div>
            </div>
            <button type="button"
                class="job-option w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700"
                data-value="">
                Pilih dari daftar pekerjaan
            </button>

            <button type="button"
                class="job-option w-full text-slate-600 rounded-lg px-3 py-2.5 text-left text-sm capitalize hover:bg-sky-50 hover:text-sky-700"
                data-value="manual">
                Pilih Untuk Ketik manual
            </button>

            @foreach ($pcp as $p)
                <button type="button"
                    class="job-option w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700"
                    data-value="{{ $p->id }}">
                    {{ $p->name }}
                    <span class="text-xs text-slate-400">
                        — {{ $p->type_check }}
                    </span>
                </button>
            @endforeach

        </div>

        <select class="job-select hidden" tabindex="-1" aria-hidden="true">
            <option value="">Pilih dari daftar pekerjaan</option>

            @foreach ($pcp as $p)
                <option value="{{ $p->id }}">
                    {{ $p->name }} — {{ $p->type_check }}
                </option>
            @endforeach

            <option value="manual" {{ $workOrder?->deskripsi ? 'selected' : '' }}>Pilih Untuk Ketik manual</option>
        </select>

    </div>
    <textarea
        class="manual-name textarea textarea-bordered mt-2 min-h-24 w-full resize-y
            border-slate-200 bg-white text-sm leading-relaxed
            focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200
            {{ $workOrder?->deskripsi ? '' : 'hidden' }}"
        name="input_manual[]"
        placeholder="Ketik deskripsi pekerjaan..."
        rows="3"
    >{{ old('input_manual.' . ($index ?? 0), $workOrder?->deskripsi) }}</textarea>
    <input class="job-value" type="hidden" name="pekerjaan_id[]">
    <label class="label mt-4 py-0">
        <span class="label-text font-semibold">Foto pekerjaan</span>
        <span class="label-text-alt text-slate-400">Maks. {{ $maxPhotos }} foto</span>
    </label>

    <div class="mt-2">
        <div
            class="dropzone flex min-h-28 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-4 text-center transition duration-200 hover:border-sky-400 hover:bg-sky-50">
            <div class="photo-preview grid w-full grid-cols-1 gap-2 sm:grid-cols-2"></div>

            <div class="photo-placeholder flex flex-col items-center">
                <i class="ri-image-add-line text-3xl text-slate-400"></i>

                <span class="text-sm font-semibold text-slate-600">
                    Tambahkan foto
                </span>

                <span class="text-xs text-slate-400">
                    Ambil foto atau pilih dari galeri (maks. {{ $maxPhotos }})
                </span>
            </div>
        </div>

        <!-- Input kamera -->
        <input class="photo-input photo-camera hidden" type="file" name="img[][]" accept="image/*"
            capture="environment" multiple>

        <!-- Input galeri -->
        <input class="photo-gallery hidden" type="file" accept="image/*" multiple>
    </div>
    <div class="photo-names mt-2 text-xs text-slate-500"></div>
    <p class="photo-limit mt-1 hidden text-xs font-semibold text-rose-600"></p>
    <label class="label mt-4 py-0"><span class="label-text font-semibold">Deskripsi pekerjaan</span></label>
    <textarea class="deskripsi-input textarea textarea-bordered mt-2 w-full" name="deskripsi[]" rows="3"
        placeholder="Jelaskan pekerjaan yang dilakukan"></textarea>
    <input class="approve-input" type="hidden" name="approve_status[]" value="proccess">
    <input class="tanggal-input" type="hidden" name="tanggal[]"
        value="{{ $workOrder?->tanggal ? \Carbon\Carbon::parse($workOrder->tanggal)->format('Y-m-d') : $selectedDate }}">
</div>
