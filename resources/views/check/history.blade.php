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
                        @php($state = $date->isWeekend() ? 'border-transparent bg-rose-500 text-white' : ($item['rejected'] ? 'border-transparent bg-amber-500 text-white' : ($item['hasData'] ? 'border-transparent bg-emerald-500 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-sky-300')))
                        <a href="{{ $item['hasData'] && $item['recordId'] ? route('checkpoint-user.history.show', ['id' => $item['recordId']]) : route('checkpoint-user.history', ['month' => $start->format('Y-m')]) }}"
                           style="animation-delay: {{ min($index * 12, 320) }}ms"
                           class="reveal-cell group relative flex min-h-[74px] min-w-0 flex-col justify-between overflow-hidden rounded-xl border p-2.5 font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:-translate-y-0.5 hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)] active:scale-[.98] {{ $state }} {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-2' : '' }}"
                           title="{{ $date->translatedFormat('l, d F Y') }}">
                            <span class="text-sm leading-none overflow-hidden">{{ $date->day }}</span>
                            @if ($item['rejected'])
                                <i class="ri-close-line text-[13px] opacity-90"></i>
                            @elseif ($item['hasData'])
                                <i class="ri-check-line text-[13px] opacity-90 transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] group-hover:translate-x-0.5"></i>
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
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-emerald-500"></span>Ada riwayat</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] border border-slate-200 bg-white"></span>Belum diisi</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-amber-500"></span>Ditolak</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-rose-500"></span>Sabtu &amp; Minggu</span>
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
