/*! nselect — skin dropdown kustom di atas <select> native.
 *
 *  Progressive enhancement: setiap <select> layak-konversi dibungkus wrapper
 *  `.ns` berisi tombol + panel daftar; select asli tetap ada (d sembunyikan
 *  secara visual) sehingga form tetap mengirim nilainya, dan event `change`
 *  yang di-dispatch memicu kembali handler inline onchange, jQuery, dan
 *  Alpine (@change / x-model) tanpa perubahan apa pun di halaman.
 *
 *  Opsi dibaca ulang SETIAP panel dibuka, jadi dropdown yang opsi-nya diisi
 *  JS (dependent filter, DataTables, Alpine x-if) tetap benar.
 *
 *  Panel dipindah ke <body> dan diposisikan `fixed`: di aplikasi ini banyak
 *  leluhur yang menjadi scroll container (aturan global overflow-x:hidden,
 *  wrapper tabel .overflow-x-auto), dan panel absolute di dalam wrapper akan
 *  terpotong olehnya.
 */
(function () {
    'use strict';

    var counter = 0;
    var openRoot = null;
    var rafId = 0;

    // Select yang tidak boleh disentuh: data-store tersembunyi, kontrol
    // internal job-row, toolbar debugbar, dan opt-out eksplisit.
    var SKIP = 'select[data-ns],select[multiple],select[aria-hidden="true"],select.hidden,' +
        'select.job-select,select[data-native-select],select.nselect-native,' +
        '[class*="phpdebugbar"] select,#debugbar select,#debugbar_loader select,' +
        '[data-nselect-skip] select,.hide-print select';

    function isSkippable(sel) {
        if (sel.matches(SKIP)) return true;
        // Debugbar menempelkan beberapa panel langsung ke <body>, jadi kenali
        // lewat awalan class-nya, bukan hanya id pembungkus utamanya.
        if (sel.closest('[class*="phpdebugbar"], #debugbar, [data-nselect-skip], .hide-print')) return true;
        return false;
    }

    function of(root, sel) {
        return root.querySelector(sel);
    }

    function isOpen(root) {
        return root.hasAttribute('data-open');
    }

    function panelOf(root) {
        return root._panel;
    }

    function optionsOf(root) {
        var panel = panelOf(root);
        return panel ? panel.querySelectorAll('.ns-o') : [];
    }

    /* ---------- label & opsi ---------- */

    function syncLabel(root) {
        var sel = root.querySelector('select');
        var label = of(root, '.ns-l');
        var btn = of(root, '.ns-t');
        var text = sel.selectedIndex >= 0
            ? (sel.options[sel.selectedIndex].textContent || '').trim()
            : '';
        label.textContent = text || '\u2014';
        btn.disabled = sel.disabled;
        if (sel.disabled) root.setAttribute('data-disabled', '');
        else root.removeAttribute('data-disabled');
    }

    function buildOptions(root) {
        var sel = root.querySelector('select');
        var panel = panelOf(root);
        var list = panel.querySelector('[role="listbox"]');
        var id = list.id;
        list.innerHTML = '';
        var delay = 0;
        var index = -1;

        // `select.options` hanya memuat <option>; optgroup dibaca dari
        // childNodes supaya judul grupnya ikut tampil.
        var nodes = [];
        for (var c = sel.firstChild; c; c = c.nextSibling) nodes.push(c);
        var flat = [];
        nodes.forEach(function (node) {
            if (node.tagName === 'OPTION') flat.push({ option: node, group: null });
            else if (node.tagName === 'OPTGROUP') {
                for (var g = node.firstElementChild; g; g = g.nextElementSibling) {
                    if (g.tagName === 'OPTION') flat.push({ option: g, group: node.label || '' });
                }
            }
        });
        var lastGroup = null;
        flat.forEach(function (entry) {
            var opt = entry.option;
            if (entry.group && entry.group !== lastGroup) {
                lastGroup = entry.group;
                var head = document.createElement('li');
                head.className = 'ns-g';
                head.setAttribute('role', 'presentation');
                head.textContent = entry.group;
                list.appendChild(head);
            } else if (!entry.group) {
                lastGroup = null;
            }
            index++;
            var li = document.createElement('li');
            li.className = 'ns-o';
            li.setAttribute('role', 'option');
            li.id = id + '-o' + index;
            li.dataset.index = String(index);
            li.style.animationDelay = Math.min(delay, 12) * 20 + 'ms';
            delay++;
            if (opt.disabled) {
                li.classList.add('ns-disabled');
                li.setAttribute('aria-disabled', 'true');
            }
            var text = document.createElement('span');
            text.className = 'ns-x';
            text.textContent = (opt.textContent || '').trim();
            var chk = document.createElement('span');
            chk.className = 'ns-chk';
            chk.setAttribute('aria-hidden', 'true');
            chk.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" ' +
                'stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
            li.appendChild(text);
            li.appendChild(chk);
            li.setAttribute('aria-selected', sel.selectedIndex === index ? 'true' : 'false');
            list.appendChild(li);
        });
    }

    /* ---------- posisi panel (fixed, lepas dari overflow leluhur) ---------- */

    function positionPanel(root) {
        var btn = of(root, '.ns-t');
        var panel = panelOf(root);
        var list = panel.querySelector('ul');
        if (!btn || panel.hidden) return;

        var r = btn.getBoundingClientRect();
        var gap = 6;
        var margin = 8;
        var viewportH = document.documentElement.clientHeight;
        var viewportW = document.documentElement.clientWidth;

        panel.style.position = 'fixed';
        panel.style.left = Math.round(Math.max(margin, Math.min(r.left, viewportW - margin - 80))) + 'px';
        panel.style.width = Math.round(Math.min(r.width, viewportW - 2 * margin)) + 'px';
        panel.style.right = 'auto';
        panel.style.bottom = 'auto';

        // Tinggi daftar mengikuti ruang yang tersedia (di bawah atau di atas),
        // lalu panel ditempatkan agar selalu utuh di dalam viewport.
        var spaceBelow = viewportH - r.bottom - gap - margin;
        var spaceAbove = r.top - gap - margin;
        var available = Math.max(96, Math.max(spaceBelow, spaceAbove));

        list.style.maxHeight = '';
        var desired = Math.min(list.scrollHeight, 240);
        list.style.maxHeight = Math.max(64, Math.min(desired, available - 12)) + 'px';

        var panelH = panel.offsetHeight;
        var top = r.bottom + gap;
        if (top + panelH > viewportH - margin && r.top - gap - panelH >= margin) {
            top = r.top - gap - panelH; // tidak muat di bawah: buka ke atas
        }
        top = Math.min(Math.max(top, margin), Math.max(margin, viewportH - margin - panelH));
        panel.style.top = Math.round(top) + 'px';
    }

    function repositionOpen() {
        if (!openRoot) return;
        positionPanel(openRoot);
    }

    function scheduleReposition() {
        if (rafId) return;
        rafId = requestAnimationFrame(function () {
            rafId = 0;
            repositionOpen();
        });
    }

    /* ---------- keadaan panel ---------- */

    function setActive(root, index) {
        var options = optionsOf(root);
        if (!options.length) return;
        index = Math.max(0, Math.min(options.length - 1, index));
        root._activeIndex = index;
        options.forEach(function (opt, i) {
            if (i === index) opt.setAttribute('data-active', '');
            else opt.removeAttribute('data-active');
        });
        var btn = of(root, '.ns-t');
        btn.setAttribute('aria-activedescendant', options[index].id);
        // Gulir hanya daftarnya, jangan pernah menggulir halaman.
        var current = options[index];
        var ul = current.parentElement;
        if (current.offsetTop < ul.scrollTop ||
            current.offsetTop + current.offsetHeight > ul.scrollTop + ul.clientHeight) {
            ul.scrollTop = Math.max(0, current.offsetTop - (ul.clientHeight - current.offsetHeight) / 2);
        }
    }

    function close(root, refocus) {
        root.removeAttribute('data-open');
        panelOf(root).hidden = true;
        var btn = of(root, '.ns-t');
        btn.setAttribute('aria-expanded', 'false');
        btn.removeAttribute('aria-activedescendant');
        if (openRoot === root) openRoot = null;
        if (refocus) btn.focus();
    }

    function open(root) {
        if (openRoot && openRoot !== root) close(openRoot, false);
        var sel = root.querySelector('select');
        var panel = panelOf(root);
        buildOptions(root);
        syncLabel(root);
        // Panel hidup di <body> supaya tidak terpotong scroll container leluhur.
        if (panel.parentNode !== document.body) document.body.appendChild(panel);
        panel.hidden = false;
        root.setAttribute('data-open', '');
        of(root, '.ns-t').setAttribute('aria-expanded', 'true');
        openRoot = root;
        root._activeIndex = -1;
        positionPanel(root);
        setActive(root, sel.selectedIndex < 0 ? 0 : sel.selectedIndex);
    }

    function pick(root, index) {
        var sel = root.querySelector('select');
        var opt = sel.options[index];
        if (!opt || opt.disabled) return;
        sel.selectedIndex = index;
        // Inline onchange, jQuery .change(), Alpine @change / x-model semua
        // bangun dari event change asli pada select.
        sel.dispatchEvent(new Event('change', { bubbles: true }));
        sel.dispatchEvent(new Event('input', { bubbles: true }));
        syncLabel(root);
        close(root, true);
    }

    /* ---------- konversi satu select ---------- */

    function enhance(sel) {
        if (sel.dataset.ns || isSkippable(sel)) return;

        var origStyle = sel.getAttribute('style') || '';
        var origClass = sel.getAttribute('class') || '';
        var fullWidth = /\bw-full\b|\bw-screen\b/.test(origClass) || /width\s*:\s*100%/.test(origStyle);
        var naturalWidth = 0;

        // Select di dalam grid ikut terentang selebar sel, bukan menyusut ke
        // lebar teksnya — ikuti perilaku aslinya, jangan dikunci ke px.
        if (!fullWidth && sel.parentElement) {
            var parentDisplay = getComputedStyle(sel.parentElement).display;
            if (parentDisplay === 'grid' || parentDisplay === 'inline-grid') fullWidth = true;
        }

        if (!fullWidth) {
            // Ukur lebar alami select (di luar layar) agar wrapper tidak
            // melebar penuh pada konteks inline yang semula menyusut.
            sel.style.cssText = origStyle +
                ';position:fixed;left:-99999px;top:0;visibility:hidden;pointer-events:none;' +
                'width:auto;height:auto;max-width:none;display:inline-block;';
            naturalWidth = Math.ceil(sel.getBoundingClientRect().width);
            sel.style.cssText = origStyle;
        }

        sel.dataset.ns = '1';

        var wrap = document.createElement('div');
        // Salin class untuk ukuran/layout (w-full, h-10, min-w…), buang class
        // penampil bawaan daisyUI (.select*) dan overflow yang memotong panel.
        var keep = origClass.split(/\s+/).filter(function (cls) {
            return cls && !/^select(-[a-z]+)?$/.test(cls) && !/^overflow-/.test(cls);
        });
        wrap.className = (keep.join(' ') + ' ns').trim();
        if (origStyle) wrap.setAttribute('style', origStyle);
        if (naturalWidth > 0) {
            wrap.style.width = naturalWidth + 'px';
            wrap.style.maxWidth = '100%';
        }
        wrap.setAttribute('data-ns', '');

        var id = 'ns-' + (++counter);

        var label = document.createElement('span');
        label.className = 'ns-l';
        label.id = id + '-l';

        var chev = document.createElement('span');
        chev.className = 'ns-c';
        chev.setAttribute('aria-hidden', 'true');
        chev.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" ' +
            'stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ns-t';
        btn.setAttribute('data-ns-toggle', '');
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        btn.setAttribute('aria-controls', id + '-list');
        // Nama aksesibel diambil dari select aslinya bila ada.
        var ariaLabel = sel.getAttribute('aria-label');
        var ariaLabelledby = sel.getAttribute('aria-labelledby');
        if (ariaLabel) btn.setAttribute('aria-label', ariaLabel);
        else if (ariaLabelledby) btn.setAttribute('aria-labelledby', ariaLabelledby);
        else btn.setAttribute('aria-labelledby', id + '-l');
        if (sel.disabled) btn.disabled = true;
        btn.appendChild(label);
        btn.appendChild(chev);

        var panel = document.createElement('div');
        panel.className = 'ns-p';
        panel.setAttribute('data-ns-panel', '');
        panel.hidden = true;
        var list = document.createElement('ul');
        list.setAttribute('role', 'listbox');
        list.id = id + '-list';
        if (ariaLabel) list.setAttribute('aria-label', ariaLabel);
        else list.setAttribute('aria-labelledby', id + '-l');
        panel.appendChild(list);
        panel._root = wrap;
        wrap._panel = panel;
        sel._nsRoot = wrap;

        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel); // pindahkan kontrol asli ke dalam wrapper
        wrap.appendChild(btn);

        syncLabel(wrap);

        // Perubahan dari luar (jQuery .val() + trigger('change'), Alpine,
        // filter dependen) ikut memperbarui label & sorotan.
        sel.addEventListener('change', function () {
            syncLabel(wrap);
            if (isOpen(wrap)) {
                buildOptions(wrap);
                setActive(wrap, sel.selectedIndex < 0 ? 0 : sel.selectedIndex);
                positionPanel(wrap);
            }
        });
    }

    /* ---------- satu penginisial untuk semua select ---------- */

    function scan(scope) {
        if (!scope || !scope.querySelectorAll) return;
        scope.querySelectorAll('select').forEach(function (sel) {
            enhance(sel);
        });
    }

    scan(document);

    // Select yang baru lahir (Alpine x-if, DataTables, jQuery) ikut dikonversi,
    // dan panel yatim dibersihkan saat select-nya dilepas dari DOM.
    if (window.MutationObserver && document.body) {
        new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                Array.prototype.forEach.call(m.addedNodes, function (node) {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'SELECT') enhance(node);
                    else scan(node);
                });
                Array.prototype.forEach.call(m.removedNodes, function (node) {
                    if (node.nodeType !== 1) return;
                    var dropped = node.tagName === 'SELECT' ? [node] : (node.querySelectorAll ? node.querySelectorAll('select') : []);
                    Array.prototype.forEach.call(dropped, function (sel) {
                        var root = sel._nsRoot;
                        if (root && root._panel && !document.body.contains(root)) root._panel.remove();
                    });
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    /* ---------- interaksi (delegasi global) ---------- */

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!target || !target.closest) return;

        var toggle = target.closest('[data-ns-toggle]');
        if (toggle) {
            var root = toggle.closest('.ns');
            if (!root) return;
            if (isOpen(root)) close(root, false);
            else open(root);
            return;
        }

        var option = target.closest('.ns-o');
        if (option && !option.classList.contains('ns-disabled')) {
            var optionPanel = option.closest('[data-ns-panel]');
            var optionRoot = optionPanel && optionPanel._root;
            if (optionRoot) pick(optionRoot, parseInt(option.dataset.index, 10) || 0);
            return;
        }

        if (openRoot) {
            var openPanel = panelOf(openRoot);
            var inside = openRoot.contains(target) || (openPanel && openPanel.contains(target));
            if (!inside) close(openRoot, false);
        }
    });

    document.addEventListener('mouseover', function (event) {
        var option = event.target && event.target.closest && event.target.closest('.ns-o');
        if (!option || option.classList.contains('ns-disabled')) return;
        var panel = option.closest('[data-ns-panel]');
        if (panel && panel._root && isOpen(panel._root)) {
            setActive(panel._root, parseInt(option.dataset.index, 10) || 0);
        }
    });

    document.addEventListener('keydown', function (event) {
        var root = openRoot;
        if (!root) return;
        var options = optionsOf(root);
        var key = event.key;

        if (key === 'Escape') { event.preventDefault(); close(root, true); return; }
        if (key === 'Tab') { close(root, false); return; }
        if (key === 'ArrowDown') { event.preventDefault(); setActive(root, (root._activeIndex || 0) + 1); return; }
        if (key === 'ArrowUp') { event.preventDefault(); setActive(root, (root._activeIndex || 0) - 1); return; }
        if (key === 'Home') { event.preventDefault(); setActive(root, 0); return; }
        if (key === 'End') { event.preventDefault(); setActive(root, options.length - 1); return; }
        if (key === 'Enter' || key === ' ' || key === 'Spacebar') {
            if (options[root._activeIndex]) {
                event.preventDefault();
                pick(root, parseInt(options[root._activeIndex].dataset.index, 10) || 0);
            }
        }
    });

    // Buka dengan Enter/Space/panah saat tombol difokus: hentikan event di
    // tombol supaya kebiasaan browser (click sintetis) tidak langsung
    // menutup kembali panel.
    document.addEventListener('keydown', function (event) {
        var btn = event.target && event.target.closest && event.target.closest('[data-ns-toggle]');
        if (!btn) return;
        // Handler global di atas sudah memproses kunci ini (panel terbuka)
        // dan membatalkan default-nya — jangan buka ulang panel setelah pilih.
        if (event.defaultPrevented) return;
        if (['Enter', ' ', 'Spacebar', 'ArrowDown', 'ArrowUp'].indexOf(event.key) === -1) return;
        var root = btn.closest('.ns');
        if (!root || isOpen(root)) return; // panel terbuka: handler global di atas yang urus
        event.preventDefault();
        event.stopPropagation();
        open(root);
        if (event.key === 'ArrowUp') setActive(root, optionsOf(root).length - 1);
    });

    // Panel fixed harus ikut bergerak saat halaman/container bergulir.
    window.addEventListener('scroll', scheduleReposition, true);
    window.addEventListener('resize', scheduleReposition);
})();
