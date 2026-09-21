@if (isset($rejectedCheckpoints) && $rejectedCheckpoints->isNotEmpty())
    <div id="checkpoint-rejected" class="fixed inset-0 z-[9000] flex items-center justify-center bg-slate-500/10 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_24px_60px_-24px_rgba(15,23,42,.35)]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-rose-600">Pemberitahuan</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Bukti Pekerjaan Ditolak</h2>
                </div>
                <button type="button" class="closeCheckpointRejected btn btn-sm bg-red-500 text-white border-0 hover:bg-red-400/20 hover:text-red-500">&times;</button>
            </div>
            <p class="mt-3 text-sm text-slate-600">Pengajuan bukti pekerjaan pada tanggal berikut ditolak dan perlu diperbaiki:</p>
            <ul class="mt-3 max-h-64 space-y-2 overflow-y-auto">
                @foreach ($rejectedCheckpoints as $item)
                    <li class="rounded-xl bg-rose-50 px-3 py-2.5 text-sm">
                        <p class="flex items-center gap-2 font-semibold text-rose-700"><i class="ri-close-circle-line"></i>{{ \Carbon\Carbon::parse($item->date)->translatedFormat('l, d F Y') }}</p>
                        <p class="mt-1 text-xs text-rose-600"><strong>Alasan:</strong> {{ $item->reason }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="dismissCheckpointRejected" class="btn btn-sm bg-amber-500 text-white border-0 hover:bg-amber-400/20 hover:text-amber-500">Tutup</button>
                <a href="{{ route('checkpoint-user.history', ['month' => \Carbon\Carbon::parse($rejectedCheckpoints->first()->date)->format('Y-m')]) }}" class="btn btn-sm bg-sky-500 text-white border-0 hover:bg-sky-400/20 hover:text-sky-500">Lihat riwayat</a>
            </div>
        </div>
    </div>
    <script>
        const rejectedKey = 'checkpoint-rejected-dismissed-until';
        const rejectedModal = document.getElementById('checkpoint-rejected');
        const rejectedUntil = Number(localStorage.getItem(rejectedKey) || 0);

        if (rejectedUntil > Date.now()) rejectedModal?.remove();

        document.querySelectorAll('.closeCheckpointRejected').forEach((button) => {
            button.addEventListener('click', () => rejectedModal?.remove());
        });
        document.getElementById('dismissCheckpointRejected')?.addEventListener('click', () => {
            const endOfDay = new Date();
            endOfDay.setHours(23, 59, 59, 999);
            localStorage.setItem(rejectedKey, String(endOfDay.getTime()));
            rejectedModal?.remove();
        });
    </script>
@endif
