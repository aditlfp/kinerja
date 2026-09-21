@if (isset($missingCheckpointDates) && $missingCheckpointDates->isNotEmpty())
    <div id="checkpoint-reminder" class="fixed inset-0 z-[9000] flex items-center justify-center bg-slate-500/10 backdrop-blur-sm">
        <div class="mx-6 w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_24px_60px_-24px_rgba(15,23,42,.35)]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Informasi</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Belum Upload Bukti Kerja</h2>
                </div>
                <button type="button" class="closeCheckpointReminder btn btn-sm bg-red-500 text-white border-0 hover:bg-red-400/20 hover:text-red-500 capitalize">&times;</button>
            </div>
            <p class="mt-3 text-sm text-slate-600">Tanggal berikut pada minggu ini belum memiliki bukti pekerjaan:</p>
            <ul class="mt-3 space-y-1.5">
                @foreach ($missingCheckpointDates as $date)
                    <li class="flex items-center gap-2 rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700">
                        <i class="ri-error-warning-line"></i>{{ $date->translatedFormat('l, d F Y') }}
                    </li>
                @endforeach
            </ul>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="dismissCheckpointReminder" class="btn btn-sm bg-amber-500 text-white border-0 hover:bg-amber-400/20 hover:text-amber-500 capitalize">Jangan tampilkan lagi hari ini</button>
                <a href="{{ route('checkpoint-user.index', ['month' => $missingCheckpointDates->first()->format('Y-m')]) }}" class="btn btn-sm bg-sky-500 text-white border-0 hover:bg-sky-400/20 hover:text-sky-500 capitalize">Isi sekarang</a>
            </div>
        </div>
    </div>
    <script>
        const reminderKey = 'checkpoint-reminder-dismissed-until';
        const reminder = document.getElementById('checkpoint-reminder');
        const dismissedUntil = Number(localStorage.getItem(reminderKey) || 0);

        if (dismissedUntil > Date.now()) reminder?.remove();

        document.querySelectorAll('.closeCheckpointReminder').forEach((button) => {
            button.addEventListener('click', () => reminder?.remove());
        });
        document.getElementById('dismissCheckpointReminder')?.addEventListener('click', () => {
            const endOfDay = new Date();
            endOfDay.setHours(23, 59, 59, 999);
            localStorage.setItem(reminderKey, String(endOfDay.getTime()));
            reminder?.remove();
        });
    </script>
@endif
