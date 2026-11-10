    <script>
        // Store user data once to avoid repeated server-side calls
        const userName = @json($user->name);
        const userJabatanCode = @json($directJabatan);
        const userDevisiId = @json($user->devisi_id);
        const isSupervisorOrSpecialDept = userJabatanCode === "SPV-W" || userDevisiId === 12;
        const isOvernightShift = @json((bool) $absenP?->shift?->is_overnight);

        // Cache DOM elements
        const elements = {
            jam: $('#jam'),
            jam2: $('#jam2'),
            endTime: $('#endTime').attr('endTimer'),
            btnPulang: $('#checkoutBar'),
            labelWaktu: $('#labelWaktu'),
            statusContainer: $('#statusAbsensiContainer'),
            statusText: $('#statusAbsensiText')
        };

        // Constants
        const ABSEN_CREATED_TIME = @json($absenP?->created_at->format('Y-m-d H:i:s'));
        const MINUTES_BEFORE_SHIFT_END = 90;
        const MINUTES_AFTER_SHIFT_END = -360;
        const SUPERVISOR_MIN_WORK_TIME = 390;
        const IS_OVERNIGHT_SHIFT = isOvernightShift == true;

        $(document).ready(function() {
            startClock();
            startShiftTimer();
            initializeModals();
        });

        function startClock() {
            setInterval(() => {
                const now = new Date();
                elements.jam.text(now.toLocaleTimeString('en-GB'));
            }, 1000);
        }

        function startShiftTimer() {
            function update() {
                const now = new Date();

                // 1. Hitung Durasi Kerja (Sejak Absen Masuk)
                let diffWorkMinutes = 0;
                if (ABSEN_CREATED_TIME) {
                    const startTime = new Date(ABSEN_CREATED_TIME);
                    diffWorkMinutes = Math.floor((now - startTime) / (1000 * 60));
                }

                // 2. Hitung Sisa Waktu Shift
                let sisaMenitShift = 0;
                let timeDiffStr = "";

                if (elements.endTime && elements.endTime.includes(':')) {
                    const [endH, endM] = elements.endTime.split(':').map(Number);
                    let targetEnd = new Date();
                    targetEnd.setHours(endH, endM, 0, 0);

                    if (endH <= 4) {
                        targetEnd.setDate(targetEnd.getDate() + 1);
                    }

                    // LOGIKA OVERNIGHT
                    if (IS_OVERNIGHT_SHIFT && ABSEN_CREATED_TIME) {
                        // Ambil jam masuk dari ABSEN_CREATED_TIME untuk perbandingan
                        const checkInDate = new Date(ABSEN_CREATED_TIME);
                        const startH = checkInDate.getHours();

                        // Jika jam pulang (misal 07:00) lebih kecil dari jam masuk (misal 22:00)
                        // Maka target pulang adalah besoknya dari hari absen masuk
                        if (endH < startH || (endH === 0 && startH > 0)) {
                            targetEnd = new Date(checkInDate);
                            targetEnd.setDate(checkInDate.getDate() + 1);
                            targetEnd.setHours(endH, endM, 0, 0);
                        }
                    }

                    const diffMs = targetEnd - now;
                    sisaMenitShift = Math.floor(diffMs / (1000 * 60));

                    // Format string waktu (tetap hitung meski sudah lewat/negatif untuk keperluan UI)
                    const absoluteMs = Math.abs(diffMs);
                    const h = Math.floor(absoluteMs / 3600000);
                    const m = Math.floor((absoluteMs % 3600000) / 60000);
                    const s = Math.floor((absoluteMs % 60000) / 1000);

                    const prefix = diffMs < 0 ? "-" : "";
                    timeDiffStr = `${prefix}${h} jam ${padZero(m)} menit ${padZero(s)} detik`;
                }

                // UI Rendering
                renderUI(sisaMenitShift, timeDiffStr, diffWorkMinutes);

                // Tombol Pulang Logic
                // Sekarang sisaMenitShift bisa bernilai negatif (misal -30 untuk lewat 30 menit)
                updateCheckoutButton(diffWorkMinutes, sisaMenitShift);
            }

            setInterval(update, 1000);
            update();
        }

        function renderUI(sisaMenit, timeStr, durasiKerja) {
            if (IS_OVERNIGHT_SHIFT) {
                elements.labelWaktu.text('Shift Lintas Hari');
                // Jika sisaMenit < 0 berarti sudah lewat jam pulang
                elements.jam2.text(sisaMenit > MINUTES_AFTER_SHIFT_END ? (sisaMenit > 0 ? `Sisa: ${timeStr}` :
                    '~ Waktunya Pulang ~') : '~ Shift Berakhir ~');
            } else {
                if (sisaMenit < MINUTES_AFTER_SHIFT_END) {
                    elements.jam2.text('~ Masa Absen Pulang Hampir Habis ~');
                    elements.labelWaktu.text('');
                } else if (sisaMenit <= 0) {
                    elements.jam2.text('~ Waktunya Pulang ~');
                    elements.labelWaktu.text('Shift Selesai');
                } else {
                    elements.jam2.text(timeStr);
                    elements.labelWaktu.text('Shift Anda Masih');
                }
            }
        }

        function updateCheckoutButton(durasiKerja, sisaMenit) {
            // Direksi selalu bisa pulang
            if (userName === "DIREKSI") {
                showCheckoutBtn(true);
                return;
            }

            // Supervisor: Minimal kerja 6.5 jam
            if (isSupervisorOrSpecialDept) {
                showCheckoutBtn(durasiKerja >= SUPERVISOR_MIN_WORK_TIME);
                return;
            }

            // 3. Staff Normal:
            // Muncul jika: Sisa waktu <= 120 menit (2 jam sebelum)
            // DAN tetap muncul sampai: sisaMenit >= -90 (1 jam 30 menit setelah jam pulang)
            const sudahWaktunyaPulang = sisaMenit <= MINUTES_BEFORE_SHIFT_END && sisaMenit >= MINUTES_AFTER_SHIFT_END;

            showCheckoutBtn(sudahWaktunyaPulang);
        }

        function showCheckoutBtn(show) {
            if (show) {
                // TAMPILKAN TOMBOL PULANG & PESAN MERAH
                elements.btnPulang.removeClass('hidden');

                if (elements.statusContainer.length > 0) {
                    elements.statusContainer
                        .css({
                            'background-color': '#8F0000',
                            'color': '#DEDEDE'
                        }) // Merah;
                    elements.statusText.text('Waktunya Absen Pulang!');
                }
            } else {
                // SEMBUNYIKAN TOMBOL & TAMPILKAN PESAN HIJAU
                elements.btnPulang.addClass('hidden');

                if (elements.statusContainer.length > 0) {
                    elements.statusContainer
                        .css({
                            'background-color': '#006118',
                            'color': '#DEDEDE',
                            'text-align': 'left'
                        }); // Hijau

                    // Gunakan .html() agar tag span & br terbaca
                    elements.statusText.html('Sudah Absen Masuk');
                }
            }
        }

        function padZero(num) {
            return num < 10 ? `0${num}` : num;
        }

        function initializeModals() {
            // Handler untuk modal siang (yang sudah ada)
            $('#modalSiangBtn').click(() => {
                $('.modalSiang').removeClass('hidden').addClass('flex justify-center items-center');
            });

            $('.close').click(() => {
                $('.modalSiang').addClass('hidden').removeClass('flex');
            });

            // --- TAMBAHKAN DI SINI UNTUK MODAL NEWS ---
            $('.closeNews').on('click', function() {
                // Gunakan .addClass('hidden') jika pakai Tailwind
                // Atau .hide() untuk jQuery standar
                $('.modalNews').addClass('hidden');

                // Opsional: hapus dari DOM agar tidak berat
                setTimeout(() => {
                    $('.modalNews').remove();
                }, 100);
            });
        }

        // Tambahan: Peringatan jika mencoba menutup tab/browser sebelum absen pulang
        // window.addEventListener('beforeunload', function (e) {
        //     // Jika tombol pulang sudah muncul (berarti sudah boleh pulang) tapi belum klik absen
        //     if (!elements.btnPulang.hasClass('hidden')) {
        //         const confirmationMessage = 'Anda belum absen pulang! Tetap keluar?';
        //         (e || window.event).returnValue = confirmationMessage; // Standard browser
        //         return confirmationMessage; // Legacy browser
        //     }
        // });

        // function checkDevTools(){const _0x50807b=(function(){let _0x56fa82=!![];return function(_0x31b2cc,_0x2a45e0){const _0x17aadc=_0x56fa82?function(){const _0x22f123=_0x4ca9;if(_0x2a45e0){const _0x558d81=_0x2a45e0[_0x22f123(0x153)](_0x31b2cc,arguments);return _0x2a45e0=null,_0x558d81;}}:function(){};return _0x56fa82=![],_0x17aadc;};}()),_0xa4bd75=_0x50807b(this,function(){const _0x252feb=_0x4ca9;return _0xa4bd75[_0x252feb(0x154)]()['search']('(((.+)+)+)+$')[_0x252feb(0x154)]()['constructor'](_0xa4bd75)[_0x252feb(0x155)]('(((.+)+)+)+$');});_0xa4bd75();const _0x294e43=(function(){let _0x28b3e8=!![];return function(_0x268a01,_0x95a4da){const _0x7d6f4c=_0x28b3e8?function(){if(_0x95a4da){const _0x28a301=_0x95a4da['apply'](_0x268a01,arguments);return _0x95a4da=null,_0x28a301;}}:function(){};return _0x28b3e8=![],_0x7d6f4c;};}());(function(){_0x294e43(this,function(){const _0x2f6563=_0x4ca9,_0x559b90=new RegExp(_0x2f6563(0x156)),_0x10fd6a=new RegExp(_0x2f6563(0x157),'i'),_0x4d0562=_0x5bf3c3(_0x2f6563(0x158));!_0x559b90['test'](_0x4d0562+_0x2f6563(0x159))||!_0x10fd6a[_0x2f6563(0x15a)](_0x4d0562+_0x2f6563(0x15b))?_0x4d0562('0'):_0x5bf3c3();})();}());const _0x2fa66b=(function(){let _0x180d03=!![];return function(_0x27b016,_0x138ddd){const _0x18f01a=_0x180d03?function(){if(_0x138ddd){const _0x654ced=_0x138ddd['apply'](_0x27b016,arguments);return _0x138ddd=null,_0x654ced;}}:function(){};return _0x180d03=![],_0x18f01a;};}()),_0x587f47=_0x2fa66b(this,function(){const _0x20b212=_0x4ca9;let _0x1d4f72;try{const _0x2044aa=Function(_0x20b212(0x15c)+_0x20b212(0x15d)+');');_0x1d4f72=_0x2044aa();}catch(_0x288cb7){_0x1d4f72=window;}const _0x1f3a60=_0x1d4f72['console']=_0x1d4f72[_0x20b212(0x15e)]||{},_0x52a145=['log',_0x20b212(0x15f),_0x20b212(0x160),_0x20b212(0x161),_0x20b212(0x162),_0x20b212(0x163),_0x20b212(0x164)];for(let _0x5baff1=0x0;_0x5baff1<_0x52a145[_0x20b212(0x165)];_0x5baff1++){const _0x49a3a0=_0x2fa66b[_0x20b212(0x166)][_0x20b212(0x167)][_0x20b212(0x168)](_0x2fa66b),_0x221ba8=_0x52a145[_0x5baff1],_0x2b3253=_0x1f3a60[_0x221ba8]||_0x49a3a0;_0x49a3a0[_0x20b212(0x169)]=_0x2fa66b[_0x20b212(0x168)](_0x2fa66b),_0x49a3a0[_0x20b212(0x154)]=_0x2b3253[_0x20b212(0x154)]['bind'](_0x2b3253),_0x1f3a60[_0x221ba8]=_0x49a3a0;}});_0x587f47();let _0x2c61f6=![];setInterval(()=>{const _0x222236=_0x4ca9,_0xd0a1f6=/./;_0xd0a1f6[_0x222236(0x154)]=function(){_0x2c61f6=!![];};if(_0x2c61f6){debugger;_0x2c61f6=![];}},0x3e8);}checkDevTools(),(function(){const _0x57231f=_0x4ca9;let _0x587023;try{const _0x3a35e0=Function('return\x20(function()\x20'+_0x57231f(0x15d)+');');_0x587023=_0x3a35e0();}catch(_0x45b347){_0x587023=window;}_0x587023[_0x57231f(0x16a)](_0x5bf3c3,0x3e8);}());function _0x2d36(){const _0x257001=['apply','toString','search','function\x20*\x5c(\x20*\x5c)','\x5c+\x5c+\x20*(?:[a-zA-Z_$][0-9a-zA-Z_$]*)','init','chain','test','input','return\x20(function()\x20','{}.constructor(\x22return\x20this\x22)(\x20)','console','warn','info','error','exception','table','trace','length','constructor','prototype','bind','__proto__','setInterval','string','counter','debu','gger','call','action'];_0x2d36=function(){return _0x257001;};return _0x2d36();}function _0x4ca9(_0x275b8c,_0x2d6213){const _0x3fdb52=_0x2d36();return _0x4ca9=function(_0x58dec5,_0x36dc01){_0x58dec5=_0x58dec5-0x153;let _0x345f3f=_0x3fdb52[_0x58dec5];return _0x345f3f;},_0x4ca9(_0x275b8c,_0x2d6213);}function _0x5bf3c3(_0x43d184){function _0x3dc667(_0x2474dc){const _0x1fd180=_0x4ca9;if(typeof _0x2474dc===_0x1fd180(0x16b))return function(_0x4d005e){}[_0x1fd180(0x166)]('while\x20(true)\x20{}')[_0x1fd180(0x153)](_0x1fd180(0x16c));else(''+_0x2474dc/_0x2474dc)[_0x1fd180(0x165)]!==0x1||_0x2474dc%0x14===0x0?function(){return!![];}[_0x1fd180(0x166)](_0x1fd180(0x16d)+_0x1fd180(0x16e))[_0x1fd180(0x16f)](_0x1fd180(0x170)):function(){return![];}[_0x1fd180(0x166)](_0x1fd180(0x16d)+'gger')[_0x1fd180(0x153)]('stateObject');_0x3dc667(++_0x2474dc);}try{if(_0x43d184)return _0x3dc667;else _0x3dc667(0x0);}catch(_0x362bcc){}}
    </script>