@php
    $autoInvoiceModalUrl = session('invoice_modal_url', '');
@endphp

<div
    class="fixed inset-0 z-[90] hidden items-center justify-center p-4"
    data-pharmacy-invoice-modal
    data-auto-open-url="{{ $autoInvoiceModalUrl }}"
    aria-hidden="true"
>
    <div class="absolute inset-0 bg-slate-950/55" data-pharmacy-invoice-modal-close></div>

    <div class="relative z-10 flex h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Factura</p>
                <p class="text-sm font-semibold text-slate-900">Visualizar e imprimir</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-slate-400"
                    data-pharmacy-invoice-modal-close
                >
                    Fechar
                </button>
            </div>
        </div>

        <iframe
            title="Factura"
            class="h-full w-full bg-slate-100"
            src="about:blank"
            data-pharmacy-invoice-modal-frame
        ></iframe>
    </div>
</div>

<script>
    (() => {
        if (window.__pharmacyInvoiceModalInitialized) {
            return;
        }
        window.__pharmacyInvoiceModalInitialized = true;

        const initModal = () => {
            const modal = document.querySelector('[data-pharmacy-invoice-modal]');
            if (!modal) {
                return;
            }

            const frame = modal.querySelector('[data-pharmacy-invoice-modal-frame]');
            const closeButtons = modal.querySelectorAll('[data-pharmacy-invoice-modal-close]');
            let bodyWasLocked = false;

            const lockBody = () => {
                if (document.body.classList.contains('overflow-hidden')) {
                    return;
                }
                document.body.classList.add('overflow-hidden');
                bodyWasLocked = true;
            };

            const unlockBody = () => {
                if (!bodyWasLocked) {
                    return;
                }
                document.body.classList.remove('overflow-hidden');
                bodyWasLocked = false;
            };

            const openModal = (url) => {
                if (!url || !frame) {
                    return;
                }

                if (frame.getAttribute('src') !== url) {
                    frame.setAttribute('src', url);
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                lockBody();
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                unlockBody();
            };

            document.querySelectorAll('[data-open-invoice-modal-url]').forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    const url = trigger.getAttribute('data-open-invoice-modal-url');
                    if (!url) {
                        return;
                    }
                    event.preventDefault();
                    openModal(url);
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('flex')) {
                    closeModal();
                }
            });

            const autoOpenUrl = modal.getAttribute('data-auto-open-url');
            if (autoOpenUrl) {
                openModal(autoOpenUrl);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initModal, { once: true });
        } else {
            initModal();
        }
    })();
</script>
