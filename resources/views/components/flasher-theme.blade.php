<style>
    #toast-container,
    .fl-main-container {
        left: 50% !important;
        right: auto !important;
        top: max(0.85rem, env(safe-area-inset-top)) !important;
        transform: translateX(-50%) !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0.5rem !important;
        width: min(390px, calc(100vw - 1.5rem)) !important;
        z-index: 99999 !important;
        pointer-events: none !important;
    }

    #toast-container>div,
    .fl-main-container .fl-container {
        position: relative !important;
        pointer-events: auto !important;
        width: 100% !important;
        min-height: auto !important;
        margin: 0 !important;
        padding: 0.75rem 0.9rem !important;
        border-radius: 18px !important;
        border: 1px solid transparent !important;
        background-image: none !important;
        background-repeat: no-repeat !important;
        backdrop-filter: blur(18px) !important;
        -webkit-backdrop-filter: blur(18px) !important;
        box-shadow: 0 16px 38px rgb(15 23 42 / 0.18), 0 2px 8px rgb(15 23 42 / 0.08) !important;
        overflow: hidden !important;
        opacity: 1 !important;
        transform-origin: top center !important;
        animation: dynamicIslandIn 360ms cubic-bezier(.22, 1, .36, 1) both !important;
        transition: opacity 220ms ease, transform 220ms ease !important;
        transform: translateY(0) scale(1) !important;
    }

    #toast-container>div.toast-closing,
    .fl-main-container .fl-container.toast-closing {
        opacity: 0 !important;
        transform: translateY(-10px) scale(.82) !important;
        animation: dynamicIslandOut 220ms ease both !important;
    }

    @keyframes dynamicIslandIn {
        0% { opacity: 0; transform: translateY(-18px) scale(.58, .72); border-radius: 999px; }
        55% { opacity: 1; transform: translateY(2px) scale(1.03, .96); border-radius: 24px; }
        100% { transform: translateY(0) scale(1); border-radius: 18px; }
    }

    @keyframes dynamicIslandOut {
        from { opacity: 1; transform: translateY(0) scale(1); }
        to { opacity: 0; transform: translateY(-10px) scale(.82); }
    }

    #toast-container .toast-title,
    .fl-main-container .fl-title {
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        margin: 0 0 0.35rem !important;
        font-size: 0.9rem !important;
        line-height: 1.2 !important;
        font-weight: 700 !important;
        padding-right: 2rem !important;
    }

    #toast-container .toast-message,
    .fl-main-container .fl-message {
        margin: 0 !important;
        font-size: 0.78rem !important;
        line-height: 1.4 !important;
        font-weight: 500 !important;
        padding-right: 2rem !important;
        color: #475569 !important;
    }

    #toast-container .toast-close-button,
    .fl-main-container .fl-close {
        position: absolute !important;
        top: 0.625rem !important;
        right: 0.625rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 1.5rem !important;
        height: 1.5rem !important;
        border-radius: 9999px !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        outline: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        color: #94a3b8 !important;
        opacity: 1 !important;
        text-shadow: none !important;
    }

    #toast-container .toast-close-button:hover,
    .fl-main-container .fl-close:hover {
        background: rgb(100 116 139 / 0.12) !important;
        color: #334155 !important;
    }

    #toast-container .toast-progress,
    .fl-main-container .fl-progress-bar {
        position: absolute !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        height: 2.5px !important;
        border-radius: 0 0 12px 12px !important;
        background: rgb(148 163 184 / 0.14) !important;
        overflow: hidden !important;
    }

    #toast-container .toast-progress>div,
    .fl-main-container .fl-progress {
        height: 100% !important;
        transform-origin: left !important;
        animation: toastTimerShrink 3s linear forwards !important;
    }

    @keyframes toastTimerShrink {
        from { transform: scaleX(1); }
        to { transform: scaleX(0); }
    }

    @media (prefers-reduced-motion: reduce) {
        .fl-main-container .fl-container,
        #toast-container > div {
            animation: none !important;
            transition: none !important;
        }
    }
</style>

<script>
    (function() {
        const toastThemes = {
            success: { background: '#ffffff', border: '#e2e8f0', text: '#059669', progress: '#10b981' },
            error: { background: '#ffffff', border: '#e2e8f0', text: '#e11d48', progress: '#f43f5e' },
            warning: { background: '#ffffff', border: '#e2e8f0', text: '#d97706', progress: '#f59e0b' },
            info: { background: '#ffffff', border: '#e2e8f0', text: '#0284c7', progress: '#0ea5e9' },
        };

        const configureToastrAnimation = () => {
            if (!window.toastr) return;
            window.toastr.options = { ...(window.toastr.options || {}), showMethod: 'fadeIn', hideMethod: 'fadeOut', closeMethod: 'fadeOut', showDuration: 180, hideDuration: 220, closeDuration: 220 };
        };
        configureToastrAnimation();

        const getToastType = (toast) => {
            const classText = toast.className.toLowerCase();
            if (classText.includes('fl-success') || classText.includes('toast-success') || classText.includes('toast-succes')) return 'success';
            if (classText.includes('fl-error') || classText.includes('toast-error') || classText.includes('toast-danger') || classText.includes('toast-errorr')) return 'error';
            if (classText.includes('fl-warning') || classText.includes('toast-warning') || classText.includes('toast-warn')) return 'warning';
            if (classText.includes('fl-info') || classText.includes('toast-info')) return 'info';
            return 'info';
        };

        const paintToast = (toast) => {
            const type = getToastType(toast);
            const theme = toastThemes[type] ?? toastThemes.info;
            if (toast.dataset.themePainted !== type) {
                toast.dataset.themePainted = type;
                toast.style.setProperty('background', theme.background, 'important');
                toast.style.setProperty('border-color', theme.border, 'important');
                toast.style.setProperty('color', theme.text, 'important');
                toast.querySelectorAll('.fl-title, .toast-title').forEach((el) => {
                    el.style.setProperty('color', theme.text, 'important');
                    const existingIcon = el.querySelector('.fl-title-status-icon');
                    if (!existingIcon) {
                        const icon = document.createElement('i');
                        const iconClass = type === 'success' ? 'ri-check-line' : type === 'error' ? 'ri-close-line' : type === 'warning' ? 'ri-alert-line' : 'ri-information-line';
                        icon.className = `fl-title-status-icon ${iconClass}`;
                        icon.style.marginRight = '0.45rem';
                        icon.style.fontSize = '0.9rem';
                        icon.style.color = theme.text;
                        el.prepend(icon);
                    } else {
                        existingIcon.style.color = theme.text;
                    }
                });
                toast.querySelectorAll('.fl-progress, .toast-progress > div').forEach((el) => el.style.setProperty('background', theme.progress, 'important'));
            }
        };

        document.addEventListener('click', (event) => {
            const closeButton = event.target.closest('#toast-container .toast-close-button, .fl-main-container .fl-close');
            if (!closeButton) return;
            closeButton.closest('#toast-container > div, .fl-main-container .fl-container')?.classList.add('toast-closing');
        }, true);

        const refreshToasts = (root = document) => {
            if (!root.querySelectorAll) return;
            root.querySelectorAll('.fl-main-container .fl-container, #toast-container > div').forEach(paintToast);
        };

        document.addEventListener('DOMContentLoaded', () => refreshToasts());
        new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (!(node instanceof HTMLElement)) return;
                    if (node.matches('.fl-container, #toast-container > div')) paintToast(node);
                    refreshToasts(node);
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    })();
</script>
