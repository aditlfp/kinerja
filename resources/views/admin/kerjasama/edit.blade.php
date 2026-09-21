<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Kerjasama')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Kerjasama</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui data kontrak kerjasama client dan detail approval.</p>
                </div>
                <a href="{{ route('admin.kerjasama.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Kembali
                </a>
            </div>
        </section>

        <form action="{{ url('kerjasama/' . $kerjasama->id) }}" method="POST" class="space-y-4">
            @method('PUT')
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="client_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Client</label>
                        <select name="client_id" id="client_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option disabled>-- Select Client --</option>
                            @foreach ($client as $i)
                                <option value="{{ $i->id }}" {{ $kerjasama->client_id == $i->id ? 'selected' : '' }}>{{ $i->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('client_id')" />
                    </div>

                    <div>
                        <label for="value" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Value</label>
                        <input type="text" name="value" id="value" value="{{ $kerjasama->value }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                        <x-input-error class="mt-2" :messages="$errors->get('value')" />
                    </div>

                    <div>
                        <label for="experied" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Expired Date</label>
                        <input type="date" name="experied" id="experied" value="{{ $kerjasama->experied }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                        <x-input-error class="mt-2" :messages="$errors->get('experied')" />
                    </div>

                    <div>
                        <label for="approve1" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Approve 1</label>
                        <input type="text" name="approve1" id="approve1" value="{{ $kerjasama->approve1 }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                    </div>
                    <div>
                        <label for="approve2" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Approve 2</label>
                        <input type="text" name="approve2" id="approve2" value="{{ $kerjasama->approve2 }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="approve3" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Approve 3</label>
                        <input type="text" name="approve3" id="approve3" value="{{ $kerjasama->approve3 }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.kerjasama.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
