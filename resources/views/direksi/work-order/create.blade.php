<x-app-layout>
    <div class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-8 sm:py-10">
        <div class="mb-6 flex items-center justify-between gap-4">
            <a href="{{ route(($isMcs ? 'mcs' : 'direksi') . '.work-order.calendar', $user->id) }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-sky-600 transition hover:text-sky-700">
                <i class="ri-arrow-left-line"></i> Kembali ke kalender
            </a>
            <time
                class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ $tanggal }}</time>
        </div>

        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Perintah Kerja</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 capitalize">Buat perintah untuk
                {{ $user->nama_lengkap }}</h1>
            <p class="mt-2 text-sm text-slate-500">Tambahkan instruksi pekerjaan baru untuk tanggal ini.</p>
        </div>

        @if ($orders->isNotEmpty())
            <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="font-bold text-slate-900">Riwayat perintah kerja</h2>
                            <p class="mt-1 text-sm text-slate-500">Perintah yang sudah dibuat pada tanggal ini.</p>
                        </div>
                        <span
                            class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">{{ $orders->count() }}
                            perintah</span>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($orders as $order)
                        <article class="flex gap-4 px-5 py-5 sm:px-6">
                            <div
                                class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                                <i class="ri-file-list-3-line text-lg"></i>
                            </div>
                            <div class="w-full flex justify-between items-center">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-bold text-slate-900">Perintah Kerja #{{ $order->id }}
                                        </p>
                                        <span
                                            class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $order->has_read ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $order->has_read ? 'Sudah dibaca' : 'Belum dibaca' }}
                                        </span>
                                        <span
                                            class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $order->has_complete ? 'bg-emerald-100 text-emerald-500' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $order->has_complete ? 'Sudah Selesai' : 'Belum Selesai' }}</span>
                                    </div>
                                    <div>
                                        <p class="whitespace-pre-line text-sm text-slate-600">
                                            {{ $order->deskripsi }}</p>
                                        <p class="mt-3 text-xs text-slate-400">Dibuat
                                            {{ $order->created_at?->format('H:i') . ' WIB' ?? '-' }} &middot; Dibuat oleh
                                            {{ $order->creator_name }}</p>
                                    </div>
                                </div>
                                <div>
                                    @if ($order->has_complete)
                                    <form action="{{ route(($isMcs ? 'mcs' : 'direksi') . '.cp.history.show', $order->id) }}">
                                        <input type="hidden" name="worker" value="true">
                                        <button
                                            type="submit"
                                            class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 flex gap-2"><i
                                                class="ri-eye-line"></i>Lihat</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
        @php
            $selectedDate = \Carbon\Carbon::parse($tanggal);
            $isPast = $selectedDate->isBefore(today());
        @endphp
        @if($isPast)
            <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-8 sm:p-10">
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200">
                        <i class="ri-lock-2-line text-2xl"></i>
                    </div>

                    <h2 class="mt-4 text-base font-bold text-slate-800">
                        Form Dikunci
                    </h2>

                    <p class="mt-1.5 max-w-sm text-sm text-slate-500">
                        Form perintah kerja tidak tersedia karena tanggal yang dipilih
                        sudah berlalu.
                    </p>

                    <span class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 ring-1 ring-slate-200">
                        <i class="ri-calendar-line"></i>
                        {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                    </span>
                </div>
            </section>
        @else
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-5 flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500 text-white shadow-sm">
                        <i class="ri-add-line text-xl"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">Perintah baru</h2>
                        <p class="mt-1 text-sm text-slate-500">Tulis instruksi yang jelas dan mudah dijalankan.</p>
                    </div>
                </div>
                <form method="POST" enctype="multipart/form-data" action="{{ route(($isMcs ? 'mcs' : 'direksi') . '.work-order.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <div>
                        <label for="deskripsi" class="mb-2 block text-sm font-bold text-slate-700">Deskripsi
                            pekerjaan</label>
                        <textarea id="deskripsi" name="deskripsi" rows="7" required maxlength="5000"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                            placeholder="Contoh: Periksa kondisi panel listrik lantai 2 dan laporkan hasilnya.">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Lampiran berkas <span class="font-normal text-slate-400">(opsional, maksimal 7)</span></label>
                        <label for="images" id="dropzone" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition hover:border-sky-400 hover:bg-sky-50">
                            <i class="ri-upload-cloud-2-line text-3xl text-sky-500"></i>
                            <span class="mt-2 text-sm font-bold text-slate-700">Tarik berkas ke sini atau klik untuk memilih</span>
                            <span id="image-count" class="mt-1 text-xs text-slate-500">0/7 file · gambar, video, PDF, DOCX, XLSX · maksimal 5 MB/file</span>
                        </label>
                        <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/webm,application/pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden">
                        <div id="image-preview" class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-7"></div>
                        @error('images') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        @error('images.*') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-500 px-5 py-3 font-bold text-white shadow-sm transition hover:bg-sky-600 active:scale-[.99]">
                        <i class="ri-send-plane-line"></i> Simpan Perintah Kerja
                    </button>
                </form>
            </section>
        @endif
    </div>
    <script>
        const input = document.getElementById('images'), preview = document.getElementById('image-preview'), count = document.getElementById('image-count'), dropzone = document.getElementById('dropzone');
        let files = [];
        function render() { preview.innerHTML = ''; files.forEach((file, i) => { const box = document.createElement('div'); box.className = 'relative overflow-hidden rounded-xl border border-slate-200 bg-slate-100 p-2 text-xs'; box.innerHTML = file.type.startsWith('image/') ? `<img src="${URL.createObjectURL(file)}" class="aspect-square w-full rounded-lg object-cover">` : `<div class="flex aspect-square items-center justify-center text-center font-semibold text-slate-600">${file.name}</div>`; box.insertAdjacentHTML('beforeend', `<button type="button" data-i="${i}" class="absolute right-1 top-1 rounded-full bg-slate-900/75 px-2 py-1 text-xs font-bold text-white">×</button>`); preview.append(box); }); count.textContent = `${files.length}/7 file · maksimal 5 MB/file`; const dt = new DataTransfer(); files.forEach(f => dt.items.add(f)); input.files = dt.files; }
        function add(list) { files = [...files, ...list].slice(0, 7); render(); }
        input.addEventListener('change', e => add([...e.target.files]));
        ['dragenter','dragover'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.add('border-sky-500','bg-sky-50'); }));
        ['dragleave','drop'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.remove('border-sky-500','bg-sky-50'); }));
        dropzone.addEventListener('drop', e => add([...e.dataTransfer.files]));
        preview.addEventListener('click', e => { if (e.target.dataset.i !== undefined) { files.splice(+e.target.dataset.i, 1); render(); } });
    </script>
</x-app-layout>
