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
            row.find('.photo-input').on('change', function () {
                const preview = row.find('.photo-preview').empty();
                row.find('.photo-placeholder').toggleClass('hidden', this.files.length > 0);
                [...this.files].forEach(file => {
                    if (!file.type.startsWith('image/')) return;
                    preview.append(`<img src="${URL.createObjectURL(file)}" alt="Preview ${file.name}" class="h-20 w-full rounded-lg object-cover ring-1 ring-slate-200">`);
                });
                row.find('.photo-names').text([...this.files].map(file => file.name).join(', '));
            });
            row.find('.remove-job').on('click', function () {
                if (jobs.find('.job-row').length > 1) { row.remove(); renumber(); }
            });
            syncRowLegacy(row);
            syncRow(row);
        }

        jobs.find('.job-row').each(function () { bind($(this)); });
        renumber();
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
