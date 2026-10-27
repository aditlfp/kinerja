<x-app-layout>
        <div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-8 lg:px-12">
            <div class="mt-10 mb-9 flex items-center justify-center gap-6">
                <div class="w-full flex justify-between items-center gap-2">
                    <a href="{{ route('checkpoint-user.history', ['month' => $previousMonth]) }}" aria-label="Bulan sebelumnya" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i class="ri-arrow-left-s-line text-xl"></i></a>
                    <div class="flex flex-col items-center">
                        <h1 class="text-3xl font-semibold overflow-hidden tracking-tight text-slate-900 sm:text-4xl">{{ $start->translatedFormat('F Y') }}</h1>
                        <p class="mt-2 text-sm text-slate-500 hidden sm:block">Pilih tanggal untuk melihat riwayat pekerjaan.</p>
                    </div>
                    <a href="{{ route('checkpoint-user.history', ['month' => $nextMonth]) }}" aria-label="Bulan berikutnya" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i class="ri-arrow-right-s-line text-xl"></i></a>
                </div>
            </div>

            @php
            // Tanggal yang bukti pekerjaannya masih menunggu verifikasi Direksi.
            // Baris kalender sudah membawa hitungan per status, jadi daftar ini
            // tidak menambah query apa pun.
                $pendingDays = $calendar
                    ->filter(fn ($item) => $item['hasData'] && $item['recordId'] && $item['counts']['process'] > 0)
                    ->values();
                $pendingTotal = (int) $pendingDays->sum(fn ($item) => $item['counts']['process']);
                $monthHasData = $calendar->contains(fn ($item) => $item['hasData']);
            @endphp

            <section
                class="mt-4 mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] sm:p-5"
                data-pending-days>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600">
                            <i class="ri-time-line"></i>
                        </span>
                        Tanggal Belum Di Approve
                        @if ($pendingDays->isNotEmpty())
                            <span
                                class="rounded-full bg-amber-500 px-2 py-0.5 text-[11px] font-bold text-white">{{ $pendingTotal }}</span>
                        @endif
                    </h2>
                    <span class="text-xs font-semibold text-slate-500">{{ $start->translatedFormat('F Y') }}</span>
                </div>

                @if ($pendingDays->isEmpty())
                    <p
                        class="mt-3 flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2.5 text-xs text-emerald-700">
                        <i class="ri-checkbox-circle-line text-base"></i>
                        {{ $monthHasData
                            ? 'Semua bukti pekerjaan bulan ini sudah di-approve Direksi.'
                            : 'Belum ada bukti pekerjaan yang diisi bulan ini.' }}
                    </p>
                @else
                    <p class="mt-2 text-xs text-slate-500">
                        {{ $pendingTotal }} bukti pekerjaan pada {{ $pendingDays->count() }} tanggal belum
                        di-approve Direksi. Klik tanggal untuk membuka pekerjaannya.
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($pendingDays as $item)
                            <a href="{{ route('checkpoint-user.edit', [
                                'checkpoint_user' => $item['recordId'],
                                'tanggal' => $item['date']->format('Y-m-d'),
                            ]) }}"
                                data-pending-date="{{ $item['date']->toDateString() }}"
                                data-pending-date-count="{{ $item['counts']['process'] }}"
                                title="Buka pekerjaan {{ $item['date']->translatedFormat('l, d F Y') }}"
                                class="group inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-800 transition duration-300 hover:border-amber-300 hover:bg-amber-100 active:scale-[.98]">
                                <span
                                    class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-amber-500 text-[11px] font-bold tabular-nums text-white">{{ $item['date']->day }}</span>
                                <span>{{ $item['date']->translatedFormat('d M') }}</span>
                                <span
                                    class="rounded-md bg-white px-1.5 py-0.5 text-[10px] font-bold tabular-nums text-amber-700">{{ $item['counts']['process'] }}
                                    bukti</span>
                                <i
                                    class="ri-arrow-right-s-line text-sm text-amber-500 transition group-hover:translate-x-0.5"></i>
                            </a>
                        @endforeach
                    </div>
                @endif

            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] sm:p-7">
                <div class="grid grid-cols-7 gap-1.5 pb-3 text-center text-[11px] font-semibold uppercase tracking-[.12em] text-slate-400">
                    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)<div>{{ $day }}</div>@endforeach
                </div>
                <div class="grid grid-cols-7 gap-1.5 overflow-hidden">
                    @php
                        $daysBefore = $start->dayOfWeekIso - 1;
                        $daysInMonth = $start->daysInMonth;
                        $daysAfter = (7 - (($daysBefore + $daysInMonth) % 7)) % 7;
                        $nextStart = $start->copy()->addMonth()->startOfMonth();
                    @endphp
                    @for ($i = $daysBefore; $i > 0; $i--)
                        @php($date = $start->copy()->subDays($i))
                        <span class="flex min-h-[74px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-xl border border-slate-100 bg-slate-50/60 p-2.5 text-slate-300" aria-disabled="true"><span class="text-sm leading-none overflow-hidden">{{ $date->day }}</span></span>
                    @endfor
                    @foreach ($calendar as $index => $item)
                        @php($date = $item['date'])
                        @php($state = $date->isWeekend() ? 'border-transparent bg-rose-500 text-white' : ($item['rejected'] ? 'border-transparent bg-amber-500 text-white' : ($item['accepted'] ? 'border-transparent bg-sky-500 text-white' : ($item['hasData'] ? 'border-transparent bg-emerald-500 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-sky-300'))))
                        <a href="{{ $item['hasData'] && $item['recordId'] ? route('checkpoint-user.history.show', ['id' => $item['recordId']]) : route('checkpoint-user.history', ['month' => $start->format('Y-m')]) }}"
                           style="animation-delay: {{ min($index * 12, 320) }}ms"
                           class="reveal-cell group relative flex min-h-[74px] min-w-0 flex-col justify-between overflow-hidden rounded-xl border p-2.5 font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:-translate-y-0.5 hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)] active:scale-[.98] {{ $state }} {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-2' : '' }}"
                           title="{{ $date->translatedFormat('l, d F Y') }}">
                            <span class="text-sm leading-none overflow-hidden">{{ $date->day }}</span>
                            @if ($item['hasData'])
                                <x-checkpoint-status-row :counts="$item['counts']" :has-data="true" size="md" />
                            @elseif ($date->isWeekend())
                                <span class="hidden sm:block text-[10px] font-semibold uppercase tracking-wide opacity-85">Libur</span>
                            @endif
                        </a>
                    @endforeach
                    @for ($i = 1; $i <= $daysAfter; $i++)
                        @php($date = $nextStart->copy()->addDays($i - 1))
                        <span class="flex min-h-[74px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-xl border border-slate-100 bg-slate-50/60 p-2.5 text-slate-300" aria-disabled="true"><span class="text-sm leading-none overflow-hidden">{{ $date->day }}</span></span>
                    @endfor
                </div>
            </section>

            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-xs text-slate-500">
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-sky-500"></span>Diterima</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-emerald-500"></span>Ada riwayat</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] border border-slate-200 bg-white"></span>Belum diisi</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-amber-500"></span>Ada yang ditolak</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-rose-500"></span>Sabtu &amp; Minggu</span>
                <span class="mx-1 hidden h-4 w-px bg-slate-200 sm:block"></span>
                <span class="flex items-center gap-2"><i class="ri-checkbox-circle-fill text-[13px] text-sky-600"></i>Disetujui</span>
                <span class="flex items-center gap-2"><i class="ri-close-circle-fill text-[13px] text-rose-600"></i>Ditolak</span>
                <span class="flex items-center gap-2"><i class="ri-time-line text-[13px] text-amber-600"></i>Menunggu verifikasi</span>
            </div>
        </div>

        <script>
            (function () {
                const cells = document.querySelectorAll('.reveal-cell');
                const show = (el) => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; };

                if (!('IntersectionObserver' in window)) { cells.forEach(show); return; }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        show(entry.target);
                        observer.unobserve(entry.target);
                    });
                }, { threshold: 0.05, rootMargin: '0px 0px -40px 0px' });

                cells.forEach((cell) => {
                    const delay = (cell.style.animationDelay || '0ms');
                    cell.style.opacity = '0';
                    cell.style.transform = 'translateY(18px)';
                    cell.style.transition = `opacity .6s cubic-bezier(.22,1,.36,1) ${delay}, transform .6s cubic-bezier(.22,1,.36,1) ${delay}`;
                    observer.observe(cell);
                });
            })();
        </script>
</x-app-layout>
