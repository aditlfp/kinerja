@if (isset($pendingDireksiCheckpoints) && $pendingDireksiCheckpoints->isNotEmpty())
<div id="checkpoint-pending" class="fixed inset-0 z-[9000] flex items-center justify-center bg-slate-500/10 p-4 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div><p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Bukti Pekerjaan</p><h2 class="mt-1 text-lg font-bold text-slate-900">Pengajuan Bukti Pekerjaan</h2></div>
            <button type="button" onclick="document.getElementById('checkpoint-pending')?.remove()" class="text-2xl leading-none text-slate-400 hover:text-slate-700">&times;</button>
        </div>
        <p class="mt-3 text-sm text-slate-600">Pengajuan berikut menunggu persetujuan:</p>
        <div class="mt-4 max-h-72 space-y-2 overflow-y-auto">
            @foreach ($pendingDireksiCheckpoints as $item)
                <a href="{{ route('direksi.cp.history.show', $item->id) }}" class="block rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 transition hover:border-sky-300 hover:bg-sky-50">
                    <div class="flex items-center justify-between gap-3"><strong class="text-sm text-slate-900">{{ $item->employee }}</strong><span class="text-xs text-slate-500">{{ $item->dates }} {{ $item->month }}</span></div>
                    <p class="mt-1 text-xs text-slate-600">Pengajuan bukti pekerjaan menunggu persetujuan.</p>
                </a>
            @endforeach
        </div>
        <div class="mt-5 flex justify-end"><button type="button" onclick="document.getElementById('checkpoint-pending')?.remove()" class="rounded-xl bg-sky-500 px-4 py-2 text-sm font-bold text-white hover:bg-sky-600">Tutup</button></div>
    </div>
</div>
@endif
