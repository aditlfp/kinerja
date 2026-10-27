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
            @foreach ($checkpoint->items as $item)
                @php
                    $jobId = $item->pekerjaan_cp_id;
                    $manual = $item->input_manual;
                    $status = $item?->approve_status;
                    $isDenied = $status === 'denied';
                    $isAccepted = $status === 'accept';
                    $rowImages = $item->images->pluck('path')->all();
                @endphp

                <article class="rounded-2xl border {{ $status != 'denied' ? 'border-gray-200' : 'border-rose-500' }} bg-white p-5 shadow-xs">
                    <h2 class="font-bold text-slate-800">{{ $jobs[$jobId]->name ?? ($manual ?? 'Pekerjaan') }}</h2>
                    @if (count($rowImages))
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($rowImages as $image)
                                <a href="{{ asset('storage/images/' . $image) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/images/' . $image) }}" alt="Foto pekerjaan"
                                        class="rounded-lg ring-1 ring-slate-200">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-600">
                        {{ $item->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                    <x-checkpoint-status-badge :status="$status" :label="$isAccepted ? 'Disetujui' : null" />
                    <p class="mt-4 text-xs text-slate-400">
                        {{ $item->tanggal?->format('Y-m-d') ?? optional($checkpoint->created_at)->format('Y-m-d') }}
                    </p>

                    @include('check.partials.item-thread', ['item' => $item, 'viewerRole' => 'employee'])
                </article>
            @endforeach
        </div>
    </div>
</x-app-layout>
