<x-mitra-layout title="Profil Saya">
    @php
        $namaLengkap = $dataUser->nama_lengkap ?? $dataUser->name;
        $namaTampil = ucwords(strtolower($namaLengkap ?? '-'));
        $workspace = $dataUser->kerjasama?->client?->name;
        $jabatan = $dataUser->divisi?->jabatan?->name_jabatan ?? $dataUser->jabatan?->name_jabatan;
        $inisial = strtoupper(mb_substr(trim($namaLengkap ?? 'M'), 0, 1));

        $nik = '-';
        if (! empty($dataUser->nik)) {
            try {
                $nik = \Illuminate\Support\Facades\Crypt::decryptString($dataUser->nik);
            } catch (\Throwable $error) {
                $nik = '-';
            }
        }

        $foto = asset('logo/person.png');
        if (! empty($dataUser->image) && $dataUser->image !== 'no-image.jpg') {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('images/' . $dataUser->image)) {
                $foto = asset('storage/images/' . $dataUser->image);
            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists('user/' . $dataUser->image)) {
                $foto = asset('storage/user/' . $dataUser->image);
            }
        }

        $kontrakTampil = $kontrak && $kontrak->send_to_atasan == 0 && $kontrak->ttd && $kontrak->ttd_atasan;
        $detail = [
            ['label' => 'Nama pengguna', 'value' => $dataUser->name],
            ['label' => 'Email', 'value' => $dataUser->email],
            ['label' => 'NIK', 'value' => $nik],
            ['label' => 'Nomor HP', 'value' => $dataUser->no_hp],
            ['label' => 'Jabatan', 'value' => $jabatan],
            ['label' => 'Penempatan', 'value' => $workspace],
        ];
    @endphp

    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-medium tracking-wide uppercase text-slate-500">
                {{ $workspace ?: 'Portal Mitra' }}
            </p>
            <h2 class="mt-1.5 text-2xl font-bold tracking-tight text-slate-900">Profil Saya</h2>
            <p class="mt-1 text-sm text-slate-600">
                Identitas akun yang dipakai untuk mengelola operasional mitra.
            </p>
        </div>
        <a href="{{ route('profile.edit', $dataUser->id) }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white transition rounded-xl bg-[var(--mitra-accent)] hover:bg-[var(--mitra-accent-strong)]">
            <i class="ri-edit-2-line"></i>
            Edit Profil
        </a>
    </section>

    <section class="grid gap-4 mt-4 lg:grid-cols-12">
        <article class="p-6 bg-white border rounded-2xl border-slate-100 lg:col-span-4">
            <div class="flex flex-col items-center text-center">
                <span class="relative inline-flex items-center justify-center overflow-hidden bg-slate-100 rounded-full w-24 h-24">
                    @if($foto !== asset('logo/person.png'))
                        <img src="{{ $foto }}" alt="Foto profil {{ $namaTampil }}" class="object-cover w-full h-full" loading="lazy" decoding="async">
                    @else
                        <span class="text-2xl font-bold text-slate-500">{{ $inisial }}</span>
                    @endif
                </span>

                <h3 class="mt-4 text-lg font-bold tracking-tight text-slate-900">{{ $namaTampil }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $jabatan ?: 'Jabatan belum diatur' }}</p>

                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 mt-3 text-[11px] font-semibold rounded-full bg-[var(--mitra-accent-soft)] text-[var(--mitra-accent-strong)]">
                    <i class="ri-shield-check-line"></i>
                    Akun Mitra
                </span>
            </div>

            <div class="pt-5 mt-5 border-t" style="border-color: var(--mitra-border);">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Workspace</p>
                <p class="mt-1.5 text-sm font-semibold text-slate-900">{{ $workspace ?: 'Belum terhubung' }}</p>
                <p class="mt-1 text-xs text-slate-500">Penempatan aktif saat ini.</p>
            </div>
        </article>

        <article class="p-6 bg-white border rounded-2xl border-slate-100 lg:col-span-8">
            <h3 class="text-base font-semibold text-slate-900">Informasi Akun</h3>
            <p class="mt-1 text-xs text-slate-500">Data ini dipakai pada seluruh modul kehadiran dan laporan.</p>

            <dl class="mt-4 divide-y divide-slate-100">
                @foreach($detail as $item)
                    <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
                        <dt class="text-sm text-slate-500">{{ $item['label'] }}</dt>
                        <dd class="text-sm font-medium break-words sm:text-right text-slate-900">
                            {{ filled($item['value']) ? $item['value'] : '-' }}
                        </dd>
                    </div>
                @endforeach
            </dl>

            <div class="flex items-start gap-3 p-4 mt-5 rounded-xl bg-slate-50">
                <i class="text-base ri-information-line text-slate-400"></i>
                <p class="text-xs leading-5 text-slate-600">
                    Perubahan nama, email, NIK, dan nomor HP dilakukan lewat tombol Edit Profil. Hubungi admin
                    kepegawaian bila jabatan atau penempatan Anda perlu diperbarui.
                </p>
            </div>
        </article>

        @if($kontrakTampil)
            <article class="p-6 bg-white border rounded-2xl border-slate-100 lg:col-span-12">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center flex-shrink-0 rounded-xl w-10 h-10 bg-emerald-50 text-emerald-700">
                            <i class="text-lg ri-file-paper-2-line"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Kontrak Kerja</h3>
                            <p class="mt-1 text-xs text-slate-500">Kontrak aktif dan sudah ditandatangani kedua pihak.</p>
                        </div>
                    </div>
                    <a href="{{ route('form-kontrak-preview', ['token' => \Illuminate\Support\Facades\Crypt::encryptString($kontrak->id)]) }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold transition bg-white border rounded-xl border-slate-200 text-slate-700 hover:bg-slate-50">
                        <i class="ri-external-link-line"></i>
                        Lihat Kontrak
                    </a>
                </div>
            </article>
        @endif
    </section>

    <div class="mt-4">
        <a href="{{ route('dashboard.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold mitra-link-accent">
            <i class="ri-arrow-left-line"></i>
            Kembali ke dashboard
        </a>
    </div>
</x-mitra-layout>
