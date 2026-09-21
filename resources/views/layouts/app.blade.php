<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME', 'Kinerja SAC-PONOROGO') }}</title>
    <link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <!-- Webcam CDN -->
    <script src="{{ URL::asset('js/webcam.min.js') }}"></script>
    <script src="{{ URL::asset('js/jqueryNew.min.js') }}"></script>
    @stack('scripts')


    <style>
        @keyframes fadeSlide { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        *,
        body,
        html {
            overflow-x: hidden;
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased bg-slate-400">
    <div class="min-h-screen">
        @if (request()->routeIs('checkpoint-user.*'))
            @include('check.partials.sidebar')
        @elseif (request()->routeIs('direksi.cp.*'))
            @include('direksi.checkpoint.partials.direksi-sidebar')
        @else
            @include('layouts.navbar')
        @endif

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="{{ (request()->routeIs('checkpoint-user.*') || request()->routeIs('direksi.cp.*')) ? 'lg:pl-[17.5rem] pt-14 lg:pt-0 bg-[#f4f6f8] min-h-screen' : '' }}">
            <div class="{{ (request()->routeIs('checkpoint-user.*') || request()->routeIs('direksi.cp.*')) ? 'motion-safe:animate-[fadeSlide_.5s_cubic-bezier(.22,1,.36,1)]' : '' }}">
                {{ $slot }}
            </div>
        </main>

    </div>
    {{-- <div class="flex justify-center">
        <div class="fixed bottom-0 z-[999]">
            <x-menu-mobile />
        </div>
    </div> --}}
    <div id="global-confirm-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-confirm-overlay></div>
        <div class="relative w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-900/5">
            <div class="p-5">
                <div class="flex items-start gap-4">
                    <div id="global-confirm-icon-wrap"
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100">
                        <i id="global-confirm-icon" class="ri-alert-line text-xl text-amber-600"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 id="global-confirm-title" class="text-base font-semibold text-slate-900">Konfirmasi</h3>
                        <p id="global-confirm-message" class="mt-2 text-sm leading-6 text-slate-600">Apakah Anda yakin?
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4">
                <button type="button" id="global-confirm-cancel"
                    class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" id="global-confirm-ok"
                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
    <!-- cdnjs -->
    <script type="text/javascript" src="{{ URL::asset('js/jquery.lazy.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.lazy.plugins.min.js') }}"></script>
    <x-flasher />
    <x-session-toast />
    <x-flasher-theme />
    <x-analytic-component />
    <script>
        window.openConfirmModal = function(config = {}) {
            const modal = document.getElementById('global-confirm-modal');
            if (!modal) return;

            const title = document.getElementById('global-confirm-title');
            const message = document.getElementById('global-confirm-message');
            const okBtn = document.getElementById('global-confirm-ok');
            const cancelBtn = document.getElementById('global-confirm-cancel');
            const overlay = modal.querySelector('[data-confirm-overlay]');
            const iconWrap = document.getElementById('global-confirm-icon-wrap');
            const icon = document.getElementById('global-confirm-icon');

            const themes = {
                danger: {
                    wrap: 'bg-rose-100',
                    icon: 'ri-error-warning-line text-rose-600',
                    button: 'bg-rose-600 hover:bg-rose-700'
                },
                warning: {
                    wrap: 'bg-amber-100',
                    icon: 'ri-alert-line text-amber-600',
                    button: 'bg-amber-500 hover:bg-amber-600'
                },
                info: {
                    wrap: 'bg-sky-100',
                    icon: 'ri-information-line text-sky-600',
                    button: 'bg-sky-600 hover:bg-sky-700'
                },
                success: {
                    wrap: 'bg-emerald-100',
                    icon: 'ri-checkbox-circle-line text-emerald-600',
                    button: 'bg-emerald-600 hover:bg-emerald-700'
                }
            };

            const theme = themes[config.type || 'warning'] || themes.warning;
            title.textContent = config.title || 'Konfirmasi';
            message.textContent = config.message || 'Apakah Anda yakin?';
            okBtn.textContent = config.confirmText || 'Ya, Lanjutkan';
            cancelBtn.textContent = config.cancelText || 'Batal';

            iconWrap.className = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full ' + theme.wrap;
            icon.className = theme.icon + ' text-xl';
            okBtn.className =
                'inline-flex flex-1 items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition ' +
                theme.button;

            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                okBtn.onclick = null;
                cancelBtn.onclick = null;
                overlay.onclick = null;
                document.onkeydown = null;
            };

            okBtn.onclick = () => {
                close();
                if (typeof config.onConfirm === 'function') {
                    config.onConfirm();
                }
            };

            cancelBtn.onclick = close;
            overlay.onclick = close;
            document.onkeydown = (event) => {
                if (event.key === 'Escape') close();
            };

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        $(document).ready(function() {
            $("#searchInput").on("keyup", function() {
                let value = $(this).val().toLowerCase();
                $("#searchTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            $('#nav-btn').click(function() {
                $('#mobile-menu').addClass('absolute');
                $('#mobile-menu').toggle();
            });

            if ($.fn.lazy) {
                $('.lazy').lazy({
                    scrollDirection: 'vertical',
                    effect: 'fadeIn',
                    visibleOnly: true,
                    onError: function(element) {
                        console.log('error loading ' + element.data('src'));
                    }
                });
            } else {
                $('.lazy[data-src]').each(function() {
                    const $element = $(this);
                    $element.attr('src', $element.data('src'));
                });
            }
        });
        //input ++

        $(document).ready(function() {
            var count = 1
            $('#add').click(function() {
                var input = $(
                    '<input class="my-2 input input-bordered" placeholder="Add Name ...." name="name[]" type="text"/>'
                );
                $('#inputContainer').append(input);

                count++
            });
        });


        //End input ++

        // Preview Script
        $(document).ready(function() {
            $('#img').change(function() {
                const input = $(this)[0];
                const preview = $('.preview');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.show();
                        preview.find('.img1').attr('src', e.target.result);
                        preview.removeClass('hidden');
                        preview.find('.img1').addClass('rounded-md shadow-md my-4');
                        $('.iImage1').removeClass('flex').addClass('hidden');
                    };

                    reader.readAsDataURL(input.files[0]);
                }



                // handle rate

                var $tableRows = $('#searchTable tbody tr');

                $('#searchInput').on('keyup', function() {
                    var value = $(this).val().toLowerCase();

                    // Show all rows initially
                    $tableRows.show();

                    // Filter rows based on the search text
                    $tableRows.filter(function() {
                        return $(this).text().toLowerCase().indexOf(value) == -1;
                    }).hide();
                });

            });
            $('#img2').change(function() {
                const input = $(this)[0];
                const preview = $('.preview2');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.show();
                        preview.find('.img2').attr('src', e.target.result);
                        preview.removeClass('hidden');
                        preview.find('.img2').addClass('rounded-md shadow-md my-4');
                        $('.iImage2').removeClass('flex').addClass('hidden');
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            });
            $('#img3').change(function() {
                const input = $(this)[0];
                const preview = $('.preview3');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.show();
                        preview.find('.img3').attr('src', e.target.result);
                        preview.removeClass('hidden');
                        preview.find('.img3').addClass('rounded-md shadow-md my-4');
                        $('.iImage3').removeClass('flex').addClass('hidden');
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            });
            $('#img4').change(function() {
                const input = $(this)[0];
                const preview = $('.preview4');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.show();
                        preview.find('.img4').attr('src', e.target.result);
                        preview.removeClass('hidden');
                        preview.find('.img4').addClass('rounded-md shadow-md my-4');
                        $('.iImage4').removeClass('flex').addClass('hidden');
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            });
            $('#img5').change(function() {
                const input = $(this)[0];
                const preview = $('.preview5');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.show();
                        preview.find('.img5').attr('src', e.target.result);
                        preview.removeClass('hidden');
                        preview.find('.img5').addClass('rounded-md shadow-md my-4');
                        $('.iImage5').removeClass('flex').addClass('hidden');
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            });

            var btnAbsensi = $("#btnAbsensi");
            var btnRating = $("#btnRating");

            var table = $("#table");
            var table2 = $("#table2");
            var btn2 = $('#btnShow2');
            var menuUser = $('#menuUser');
            var user = $('#user');
            var menu1 = $('.menu1');
            var menu2 = $('.menu2');
            var menu3 = $('.menu3');
            var menu4 = $('.menu4');
            var menu5 = $('.menu5');
            var menu6 = $('.menu6');
            var menu7 = $('.menu7');
            var menu8 = $('.menu8');
            var menu9 = $('.menu9');
            var menu10 = $('.menu10');
            var menu11 = $('.menu11');
            var menu12 = $('.menu12');
            var menu13 = $('.menu13');
            var menu14 = $('.menu14');
            var absen = $('#absen');
            var iPulang = $('.iPulang');
            var iAbsensi = $('.iAbsensi');

            btnAbsensi.click(function() {
                btnRating.toggle();
                $('#isiAbsen').toggle();
                $('#ngabsen').toggle();
                $('#ngeLembur').toggle();
                $('#isiLembur').toggle();
            });

            btnRating.click(function() {
                $('#cekMe').toggle();
                $('#cekRate').toggle();
            });

            // $('#isiAbsen').click(function() {
            // 	table.toggle();
            // 	table.addClass('my-0 sm:my-5 mx-5 shadow-md');
            // });

            $('#btnShow').click(function() {
                $('#pag-1').toggle();
                btn2.toggle();
                table.toggle();
                table.addClass('my-0 sm:my-5 mx-5 shadow-md');
                iPulang.toggle();

            });

            btn2.click(function() {
                table2.toggle();
                table2.addClass('my-0 sm:my-5 mx-0 sm:mx-5 shadow-md');
                iAbsensi.toggle();
            });

            $('#btnUser').click(function() {
                user.toggle();
                $('.btnRuangan').toggle();
                $('#btnAbsen').toggle();
            });

            $('#btnClient').click(function() {
                $('#client').toggle();
                $('.btnArea').toggle();
                $('#btnLembur').toggle();

            });

            $('#btnKerjasama').click(function() {
                $('#kerjasama').toggle();
                $('#btnJabatan').toggle();
                $('.menuCP').toggle();

            });

            $('#btnAbsen').click(function() {
                $('#absen').toggle();
                $('#btnLokasi').toggle();


            });
            $('#btnDevisi').click(function() {
                $('#devisi').toggle();
                $('.btnPoint').toggle();
                $('#btnPerlengkapan').toggle();

            });
            $('#btnPerlengkapan').click(function() {
                $('#perlengkapan').toggle();
                $('#btnJadwal').toggle();

            });
            $('#btnLembur').click(function() {
                $('#lembur').toggle();
                $('#btnLaporan').toggle();


            });
            $('#btnJabatan').click(function() {
                $('#jabatan').toggle();
                $('.menuCP').toggle();
            });
            $('#btnShift').click(function() {
                $('#shift').toggle();
                $('#btnKerjasama').toggle();
                $('#btnJabatan').toggle();
            });
            $('#btnCP').click(function() {
                $('#CP').toggle();
            });
            $('#btnPCP').click(function() {
                $('#PCP').toggle();
            });

            $('#btnLokasi').click(function() {
                $('#lokasi').toggle();
                $('#btnPCP').toggle();
            });

            $('#btnJadwal').click(function() {
                $('#jadwal').toggle();
            });
            $('#btnLaporan').click(function() {
                $('#laporan').toggle();
            });
            $('#btnNews').click(function() {
                $('#News').toggle();
            });
            $('#btnSubArea').click(function() {
                $('#SubArea').toggle();
            });
            $('#btnChecklist').click(function() {
                $('#Checklist').toggle();
            });
            $('#btnListPekerjaan').click(function() {
                $('#listPekerjaan').toggle();
            });

        });
        $(document).ready(function() {
            $(document).on("click", ".myModalBtn", function() {
                var modalId = $(this).attr('id').replace('myModalBtn', '');
                var modal = $('#myModal' + modalId);
                modal.removeClass('hidden ');
                modal.addClass(' inset-0 z-[999]');
            });

            $(document).on("click", ".close", function() {
                var modalId = $(this).closest('.modalz').attr('id').replace('myModal', '');
                var modal = $('#myModal' + modalId);
                modal.removeClass(' inset-0 z-[99]');
                modal.addClass('hidden ');
            });
        });
    </script>
</body>

</html>
