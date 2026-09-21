<x-admin-layout :fullWidth="true">
        <form action="{{ route('admin.pekerjaan-cp.update', [$pcpId->id]) }}" method="POST">
        @method('put')
        @csrf
        <div>
		<h1 class="my-6 text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Pekerjaan</h1>
        <div class="bg-slate-100 mx-10 my-10 px-10 py-5 rounded shadow">
            <!-- user -->
				<div class="flex flex-col">
					<label for="user_id" class="label">Pilih User</label>
                    <select name="user_id" id="user_id" class="select-bordered select">
                        <option disabled selected>~ Pilih User ~</option>
                        @forelse ($user as $us)
                            @if($us->kerjasama_id == 1)
                                <option value="{{ $us->id }}" {{ $pcpId->user_id == $us->id ? "selected" : "" }}>{{ $us->nama_lengkap }}</option>
                            @endif
                        @empty
                            <option disabled>~ Data Kosong ~</option>
                        @endforelse
                    </select>
				</div>
				<!-- divisi -->
				<div class="flex flex-col">
					<label for="devisi_id" class="label">Pilih divisi</label>
                    <select name="devisi_id" id="devisi_id" class="select-bordered select">
                        <option disabled selected>~ Pilih Divisi ~</option>
                        @forelse ($divisi as $d)
                            <option value="{{ $d->id }}" {{ $pcpId->divisi_id == $d->id ? "selected" : "" }}>{{ $d->jabatan->name_jabatan }}</option>
                        @empty
                            <option disabled>~ Data Kosong ~</option>
                        @endforelse
                    </select>
				</div>
            <!-- kerja bagus -->
				<div class="flex flex-col">
					<label for="jabatan_id" class="label">Pilih Kerjasama</label>
                    <select name="kerjasama_id" id="kerjasama_id" class="select-bordered select">
                        <option selected disabled>~ Pilih Kerjasama ~</option>
                        @forelse ($kerjasama as $ker)
                            <option value="{{ $ker->id }}" {{ $pcpId->kerjasama_id == $ker->id ? "selected" : "" }}>{{ $ker->client->name }}</option>
                        @empty
                            <option disabled>~ Data Kosong ~</option>
                        @endforelse
                    </select>
				</div>
				<!-- type check -->
				<div class="flex flex-col">
					<label for="type_check" class="label">Pilih Jenis Pekerjaan</label>
                    <select name="type_check" id="type_check" class="select-bordered select">
                        <option disabled selected>~ Pilih Jenis Pekerjaan ~</option>
                        <option value="harian" name="type_check" {{ $pcpId->type_check == "harian" ? "selected" : "" }}>harian</option>
                        <option value="mingguan" name="type_check" {{ $pcpId->type_check == "mingguan" ? "selected" : "" }}>migguan</option>
                        <option value="bulanan" name="type_check" {{ $pcpId->type_check == "bulanan" ? "selected" : "" }}>bulanan</option>
                        <option value="isidental" name="type_check" {{ $pcpId->type_check == "isidental" ? "selected" : "" }}>isidental</option>
                    </select>
				</div>
			    <!-- nama -->
				<div class="flex flex-col">
					<label for="name" class="label">Nama Pekerjaan</label>
                    <input type="text" name="name" id="name" value="{{ $pcpId->name }}" class="input input-bordered" placeholder="Nama pekerjaan...">
				</div>

				<div class="flex gap-2 my-5 justify-end">
					<button><a href="{{ route('admin.pekerjaan-cp.index') }}" class="btn btn-error">Back</a></button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
        </div>
        </form>
</x-admin-layout>
