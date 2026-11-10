<x-app-layout>
    <x-main-div>
        <div class="-mx-5 min-h-screen bg-slate-50 px-4 py-5 text-slate-800 sm:-mx-10">
            <div class="mx-auto flex w-full max-w-2xl items-center justify-between">
                <a href="{{ route('dashboard.index') }}"><img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32"></a>
                <p class="flex items-center gap-2 text-lg font-black"><i class="ri-file-text-line"></i> Detail Izin</p>
            </div>
            <div class="mx-auto max-w-2xl pt-8">
            <p class="border-l-4 border-sky-500 pl-3 text-xl font-black uppercase">Detail Izin</p>
            <div class="my-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div>
                    <div class="flex items-center justify-center pt-10">
                        <div class=" mx-2 my-2 overflow-hidden flex items-center sm:w-[40%] justify-center bg-slate-200  shadow-md  hover:shadow-none transition-all .2s  ease-in-out">
                            @if ($izinId->img == 'no-image.jpg')
                                <img class="w-20 rounded-full " src="{{ URL::asset('/logo/person.png') }}" alt="profile-logo.png"
                                srcset="{{ URL::asset('/logo/person.png') }}">
                            @else
                                <img class="m-2 " src="{{ asset('storage/images/'.  $izinId->img) }}" alt="profile-logo2.png" srcset="{{ asset('storage/images/'.  $izinId->img) }}">
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-center pb-5">
                        @if ($izinId->approve_status == 'process')
                            <div class="w-full px-4 py-2 mx-10 overflow-hidden text-xs font-semibold text-center capitalize shadow-md rounded-tr-md rounded-bl-md bg-amber-500 sm:text-base">
                                <span >{{ $izinId->approve_status }}</span>
                            </div>
                        @elseif($izinId->approve_status == 'accept')
                            <div class="w-full px-4 py-2 mx-10 overflow-hidden text-xs text-center text-white capitalize shadow-md rounded-tr-md rounded-bl-md bg-emerald-700 sm:text-base">
                                <span >{{ $izinId->approve_status }}</span>
                            </div>
                        @else
                            <div class="w-full px-4 py-2 mx-10 overflow-hidden text-xs font-semibold text-center text-white capitalize bg-red-500 shadow-md rounded-tr-md rounded-bl-md sm:text-base">
                                <span >{{ $izinId->approve_status }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mx-4 my-4 rounded-xl bg-slate-50 p-3 py-5 text-sm font-semibold ring-1 ring-slate-200">
                        <div class="space-y-2 text-xs text-slate-800 sm:text-sm">
                            <div class="flex flex-col w-full sm:flex-row ">
                                <span>Nama Lengkap: </span>
                                <span class="indent-2">{{ $izinId->user->nama_lengkap }}</span>
                            </div>
                            <div class="flex flex-col w-full sm:flex-row ">
                                <span>Bermitra Dengan: </span>
                                <span class="indent-2">{{ $izinId->kerjasama->client->name }}</span>
                            </div>
                            <div class="flex flex-col w-full sm:flex-row ">
                                <span>Shift :</span>
                                <span class="indent-2">{{ $izinId->shift->shift_name }} | {{ $izinId->shift->jam_start }} - {{ $izinId->shift->jam_end }}</span>
                            </div>
                            <div class="flex flex-col w-full sm:flex-row ">
                                <span>Tanggal Dibuat: </span>
                                <span class="indent-2">{{ $izinId->created_at->format('Y-m-d : H:i:s') }}</span>
                            </div>
                            <div class="flex flex-col w-full wrap-break-word whitespace-normal">
                                <span>Alasan izin: </span>
                                <span class="textarea textarea-bordered">{{ $izinId->alasan_izin }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-4 flex justify-center sm:justify-end">
            @if(Auth::user()->role_id == 2)
                <a href="{{ route('admin.izin.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">Kembali</a>
            @elseif (Auth::user()->jabatan_id == 15)
                <a href="{{ route('mitra_izin') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">Kembali</a>
            @else
                <a href="{{ route('lead_izin') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">Kembali</a>
            @endif
        </div>
            </div>
        </div>
    </x-main-div>
</x-app-layout>
