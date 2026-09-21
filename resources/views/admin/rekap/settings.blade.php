<x-admin-layout :fullWidth="true">
    @section('title', 'Pengaturan Rekap')

    <div class="w-full px-2 py-6 mx-auto space-y-4 max-w-screen-xl sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Pengaturan Batas Waktu Rekap</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Atur tanggal (1–31) + jam. Berlaku tiap bulan: setelah lewat, pengajuan rekap Leader/SPVW dikunci sampai bulan berikutnya. Belum diisi = tetap terbuka.
                    </p>
                </div>
                <a href="{{ route('admin.index') }}" class="inline-flex h-10 items-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">Kembali</a>
            </div>
        </section>

        <section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
            @if (!$setting)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                    Batas waktu belum diatur. Semua pengajuan rekap masih terbuka.
                </div>
            @endif

            @php
                $oldDay = old('due_day', $setting?->due_date?->day);
                $oldTime = old('due_time', $setting?->due_date?->format('H:i') ?? '00:00');
            @endphp

            <form method="POST" action="{{ route('admin.rekap.settings.update') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="due_day" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tanggal (tiap bulan)</label>
                        <select
                            id="due_day"
                            name="due_day"
                            class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none"
                            required
                        >
                            <option value="">Pilih tanggal</option>
                            @for ($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" @selected((string) $oldDay === (string) $d)>{{ $d }}</option>
                            @endfor
                        </select>
                        @error('due_day')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_time" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Jam</label>
                        <input
                            type="time"
                            id="due_time"
                            name="due_time"
                            value="{{ $oldTime }}"
                            class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none"
                            required
                        >
                        @error('due_time')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="text-xs text-gray-500">
                    Contoh: tanggal 10 jam 00:00 → mulai tgl 10 pukul 00:00 rekap dikunci sampai ganti bulan.
                </p>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan Batas Waktu
                    </button>
                </div>
            </form>

            @if ($setting)
                <div class="mt-5 border-t border-gray-100 pt-4 text-sm text-gray-600 space-y-1">
                    <p>Batas waktu aktif: <span class="font-semibold text-gray-800">{{ $setting->label() }}</span></p>
                    <p>Batas bulan ini: <span class="font-semibold text-gray-800">{{ $setting->deadlineFor()->format('d M Y H:i') }}</span></p>
                    <p>Status: <span class="font-semibold text-gray-800">{{ $setting->isLockedAt() ? 'Terkunci' : 'Terbuka' }}</span></p>
                    <p>Diubah oleh: <span class="font-semibold text-gray-800">{{ $setting->updater->nama_lengkap ?? '-' }}</span></p>
                    <p>Terakhir update: <span class="font-semibold text-gray-800">{{ $setting->updated_at?->format('d M Y H:i') }}</span></p>

                    <form id="reset-batas-waktu-form" method="POST" action="{{ route('admin.rekap.settings.reset') }}" class="pt-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" id="reset-batas-waktu-btn" class="inline-flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                            Reset Batas Waktu
                        </button>
                    </form>
                </div>

                <div id="reset-batas-waktu-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center px-4 py-6">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-reset-overlay></div>
                    <div class="relative w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-900/5">
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                                    <i class="ri-delete-bin-line text-xl text-red-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-semibold text-slate-900">Reset Batas Waktu?</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        Hapus batas waktu rekap. Semua pengajuan Leader/SPVW akan terbuka kembali.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4">
                            <button type="button" data-reset-cancel class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="button" data-reset-confirm class="inline-flex flex-1 items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                                Ya, Reset
                            </button>
                        </div>
                    </div>
                </div>

                @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('reset-batas-waktu-form');
                            const openBtn = document.getElementById('reset-batas-waktu-btn');
                            const modal = document.getElementById('reset-batas-waktu-modal');
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
    </div>
</x-admin-layout>
