<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Laporan Mitra')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Laporan: {{ $laporanMitra->kerjasama->client->name }}</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui file PDF laporan mitra.</p>
                </div>
                <a href="{{ route('laporanMitra.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form action="{{ route('laporanMitra.update', $laporanMitra->id) }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="space-y-4">
                    <div>
                        <label for="kerjasama_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Mitra</label>
                        <select class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-none" name="kerjasama_id" id="kerjasama_id">
                            <option selected disabled>~ Pilih Mitra ~</option>
                            @foreach ($kerjasama as $i)
                                <option value="{{ $i->id }}" {{ $laporanMitra->kerjasama_id == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('client_id')" />
                    </div>

                    <div>
                        <label for="value" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">File Laporan PDF</label>
                        <div id="oldFile-container" class="mb-2">
                            <p class="mb-1 text-xs font-semibold text-gray-500">File lama:</p>
                            <div id="oldFile" class="pdf-container"></div>
                        </div>
                        <input type="file" name="file_pdf" id="value" class="file-input file-input-bordered w-full" accept=".pdf">
                        <input type="hidden" name="oldfile" value="{{ $laporanMitra->file_pdf }}"/>
                        <x-input-error class="mt-2" :messages="$errors->get('value')" />
                    </div>
                    <div id="pdf-preview-container" class="flex justify-center"></div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('laporanMitra.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Simpan</button>
                </div>
            </section>
        </form>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
        <script>
            $(function() {
                function renderPDF(url, container) {
                    pdfjsLib.getDocument(url).promise.then(function(pdf) {
                        pdf.getPage(1).then(function(page) {
                            const viewport = page.getViewport({ scale: 0.45 });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            canvas.style.maxWidth = '100%';
                            canvas.style.height = 'auto';
                            container.appendChild(canvas);
                            page.render({ canvasContext: context, viewport: viewport });
                        });
                    });
                }

                renderPDF("{{ asset('storage/pdf/'. $laporanMitra->file_pdf) }}", document.getElementById('oldFile'));

                function displayPDFPreview(pdfData) {
                    $('#pdf-preview-container').empty();
                    const loadingTask = pdfjsLib.getDocument({ data: atob(pdfData) });
                    loadingTask.promise.then(function(pdf) {
                        pdf.getPage(1).then(function(page) {
                            const viewport = page.getViewport({ scale: 0.45 });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            $('#pdf-preview-container').append(canvas);
                            page.render({ canvasContext: context, viewport: viewport });
                        });
                    });
                }

                $('#value').change(function() {
                    $('#oldFile-container').hide();
                    const file = this.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const pdfData = e.target.result.split(',')[1];
                        displayPDFPreview(pdfData);
                    };
                    reader.readAsDataURL(file);
                });
            });
        </script>
    @endpush
</x-admin-layout>
