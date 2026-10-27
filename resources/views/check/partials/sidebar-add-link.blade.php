@if ($checkpointLocked)
    <span aria-disabled="true"
        title="Laporan pekan ini ditutup. Dibuka {{ $checkpointNextOpen->translatedFormat('l, d F Y') }} pukul 00:00."
        class="flex cursor-not-allowed items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium text-slate-400">
        <i class="ri-lock-2-line text-base text-slate-300"></i>
        Tambah pekerjaan
    </span>
@else
    <a href="{{ route('checkpoint-user.create') }}"
        class="group flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium transition duration-300 hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('checkpoint-user.create') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
        <i class="ri-add-line text-base text-slate-400 group-hover:text-sky-500"></i>
        Tambah pekerjaan
    </a>
@endif
