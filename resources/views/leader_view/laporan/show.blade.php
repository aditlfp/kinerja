<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
            </div>
            <div class="rounded-2xl bg-white py-6 shadow-xs ring-1 ring-slate-900/5 sm:py-10">
                <p class="border-l-4 border-sky-500 text-center text-lg font-bold uppercase sm:text-2xl">Hasil Laporan, {{ $laporan->user->nama_lengkap }}</p>
            <div class="flex flex-col items-center mx-2 my-2 sm:justify-center justify-start">
                <div class="overflow-x-auto w-full md:overflow-hidden mx-2 sm:mx-0 sm:w-full">
                    <div class="bg-slate-50 rounded-lg shadow-md mx-5">
                        <div class="my-5">
                            @if($laporan->image1 != 'no-image.jpg' && $laporan->image1 != null && Storage::disk('public')->exists('images/' . $laporan->image1)
                            || $laporan->image2 != 'no-image.jpg' && $laporan->image2 != null && Storage::disk('public')->exists('images/' . $laporan->image2)
                            || $laporan->image3 != 'no-image.jpg' && $laporan->image3 != null && Storage::disk('public')->exists('images/' . $laporan->image3))
                                @if($laporan->image1 != 'no-image.jpg' && $laporan->image1 != null && Storage::disk('public')->exists('images/' . $laporan->image1))
                                    <p class="text-center font-semibold">~ Sebelum ~</p>
                                    <span class="flex justify-center"><img src="{{ asset('storage/images/' . $laporan->image1) }}" alt="{{ asset('storage/images/' . $laporan->image1) }}" srcset="{{ asset('storage/images/' . $laporan->image1) }}" width="150px" class="rounded-sm"></span>
                                @endif
                                
                                @if($laporan->image2 != 'no-image.jpg' && $laporan->image2 != null && Storage::disk('public')->exists('images/' . $laporan->image2))
                                    <p class="text-center font-semibold">~ Proses ~</p>
                                    <span class="flex justify-center"><img src="{{ asset('storage/images/' . $laporan->image2) }}" alt="{{ asset('storage/images/' . $laporan->image2) }}" srcset="{{ asset('storage/images/' . $laporan->image2) }}" width="150px" class="rounded-sm"></span>
                                @endif
                                @if($laporan->image3 != 'no-image.jpg' && $laporan->image3 != null && Storage::disk('public')->exists('images/' . $laporan->image3))
                                    <p class="text-center font-semibold">~ Sesudah ~</p>
                                    <span class="flex justify-center"><img src="{{ asset('storage/images/' . $laporan->image3) }}" alt="{{ asset('storage/images/' . $laporan->image3) }}" srcset="{{ asset('storage/images/' . $laporan->image3) }}" width="150px" class="rounded-sm"></span>
                                @endif
                            @else
                                <p class="text-center font-semibold my-10">Gambar Tidak Ditemukan</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-center sm:justify-end w-full mt-5">
            			 <a href="{{ route('dashboard.index') }}" class="btn btn-error">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
        
            </div>
        </div>
    </div>
</x-app-layout>