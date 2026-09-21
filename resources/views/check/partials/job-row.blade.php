<div class="job-row relative rounded-xl border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,.04)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:shadow-[0_10px_24px_-16px_rgba(15,23,42,.2)]" style="animation: fadeSlide .28s cubic-bezier(.22,1,.36,1)">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="font-bold text-slate-800">Pekerjaan <span class="job-number">{{ ($index ?? 0) + 1 }}</span></h2>
        <button type="button" class="remove-job btn btn-xs btn-ghost text-error">
            <i class="ri-delete-bin-2-line"></i>
            Hapus</button>
    </div>
        <label class="label py-0"><span class="label-text font-semibold">Nama pekerjaan</span></label>
        <div class="job-dropdown relative mt-2">

        <button type="button"
            class="job-trigger flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 text-left text-sm text-slate-700 outline-none transition duration-300 hover:border-sky-300 focus:border-sky-400 focus:ring-2 focus:ring-sky-100">

            <span class="job-label truncate text-slate-400">
                Pilih dari daftar pekerjaan
            </span>

            <i class="job-chevron shrink-0 text-lg text-slate-400 transition-transform duration-300 ri-arrow-down-s-line"></i>
        </button>

        <div
            class="job-menu fixed z-[9999] hidden max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-[0_18px_40px_-18px_rgba(15,23,42,.28)]">

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

            <option value="manual">Pilih Untuk Ketik manual</option>
        </select>

    </div>
    <input class="manual-name input input-bordered border-slate-200 mt-2 hidden w-full" type="text" name="input_manual[]" placeholder="Ketik nama pekerjaan">
    <input class="job-value" type="hidden" name="pekerjaan_id[]">
    <label class="label mt-4 py-0"><span class="label-text font-semibold">Foto pekerjaan</span></label>
    <label class="dropzone mt-2 flex min-h-28 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-4 text-center transition duration-200 hover:border-sky-400 hover:bg-sky-50">
        <div class="photo-preview grid w-full grid-cols-3 gap-2 sm:grid-cols-5"></div>
        <div class="photo-placeholder flex flex-col items-center"><i class="ri-upload-cloud-2-line text-3xl text-slate-400"></i><span class="text-sm font-semibold text-slate-600">Klik atau tarik foto ke sini</span><span class="text-xs text-slate-400">JPG, PNG — bisa lebih dari satu</span></div>
        <input class="photo-input hidden" type="file" name="img[][]" accept="image/*" multiple>
    </label>
    <div class="photo-names mt-2 text-xs text-slate-500"></div>
    <label class="label mt-4 py-0"><span class="label-text font-semibold">Deskripsi pekerjaan</span></label>
    <textarea class="textarea textarea-bordered mt-2 w-full" name="deskripsi[]" rows="3" placeholder="Jelaskan pekerjaan yang dilakukan"></textarea>
    <input type="hidden" name="approve_status[]" value="proccess">
    <input type="hidden" name="tanggal[]" value="{{ $selectedDate }}">
</div>
