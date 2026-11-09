<x-app-layout>
        @php
            $selectedDate = $selectedDate ?? now()->format('Y-m-d');
        @endphp

        <div class="mx-auto w-full max-w-4xl px-4 py-6 mt-10">
            <div class="card overflow-visible border border-white/70 bg-white shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:shadow-[0_16px_36px_-18px_rgba(15,23,42,.18)]">
                <div class="card-body overflow-visible gap-5 p-5 sm:p-7">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Check Point</p>
                            <h1 class="mt-1 text-2xl font-bold text-slate-900">Tambah pekerjaan</h1>
                            <p class="mt-1 text-sm text-slate-500">Semua pekerjaan berikut disimpan pada tanggal yang sama.</p>
                        </div>
                        <a href="{{ route('checkpoint-user.index') }}" class="btn btn-sm btn-ghost">
                            <i class="ri-arrow-left-s-line"></i>
                            Kembali</a>
                    </div>

                    <form id="form-cp" method="POST" enctype="multipart/form-data" data-max-photos="{{ \App\Services\CheckPointSyncService::MAX_IMAGES_PER_ITEM }}" action="{{ route('checkpoint-user.store') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        <input type="hidden" name="divisi_id" value="{{ Auth::user()->divisi->id }}">
                        <input type="hidden" id="latitude" name="latitude" value="">
                        <input type="hidden" id="longtitude" name="longtitude" value="">
                        @if($workOrder)
                        <input type="hidden" name="work_order_id" value="{{ $workOrder->id }}">
                        @endif

                        @if (Auth::user()->devisi_id == 3)
                            <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50 p-4">
                                <label class="label py-0"><span class="label-text font-semibold">Jenis pekerjaan</span></label>
                                <select name="work_mode" id="work-mode" class="select select-bordered mt-2 w-full bg-white">
                                    <option value="individual">Individu</option>
                                    <option value="shared">Bersama</option>
                                </select>
                                <div id="shared-users" class="mt-4 hidden">
                                    <div class="mb-3 flex items-end justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-bold uppercase tracking-[.16em] text-sky-700">Pilih rekan kerja</p>
                                        </div>
                                        <label class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-sky-700 overflow-hidden">
                                            <input type="checkbox" id="select-all-users" class="checkbox checkbox-xs checkbox-info">
                                            Pilih semua
                                        </label>
                                    </div>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        @foreach ($sharedUsers as $sharedUser)
                                            <label class="group flex min-w-0 cursor-pointer items-center gap-2 rounded-xl bg-white p-2 ring-1 m-1 ring-slate-200 transition-all duration-500 ease-[cubic-bezier(.32,.72,0,1)] hover:ring-sky-300 has-[:checked]:bg-sky-50 has-[:checked]:ring-sky-400">
                                                <input type="checkbox" name="participant_ids[]" value="{{ $sharedUser->id }}" class="peer sr-only participant-checkbox">
                                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500 transition-all duration-500 ease-[cubic-bezier(.32,.72,0,1)] group-hover:bg-sky-100 group-hover:text-sky-600 peer-checked:bg-sky-500 peer-checked:text-white">
                                                    <i class="ri-user-line text-lg"></i>
                                                </span>
                                                <span class="min-w-0 flex-1 truncate text-xs font-semibold text-slate-700">{{ $sharedUser->nama_lengkap }}</span>
                                                <span class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 text-transparent transition-all duration-500 ease-[cubic-bezier(.32,.72,0,1)] peer-checked:border-sky-500 peer-checked:bg-sky-500 peer-checked:text-white overflow-hidden"><i class="ri-check-line text-sm"></i></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-6 rounded-xl bg-slate-50 p-4">
                            @if($workOrder)
                                @if($workOrder->images->isNotEmpty())
                                    @php($imageFiles = $workOrder->images->filter(fn ($file) => str_starts_with((string) mime_content_type(storage_path('app/public/' . $file->path)), 'image/')))
                                    @php($otherFiles = $workOrder->images->reject(fn ($file) => str_starts_with((string) mime_content_type(storage_path('app/public/' . $file->path)), 'image/')))
                                    <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 p-4">
                                        <p class="mb-3 text-xs font-bold uppercase tracking-[.16em] text-sky-700">Lampiran perintah kerja</p>
                                        <div class="grid grid-cols-3 gap-3 sm:grid-cols-7">
                                            @foreach($imageFiles as $image)
                                                <a href="{{ asset('storage/' . ltrim($image->path, '/')) }}" data-work-order-image="{{ asset('storage/' . ltrim($image->path, '/')) }}" class="group aspect-square overflow-hidden rounded-lg border border-sky-100 bg-white">
                                                    <img src="{{ asset('storage/' . ltrim($image->path, '/')) }}" alt="Lampiran perintah kerja" class="h-full w-full object-cover transition group-hover:scale-105">
                                                </a>
                                            @endforeach
                                        </div>
                                        @if($otherFiles->isNotEmpty())
                                            <div class="mt-4 space-y-2">
                                                @foreach($otherFiles as $file)
                                                    @php($extension = strtoupper(pathinfo($file->path, PATHINFO_EXTENSION)))
                                                    <div class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-sky-300 hover:shadow-md">
                                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                                                            <i class="{{ in_array(strtolower($extension), ['xls', 'xlsx']) ? 'ri-file-excel-2-line' : (in_array(strtolower($extension), ['doc', 'docx']) ? 'ri-file-word-2-line' : ($extension === 'PDF' ? 'ri-file-pdf-2-line' : 'ri-file-line')) }} text-xl"></i>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="truncate text-sm font-bold text-slate-800" title="{{ basename($file->path) }}">{{ basename($file->path) }}</p>
                                                            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $extension }} · Lampiran perintah kerja</p>
                                                        </div>
                                                        <a href="{{ route('checkpoint-user.work-order-file', $file) }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-sky-500 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-sky-600 hover:shadow-md" download>
                                                            <i class="ri-download-2-line"></i> Download
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <div class="mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                    <i class="ri-user-star-line mt-0.5 text-lg text-amber-600"></i>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[.16em] text-amber-700">Diperintah oleh</p>
                                        <p class="mt-0.5 text-sm font-semibold text-amber-900">{{ $workOrder->creator_name }}</p>
                                    </div>
                                </div>
                            @endif
                            <label class="label py-0"><span class="label-text font-semibold">Tanggal pekerjaan</span></label>
                            <input class="input input-bordered mt-2 w-full bg-white font-semibold" type="date" name="tanggal[]" value="{{ $selectedDate }}" readonly>
                        </div>

                        @if ($sharedCheckpoints->isNotEmpty())
                            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-sm font-bold text-amber-900">Pekerjaan bersama tanggal ini</p>
                                @foreach ($sharedCheckpoints as $sharedCheckpoint)
                                    @foreach ($sharedCheckpoint->items as $sharedItem)
                                        <div class="mt-3 rounded-lg bg-white p-3 text-sm text-slate-700">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="font-semibold">{{ $sharedJobs[$sharedItem->pekerjaan_cp_id]->nama_pekerjaan ?? $sharedItem->input_manual ?? 'Pekerjaan' }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $sharedItem->deskripsi ?: 'Bukti pekerjaan sudah diinput bersama.' }}</p>
                                                </div>
                                                <a href="{{ route('checkpoint-user.history.show', ['id' => $sharedCheckpoint->id]) }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-sky-500 px-3 py-2 text-xs font-bold text-white transition hover:bg-sky-600">
                                                    <i class="ri-eye-line"></i>
                                                    Lihat detail
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif

                        <div id="jobs" class="space-y-4">
                            @include('check.partials.job-row', ['index' => 0, 'job' => null, 'pcp' => $pcp, 'selectedDate' => $selectedDate, 'workOrder' => $workOrder])
                        </div>

                        <button id="add-job" type="button" class="btn mt-4 w-full border-2 border-dashed border-sky-300 bg-sky-50 text-sky-700 transition duration-200 hover:-translate-y-0.5 hover:border-sky-400 hover:bg-sky-100">+ Tambah pekerjaan</button>
                        <button id="submit-job" type="submit" class="btn mt-5 w-full border-0 bg-sky-500 font-bold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">Simpan pekerjaan</button>
                    </form>
                </div>
            </div>
        </div>

        <template id="job-template">
            @include('check.partials.job-row', ['index' => null, 'job' => null, 'pcp' => $pcp, 'selectedDate' => $selectedDate, 'workOrder' => $workOrder])
        </template>

        @include('check.partials.job-script', ['isEdit' => false])
        @if (Auth::user()->devisi_id == 3)
            <script>
                const mode = document.getElementById('work-mode');
                const panel = document.getElementById('shared-users');
                const count = document.getElementById('selected-user-count');
                const selectAll = document.getElementById('select-all-users');
                const updateCount = () => {
                    const boxes = [...document.querySelectorAll('.participant-checkbox')];
                    const selected = boxes.filter((checkbox) => checkbox.checked).length;
                    count.textContent = selected;
                    if (selectAll) {
                        selectAll.checked = boxes.length > 0 && selected === boxes.length;
                        selectAll.indeterminate = selected > 0 && selected < boxes.length;
                    }
                };
                mode?.addEventListener('change', (event) => {
                    panel?.classList.toggle('hidden', event.target.value !== 'shared');
                    updateCount();
                });
                document.querySelectorAll('.participant-checkbox').forEach((checkbox) => checkbox.addEventListener('change', updateCount));
                selectAll?.addEventListener('change', () => {
                    document.querySelectorAll('.participant-checkbox').forEach((checkbox) => { checkbox.checked = selectAll.checked; });
                    updateCount();
                });
            </script>
        @endif

        @if($workOrder?->images->isNotEmpty())
            <div id="work-order-image-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 p-4" role="dialog" aria-modal="true" aria-label="Preview gambar perintah kerja">
                <button type="button" id="close-work-order-image-modal" class="absolute right-4 top-4 rounded-full bg-white/15 px-4 py-2 text-2xl text-white hover:bg-white/25" aria-label="Tutup">&times;</button>
                <button type="button" id="previous-work-order-image" class="absolute left-4 rounded-full bg-white/15 px-4 py-2 text-3xl text-white hover:bg-white/25" aria-label="Gambar sebelumnya">‹</button>
                <img id="work-order-image-modal-preview" src="" alt="Preview lampiran perintah kerja" class="max-h-[90vh] max-w-[85vw] rounded-xl object-contain shadow-2xl">
                <button type="button" id="next-work-order-image" class="absolute right-4 rounded-full bg-white/15 px-4 py-2 text-3xl text-white hover:bg-white/25" aria-label="Gambar berikutnya">›</button>
            </div>
            <script>
                const imageModal = document.getElementById('work-order-image-modal');
                const imageModalPreview = document.getElementById('work-order-image-modal-preview');
                const imageUrls = [...document.querySelectorAll('[data-work-order-image]')].map(image => image.dataset.workOrderImage);
                let imageIndex = 0;
                const showImage = () => { imageModalPreview.src = imageUrls[imageIndex]; };
                const closeImageModal = () => { imageModal.classList.add('hidden'); imageModal.classList.remove('flex'); imageModalPreview.src = ''; };
                document.querySelectorAll('[data-work-order-image]').forEach((image, index) => image.addEventListener('click', event => {
                    event.preventDefault(); imageIndex = index; showImage(); imageModal.classList.remove('hidden'); imageModal.classList.add('flex');
                }));
                document.getElementById('previous-work-order-image').addEventListener('click', () => { imageIndex = (imageIndex - 1 + imageUrls.length) % imageUrls.length; showImage(); });
                document.getElementById('next-work-order-image').addEventListener('click', () => { imageIndex = (imageIndex + 1) % imageUrls.length; showImage(); });
                document.getElementById('close-work-order-image-modal').addEventListener('click', closeImageModal);
                imageModal.addEventListener('click', event => { if (event.target === imageModal) closeImageModal(); });
                document.addEventListener('keydown', event => { if (event.key === 'Escape') closeImageModal(); if (event.key === 'ArrowLeft') document.getElementById('previous-work-order-image').click(); if (event.key === 'ArrowRight') document.getElementById('next-work-order-image').click(); });
            </script>
        @endif
</x-app-layout>
