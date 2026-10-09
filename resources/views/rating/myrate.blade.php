<x-app-layout>
        <div class="min-h-screen bg-slate-50 px-4 py-5 text-slate-800 sm:-mx-10">
            <div class="mx-auto flex w-full max-w-md items-center justify-between">
                <a href="{{ route('dashboard.index') }}"><img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32"></a>
                <span class="font-semibold text-sm text-slate-500/50">Rating Saya</span>
            </div>
            <div class="mx-auto mt-8 w-full max-w-md overflow-hidden rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-200">
                <div class="rounded-xl bg-slate-50 px-5 py-8 font-semibold">
                <p class="border-l-4 border-sky-500 pl-3 text-xl font-black">Rating Saya</p>
                @php
                    $ratM = $rating ? $rating->rate_mitra : 0;
                    $ratL = $rating ? $rating->rate_leader : 0;
                    $totalR = ($ratM + $ratL) / 2;
                @endphp
                <p class="mt-6 text-center font-semibold text-slate-600">Rating</p>
                <span class="mb-5 flex items-center justify-center rounded-lg bg-white p-3 ring-1 ring-slate-200">
                    @if($totalR <= 0)
                        <p class="text-center font-semibold">Belum Ada Rating</p>
                    @else
                    @endif
                    @for ($i = 1; $i <= $totalR; $i++)
						<input type="radio" class="mask mask-star-2 bg-orange-400" readonly disabled />
					@endfor
                </span>
                <div class="pt-5 flex justify-center">
                    <a href="{{ route('dashboard.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">Kembali</a>
                </div>
                </div>
            </div>
        </div>
</x-app-layout>