<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
            </div>
            <div id="mDiv" class="rounded-2xl bg-white py-6 shadow-xs ring-1 ring-slate-900/5 sm:py-10">
            <p class="text-lg font-bold text-center uppercase sm:text-2xl ">Riwayat Laporan, {{ Auth::user()->kerjasama->client->name }}</p>
            <div class="flex flex-col items-center justify-start mx-2 my-2 sm:justify-center">
                <div class="flex items-center justify-center w-full my-5 sm:justify-end">
                    <x-search />
                </div>
                <div class="w-full mx-2 overflow-x-auto md:overflow-hidden sm:mx-0 sm:w-full ">
                    <table id="searchTable" class="table w-full text-xs font-semibold table-xs table-zebra sm:table-md bg-slate-50 sm:text-md ">
                        <thead>
							<tr >
								<th class="p-1 py-2 bg-slate-300 rounded-tl-2xl">#</th>
                                <th class="p-1 py-2 text-center bg-slate-300" style="min-width: 230px;" colspan="3">Foto Progres</th>
                                @if(Auth::user()->divisi->jabatan->code_jabatan == 'MITRA')
								    <th class="p-1 py-2 bg-slate-300">Keterangan</th>
								    <th class="p-1 py-2 text-center bg-slate-300 rounded-tr-2xl">Tanggal</th>
								    <!--<th class="p-1 py-2 bg-slate-300 rounded-tr-2xl">Action</th>-->
                                @else
								    <th class="p-1 py-2 bg-slate-300">Keterangan</th>
								    <th class="p-1 py-2 text-center bg-slate-300 rounded-tr-2xl">Tanggal</th>
                                @endif
							</tr>
						</thead>
                        <tbody>
                            @php
                                $n = 1;
                            @endphp
                            @forelse ($laporan as $i)
                            <tr>
                                <td class="p-1">{{ $n++ }}.</td>
                                @if ($i->img_before != null)
                                    <td><img id="image1" src="http://laporan-sac.sac-po.com/storage/{{ $i->img_before }}" data-index="{{ $loop->index }}" data-image="{{ $i->img_before }}" data-imgName="img_before" class="rounded-sm imeg min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]"></td>
                                @else
                                 <td>
                                    <x-no-img class="min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]" />
                                 </td>
                                @endif

                                @if ($i->img_proccess != null)
                                    <td><img id="image2" src="http://laporan-sac.sac-po.com/storage/{{ $i->img_proccess }}" data-index="{{ $loop->index }}" data-image="{{ $i->img_proccess }}" data-imgName="img_proccess" class="rounded-sm imeg min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]"></td>
                                @else
                                 <td>
                                    <x-no-img class="min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]" />
                                 </td>
                                @endif

                                 @if ($i->img_final != null)
                                    <td><img id="image3" src="http://laporan-sac.sac-po.com/storage/{{ $i->img_final }}" data-index="{{ $loop->index }}" data-image="{{ $i->img_final }}" data-imgName="img_final" class="rounded-sm imeg min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]"></td>
                                @else
                                 <td>
                                    <x-no-img class="min-w-[40px] md:min-w-[90px] max-w-[80px] md:max-w-[160px]" />
                                 </td>
                                @endif

                                @if(Auth::user()->devisi_id == 8)
                                    <td>{{ $i->note }}</td>
                                    <td style="min-width: 100px; text-align: center;">{{ $i->created_at->format('Y-m-d') }}</td>
                                    <!--<td>-->
                                    <!--    <div class="overflow-hidden ">-->
                                    <!--        <a href="{{ route('mitra_laporan.show', $i->id) }}" class="text-sky-400 hover:text-sky-500 text-xl transition-all ease-linear .2s"><i class="ri-eye-fill"></i></a>-->
                                    <!--    </div>-->
                                    <!--</td>-->
                                @else
                                    <td>{{ $i->keterangan }}</td>
                                    <td style="min-width: 100px; text-align: center;">{{ $i->created_at->format('Y-m-d') }}</td>
                                @endif
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        ~ Data Kosong ~
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                    <div id="pag-1" class="mx-10 mt-5 mb-5">
                        {{ $laporan->links() }}
                    </div>
                    <div class="flex justify-center w-full sm:justify-end">
		                @if(Auth::user()->divisi->code_jabatan == "CO-CS")
            			    <a href="{{ route('leaderView') }}" class="btn btn-error">Kembali</a>
            		    @elseif(Auth::user()->divisi->jabatan->code_jabatan == "CO-SCR")
            			    <a href="{{ route('danruView') }}" class="btn btn-error">Kembali</a>
            		    @else
            			    <a href="{{ route('dashboard.index') }}" class="btn btn-error">Kembali</a>
            		    @endif
                    </div>

                    <div id="divImeg" style="z-index: 9000; gap: 1rem; display: none; backdrop-filter: blur(8px); " class="fixed top-0 flex flex-col items-center justify-center w-full min-h-screen overflow-hidden bg-slate-500/10">
                        <div  style="z-index: 9001; inset: 0px; padding: 20px;" class="top-0 flex flex-col items-center justify-center rounded-lg bg-slate-100">
                            <div id="showImage" class="flex items-center justify-center">

                            </div>
                        </div>
                        <div class="flex items-center justify-center">
                            <button id="closeMod" class="btn btn-error">&times;</button>
                        </div>
                    </div>
            </div>
        </div>
    <script>
        $(document).ready(function() {
            var getData = {!! json_encode($laporan) !!};
            $('.imeg').click(function() {

                var imegId = $(this).attr('id');
                var index = $(this).data('index');
                var imageName = $(this).data('imgname');
                let imgData = getData.data[index];
                var imegUri = 'http://laporan-sac.sac-po.com/storage/' + imgData[imageName];

                $('#showImage').html(
                    `<img src="${imegUri}" style="width: 100%; max-width: 260px;">`
                )
                $('#divImeg').fadeIn('fast');

                $(window).on('scroll.disableScroll', function() {
                    $(this).scrollTop(0).scrollLeft(0);
                });

                // Disable scrolling on the document
                $(document).on('scroll.disableScroll', function() {
                    $(this).scrollTop(0).scrollLeft(0);
                });

                // Disable touchmove events
                $(document).on('touchmove.disableScroll', function(event) {
                    event.preventDefault();
                });

                // Disable mousewheel events
                $(document).on('mousewheel.disableScroll', function(event) {
                    event.preventDefault();
                });


                // console.log(imegId, getData.data[index][imageName], imageName);
            })
            $('#closeMod').click(function(){
                $('#divImeg').fadeOut('fast');

                $(window).off('scroll.disableScroll');
                $(document).off('scroll.disableScroll');
                $(document).off('touchmove.disableScroll');
                $(document).off('mousewheel.disableScroll');
            })
        })
    </script>
            </div>
        </div>
    </div>
</x-app-layout>
