<x-app-layout>
        <style>
            .divImg {
                @media (min-width: 768px) {
                    max-width: 50svw;
                }
            }

            .container-data p {
                font-size: 7%;

                @media (min-width: 768px) {
                    font-size: 60%;
                }
            }

            .container-data #bulan {
                top: 31.8%;

                @media (min-width: 768px) {
                    top: 32%;
                }
            }

            .container-data #dibuat {
                top: 27.2%;

                @media (min-width: 768px) {
                    top: 27.4%;
                }
            }

            .container-data #nama {
                top: 34.5%;

                @media (min-width: 768px) {
                    top: 34.4%;
                }
            }

            .container-data #jabatan {
                top: 37%;

                @media (min-width: 768px) {
                    top: 36.9%;
                }
            }

            .container-data #mitra {
                top: 39.5%;

                @media (min-width: 768px) {
                    top: 39.4%;
                }
            }

            .container-data #status {
                top: 42%;

                @media (min-width: 768px) {
                    top: 41.9%;
                }
            }

            /*penghasilan*/
            .container-data #gaji_pokok,
            #bpjs {
                top: 51.5%;

                @media (min-width: 768px) {
                    top: 51.4%;
                }
            }

            .container-data #gaji_lembur,
            #pinjaman {
                top: 54.3%;

                @media (min-width: 768px) {
                    top: 54.2%;
                }
            }

            .container-data #tj_jabatan,
            #absen {
                top: 56.8%;

                @media (min-width: 768px) {
                    top: 56.7%;
                }
            }

            .container-data #tj_kehadiran,
            #lain_lain {
                top: 59.3%;

                @media (min-width: 768px) {
                    top: 59.2%;
                }
            }

            .container-data #tj_kinerja {
                top: 61.8%;

                @media (min-width: 768px) {
                    top: 61.7%;
                }
            }

            .container-data #tj_lain {
                top: 64.3%;

                @media (min-width: 768px) {
                    top: 64.2%;
                }
            }

            .container-data #total_penghasilan,
            #total_potongan {
                top: 69.4%;

                @media (min-width: 768px) {
                    top: 69.6%;
                }
            }

            .container-data #total_bersih {
                top: 73.5%;

                @media (min-width: 768px) {
                    top: 73.6%;
                }
            }
        </style>
        <div class="min-h-screen bg-slate-50 px-4 py-5 text-slate-800 md:flex md:flex-col md:items-center md:justify-center">
            <div class="mx-auto flex w-full max-w-2xl items-center justify-between">
                <a href="{{ route('dashboard.index') }}">
                    <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32">
                </a>
                <span class="font-semibold text-sm text-slate-500/50">Slip Gaji</span>
            </div>
            <div class="mx-auto pt-8">
                <p class="border-l-4 border-sky-500 pl-3 text-xl font-black uppercase sm:text-2xl">Data Slip Gaji <br>
                    {{ Carbon\Carbon::createFromFormat('Y-m', $bulan)->isoFormat('MMMM Y') }}</p>
            </div>

            <div class="mt-5 flex justify-center px-0">
                <form action="" method="get"
                    class="flex w-full max-w-md flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="month" class="mb-1 block text-sm font-medium text-slate-700">Filter Bulan</label>
                        <input type="month" name="bulan" value="{{ $bulan }}" id="month"
                            class="input input-sm input-bordered w-full bg-slate-50">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-600 sm:w-auto">Cari</button>
                </form>
            </div>
            @if ($slip)
                <div class="flex flex-col items-center mx-2 my-5 sm:justify-center justify-start">
                    <div class="overflow-x-auto md:overflow-hidden mx-2 sm:mx-0 bg-white border-slate-200 shadow-sm p-2 rounded-md"
                        id="divImg">
                        <div class="rounded-md relative" style="user-select: none;">
                            <img src="{{ asset('logo/desain_slip.png') }}" width="450" oncontextmenu="return false;"
                                class="rounded-md slip-img" />

                            <div style="z-index: 10;" class="absolute inset-0 container-data">
                                @php
                                    $totalPenghasilan =
                                        $slip?->gaji_pokok +
                                        $slip?->gaji_lembur +
                                        $slip?->tj_jabatan +
                                        $slip?->tj_kehadiran +
                                        $slip?->tj_kinerja +
                                        $slip?->tj_lain;
                                    $totalPotongan =
                                        $slip?->bpjs + $slip?->pinjaman + $slip?->absen + $slip?->lain_lain;
                                    $totalBersih = 0;
                                    if ($totalPotongan > 0) {
                                        $totalBersih = $totalPenghasilan - $totalPotongan;
                                    } else {
                                        $totalBersih = $totalPenghasilan + $totalPotongan;
                                    }
                                @endphp
                                <!--bulan-->
                                <p id="bulan" class="absolute font-semibold" style=" left: 51.5%; ">
                                    {{ Carbon\Carbon::createFromFormat('Y-m', $slip->bulan_tahun)->isoFormat('MMMM Y') }}
                                </p>
                                <p id="dibuat" class="absolute" style="right: 5%; font-weight: 500;">Dibuat Pada :
                                    {{ Carbon\Carbon::createFromFormat('Y-m-d', $slip->created_at->format('Y-m-d'))->isoFormat('D MMMM Y') }}
                                </p>
                                <!--data diri-->
                                <p id="nama" class="absolute font-semibold capitalize" style=" left: 20%; ">
                                    {{ ucwords(strtolower(Auth::user()->nama_lengkap)) }}</p>
                                <p id="jabatan" class="absolute font-semibold capitalize" style=" left: 20%; ">
                                    {{ Auth::user()->divisi->jabatan->name_jabatan }}</p>
                                <p id="mitra" class="absolute font-semibold capitalize" style=" left: 20%; ">
                                    {{ Auth::user()->kerjasama->client->name }}</p>
                                <p id="status" class="absolute font-semibold capitalize" style=" left: 20%; ">
                                    {{ $slip->status ? 'Kontrak' : 'Training' }}</p>
                                <!--penghasilan-->
                                <p id="gaji_pokok" class="absolute font-semibold" style=" left: 25%; ">
                                    {{ toRupiah($slip?->gaji_pokok) }}</p>
                                <p id="gaji_lembur" class="absolute font-semibold" style=" left: 25%; ">
                                    {{ toRupiah($slip?->gaji_lembur) }}</p>
                                <p id="tj_jabatan" class="absolute font-semibold" style=" left: 25%; ">
                                    {{ toRupiah($slip?->tj_jabatan) }}</p>
                                <p id="tj_kehadiran" class="absolute font-semibold" style=" left: 25%; ">
                                    {{ toRupiah($slip?->tj_kehadiran) }}</p>
                                <p id="tj_kinerja" class="absolute font-semibold" style=" left: 25%; ">
                                    {{ toRupiah($slip?->tj_kinerja) }}</p>
                                <p id="tj_lain" class="absolute font-semibold" style=" left: 25%; ">
                                    {{ toRupiah($slip?->tj_lain) }}</p>
                                <p id="total_penghasilan" class="absolute" style=" left: 25%; font-weight: 700; ">
                                    {{ toRupiah($totalPenghasilan) }}</p>
                                <!--potongan-->
                                <p id="bpjs" class="absolute font-semibold" style=" left: 77%; ">
                                    {{ toRupiah($slip?->bpjs) }}</p>
                                <p id="pinjaman" class="absolute font-semibold" style=" left: 77%; ">
                                    {{ toRupiah($slip?->pinjaman) }}</p>
                                <p id="absen" class="absolute font-semibold" style=" left: 77%; ">
                                    {{ toRupiah($slip?->absen) }}</p>
                                <p id="lain_lain" class="absolute font-semibold" style=" left: 77%; ">
                                    {{ toRupiah($slip?->lain_lain) }}</p>
                                <p id="total_potongan" class="absolute" style=" left: 77%; font-weight: 700; ">
                                    {{ toRupiah($totalPotongan) }}</p>
                                <!--total bersih-->
                                <p id="total_bersih" class="absolute" style=" left: 39%; font-weight: 800; ">
                                    {{ toRupiah($totalBersih) }}</p>
                            </div>
                        </div>
                        @php
                            $carbon = Carbon\Carbon::now();

                            $lastDay = Carbon\Carbon::now()->endOfMonth()->startOfDay();
                            $lastDayPlus = Carbon\Carbon::now()->endOfMonth()->addDays(3)->endOfDay();

                            $isComplain = $carbon->between($lastDay, $lastDayPlus);
                        @endphp
                        <div class="flex items-start mx-4 {{ $isComplain ? 'justify-between' : 'justify-center' }}"
                            style="padding: 20px 0 10px 0;">
                            @if (
                                $slip->bulan_tahun == $carbon->subMonth()->format('Y-m') &&
                                    $bulan == $carbon->subMonth()->format('Y-m') &&
                                    $isComplain)
                                <div class="flex flex-col justify-center items-center">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLScboxEKmSbdSYQtGathLGiwuoBesmye41v5u1vacMLsGZM8rQ/viewform?usp=header"
                                        target="_blank" class="btn btn-sm btn-warning">Ajukan Komplain?</a>
                                    <p class="text-[7pt] font-medium">*jika gaji kurang sesuai</p>
                                </div>
                            @endif
                            <form action="{{ route('slip-gaji.export') }}" method="get">
                                <input type="hidden" name="slip_id" value="{{ $slip->id }}" />
                                <button type="submit" class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-amber-500">Download</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-slate-50 rounded-md mx-5 my-5 flex items-center justify-center"
                    style="min-height: 100px;">
                    <p class="text-center font-semibold italic">~Data Masih Kosong~</p>
                </div>
            @endif

            <div class="flex justify-center gap-2 py-5 sm:justify-end">
                <a href="{{ route('dashboard.index') }}" class="rounded-lg bg-red-400 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-300 border border-red-500/20">Kembali</a>
            </div>
            </div>
        </div>

        </div>
        <script></script>
</x-app-layout>
