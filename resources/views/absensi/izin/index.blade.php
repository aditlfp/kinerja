<x-app-layout>
        <div class=" min-h-screen bg-slate-50 px-4 py-5 text-slate-800 sm:-mx-10">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between">
                <a href="{{ route('dashboard.index') }}"><img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32"></a>
                <span class="font-semibold text-sm text-slate-500/50">Halaman Izin</span>
            </div>
            <p class="mx-auto mt-8 max-w-5xl border-l-4 border-sky-500 pl-3 text-xl font-black uppercase">Riwayat Izin Saya</p>
            
            <div class="flex flex-col items-center mx-2 my-2 sm:justify-center justify-start">
                <div class="overflow-x-scroll w-full md:overflow-hidden mx-2 sm:mx-0 sm:w-full">
                    <table class="table table-xs w-full overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200" id="searchTable">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700">
                                <th>#</th>
                                <th class="px-7">Nama lengkap</th>
                                <th class="px-12">Mitra</th>
                                <th>Shift</th>
                                <th class="px-10">Alasan izin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="rounded-b-xl">
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($izin as $i)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $i->user->nama_lengkap }}</td>
                                    <td>{{ $i->kerjasama->client->name }}</td>
                                    <td>{{ $i->shift->shift_name }}</td>
                                    <td>{{ $i->alasan_izin }}</td>
                                    <td>
                                        @if ($i->approve_status == 'process')
                                            <span class="badge badge-warning overflow-hidden">{{ $i->approve_status }}</span>    
                                        @elseif($i->approve_status == 'accept')
                                            <span class="badge badge-success overflow-hidden">{{ $i->approve_status }}</span>    
                                        @else
                                            <span class="badge badge-error overflow-hidden">{{ $i->approve_status }}</span>    
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">~ Kosong ~</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="pag-1" class="mt-5 mb-5 mx-10">
                    {{ $izin->links() }}
                </div>
                <div class="mt-4 flex justify-center sm:justify-end">
                    <a href="{{ route('dashboard.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">Kembali</a>
                </div>
            </div>
        </div>
            </div>
        </div>
</x-app-layout>