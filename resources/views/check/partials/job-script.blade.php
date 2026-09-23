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
            row.find('.deskripsi-input').attr('name', `deskripsi[${stableIndex}]`);
            row.find('.tanggal-input').attr('name', `tanggal[${stableIndex}]`);
            row.find('.approve-input').attr('name', `approve_status[${stableIndex}]`);
            row.find('.job-value').attr('name', `pekerjaan_id[${stableIndex}]`);
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
            const updatePhotos = files => {
                const input = row.find('.photo-camera')[0];
                const transfer = new DataTransfer();
                [...files].forEach(file => transfer.items.add(file));
                input.files = transfer.files;

                const preview = row.find('.photo-preview').empty();
                row.find('.photo-placeholder').toggleClass('hidden', input.files.length > 0);
                [...input.files].forEach(file => {
                    if (!file.type.startsWith('image/')) return;
                    preview.append(`<img src="${URL.createObjectURL(file)}" alt="Preview ${file.name}" class="h-20 w-full rounded-lg object-cover ring-1 ring-slate-200">`);
                });
                row.find('.photo-names').text([...input.files].map(file => file.name).join(', '));
            };

            row.find('.photo-camera, .photo-gallery').on('change', function () {
                updatePhotos(this.files);
            });
            row.find('.dropzone').on('click', function (event) {
                event.preventDefault();
                const camera = row.find('.photo-camera')[0];
                const gallery = row.find('.photo-gallery')[0];
                const modal = $('#photo-source-modal');

                openPhotoModal(camera, gallery);
            });
            row.find('.remove-job').on('click', function () {
                if (jobs.find('.job-row').length > 1) { row.remove(); renumber(); }
            });
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

        const openPhotoModal = (cameraInput, galleryInput) => {
            photoModal
                .data({ camera: cameraInput, gallery: galleryInput })
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
                photoModal.removeClass('flex').addClass('hidden').removeData('camera gallery');
                $('html, body').removeClass('overflow-hidden').css({ overflow: '', height: '', overscrollBehavior: '' });
            }, 200);
        };

        photoModal.on('click', function (event) {
            if (event.target === this) closePhotoModal();
        });

        photoModal.find('.photo-modal-close').on('click', closePhotoModal);

        photoModal.find('.photo-modal-camera').on('click', function () {
            photoModal.data('camera').click();
            closePhotoModal();
        });

        photoModal.find('.photo-modal-gallery').on('click', function () {
            photoModal.data('gallery').click();
            closePhotoModal();
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
            row.attr('data-stable-index', rowIndex++);
            jobs.append(row);
            bind(row);
            renumber();
        });
        $('#form-cp').on('submit', function () { $('#submit-job').prop('disabled', true).text('Menyimpan...'); });

    });

    const dropdown = document.querySelector('.job-dropdown');
    const searchInput = dropdown.querySelector('.job-search');
    const options = dropdown.querySelectorAll('.job-option');
    const noResult = dropdown.querySelector('.job-no-result');

    searchInput?.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();
        let found = false;

        options.forEach(option => {
            const text = option.textContent.toLowerCase();

            const match = text.includes(keyword);

            option.classList.toggle('hidden', !match);

            if (match) {
                found = true;
            }
        });

        noResult?.classList.toggle('hidden', found);
    });
</script>
