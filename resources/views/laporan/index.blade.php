<x-app-layout>
    <x-main-div>
        <div>
            <p class="py-10 text-lg font-bold text-center uppercase sm:text-2xl">Data Laporan</p>
        </div>
        <span class="flex items-center justify-between w-full">
            @if (Auth::user()->role_id == 2)
                <div class="ml-5" style="width: 75%">
                    <form action="{{ route('export.laporans') }}" method="get" class="w-full mb-5">
                        <div class="flex items-center justify-start w-full gap-2">
                            <div class="flex items-center gap-2 " style="width: 90%">
                                <div style="width: 50%;" class="flex flex-col gap-2">
                                    <label for="client_id" class="label">Pilih Mitra & Ruangan (opsional)</label>
                                    <span class="flex flex-col gap-2" style="width: 100%;">
                                        <select name="client_id" id="client_id" style=""
                                            class="text-xs select select-sm select-bordered">
                                            <option selected disabled>~Pilih Mitra~</option>
                                            @forelse ($mitra as $i)
                                                <option value="{{ $i->id }}">{{ $i->client->name }}</option>
                                            @empty
                                                <option>~Kosong~</option>
                                            @endforelse
                                        </select>
                                        <select name="ruangan_id" style=""
                                            class="text-xs select select-sm select-bordered">
                                            <option selected disabled>~Pilih Ruangan (opsional)~</option>
                                            @forelse($ruangan as $ru)
                                                <option value="{{ $ru->id }}">{{ $ru->nama_ruangan }}</option>
                                            @empty
                                                <option>~Kosong~</option>
                                            @endforelse
                                        </select>
                                    </span>
                                </div>
                                <span style="width: 30%" class="flex flex-col gap-2">
                                    <label for="str1" class="label">Tanggal</label>
                                    <div class="flex flex-col gap-2" style="">
                                        <input type="date" name="str1" id="str1" placeholder="Tanggal Mulai"
                                            class="text-md input input-sm input-bordered">
                                        <input type="date" name="end1" id="end1"
                                            class="text-md input input-sm input-bordered">
                                    </div>
                                </span>
                                <div style="width: 20%;" class="">
                                    <label for="nilai" class="label">Nilai</label>
                                    <div class="rounded-lg form-control bg-slate-50">
                                        <label class="cursor-pointer label"
                                            style="padding-top: 2px; padding-bottom: 2px;">
                                            <span class="label-text">Baik</span>
                                            <input type="checkbox" name="nilai[]" value="baik" class="checkbox" />
                                        </label>
                                        <label class="cursor-pointer label"
                                            style="padding-top: 2px; padding-bottom: 2px;">
                                            <span class="label-text">Cukup</span>
                                            <input type="checkbox" name="nilai[]" value="cukup" class="checkbox" />
                                        </label>
                                        <label class="cursor-pointer label"
                                            style="padding-top: 2px; padding-bottom: 2px;">
                                            <span class="label-text">Kurang</span>
                                            <input type="checkbox" name="nilai[]" value="kurang" class="checkbox" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center" style="width: 10%">
                                <input type="hidden" name="action" value="download" />
                                <button type="submit" class="text-sm btn btn-warning sm:btn-sm btn-xs">Print
                                    PDF</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <div class="flex items-center justify-end">
                <x-search />
            </div>
        </span>

        @if (Auth::user()->role_id == 2)
            <div class="p-2 mx-10 mb-5 font-semibold rounded-md bg-slate-50 w-fit">
                <p>*Hapus Foto Laporan</p>
                <form action="{{ route('laporan.hapusFotoLaporan') }}" method="post">
                    @csrf
                    <div class="flex items-end gap-2">
                        <span>
                            <label class="label">Mulai: </label>
                            <input type="date" min="{{ $min }}" max="{{ $max }}" name="mulai"
                                class="input input-bordered input-sm" />
                        </span>
                        <span>
                            <label class="label">Selesai: </label>
                            <input type="date" min="{{ $min }}" max="{{ $max }}" name="selesai"
                                class="input input-bordered input-sm" />
                        </span>
                        <span>
                            <button type="submit" class="btn btn-sm btn-warning">Hapus</button>
                        </span>
                    </div>
                </form>
            </div>
        @endif

        <div class="mx-5 overflow-x-auto">
            <table class="table w-full table-zebra table-xs sm:table-md bg-slate-50" id="searchTable">
                <thead>
                    <tr>
                        <th class="p-1 py-2 bg-slate-300 rounded-tl-2xl">#</th>
                        <th class="p-1 py-2 text-center bg-slate-300" style="min-width: 230px;" colspan="3">Foto Progres</th>
                        <th class="p-1 py-2 bg-slate-300">Keterangan</th>
                        <th class="p-1 py-2 text-center bg-slate-300 rounded-tr-2xl">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $n = 1;
                    @endphp
                    @forelse ($laporan as $i)
                    <tr>
                        <td class="p-1">{{ $n++ }}.</td>
                        @if ($i->img_before != null)
                            <td><img id="image1" src="http://laporan-sac.sac-po.com/storage/{{ $i->img_before }}" data-index="{{ $loop->index }}" data-image="{{ $i->img_before }}" data-imgName="img_before" class="rounded-sm imeg min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]"></td>
                        @else
                            <td>
                            <x-no-img class="min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]" />
                            </td>
                        @endif

                        @if ($i->img_proccess != null)
                            <td><img id="image2" src="http://laporan-sac.sac-po.com/storage/{{ $i->img_proccess }}" data-index="{{ $loop->index }}" data-image="{{ $i->img_proccess }}" data-imgName="img_proccess" class="rounded-sm imeg min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]"></td>
                        @else
                            <td>
                            <x-no-img class="min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]" />
                            </td>
                        @endif

                            @if ($i->img_final != null)
                            <td><img id="image3" src="http://laporan-sac.sac-po.com/storage/{{ $i->img_final }}" data-index="{{ $loop->index }}" data-image="{{ $i->img_final }}" data-imgName="img_final" class="rounded-sm imeg min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]"></td>
                        @else
                            <td>
                            <x-no-img class="min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]" />
                            </td>
                        @endif

                        @if(Auth::user()->devisi_id == 8)
                            <td>{{ $i->note }}</td>
                            <td style="min-width: 100px; text-align: center;">{{ $i->created_at->format('Y-m-d') }}</td>
                            <!--<td>-->
                            <!--    <div class="overflow-hidden ">-->
                            <!--        <a href="{{ route('mitra_laporan.show', $i->id) }}" class="text-sky-400 hover:text-sky-500 text-xl transition-all ease-linear .2s"><i class="ri-eye-fill"></i></a>-->
                            <!--    </div>-->
                            <!--</td>-->
                        @else
                            <td>{{ $i->keterangan }}</td>
                            <td style="min-width: 100px; text-align: center;">{{ $i->created_at->format('Y-m-d') }}</td>
                        @endif
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                ~ Data Kosong ~
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mx-10 mt-5">
            {{ $laporan->links() }}
        </div>
        <div>
            <div class="flex justify-center gap-2 my-3 mr-0 sm:justify-end sm:mr-9">
                <a href="{{ url('/scan') }}"
                    class="btn btn-warning hover:bg-yellow-600 border-none transition-all ease-in-out .2s">+
                    Laporan</a>
                <a href="{{ route('dashboard.index') }}"
                    class="btn btn-error border-none hover:bg-red-500 transition-all ease-in-out .2s">Kembali</a>
            </div>
        </div>
    </x-main-div>
</x-app-layout>
