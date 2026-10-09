<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <x-main-div>

        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between mb-4">
                <a href="{{ route('dashboard.index') }}"
                    class="bg-white inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 transition">
                    <i class="ri-arrow-left-line"></i>
                    Kembali
                </a>
            </div>

            {{-- Header --}}
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 mb-6">


                <div class="flex flex-col items-center text-center">

                    <div class="w-14 h-14 rounded-xl bg-sky-100 flex items-center justify-center mb-4">
                        <i class="ri-file-text-line text-2xl text-sky-600"></i>
                    </div>

                    <h1 class="text-2xl font-bold text-slate-800">
                        Form Persetujuan Kontrak
                    </h1>

                    <p class="text-slate-500 mt-2 max-w-lg">
                        Silakan baca dokumen kontrak terlebih dahulu, kemudian
                        berikan tanda tangan digital sebagai bentuk persetujuan.
                    </p>

                </div>

            </div>

            {{-- Kontrak --}}
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 mb-6">

                <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between">

                    <div>
                        <h2 class="font-semibold text-slate-800">
                            Dokumen Kontrak
                        </h2>

                        <p class="text-sm text-slate-500">
                            Pastikan seluruh isi kontrak telah dibaca dengan seksama.
                        </p>
                    </div>

                    <a href="{{ route('form-kontrak-preview', ['token' => Crypt::encryptString($kontrak?->id)]) }}"
                        target="_blank"
                        onclick="window.open(this.href, '_blank'); window.location.reload(); return false;"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-sky-600 text-white rounded-xl hover:bg-sky-700 transition">

                        <i class="ri-eye-line"></i>
                        Lihat Kontrak

                    </a>

                </div>

            </div>

            {{-- Signature --}}
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">

                <div class="border-b border-slate-200 px-6 py-4">

                    <h2 class="font-semibold text-slate-800 flex items-center gap-2">
                        <i class="ri-quill-pen-line"></i>
                        Tanda Tangan Digital
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Tanda tangani pada area di bawah ini.
                    </p>

                </div>

                <div class="p-6">

                    <div class="relative">

                        @if (session()->has('seen_kontrak') &&
                                is_array(session('seen_kontrak')) &&
                                session('seen_kontrak')[0] != true &&
                                session('seen_kontrak')[1] != \Carbon\Carbon::now()->format('Y-m-d'))
                            <div
                                class="absolute inset-0 z-10 bg-slate-100/80 backdrop-blur-xs flex items-center justify-center rounded-xl">

                                <div
                                    class="bg-white border border-red-200 text-red-600 px-5 py-3 rounded-xl font-medium shadow-sm">
                                    Baca kontrak terlebih dahulu
                                </div>

                            </div>
                        @endif

                        <canvas id="signature-pad"
                            class="w-full h-[250px] md:h-[350px] border-2 border-dashed border-slate-300 rounded-xl bg-slate-50">
                        </canvas>

                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-between mt-6">

                        <button id="clear" type="button"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-300 hover:bg-slate-100">

                            <i class="ri-refresh-line"></i>
                            Reset

                        </button>

                        <form id="signature-form" method="POST"
                            action="{{ route('form-kontrak-update', Crypt::encryptString($kontrak?->id)) }}">

                            @csrf
                            @method('PUT')

                            <input type="hidden" name="signature_svg" id="signature-svg">

                            <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition">

                                <i class="ri-send-plane-fill"></i>
                                Simpan & Kirim

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </x-main-div>

    <script>
        const canvas = document.getElementById('signature-pad');

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;

            canvas.getContext("2d").scale(ratio, ratio);
        }

        window.addEventListener('resize', resizeCanvas);

        resizeCanvas();

        const signaturePad = new SignaturePad(canvas, {
            penColor: "#000",
            backgroundColor: "#fff"
        });

        document.getElementById('clear').addEventListener('click', () => {
            signaturePad.clear();
        });

        document.getElementById('signature-form').addEventListener('submit', function(e) {

            if (signaturePad.isEmpty()) {
                alert('Harap berikan tanda tangan terlebih dahulu.');
                e.preventDefault();
                return;
            }

            document.getElementById('signature-svg').value =
                signaturePad.toDataURL('image/svg+xml');
        });
    </script>

</x-app-layout>
