@if (session('toast'))
    <div id="session-toast"
        class="fixed left-1/2 top-4 z-99999 w-[calc(100%-1.5rem)] max-w-[400px] -translate-x-1/2 overflow-hidden rounded-xl border bg-white shadow-[0_12px_32px_rgba(15,23,42,0.15)] ring-1 ring-slate-900/5"
        data-type="{{ session('toast.type') }}">
        <div class="relative px-3.5 py-3">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full" data-toast-icon-wrap>
                    <i class="ri-check-line text-base" data-toast-icon></i>
                </div>
                <div class="min-w-0 flex-1 pr-7">
                    <div class="flex items-center gap-2">
                        <i class="text-base" data-toast-title-icon></i>
                        <p class="text-[0.9rem] font-bold leading-[1.2]" data-toast-title>Berhasil</p>
                    </div>
                    <p class="mt-1 text-[0.78rem] font-medium leading-[1.4] text-slate-600" data-toast-message>
                        {{ session('toast.message') }}
                    </p>
                    <p class="mt-1.5 text-[0.7rem] text-slate-500" data-toast-timer>
                        Menutup dalam <span data-toast-countdown>3</span> detik. <button type="button" class="font-semibold text-slate-700 hover:text-slate-900" data-toast-stop>Hentikan</button>
                    </p>
                </div>
                <button type="button"
                    class="absolute right-2.5 top-2.5 inline-flex h-6 w-6 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-500/10 hover:text-slate-700"
                    data-toast-close aria-label="Tutup notifikasi">
                    <i class="ri-close-line text-sm"></i>
                </button>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-[2.5px] bg-slate-200/50">
            <div class="h-full origin-left" data-toast-progress></div>
        </div>
    </div>
@endif

<script>
    (function() {
        if (window.showAppToast) {
            const existingToast = document.getElementById('session-toast');
            if (existingToast && !existingToast.dataset.bound) {
                window.showAppToast(existingToast.dataset.type, existingToast.querySelector('[data-toast-message]')?.textContent?.trim() || '');
                existingToast.remove();
            }
            return;
        }

        const normalizeType = (rawType) => {
            const value = String(rawType || '').toLowerCase().trim();
            if (['success', 'succes', 'ok'].includes(value)) return 'success';
            if (['error', 'errorr', 'danger', 'failed', 'gagal'].includes(value)) return 'error';
            if (['warning', 'warn', 'peringatan'].includes(value)) return 'warning';
            if (['info', 'information'].includes(value)) return 'info';
            return 'info';
        };

        const themes = {
            success: { border: '#e2e8f0', background: '#ffffff', iconBg: '#d1fae5', iconColor: '#059669', title: 'Berhasil', text: '#059669', progress: '#10b981' },
            error: { border: '#e2e8f0', background: '#ffffff', iconBg: '#ffe4e6', iconColor: '#e11d48', title: 'Error', text: '#e11d48', progress: '#f43f5e' },
            warning: { border: '#e2e8f0', background: '#ffffff', iconBg: '#fef3c7', iconColor: '#d97706', title: 'Peringatan', text: '#d97706', progress: '#f59e0b' },
            info: { border: '#e2e8f0', background: '#ffffff', iconBg: '#dbeafe', iconColor: '#0284c7', title: 'Info', text: '#0284c7', progress: '#0ea5e9' },
        };

        const icons = {
            success: 'ri-check-line',
            error: 'ri-close-line',
            warning: 'ri-alert-line',
            info: 'ri-information-line'
        };

        const escapeHtml = (rawText) => String(rawText || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const toastMarkup = (type, message) => `
            <div id="session-toast"
                class="fixed left-1/2 top-4 z-99999 w-[calc(100%-1.5rem)] max-w-[400px] -translate-x-1/2 overflow-hidden rounded-xl border bg-white shadow-[0_12px_32px_rgba(15,23,42,0.15)] ring-1 ring-slate-900/5 opacity-0 -translate-y-2 transition-all duration-200"
                data-type="${type}">
                <div class="relative px-3.5 py-3">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full" data-toast-icon-wrap>
                            <i class="text-base" data-toast-icon></i>
                        </div>
                        <div class="min-w-0 flex-1 pr-7">
                            <div class="flex items-center gap-2">
                                <i class="text-base" data-toast-title-icon></i>
                                <p class="text-[0.9rem] font-bold leading-[1.2]" data-toast-title></p>
                            </div>
                            <p class="mt-1 text-[0.78rem] font-medium leading-[1.4] text-slate-600" data-toast-message>${message}</p>
                            <p class="mt-1.5 text-[0.7rem] text-slate-500" data-toast-timer>
                                Menutup dalam <span data-toast-countdown>3</span> detik. <button type="button" class="font-semibold text-slate-700 hover:text-slate-900" data-toast-stop>Hentikan</button>
                            </p>
                        </div>
                        <button type="button"
                            class="absolute right-2.5 top-2.5 inline-flex h-6 w-6 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-500/10 hover:text-slate-700"
                            data-toast-close aria-label="Tutup notifikasi">
                            <i class="ri-close-line text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="absolute inset-x-0 bottom-0 h-[2.5px] bg-slate-200/50">
                    <div class="h-full origin-left" data-toast-progress></div>
                </div>
            </div>`;

        const bindToast = (toast) => {
            if (!toast) return;

            toast.dataset.bound = '1';

            const type = normalizeType(toast.dataset.type);
            const theme = themes[type] ?? themes.info;
            const iconWrap = toast.querySelector('[data-toast-icon-wrap]');
            const icon = toast.querySelector('[data-toast-icon]');
            const titleIcon = toast.querySelector('[data-toast-title-icon]');
            const title = toast.querySelector('[data-toast-title]');
            const progress = toast.querySelector('[data-toast-progress]');
            const countdown = toast.querySelector('[data-toast-countdown]');
            const stopBtn = toast.querySelector('[data-toast-stop]');
            const closeBtn = toast.querySelector('[data-toast-close]');
            const timerText = toast.querySelector('[data-toast-timer]');

            toast.style.borderColor = theme.border;
            toast.style.background = theme.background;
            if (iconWrap) iconWrap.style.background = theme.iconBg;
            if (icon) {
                icon.className = `${icons[type] ?? icons.info} text-lg`;
                icon.style.color = theme.iconColor;
            }
            if (titleIcon) {
                titleIcon.className = `${icons[type] ?? icons.info} text-base`;
                titleIcon.style.color = theme.iconColor;
            }
            if (title) {
                title.textContent = theme.title;
                title.style.color = theme.text;
            }

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', '-translate-y-2');
            });

            if (progress) {
                progress.style.background = theme.progress;
                progress.style.transition = 'transform 3000ms linear';
                progress.style.transformOrigin = 'left';
                requestAnimationFrame(() => {
                    progress.style.transform = 'scaleX(0)';
                });
            }

            let timeLeft = 3;
            let timerInterval = null;
            let isStopped = false;

            const updateCountdown = () => {
                if (countdown) countdown.textContent = timeLeft;
            };

            const closeToast = () => {
                if (timerInterval) clearInterval(timerInterval);
                toast.classList.add('opacity-0', 'translate-y-1', 'pointer-events-none');
                setTimeout(() => toast.remove(), 220);
            };

            timerInterval = setInterval(() => {
                if (isStopped) return;
                timeLeft -= 1;
                updateCountdown();
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    closeToast();
                }
            }, 1000);

            stopBtn?.addEventListener('click', () => {
                isStopped = true;
                if (timerInterval) clearInterval(timerInterval);
                if (progress) progress.style.transition = 'none';
                if (timerText) timerText.innerHTML = 'Timer dihentikan.';
            });

            closeBtn?.addEventListener('click', closeToast);
            updateCountdown();
        };

        window.showAppToast = (rawType, rawMessage) => {
            const type = normalizeType(rawType);
            const message = escapeHtml(String(rawMessage || '').trim());

            const existingToast = document.getElementById('session-toast');
            if (existingToast) {
                existingToast.remove();
            }

            document.body.insertAdjacentHTML('beforeend', toastMarkup(type, message));
            bindToast(document.getElementById('session-toast'));
        };

        const initialToast = document.getElementById('session-toast');
        if (initialToast) {
            const initialType = initialToast.dataset.type;
            const initialMessage = initialToast.querySelector('[data-toast-message]')?.textContent?.trim() || '';
            initialToast.remove();
            window.showAppToast(initialType, initialMessage);
        }
    })();
</script>
