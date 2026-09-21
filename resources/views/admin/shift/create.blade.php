<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Shift')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Shift</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Atur jadwal shift baru berdasarkan jabatan dan client.</p>
                </div>
                <a href="{{ route('admin.shift.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.shift.store') }}" class="space-y-4" id="form">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="jabatan_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Jabatan</label>
                        <select name="jabatan_id" id="jabatan_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Jabatan ~</option>
                            @forelse ($jabatan as $jab)
                                <option value="{{ $jab->id }}">{{ $jab->name_jabatan }}</option>
                            @empty
                                <option disabled>~ Data Kosong ~</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label for="client_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Client</label>
                        <select name="client_id" id="client_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Client ~</option>
                            @forelse ($client as $cli)
                                <option value="{{ $cli->id }}">{{ $cli->name }}</option>
                            @empty
                                <option disabled>~ Data Kosong ~</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="shift_name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Shift</label>
                        <input type="text" name="shift_name" id="shift_name" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Masukkan nama shift...">
                    </div>
                    <div>
                        <label for="jam_start" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Jam Mulai</label>
                        <input type="time" name="jam_start" id="jam_start" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label for="jam_end" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Jam Selesai</label>
                        <input type="time" name="jam_end" id="jam_end" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label for="is_overnight" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Pergantian Hari</label>
                        <select name="is_overnight" id="is_overnight" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Pergantian Hari ~</option>
                            <option value="0">Tidak</option>
                            <option value="1">Ya</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Status Shift</p>
                            <p class="mt-0.5 text-xs text-gray-500">Shift aktif akan muncul di pilihan absensi.</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked class="sr-only peer">
                            <div class="peer h-6 w-11 rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-green-500 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 peer-checked:after:translate-x-5"></div>
                            <span class="ml-2 text-sm font-medium text-gray-700" id="is_active_label">Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Hari</label>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                        @php
                            $days = ['Senin' => 'Sen', 'Selasa' => 'Sel', 'Rabu' => 'Rab', 'Kamis' => 'Kam', 'Jumat' => 'Jum', 'Sabtu' => 'Sab', 'Minggu' => 'Min'];
                        @endphp
                        @foreach ($days as $fullDay => $shortDay)
                            <label class="flex cursor-pointer items-center justify-center rounded-lg border border-gray-200 bg-white p-2.5 text-xs font-semibold text-gray-700 transition hover:border-blue-300 has-[:checked]:border-blue-300 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700">
                                <input type="checkbox" name="hari[]" value="{{ $fullDay }}" class="sr-only day-input">
                                <span>{{ $shortDay }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button type="button" id="toggleDaysBtn" class="mt-3 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Pilih Semua</button>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.shift.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Shift</button>
                </div>
            </section>
        </form>
    </div>

    @push('scripts')
        <script>
            (function() {
                const btn = document.getElementById('toggleDaysBtn');
                if (!btn) return;
                btn.addEventListener('click', function() {
                    const checkboxes = Array.from(document.querySelectorAll('.day-input'));
                    const allChecked = checkboxes.every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                    btn.textContent = allChecked ? 'Pilih Semua' : 'Batalkan Semua';
                });
            })();
        </script>
    @endpush
</x-admin-layout>
