@php
    $workOrderDescription = $workOrder?->deskripsi;
    $jobId = $item?->pekerjaan_cp_id;
    $manual = $item?->input_manual;
    $manualValue = filled($manual) ? $manual : null;
    $isManual = filled($jobId) ? false : $manualValue !== null;
    $selectedId = $isManual ? 'manual' : (string) ($jobId ?? '');
    $status = $item?->approve_status;
    $isDenied = $status === 'denied';
    $isAccepted = $status === 'accept';
    $windowLocked = $windowLocked ?? false;
    // Saat pekan laporan ditutup hanya baris yang punya catatan evaluasi yang
    // tetap bisa diperbaiki; baris lain (dan semua yang sudah disetujui) dibaca saja.
    $readOnly = $isAccepted || ($windowLocked && blank($item?->note));
    $rowClass = $isDenied
        ? 'border-rose-300 bg-rose-50/50 hover:border-rose-400'
        : ($isAccepted
            ? 'border-emerald-300 bg-emerald-50/50 hover:border-emerald-400'
            : 'border-slate-200 bg-white hover:border-sky-300 hover:shadow-[0_10px_24px_-16px_rgba(15,23,42,.2)]');
    $locked = $readOnly ? 'disabled' : '';
    $rowImages = $item ? $item->images->pluck('path')->all() : [];
@endphp
<div class="job-row relative rounded-xl border p-4 shadow-[0_1px_2px_rgba(15,23,42,.04)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] {{ $rowClass }}"
    style="animation: fadeSlide .28s cubic-bezier(.22,1,.36,1)" data-status="{{ $status ?? '' }}">
    <div class="mb-3 flex items-center justify-between gap-2">
        <h2 class="flex items-center gap-2 font-bold text-slate-800">Bukti Pekerjaan <span
                class="job-number">{{ ($i ?? 0) + 1 }}</span>
            <x-checkpoint-status-badge :status="$status" :label="$isAccepted ? 'Disetujui' : null" />
        </h2>
        @unless ($readOnly)
            <button type="button" class="remove-job btn btn-xs btn-ghost text-error">Hapus</button>
        @endunless
    </div>
    @if ($isDenied)
        <p class="mb-3 rounded-lg bg-rose-100/70 px-3 py-2 text-xs text-rose-700"><span
                class="inline-flex items-center gap-1 font-semibold"><i class="ri-refresh-line"></i>Setelah disimpan,
                pekerjaan ini diajukan ulang untuk ditinjau Direksi.</span></p>
    @elseif ($isAccepted)
        <p class="mb-3 rounded-lg bg-emerald-100/70 px-3 py-2 text-xs text-emerald-700">Pekerjaan ini telah disetujui.
        </p>
    @endif
    @if ($windowLocked && ! $isAccepted)
        @if ($readOnly)
            <p class="mb-3 flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-500"><i
                    class="ri-lock-line"></i>Pekan laporan ditutup, pekerjaan ini hanya bisa dibaca.</p>
        @else
            <p class="mb-3 flex items-center gap-1 rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-700"><i
                    class="ri-edit-line"></i>Bisa diperbaiki karena ada catatan evaluasi Direksi.</p>
        @endif
    @endif
    <label class="label py-0">
        <span class="label-text font-semibold">Nama pekerjaan</span>
    </label>

    <div class="job-dropdown relative mt-2 {{ $readOnly ? 'pointer-events-none' : '' }}">

        <button type="button" {{ $locked }}
            class="job-trigger flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 text-left text-sm text-slate-700 outline-hidden {{ $readOnly ? 'cursor-not-allowed' : 'transition duration-300 hover:border-sky-300 focus:border-sky-400 focus:ring-2 focus:ring-sky-100' }}">
            <span class="job-label truncate {{ $selectedId === '' ? 'text-slate-400' : 'text-slate-700' }}">
                {{ $isManual ? $manualValue : optional($pcp->firstWhere('id', $selectedId))->name ?? 'Pilih dari daftar pekerjaan' }}
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
                class="job-option w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700"
                data-value="manual">
                Pilih Untuk Ketik manual
            </button>

            @foreach ($pcp as $p)
                <button type="button"
                    class="job-option w-full rounded-lg px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-sky-50 hover:text-sky-700 {{ $selectedId === (string) $p->id ? 'bg-sky-50 font-medium text-sky-700' : '' }}"
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
                <option value="{{ $p->id }}" {{ $selectedId === (string) $p->id ? 'selected' : '' }}>
                    {{ $p->name }} — {{ $p->type_check }}
                </option>
            @endforeach

            <option value="manual" {{ $selectedId === 'manual' ? 'selected' : '' }}>Pilih Untuk Ketik manual</option>
        </select>

    </div>
    <textarea
        class="manual-name textarea textarea-bordered mt-2 w-full resize-y
            {{ $isManual ? '' : 'hidden' }}"
        name="input_manual[]"
        {{ $locked }}
        data-work-order-description="{{ $workOrderDescription }}"
        placeholder="Ketik deskripsi pekerjaan..."
        rows="3"
    >{{ $isManual ? $manualValue : '' }}</textarea>
    <input class="original-index" type="hidden" name="original_index[]" value="{{ $i ?? 0 }}">
    <input class="item-id" type="hidden" name="item_id[{{ $i ?? 0 }}]" value="{{ $item?->id }}">
    <input class="job-value" type="hidden" name="pekerjaan_id[]" value="{{ $selectedId }}">

    <label class="label mt-4 py-0"><span class="label-text font-semibold">Foto pekerjaan</span><span
            class="label-text-alt text-slate-400">Maks. {{ \App\Services\CheckPointSyncService::MAX_IMAGES_PER_ITEM }}
            foto</span></label>
    <div
        class="{{ $readOnly ? 'cursor-not-allowed opacity-70' : 'dropzone transition duration-200 hover:border-sky-400 hover:bg-sky-50 cursor-pointer' }} mt-2 flex min-h-28 flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-4 text-center {{ $readOnly ? 'pointer-events-none' : '' }}">
        <div class="photo-preview grid w-full grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach ($rowImages as $image)
                @if ($image)
                    <div class="photo-item group relative" data-existing-image="{{ $image }}">
                        <img src="{{ asset('storage/images/' . $image) }}" alt="Foto pekerjaan"
                            class="h-full w-full rounded-lg object-cover ring-1 ring-slate-200">
                        @unless ($readOnly)
                            <button type="button"
                                class="photo-delete absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow-md transition hover:bg-red-600"
                                aria-label="Hapus foto">
                                <i class="ri-close-line"></i>
                            </button>
                            <button type="button"
                                class="photo-edit absolute bottom-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-white shadow-md transition hover:bg-amber-600"
                                aria-label="Edit foto">
                                <i class="ri-pencil-line"></i>
                            </button>
                        @endunless
                        <input type="hidden" class="existing-image" name="existing_img[{{ $i ?? 0 }}][]"
                            value="{{ $image }}">
                    </div>
                @endif
            @endforeach
        </div>
        <div
            class="photo-placeholder flex flex-col items-center {{ $readOnly || count(array_filter($rowImages)) ? 'hidden' : '' }}">
            <i class="ri-upload-cloud-2-line text-3xl text-slate-400"></i><span
                class="text-sm font-semibold text-slate-600">Klik atau tarik foto ke sini</span><span
                class="text-xs text-slate-400">JPG, PNG — maks.
                {{ \App\Services\CheckPointSyncService::MAX_IMAGES_PER_ITEM }} foto</span>
        </div>
        <input class="existing-images" type="hidden" name="existing_img[{{ $i ?? 0 }}][]" value="">
        {{-- `multiple` stays off this input: mobile browsers ignore `capture` when it is set, so the picker opens instead of the camera. --}}
        <input class="photo-input photo-camera hidden" type="file" name="img[{{ $i ?? 0 }}][]"
            accept="image/*" capture="environment" {{ $locked }}>
    </div>
    <input class="photo-gallery hidden" type="file" accept="image/*" multiple {{ $locked }}>
    <div class="photo-names mt-2 text-xs text-slate-500"></div>
    <p class="photo-limit mt-1 hidden text-xs font-semibold text-rose-600"></p>

    <label class="label mt-4 py-0"><span class="label-text font-semibold">Deskripsi pekerjaan</span></label>
    <textarea class="deskripsi-input textarea textarea-bordered mt-2 w-full" name="deskripsi[{{ $i ?? 0 }}]"
        rows="3" placeholder="Jelaskan pekerjaan yang dilakukan" {{ $locked }}>{{ $item?->deskripsi ?? '' }}</textarea>
    {{-- Conversation lives only on saved rows: a brand-new row has no item id
         to post against yet. --}}
    @if ($item)
        @include('check.partials.item-thread', ['item' => $item, 'viewerRole' => 'employee'])
    @endif

    <input class="approve-input" type="hidden" name="approve_status[{{ $i ?? 0 }}]"
        value="{{ $isAccepted ? 'accept' : ($isDenied ? 'proccess' : $status ?? 'proccess') }}">
    <input class="tanggal-input" type="hidden" name="tanggal[{{ $i ?? 0 }}]"
        value="{{ $item?->tanggal?->format('Y-m-d') ?? $selectedDate }}">
</div>
