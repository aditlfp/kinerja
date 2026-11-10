                @if (count($hitungNews) > 0)
                    <div>
                        @if (session()->has('is_modal'))
                            <!-- Display your modal here -->
                            <div class="modalNews">
                                <div style="z-index: 9000;"
                                    class="fixed inset-0 flex items-center justify-center w-full h-screen transition-all duration-300 ease-in-out bg-slate-500/10 backdrop-blur-xs">
                                    <div class="flex items-center justify-center">
                                        <div style="z-index: 9001;"
                                            class="relative inset-0 p-3 mx-10 my-10 rounded-md shadow-sm bg-slate-200 w-fit">
                                            @if (Carbon\Carbon::now()->lessThan(Carbon\Carbon::parse('2025-04-09')))
                                                <img src="{{ URL::asset('/logo/ketupat-3.png') }}" width="24%"
                                                    class="hanging"
                                                    style="z-index: 9000; left: 0px; padding: 10px; border-radius: 100%; filter: drop-shadow(0 3px 3px rgb(0 0 0 / 0.15));" />
                                                <img src="{{ URL::asset('/logo/ketupat-1.png') }}" width="24%"
                                                    class="hanging2"
                                                    style="z-index: 8999; left: 0px; padding: 10px; border-radius: 100%; filter: drop-shadow(0 3px 3px rgb(0 0 0 / 0.15));" />
                                            @endif
                                            <div class="flex justify-end mb-3">
                                                <button class="scale-90 btn btn-error closeNews">&times;</button>
                                            </div>
                                            <div class="flex w-full overflow-x-auto carousel divImage">
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @forelse($hitungNews as $new)
                                                    <a id="slide{{ $no++ }}"
                                                        class="relative carousel-item w-fit"
                                                        href="{{ route('newsDownload', $new->id) }}">
                                                        <img class="akuImage" id="akuImage"
                                                            src="{{ asset('storage/images/' . $new->image) }}"
                                                            data-src="{{ asset('storage/images/' . $new->image) }}"
                                                            alt="data-berita-image" />

                                                    </a>
                                                @empty
                                                @endforelse
                                            </div>
                                            @if (count($hitungNews) > 1)
                                                <div class="flex items-center justify-center mt-3">
                                                    <span
                                                        class="text-xs font-semibold text-center text-slate-700">Geser
                                                        untuk melihat berita lainnya</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                session()->forget('is_modal');
                            @endphp
                        @endif
                    </div>
                @endif