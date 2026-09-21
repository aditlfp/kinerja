<x-app-layout>
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
                        class="ri-arrow-right-s-line text-xl"></i></a>
            </div>
        </div>

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
                    } elseif ($item['hasData']) {
                        $state = 'border-transparent bg-emerald-500 text-white';
                    } else {
                        $state = 'border-slate-200 bg-white text-slate-700 hover:border-sky-300';
                    }
                @endphp

                <a
                    href="{{ $item['hasData'] && $item['recordId']
                        ? route('checkpoint-user.edit', [
                            'checkpoint_user' => $item['recordId'],
                            'tanggal' => $date->format('Y-m-d'),
                        ])
                        : route('checkpoint-user.create', [
                            'tanggal' => $date->format('Y-m-d'),
                        ]) }}"

                    style="animation-delay: {{ min($index * 12, 320) }}ms"

                    class="reveal-cell group relative flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden rounded-lg border p-1.5 font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)]
                    hover:-translate-y-0.5
                    hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)]
                    active:scale-[.98]
                    sm:min-h-[64px] sm:rounded-xl sm:p-2
                    md:min-h-[74px] md:p-2.5
                    {{ $state }}
                    {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-1 sm:ring-offset-2' : '' }}"

                    title="{{ $date->translatedFormat('l, d F Y') }}">

                    {{-- Date --}}
                    <span class="overflow-hidden text-[11px] leading-none sm:text-xs md:text-sm flex justify-between items-center">
                        {{ $date->day }}
                        <i class="ri-pushpin-line {{ $date->isToday() ? '' : 'hidden' }}"></i>
                    </span>

                    {{-- Status --}}
                    @if ($item['rejected'])

                        <i class="ri-close-line text-[11px] opacity-90 sm:text-xs md:text-[13px]"></i>

                    @elseif ($item['hasData'])

                        <i
                            class="ri-check-line text-[11px] opacity-90 transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] group-hover:translate-x-0.5 sm:text-xs md:text-[13px]">
                        </i>

                    @elseif ($date->isWeekend())

                        <span
                            class="hidden truncate text-[8px] font-semibold uppercase tracking-wide opacity-85 sm:block sm:text-[9px] md:text-[10px]">
                            Libur
                        </span>

                    @endif

                </a>

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
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-emerald-500"></span>Ada
                pekerjaan</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-amber-500"></span>Ditolak</span>
            <span class="flex items-center gap-2"><span
                    class="h-3 w-3 rounded-[4px] border border-slate-200 bg-white"></span>Belum diisi</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-rose-500"></span>Sabtu &amp;
                Minggu</span>
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
