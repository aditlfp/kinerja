                        @if ($absenP)
                            {{-- handle Pulang --}}
                            <div class="flex flex-col items-center justify-center sm:justify-end">
                                @php
                                    $luweh1Dino = Carbon\Carbon::createFromFormat(
                                        'Y-m-d, H:i:s',
                                        $absenP?->created_at->format('Y-m-d, H:i:s'),
                                    )->diffInHours(Carbon\Carbon::now());
                                @endphp
                                @if (Auth::user()->id == $absenP?->user_id && $absenP?->absensi_type_pulang == null)
                                    @php
                                        $now = now();
                                        $shiftEnd = \Carbon\Carbon::parse($absenP->shift?->jam_end);
                                        $timeDifference = $now->diffInMinutes($shiftEnd, false);
                                    @endphp

                                    <span class="hidden">
                                        <span id="userId" data-user-id="{{ $absenP->user_id }}"
                                            data-auth-user="{{ Auth::user()->id }}"></span>
                                        <span id="endTime" endTimer="{{ $absenP->shift?->jam_end }}"></span>
                                        <span id="startTime" startTimer="{{ $absenP->shift?->jam_start }}"></span>
                                    </span>

                                    {{-- <div>
                                        <button id="modalPulangBtn" data-absen="{{ $absenP }}"
                                            class="items-center justify-center hidden px-3 py-1 mt-5 mr-0 text-xl text-white uppercase transition duration-100 ease-out bg-yellow-600 rounded-md shadow-md hover:bg-yellow-700 hover:shadow-none all sm:mr-2">
                                            <i class="font-sans text-3xl ri-run-line"></i>
                                            <span class="font-bold">Pulang</span>
                                        </button>
                                    </div> --}}
                                    <div
                                        class="fixed inset-0 z-9000 hidden transition-all duration-300 ease-in-out modalp bg-slate-950/35 backdrop-blur-xs">
                                        <div class="w-[min(94vw,420px)] max-h-[88vh] overflow-y-auto mx-2 rounded-lg shadow-2xl bg-slate-50 ring-1 ring-white/80">
                                            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-3 py-2.5">
                                                <div>
                                                    <p class="text-[10px] font-bold uppercase tracking-wide text-yellow-700">Konfirmasi Pulang</p>
                                                    <p class="text-base font-black leading-tight text-slate-900">Pastikan titik lokasi benar</p>
                                                </div>
                                                <button type="button"
                                                    class="flex items-center justify-center w-8 h-8 text-lg font-bold text-red-600 transition rounded-full shadow-xs close shrink-0 bg-red-50 hover:bg-red-100">&times;</button>
                                            </div>
                                            <form id="checkoutForm" action="{{ route('data.update', $absenP->id) }}" method="POST"
                                                class="flex items-center justify-center">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex flex-col justify-center w-full p-3">
                                                    <div class="rounded-md border border-yellow-200 bg-yellow-50 px-3 py-1.5 text-center">
                                                        <p class="text-xs font-bold text-slate-900">Apakah Anda yakin ingin pulang sekarang?</p>
                                                        @if (Auth::user()->name != 'DIREKSI' && Auth::user()->jabatan_id != 35)
                                                            <span id="labelWaktu" class="block text-[11px] font-semibold text-slate-600"></span>
                                                            <span class="flex justify-center">
                                                                <span id="jam2"
                                                                    class="mt-0.5 text-[11px] font-semibold underline badge badge-info text-slate-800"></span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-3 space-y-1.5">
                                                        <div class="overflow-hidden rounded-md border border-slate-200 bg-white p-1.5 shadow-xs">
                                                            <div id="checkoutMap"></div>
                                                        </div>
                                                        <div class="grid grid-cols-4 justify-evenly items-center gap-1.5 text-[10px] font-semibold text-slate-700">
                                                            <span class="flex items-center justify-center gap-1">
                                                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                                                Masuk
                                                            </span>
                                                            <span class="flex items-center justify-center gap-1">
                                                                <span class="w-2.5 h-2.5 rounded-full bg-green-600"></span>
                                                                Sekarang
                                                            </span>
                                                            <span class="flex items-center justify-center gap-1">
                                                                <span class="w-2.5 h-2.5 rounded-full border border-amber-500 bg-amber-200"></span>
                                                                Radius
                                                            </span>
                                                            <span class="flex items-center justify-center gap-1">
                                                                <span class="h-0.5 w-4 rounded-full bg-slate-800"></span>
                                                                Jarak
                                                            </span>
                                                        </div>
                                                        <div id="checkoutDistanceInfo"
                                                            class="rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-center text-[11px] font-bold text-slate-700 shadow-xs">
                                                            Menunggu lokasi sekarang...
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-center">
                                                        <button id="checkoutSubmitBtn" type="submit"
                                                            class="flex items-center justify-center w-full gap-2 px-3 py-2 mt-3 text-base text-white uppercase transition duration-100 ease-out bg-yellow-600 rounded-md shadow-md hover:bg-yellow-700 hover:shadow-none all">
                                                            <i class="font-sans text-2xl ri-run-line"></i>
                                                            <span class="font-bold">Pulang Sekarang</span>
                                                        </button>
                                                        <input name="lat_user" value="" class="hidden lat checkout-lat" />
                                                        <input name="long_user" value="" class="hidden long checkout-long" />
                                                        <div id="map" class="hidden"></div>
                                                    </div>
                                                    <span id="checkoutGpsStatus"
                                                        class="hidden mt-1.5 text-xs font-semibold text-center text-red-600"></span>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif