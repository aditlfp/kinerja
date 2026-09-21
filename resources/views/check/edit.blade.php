<x-app-layout>
        @php
            $ids = array_values((array) $cex->pekerjaan_cp_id);
            $manuals = array_values((array) $cex->input_manual);
            $descriptions = array_values((array) $cex->deskripsi);
            $images = array_values((array) $cex->img);
            $dates = array_values((array) $cex->tanggal);
            $manualValues = array_values(array_filter($manuals, fn ($value) => filled($value)));
            $ids = array_values(array_pad($ids, count($descriptions), null));
            $rows = max(count($ids), count($descriptions), count($images), 1);
            $manuals = array_fill(0, $rows, null);
            $manualIndex = 0;
            for ($rowIndex = 0; $rowIndex < $rows && $manualIndex < count($manualValues); $rowIndex++) {
                if (empty($ids[$rowIndex])) {
                    $manuals[$rowIndex] = $manualValues[$manualIndex++];
                }
            }
            while ($manualIndex < count($manualValues)) {
                $ids[] = null;
                $manuals[] = $manualValues[$manualIndex++];
                $rows++;
            }
            $selectedDate = request('tanggal', $dates[0] ?? optional($cex->created_at)->format('Y-m-d'));
            $approveStatuses = array_values((array) $cex->approve_status);
        @endphp
        <div class="mx-auto w-full max-w-4xl px-4 py-6">
            <div class="card overflow-visible border border-white/70 bg-white shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:shadow-[0_16px_36px_-18px_rgba(15,23,42,.18)]">
                <div class="card-body overflow-visible gap-5 p-5 sm:p-7">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5"><div><p class="text-xs font-bold uppercase tracking-[.18em] text-sky-600">Check Point</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Ubah pekerjaan</h1></div><a href="{{ route('checkpoint-user.index') }}" class="btn btn-sm btn-ghost">Kembali</a></div>
                    <form id="form-cp" method="POST" enctype="multipart/form-data" action="{{ route('checkpoint-user.update', $cex->id) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="user_id" value="{{ $cex->user_id }}"><input type="hidden" name="divisi_id" value="{{ $cex->divisi_id }}"><input type="hidden" id="latitude" name="latitude" value="{{ $cex->latitude }}"><input type="hidden" id="longtitude" name="longtitude" value="{{ $cex->longtitude }}">
                        <div class="mb-6 rounded-xl bg-slate-50 p-4"><label class="label py-0"><span class="label-text font-semibold">Tanggal pekerjaan</span></label><input class="input input-bordered mt-2 w-full bg-white font-semibold" type="date" value="{{ $selectedDate }}" readonly><input type="hidden" name="tanggal[]" value="{{ $selectedDate }}"></div>
                        <div id="jobs" class="space-y-4">
                            @for ($i = 0; $i < $rows; $i++)
                                @php
                                    $jobId = $ids[$i] ?? null;
                                    $manual = filled($manuals[$i] ?? null) ? $manuals[$i] : null;
                                    $rowImages = is_array($images[$i] ?? null) ? $images[$i] : (($images[$i] ?? null) ? [$images[$i]] : []);
                                @endphp
                                @include('check.partials.job-row-edit', compact('i', 'jobId', 'manual', 'rowImages', 'pcp', 'selectedDate', 'descriptions', 'approveStatuses'))
                            @endfor
                        </div>
                        <button id="add-job" type="button" class="btn mt-4 w-full border-2 border-dashed border-sky-300 bg-sky-50 text-sky-700 transition duration-200 hover:-translate-y-0.5 hover:border-sky-400 hover:bg-sky-100">+ Tambah pekerjaan</button><button id="submit-job" type="submit" class="btn mt-5 w-full border-0 bg-sky-500 font-bold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">Simpan perubahan</button>
                    </form>
                </div>
            </div>
        </div>
        <template id="job-template">@include('check.partials.job-row', ['index' => null, 'job' => null, 'pcp' => $pcp, 'selectedDate' => $selectedDate])</template>
        @include('check.partials.job-script', ['isEdit' => true])
</x-app-layout>
