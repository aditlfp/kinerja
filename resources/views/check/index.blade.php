<x-app-layout>
    @inject('checkReportWindow', \App\Services\CheckPointReportWindow::class)
    @php
        $reportLocked = $checkReportWindow->blocks();
        $lockMessage = $checkReportWindow->message();
        $nextOpenAt = $checkReportWindow->nextOpenAt();
    @endphp

    <div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-8 lg:px-12">
        <div class="mt-10 mb-9 flex items-center justify-center gap-6">
            <div class="w-full flex justify-between items-center gap-2">
                <a href="{{ route('checkpoint-user.index', ['month' => $previousMonth]) }}" aria-label="Bulan sebelumnya"
                    class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i
                        class="ri-arrow-left-s-line text-xl"></i></a>
                <div class="flex flex-col items-center">
                    <h1 class="text-3xl font-semibold overflow-hidden tracking-tight text-slate-900 sm:text-4xl">
                        {{ $start->translatedFormat('F Y') }}</h1>
                    <p class="mt-2 text-sm text-slate-500 hidden sm:block">Pilih tanggal untuk melihat atau mengisi
                        pekerjaan.</p>
                </div>
                <a href="{{ route('checkpoint-user.index', ['month' => $nextMonth]) }}" aria-label="Bulan berikutnya"
                    class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i
                        class="ri-arrow-right-s-line text-xl"></i>                </a>
            </div>
        </div>

        @if ($reportLocked || session('checkpoint_locked'))
            <div
                class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-amber-800">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600">
                    <i class="ri-lock-2-line text-lg"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold">Laporan pekan ini sudah ditutup</p>
                    <p class="mt-0.5 text-xs leading-5 text-amber-700">
                        {{ session('checkpoint_locked') ?? $lockMessage }}</p>
                    <p class="mt-1 text-xs font-semibold text-amber-700">
                        Dibuka lagi {{ $nextOpenAt->translatedFormat('l, d F Y') }} pukul 00:00.</p>
                </div>
            </div>
        @endif

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
                        @php
                            // Pekan ditutup: hanya tanggal yang punya catatan evaluasi
                            // yang masih bisa dibuka untuk diperbaiki.
                            $pendingUrl = $reportLocked && ! $item['hasNote']
                                ? route('checkpoint-user.history.show', ['id' => $item['recordId']])
                                : route('checkpoint-user.edit', [
                                    'checkpoint_user' => $item['recordId'],
                                    'tanggal' => $item['date']->format('Y-m-d'),
                                ]);
                        @endphp
                        <a href="{{ $pendingUrl }}"
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

        <section
        class="w-full rounded-2xl border border-slate-200 bg-white p-2.5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] sm:p-4 md:p-6">

        {{-- Header hari --}}
        <div
            class="grid grid-cols-7 gap-1 pb-2 text-center text-[9px] font-semibold uppercase tracking-[.08em] text-slate-400 sm:gap-1.5 sm:pb-3 sm:text-[10px] md:text-[11px]">

            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                <div class="min-w-0 truncate">
                    {{ $day }}
                </div>
            @endforeach

        </div>

        @php
            $daysBefore = $start->dayOfWeekIso - 1;
            $daysInMonth = $start->daysInMonth;
            $daysAfter = (7 - (($daysBefore + $daysInMonth) % 7)) % 7;
            $nextStart = $start->copy()->addMonth()->startOfMonth();
        @endphp

        {{-- Calendar --}}
        <div class="grid grid-cols-7 gap-1 sm:gap-1.5 p-2">

            {{-- Previous month --}}
            @for ($i = $daysBefore; $i > 0; $i--)

                @php
                    $date = $start->copy()->subDays($i);
                @endphp

                <span
                    class="flex min-h-[48px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-lg border border-slate-100 bg-slate-50/60 p-1.5 text-slate-300 sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5"
                    aria-disabled="true">

                    <span class="overflow-hidden text-[11px] leading-none sm:text-xs md:text-sm">
                        {{ $date->day }}
                    </span>

                </span>

            @endfor


            {{-- Current month --}}
            @foreach ($calendar as $index => $item)
                @php
                    $date = $item['date'];

                    if ($date->isWeekend()) {
                        $state = 'border-transparent bg-rose-500 text-white';
                    } elseif ($item['rejected']) {
                        $state = 'border-transparent bg-amber-500 text-white';
                    } elseif ($item['accepted']) {
                        $state = 'border-transparent bg-sky-500 text-white';
                    } elseif ($item['hasData']) {
                        $state = 'border-transparent bg-emerald-500 text-white';
                    } else {
                        $state = 'border-slate-200 bg-white text-slate-700';
                    }

                    // Pekan ditutup: hari berisi bukti hanya bisa dibaca,
                    // hari kosong tidak bisa diklik sama sekali.
                    $hasRecord = $item['hasData'] && $item['recordId'];
                    if ($hasRecord) {
                        // Hari dengan catatan evaluasi tetap bisa diedit walau pekan
                        // sudah ditutup; hari lain hanya bisa dibaca.
                        $cellHref = $reportLocked && ! $item['hasNote']
                            ? route('checkpoint-user.history.show', ['id' => $item['recordId']])
                            : route('checkpoint-user.edit', [
                                'checkpoint_user' => $item['recordId'],
                                'tanggal' => $date->format('Y-m-d'),
                            ]);
                    } else {
                        $cellHref = $reportLocked
                            ? null
                            : route('checkpoint-user.create', ['tanggal' => $date->format('Y-m-d')]);
                    }

                    $cellClass =
                        'reveal-cell group relative flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden rounded-lg border p-1.5 font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] ' .
                        'sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5 ' .
                        $state .
                        ($date->isToday() ? ' ring-2 ring-sky-400 ring-offset-1 sm:ring-offset-2' : '');
                @endphp

                @if ($cellHref)
                    <a href="{{ $cellHref }}" style="animation-delay: {{ min($index * 12, 320) }}ms"
                        class="{{ $cellClass }} hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)] active:scale-[.98]"
                        title="{{ $date->translatedFormat('l, d F Y') }}">

                        @include('check.partials.calendar-day')

                    </a>
                @else
                    <span aria-disabled="true" style="animation-delay: {{ min($index * 12, 320) }}ms"
                        class="{{ $cellClass }} cursor-not-allowed opacity-70"
                        title="Laporan pekan ini ditutup. Dibuka {{ $nextOpenAt->translatedFormat('l, d F Y') }} pukul 00:00.">

                        @include('check.partials.calendar-day')

                    </span>
                @endif

            @endforeach


            {{-- Next month --}}
            @for ($i = 1; $i <= $daysAfter; $i++)

                @php
                    $date = $nextStart->copy()->addDays($i - 1);
                @endphp

                <span
                    class="flex min-h-[48px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-lg border border-slate-100 bg-slate-50/60 p-1.5 text-slate-300 sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5"
                    aria-disabled="true">

                    <span class="overflow-hidden text-[11px] leading-none sm:text-xs md:text-sm">
                        {{ $date->day }}
                    </span>

                </span>

            @endfor

        </div>
    </section>

        <div
            class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-xs text-slate-500">
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-sky-500"></span>Ada yang disetujui</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-emerald-500"></span>Ada bukti
                pekerjaan</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-amber-500"></span>Ada yang
                ditolak</span>
            <span class="flex items-center gap-2"><span
                    class="h-3 w-3 rounded-[4px] border border-slate-200 bg-white"></span>Belum diisi</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-rose-500"></span>Sabtu &amp;
                Minggu</span>
            <span class="mx-1 hidden h-4 w-px bg-slate-200 sm:block"></span>
            <span class="flex items-center gap-2"><i class="ri-checkbox-circle-fill text-[13px] text-sky-600"></i>Disetujui</span>
            <span class="flex items-center gap-2"><i class="ri-close-circle-fill text-[13px] text-rose-600"></i>Ditolak</span>
            <span class="flex items-center gap-2"><i class="ri-time-line text-[13px] text-amber-600"></i>Menunggu
                verifikasi</span>
        </div>
    </div>

    <script>
        (function() {
            const cells = document.querySelectorAll('.reveal-cell');
            const show = (el) => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            };

            if (!('IntersectionObserver' in window)) {
                cells.forEach(show);
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    show(entry.target);
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.05,
                rootMargin: '0px 0px -40px 0px'
            });

            cells.forEach((cell) => {
                const delay = (cell.style.animationDelay || '0ms');
                cell.style.opacity = '0';
                cell.style.transform = 'translateY(18px)';
                cell.style.transition =
                    `opacity .6s cubic-bezier(.22,1,.36,1) ${delay}, transform .6s cubic-bezier(.22,1,.36,1) ${delay}`;
                observer.observe(cell);
            });
        })();
    </script>
</x-app-layout>
