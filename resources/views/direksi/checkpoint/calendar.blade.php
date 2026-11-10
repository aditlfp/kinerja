<x-app-layout>
    @section('title', 'Kalender Checkpoint')

    <x-page-loading target="checkpoint-page" title="Memuat kalender..."
        description="Menyiapkan data kinerja dan checkpoint." />
    <div id="checkpoint-page" class="opacity-0 transition-opacity duration-300 motion-reduce:transition-none" aria-busy="true">
        <div class="w-full max-w-5xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="mt-10 p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-sky-50 text-xl text-sky-600"><i
                            class="ri-user-line"></i></span>
                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold capitalize text-gray-900 sm:text-xl">
                            {{ strtolower($employee->nama_lengkap) }}</h1>
                        <p class="mt-0.5 truncate text-xs text-gray-500">{{ $employee->jabatan->nama_jabatan ?? '-' }}
                            &middot; {{ $employee->divisi->name ?? '-' }} &middot;
                            {{ $employee->kerjasama->client->name ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('direksi.cp.index') }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50"><i
                        class="mr-1.5 ri-arrow-left-line"></i> Kembali</a>
            </div>
        </section>

        @php
            // The three numbers the recap always reports, in display order. The
            // labels live here instead of the service so the service stays a
            // plain data source.
            $recapCards = [
                'attendance' => [
                    'label' => 'Absensi Kehadiran',
                    'unit' => 'hari kerja',
                    'icon' => 'ri-fingerprint-line',
                    'chip' => 'bg-sky-50 text-sky-600',
                    'bar' => 'bg-sky-500',
                ],
                'checkpoint' => [
                    'label' => 'Kinerja Harian',
                    'unit' => 'hari kerja',
                    'icon' => 'ri-task-line',
                    'chip' => 'bg-emerald-50 text-emerald-600',
                    'bar' => 'bg-emerald-500',
                ],
                'work_order' => [
                    'label' => 'Perintah Kerja',
                    'unit' => 'perintah kerja',
                    'icon' => 'ri-file-list-3-line',
                    'chip' => 'bg-purple-50 text-purple-600',
                    'bar' => 'bg-purple-500',
                ],
            ];
        @endphp

        <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm motion-safe:animate-[fadeSlide_.5s_cubic-bezier(.22,1,.36,1)_both] sm:p-5" data-performance-recap
            @if (! $recap['visible']) data-recap-locked @endif>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-600 text-lg text-white">
                        <i class="ri-bar-chart-grouped-line"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">Rekap Persentase</h2>
                            @if ($recap['visible'])
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Terbuka
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                                    <i class="ri-lock-2-line"></i> Terkunci
                                </span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">Ringkasan kinerja {{ $start->translatedFormat('F Y') }}.</p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-slate-500 sm:pt-2 sm:text-right">
                    {{ $start->translatedFormat('F Y') }}
                </span>
            </div>

            @if ($recap['visible'])
                <div class="relative mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recapCards as $key => $card)
                        @php
                            $metric = $recap['metrics'][$key];
                            $percent = $metric['percent'];
                        @endphp

                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 opacity-0 transition duration-300 hover:border-slate-300 hover:bg-white motion-safe:animate-[fadeSlide_.45s_cubic-bezier(.22,1,.36,1)_both]" style="animation-delay: {{ $loop->index * 80 }}ms"
                            data-recap-metric="{{ $key }}"
                            @if (! is_null($percent)) data-recap-percent="{{ $percent }}" @endif>
                            <div class="flex min-w-0 items-center gap-2.5">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $card['chip'] }} text-base">
                                    <i class="{{ $card['icon'] }}" aria-hidden="true"></i>
                                </span>
                                <p class="min-w-0 truncate text-xs font-semibold leading-tight text-slate-600">{{ $card['label'] }}</p>
                            </div>

                            @if ($key === 'work_order')
                                <p class="mt-5 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                                    {{ $metric['achieved'] }} dari {{ $metric['total'] }} {{ $card['unit'] }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-500">
                                    {{ is_null($percent) ? 'Belum ada perintah kerja' : 'Perintah kerja selesai' }}
                                </p>
                            @else
                                <div class="mt-5 flex items-end justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-3xl font-bold tabular-nums tracking-tight text-slate-900">
                                            {{ is_null($percent) ? '—' : $percent . '%' }}
                                        </p>
                                        <p class="mt-1 truncate text-[11px] text-slate-500">
                                            @if (is_null($percent))
                                                Belum ada data
                                            @else
                                                {{ $metric['achieved'] }} dari {{ $metric['total'] }} {{ $card['unit'] }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="shrink-0 pb-1 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                        {{ is_null($percent) ? 'Kosong' : 'Capaian' }}
                                    </span>
                                </div>

                                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200" role="progressbar"
                                    aria-label="{{ $card['label'] }}" aria-valuemin="0" aria-valuemax="100"
                                    aria-valuenow="{{ $percent ?? 0 }}">
                                    <div class="h-full rounded-full {{ $card['bar'] }} transition-[width] duration-500 ease-out"
                                        style="width: {{ $percent ?? 0 }}%"></div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="relative mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-700">Pekerjaan rutin</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums text-slate-900">{{ $pekerjaanCpDoneCounts->get('rutin', 0) }} <span class="text-sm font-medium text-slate-400">/ {{ $pekerjaanCpCounts->get('rutin', 0) }}</span></p>
                        <p class="text-[11px] text-slate-500">Sudah dikerjakan · Harian, mingguan, bulanan</p>
                    </div>
                    <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-orange-700">Pekerjaan isidental</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums text-slate-900">{{ $pekerjaanCpDoneCounts->get('isidental', 0) }} <span class="text-sm font-medium text-slate-400">/ {{ $pekerjaanCpCounts->get('isidental', 0) }}</span></p>
                        <p class="text-[11px] text-slate-500">Sudah dikerjakan · Isidental</p>
                    </div>
                </div>

                @php
                    $excludedHolidays = collect($recap['excludedHolidays'] ?? [])
                        ->map(
                            fn ($name, $date) => \Carbon\Carbon::parse($date)->translatedFormat('d M Y') .
                                (filled($name) ? ' (' . $name . ')' : ''),
                        )
                        ->implode(', ');
                    $holidayTooltip =
                        $excludedHolidays === ''
                            ? 'Tidak ada hari libur nasional pada periode ini.'
                            : 'Hari libur nasional yang dikecualikan: ' . $excludedHolidays;
                @endphp

                <p class="relative mt-4 flex items-center gap-1.5 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-[10px] leading-relaxed text-slate-500"
                    title="{{ $holidayTooltip }}">
                    <i class="ri-information-line text-[13px] text-slate-400"></i>
                    <span class="cursor-help underline decoration-slate-300 decoration-dotted underline-offset-2">
                        Hari libur nasional dan akhir pekan tidak dihitung sebagai hari kerja.
                    </span>
                </p>
            @else
                <div class="relative mt-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700">
                        <i class="ri-lock-2-line"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold">Rekap sedang dikunci</p>
                        <p class="mt-1 text-xs leading-relaxed text-amber-800">
                            Rekap dapat dilihat pada hari {{ $recap['visibleDaysLabel'] }} dan 3 hari terakhir bulan.
                            Dibuka lagi <span class="font-semibold">{{ $recap['nextVisibleAt']->translatedFormat('l, d F Y') }}</span>.
                        </p>
                    </div>
                </div>
            @endif
        </section>

        @php
            // Days that still hold evidence direksi has not reviewed. The
            // calendar rows already carry their per-day status counts, so this
            // costs no extra query — it just makes those days findable without
            // scanning the whole grid for the amber marker.
            $pendingDays = $calendar
                ->filter(fn ($item) => $item['hasData'] && $item['recordId'] && $item['counts']['process'] > 0)
                ->values();
            $pendingTotal = (int) $pendingDays->sum(fn ($item) => $item['counts']['process']);
        @endphp

        <section class="relative overflow-hidden rounded-2xl border border-amber-100 bg-white p-4 shadow-sm motion-safe:animate-[fadeSlide_.5s_cubic-bezier(.22,1,.36,1)_both] sm:p-5" data-pending-days>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-500 text-lg text-white">
                        <i class="ri-time-line"></i>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">Tanggal Belum Di Approve</h2>
                            @if ($pendingDays->isNotEmpty())
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">
                                    {{ $pendingDays->count() }} tanggal
                                </span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">Bukti pekerjaan yang perlu ditinjau.</p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-slate-500 sm:pt-2 sm:text-right">
                    {{ $start->translatedFormat('F Y') }}
                </span>
            </div>

            @if ($pendingDays->isEmpty())
                <div class="relative mt-5 flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-emerald-800">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
                        <i class="ri-checkbox-circle-line text-lg"></i>
                    </span>
                    <p class="text-xs leading-relaxed">
                        {{ $totalRecords > 0
                            ? 'Semua bukti pekerjaan bulan ini sudah di-approve Direksi.'
                            : 'Belum ada bukti pekerjaan yang diisi bulan ini.' }}
                    </p>
                </div>
            @else
                <p class="relative mt-5 text-xs leading-relaxed text-amber-900">
                    <span class="font-bold">{{ $pendingTotal }} bukti</span> menunggu approval pada
                    <span class="font-bold">{{ $pendingDays->count() }} tanggal</span>.
                </p>

                <div class="relative mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($pendingDays as $item)
                        <a href="{{ route('direksi.cp.history.show', $item['recordId']) }}"
                            data-pending-date="{{ $item['date']->toDateString() }}"
                            data-pending-date-count="{{ $item['counts']['process'] }}"
                            title="Tinjau bukti {{ $item['date']->translatedFormat('l, d F Y') }}"
                            class="group flex min-w-0 items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white p-3 opacity-0 transition duration-300 hover:border-amber-300 hover:bg-amber-50/50 active:scale-[.99] motion-safe:animate-[fadeSlide_.45s_cubic-bezier(.22,1,.36,1)_both]" style="animation-delay: {{ $loop->index * 60 }}ms">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-500 text-sm font-bold tabular-nums text-white">
                                    {{ $item['date']->day }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-xs font-bold text-slate-800">{{ $item['date']->translatedFormat('l') }}</span>
                                    <span class="mt-0.5 block text-[11px] text-slate-500">{{ $item['date']->translatedFormat('d F Y') }}</span>
                                </span>
                            </span>
                            <span class="flex shrink-0 items-center gap-1.5">
                                <span class="rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-bold tabular-nums text-amber-700">
                                    {{ $item['counts']['process'] }} bukti
                                </span>
                                <i class="ri-arrow-right-s-line text-base text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-amber-500"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-purple-100 bg-white p-4 shadow-sm motion-safe:animate-[fadeSlide_.5s_cubic-bezier(.22,1,.36,1)_both] sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-purple-600 text-lg text-white">
                        <i class="ri-medal-2-line"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-gray-900">Nilai Akhir</h2>
                            <span class="rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-purple-700">
                                Input Direksi
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500">Tetapkan nilai akhir kinerja karyawan.</p>
                    </div>
                </div>
                <span class="hidden text-[11px] font-medium text-gray-400 sm:inline-flex">
                    Rentang 0–100%
                </span>
            </div>

            <form method="POST" action="{{ route('direksi.cp.final-score.store', $employee) }}" class="relative mt-4 flex flex-col gap-3 md:flex-row md:items-end">
                @csrf
                <input type="hidden" name="month" value="{{ $start->month }}">
                <input type="hidden" name="year" value="{{ $start->year }}">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="w-full md:flex-1">
                    <label for="nilai_akhir" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Nilai persentase
                    </label>
                    <div class="relative">
                        <input type="number" id="nilai_akhir" name="score" min="0" max="100" step="0.01"
                            value="{{ old('score', $finalScore?->score) }}" placeholder="Masukkan nilai"
                            class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 pr-12 text-lg font-bold tabular-nums text-gray-900 outline-none transition placeholder:text-sm placeholder:font-normal placeholder:text-gray-400 focus:border-purple-400 focus:bg-white focus:ring-4 focus:ring-purple-100"
                            aria-describedby="nilai-akhir-help" required>
                        <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-lg font-bold text-purple-600">%</span>
                    </div>
                    <p id="nilai-akhir-help" class="mt-1.5 text-[11px] text-gray-500">
                        Nilai bulan {{ $start->translatedFormat('F Y') }}, rentang 0–100 persen.
                    </p>
                    @error('score')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-purple-600 px-5 text-sm font-semibold text-white shadow-sm shadow-purple-200 transition duration-200 hover:bg-purple-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-purple-100 active:scale-[.98] md:w-auto md:min-w-48 md:mb-[22px]">
                    <i class="ri-save-3-line text-base"></i>
                    {{ $finalScore ? 'Perbarui Nilai Akhir' : 'Simpan Nilai Akhir' }}
                </button>
            </form>
            @if ($finalScore)
                <p class="relative mt-3 text-[11px] text-gray-500">
                    Terakhir disimpan oleh {{ $finalScore->creator?->nama_lengkap ?? '-' }} pada
                    {{ $finalScore->updated_at?->translatedFormat('d M Y H:i') }}.
                </p>
            @endif
            @if (session('success'))
                <p class="relative mt-2 text-xs font-medium text-emerald-600">{{ session('success') }}</p>
            @endif
            @error('month')
                <p class="relative mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            @error('year')
                <p class="relative mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </section>

        <section
    class="w-full rounded-2xl border border-gray-100 bg-white p-2.5 shadow-xs sm:p-4 md:p-5">

    {{-- Header --}}
    <div class="mb-3 flex items-center justify-between gap-2 sm:mb-4">

        {{-- Previous --}}
        <a href="{{ route('direksi.cp.calendar', [
            'user' => $employee->id,
            'month' => $previousMonth,
            'type' => $type
        ]) }}"
            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-purple-300 hover:text-purple-600 sm:h-10 sm:w-10"
            aria-label="Bulan sebelumnya">

            <i class="text-lg ri-arrow-left-s-line sm:text-xl"></i>
        </a>


        {{-- Month --}}
        <div class="min-w-0 flex-1 text-center">

            <h2 class="truncate text-base font-bold text-gray-900 sm:text-lg">
                {{ $start->translatedFormat('F Y') }}
            </h2>

            <p class="truncate text-[10px] text-gray-500 sm:text-xs">
                {{ $totalRecords }} Bukti Pekerjaan
            </p>

        </div>


        {{-- Next --}}
        <a href="{{ route('direksi.cp.calendar', [
            'user' => $employee->id,
            'month' => $nextMonth,
            'type' => $type
        ]) }}"
            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-purple-300 hover:text-purple-600 sm:h-10 sm:w-10"
            aria-label="Bulan berikutnya">

            <i class="text-lg ri-arrow-right-s-line sm:text-xl"></i>
        </a>

    </div>


    @php
        $daysBefore = $start->dayOfWeekIso - 1;
        $daysInMonth = $start->daysInMonth;
        $daysAfter = (7 - (($daysBefore + $daysInMonth) % 7)) % 7;
        $nextStart = $start->copy()->addMonth()->startOfMonth();
    @endphp


    {{-- Day Header --}}
    <div
        class="grid grid-cols-7 gap-1 pb-2 text-center text-[8px] font-semibold uppercase tracking-[.06em] text-gray-400 sm:gap-1.5 sm:pb-3 sm:text-[10px] sm:tracking-widest md:text-[11px]">

        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)

            <div class="min-w-0 truncate">
                {{ $day }}
            </div>

        @endforeach

    </div>


    {{-- Calendar --}}
    <div class="grid grid-cols-7 gap-1 sm:gap-1.5 p-2">


        {{-- Previous Month --}}
        @for ($i = $daysBefore; $i > 0; $i--)

            @php
                $date = $start->copy()->subDays($i);
            @endphp

            <span
                class="flex min-h-[48px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-lg border border-gray-100 bg-gray-50/60 p-1.5 text-gray-300 sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5"
                aria-disabled="true">

                <span class="overflow-hidden text-[10px] leading-none sm:text-xs md:text-sm">
                    {{ $date->day }}
                </span>

            </span>

        @endfor


        {{-- Current Month --}}
        @foreach ($calendar as $index => $item)

            @php
                $date = $item['date'];
                $isHoliday = $holidays->has($date->toDateString());
                $holidayName = $holidays->get($date->toDateString());
                $cellTitle = $date->translatedFormat('l, d F Y');
                if ($isHoliday && filled($holidayName)) {
                    $cellTitle .= ' — ' . $holidayName;
                }
                // "Approved" here means the day has work and nothing left pending.
                $approved = $item['hasData'] && $item['counts']['process'] === 0;
                if ($approved) {
                    $state = 'border-transparent bg-sky-500 text-white hover:bg-sky-600';
                } elseif ($item['hasData']) {
                    $state = 'border-transparent bg-emerald-500 text-white hover:bg-emerald-600';
                } elseif ($isHoliday) {
                    $state = 'border-rose-200 bg-rose-100 text-rose-600';
                } elseif ($date->isWeekend()) {
                    $state = 'border-rose-100 bg-rose-50 text-rose-400';
                } else {
                    $state = 'border-gray-200 bg-white text-gray-700 hover:border-purple-300';
                }
            @endphp


            @if ($item['hasData'] && $item['recordId'])

                <a
                    href="{{ route('direksi.cp.history.show', $item['recordId']) }}"

                    style="animation-delay: {{ min($index * 12, 320) }}ms"

                    title="{{ $cellTitle }}"

                    @if ($isHoliday) data-calendar-holiday="{{ $date->toDateString() }}" @endif

                    class="reveal-cell group relative flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden rounded-lg border p-1.5 font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)] sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5
                    {{ $state }}
                    {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-1 sm:ring-offset-2' : '' }}">

                    <span class="flex items-center justify-between gap-1 overflow-hidden">
                        <span class="truncate text-[10px] leading-none sm:text-xs md:text-sm">
                            {{ $date->day }}
                        </span>

                        @if ($isHoliday)
                            <span
                                class="grid h-3 w-3 shrink-0 place-items-center rounded-full bg-white/95 shadow-sm sm:h-3.5 sm:w-3.5"
                                title="{{ filled($holidayName) ? 'Libur nasional: ' . $holidayName : 'Libur nasional' }}">
                                <i class="ri-flag-2-fill text-[7px] text-rose-500 sm:text-[8px]"></i>
                            </span>
                        @endif
                    </span>

                    <x-checkpoint-status-row :counts="$item['counts']" :has-data="$item['hasData']" size="md" />
                </a>


            @else

                <span
                    title="{{ $cellTitle }}"

                    @if ($isHoliday) data-calendar-holiday="{{ $date->toDateString() }}" @endif

                    class="flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden rounded-lg border p-1.5 font-medium sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5
                    {{ $state }}
                    {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-1 sm:ring-offset-2' : '' }}">

                    <span class="flex items-center justify-between gap-1 overflow-hidden">
                        <span class="truncate text-[10px] leading-none sm:text-xs md:text-sm">
                            {{ $date->day }}
                        </span>

                        @if ($isHoliday)
                            <span
                                class="grid h-3 w-3 shrink-0 place-items-center rounded-full bg-white/95 shadow-sm sm:h-3.5 sm:w-3.5"
                                title="{{ filled($holidayName) ? 'Libur nasional: ' . $holidayName : 'Libur nasional' }}">
                                <i class="ri-flag-2-fill text-[7px] text-rose-500 sm:text-[8px]"></i>
                            </span>
                        @endif
                    </span>

                    @if ($isHoliday)

                        <span
                            class="truncate text-[7px] font-semibold uppercase leading-tight text-rose-500 sm:text-[9px] md:text-[10px]">
                            Libur Nasional
                        </span>

                    @elseif ($date->isWeekend())

                        <span
                            class="hidden truncate text-[8px] font-semibold uppercase opacity-85 sm:block sm:text-[9px] md:text-[10px]">
                            Libur
                        </span>

                    @endif

                </span>

            @endif

        @endforeach


        {{-- Next Month --}}
        @for ($i = 1; $i <= $daysAfter; $i++)

            @php
                $date = $nextStart->copy()->addDays($i - 1);
            @endphp

            <span
                class="flex min-h-[48px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-lg border border-gray-100 bg-gray-50/60 p-1.5 text-gray-300 sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5"
                aria-disabled="true">

                <span class="overflow-hidden text-[10px] leading-none sm:text-xs md:text-sm">
                    {{ $date->day }}
                </span>

            </span>

        @endfor

    </div>


    {{-- Legend --}}
    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] text-gray-500 sm:mt-5 sm:gap-x-5 sm:text-xs w-full">

            <span class="flex items-center gap-1.5 sm:gap-2">
                <span class="m-1 h-2.5 w-2.5 shrink-0 rounded-[3px] bg-sky-500 sm:h-3 sm:w-3"></span>
                Data Sudah Di Verifikasi Semua
            </span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <span class="m-1 h-2.5 w-2.5 shrink-0 rounded-[3px] bg-emerald-500 sm:h-3 sm:w-3"></span>
                Ada Checkpoint / Ada Yang Belum Di Verifikasi
            </span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <span class="m-1 h-2.5 w-2.5 shrink-0 rounded-[3px] bg-rose-50 ring-1 ring-rose-100 sm:h-3 sm:w-3"></span>
                Sabtu &amp; Minggu
            </span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <i class="ri-flag-2-fill text-[11px] text-rose-500 sm:text-[12px]"></i>
                Libur nasional
            </span>

            <span class="mx-1 hidden h-4 w-px bg-gray-200 sm:block"></span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <i class="ri-checkbox-circle-fill text-[13px] text-sky-600"></i>
                Disetujui
            </span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <i class="ri-close-circle-fill text-[13px] text-rose-600"></i>
                Ditolak
            </span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <i class="ri-time-line text-[13px] text-amber-600"></i>
                Menunggu verifikasi
            </span>

        </div>

        </section>
        </div>
    </div>

</x-app-layout>
