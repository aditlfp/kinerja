<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME', 'Kinerja SAC-PONOROGO') }}</title>
    <link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        *,
        body,
        html {
            overflow-x: hidden;
        }

        body {
            background: radial-gradient(1200px 600px at 100% -10%, #FBF3EA 0%, rgba(251, 243, 234, 0) 60%), #FFFFFF;
        }

        /* ---------- Step rail ---------- */
        .step-rail {
            --dot-size: 44px;
        }

        .step-link {
            display: flex;
            gap: 0.85rem;
            width: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            text-align: left;
            cursor: pointer;
            font: inherit;
            color: inherit;
            text-decoration: none;
        }

        .step-dot {
            width: var(--dot-size, 44px);
            height: var(--dot-size, 44px);
            flex: 0 0 var(--dot-size, 44px);
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #64748B;
            background: #FFFFFF;
            border: 1.5px solid #CBD5E1;
            transition: all 0.25s ease;
        }

        .step-line {
            width: 4px;
            flex: 1 1 auto;
            min-height: 34px;
            background-image: radial-gradient(circle, #CBD5E1 1.2px, transparent 1.3px);
            background-size: 4px 8px;
            background-position: center;
            background-repeat: repeat-y;
        }

        .step-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #475569;
            transition: color 0.25s ease;
        }

        .step-label {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #94A3B8;
        }

        .step.is-active .step-dot {
            background: #0F172A;
            border-color: #0F172A;
            color: #FFFFFF;
        }

        .step.is-active .step-title {
            color: #0F172A;
        }

        .step.is-done .step-dot {
            background: #0F172A;
            border-color: #0F172A;
            color: #FFFFFF;
        }

        .step.is-done .step-line {
            background-image: radial-gradient(circle, #0F172A 1.4px, transparent 1.5px);
        }

        .step.is-done .step-title {
            color: #0F172A;
        }

        /* ---------- Form fields ---------- */
        input.field,
        select.field,
        textarea.field {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            padding: 0.7rem 0.9rem;
            font-size: 0.875rem;
            color: #0F172A;
            box-shadow: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        input.field::placeholder {
            color: #94A3B8;
        }

        input.field:focus,
        select.field:focus,
        textarea.field:focus {
            outline: none;
            border-color: #0F172A;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
        }

        select.field {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2364748B'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.1rem;
            padding-right: 2.25rem;
        }

        .field-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        /* ---------- Upload tiles ---------- */
        .upload-tile {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            width: 100%;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            background: #FCEFE4;
            color: #1F2937;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: background 0.2s ease;
        }

        .upload-tile:hover {
            background: #FAE5D4;
        }

        .upload-tile.has-file {
            background: #EFF3DC;
            color: #3F4A12;
        }

        /* Tom Select menyalin class select ke wrapper-nya; matikan sisa gaya field & tanda wajib di wrapper */
        .ts-wrapper.field::after,
        .ts-wrapper.required::after {
            content: none;
        }

        .ts-wrapper.field {
            padding: 0;
            border: 0;
            border-radius: 0;
        }

        /* Tom Select: samakan tampilannya dengan field lain di halaman ini */
        .ts-wrapper.single .ts-control {
            border-radius: 12px;
            border-color: #E2E8F0;
            padding: 0.35rem 0.9rem;
            font-size: 0.875rem;
        }

        .ts-wrapper.focus .ts-control {
            border-color: #0F172A;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
        }

        .ts-dropdown {
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 24px 60px -38px rgba(15, 23, 42, 0.55);
        }

        /* Tom Select: penanda error memakai warna yang sama dengan field lain */
        .ts-wrapper.ts-invalid .ts-control {
            border-color: #EF4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }

        .upload-tile .file-name {
            font-weight: 600;
            font-size: 0.82rem;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 640px) {
            .step-rail {
                --dot-size: 38px;
            }

            .step-dot {
                font-size: 12px;
            }
        }

        /* ---------- Responsif ---------- */
        @media (max-width: 1023.98px) {
            .step-rail ol {
                position: relative;
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.5rem;
            }

            /* Garis titik-titik horizontal yang menghubungkan bulatan step */
            .step-rail ol::before {
                content: "";
                position: absolute;
                top: calc(var(--dot-size) / 2 - 2px);
                left: 16.6667%;
                right: 16.6667%;
                height: 4px;
                background-image: radial-gradient(circle, #CBD5E1 1.2px, transparent 1.3px);
                background-size: 8px 4px;
                background-repeat: repeat-x;
                background-position: center;
                pointer-events: none;
            }

            .step-link {
                position: relative;
                z-index: 1;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                text-align: center;
            }

            .step-link > span:first-child {
                flex-direction: row;
            }

            .step-line {
                display: none;
            }

            .step-link > span:last-child {
                padding: 0;
            }

            .step-label {
                display: none;
            }

            .step-title {
                font-size: 0.78rem;
                line-height: 1.25;
            }

            .step-done {
                justify-content: center;
                font-size: 10px;
            }
        }

        /* Cegah iOS auto-zoom saat field difokus (font < 16px memicu zoom di perangkat sentuh) */
        @media (hover: none) and (pointer: coarse) {
            input.field,
            select.field,
            textarea.field,
            .ts-wrapper.single .ts-control {
                font-size: 16px;
            }
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-900">
    <div class="min-h-screen">
        @php
            // Bila validasi server gagal, buka langkah yang memuat error pertama.
            $stepFields = [
                1 => ['nama_lengkap', 'email', 'no_hp', 'tpt', 'tgl', 'alamat', 'nik', 'kk'],
                2 => ['client_id', 'devisi_id', 'password'],
                3 => ['ktp', 'image', 'otp'],
            ];
            $activeStep = 1;
            foreach ($stepFields as $step => $keys) {
                if ($errors->hasAny($keys)) {
                    $activeStep = $step;
                    break;
                }
            }
        @endphp

        <main class="w-full max-w-6xl px-4 py-6 mx-auto sm:px-6 sm:py-8 lg:py-14">
            <div class="grid items-start gap-6 lg:grid-cols-[248px_minmax(0,1fr)] lg:gap-12">

                {{-- ================= STEP RAIL ================= --}}
                <aside class="step-rail lg:sticky lg:top-10">
                    <div class="p-4 bg-white rounded-2xl shadow-[0_24px_60px_-38px_rgba(15,23,42,0.55)] ring-1 ring-slate-900/5 sm:p-6">
                        <ol class="m-0 list-none">
                            <li class="step {{ $activeStep === 1 ? 'is-active' : '' }}" data-target="step-1">
                                <button type="button" class="step-link" data-go="1">
                                    <span class="flex flex-col items-center">
                                        <span class="step-dot">01</span>
                                        <span class="step-line"></span>
                                    </span>
                                    <span class="pt-1 pb-8">
                                        <span class="block step-label">Step 01</span>
                                        <span class="block mt-1 step-title">Data Pribadi</span>
                                        <span class="step-done hidden items-center gap-1 mt-1 text-[12px] font-semibold text-[#8C9A2E]">
                                            Completed <i class="ri-check-line"></i>
                                        </span>
                                    </span>
                                </button>
                            </li>
                            <li class="step" data-target="step-2">
                                <button type="button" class="step-link" data-go="2">
                                    <span class="flex flex-col items-center">
                                        <span class="step-dot">02</span>
                                        <span class="step-line"></span>
                                    </span>
                                    <span class="pt-1 pb-8">
                                        <span class="block step-label">Step 02</span>
                                        <span class="block mt-1 step-title">Unit Kerja &amp; Akun</span>
                                        <span class="step-done hidden items-center gap-1 mt-1 text-[12px] font-semibold text-[#8C9A2E]">
                                            Completed <i class="ri-check-line"></i>
                                        </span>
                                    </span>
                                </button>
                            </li>
                            <li class="step" data-target="step-3">
                                <button type="button" class="step-link" data-go="3">
                                    <span class="flex flex-col items-center">
                                        <span class="step-dot">03</span>
                                    </span>
                                    <span class="pt-1">
                                        <span class="block step-label">Step 03</span>
                                        <span class="block mt-1 step-title">Berkas &amp; Verifikasi</span>
                                        <span class="step-done hidden items-center gap-1 mt-1 text-[12px] font-semibold text-[#8C9A2E]">
                                            Completed <i class="ri-check-line"></i>
                                        </span>
                                    </span>
                                </button>
                            </li>
                        </ol>
                    </div>
                </aside>

                {{-- ================= CONTENT ================= --}}
                <div>
                    <header class="mb-7">
                        <p class="m-0 text-[11px] font-semibold tracking-[0.2em] text-slate-400 uppercase">Formulir Pendaftaran</p>
                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">Daftar Sebagai Karyawan Baru</h1>
                        <p class="max-w-xl mt-3 text-sm leading-relaxed text-slate-500">
                            Lengkapi data di bawah ini untuk mendaftar di PT. Surya Amanah Cendikia.
                            Pastikan semua data sesuai dengan dokumen resmi Anda.
                        </p>
                    </header>

                    @if ($errors->any())
                        <div class="p-4 mb-6 text-sm border rounded-xl border-red-200 bg-red-50 text-red-700">
                            <p class="mb-1 font-semibold">Terdapat data yang perlu diperbaiki:</p>
                            <ul class="pl-5 list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('addKaryawanStore') }}" id="form"
                        enctype="multipart/form-data" novalidate data-active-step="{{ $activeStep }}">
                        @csrf

                        {{-- ---------- STEP 01 ---------- --}}
                        <section id="step-1" data-step="1"
                            class="{{ $activeStep === 1 ? '' : 'hidden ' }}scroll-mt-8 rounded-2xl bg-white p-4 shadow-[0_24px_60px_-52px_rgba(15,23,42,0.6)] ring-1 ring-slate-900/5 sm:p-7">
                            <div class="flex items-start gap-3 mb-6">
                                <span class="flex items-center justify-center text-xs font-bold text-white bg-slate-900 rounded-full w-7 h-7 shrink-0">01</span>
                                <div>
                                    <h2 class="m-0 text-lg font-bold text-slate-900">Data Pribadi</h2>
                                    <p class="m-0 mt-1 text-sm text-slate-500">Identitas sesuai KTP dan kontak yang aktif.</p>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                                <div class="sm:col-span-2">
                                    <label for="nama_lengkap" class="field-label required">Nama lengkap</label>
                                    <input id="nama_lengkap" class="field" type="text" name="nama_lengkap"
                                        value="{{ old('nama_lengkap') }}" required autofocus autocomplete="name"
                                        placeholder="Masukkan nama lengkap user...">
                                    <x-input-error :messages="$errors->get('nama_lengkap')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="email" class="field-label required">Email aktif</label>
                                    <input id="email" class="field" type="email" name="email"
                                        value="{{ old('email') }}" required autocomplete="email"
                                        placeholder="Masukkan email user...">
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="phone" class="field-label required">No. HP aktif</label>
                                    <input id="phone" class="field" type="tel" name="no_hp" required
                                        value="{{ old('no_hp') }}" placeholder="No hp aktif..." maxlength="14"
                                        pattern="[0-9]{10,15}" autocomplete="tel">
                                    <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="field-label required">Tempat tanggal lahir</label>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <input id="tpt" class="field" type="text" name="tpt" required
                                            value="{{ old('tpt') }}" placeholder="Tempat lahir..."
                                            autocomplete="address-level2">
                                        <input id="tgl" class="field" type="date" name="tgl" required
                                            value="{{ old('tgl') }}" autocomplete="bday">
                                    </div>
                                    <x-input-error :messages="array_merge($errors->get('tpt'), $errors->get('tgl'))" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="alamat" class="field-label required">Alamat tinggal (sekarang)</label>
                                    <input id="alamat" class="field" type="text" name="alamat" required
                                        value="{{ old('alamat') }}" placeholder="Alamat..." autocomplete="street-address">
                                    <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="NIK" class="field-label required">NIK</label>
                                    <input id="NIK" class="field" type="text" name="nik" required
                                        value="{{ old('nik') }}" placeholder="Nik..." maxlength="16"
                                        pattern="[0-9]*" autocomplete="off">
                                    <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="kk" class="field-label required">No. KK</label>
                                    <input id="kk" class="field" type="text" name="kk" required
                                        value="{{ old('kk') }}" placeholder="No. KK..." maxlength="16"
                                        pattern="[0-9]*" autocomplete="off">
                                    <x-input-error :messages="$errors->get('kk')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex flex-col-reverse gap-2 mt-6 sm:flex-row sm:flex-wrap sm:justify-end sm:mt-8">
                                <a href="/"
                                    class="w-full py-2.5 text-sm font-semibold text-center text-slate-600 transition-colors bg-white border rounded-xl border-slate-200 hover:bg-slate-50 sm:w-auto sm:px-5">
                                    Batal
                                </a>
                                <button type="button" data-next="1"
                                    class="inline-flex items-center justify-center w-full gap-1 py-2.5 text-sm font-semibold text-white transition-colors rounded-xl bg-slate-900 hover:bg-slate-800 sm:w-auto sm:px-6">
                                    Lanjutkan <i class="ri-arrow-right-line"></i>
                                </button>
                            </div>
                        </section>

                        {{-- ---------- STEP 02 ---------- --}}
                        <section id="step-2" data-step="2"
                            class="mt-6 {{ $activeStep === 2 ? '' : 'hidden ' }}scroll-mt-8 rounded-2xl bg-white p-4 shadow-[0_24px_60px_-52px_rgba(15,23,42,0.6)] ring-1 ring-slate-900/5 sm:p-7">
                            <div class="flex items-start gap-3 mb-6">
                                <span class="flex items-center justify-center text-xs font-bold text-white bg-slate-900 rounded-full w-7 h-7 shrink-0">02</span>
                                <div>
                                    <h2 class="m-0 text-lg font-bold text-slate-900">Unit Kerja &amp; Akun</h2>
                                    <p class="m-0 mt-1 text-sm text-slate-500">Penempatan Anda serta kredensial untuk masuk aplikasi.</p>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                                <div>
                                    <label for="client" class="field-label required">Unit kerja</label>
                                    <select name="client_id" id="client" required class="field">
                                        <option value="" selected disabled>~ Pilih Unit Kerja ~</option>
                                        @foreach ($kerjasama as $i)
                                            <option value="{{ $i->client_id }}">
                                                {{ ucwords(strtolower($i->client->name)) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="devisi" class="field-label required">Bagian</label>
                                    <select name="devisi_id" id="devisi" required class="field">
                                        <option value="" selected disabled>~ Pilih Bagian ~</option>
                                        @foreach ($devisi as $i)
                                            <option value="{{ $i->id }}">
                                                {{ ucwords(strtolower($i->jabatan->name_jabatan)) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('devisi_id')" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="password" class="field-label required">Password aplikasi</label>
                                    <input id="password" class="field" type="password" name="password" required
                                        autocomplete="new-password" placeholder="Buat password anda...">
                                    <p class="m-0 mt-2 text-xs text-slate-400">Gunakan kombinasi huruf dan angka yang mudah Anda ingat.</p>
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex flex-col-reverse gap-2 mt-6 sm:flex-row sm:flex-wrap sm:justify-end sm:mt-8">
                                <button type="button" data-prev="2"
                                    class="w-full py-2.5 text-sm font-semibold text-center text-slate-600 transition-colors bg-white border rounded-xl border-slate-200 hover:bg-slate-50 sm:w-auto sm:px-5">
                                    Kembali
                                </button>
                                <button type="button" data-next="2"
                                    class="inline-flex items-center justify-center w-full gap-1 py-2.5 text-sm font-semibold text-white transition-colors rounded-xl bg-slate-900 hover:bg-slate-800 sm:w-auto sm:px-6">
                                    Lanjutkan <i class="ri-arrow-right-line"></i>
                                </button>
                            </div>
                        </section>

                        {{-- ---------- STEP 03 ---------- --}}
                        <section id="step-3" data-step="3"
                            class="mt-6 {{ $activeStep === 3 ? '' : 'hidden ' }}scroll-mt-8 rounded-2xl bg-white p-4 shadow-[0_24px_60px_-52px_rgba(15,23,42,0.6)] ring-1 ring-slate-900/5 sm:p-7">
                            <div class="flex items-start gap-3 mb-6">
                                <span class="flex items-center justify-center text-xs font-bold text-white bg-slate-900 rounded-full w-7 h-7 shrink-0">03</span>
                                <div>
                                    <h2 class="m-0 text-lg font-bold text-slate-900">Berkas &amp; Verifikasi</h2>
                                    <p class="m-0 mt-1 text-sm text-slate-500">Unggah dokumen Anda lalu verifikasi email dengan kode OTP.</p>
                                </div>
                            </div>

                            <div class="p-4 border-2 border-dashed rounded-2xl border-slate-200 sm:p-8">
                                <div class="text-center">
                                    <h3 class="m-0 text-lg font-extrabold text-slate-900 sm:text-xl">Unggah Dokumen Anda</h3>
                                    <p class="m-0 mt-1 text-sm text-slate-400">Foto yang jelas dan terbaca akan mempercepat proses verifikasi</p>
                                </div>

                                <div class="mt-6 space-y-3">
                                    {{-- Foto KTP --}}
                                    <div>
                                        <label for="img2" class="upload-tile" data-upload="ktp">
                                            <i class="text-lg ri-image-add-line"></i>
                                            <span class="file-label">Upload Foto KTP (depan)</span>
                                            <input id="img2" class="sr-only" type="file" name="ktp"
                                                accept="image/*" required>
                                        </label>
                                        <div class="hidden mt-3 preview2">
                                            <img class="w-40 rounded-xl img2 ring-1 ring-slate-200" src=""
                                                alt="Preview KTP">
                                        </div>
                                        <x-input-error :messages="$errors->get('ktp')" class="mt-2" />
                                    </div>

                                    {{-- Foto Profil --}}
                                    <div>
                                        <label for="img" class="upload-tile" data-upload="profile">
                                            <i class="text-lg ri-camera-lens-line"></i>
                                            <span class="file-label">Upload Foto Profil</span>
                                            <input id="img" class="sr-only" type="file" name="image"
                                                accept="image/*" required>
                                        </label>
                                        <div class="hidden mt-3 preview">
                                            <img class="w-40 rounded-xl img1 ring-1 ring-slate-200" src=""
                                                alt="Preview foto profil">
                                        </div>
                                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div id="otp-section" class="mt-6">
                                <label for="otp" class="field-label required">Kode OTP</label>
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <input id="otp" class="field" type="text" name="otp" maxlength="6"
                                        placeholder="Masukkan OTP..." required>
                                    <button type="button" id="send-otp" data-jenis="send"
                                        class="px-4 py-2.5 text-sm font-semibold text-white transition-colors bg-slate-900 rounded-xl hover:bg-slate-800 whitespace-nowrap sm:w-auto">
                                        Kirim OTP
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                                <p id="otp-timer" class="hidden mt-2 text-sm text-gray-600">
                                    Kirim ulang OTP dalam <span id="otp-countdown">90</span> detik...
                                </p>
                            </div>

                            <div class="flex flex-col-reverse gap-2 mt-6 sm:flex-row sm:flex-wrap sm:justify-end sm:mt-8">
                                <button type="button" data-prev="3"
                                    class="w-full py-2.5 text-sm font-semibold text-center text-slate-600 transition-colors bg-white border rounded-xl border-slate-200 hover:bg-slate-50 sm:w-auto sm:px-5">
                                    Kembali
                                </button>
                                <button type="submit"
                                    class="w-full py-2.5 text-sm font-semibold text-white transition-colors rounded-xl bg-slate-900 hover:bg-slate-800 sm:w-auto sm:px-6">
                                    Simpan &amp; Kirim
                                </button>
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </main>

        <div id="toast"
            style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); max-width:calc(100vw - 2rem); width:max-content;
                        background:#f87171; color:white; padding:12px 20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.2); z-index:9999; font-weight:500; text-align: center;">
        </div>
    </div>

    <script>
        // ---------- File previews ----------
        const uploadPreviews = [{
                input: '#img',
                wrapper: '.preview',
                image: '.img1'
            },
            {
                input: '#img2',
                wrapper: '.preview2',
                image: '.img2'
            },
        ];

        uploadPreviews.forEach(({
            input,
            wrapper,
            image
        }) => {
            const el = document.querySelector(input);
            if (!el) return;

            el.addEventListener('change', () => {
                const tile = el.closest('.upload-tile');
                const label = tile ? tile.querySelector('.file-label') : null;
                const box = document.querySelector(wrapper);
                const img = document.querySelector(image);

                if (el.files && el.files[0] && img && box) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        img.src = e.target.result;
                        box.classList.remove('hidden');
                    };
                    reader.readAsDataURL(el.files[0]);

                    if (tile) tile.classList.add('has-file');
                    if (label) {
                        if (!label.dataset.original) label.dataset.original = label.textContent.trim();
                        label.textContent = el.files[0].name;
                        label.classList.add('file-name');
                    }
                } else {
                    if (box) box.classList.add('hidden');
                    if (tile) tile.classList.remove('has-file');
                    if (label && label.dataset.original) {
                        label.textContent = label.dataset.original;
                        label.classList.remove('file-name');
                    }
                }
            });
        });

        // ---------- Wizard langkah ----------
        const formEl = document.getElementById('form');
        const panels = Array.from(document.querySelectorAll('section[data-step]'));
        const railItems = Array.from(document.querySelectorAll('aside .step'));
        const lastStep = panels.length ? Math.max(...panels.map((p) => Number(p.dataset.step))) : 1;
        let currentStep = Number(formEl ? formEl.dataset.activeStep : 1) || 1;

        const stepPanel = (step) => panels.find((panel) => Number(panel.dataset.step) === Number(step));
        const stepFields = (panel) => Array.from(panel.querySelectorAll('input, select, textarea'));
        const firstInvalid = (panel) => stepFields(panel).find((el) => !el.checkValidity()) || null;
        const panelIsValid = (panel) => firstInvalid(panel) === null;

        function syncRail() {
            railItems.forEach((item) => {
                const step = Number(item.dataset.target.replace('step-', ''));
                const panel = stepPanel(step);
                const done = step !== currentStep && panel && panelIsValid(panel);

                item.classList.toggle('is-active', step === currentStep);
                item.classList.toggle('is-done', !!done);

                const badge = item.querySelector('.step-done');
                if (badge) {
                    badge.classList.toggle('hidden', !done);
                    badge.classList.toggle('flex', !!done);
                }
            });
        }

        // ---------- Select custom (Tom Select, pola yang sama dengan form lain) ----------
        const customSelects = {
            client: 'Pilih unit kerja',
            devisi: 'Pilih bagian',
        };

        function initCustomSelects() {
            if (typeof window.initTomUserSelect !== 'function') return;

            Object.keys(customSelects).forEach((id) => {
                const select = document.getElementById(id);
                if (!select || select.tomselect) return;

                const instance = window.initTomUserSelect(id, {
                    placeholder: customSelects[id],
                });

                if (instance) {
                    instance.on('change', () => instance.wrapper.classList.remove('ts-invalid'));
                }
            });
        }

        function showStep(step, options = {}) {
            const target = Math.min(Math.max(Number(step) || 1, 1), lastStep);

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', Number(panel.dataset.step) !== target);
            });

            // Select baru di-init saat panelnya sudah terlihat, supaya ukurannya benar.
            if (target >= 2) {
                initCustomSelects();
            }

            currentStep = target;
            syncRail();

            const active = stepPanel(target);
            if (!active) return;

            if (options.scroll !== false) {
                active.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            if (options.focus) {
                const firstField = active.querySelector('input:not(.sr-only), select');
                if (firstField) firstField.focus({ preventScroll: true });
            }
        }

        function validateStep(step) {
            const panel = stepPanel(step);
            if (!panel) return true;

            const invalid = firstInvalid(panel);
            if (!invalid) return true;

            // Select sudah diganti Tom Select: tandai wrapper-nya, bukan select asli yang tersembunyi.
            if (invalid.tagName === 'SELECT' && invalid.tomselect) {
                invalid.tomselect.wrapper.classList.add('ts-invalid');
                invalid.tomselect.focus();
                return false;
            }

            if (typeof invalid.reportValidity === 'function') invalid.reportValidity();
            invalid.focus({ preventScroll: true });
            return false;
        }

        document.querySelectorAll('[data-next]').forEach((button) => {
            button.addEventListener('click', () => {
                const step = Number(button.dataset.next);
                if (validateStep(step)) showStep(step + 1, { focus: true });
            });
        });

        document.querySelectorAll('[data-prev]').forEach((button) => {
            button.addEventListener('click', () => showStep(Number(button.dataset.prev) - 1, { focus: true }));
        });

        // Rail hanya boleh mundur bebas; maju kalau langkah sebelumnya sudah valid.
        railItems.forEach((item) => {
            const link = item.querySelector('.step-link');
            if (!link) return;

            link.addEventListener('click', () => {
                const step = Number(item.dataset.target.replace('step-', ''));

                if (step > currentStep) {
                    for (let from = currentStep; from < step; from++) {
                        if (!validateStep(from)) {
                            showStep(from);
                            return;
                        }
                    }
                }

                showStep(step);
            });
        });

        showStep(currentStep, { scroll: false });

        // Kalau halaman terbuka langsung di langkah 2/3 (mis. error validasi), init setelah app.js siap.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                if (currentStep >= 2) initCustomSelects();
            });
        } else if (currentStep >= 2) {
            initCustomSelects();
        }

        // ---------- OTP ----------
        $(document).ready(function() {
            let countdownInterval;
            const countdownDuration = 90; // seconds

            function showToast(message, type = 'error') {
                const colors = {
                    success: '#4ade80',
                    error: '#f87171'
                };

                $('#toast')
                    .css('background', colors[type] || colors.error)
                    .text(message)
                    .stop(true, true)
                    .fadeIn(300)
                    .delay(3000)
                    .fadeOut(300);
            }

            function sendOtpReg(email) {
                return $.post('/send-otp-reg', {
                    email: email,
                    _token: '{{ csrf_token() }}'
                });
            }

            function cekEmail(email, no_hp) {
                return $.get('/check-email', {
                    email: email,
                    no_hp: no_hp,
                    _token: '{{ csrf_token() }}'
                });
            }

            function startCountdown(duration, button, timerContainer) {
                let countdown = duration;
                $('#otp-countdown').text(countdown);
                timerContainer.removeClass('hidden');

                countdownInterval = setInterval(() => {
                    countdown--;
                    $('#otp-countdown').text(countdown);

                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        button.prop('disabled', false).text('Kirim Ulang OTP');
                        timerContainer.addClass('hidden');
                    }
                }, 1000);
            }

            $('#send-otp').on('click', function() {
                const email = $('#email').val().trim();
                const phone = $('#phone').val().trim();
                const button = $(this);
                const timerContainer = $('#otp-timer');

                if (!email) {
                    showToast('Masukkan email terlebih dahulu.', 'error');
                    return;
                }

                if (!phone) {
                    showToast('Masukkan no. telepon terlebih dahulu.', 'error');
                    return;
                }

                cekEmail(email, phone).then(function(response) {
                    if (response.email) {
                        showToast('Email sudah terdaftar.', 'error');
                        return;
                    }
                    if (response.phone) {
                        showToast('No. hp sudah terdaftar.', 'error');
                        return;
                    }

                    sendOtpReg(email).then(() => {
                        showToast('Kode OTP telah dikirim!', 'success');
                    });

                    button.prop('disabled', true).text('Tunggu...');
                    clearInterval(countdownInterval); // Reset previous timer if any
                    startCountdown(countdownDuration, button, timerContainer);
                }).fail(() => {
                    showToast('Terjadi kesalahan saat memeriksa email.', 'error');
                });
            });

            $('#form').on('submit', function(e) {
                e.preventDefault();

                const form = this;

                // Semua langkah harus lolos validasi sebelum OTP diverifikasi.
                for (let step = 1; step <= lastStep; step++) {
                    const panel = stepPanel(step);
                    if (panel && firstInvalid(panel)) {
                        showStep(step, { scroll: false });
                        validateStep(step);
                        return;
                    }
                }

                const email = $('#email').val();
                const otp = $('#otp').val();

                if (!email || !otp) {
                    showToast('Mohon isi email dan OTP terlebih dahulu.', 'error');
                    return;
                }

                $.ajax({
                    url: '/verify-otp-reg',
                    method: 'POST',
                    data: {
                        email: email,
                        otp: otp,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        showToast('✅ OTP valid. Mengirim data...', 'success');
                        form.submit(); // lanjutkan submit
                    },
                    error: function(xhr) {
                        const status = xhr.responseJSON?.status;

                        if (status === 'otp.invalid' || status === 'otp.expired') {
                            showToast('❌ OTP salah atau kadaluarsa! Mengirim ulang OTP...',
                                'error');

                            // Kirim ulang OTP otomatis
                            sendOtpReg(email);

                            // Reset timer
                            clearInterval(countdownInterval);
                            $('#send-otp').prop('disabled', true).text('Tunggu...');
                            $('#otp-timer').removeClass('hidden');
                            let countdown = 90;
                            $('#otp-countdown').text(countdown);

                            countdownInterval = setInterval(() => {
                                countdown--;
                                $('#otp-countdown').text(countdown);
                                if (countdown <= 0) {
                                    clearInterval(countdownInterval);
                                    $('#send-otp').prop('disabled', false).text(
                                        'Kirim Ulang OTP');
                                    $('#otp-timer').addClass('hidden');
                                }
                            }, 1000);
                        } else {
                            showToast('Terjadi kesalahan saat verifikasi.', 'error');
                        }
                    }
                });
            });
        })
    </script>
</body>

</html>
