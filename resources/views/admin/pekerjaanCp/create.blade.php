<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Pekerjaan')

    <div class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-1 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Rencana kerja</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Tambah Pekerjaan CP</h1>
                <p class="mt-1 text-sm text-slate-500">Tentukan pemilik, klasifikasi, dan bobot pekerjaan.</p>
            </div>
            <a href="{{ route('admin.pekerjaan-cp.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('admin.pekerjaan-cp.store') }}" id="form" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            <div class="grid gap-8 p-5 sm:p-7 lg:grid-cols-2">
                <section>
                    <div class="mb-5 flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-blue-50 text-blue-600"><i class="ri-user-settings-line"></i></span>
                        <div><h2 class="font-bold text-slate-900">Penanggung jawab</h2><p class="text-xs text-slate-500">Hubungkan pekerjaan dengan data operasional.</p></div>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <label for="user_id" class="field-label">User</label>
                            <select name="user_id" id="user_id" class="field-control" required>
                                <option disabled selected>~ Pilih User ~</option>
                                @forelse ($user as $us)
                                    @if($us->kerjasama_id == 1)<option value="{{ $us->id }}" data-divisi="{{ $us->devisi_id }}">{{ $us->nama_lengkap }}</option>@endif
                                @empty<option disabled>~ Data Kosong ~</option>@endforelse
                            </select>
                            @error('user_id')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="devisi_id" class="field-label">Divisi</label>
                            <select name="devisi_id" id="devisi_id" class="field-control" required>
                                <option disabled selected>~ Pilih Divisi ~</option>
                                @forelse ($divisi as $d)<option value="{{ $d->id }}">{{ $d->jabatan->name_jabatan }}</option>@empty<option disabled>~ Data Kosong ~</option>@endforelse
                            </select>
                            <p class="field-help">Terisi otomatis berdasarkan user yang dipilih.</p>
                            @error('devisi_id')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="kerjasama_id" class="field-label">Kerjasama</label>
                            <select name="kerjasama_id" id="kerjasama_id" class="field-control" required>
                                <option disabled>~ Pilih Kerjasama ~</option>
                                @forelse ($kerjasama as $ker)<option value="{{ $ker->id }}" {{ Auth::user()->kerjasama_id == $ker->id ? 'selected' : '' }}>{{ $ker->client->name }}</option>@empty<option disabled>~ Data Kosong ~</option>@endforelse
                            </select>
                            @error('kerjasama_id')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section>
                    <div class="mb-5 flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><i class="ri-file-list-3-line"></i></span>
                        <div><h2 class="font-bold text-slate-900">Detail pekerjaan</h2><p class="text-xs text-slate-500">Informasi yang muncul pada rencana kerja.</p></div>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <label for="type_check" class="field-label">Jenis pekerjaan</label>
                            <select name="type_check" id="type_check" class="field-control" required>
                                <option disabled selected>~ Pilih Jenis Pekerjaan ~</option>
                                <option value="harian">Harian</option><option value="mingguan">Mingguan</option><option value="bulanan">Bulanan</option><option value="isidental">Isidental</option>
                            </select>
                            @error('type_check')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="name" class="field-label">Nama pekerjaan</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="field-control" placeholder="Contoh: Pemeriksaan stok gudang" required maxlength="255">
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                            <label for="weighting_value" class="flex items-center justify-between text-xs font-bold uppercase tracking-wide text-blue-800"><span>Nilai bobot</span><span class="font-medium normal-case tracking-normal text-blue-600">0–1000 poin</span></label>
                            <input type="number" name="weighting_value" id="weighting_value" min="0" max="1000" value="{{ old('weighting_value', 0) }}" required class="mt-2 field-control border-blue-200 font-semibold focus:border-blue-500 focus:ring-blue-100">
                            <p class="field-help text-blue-700/70">Bobot pekerjaan untuk perhitungan kinerja.</p>
                            @error('weighting_value')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
                <a href="{{ route('admin.pekerjaan-cp.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"><i class="ri-save-3-line"></i> Simpan pekerjaan</button>
            </div>
        </form>
    </div>

    <style>
        .field-label { display:block; margin-bottom:.45rem; color:#334155; font-size:.75rem; font-weight:700; letter-spacing:.025em; }
        .field-control { box-sizing:border-box; display:block; width:100%; height:2.75rem; min-height:2.75rem; border:1px solid #cbd5e1; border-radius:.75rem; background:#fff; padding:.65rem .8rem; color:#334155; font:inherit; font-size:.875rem; line-height:1.25rem; outline:none; transition:border-color .15s, box-shadow .15s; }
        .field-control:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgb(59 130 246 / .12); }
        .field-help { margin-top:.4rem; color:#64748b; font-size:.75rem; }
        .field-error { margin-top:.4rem; color:#dc2626; font-size:.75rem; font-weight:600; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const user = document.getElementById('user_id');
            const divisi = document.getElementById('devisi_id');
            const syncDivisi = () => {
                const selectedDivisi = user.selectedOptions[0]?.dataset.divisi;
                if (selectedDivisi && [...divisi.options].some(option => option.value === selectedDivisi)) {
                    divisi.value = selectedDivisi;
                    divisi.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };
            user.addEventListener('change', syncDivisi);
            syncDivisi();
        });
    </script>
</x-admin-layout>
