<style>
    #session-toast {
        box-sizing: border-box !important;
        top: calc(env(safe-area-inset-top) + 0.75rem) !important;
        left: 0 !important;
        right: 0 !important;
        width: min(320px, calc(100vw - 1.5rem)) !important;
        max-width: calc(100vw - 1.5rem) !important;
        min-width: 0 !important;
        margin-left: auto !important;
        margin-right: auto !important;
        will-change: transform, opacity, border-radius !important;
        border: 1px solid rgb(15 23 42 / 0.08) !important;
        border-radius: 22px !important;
        background: rgb(255 255 255 / 0.88) !important;
        color: #0f172a !important;
        backdrop-filter: blur(18px) saturate(140%) !important;
        -webkit-backdrop-filter: blur(18px) saturate(140%) !important;
        box-shadow: 0 14px 35px rgb(15 23 42 / 0.18), 0 2px 8px rgb(15 23 42 / 0.08) !important;
        isolation: isolate !important;
        transform-origin: 50% 0 !important;
        opacity: 0;
    }

    #session-toast.is-entering {
        animation: session-toast-expand 520ms cubic-bezier(.2, .9, .3, 1) both !important;
    }

    #session-toast [data-toast-title] { color: #0f172a !important; }
    #session-toast [data-toast-message],
    #session-toast [data-toast-title] { overflow-wrap: anywhere !important; word-break: break-word !important; }
    #session-toast [data-toast-icon-wrap] { flex: 0 0 1.75rem !important; }
    #session-toast [data-toast-close] { flex: 0 0 auto !important; }
    #session-toast .min-w-0 { min-width: 0 !important; }
    #session-toast [data-toast-message] { color: #475569 !important; }
    #session-toast [data-toast-progress] { border-radius: 999px; }
    #session-toast .relative.px-3\.5.py-3,
    #session-toast .relative.px-3.py-2\.5 { padding: 0.625rem 0.75rem !important; }
    #session-toast [data-toast-icon-wrap] { width: 1.75rem !important; height: 1.75rem !important; }
    #session-toast [data-toast-title] { font-size: 0.8rem !important; }
    #session-toast [data-toast-message] { font-size: 0.7rem !important; line-height: 1.35 !important; }
    #session-toast [data-toast-timer] { font-size: 0.62rem !important; }


    #session-toast.is-closing {
        animation: session-toast-collapse 360ms cubic-bezier(.4, 0, 1, 1) both !important;
    }

    @keyframes session-toast-expand {
        0% { opacity: 0; transform: translate3d(0, -8px, 0) scaleX(.16) scaleY(.72); border-radius: 999px; }
        55% { opacity: 1; transform: translate3d(0, 1px, 0) scaleX(1.04) scaleY(.94); border-radius: 26px; }
        78% { transform: translate3d(0, 0, 0) scaleX(.98) scaleY(1.02); border-radius: 23px; }
        100% { opacity: 1; transform: translate3d(0, 0, 0) scaleX(1) scaleY(1); border-radius: 22px; }
    }

    @keyframes session-toast-collapse {
        0% { opacity: 1; transform: translate3d(0, 0, 0) scaleX(1) scaleY(1); border-radius: 22px; }
        45% { opacity: 1; transform: translate3d(0, -2px, 0) scaleX(1.03) scaleY(.88); border-radius: 26px; }
        100% { opacity: 0; transform: translate3d(0, -7px, 0) scaleX(.16) scaleY(.72); border-radius: 999px; }
    }

    @media (max-width: 380px) {
        #session-toast {
            width: calc(100vw - 1rem) !important;
            max-width: calc(100vw - 1rem) !important;
            border-radius: 18px !important;
        }

        #session-toast .relative.px-3\\.5.py-3,
        #session-toast .relative.px-3.py-2\\.5 {
            padding: 0.55rem 0.65rem !important;
        }


    }

</style>

@if (session('toast'))
    <div id="session-toast"
        class="fixed inset-x-0 top-4 z-99999 mx-auto w-[calc(100%-1.5rem)] max-w-[320px] overflow-hidden rounded-xl border bg-white shadow-[0_12px_32px_rgba(15,23,42,0.15)] ring-1 ring-slate-900/5"
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
                </div>
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
                class="fixed inset-x-0 top-4 z-99999 mx-auto w-[calc(100%-1.5rem)] max-w-[320px] overflow-hidden rounded-xl border bg-white shadow-[0_12px_32px_rgba(15,23,42,0.15)] ring-1 ring-slate-900/5 opacity-0 -translate-y-2 transition-all duration-200"
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
                        </div>
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
                toast.classList.add('is-entering');
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

            let timerInterval = null;

            const closeToast = () => {
                if (timerInterval) clearInterval(timerInterval);
                toast.classList.remove('is-entering');
                toast.classList.add('is-closing', 'pointer-events-none');
                setTimeout(() => toast.remove(), 360);
            };

            timerInterval = setTimeout(closeToast, 3000);


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

        const showInitialToastAfterLoad = () => {
            const initialToast = document.getElementById('session-toast');
            if (!initialToast) return;

            const initialType = initialToast.dataset.type;
            const initialMessage = initialToast.querySelector('[data-toast-message]')?.textContent?.trim() || '';
            initialToast.remove();
            window.showAppToast(initialType, initialMessage);
        };

        if (document.readyState === 'complete') {
            requestAnimationFrame(showInitialToastAfterLoad);
        } else {
            window.addEventListener('load', showInitialToastAfterLoad, { once: true });
        }
    })();
</script>
