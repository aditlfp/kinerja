<x-admin-layout :fullWidth="true">
    @section('title', 'Data Checkpoint')

    <div class="w-full px-2 mx-auto space-y-4 max-w-(--breakpoint-2xl) sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-(--md-sys-color-on-surface)">Index Rencana Kerja</h1>
                    <p class="mt-1 text-sm leading-5 text-(--md-sys-color-on-surface-variant)">Monitor rencana dan pekerjaan checkpoint karyawan.</p>
                </div>
                {{-- <div class="flex items-center gap-2">
                    <a href="{{ Auth::user()->role_id == 2 ? route('admin.index') : route('dashboard.index') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Kembali</a>
                    <a href="{{ route('admin.cp.create') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700">
                        <i class="ri-add-line mr-1.5"></i> Checkpoint
                    </a>
                </div> --}}
            </div>

            <div class="flex flex-col gap-3 mt-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if (Auth::user()->role_id == 2)
                        <form id="filterForm" action="{{ route('admin.cp.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                            <select name="filterKerjasama" id="filterKerjasama" class="h-10 min-w-[220px] rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-hidden">
                                <option selected disabled>~ Kerja Sama ~</option>
                                @foreach ($kerjasama as $i)
                                    <option value="{{ $i->id }}" {{ $filter == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700">Filter</button>
                        </form>
                    @elseif(Auth::user()->devisi_id == 18)
                        <div class="flex items-center gap-2">
                            @foreach (['rencana' => 'Rencana', 'dikerjakan' => 'Dikerjakan'] as $filterType => $label)
                                <form action="#" method="get">
                                    <input type="hidden" name="type" value="{{ $filterType }}">
                                    <button type="submit" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">{{ $label }}</button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                </div>

                <label class="flex items-center w-full h-10 max-w-sm gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50">
                    <i class="text-base text-gray-500 ri-search-2-line"></i>
                    <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-hidden" placeholder="Cari nama, CP, deskripsi..." />
                </label>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-xs rounded-2xl">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[980px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-center text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            @if ($type !== 'rencana')
                                <th class="px-4 py-3 sm:px-5">Gambar Bukti</th>
                            @endif
                            <th class="px-4 py-3 sm:px-5">Nama CP - Karyawan</th>
                            @if ($type !== 'rencana')
                                <th class="px-4 py-3 sm:px-5">Deskripsi</th>
                            @endif
                            <th class="px-4 py-3 sm:px-5">Tanggal</th>
                            <th class="px-4 py-3 sm:px-5">Check Point</th>
                            @if ($type !== 'rencana')
                                <th class="px-4 py-3 sm:px-5">Status</th>
                            @endif
                            <th class="px-4 py-3 sm:px-5">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $counter = 0; @endphp
                        @forelse ($cex2 as $c2)
                            @foreach ($c2->items as $item)
                                @php
                                    $counter++;
                                    $cpId = $item->pekerjaan_cp_id;
                                    $pc = $pcp->firstWhere('id', $cpId);
                                    $img = $item->images->first()->path ?? null;
                                    $label = $pc->name ?? ($item->input_manual ?: $cpId);
                                @endphp
                                <tr class="hover:bg-blue-50/40">
                                    <td class="px-4 py-3 sm:px-5">{{ $counter }}</td>

                                    @if ($type !== 'rencana')
                                        <td class="px-4 py-3 sm:px-5">
                                            @if (!$img)
                                                <x-no-img class="scale-50" />
                                            @else
                                                <img src="{{ asset('storage/images/' . $img) }}" width="70" class="rounded-sm" alt="Gambar Bukti">
                                            @endif
                                        </td>
                                    @endif

                                    <td class="min-w-[180px] px-4 py-3 text-start capitalize sm:px-5">
                                        {{ $label ? '~ ' . $label : '~ -' }} <br /> -
                                        <span class="capitalize">{{ strtolower($c2->user->nama_lengkap) }}</span>
                                    </td>

                                    @if ($type !== 'rencana')
                                        <td class="min-w-[220px] px-4 py-3 text-start capitalize sm:px-5">~ {{ $item->deskripsi ?? '' }}</td>
                                    @endif

                                    <td class="min-w-[120px] px-4 py-3 sm:px-5">{{ $item->tanggal?->format('Y-m-d') ?? $c2->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 sm:px-5">{{ $pc?->type_check ?? '' }}</td>

                                    @if ($type !== 'rencana')
                                        <td class="px-4 py-3 sm:px-5">
                                            @if ($item->approve_status)
                                                <div class="flex flex-col items-center">
                                                    <span class="rounded-md px-2 py-0.5 text-xs text-white {{ $item->approve_status === 'accept' ? 'bg-emerald-700' : ($item->approve_status === 'proccess' ? 'bg-amber-500' : 'bg-red-500') }}">{{ ucfirst($item->approve_status) }}</span>
                                                    @if ($item->note)
                                                        <p class="text-xs italic">Note: {{ $item->note }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    @endif

                                    <td class="px-4 py-3 sm:px-5">
                                        @if ($type == 'dikerjakan')
                                            <button data-item-id="{{ $item->id }}" data-checkpoint-id="{{ $c2->id }}" class="btn btn-info btn-sm btn-nilai">Nilai</button>
                                        @else
                                            <button data-id="{{ $c2->id }}" data-item-id="{{ $item->id }}" class="btn btn-error btn-sm btn-hapus">Hapus</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="{{ $type === 'rencana' ? 6 : 8 }}" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div id="div_form_nilai" class="fixed inset-0 z-50 flex items-center justify-center hidden p-10 overflow-hidden bg-slate-900/50 drop-shadow-md"></div>
        <div id="div_form_delete" class="fixed inset-0 z-50 flex items-center justify-center hidden p-10 overflow-hidden bg-slate-900/50 drop-shadow-md"></div>

        <div id="modalShow" class="modalShow hidden fixed inset-0 z-9000 flex justify-center items-center bg-slate-500/10 backdrop-blur-xs p-5">
            <div class="relative w-full max-w-xl p-6 rounded-md shadow-sm bg-slate-50">
                <button id="closeButton" class="absolute scale-90 btn btn-error top-2 right-2">&times;</button>
                <div class="flex flex-col items-center justify-center gap-4">
                    <span id="status" class="p-2 text-lg font-semibold text-white rounded-lg"></span>
                    <img id="modalImg" loading="lazy" alt="" width="120" class="rounded-sm" />
                    <p id="modalTitle" class="py-5 font-semibold text-center wrap-break-word whitespace-pre-wrap"></p>
                    <div class="flex flex-col w-full">
                        <label for="notes">Note</label>
                        <textarea id="notes" name="note" class="textarea textarea-bordered" placeholder="notes..."></textarea>
                    </div>
                    <button id="confirmButton" class="px-10 btn btn-warning rounded-btn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                $('.btn-nilai, .btn-hapus').click(function() {
                    const isDelete = $(this).hasClass('btn-hapus');
                    const itemId = $(this).data('item-id');
                    const checkpointId = $(this).data('checkpoint-id') || $(this).data('id');

                    if (isDelete) {
                        const route = "{{ url('direksi/deleteRencana') }}/" + checkpointId;
                        const html = `
                            <div class="flex flex-col p-4 mx-10 rounded-md bg-slate-200">
                                <div class="flex justify-end">
                                    <button id="btnClose" class="btn btn-error">&times;</button>
                                </div>
                                <form action="${route}" method="POST" class="flex flex-col items-center gap-4">
                                    @csrf
                                    <p class="text-sm text-center">Yakin ingin menghapus data ini?</p>
                                    <input type="hidden" name="arrKe" value="${itemId}">
                                    <button type="submit" class="btn btn-info btn-sm">Submit</button>
                                </form>
                            </div>`;
                        $('#div_form_delete').html(html).removeClass('hidden').addClass('flex');
                    } else {
                        @if ($cex2)
                            const dataCex = @json($cex2);
                            const dataPcp = @json($pcp);
                            const checkpoint = dataCex.find(c => c.id == checkpointId);
                            const item = checkpoint ? checkpoint.items.find(i => i.id == itemId) : null;
                            const filteredPc = item ? dataPcp.find(pc => pc.id == item.pekerjaan_cp_id) : null;

                            const route = "{{ route('direksi.uploadNilai', ':id') }}".replace(':id', checkpointId);
                            const firstImage = item && item.images && item.images.length ? item.images[0].path : null;
                            const imgSrc = firstImage ? `{{ asset('storage/images') }}/${firstImage}` : '';
                            const label = filteredPc ? filteredPc.name : (item && item.input_manual ? item.input_manual : '');
                            const deskripsi = item && item.deskripsi ? item.deskripsi : '';
                            const html = `
                            <div class="flex flex-col max-w-md min-w-full p-5 mx-auto rounded-md bg-slate-200 lg:max-w-sm">
                                <div class="flex justify-end">
                                    <button id="btnClose" class="btn btn-error">&times;</button>
                                </div>
                                <form action="${route}" method="POST" class="flex flex-col items-center gap-4">
                                    @csrf
                                    @method('PUT')
                                    <p class="font-semibold text-center">~${label}~</p>
                                    <div class="flex justify-center">
                                        ${imgSrc ? `<img src="${imgSrc}" class="rounded-sm" alt="Gambar Bukti" width="70">` : ''}
                                    </div>
                                    <p class="my-2 text-xs sm:text-sm line-clamp-2 text-start">${deskripsi}</p>
                                    <fieldset class="w-full">
                                        <legend class="mb-2 font-semibold text-center">Status:</legend>
                                        <label class="inline-flex items-center gap-2">
                                            <input type="radio" name="approve_status[]" value="accept" checked class="radio radio-sm radio-success">
                                            Disetujui
                                        </label>
                                        <label class="inline-flex items-center gap-2 ml-4">
                                            <input type="radio" name="approve_status[]" value="denied" class="radio radio-sm radio-error">
                                            Ditolak
                                        </label>
                                    </fieldset>
                                    <input type="text" name="note[]" placeholder="note.." class="w-full text-sm input input-bordered input-sm">
                                    <input type="hidden" name="arrKe" value="${itemId}">
                                    <input type="hidden" name="id" value="${checkpointId}">
                                    <button type="submit" class="w-full btn btn-info btn-sm">Submit</button>
                                </form>
                            </div>`;
                            $('#div_form_nilai').html(html).removeClass('hidden').addClass('flex');
                        @endif
                    }
                    $('body').addClass('overflow-hidden');
                });

                $(document).on('click', '#btnClose', function() {
                    $('#div_form_nilai, #div_form_delete').addClass('hidden').removeClass('flex').empty();
                    $('body').removeClass('overflow-hidden');
                });

                $('#closeButton').click(function() {
                    $('#modalShow').toggleClass('hidden');
                });

                $('#confirmButton').click(function() {
                    const route = $(this).data('route');
                    const status = $(this).data('status');
                    const note = $('#notes').val();

                    $.ajax({
                        url: route,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            _method: 'PATCH',
                            approve_status: status,
                            note: note,
                        },
                        success: () => location.reload(),
                        error: (xhr, status, error) => console.error(error)
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
