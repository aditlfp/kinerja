<x-app-layout>
    @php
        $firstDate = $checkpoint->items->first()?->tanggal ?? $checkpoint->created_at;
    @endphp
    <div class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">{{ $isMcs ? 'Manager CS Bukti Pekerjaan' : 'Direksi Bukti Pekerjaan' }}</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900 capitalize">Riwayat pekerjaan</h1>
                <p class="mt-1 text-sm capitalize text-slate-500">
                    {{ strtolower($checkpoint->user->nama_lengkap ?? '-') }} &middot;
                    {{ Carbon\Carbon::parse($firstDate)->locale('id')->translatedFormat('d F Y') }}</p>
            </div>
            <a href="{{ $isMcs
                ? route('mcs.work-order.calendar', [
                    'user' => $checkpoint->user_id,
                    'month' => \Carbon\Carbon::parse($firstDate)->format('Y-m'),
                ])
                : route('direksi.cp.calendar', [
                    'user' => $checkpoint->user_id,
                    'month' => \Carbon\Carbon::parse($firstDate)->format('Y-m'),
                ]) }}"
                class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-sky-300 hover:text-sky-600"
                aria-label="Kembali"><i class="ri-arrow-left-line text-xl"></i></a>
        </div>
        <div class="space-y-4">
            @foreach ($checkpoint->items as $item)
                @php
                    $jobId = $item->pekerjaan_cp_id;
                    $manual = $item->input_manual;
                    $status = $item->approve_status;
                    $rowImages = $item->images->pluck('path')->all();
                @endphp
                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)]">
                    <h2 class="font-bold capitalize text-slate-800">
                      {{ $item->pekerjaanCp?->name ?? ($item->input_manual ?: 'Pekerjaan') }}
                    </h2>
                    @if (count($rowImages))
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($rowImages as $image)
                                <a href="{{ asset('storage/images/' . $image) }}" target="_blank" rel="noopener"><img
                                        src="{{ asset('storage/images/' . $image) }}" alt="Foto pekerjaan"
                                        class="rounded-lg ring-1 ring-slate-200"></a>
                            @endforeach
                        </div>
                    @endif
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-600">
                        {{ $item->deskripsi ?: 'Tidak ada deskripsi.' }}</p>

                    @if ($item->note)
                        <p class="mt-2 rounded-lg  px-3 py-2 text-xs {{ $status == 'denied' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}"><strong>Alasan:</strong>
                            {{ $item->note }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <p class="text-xs text-slate-400">
                            {{ $item->tanggal?->format('Y-m-d') ?? optional($checkpoint->created_at)->format('Y-m-d') }}
                        </p>
                        <div class="flex items-center gap-2 w-full">
                            <span
                                class="rounded-lg px-2.5 py-1 text-sm font-semibold {{ $status === 'accept' ? 'bg-emerald-50 text-emerald-700' : ($status === 'denied' ? 'bg-rose-50 text-rose-700' : '') }}">
                                @if ($status === 'accept')
                                    Diterima
                                @elseif($status === 'denied')
                                    Ditolak
                                @endif
                            </span>
                            @if($status != 'accept' && ! $isMcs)
                            <div class="w-full flex justify-end items-center gap-2">
                                <button type="button"
                                    onclick="openCheckpointActionModal({{ $loop->index }}, 'accept')"
                                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-emerald-600 hover:shadow-md active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                    </svg>

                                    Terima
                                </button>

                                <button type="button"
                                    onclick="openCheckpointActionModal({{ $loop->index }}, 'denied')"
                                    class="{{ $status == 'denied' ? 'hidden' : '' }} inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 shadow-sm transition-all duration-200 hover:border-rose-300 hover:bg-rose-100 hover:text-rose-700 hover:shadow-md active:scale-95 {{ $status == 'denied' ? 'hidden' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>

                                    Tolak
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    @unless ($isMcs)
    <div id="checkpointActionModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">

        <form method="POST" action="{{ route('direksi.cp.history.approve', $checkpoint->id) }}"
            class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">

            @csrf
            @method('PATCH')

            <input type="hidden" name="index" id="actionIndex">
            <input type="hidden" name="status" id="actionStatus">

            {{-- Header --}}
            <div class="flex items-start gap-3">
                <span id="actionIcon"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-lg text-emerald-600">
                    <i class="ri-checkbox-circle-line"></i>
                </span>

                <div>
                    <h3 id="actionTitle" class="font-bold text-slate-900">
                        Setujui checkpoint
                    </h3>

                    <p id="actionDescription" class="text-xs text-slate-500">
                        Pastikan checkpoint sudah sesuai sebelum menyetujui.
                    </p>
                </div>
            </div>

            {{-- Note --}}
            <div class="mt-5">
                <label id="actionNoteLabel" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Catatan
                </label>

                <textarea name="note" id="actionNote" rows="3" placeholder="Tambahkan catatan..."
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition  focus:ring-2 "></textarea>

                @error('note')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="closeCheckpointActionModal()"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit" id="actionSubmit"
                    class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-600">
                    Setujui
                </button>
            </div>
        </form>
    </div>

    <script>
        const checkpointActionModal = document.getElementById('checkpointActionModal');

        const actionIndex = document.getElementById('actionIndex');
        const actionStatus = document.getElementById('actionStatus');

        const actionIcon = document.getElementById('actionIcon');
        const actionTitle = document.getElementById('actionTitle');
        const actionDescription = document.getElementById('actionDescription');

        const actionNoteLabel = document.getElementById('actionNoteLabel');
        const actionNote = document.getElementById('actionNote');

        const actionSubmit = document.getElementById('actionSubmit');


        function openCheckpointActionModal(index, status) {

            actionIndex.value = index;
            actionStatus.value = status;
            actionNote.value = '';

            // =========================
            // APPROVE
            // =========================
            if (status === 'accept') {

                actionIcon.className =
                    'grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-lg text-emerald-600';

                actionIcon.innerHTML =
                    '<i class="ri-checkbox-circle-line"></i>';

                actionTitle.textContent =
                    'Setujui checkpoint';

                actionDescription.textContent =
                    'Pastikan checkpoint sudah sesuai sebelum menyetujui.';

                actionNote.classList.remove('focus:border-rose-300', 'focus:ring-rose-100');
                actionNote.classList.add('focus:border-emerald-300', 'focus:ring-emerald-100');

                actionNoteLabel.textContent =
                    'Catatan (opsional)';

                actionNote.placeholder =
                    'Tambahkan catatan jika diperlukan...';

                actionNote.required = false;

                actionSubmit.textContent =
                    'Setujui';

                actionSubmit.className =
                    'rounded-xl bg-emerald-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-600';

            }

            // =========================
            // DENIED
            // =========================
            else if (status === 'denied') {

                actionIcon.className =
                    'grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-lg text-rose-600';

                actionIcon.innerHTML =
                    '<i class="ri-close-circle-line"></i>';

                actionTitle.textContent =
                    'Tolak checkpoint';

                actionDescription.textContent =
                    'Alasan wajib diisi sebelum menolak checkpoint.';

                actionNote.classList.remove('focus:border-emerald-300', 'focus:ring-emerald-100');
                actionNote.classList.add('focus:border-rose-300', 'focus:ring-rose-100');

                actionNoteLabel.textContent =
                    'Alasan penolakan';

                actionNote.placeholder =
                    'Tulis alasan penolakan...';

                actionNote.required = true;

                actionSubmit.textContent =
                    'Tolak';

                actionSubmit.className =
                    'rounded-xl bg-rose-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-rose-600';
            }

            checkpointActionModal.classList.remove('hidden');
            checkpointActionModal.classList.add('flex');

            // Focus hanya ketika denied
            setTimeout(() => {
                actionNote.focus();
            }, 100);
        }


        function closeCheckpointActionModal() {
            checkpointActionModal.classList.add('hidden');
            checkpointActionModal.classList.remove('flex');

            actionNote.value = '';
            actionStatus.value = '';
        }


        // Klik backdrop
        checkpointActionModal.addEventListener('click', (e) => {
            if (e.target === checkpointActionModal) {
                closeCheckpointActionModal();
            }
        });


        // Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCheckpointActionModal();
            }
        });
    </script>
    @endunless
</x-app-layout>
