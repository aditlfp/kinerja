<x-admin-layout :fullWidth="true">
        <div class="space-y-6 p-4 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Administrasi</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Hari Libur</h1>
                </div>
                <a href="{{ route('admin.holiday.create') }}" class="btn btn-sm bg-indigo-600 text-white hover:bg-indigo-700">Tambah Hari Libur</a>
            </div>
            <div class="flex justify-end">
                <div class="input flex w-full max-w-sm items-center border-slate-300 bg-white sm:w-fit">
					<i class="ri-search-2-line"></i>
					<input type="search" id="searchInput" class="border-none rounded-sm ml-1" placeholder="Search..." required>
				</div>
			</div>
            <div class="flex justify-center overflow-x-auto mx-10 pb-10">
                <table class="table table-sm  table-zebra w-full shadow-md bg-slate-50" id="searchTable">
                    <thead>
                        <tr>
                            <th class="bg-slate-100 text-slate-700 rounded-tl-2xl">#</th>
                            <th class="bg-slate-100 text-slate-700 ">Hari libur</th>
                            <th class="bg-slate-100 text-slate-700 rounded-tr-2xl">Tanggal Libur</th>
                        </tr>
                    </thead>
                    <tbody class=" text-sm my-10">
                        @php
                            $n = 1
                        @endphp
                        @forelse ($holiday as $i)
                        <tr>
                            <td class="py-1">{{ $n++ }}</td>
                            <td class="py-1">{{ $i->name }}</td>
                            <td class="py-1">{{ $i->tanggal_libur }}</td>
                        </tr>
                            
                        @empty
                            
                        <tr>
                            <td colspan="3" class="text-center">~ Data Kosong ~</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5 mx-10">
                {{ $holiday->links() }}
            </div>
            <div class="flex justify-end gap-2 py-3">
                <a href="{{ route('admin.index') }}" class="btn btn-sm border-slate-300 text-slate-700">Kembali</a>
            </div>
        </div>
</x-admin-layout>
