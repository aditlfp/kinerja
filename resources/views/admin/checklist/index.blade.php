<x-admin-layout :fullWidth="true">
    @section('title', 'Data Checklist')

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Checklist</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Monitoring kebersihan area berdasarkan rentang tanggal.</p>
                </div>
                <label class="flex h-10 w-full max-w-sm items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3">
                    <i class="ri-search-2-line text-base text-gray-500"></i>
                    <input type="search" id="searchInput" class="w-full border-none bg-transparent text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none" placeholder="Cari area atau sub area..." />
                </label>
            </div>
        </section>

        @if($startDate && $endDate)
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <a href="{{ Auth::user()->role_id == 2 ? route('admin.index') : route('leaderView') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
                    <a href="{{ Auth::user()->role_id == 2 ? route('admin.checklist.create') : route('leader-checklist.create') }}" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">+ Checklist</a>
                </div>
            </section>

            <div class="space-y-4">
                @php
                    $str = Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                    $end = Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
                @endphp
                @for($date = $str->copy(); $date->lte($end); $date->addDay())
                    @php
                        $d = $date->isoFormat('dddd, D MMMM Y');
                        $filteredChecklist = $checklist->where('created_at', '>=', $date)->where('created_at', '<', $date->copy()->addDay());
                    @endphp
                    @if($filteredChecklist->count() > 0)
                        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 text-center text-sm font-semibold text-gray-700">{{ $d }}</div>
                            <div class="w-full overflow-x-auto">
                                <table class="w-full min-w-[620px] divide-y divide-gray-100" id="searchTable">
                                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        <tr>
                                            <th class="px-4 py-3 sm:px-5">#</th>
                                            <th class="px-4 py-3 sm:px-5">Area - Subarea</th>
                                            <th class="px-4 py-3 sm:px-5">Tingkat Bersih</th>
                                            <th class="px-4 py-3 text-right sm:px-5">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                                        @php $no = 1; @endphp
                                        @foreach ($filteredChecklist as $c)
                                            <tr class="hover:bg-blue-50/40">
                                                <td class="px-4 py-3 sm:px-5">{{ $no++ }}</td>
                                                <td class="px-4 py-3 sm:px-5">{{ $c->area}} - {{ $c->sub_area }}</td>
                                                <td class="px-4 py-3 sm:px-5">{{ $c->tingkat_bersih }}</td>
                                                <td class="px-4 py-3 sm:px-5 text-right">
                                                    <form action="{{ route('admin.checklist.destroy', $c->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="text-red-500 hover:text-red-600 text-xl transition"><i class="ri-delete-bin-5-line"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif
                @endfor
            </div>

            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col items-center gap-4">
                    @php
                        $hasApprovedMatch = false;
                        foreach($checklistApproved as $c){
                            foreach($final as $fin){
                                if($c->id == $fin->checklist_id){ $hasApprovedMatch = true; break 2; }
                            }
                        }
                    @endphp

                    @if($hasApprovedMatch)
                        <img srcset="{{ asset('storage/'. Auth::user()->signature->getSignatureImagePath()) }}" class="max-h-28"/>
                    @elseif (!Auth::user()->hasBeenSigned())
                        <form id="signForm" action="{{ Auth::user()->getSignatureRoute() }}" method="POST" class="w-full flex justify-center">
                            @csrf
                            <div style="text-align: center">
                                <x-creagia-signature-pad
                                    border-color="#AFAFAF"
                                    pad-classes="rounded-xl border-2"
                                    button-classes="btn btn-primary mt-4"
                                    clear-name="Hapus"
                                    submit-name="Approve"
                                    :disabled-without-signature="true"
                                />
                            </div>
                        </form>
                    @else
                        <form id="hiddenForm" action="{{ route('admin.checklist.index')}}" method="GET" class="approvalForm w-full max-w-md">
                            @csrf
                            @foreach ($checklistApproved as $item)
                                <input name="allow" value="true" class="hidden"/>
                                <input type="text" name="checklist_id[]" value="{{ $item->id }}" class="hidden">
                                <input name="signature" id="approve" value="{{ Auth::user()->signature->getSignatureImagePath()}}" class="hidden"/>
                            @endforeach
                            <div class="flex justify-center">
                                <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Approve</button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        @else
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <form action="{{ Auth::user()->role_id == 2 ? route('admin.checklist.index') : route('leader-checklist.index') }}" method="GET" class="mx-auto max-w-xl space-y-4">
                    @csrf
                    <p class="text-center text-lg font-semibold text-gray-800">Tanggal Mulai - Akhir</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input type="date" name="start_date" id="str1" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-none">
                        <input type="date" name="end_date" id="end1" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-none">
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </section>
        @endif
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/sign-pad/sign-pad.min.js') }}"></script>
    @endpush
</x-admin-layout>
