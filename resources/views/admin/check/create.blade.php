<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Check Point')

    <div class="mx-auto w-full max-w-screen-md space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Check Point</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Buat rencana checkpoint berdasarkan user dan jumlah item.</p>
                </div>
                <a href="{{ route('admin.cp.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kembali</a>
            </div>
            @if(session('msg'))
                <div class="mt-4 rounded-xl bg-emerald-50 px-4 py-2 text-center text-sm font-semibold text-emerald-700">{{ session('msg') . '!!!' }}</div>
            @endif
        </section>

        <form action="{{ route('admin.cp.store')}}" method="post" class="space-y-4">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="space-y-4">
                    <div>
                        <label for="user_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Pilih User/Karyawan</label>
                        <select name="user_id" id="user_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option readonly disabled selected>~ Pilih User ~</option>
                            @forelse ($user as $arr)
                                <option value="{{ $arr->id}}" data-client_id="{{ $arr->kerjasama->client_id}}">{{ $arr->nama_lengkap}}</option>
                            @empty
                                <option disabled>~ User Kosong ~</option>
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="client_id_display" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Pilih Client</label>
                        <select id="client_id_display" class="h-10 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700" disabled>
                            <option readonly disabled selected>~ Pilih Client ~</option>
                            @forelse ($client as $arr)
                                <option value="{{ $arr->id}}">{{ $arr->name}}</option>
                            @empty
                                <option disabled>~ User Kosong ~</option>
                            @endforelse
                        </select>
                        <input type="hidden" name="client_id" id="client_id_hidden">
                        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="check_count" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Masukkan Jumlah CheckPoint</label>
                        <input type="number" min="1" name="check_count" id="check_count" placeholder="10" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-none">
                        <x-input-error :messages="$errors->get('check-count')" class="mt-2" />
                    </div>

                    <div id="nameInputs" class="space-y-2"></div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.cp.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Simpan</button>
                </div>
            </section>
        </form>
    </div>

    @push('scripts')
        <script>
            $(function () {
                $('#user_id').on('change', function () {
                    const selectedClientId = $(this).find(':selected').data('client_id');
                    $('#client_id_display').val(selectedClientId);
                    $('#client_id_hidden').val(selectedClientId || '');
                });

                const checkCountInput = $('#check_count');
                const nameInputsDiv = $('#nameInputs');

                checkCountInput.on('input', function () {
                    const checkCount = parseInt(checkCountInput.val(), 10);
                    nameInputsDiv.empty();

                    if (!checkCount || checkCount < 1) return;

                    for (let i = 1; i <= checkCount; i++) {
                        nameInputsDiv.append(`
                            <label for="name${i}" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Name ${i} (Opsional)</label>
                            <input type="text" name="name[]" id="name${i}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Name...">
                        `);
                    }
                });
            });
        </script>
    @endpush
</x-admin-layout>
