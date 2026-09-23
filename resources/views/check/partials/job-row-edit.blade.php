@php
    $isManual = $manual !== null && $manual !== '';
    $selectedId = $isManual ? 'manual' : (string) ($jobId ?? '');
    $status = $approveStatuses[$i] ?? null;
    $isDenied = $status === 'denied';
    $isAccepted = $status === 'accept';
    $rowClass = $isDenied
        ? 'border-rose-300 bg-rose-50/50 hover:border-rose-400'
        : ($isAccepted
            ? 'border-emerald-300 bg-emerald-50/50 hover:border-emerald-400'
            : 'border-slate-200 bg-white hover:border-sky-300 hover:shadow-[0_10px_24px_-16px_rgba(15,23,42,.2)]');
    $locked = $isAccepted ? 'disabled' : '';
    $note = data_get((array) $cex->note, $i);
@endphp
<div class="job-row relative rounded-xl border p-4 shadow-[0_1px_2px_rgba(15,23,42,.04)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] {{ $rowClass }}"
    style="animation: fadeSlide .28s cubic-bezier(.22,1,.36,1)" data-status="{{ $status ?? '' }}">
    <div class="mb-3 flex items-center justify-between gap-2">
        <h2 class="flex items-center gap-2 font-bold text-slate-800">Pekerjaan <span
                class="job-number">{{ $i + 1 }}</span>
            @if ($isAccepted)
                <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700"><i
                        class="ri-lock-line"></i> Disetujui</span>
            @elseif ($isDenied)
                <span
                    class="rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold uppercase text-rose-700">Ditolak</span>
            @endif
        </h2>
        @unless ($isAccepted)
            <button type="button" class="remove-job btn btn-xs btn-ghost text-error">Hapus</button>
        @endunless
    </div>
    @if ($isDenied && filled($note))
        <p class="mb-3 rounded-lg bg-rose-100/70 px-3 py-2 text-xs text-rose-700"><strong>Alasan:</strong>
            {{ $note }}<br><span class="mt-1 inline-flex items-center gap-1 ">Setelah disimpan, pekerjaan ini diajukan ulang untuk ditinjau
                Direksi.</span></p>
    @elseif ($isDenied)
        <p class="mb-3 rounded-lg bg-rose-100/70 px-3 py-2 text-xs text-rose-700"><span
                class="inline-flex items-center gap-1 font-semibold"><i class="ri-refresh-line"></i>Setelah disimpan,
                pekerjaan ini diajukan ulang untuk ditinjau Direksi.</span></p>
    @elseif ($isAccepted)
        <p class="mb-3 rounded-lg bg-emerald-100/70 px-3 py-2 text-xs text-emerald-700">Pekerjaan ini sudah disetujui
            dan tidak dapat diubah.</p>
    @endif
<label class="label py-0">
    <span class="label-text font-semibold">Nama pekerjaan</span>
</label>

<div class="job-dropdown relative mt-2">

    <button
        type="button"
        {{ $locked }}
        class="job-trigger flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 text-left text-sm text-slate-700 outline-none transition duration-300 hover:border-sky-300 focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
    >
        <span class="job-label truncate {{ $selectedId === '' ? 'text-slate-400' : 'text-slate-700' }}">
            {{ $isManual
                ? $manual
                : (optional($pcp->firstWhere('id', $selectedId))->name ?? 'Pilih dari daftar pekerjaan')
            }}
        </span>

        <i class="job-chevron shrink-0 text-lg text-slate-400 transition-transform duration-300 ri-arrow-down-s-line"></i>
    </button>

    <div
        class="job-menu fixed z-[9999] hidden max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-[0_18px_40px_-18px_rgba(15,23,42,.28)]">
        {{-- Search --}}
        <div class="sticky top-0 z-10 bg-white pb-1.5">
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>

                <input
                    type="text"
                    class="job-search h-10 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-2 focus:ring-sky-100"
                    placeholder="Cari pekerjaan..."
                    autocomplete="off">
            </div>
        </div>

        <button
            type="button"
            class="job-option w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700"
            data-value=""
        >
            Pilih dari daftar pekerjaan
        </button>

        <button
            type="button"
            class="job-option w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700"
            data-value="manual"
        >
            Pilih Untuk Ketik manual
        </button>

        @foreach ($pcp as $p)
            <button
                type="button"
                class="job-option w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 {{ $selectedId === (string) $p->id ? 'bg-sky-50 font-medium text-sky-700' : '' }}"
                data-value="{{ $p->id }}"
            >
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
            <option
                value="{{ $p->id }}"
                {{ $selectedId === (string) $p->id ? 'selected' : '' }}
            >
                {{ $p->name }} — {{ $p->type_check }}
            </option>
        @endforeach

        <option value="manual" {{ $selectedId === 'manual' ? 'selected' : '' }}>Pilih Untuk Ketik manual</option>
    </select>

</div>
    <input class="manual-name input input-bordered mt-2 w-full {{ $isManual ? '' : 'hidden' }}" type="text"
        name="input_manual[]" value="{{ $isManual ? $manual : '' }}" placeholder="Ketik nama pekerjaan"
        {{ $locked }}>
    <input class="original-index" type="hidden" name="original_index[]" value="{{ $i }}">
    <input class="job-value" type="hidden" name="pekerjaan_id[]" value="{{ $selectedId }}">

    <label class="label mt-4 py-0"><span class="label-text font-semibold">Foto pekerjaan</span></label>
    <label
        class="dropzone mt-2 flex min-h-28 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-4 text-center transition duration-200 hover:border-sky-400 hover:bg-sky-50">
        <div class="photo-preview grid w-full grid-cols-3 gap-2 sm:grid-cols-5">
            @foreach ($rowImages as $image)
                @if ($image)
                    <img src="{{ asset('storage/images/' . $image) }}" alt="Foto pekerjaan"
                        class="h-20 w-full rounded-lg object-cover ring-1 ring-slate-200">
                @endif
            @endforeach
        </div>
        <div
            class="photo-placeholder flex flex-col items-center {{ count(array_filter($rowImages)) ? 'hidden' : '' }}">
            <i class="ri-upload-cloud-2-line text-3xl text-slate-400"></i><span
                class="text-sm font-semibold text-slate-600">Klik atau tarik foto ke sini</span><span
                class="text-xs text-slate-400">JPG, PNG — bisa lebih dari satu</span></div>
        <input class="existing-images" type="hidden" name="existing_img[{{ $i }}][]"
            value="{{ implode(',', $rowImages) }}">
        <input class="photo-input photo-camera hidden" type="file" name="img[{{ $i }}][]" accept="image/*"
            capture="environment" multiple {{ $locked }}>
        <input class="photo-gallery hidden" type="file" accept="image/*" multiple {{ $locked }}>
    </label>
    <div class="photo-names mt-2 text-xs text-slate-500"></div>

    <label class="label mt-4 py-0"><span class="label-text font-semibold">Deskripsi pekerjaan</span></label>
    <textarea class="deskripsi-input textarea textarea-bordered mt-2 w-full" name="deskripsi[{{ $i }}]"
        rows="3" placeholder="Jelaskan pekerjaan yang dilakukan" {{ $locked }}>{{ $descriptions[$i] ?? '' }}</textarea>
    <input class="approve-input" type="hidden" name="approve_status[{{ $i }}]"
        value="{{ $isAccepted ? 'accept' : ($isDenied ? 'proccess' : $status ?? 'proccess') }}">
    <input class="tanggal-input" type="hidden" name="tanggal[{{ $i }}]" value="{{ $selectedDate }}">
</div>
