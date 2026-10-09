<x-app-layout>

    <div class="mx-auto w-full max-w-5xl space-y-4 px-2 sm:px-3 lg:px-4">

        {{-- Header --}}
        <section class="mt-10 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-sky-50 text-xl text-sky-600">
                        <i class="ri-user-line"></i>
                    </span>

                    <div>
                        <h1 class="text-lg font-bold capitalize text-gray-900">
                            {{ strtolower($user->nama_lengkap) }}
                        </h1>

                        <p class="text-xs text-gray-500">
                            {{ $user->jabatan->name_jabatan }}
                        </p>
                    </div>

                </div>

                <a href="{{ route(($isMcs ? 'mcs' : 'direksi') . '.work-order.index') }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    <i class="ri-arrow-left-line mr-1.5"></i>
                    Kembali
                </a>

            </div>

        </section>


        {{-- Success --}}
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif


        {{-- Calendar --}}
        <section class="w-full rounded-2xl border border-gray-100 bg-white p-3 shadow-sm sm:p-5">

            {{-- Calendar Header --}}
            <div class="mb-4 flex items-center justify-between">

                {{-- Previous Month --}}
                <a href="{{ route(($isMcs ? 'mcs' : 'direksi') . '.work-order.calendar', [
                    'user' => $user->id,
                    'month' => $previousMonth,
                ]) }}"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-sky-300 hover:text-sky-600 sm:h-10 sm:w-10">
                    <i class="ri-arrow-left-s-line text-xl"></i>
                </a>


                {{-- Month --}}
                <h2 class="text-base font-semibold text-gray-900 sm:text-lg">
                    {{ $start->translatedFormat('F Y') }}
                </h2>


                {{-- Next Month --}}
                <a href="{{ route(($isMcs ? 'mcs' : 'direksi') . '.work-order.calendar', [
                    'user' => $user->id,
                    'month' => $nextMonth,
                ]) }}"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-gray-200 text-gray-500 transition hover:border-sky-300 hover:text-sky-600 sm:h-10 sm:w-10">
                    <i class="ri-arrow-right-s-line text-xl"></i>
                </a>

            </div>


            @php

                /*
                |--------------------------------------------------------------------------
                | Calendar Range
                |--------------------------------------------------------------------------
                | Minggu sebagai hari pertama
                */

                $calendarStart = $start->copy()->startOfMonth()->startOfWeek(Carbon\Carbon::SUNDAY);

                $calendarEnd = $start->copy()->endOfMonth()->endOfWeek(Carbon\Carbon::SATURDAY);

                $calendarDate = $calendarStart->copy();

                $calendarDays = [];

                while ($calendarDate->lte($calendarEnd)) {
                    $calendarDays[] = $calendarDate->copy();
                    $calendarDate->addDay();
                }

            @endphp



            {{-- Day Header --}}
            <div
                class="grid grid-cols-7 gap-1 pb-2 text-center text-[8px] font-semibold uppercase tracking-[.06em] text-gray-400 sm:gap-1.5 sm:pb-3 sm:text-[10px] sm:tracking-[.1em] md:text-[11px]">

                @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                    <div class="min-w-0 truncate">
                        {{ $day }}
                    </div>
                @endforeach

            </div>


            {{-- Calendar Body --}}
            <div class="grid grid-cols-7 gap-1 sm:gap-1.5">

                @foreach ($calendarDays as $date)

                    @php

                        $isCurrentMonth = $date->month === $start->month && $date->year === $start->year;

                        $isWeekend = $date->isSaturday() || $date->isSunday();

                        $isToday = $date->isToday();

                        $order = $isCurrentMonth ? $orders->get($date->format('Y-m-d')) : null;

                        $isPast = $date->isBefore(today());
                        $isComplete = (bool) $order?->has_complete;

                    @endphp


                    {{-- Outside Current Month --}}
                    @if (!$isCurrentMonth)
                        <span
                            class="flex min-h-[48px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-lg border {{ $isWeekend ? 'border-rose-100 bg-rose-50/60 text-rose-300' : 'border-gray-100 bg-gray-50/60 text-gray-300' }} sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5"
                            aria-disabled="true">
                            <span
                                class="flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden p-1.5 font-medium sm:min-h-[64px] sm:p-2 md:min-h-[74px] md:p-2.5">
                                <span class="overflow-hidden text-[10px] leading-none sm:text-xs md:text-sm">
                                    {{ $date->day }}
                                </span>
                            </span>

                        </span>


                        {{-- Past Date --}}
                    @elseif ($isPast)
                            @if ($order)
                            <a href="{{ route(($isMcs ? 'mcs' : 'direksi') . '.work-order.create', ['user' => $user->id, 'tanggal' => $date->format('Y-m-d')]) }}"
                                class="relative flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden rounded-lg border border-emerald-200 bg-emerald-500 p-1.5 text-white transition hover:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5">
                                <span
                                    class="flex min-h-[48px] min-w-0 justify-between overflow-hidden p-1.5 font-medium sm:min-h-[64px] sm:p-2 md:min-h-[74px] md:p-2.5">

                                    <span class="text-[10px] leading-none sm:text-xs md:text-sm overflow-hidden">
                                        {{ $date->day }}
                                    </span>

                                    @if ($order)
                                        <span class="mt-10 text-white">
                                            <i class="{{ $isComplete ? 'ri-checkbox-circle-fill' : 'ri-file-list-3-line' }} text-xs" aria-hidden="true"></i>
                                        </span>
                                    @endif
                                </span>
                            @else
                                <div
                                    class="relative flex min-h-[48px] min-w-0 cursor-not-allowed flex-col justify-between overflow-hidden rounded-lg border {{ $isWeekend ? 'border-rose-100 bg-rose-50/60 text-rose-300' : 'border-gray-100 bg-gray-50/60 text-gray-300' }} sm:min-h-[64px] sm:rounded-xl sm:p-2 md:min-h-[74px] md:p-2.5">

                                    <span
                                        class="flex min-h-[48px] min-w-0 flex-col justify-between overflow-hidden p-1.5 font-medium sm:min-h-[64px] sm:p-2 md:min-h-[74px] md:p-2.5 {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-1 sm:ring-offset-2' : '' }}">

                                        <span class="text-[10px] leading-none sm:text-xs md:text-sm overflow-hidden">
                                            {{ $date->day }}
                                        </span>
                                    </span>


                                    @if ($order)
                                        <span class="absolute right-1 top-1 text-white">
                                            <i class="{{ $isComplete ? 'ri-checkbox-circle-fill' : 'ri-file-list-3-line' }} text-xs" aria-hidden="true"></i>
                                        </span>
                                    @endif
                            @endif

                        @if ($order)
                            </a>
                        @else
            </div>
            @endif

            {{-- Available Date --}}
        @else
            <a href="{{ route(($isMcs ? 'mcs' : 'direksi') . '.work-order.create', [
                'user' => $user->id,
                'tanggal' => $date->format('Y-m-d'),
            ]) }}"
                class="
                                group relative flex min-h-[48px] min-w-0
                                cursor-pointer flex-col justify-between
                                overflow-hidden rounded-lg border p-1.5
                                transition
                                sm:min-h-[64px] sm:rounded-xl sm:p-2
                                md:min-h-[74px] md:p-2.5

                                {{ $isComplete
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : ($isToday
                                        ? 'border-sky-200 bg-sky-50 text-sky-700 ring-1 ring-sky-200'
                                        : ($isWeekend
                                            ? 'border-rose-100 bg-rose-50/60 text-rose-400'
                                            : 'border-gray-100 bg-white text-gray-800')) }}


                                {{ !$isToday && !$order && !$isWeekend ? 'hover:border-sky-200 hover:bg-sky-50' : '' }}
                            ">

                {{-- Date --}}
                <span class="relative z-10 text-[10px] font-medium leading-none sm:text-xs md:text-sm overflow-hidden">
                    {{ $date->day }}
                </span>


                {{-- Status --}}
                @if ($isComplete)
                    {{-- Complete --}}
                    <span class="relative z-10 self-end overflow-hidden">
                        <i class="ri-checkbox-circle-fill text-xs sm:text-sm"></i>
                    </span>
                @elseif ($order)
                    {{-- Has Order --}}
                    <span class="relative z-10 self-end overflow-hidden">
                        <i class="ri-file-list-3-line text-xs sm:text-sm"></i>
                    </span>
                @elseif ($isToday)
                    {{-- Today --}}
                    <span class="relative z-10 self-end overflow-hidden">
                        <span class="block h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                    </span>
                @endif

            </a>


            {{-- Work Order Indicator --}}
            @if ($order)
                <span class="absolute bottom-0.5 left-1/2 z-10 -translate-x-1/2">
                    <i class="ri-check-line text-[10px] text-white"></i>
                </span>
            @endif
            @endif

            @endforeach

    </div>


    {{-- Legend --}}
    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-gray-100 pt-4 text-[10px] text-gray-500 sm:text-xs">

        {{-- Work Order --}}
        <span class="flex items-center gap-1.5 p-1">

            <i class="ri-file-list-3-line text-xs sm:text-sm rounded-full text-sky-500"></i>
            Ada Perintah Kerja

        </span>

        {{-- Complete Work Order --}}
        <span class="flex items-center gap-1.5 p-1">

            <i class="ri-checkbox-circle-fill text-xs sm:text-sm rounded-full text-emerald-700"></i>
            Sudah dikerjakan

        </span>

        {{-- Today --}}
        <span class="flex items-center gap-1.5 p-1">

            <i class="h-3 w-3 rounded-md bg-sky-600"></i>

            Hari ini

        </span>

        {{-- Weekend --}}
        <span class="flex items-center gap-1.5 p-1">

            <i class="h-3 w-3 rounded-md bg-rose-50 ring-1 ring-rose-100"></i>

            Sabtu/Minggu

        </span>

        {{-- Not Available --}}
        <span class="flex items-center gap-1.5 p-1">

            <i class="h-3 w-3 rounded-md bg-gray-100"></i>

            Tidak tersedia

        </span>

    </div>

    </section>

    </div>

</x-app-layout>
