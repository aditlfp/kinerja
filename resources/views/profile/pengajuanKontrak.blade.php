<x-app-layout>
    <x-main-div>
        <div class="max-w-3xl px-4 py-10 mx-auto">

            <div class="bg-white p-3 rounded-lg mb-2">
                {{-- Eyebrow + document code, like a real internal form header --}}
                <div class="flex items-baseline justify-between mb-1">
                    <span class="text-xs font-semibold tracking-widest text-indigo-600 uppercase">Data Kepegawaian</span>
                    <span class="font-mono text-xs text-slate-400">Form: HRD - PKWT</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Pengajuan Kontrak Kerja
                </h1>
                <p class="mt-1 mb-6 text-sm text-slate-500">
                    Lengkapi data diri sesuai KTP. Data ini akan digunakan untuk penyusunan dokumen kontrak.
                </p>
            </div>

            <form method="POST" action="{{ route('form-kontrak-kirimPengajuan') }}">
                @csrf
                @method('POST')

                <div class="overflow-hidden bg-white border rounded-xl border-slate-200">

                    <div class="p-6 space-y-6 sm:p-8">

                        {{-- Nama Lengkap — readonly, pulled from account --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-slate-700 required">Nama Lengkap</label>
                            <input type="text" value="{{ Auth::user()->nama_lengkap }}" readonly
                                class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-200 bg-slate-50 text-slate-600 cursor-not-allowed" />
                        </div>

                        {{-- Tempat & Tanggal Lahir — grouped, they describe the same fact --}}
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-slate-700 required">Tempat Lahir</label>
                                <input type="text" name="tempat_lhr" value="{{ old('tempat_lhr') }}"
                                    placeholder="Contoh: Madiun"
                                    class="w-full px-3.5 py-2.5 text-sm rounded-lg border bg-white text-slate-900 placeholder:text-slate-400 transition-colors focus:outline-hidden focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500
                                    @error('tempat_lhr') border-red-400 focus:ring-red-100 focus:border-red-500 @else border-slate-300 @enderror" />
                                @error('tempat_lhr')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-slate-700 required">Tanggal Lahir</label>
                                <input type="date" name="tgl_lhr" value="{{ old('tgl_lhr') }}"
                                    max="{{ Carbon\Carbon::now()->subYears(17)->format('Y-m-d') }}"
                                    class="w-full px-3.5 py-2.5 text-sm rounded-lg border bg-white text-slate-900 transition-colors focus:outline-hidden focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500
                                    @error('tgl_lhr') border-red-400 focus:ring-red-100 focus:border-red-500 @else border-slate-300 @enderror" />
                                @error('tgl_lhr')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- NIK — tabular numerals so a 16-digit number actually lines up and is easy to check --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-slate-700 required">NIK</label>
                                <span class="text-[11px] text-slate-400">16 digit, sesuai KTP</span>
                            </div>
                            <input type="text" name="nik" value="{{ old('nik') }}" inputmode="numeric"
                                maxlength="16" placeholder="0000 0000 0000 0000"
                                style="font-variant-numeric: tabular-nums; letter-spacing: 0.04em;"
                                class="w-full px-3.5 py-2.5 text-sm font-mono rounded-lg border bg-white text-slate-900 placeholder:text-slate-400 placeholder:font-sans placeholder:tracking-normal transition-colors focus:outline-hidden focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500
                                @error('nik') border-red-400 focus:ring-red-100 focus:border-red-500 @else border-slate-300 @enderror" />
                            @error('nik')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-slate-700 required">Alamat</label>
                            <textarea name="alamat_pk_kda" rows="3" placeholder="Alamat tempat tinggal sekarang"
                                class="w-full px-3.5 py-2.5 text-sm rounded-lg border bg-white text-slate-900 placeholder:text-slate-400 transition-colors resize-none focus:outline-hidden focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500
                                @error('alamat_pk_kda') border-red-400 focus:ring-red-100 focus:border-red-500 @else border-slate-300 @enderror">{{ old('alamat_pk_kda') }}</textarea>
                            @error('alamat_pk_kda')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="flex items-center justify-between gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50 sm:px-8">
                        <p class="hidden text-xs text-slate-400 sm:block">Pastikan seluruh data sesuai KTP sebelum
                            disimpan.</p>
                        <div class="flex items-center w-full gap-2 sm:w-auto">
                            <a href="{{ route('dashboard.index') }}"
                                class="flex-1 px-4 py-2 text-sm font-medium text-center transition-colors border rounded-lg sm:flex-none border-slate-300 text-slate-700 hover:bg-slate-100">
                                Batal
                            </a>
                            <button type="submit"
                                class="flex-1 px-4 py-2 text-sm font-medium text-center text-white transition-colors rounded-lg sm:flex-none bg-emerald-600 hover:bg-emerald-700">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </x-main-div>
</x-app-layout>
