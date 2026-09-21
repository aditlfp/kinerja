<x-app-layout>
    <div class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Direksi Bukti Pekerjaan</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900 capitalize">Riwayat pekerjaan</h1>
                <p class="mt-1 text-sm capitalize text-slate-500">
                    {{ strtolower($checkpoint->user->nama_lengkap ?? '-') }} &middot;
                    {{ Carbon\Carbon::parse($checkpoint->tanggal[0])->locale('id')->translatedFormat('d F Y') }}</p>
            </div>
            <a href="{{ route('direksi.cp.calendar', [
                        'user' => $checkpoint->user_id,
                        'month' => \Carbon\Carbon::parse($checkpoint->tanggal[0])->format('Y-m'),
                    ]) }}"
                class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-sky-300 hover:text-sky-600"
                aria-label="Kembali"><i class="ri-arrow-left-line text-xl"></i></a>
        </div>
        <div class="space-y-4">
            @foreach (array_values((array) $checkpoint->deskripsi) as $i => $description)
                @php($jobId = array_values((array) $checkpoint->pekerjaan_cp_id)[$i] ?? null)
                @php($manual = array_values((array) $checkpoint->input_manual)[$i] ?? null)
                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)]">
                    <h2 class="font-bold capitalize text-slate-800">{{ $jobs[$jobId]->name ?? ($manual ?? 'Pekerjaan') }}
                    </h2>
                    @php($rowImages = data_get((array) $checkpoint->img, $i, []))
                    @php($rowImages = is_array($rowImages) ? $rowImages : explode(',', (string) $rowImages))
                    @php($rowImages = array_filter($rowImages))
                    @if (count($rowImages))
                        <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                            @foreach ($rowImages as $image)
                                <a href="{{ asset('storage/images/' . $image) }}" target="_blank" rel="noopener"><img
                                        src="{{ asset('storage/images/' . $image) }}" alt="Foto pekerjaan"
                                        class="h-20 w-full rounded-lg object-cover ring-1 ring-slate-200"></a>
                            @endforeach
                        </div>
                    @endif
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-600">
                        {{ $description ?: 'Tidak ada deskripsi.' }}</p>
                    @if (data_get((array) $checkpoint->note, $i))<p class="mt-2 rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700"><strong>Alasan:</strong> {{ data_get((array) $checkpoint->note, $i) }}</p>@endif
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <p class="text-xs text-slate-400">
                            {{ data_get((array) $checkpoint->tanggal, $i, optional($checkpoint->created_at)->format('Y-m-d')) }}
                        </p>
                        @php($status = data_get((array) $checkpoint->approve_status, $i))
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-lg px-2.5 py-1 text-sm font-semibold {{ $status === 'accept' ? 'bg-emerald-50 text-emerald-700' : ($status === 'denied' ? 'bg-rose-50 text-rose-700' : '') }}">@if($status === 'accept') Diterima @elseif($status === 'denied') Ditolak  @endif</span>
                            <form method="POST" action="{{ route('direksi.cp.history.approve', $checkpoint->id) }}"
                                class="flex gap-1.5 {{ $status != null && $status != 'proccess' && $status != 'denied' ? 'hidden' : '' }}  ">@csrf @method('PATCH')<input type="hidden" name="index"
                                    value="{{ $i }}"><input type="hidden" name="note" value=""><button name="status" value="accept"
                                    class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-600">Terima</button><button
                                    type="button" onclick="openRejectModal({{ $i }}, this)"
                                    class="rounded-lg bg-rose-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-600 {{ $status == 'denied' ? 'hidden' : ''}}">Tolak</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <form method="POST" action="{{ route('direksi.cp.history.approve', $checkpoint->id) }}"
            class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            @csrf @method('PATCH')
            <input type="hidden" name="index" id="rejectIndex">
            <input type="hidden" name="status" value="denied">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-rose-50 text-lg text-rose-600"><i class="ri-close-circle-line"></i></span>
                <div><h3 class="font-bold text-slate-900">Tolak checkpoint</h3><p class="text-xs text-slate-500">Alasan wajib diisi sebelum menolak.</p></div>
            </div>
            <label class="mt-5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Alasan penolakan</label>
            <textarea name="note" id="rejectNote" rows="3" required placeholder="Tulis alasan penolakan..." class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-rose-300 focus:outline-none"></textarea>
            @error('note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-bold text-white hover:bg-rose-600">Tolak</button>
            </div>
        </form>
    </div>

    <script>
        const rejectModal = document.getElementById('rejectModal');
        function openRejectModal(index) {
            document.getElementById('rejectIndex').value = index;
            document.getElementById('rejectNote').value = '';
            rejectModal.classList.remove('hidden');
            rejectModal.classList.add('flex');
            document.getElementById('rejectNote').focus();
        }
        function closeRejectModal() {
            rejectModal.classList.add('hidden');
            rejectModal.classList.remove('flex');
        }
        rejectModal.addEventListener('click', (e) => { if (e.target === rejectModal) closeRejectModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeRejectModal(); });
    </script>
</x-app-layout>
