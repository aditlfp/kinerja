<x-app-layout>
        @php
            $selectedDate = $selectedDate ?? now()->format('Y-m-d');
        @endphp

        <div class="mx-auto w-full max-w-4xl px-4 py-6 mt-10">
            <div class="card overflow-visible border border-white/70 bg-white shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:shadow-[0_16px_36px_-18px_rgba(15,23,42,.18)]">
                <div class="card-body overflow-visible gap-5 p-5 sm:p-7">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Check Point</p>
                            <h1 class="mt-1 text-2xl font-bold text-slate-900">Tambah pekerjaan</h1>
                            <p class="mt-1 text-sm text-slate-500">Semua pekerjaan berikut disimpan pada tanggal yang sama.</p>
                        </div>
                        <a href="{{ route('checkpoint-user.index') }}" class="btn btn-sm btn-ghost">
                            <i class="ri-arrow-left-s-line"></i>
                            Kembali</a>
                    </div>

                    <form id="form-cp" method="POST" enctype="multipart/form-data" action="{{ route('checkpoint-user.store') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        <input type="hidden" name="divisi_id" value="{{ Auth::user()->divisi->id }}">
                        <input type="hidden" id="latitude" name="latitude" value="">
                        <input type="hidden" id="longtitude" name="longtitude" value="">

                        <div class="mb-6 rounded-xl bg-slate-50 p-4">
                            <label class="label py-0"><span class="label-text font-semibold">Tanggal pekerjaan</span></label>
                            <input class="input input-bordered mt-2 w-full bg-white font-semibold" type="date" value="{{ $selectedDate }}" readonly>
                        </div>

                        <div id="jobs" class="space-y-4">
                            @include('check.partials.job-row', ['index' => 0, 'job' => null, 'pcp' => $pcp, 'selectedDate' => $selectedDate])
                        </div>

                        <button id="add-job" type="button" class="btn mt-4 w-full border-2 border-dashed border-sky-300 bg-sky-50 text-sky-700 transition duration-200 hover:-translate-y-0.5 hover:border-sky-400 hover:bg-sky-100">+ Tambah pekerjaan</button>
                        <button id="submit-job" type="submit" class="btn mt-5 w-full border-0 bg-sky-500 font-bold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">Simpan pekerjaan</button>
                    </form>
                </div>
            </div>
        </div>

        <template id="job-template">
            @include('check.partials.job-row', ['index' => null, 'job' => null, 'pcp' => $pcp, 'selectedDate' => $selectedDate])
        </template>

        @include('check.partials.job-script', ['isEdit' => false])
</x-app-layout>
