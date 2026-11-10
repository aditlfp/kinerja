<x-app-layout>
    <x-main-div>
            <div
            	style="z-index: 9000;" class="fixed w-full flex justify-center items-center inset-0 bg-slate-500/10 backdrop-blur-xs transition-all duration-300 ease-in-out h-screen">
                <div class="flex justify-center items-center">
                	<div class="bg-slate-200 inset-0 w-fit p-5 mx-10 my-10 rounded-md shadow-sm">
                		<div class="flex justify-end mb-3">
                			<button class="btn btn-error scale-90 close">&times;</button>
                		</div>
                		<div class="overflow-x-scroll">
                    		@foreach($newsId as $news)
                    		    <a class="" href="{{ route('newsDownload', $news->id) }}"><img class="lazy lazy-image" loading="lazy" src="{{asset('storage/images/'.$news->image)}}" data-src="{{asset('storage/images/'.$news->image)}}" alt="data-absensi-image"/></a>
                    		@endforeach
                		</div>
                	</div>
                </div>
            </div>
    </x-main-div>
</x-app-layout>