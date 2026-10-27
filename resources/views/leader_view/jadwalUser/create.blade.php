<x-app-layout>
    <x-main-div>
        <div class="py-10 sm:mx-10">
            <p class="text-center text-lg sm:text-2xl uppercase font-bold">
                Jadwal Bulanan,<br>
                {{ Auth::user()->kerjasama->client->name }}
            </p>

            <div class="flex flex-col items-center mx-2 my-2 sm:justify-center justify-start">
                {{-- Top Controls --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 items-center gap-3 w-full mt-5">
                    <div>
                        @php
                            $jabatan = Auth::user()->divisi->jabatan->code_jabatan ?? Auth::user()->divisi->code_jabatan;
                        @endphp
                        <a href="{{
                            $jabatan === 'CO-CS' ? route('leader-jadwal.index') :
                            ($jabatan === 'CO-SCR' ? route('danru-jadwal.index') : route('dashboard.index'))
                        }}" class="btn btn-error">Kembali</a>
                    </div>

                    <div class="mt-0 sm:mt-5">
                        <form method="GET" action="{{ route('leader-jadwal.create') }}" class="flex flex-col sm:flex-row sm:items-end sm:justify-end gap-2">
                            <label class="label p-0 text-xs sm:text-base flex-col sm:flex-row items-start sm:items-center gap-1">Bulan
                                <input type="month" name="month" value="{{ $month }}" class="input input-bordered input-sm text-xs w-full sm:w-auto">
                            </label>
                            <button class="btn btn-primary btn-sm w-full sm:w-auto">Tampilkan</button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('storeJadwalLeader') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">

                    {{-- Bulk All --}}
                    @php $kodeToggle = ['Pagi' => 'btn-warning', 'Siang' => 'btn-info', 'Sore' => 'btn-accent', 'Malam' => 'btn-secondary', 'OFF' => 'btn-error']; @endphp
                    <div class="flex flex-wrap items-center gap-1.5 rounded w-full px-2 py-3 my-3 bg-slate-100">
                        <span class="text-xs sm:text-sm font-semibold w-full sm:w-auto">Isi semua tanggal:</span>
                        @foreach (\App\Models\JadwalUser::OPTIONS as $option)
                            <button type="button" data-bulk-status="{{ $option }}" class="btn btn-xs sm:btn-sm {{ $kodeToggle[$option] ?? 'btn-neutral' }}">
                                <span class="sm:hidden">{{ substr($option, 0, 1) }}</span><span class="hidden sm:inline">{{ $option }}</span>
                            </button>
                        @endforeach
                    </div>
                    <p class="md:hidden text-center text-xs text-gray-600">Geser tabel ke samping untuk melihat semua tanggal.</p>

                    {{-- Table --}}
                    <div class="overflow-x-auto max-w-full w-full mx-0 sm:mx-10 rounded">
                        <table class="table table-xs table-zebra sm:table-md text-xs bg-slate-50 font-semibold sm:text-md min-w-max">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 z-10 bg-slate-200 shadow-[2px_0_4px_rgba(0,0,0,.12)] min-w-[150px] sm:min-w-[220px]">Nama Karyawan</th>
                                    @foreach ($days as $day)
                                        <th class="min-w-[96px] sm:min-w-[92px] text-center">{{ $day->format('d') }}<br><span class="hidden sm:inline text-[10px] font-normal">{{ $day->locale('id')->isoFormat('ddd') }}</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="sticky left-0 z-10 bg-slate-50 shadow-[2px_0_4px_rgba(0,0,0,.12)]">
                                            <div class="font-semibold">{{ $user->nama_lengkap }}</div>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach (\App\Models\JadwalUser::OPTIONS as $option)
                                                    <button type="button" data-row-status="{{ $option }}" data-user-id="{{ $user->id }}" class="btn btn-xs">{{ substr($option, 0, 1) }}</button>
                                                @endforeach
                                            </div>
                                        </td>
                                        @foreach ($days as $day)
                                            @php $key = $user->id . '|' . $day->toDateString(); @endphp
                                            <td class="px-1 py-1">
                                                <select name="schedule[{{ $user->id }}][{{ $day->day }}]" aria-label="Shift {{ $day->format('d') }} untuk {{ $user->nama_lengkap }}" class="select select-bordered select-sm text-xs w-full min-w-[110px]">
                                                    @foreach (\App\Models\JadwalUser::OPTIONS as $option)
                                                        <option value="{{ $option }}" @selected(($schedules->get($key)?->status ?? 'OFF') === $option)>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $days->count() + 1 }}" class="text-center py-8">Tidak ada karyawan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 sm:flex sm:justify-end mx-0 sm:mx-10 mt-5">
                        <button class="btn btn-success w-full sm:w-auto">Simpan Jadwal</button>
                    </div>
                </form>

                <form method="GET" action="{{ route('leader-jadwal.export') }}" class="grid grid-cols-1 sm:flex sm:items-center sm:justify-end gap-2 w-full mx-0 sm:mx-10 my-5">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button name="format" value="pdf" class="btn btn-error btn-sm w-full sm:w-auto">Export PDF</button>
                    <button name="format" value="xlsx" class="btn btn-success btn-sm w-full sm:w-auto">Export Excel</button>
                </form>
            </div>
        </div>
    </x-main-div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-bulk-status]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const status = button.dataset.bulkStatus;
                        document.querySelectorAll('select[name^="schedule["]').forEach((select) => {
                            select.value = status;
                        });
                    });
                });

                document.querySelectorAll('[data-row-status]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const status = button.dataset.rowStatus;
                        const userId = button.dataset.userId;
                        document.querySelectorAll(`select[name^="schedule[${userId}]"]`).forEach((select) => {
                            select.value = status;
                        });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
