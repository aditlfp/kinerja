<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Pekerjaan')

    <div class="w-full px-2 py-6 mx-auto max-w-screen-xl sm:px-3 lg:px-4">
        <section class="px-0.5">
            <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Pekerjaan CP</h1>
            <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Lengkapi data pekerjaan untuk menentukan rencana kerja berdasarkan user, divisi, dan kerjasama.</p>
        </section>

        <section class="p-4 mt-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-6">
            <form method="POST" action="{{ route('admin.pekerjaan-cp.store') }}" id="form" class="space-y-5">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex flex-col">
                        <label for="user_id" class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Pilih User</label>
                        <select name="user_id" id="user_id" class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                            <option disabled selected>~ Pilih User ~</option>
                            @forelse ($user as $us)
                                @if($us->kerjasama_id == 1)
                                    <option value="{{ $us->id }}" data-divisi="{{ $us->devisi_id }}">{{ $us->nama_lengkap }}</option>
                                @endif
                            @empty
                                <option disabled>~ Data Kosong ~</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="devisi_id" class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Pilih Divisi</label>
                        <select name="devisi_id" id="devisi_id" class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                            <option disabled selected>~ Pilih Divisi ~</option>
                            @forelse ($divisi as $d)
                                <option value="{{ $d->id }}">{{ $d->jabatan->name_jabatan }}</option>
                            @empty
                                <option disabled>~ Data Kosong ~</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="kerjasama_id" class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Pilih Kerjasama</label>
                        <select name="kerjasama_id" id="kerjasama_id" class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                            <option disabled selected>~ Pilih Kerjasama ~</option>
                            @forelse ($kerjasama as $ker)
                                <option value="{{ $ker->id }}" {{ Auth::user()->kerjasama_id == $ker->id ? 'selected' : '' }}>{{ $ker->client->name }}</option>
                            @empty
                                <option disabled>~ Data Kosong ~</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="type_check" class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Pilih Jenis Pekerjaan</label>
                        <select name="type_check" id="type_check" class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                            <option disabled selected>~ Pilih Jenis Pekerjaan ~</option>
                            <option value="harian">Harian</option>
                            <option value="mingguan">Mingguan</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="isidental">Isidental</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="name" class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Pekerjaan</label>
                    <input type="text" name="name" id="name" class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" placeholder="Nama pekerjaan...">
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <a href="{{ route('admin.pekerjaan-cp.index') }}" class="inline-flex h-10 items-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">Back</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </section>
    </div>
		
		<script>
        $(document).ready(function() {
            $('#user_id').on('change', function() {
                var selectedDivisi = $(this).find(':selected').data('divisi'); // Get the data-divisi attribute value
    
                // Set the selected value in the 'devisi_id' dropdown
                $('#devisi_id').val(selectedDivisi);
            });
        });
    </script>
</x-admin-layout>
