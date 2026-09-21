<x-admin-layout :fullWidth="true">
    @section('title', 'Buat Jadwal')

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Jadwal Hari {{ $hari }}</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Tetapkan shift harian untuk karyawan terpilih.</p>
                </div>
                <a href="{{ route('admin.jadwal.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
            <form id="jadwalForm" action="{{ route('storeJadwalLeader') }}" method="POST" class="space-y-4">
                @csrf
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-[680px] divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Nama Lengkap</th>
                                <th class="px-4 py-3">Centang</th>
                                <th class="px-4 py-3">Shift</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @php $no = 1; @endphp
                            @forelse ($user as $us)
                                @if ($us->nama_lengkap != 'admin' && $us->nama_lengkap != 'user')
                                    <tr>
                                        <td class="px-4 py-3">{{ $no++ }}</td>
                                        <td class="px-4 py-3">{{ $us->nama_lengkap }}</td>
                                        <td class="px-4 py-3"><input name="userID[]" value="{{ $us->id }}" type="checkbox" class="checkbox checkbox-sm"/></td>
                                        <td class="px-4 py-3">
                                            <select name="shift[]" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-2 text-xs text-gray-700">
                                                <option selected disabled>Pilih Shift</option>
                                                @forelse($shift as $i)
                                                    <option value="{{ $i->id }}">{{ $i->shift_name }} || {{ $i->jam_start }} - {{ $i->jam_end }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="hari" value="{{ $hari }}"/>
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.jadwal.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </section>
    </div>
</x-admin-layout>
