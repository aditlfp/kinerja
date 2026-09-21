<x-app-layout>
    @php $total = $users->total(); @endphp
    <div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-8 lg:px-12">
        <div class="mt-10 mb-9 flex items-center justify-center gap-6">
                <div class="w-full flex justify-between items-center gap-2">
                    <a href="{{ route('direksi.cp.index', ['month' => $previousMonth]) }}" aria-label="Bulan sebelumnya" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i class="ri-arrow-left-s-line text-xl"></i></a>
                    <div class="flex flex-col items-center">
                        <h1 class="text-3xl font-semibold overflow-hidden tracking-tight text-slate-900 sm:text-4xl">{{ $start->translatedFormat('F Y') }}</h1>
                        <p class="mt-2 text-sm text-slate-500 hidden sm:block">Pilih tanggal untuk melihat atau mengisi pekerjaan.</p>
                    </div>
                    <a href="{{ route('direksi.cp.index', ['month' => $nextMonth]) }}" aria-label="Bulan berikutnya" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i class="ri-arrow-right-s-line text-xl"></i></a>
                </div>
            </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] sm:p-7">
        <section class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($users as $employee)
                <a href="{{ route('direksi.cp.calendar', ['user' => $employee->id, 'month' => $start->format('Y-m')]) }}"
                   class="mt-3 group rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-[0_16px_36px_-18px_rgba(15,23,42,.22)] active:scale-[.99]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-50 text-lg text-sky-600 transition duration-300 group-hover:bg-sky-500 group-hover:text-white"><i class="ri-user-line"></i></span>
                            <div class="min-w-0">
                                <h2 class="truncate font-semibold capitalize text-slate-900">{{ strtolower($employee->nama_lengkap) }}</h2>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $employee->jabatan->name_jabatan ?? '-' }}</p>
                            </div>
                        </div>
                        <i class="ri-arrow-right-line text-lg text-slate-300 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] group-hover:translate-x-1 group-hover:text-sky-500"></i>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-slate-500"><i class="ri-building-line text-sky-500"></i>{{ $employee->divisi->name ?? 'Divisi -' }}</div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-14 text-center text-sm text-slate-500">
                    <i class="ri-user-search-line mb-2 block text-3xl text-slate-300"></i>
                    Karyawan tidak ditemukan.
                </div>
            @endforelse
        </section>

        @if ($users->hasPages())
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white px-4 py-3">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
