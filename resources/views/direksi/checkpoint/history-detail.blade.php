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
                <article id="item-{{ $item->id }}"
                    class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)]">
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

                    {{-- The verdict note is rendered by the thread partial's
                         "Catatan Evaluasi" banner now. --}}
                    @include('check.partials.item-thread', ['item' => $item, 'viewerRole' => 'management'])

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <p class="text-xs text-slate-400">
                            {{ $item->tanggal?->format('Y-m-d') ?? optional($checkpoint->created_at)->format('Y-m-d') }}
                        </p>
                        <div class="flex items-center gap-2 w-full">
                            <x-checkpoint-status-badge :status="$status" size="md" />
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
            class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">

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

            {{-- Quick reactions: one tap approves without writing anything.
                 The key travels as `emoji` and is re-checked server-side, so a
                 tampered value can never reach the stored note. --}}
            <div id="actionEmojiBlock" class="mt-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Setujui dengan emoji</p>
                <p class="mt-1 text-xs leading-relaxed text-slate-400">Cocok untuk pekerjaan yang sudah benar dan
                    tidak membutuhkan penjelasan panjang.</p>

                <input type="hidden" name="emoji" id="actionEmoji">

                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (\App\Models\CheckPointItem::APPROVAL_REACTIONS as $key => $reaction)
                        <button type="button" data-emoji-choice="{{ $key }}" aria-pressed="false"
                            class="flex flex-col items-center gap-1 rounded-xl border border-slate-200 bg-white px-2 py-3 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:bg-emerald-50">
                            <i class="{{ $reaction['icon'] }} {{ $reaction['tone'] }} text-3xl leading-none"
                                aria-hidden="true"></i>
                            <span>{{ $reaction['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                @error('emoji')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
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

        const actionEmojiBlock = document.getElementById('actionEmojiBlock');
        const actionEmoji = document.getElementById('actionEmoji');
        const emojiChoices = Array.from(document.querySelectorAll('[data-emoji-choice]'));

        const emojiChoiceIdle = ['border-slate-200', 'bg-white', 'text-slate-600'];
        const emojiChoiceActive = ['border-emerald-400', 'bg-emerald-50', 'text-emerald-700'];


        function selectEmoji(key) {

            actionEmoji.value = key || '';

            emojiChoices.forEach((button) => {

                const active = button.dataset.emojiChoice === actionEmoji.value;

                button.setAttribute('aria-pressed', active ? 'true' : 'false');
                button.classList.remove(...(active ? emojiChoiceIdle : emojiChoiceActive));
                button.classList.add(...(active ? emojiChoiceActive : emojiChoiceIdle));
            });
        }


        emojiChoices.forEach((button) => {
            button.addEventListener('click', () => {
                // Tapping the chosen chip again clears it, so an accidental
                // reaction never has to be reset by closing the modal.
                selectEmoji(actionEmoji.value === button.dataset.emojiChoice ? '' : button.dataset.emojiChoice);
            });
        });


        function openCheckpointActionModal(index, status) {

            actionIndex.value = index;
            actionStatus.value = status;
            actionNote.value = '';
            selectEmoji('');

            // =========================
            // APPROVE
            // =========================
            if (status === 'accept') {

                actionEmojiBlock.classList.remove('hidden');

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
                    'Komentar (opsional)';

                actionNote.placeholder =
                    'Tambahkan apresiasi atau arahan tambahan...';

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

                actionEmojiBlock.classList.add('hidden');

                actionIcon.className =
                    'grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-lg text-rose-600';

                actionIcon.innerHTML =
                    '<i class="ri-close-circle-line"></i>';

                actionTitle.textContent =
                    'Minta revisi atau klarifikasi';

                actionDescription.textContent =
                    'Jelaskan bagian yang perlu dilengkapi agar karyawan tahu apa yang harus diperbaiki.';

                actionNote.classList.remove('focus:border-emerald-300', 'focus:ring-emerald-100');
                actionNote.classList.add('focus:border-rose-300', 'focus:ring-rose-100');

                actionNoteLabel.textContent =
                    'Catatan revisi';

                actionNote.placeholder =
                    'Mohon dilengkapi keterangannya terlebih dahulu...';

                actionNote.required = true;

                actionSubmit.textContent =
                    'Minta revisi';

                actionSubmit.className =
                    'rounded-xl bg-rose-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-rose-600';
            }

            checkpointActionModal.classList.remove('hidden');
            checkpointActionModal.classList.add('flex');

            // Focus hanya ketika denied; approve starts with the emoji row.
            setTimeout(() => {
                if (status === 'denied') {
                    actionNote.focus();
                }
            }, 100);
        }


        function closeCheckpointActionModal() {
            checkpointActionModal.classList.add('hidden');
            checkpointActionModal.classList.remove('flex');

            actionNote.value = '';
            actionStatus.value = '';
            selectEmoji('');
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

    <script>
        // Arriving from the note history (#item-<id>): the browser resolves the
        // fragment while parsing, before Tailwind applies `scroll-mt-24`, so the
        // row lands under the fixed phone bar. Re-align once styles and images
        // have settled.
        (function () {
            if (!location.hash) return;

            var target = document.getElementById(location.hash.slice(1));
            if (!target) return;

            var align = function () {
                var top = target.getBoundingClientRect().top + window.scrollY - 88;
                window.scrollTo(0, Math.max(0, top));
            };

            if (document.readyState === 'complete') {
                align();
            } else {
                window.addEventListener('load', align, { once: true });
            }
        })();
    </script>
</x-app-layout>
