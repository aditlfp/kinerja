<x-app-layout>
    @section('title', 'Kalender Checkpoint')
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
        <section
    class="w-full rounded-2xl border border-gray-100 bg-white p-2.5 shadow-sm sm:p-4 md:p-5">

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
        class="grid grid-cols-7 gap-1 pb-2 text-center text-[8px] font-semibold uppercase tracking-[.06em] text-gray-400 sm:gap-1.5 sm:pb-3 sm:text-[10px] sm:tracking-[.1em] md:text-[11px]">

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

                if ($item['hasData']) {
                    $state = 'border-transparent bg-emerald-500 text-white hover:bg-emerald-600';
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

                    title="{{ $date->translatedFormat('l, d F Y') }}"

                    class="reveal-cell group relative flex min-h-[48px] min-w-0 flex-col justify-between overflow-visible rounded-lg border p-1.5 font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)] sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5
                    {{ $state }}
                    {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-1 sm:ring-offset-2' : '' }}">

                    <span class="text-[10px] leading-none sm:text-xs md:text-sm overflow-hidden">
                        {{ $date->day }}
                    </span>

                    <i class="text-[10px] ri-check-line opacity-90 sm:text-xs md:text-[13px]"></i>

                </a>


            @else

                <span
                    title="{{ $date->translatedFormat('l, d F Y') }}"

                    class="flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden rounded-lg border p-1.5 font-medium sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5
                    {{ $state }}
                    {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-1 sm:ring-offset-2' : '' }}">

                    <span class="overflow-hidden text-[10px] leading-none sm:text-xs md:text-sm">
                        {{ $date->day }}
                    </span>


                    @if ($date->isWeekend())

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
    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] text-gray-500 sm:mt-5 sm:gap-x-5 sm:text-xs">

            <span class="flex items-center gap-1.5 sm:gap-2">
                <span class="h-2.5 w-2.5 shrink-0 rounded-[3px] bg-emerald-500 sm:h-3 sm:w-3"></span>
                Ada checkpoint
            </span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <span class="h-2.5 w-2.5 shrink-0 rounded-[3px] border border-gray-200 bg-white sm:h-3 sm:w-3"></span>
                Belum diisi
            </span>

            <span class="flex items-center gap-1.5 sm:gap-2">
                <span class="h-2.5 w-2.5 shrink-0 rounded-[3px] bg-rose-50 ring-1 ring-rose-100 sm:h-3 sm:w-3"></span>
                Sabtu &amp; Minggu
            </span>

        </div>

    </section>
    </div>
</x-app-layout>
