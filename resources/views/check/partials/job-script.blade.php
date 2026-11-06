<script>
    $(function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                $('#latitude').val(position.coords.latitude);
                $('#longtitude').val(position.coords.longitude);
            });
        }

        const jobs = $('#jobs');
        const isEdit = @json($isEdit ?? false);
        let rowIndex = jobs.find('.job-row').length;
        jobs.find('.job-row').each(function () {
            const row = $(this);
            const existing = row.find('.original-index').val();
            const stable = existing !== undefined && existing !== '' ? existing : rowIndex++;
            row.attr('data-stable-index', stable);
        });

        function applyIndex(row, visualIndex) {
            let stableIndex = row.attr('data-stable-index');
            if (stableIndex === undefined) {
                stableIndex = row.find('.original-index').val() || rowIndex++;
                row.attr('data-stable-index', stableIndex);
            }
            row.attr('data-index', stableIndex);
            row.find('.job-number').text(visualIndex + 1);
            row.find('.original-index').attr('name', `original_index[${stableIndex}]`).val(stableIndex);
            row.find('.photo-input').attr('name', `img[${stableIndex}][]`);
            row.find('.existing-images').attr('name', `existing_img[${stableIndex}][]`);
            row.find('.existing-image').attr('name', `existing_img[${stableIndex}][]`);
            row.find('.deskripsi-input').attr('name', `deskripsi[${stableIndex}]`);
            row.find('.tanggal-input').attr('name', `tanggal[${stableIndex}]`);
            row.find('.approve-input').attr('name', `approve_status[${stableIndex}]`);
            row.find('.job-value').attr('name', `pekerjaan_id[${stableIndex}]`);
            row.find('.item-id').attr('name', `item_id[${stableIndex}]`);
            row.find('.manual-name').attr('name', `input_manual[${stableIndex}]`);
        }
        function renumber() {
            jobs.find('.job-row').each(function (i) { applyIndex($(this), i); });
        }
        function syncRow(row) {
            const manual = row.find('.job-select').val() === 'manual';
            const selected = row.find('.job-select option:selected');
            const label = manual ? row.find('.manual-name').val() || 'Pilih Untuk Ketik manual' : (selected.text() || 'Pilih dari daftar pekerjaan');
            row.find('.job-label').text(label).toggleClass('text-slate-400', !row.find('.job-select').val()).toggleClass('text-slate-700', !!row.find('.job-select').val());
        }
        function closeDropdowns() {
            $('.job-menu').addClass('hidden');
            $('.job-chevron').removeClass('rotate-180');
            $('.job-row').css('z-index', '');
        }

        function bindDropdown(row) {
            const trigger = row.find('.job-trigger');
            const menu = row.find('.job-menu');

            trigger.on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const isClosed = menu.hasClass('hidden');

                closeDropdowns();

                if (isClosed) {
                    const rect = trigger[0].getBoundingClientRect();
                    const menuHeight = Math.min(menu[0].scrollHeight, 240);
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const top = spaceBelow < menuHeight + 12 ? Math.max(8, rect.top - menuHeight - 8) : rect.bottom + 8;
                    menu.css({ top: `${top}px`, left: `${rect.left}px`, width: `${rect.width}px` });
                    row.css('z-index', '60');
                    menu.removeClass('hidden');
                    trigger.find('.job-chevron').addClass('rotate-180');
                } else {
                    row.css('z-index', '');
                }
            });

            row.find('.job-option').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const value = $(this).data('value');

                row.find('.job-select')
                    .val(value)
                    .trigger('change');

                closeDropdowns();
            });
        }
        function syncRowLegacy(row) {
            const manual = row.find('.job-select').val() === 'manual';
            row.find('.manual-name').toggleClass('hidden', !manual).prop('required', manual);
            row.find('.job-value').val(manual ? 'manual' : row.find('.job-select').val());
        }
        function bind(row) {
            row.find('.job-select').on('change', function () { syncRowLegacy(row); syncRow(row); });
            row.find('.manual-name').on('input', function () {
                row.find('.job-select').val('manual');
                row.find('.job-value').val('manual');
                syncRow(row);
            });
            bindDropdown(row);
            // The staged list is the single source of truth for what gets
            // uploaded. A file input is replaced on every new selection, so
            // reading input.files here used to drop everything picked before
            // and duplicate what was just picked (the change event hands over
            // the same list the input now holds).
            const rebuildFileInput = (row) => {
                const staged = row.data('photo-files') || [];
                const transfer = new DataTransfer();
                staged.forEach((file) => transfer.items.add(file));
                row.find('.photo-input')[0].files = transfer.files;
                row.find('.photo-names').text(staged.map((file) => file.name).join(', '));
            };

            const updatePhotos = function (files) {
                const activeRow = this instanceof HTMLElement ? $(this) : this;
                const input = activeRow.find('.photo-input')[0];
                const editingPhoto = $(input).data('editing-photo');
                const staged = activeRow.data('photo-files') || [];

                if (editingPhoto?.length) {
                    // Retaking a staged photo replaces it in place; a saved
                    // photo only leaves the existing_img[] list.
                    const stagedItems = activeRow.find('.photo-preview .photo-item[data-staged]');
                    const position = stagedItems.index(editingPhoto);
                    if (position > -1) staged.splice(position, 1);
                    editingPhoto.find('.existing-image').remove();
                    editingPhoto.remove();
                    $(input).removeData('editing-photo');
                    activeRow.removeData('editing-photo');
                }

                const limit = Number($('#form-cp').data('max-photos')) || 7;
                const picked = Array.from(files).filter((file) => file.type.startsWith('image/'));
                const accepted = picked.slice(0, Math.max(limit - staged.length, 0));

                staged.push(...accepted);
                activeRow.data('photo-files', staged);
                rebuildFileInput(activeRow);

                const overflow = picked.length - accepted.length;
                const warning = activeRow.find('.photo-limit');
                if (overflow > 0) {
                    warning.text(`Maksimal ${limit} foto per pekerjaan. ${overflow} foto terakhir tidak dipakai.`).removeClass('hidden');
                } else {
                    warning.addClass('hidden').text('');
                }

                const preview = activeRow.find('.photo-preview');
                activeRow.find('.photo-placeholder').addClass('hidden');
                accepted.forEach((file) => {
                    const item = $('<div class="photo-item group relative" data-staged="1"></div>');
                    item.append($('<img class="h-full w-full rounded-lg object-cover ring-1 ring-slate-200">').attr({ src: URL.createObjectURL(file), alt: `Preview ${file.name}` }));
                    item.append('<button type="button" class="photo-delete absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow-md" aria-label="Hapus foto"><i class="ri-close-line"></i></button>');
                    item.append('<button type="button" class="photo-edit absolute bottom-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-white shadow-md" aria-label="Edit foto"><i class="ri-pencil-line"></i></button>');
                    preview.append(item);
                });
            };

            row.find('.photo-camera, .photo-gallery').on('change', function (event) {
                event.stopPropagation();
                updatePhotos.call($(this).closest('.job-row'), this.files);
            });
            if (!row.find('.photo-input').prop('disabled')) {
                row.find('.dropzone').on('click', function (event) {
                    if ($(event.target).closest('.photo-delete, .photo-edit').length) return;
                    event.preventDefault();
                    event.stopPropagation();
                    openPhotoModal(row, row.find('.photo-camera')[0], row.find('.photo-gallery')[0]);
                });
            }
            row.on('click', '.photo-delete', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const item = $(this).closest('.photo-item');
                const staged = row.data('photo-files') || [];
                const position = row.find('.photo-preview .photo-item[data-staged]').index(item);
                if (position > -1) {
                    staged.splice(position, 1);
                    row.data('photo-files', staged);
                    rebuildFileInput(row);
                }
                item.remove();
                const preview = row.find('.photo-preview');
                row.find('.photo-placeholder').toggleClass('hidden', preview.children().length > 0);
            });
            row.on('click', '.photo-edit', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const item = $(this).closest('.photo-item');
                row.data('editing-photo', item);
                row.find('.photo-input').data('editing-photo', item);
                openPhotoModal(row, row.find('.photo-camera')[0], row.find('.photo-gallery')[0]);
            });
            row.find('.remove-job').on('click', function () {
                if (jobs.find('.job-row').length > 1) { row.remove(); renumber(); }
            });
            const manualName = row.find('.manual-name').val();
            const isWorkOrderRow = Number(row.find('.original-index').val()) >= 1;
            if (manualName && isWorkOrderRow) {
                row.find('.job-select').val('manual');
                row.find('.job-value').val('manual');
            }
            syncRowLegacy(row);
            syncRow(row);
        }

        jobs.find('.job-row').each(function () { bind($(this)); });
        renumber();

        $('body').append(`
            <div
                id="photo-source-modal"
                class="fixed inset-0 z-[10000] hidden items-center justify-center overflow-hidden bg-slate-950/60 p-3 backdrop-blur-md sm:p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="photo-source-title"
            >
                <div
                    class="photo-modal-panel w-full max-w-md scale-95 opacity-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl transition-opacity duration-150 ease-out"
                >
                    <!-- Header -->
                    <div class="px-6 pt-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-sky-500 text-white">
                                    <i class="ri-image-add-line text-2xl"></i>
                                </div>

                                <h2 id="photo-source-title" class="text-xl font-extrabold tracking-tight text-slate-900">
                                    Tambahkan foto
                                </h2>

                                <p class="mt-1 text-sm leading-5 text-slate-500">
                                    Pilih cara untuk menambahkan foto pekerjaan.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="photo-modal-close flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-colors duration-150 hover:bg-slate-100 hover:text-slate-700"
                                aria-label="Tutup"
                            >
                                <i class="ri-close-line text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="grid gap-3 p-6 sm:grid-cols-2">

                        <!-- Camera -->
                        <button
                            type="button"
                            class="photo-modal-camera group flex min-h-[124px] flex-col items-start justify-between rounded-lg border border-slate-200 bg-slate-50 p-4 text-left transition-colors duration-150 hover:border-sky-300 hover:bg-sky-50"
                        >
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-sky-600 ring-1 ring-slate-200 transition-colors duration-150 group-hover:bg-sky-500 group-hover:text-white group-hover:ring-sky-500">
                                <i class="ri-camera-line text-xl"></i>
                            </span>

                            <span class="mt-4">
                                <span class="block text-sm font-bold text-slate-800">Ambil foto</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Gunakan kamera</span>
                            </span>
                        </button>

                        <!-- Gallery -->
                        <button
                            type="button"
                            class="photo-modal-gallery group flex min-h-[124px] flex-col items-start justify-between rounded-lg border border-slate-200 bg-slate-50 p-4 text-left transition-colors duration-150 hover:border-sky-300 hover:bg-sky-50"
                        >
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-sky-600 ring-1 ring-slate-200 transition-colors duration-150 group-hover:bg-violet-500 group-hover:text-white group-hover:ring-violet-500">
                                <i class="ri-gallery-line text-xl"></i>
                            </span>

                            <span class="mt-4">
                                <span class="block text-sm font-bold text-slate-800">Pilih dari galeri</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Pilih foto yang sudah ada</span>
                            </span>
                        </button>

                    </div>

                    <!-- Footer -->
                    <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-3">
                        <button
                            type="button"
                            class="photo-modal-close w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-500 transition-colors duration-150 hover:bg-slate-100 hover:text-slate-700"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        `);

        const photoModal = $('#photo-source-modal');
        const photoModalPanel = photoModal.find('.photo-modal-panel');

        // Keep one upload target per row; gallery files must submit as img[index][].

        const openPhotoModal = (row, cameraInput, galleryInput) => {
            photoModal
                .data({ row, camera: cameraInput, gallery: galleryInput })
                .removeClass('hidden')
                .addClass('flex');
            $('html, body').addClass('overflow-hidden').css({ overflow: 'hidden', height: '100%', overscrollBehavior: 'none' });

            // trigger animasi setelah elemen ke-render sebagai flex
            requestAnimationFrame(() => {
                photoModalPanel.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            });
        };

        const closePhotoModal = () => {
            photoModalPanel.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');

            setTimeout(() => {
                photoModal.removeClass('flex').addClass('hidden').removeData('row camera gallery');
                $('html, body').removeClass('overflow-hidden').css({ overflow: '', height: '', overscrollBehavior: '' });
            }, 200);
        };

        photoModal.on('click', function (event) {
            if (event.target === this) closePhotoModal();
        });

        photoModal.find('.photo-modal-close').on('click', closePhotoModal);

        // "Ambil foto" must never be a dead end. Ways it can fail here: no
        // camera on the device, an insecure origin, or a WebView that swallows
        // input.click(). In every case the same button opens the gallery,
        // because a photo still has to get attached.
        const notifyPhoto = (message) => {
            if (window.toastr?.info) window.toastr.info(message);
        };

        // A picker that opens blurs the window; a swallowed click does nothing
        // at all. Only the latter counts as dead, so an open camera is never
        // interrupted.
        const watchForDeadPicker = (input, onDead) => {
            let opened = false;
            let timer = null;
            const markOpened = () => { opened = true; };
            const settle = () => {
                clearTimeout(timer);
                window.removeEventListener('blur', markOpened);
            };
            timer = setTimeout(() => {
                window.removeEventListener('blur', markOpened);
                if (!opened && input.files.length === 0) onDead();
            }, 1800);
            window.addEventListener('blur', markOpened);
            input.addEventListener('change', settle, { once: true });
            input.addEventListener('cancel', settle, { once: true });
        };

        // Last resort: when even the gallery tap is swallowed, stop hiding the
        // control and let the person tap the native file input itself.
        const revealNativePicker = (galleryInput) => {
            notifyPhoto('Pemilih berkas diblokir peramban ini. Ketuk tombol di bawah untuk memilih foto.');
            galleryInput.removeClass('hidden').attr('aria-label', 'Pilih foto');
        };

        const openGalleryInstead = (input) => {
            const row = $(input).data('photo-row');
            const gallery = row?.find('.photo-gallery')[0];
            if (!gallery) return;
            // A retake that falls back must still replace the photo it was
            // opened for, so carry the marker over to the gallery input.
            $(gallery).data('editing-photo', $(input).data('editing-photo'));
            notifyPhoto('Kamera tidak dapat dibuka, memilih foto dari galeri.');
            watchForDeadPicker(gallery, () => revealNativePicker($(gallery)));
            gallery.click();
        };

        // false = this device reports no camera, null = unknown, keep trying.
        const cameraLooksAvailable = async () => {
            if (!navigator.mediaDevices?.enumerateDevices) return null;
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                return devices.some((device) => device.kind === 'videoinput');
            } catch (error) {
                return null;
            }
        };

        // Probed once at load. The check must NOT sit between the tap and
        // input.click(): an await there breaks the user-gesture chain and
        // strict WebViews then refuse the click outright.
        let cameraStatus = null;
        cameraLooksAvailable().then((value) => { cameraStatus = value; });

        photoModal.find('.photo-modal-camera').on('click', function () {
            const row = photoModal.data('row');
            const editingPhoto = row?.data('editing-photo');
            const input = photoModal.data('camera');
            if (!input) return;

            $(input).data('editing-photo', editingPhoto);
            if (row) $(input).data('photo-row', row);
            closePhotoModal();

            if (cameraStatus === false) {
                openGalleryInstead(input);
                return;
            }

            watchForDeadPicker(input, () => openGalleryInstead(input));
            input.click();
        });

        photoModal.find('.photo-modal-gallery').on('click', function () {
            const row = photoModal.data('row');
            const editingPhoto = row?.data('editing-photo');
            const input = photoModal.data('gallery');
            if (input) {
                $(input).data('editing-photo', editingPhoto);
                closePhotoModal();
                input.click();
            }
        });

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && !photoModal.hasClass('hidden')) closePhotoModal();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.job-dropdown, .job-menu').length) closeDropdowns();
        });
        $(window).on('resize scroll', closeDropdowns);
        $('#add-job').on('click', function () {
            const row = $($('#job-template').html());
            const stableIndex = rowIndex++;
            row.attr('data-stable-index', stableIndex);
            row.find('.job-select').val('manual');
            row.find('.job-value').val('manual');
            row.find('.job-label').text('Pilih Untuk Ketik manual').removeClass('text-slate-400').addClass('text-slate-700');
            row.find('.manual-name').val(row.find('.manual-name').attr('data-work-order-description') || '').removeClass('hidden').prop('required', true);
            jobs.append(row);
            bind(row);
            renumber();
        });
        $('#form-cp').on('submit', async function (event) {
            event.preventDefault();
            const form = this;
            if (!document.getElementById('checkpoint-upload-modal')) {
                document.body.insertAdjacentHTML('beforeend', `<div id="checkpoint-upload-modal" class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-950/65 p-4" role="dialog" aria-modal="true" aria-labelledby="checkpoint-upload-title"><div class="w-full max-w-md overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg"><div class="h-1 bg-slate-100"><div id="checkpoint-upload-line" class="h-full w-0 bg-sky-600 transition-[width] duration-200 ease-out"></div></div><div class="p-6"><div class="flex items-start gap-3"><i class="ri-upload-2-line mt-0.5 text-xl text-sky-600"></i><div><h2 id="checkpoint-upload-title" class="text-lg font-bold text-slate-900">Mengunggah foto</h2><p class="mt-1 text-sm leading-5 text-slate-500">Jangan meninggalkan halaman sampai upload selesai.</p></div></div><div class="mt-6 flex items-end justify-between"><p id="checkpoint-upload-status" class="text-sm font-semibold text-slate-800">Menyiapkan...</p><p id="checkpoint-upload-percent" class="text-xl font-bold tabular-nums text-sky-600">0%</p></div><div class="mt-2 h-2 overflow-hidden rounded-sm bg-slate-100"><div id="checkpoint-upload-progress" class="h-full w-0 rounded-sm bg-sky-600 transition-[width] duration-200 ease-out"></div></div><p class="mt-4 text-xs text-slate-500">Jangan tutup atau refresh halaman.</p></div></div></div>`);
            }
            const modal = document.getElementById('checkpoint-upload-modal');
            const status = document.getElementById('checkpoint-upload-status');
            const progress = document.getElementById('checkpoint-upload-progress');
            const progressLine = document.getElementById('checkpoint-upload-line');
            const percent = document.getElementById('checkpoint-upload-percent');
            let leaving = true;
            const warnLeaving = (e) => { if (leaving) { e.preventDefault(); e.returnValue = ''; } };
            window.addEventListener('beforeunload', warnLeaving);
            const seen = new Set();
            const uploads = Array.from(form.querySelectorAll('input[type="file"]')).flatMap((input) => Array.from(input.files || []).map((file) => ({ file, input }))).filter(({ file }) => {
                const key = `${file.name}:${file.size}:${file.lastModified}`;
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            });
            const totalBytes = uploads.reduce((total, upload) => total + upload.file.size, 0);
            if (totalBytes > 20 * 1024 * 1024) {
                window.toastr?.error('Total ukuran foto maksimal 20 MB per simpan.');
                return;
            }
            const button = $('#submit-job').prop('disabled', true).text('Mengunggah foto...');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('[name="_token"]').value;
            const chunkSize = 1024 * 1024;
            try {
                const totalChunks = uploads.reduce((sum, { file }) => sum + Math.ceil(file.size / chunkSize), 0);
                let completedChunks = 0;
                status.textContent = `Menyiapkan ${uploads.length} foto...`;
                for (const [fileIndex, { file, input }] of uploads.entries()) {
                    const uploadId = crypto.randomUUID();
                    const total = Math.ceil(file.size / chunkSize);
                    const row = $(input).closest('.job-row');
                    const rowIndex = row.attr('data-stable-index') || '0';
                    for (let i = 0; i < total; i++) {
                        status.textContent = `Mengunggah foto ${fileIndex + 1}/${uploads.length} (bagian ${i + 1}/${total})...`;
                        const body = new FormData();
                        body.append('_token', csrf); body.append('upload_id', uploadId); body.append('chunk', i); body.append('total', total);
                        body.append('file', file.slice(i * chunkSize, Math.min(file.size, (i + 1) * chunkSize)), file.name);
                        const response = await fetch('{{ route('checkpoint-upload.chunk') }}', { method: 'POST', body });
                        if (!response.ok) throw new Error('Chunk upload gagal');
                        completedChunks++;
                        const value = Math.round(completedChunks / totalChunks * 100);
                        progress.style.width = `${value}%`;
                        progressLine.style.width = `${value}%`;
                        percent.textContent = `${value}%`;
                    }
                    const complete = new FormData(); complete.append('_token', csrf); complete.append('upload_id', uploadId);
                    const response = await fetch('{{ route('checkpoint-upload.complete') }}', { method: 'POST', body: complete });
                    if (!response.ok) throw new Error('Assembly foto gagal');
                    const token = (await response.json()).token;
                    const hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = `img_token[${rowIndex}][]`; hidden.value = token; form.appendChild(hidden);
                }
                form.querySelectorAll('input[type="file"]').forEach((input) => {
                    input.value = '';
                    input.disabled = true;
                });
                leaving = false;
                window.removeEventListener('beforeunload', warnLeaving);
                form.submit();
            } catch (error) {
                leaving = false;
                window.removeEventListener('beforeunload', warnLeaving);
                modal.remove();
                button.prop('disabled', false).text('Simpan pekerjaan');
                window.toastr?.error(error.message || 'Upload foto gagal.');
            }
        });

    });

    $(document).on('input', '.job-search', function () {
        const search = $(this);
        const keyword = search.val().toLowerCase().trim();
        const dropdown = search.closest('.job-dropdown');
        let found = false;

        dropdown.find('.job-option').each(function () {
            const match = $(this).text().toLowerCase().includes(keyword);
            $(this).toggleClass('hidden', !match);
            found ||= match;
        });

        dropdown.find('.job-no-result').toggleClass('hidden', found);
    });
</script>
