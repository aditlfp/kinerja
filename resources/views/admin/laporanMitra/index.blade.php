<x-admin-layout :fullWidth="true">
    @section('title', 'Laporan Mitra')

    {{-- <div x-data="{ delOpen: false, deleteId: null }" class="w-full max-w-screen-xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Laporan Mitra</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola dokumen PDF laporan dari masing-masing mitra.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari laporan mitra..." />
                    </label>
                    <a href="{{ route('admin.index') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Dashboard</a>
                    @if(Auth::user()->id == 2)
                        <a href="{{ route('laporanMitra.create') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700"><i class="ri-add-line mr-1.5"></i>Laporan</a>
                    @endif
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[700px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Nama Mitra</th>
                            <th class="px-4 py-3 sm:px-5">File PDF</th>
                            @if(Auth::user()->id == 2)
                                <th class="px-4 py-3 text-right sm:px-5">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $no = 1; @endphp
                        @forelse ($laporanMitra as $index => $i)
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800 sm:px-5">{{ $i->kerjasama->client->name }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div id="pdf-container-{{$index}}" class="pdf-container"></div>
                                </td>
                                @if(Auth::user()->id == 2)
                                    <td class="px-4 py-3 sm:px-5">
                                        <div class="flex justify-end gap-1.5">
                                            <x-btn-edit>{{ route('laporanMitra.edit', $i->id) }}</x-btn-edit>
                                            <button type="button" @click="delOpen = true; deleteId = {{ $i->id }}" class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-700 hover:bg-red-100">Hapus</button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->id == 2 ? 4 : 3 }}" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data laporan mitra kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div x-show="delOpen" x-cloak style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/35 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden bg-white shadow-xl rounded-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Konfirmasi Hapus Laporan</h3>
                    <button @click="delOpen = false" type="button" class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50">Tutup</button>
                </div>
                <form :action="`{{ url('laporanMitra') }}/${deleteId}`" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus laporan ini?</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="delOpen = false" class="px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-3 py-2 text-xs font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>[x-cloak]{display:none!important;}</style>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
        <script>
            function renderPDF(url, container) {
                pdfjsLib.getDocument(url).promise.then(function(pdf) {
                    pdf.getPage(1).then(function(page) {
                        const viewport = page.getViewport({ scale: 0.45 });
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        canvas.style.maxWidth = '250px';
                        canvas.style.height = 'auto';
                        container.appendChild(canvas);
                        page.render({ canvasContext: context, viewport: viewport });
                    });
                });
            }

            $(function() {
                @foreach ($laporanMitra as $index => $i)
                    renderPDF("{{ asset('storage/pdf/'. $i->file_pdf) }}", document.getElementById("pdf-container-{{$index}}"));
                @endforeach
            });
        </script>
    @endpush --}}

    <div class="w-full max-w-3xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
        <div class="px-5 py-4 text-sm text-center text-blue-900 border border-blue-100 shadow-sm rounded-2xl bg-blue-50">
            <span class="font-medium">
                Sekarang laporan berada di link yang berbeda,
                <a href="https://laporan-sac.sac-po.com/" class="inline-flex items-center ml-1 font-semibold text-blue-700 underline transition decoration-blue-300 underline-offset-4 hover:text-blue-900">
                    Klik Disini
                </a>
            </span>
        </div>
    </div>
</x-admin-layout>
