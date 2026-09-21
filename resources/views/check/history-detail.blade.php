<x-app-layout>
    <div class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Check Point</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Riwayat pekerjaan</h1>
            </div>
            <a href="{{ route('checkpoint-user.history') }}" class="btn btn-sm btn-info"><i class="ri-arrow-left-s-line"></i>Kembali</a>
        </div>
        <div class="space-y-4">
            @foreach (array_values((array) $checkpoint->deskripsi) as $i => $description)
                @php($jobId = array_values((array) $checkpoint->pekerjaan_cp_id)[$i] ?? null)
                @php($manual = array_values((array) $checkpoint->input_manual)[$i] ?? null)
                @php($status = data_get((array) $checkpoint->approve_status, $i))

                <article class="rounded-2xl border {{ $status != 'denied' ? 'border-gray-200' : 'border-rose-500' }} bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-800">{{ $jobs[$jobId]->name ?? ($manual ?? 'Pekerjaan') }}</h2>
                    @php($rowImages = data_get((array) $checkpoint->img, $i, []))
                    @php($rowImages = is_array($rowImages) ? $rowImages : explode(',', (string) $rowImages))
                    @php($rowImages = array_filter($rowImages))
                    @if (count($rowImages))
                        <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                            @foreach ($rowImages as $image)
                                <a href="{{ asset('storage/images/' . $image) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/images/' . $image) }}" alt="Foto pekerjaan"
                                        class="h-20 w-full rounded-lg object-cover ring-1 ring-slate-200">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-600">
                        {{ $description ?: 'Tidak ada deskripsi.' }}</p>
                    @php( $notes = data_get((array) $checkpoint->note, $i))
                    @if ($status === 'denied')
                        <div class="mt-3 rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700 capitalize">
                            <strong>Ditolak:</strong>
                            @if($notes != null) 
                                {{ data_get((array) $checkpoint->note, $i, 'Bukti pekerjaan perlu diperbaiki.') }}</div>
                            @else
                              Bukti pekerjaan perlu diperbaiki. </div>
                            @endif
                    @elseif ($status === 'accept')
                        <div class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-700 capitalize">
                        <strong>Di Setujui:</strong>   
                        Bukti pekerjaan telah disetujui
                        </div>
                    @else
                    <div class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 capitalize">
                    <strong>Menunggu:</strong>   
                       Bukti pekerjaan menunggu di approve
                    </div>
                    @endif
                    <p class="mt-4 text-xs text-slate-400">
                        {{ data_get((array) $checkpoint->tanggal, $i, optional($checkpoint->created_at)->format('Y-m-d')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</x-app-layout>
