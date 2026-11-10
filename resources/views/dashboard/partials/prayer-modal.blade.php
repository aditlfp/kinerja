        <div id="prayerContainer"
            class="fixed inset-0 z-900000 hidden items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-md px-0 sm:px-4">
            <div class="relative w-full sm:max-w-lg overflow-hidden rounded-t-[28px] sm:rounded-3xl bg-white shadow-2xl ring-1 ring-white/80 max-h-[92dvh] sm:max-h-[88vh] flex flex-col">
                
                <div class="absolute inset-x-0 top-0 h-1.5 bg-linear-to-r from-amber-400 via-yellow-500 to-orange-500"></div>

                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 sm:px-6 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                            <i class="ri-sun-foggy-line text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-amber-600">
                                Waktu Sholat
                            </p>
                            <p class="text-sm font-semibold text-slate-500">
                                Pengingat ibadah
                            </p>
                        </div>
                    </div>

                    <button id="closePrayerModal" type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-900">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-5 pb-6 sm:pb-6">
                    <div class="text-center">
                        <h3 id="prayerText" class="text-xl sm:text-2xl font-black leading-tight text-slate-900">
                            
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            Silakan bersiap untuk menunaikan ibadah sholat.
                        </p>
                    </div>

                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-center">
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-700">Pengingat</p>
                        <p class="mt-1 text-sm font-medium text-slate-700">
                            Jaga wudhu dan segera menuju tempat sholat.
                        </p>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-3xl border border-slate-200 bg-slate-950 shadow-inner">
                        <div class="relative aspect-3/4 sm:aspect-video w-full max-h-[250px] bg-slate-900">
                            <video id="prayerCameraVideo" autoplay playsinline muted
                                class="h-full w-full object-cover"></video>

                            <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-linear-to-t from-slate-950/75 to-transparent px-3 py-3">
                                <div class="flex items-center justify-between gap-2 text-[11px] font-semibold text-slate-100">
                                    <span class="flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                        Kamera depan aktif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <canvas id="prayerCameraCanvas" class="hidden"></canvas>
                        <input type="file" id="prayerCameraInput" name="camera_photo" accept="image/*" form="prayerForm" class="hidden">
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-white px-4 sm:px-6 py-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
                    <form id="prayerForm" action="{{ route('absensi-sholat.update', $absenP ? $absenP->id : 0) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input name="lat_user" value="" class="hidden lat" />
                        <input name="long_user" value="" class="hidden long" />
                        <input name="waktu_sholat" value="" class="hidden waktuSholat" />

                        <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-linear-to-r from-yellow-600 to-amber-600 px-4 py-3.5 text-base font-bold text-white shadow-lg shadow-amber-500/30 transition hover:from-yellow-700 hover:to-amber-700 hover:shadow-xl active:scale-[0.99]">
                            <i class="ri-check-line text-xl"></i>
                            <span>Oke Siap</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="mx-5 rounded-md shadow-md sm:mx-10 bg-slate-500">