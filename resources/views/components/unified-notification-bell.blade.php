@props([
    'pharmacyNotifications' => [],
    'adminNotifications' => [],
    'customerNotifications' => [],
])

@php
    $pharmacyNotifications = is_array($pharmacyNotifications) ? $pharmacyNotifications : [];
    $adminNotifications = is_array($adminNotifications) ? $adminNotifications : [];
    $customerNotifications = is_array($customerNotifications) ? $customerNotifications : [];

    $pharmacyEnabled = (bool) ($pharmacyNotifications['enabled'] ?? false);
    $adminEnabled = (bool) ($adminNotifications['enabled'] ?? false);
    $customerEnabled = (bool) ($customerNotifications['enabled'] ?? false);

    $pharmacyTotal = (int) ($pharmacyNotifications['total'] ?? 0);
    $pharmacyOrderCount = (int) ($pharmacyNotifications['order_count'] ?? 0);
    $adminTotal = (int) ($adminNotifications['pending_count'] ?? 0);
    $customerTotal = (int) ($customerNotifications['total'] ?? 0);

    $totalNotifications = $pharmacyTotal + $adminTotal + $customerTotal;
    $hasAnyNotificationContext = $pharmacyEnabled || $adminEnabled || $customerEnabled;
@endphp

@if ($hasAnyNotificationContext)
    <details
        class="relative z-[70] flex shrink-0 items-center"
        data-unified-notification
        data-mark-orders-seen-url="{{ auth()->check() ? route('notifications.orders.seen') : '' }}"
        data-pharmacy-total="{{ $pharmacyTotal }}"
        data-pharmacy-order-count="{{ $pharmacyOrderCount }}"
        data-admin-total="{{ $adminTotal }}"
        data-customer-total="{{ $customerTotal }}"
    >
        <summary class="flex items-center list-none cursor-pointer [&::-webkit-details-marker]:hidden">
            <span class="relative flex h-10 w-10 items-center justify-center rounded-full border border-amber-300 bg-amber-200 text-amber-700 hover:border-amber-400" title="Notificações">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                    <path d="M9 17v1a3 3 0 0 0 6 0v-1"></path>
                </svg>
                @if ($totalNotifications > 0)
                    <span class="absolute -right-1 -top-1 rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold text-white" data-notification-total-badge>
                        {{ $totalNotifications }}
                    </span>
                @endif
            </span>
        </summary>

        <div class="absolute right-0 z-[80] mt-3 w-80 max-w-[92vw] rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Notificações</p>
                <span class="text-xs text-slate-500" data-notification-total-label>{{ $totalNotifications }} {{ $totalNotifications === 1 ? 'item' : 'itens' }}</span>
            </div>

            @if ($totalNotifications === 0)
                <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                    Sem notificações novas.
                </div>
            @endif

            @if ($adminEnabled)
                <div class="mt-3 rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">Admin</p>
                        <span class="text-xs text-slate-500">{{ $adminTotal }} pendentes</span>
                    </div>

                    @if ($adminTotal === 0)
                        <p class="mt-2 text-xs text-slate-500">Nenhuma farmácia pendente de aprovação.</p>
                    @else
                        <ul class="mt-2 space-y-2 text-xs text-slate-600">
                            @foreach (($adminNotifications['pending'] ?? collect()) as $pharmacy)
                                <li class="rounded-xl border border-slate-200 px-3 py-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-slate-900">{{ $pharmacy->name }}</span>
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Pendente</span>
                                    </div>
                                    <p class="mt-1 text-slate-500">{{ $pharmacy->responsible_name }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <a class="mt-3 inline-flex text-xs text-lime-700 hover:text-lime-700" href="{{ route('admin.pharmacies.index') }}">
                        Ir para aprovações
                    </a>
                </div>
            @endif

            @if ($customerEnabled)
                <div class="mt-3 rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">Pedidos</p>
                        <span class="text-xs text-slate-500" data-notification-customer-count>{{ $customerTotal }} novos</span>
                    </div>

                    @if (($customerNotifications['orders'] ?? collect())->isEmpty())
                        <p class="mt-2 text-xs text-slate-500">Sem notificações de pedidos.</p>
                    @else
                        <ul class="mt-2 space-y-2 text-xs text-slate-600" data-notification-customer-list>
                            @foreach (($customerNotifications['orders'] ?? collect()) as $order)
                                <li>
                                    <a class="block rounded-xl border border-slate-200 px-3 py-2 hover:border-lime-300 hover:bg-lime-50/40" href="{{ route('orders.show', $order->id) }}">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-semibold text-slate-900">Pedido #{{ $order->id }}</span>
                                            <span class="text-lime-700">Kz {{ number_format((float) $order->total, 2, ',', '.') }}</span>
                                        </div>
                                        <p class="mt-1 text-slate-500">Confirmado. Toque para ver os detalhes.</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if ($pharmacyEnabled)
                <div class="mt-3 rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">Farmácia</p>
                        <span class="text-xs text-slate-500" data-notification-pharmacy-total>{{ $pharmacyTotal }} alertas</span>
                    </div>

                    @if (($pharmacyNotifications['pending'] ?? false))
                        <div class="mt-2 rounded-xl border border-amber-400/30 bg-amber-500/10 p-3 text-sm text-amber-700">
                            Sua farmácia está pendente de aprovação.
                        </div>
                    @else
                        <div class="mt-2 space-y-3">
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-semibold text-slate-900">Pedidos recentes</p>
                                    <span class="text-[11px] text-slate-500" data-notification-pharmacy-order-count>{{ (int) ($pharmacyNotifications['order_count'] ?? 0) }} pedidos</span>
                                </div>
                                @if (($pharmacyNotifications['orders'] ?? collect())->isEmpty())
                                    <p class="mt-1 text-xs text-slate-500">Nenhum pedido recente.</p>
                                @else
                                    <ul class="mt-1 space-y-1 text-xs text-slate-600" data-notification-pharmacy-order-list>
                                        @foreach (($pharmacyNotifications['orders'] ?? collect()) as $order)
                                            <li class="flex items-center justify-between rounded-lg border border-slate-200 px-2.5 py-1.5">
                                                <span>#{{ $order->id }}</span>
                                                <span>Kz {{ number_format((float) $order->total, 2, ',', '.') }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-semibold text-slate-900">Stock</p>
                                    <span class="text-[11px] text-slate-500">Até {{ (int) ($pharmacyNotifications['threshold'] ?? 5) }} un.</span>
                                </div>
                                @php
                                    $outOfStock = $pharmacyNotifications['out_of_stock'] ?? collect();
                                    $lowStock = $pharmacyNotifications['low_stock'] ?? collect();
                                @endphp
                                @if ($outOfStock->isEmpty() && $lowStock->isEmpty())
                                    <p class="mt-1 text-xs text-slate-500">Sem alertas de stock.</p>
                                @else
                                    <ul class="mt-1 space-y-1 text-xs text-slate-600">
                                        @foreach ($outOfStock as $product)
                                            <li class="flex items-center justify-between rounded-lg border border-rose-500/30 px-2.5 py-1.5">
                                                <span>{{ $product->name }}</span>
                                                <span class="text-rose-700">0 un.</span>
                                            </li>
                                        @endforeach
                                        @foreach ($lowStock as $product)
                                            <li class="flex items-center justify-between rounded-lg border border-amber-500/30 px-2.5 py-1.5">
                                                <span>{{ $product->name }}</span>
                                                <span class="text-amber-700">{{ $product->stock }} un.</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        @if (Route::has('pharmacy.orders.index') || Route::has('pharmacy.products.index'))
                            <div class="mt-3 flex items-center gap-4 text-xs">
                                @if (Route::has('pharmacy.products.index'))
                                    <a class="text-lime-700 hover:text-lime-700" href="{{ route('pharmacy.products.index') }}">Ver produtos</a>
                                @endif
                                @if (Route::has('pharmacy.orders.index'))
                                    <a class="text-lime-700 hover:text-lime-700" href="{{ route('pharmacy.orders.index') }}">Ver pedidos</a>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </details>
@endif

<script>
    (() => {
        if (window.__medlinkUnifiedNotificationSeenInit) {
            return;
        }
        window.__medlinkUnifiedNotificationSeenInit = true;

        const roots = () => Array.from(document.querySelectorAll('[data-unified-notification]'));
        const formatItemsLabel = (count) => `${count} ${count === 1 ? 'item' : 'itens'}`;

        const applyOrderNotificationsSeenUi = (root) => {
            const adminTotal = Number(root.dataset.adminTotal || 0);
            const customerTotal = Number(root.dataset.customerTotal || 0);
            const pharmacyTotal = Number(root.dataset.pharmacyTotal || 0);
            const pharmacyOrderCount = Number(root.dataset.pharmacyOrderCount || 0);

            const nextCustomerTotal = 0;
            const nextPharmacyTotal = Math.max(0, pharmacyTotal - pharmacyOrderCount);
            const nextTotal = Math.max(0, adminTotal + nextPharmacyTotal + nextCustomerTotal);

            root.dataset.customerTotal = String(nextCustomerTotal);
            root.dataset.pharmacyTotal = String(nextPharmacyTotal);
            root.dataset.pharmacyOrderCount = '0';

            const badge = root.querySelector('[data-notification-total-badge]');
            if (badge) {
                badge.textContent = String(nextTotal);
                badge.classList.toggle('hidden', nextTotal <= 0);
            }

            const totalLabel = root.querySelector('[data-notification-total-label]');
            if (totalLabel) {
                totalLabel.textContent = formatItemsLabel(nextTotal);
            }

            const customerCount = root.querySelector('[data-notification-customer-count]');
            if (customerCount) {
                customerCount.textContent = '0 novos';
            }

            const customerList = root.querySelector('[data-notification-customer-list]');
            if (customerList && customerTotal > 0) {
                customerList.innerHTML = '<li class="text-xs text-slate-500">Sem notificações de pedidos.</li>';
            }

            const pharmacyTotalLabel = root.querySelector('[data-notification-pharmacy-total]');
            if (pharmacyTotalLabel) {
                pharmacyTotalLabel.textContent = `${nextPharmacyTotal} alertas`;
            }

            const pharmacyOrderCountLabel = root.querySelector('[data-notification-pharmacy-order-count]');
            if (pharmacyOrderCountLabel) {
                pharmacyOrderCountLabel.textContent = '0 pedidos';
            }

            const pharmacyOrderList = root.querySelector('[data-notification-pharmacy-order-list]');
            if (pharmacyOrderList && pharmacyOrderCount > 0) {
                pharmacyOrderList.innerHTML = '<li class="text-xs text-slate-500">Nenhum pedido recente.</li>';
            }
        };

        const markOrderNotificationsSeen = async (root) => {
            const customerTotal = Number(root.dataset.customerTotal || 0);
            const pharmacyOrderCount = Number(root.dataset.pharmacyOrderCount || 0);
            const markUrl = root.dataset.markOrdersSeenUrl || '';

            if ((customerTotal + pharmacyOrderCount) <= 0 || !markUrl || root.dataset.ordersSeenRequest === 'done') {
                return;
            }

            root.dataset.ordersSeenRequest = 'pending';

            try {
                const response = await fetch(markUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Falha ao marcar notificações como vistas.');
                }

                roots().forEach((item) => applyOrderNotificationsSeenUi(item));
                roots().forEach((item) => {
                    item.dataset.ordersSeenRequest = 'done';
                });
            } catch (error) {
                root.dataset.ordersSeenRequest = '';
            }
        };

        const bind = (root) => {
            if (!root || root.dataset.notificationBound === '1') {
                return;
            }

            root.dataset.notificationBound = '1';
            root.addEventListener('toggle', () => {
                if (root.open) {
                    void markOrderNotificationsSeen(root);
                }
            });
        };

        const init = () => {
            roots().forEach(bind);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }
    })();
</script>
