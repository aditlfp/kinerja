<x-admin-layout :fullWidth="true">
    @section('title', 'Pengaturan Bukti Pekerjaan')

    <div class="w-full max-w-screen-xl px-2 py-6 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">
                        Pengaturan Bukti Pekerjaan</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Tentukan hari terakhir laporan bukti pekerjaan dalam satu pekan. Setelah hari itu lewat,
                        semua input checkpoint ditutup sampai pekan berikutnya (hari Senin).
                    </p>
                </div>
                <a href="{{ route('admin.cp.index') }}"
                    class="inline-flex h-10 items-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">Kembali</a>
            </div>
        </section>

        {{-- Ringkasan status hari ini --}}
        <section class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Batas laporan</p>
                <p class="mt-1 text-lg font-semibold text-gray-800">Hari {{ $days[$lastDay] }}</p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $setting ? 'Diatur admin' : 'Default (belum diatur)' }}
                </p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status hari ini</p>
                <p class="mt-1 inline-flex items-center gap-2 text-lg font-semibold {{ $isLocked ? 'text-red-600' : 'text-emerald-600' }}">
                    <span class="inline-block h-2.5 w-2.5 rounded-full {{ $isLocked ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                    {{ $isLocked ? 'Terkunci' : 'Terbuka' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $isLocked ? 'Input checkpoint ditolak hari ini.' : 'Input checkpoint masih diterima hari ini.' }}
                </p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ $isLocked ? 'Dibuka lagi' : 'Tutup pekan ini' }}
                </p>
                <p class="mt-1 text-lg font-semibold text-gray-800">
                    {{ ($isLocked ? $nextOpenAt : $deadline)->translatedFormat('d M Y') }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ ($isLocked ? $nextOpenAt : $deadline)->translatedFormat('H:i') }} WIB
                </p>
            </div>
        </section>

        <section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
            @if (!$setting)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                    Batas laporan belum diatur. Sistem memakai default: <strong>hari Sabtu</strong>.
                </div>
            @endif

            @php
                $oldDay = old('last_day', $setting?->lastDay() ?? \App\Models\CheckPointReportSetting::DEFAULT_LAST_DAY);
            @endphp

            <form method="POST" action="{{ route('admin.cp.settings.update') }}" class="mt-4 space-y-4">
                @csrf
                <div class="max-w-sm">
                    <label for="last_day"
                        class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Hari terakhir laporan</label>
                    <select id="last_day" name="last_day" required
                        class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                        @foreach ($days as $value => $label)
                            <option value="{{ $value }}" @selected((string) $oldDay === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('last_day')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl bg-gray-50 p-3 text-xs leading-5 text-gray-600">
                    <p class="font-semibold text-gray-700">Cara kerja</p>
                    <p class="mt-1">
                        Pekan dihitung Senin–Minggu. Bila batas disetel <strong>Sabtu</strong>, maka hari
                        <strong>Minggu</strong> input bukti pekerjaan ditutup dan dibuka lagi hari Senin.
                        Admin dan Direksi tetap bisa input kapan saja.
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>

            @if ($setting)
                <div class="mt-5 space-y-1 border-t border-gray-100 pt-4 text-sm text-gray-600">
                    <p>Batas aktif: <span class="font-semibold text-gray-800">{{ $setting->label() }}</span></p>
                    <p>Diubah oleh: <span
                            class="font-semibold text-gray-800">{{ $setting->updater->nama_lengkap ?? '-' }}</span></p>
                    <p>Terakhir update: <span
                            class="font-semibold text-gray-800">{{ $setting->updated_at?->format('d M Y H:i') }}</span></p>

                    <form id="reset-batas-laporan-form" method="POST" action="{{ route('admin.cp.settings.reset') }}"
                        class="pt-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" id="reset-batas-laporan-btn"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                            Reset ke Default (Sabtu)
                        </button>
                    </form>
                </div>

                <div id="reset-batas-laporan-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center px-4 py-6">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-reset-overlay></div>
                    <div class="relative w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-xl ring-1 ring-slate-900/5">
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 shrink-0">
                                    <i class="text-xl text-red-600 ri-delete-bin-line"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-slate-900">Reset Pengaturan?</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        Hapus batas laporan yang tersimpan. Sistem kembali memakai default:
                                        hari Sabtu.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 px-5 py-4 border-t border-slate-100 bg-slate-50">
                            <button type="button" data-reset-cancel
                                class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="button" data-reset-confirm
                                class="inline-flex flex-1 items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                                Ya, Reset
                            </button>
                        </div>
                    </div>
                </div>

                @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('reset-batas-laporan-form');
                            const openBtn = document.getElementById('reset-batas-laporan-btn');
                            const modal = document.getElementById('reset-batas-laporan-modal');
                            if (!form || !openBtn || !modal) return;

                            const overlay = modal.querySelector('[data-reset-overlay]');
                            const cancelBtn = modal.querySelector('[data-reset-cancel]');
                            const confirmBtn = modal.querySelector('[data-reset-confirm]');

                            const close = () => {
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                            };
                            const open = () => {
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                            };

                            openBtn.addEventListener('click', open);
                            cancelBtn?.addEventListener('click', close);
                            overlay?.addEventListener('click', close);
                            confirmBtn?.addEventListener('click', () => form.submit());
                            document.addEventListener('keydown', (e) => {
                                if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
                            });
                        });
                    </script>
                @endpush
            @endif
        </section>

        <section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Pengaturan Rekap Persentase</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Tentukan hari rekap persentase dapat dilihat oleh Direksi. Default: Sabtu dan Minggu.
                    Rekap bulan yang sudah selesai tetap dapat dilihat kapan saja.
                </p>
            </div>

            @php
                $oldRecapDays = old('visible_days', $recapVisibleDays);
            @endphp

            <form method="POST" action="{{ route('admin.cp.settings.recap.update') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-2 sm:grid-cols-4">
                    @foreach ($recapDays as $value => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="visible_days[]" value="{{ $value }}"
                                @checked(in_array($value, array_map('intval', (array) $oldRecapDays), true))
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('visible_days')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('visible_days.*')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan Hari Rekap
                    </button>
                </div>
            </form>

            @if ($recapSetting)
                <div class="mt-5 space-y-1 border-t border-gray-100 pt-4 text-sm text-gray-600">
                    <p>Hari aktif: <span class="font-semibold text-gray-800">{{ $recapSetting->daysLabel() }}</span></p>
                    <p>Diubah oleh: <span class="font-semibold text-gray-800">{{ $recapSetting->updater->nama_lengkap ?? '-' }}</span></p>
                    <p>Terakhir update: <span class="font-semibold text-gray-800">{{ $recapSetting->updated_at?->format('d M Y H:i') }}</span></p>
                    <form method="POST" action="{{ route('admin.cp.settings.recap.reset') }}" class="pt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                            Reset ke Default (Sabtu & Minggu)
                        </button>
                    </form>
                </div>
            @endif
        </section>
    </div>
</x-admin-layout>
