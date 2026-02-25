@extends('layouts.storefront')

@section('title', html_entity_decode('Carteira do Sistema - Medlink', ENT_QUOTES, 'UTF-8'))

@section('content')
    @php
        $statusLabels = ['pending' => 'PENDENTE', 'approved' => 'APROVADO', 'rejected' => 'REJEITADO', 'processing' => 'EM PROCESSAMENTO', 'paid' => 'PAGO'];
        $statusClasses = [
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
            'processing' => 'bg-sky-100 text-sky-800 border-sky-200',
            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        ];
        $pendingApprovalsTotal = (int) $walletSummary['pending_topups_count'] + (int) $walletSummary['pending_withdrawals_count'];
    @endphp

    <section class="glass rounded-3xl p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Carteira</p>
                <h1 class="brand-title mt-2 text-4xl text-slate-900">Carteira do sistema</h1>
                <p class="mt-3 max-w-4xl text-sm text-slate-600">
                    Gest&atilde;o simples de fundos, carregamentos, levantamentos e divis&atilde;o estimada das vendas entre o sistema e as farm&aacute;cias.
                </p>
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-6">
        <div class="glass rounded-3xl p-4 sm:p-5"><p class="text-[11px] text-slate-500 uppercase tracking-[0.2em]">Saldo sistema</p><p class="mt-2 text-2xl sm:text-3xl font-semibold">Kz {{ number_format((float) $systemWallet->balance, 2, ',', '.') }}</p></div>
        <div class="glass rounded-3xl p-4 sm:p-5"><p class="text-[11px] text-slate-500 uppercase tracking-[0.2em]">Rendimentos (total)</p><p class="mt-2 text-2xl sm:text-3xl font-semibold">Kz {{ number_format((float) ($walletSummary['platform_earnings_total'] ?? 0), 2, ',', '.') }}</p><p class="mt-1 text-xs sm:text-sm text-slate-500">Comissões + vendas diretas + entrega</p></div>
        <div class="glass rounded-3xl p-4 sm:p-5"><p class="text-[11px] text-slate-500 uppercase tracking-[0.2em]">Rendimentos hoje</p><p class="mt-2 text-2xl sm:text-3xl font-semibold">Kz {{ number_format((float) ($walletSummary['platform_earnings_today'] ?? 0), 2, ',', '.') }}</p><p class="mt-1 text-xs sm:text-sm text-slate-500">Total diário da plataforma</p></div>
        <div class="glass rounded-3xl p-4 sm:p-5"><p class="text-[11px] text-slate-500 uppercase tracking-[0.2em]">Carteiras</p><p class="mt-2 text-2xl sm:text-3xl font-semibold">{{ $walletSummary['wallets_count'] }}</p><p class="mt-1 text-xs sm:text-sm text-slate-500">Utilizadores {{ $walletSummary['users_wallets_count'] }} | Farm&aacute;cias {{ $walletSummary['pharmacies_wallets_count'] }}</p></div>
        <div class="glass rounded-3xl p-4 sm:p-5"><p class="text-[11px] text-slate-500 uppercase tracking-[0.2em]">Carregamentos pendentes</p><p class="mt-2 text-2xl sm:text-3xl font-semibold">{{ $walletSummary['pending_topups_count'] }}</p></div>
        <div class="glass rounded-3xl p-4 sm:p-5"><p class="text-[11px] text-slate-500 uppercase tracking-[0.2em]">Levantamentos pendentes</p><p class="mt-2 text-2xl sm:text-3xl font-semibold">{{ $walletSummary['pending_withdrawals_count'] }}</p></div>
    </section>

    <section class="mt-8 glass rounded-3xl p-4 sm:p-5" data-admin-wallet-tabs>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-600">Use os separadores para reduzir o ru&iacute;do e trabalhar mais r&aacute;pido.</p>
            </div>
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Sec&ccedil;&otilde;es da carteira">
                <button type="button" role="tab" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-admin-wallet-tab="overview">Vis&atilde;o geral</button>
                <button type="button" role="tab" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-admin-wallet-tab="approvals">
                    Aprova&ccedil;&otilde;es
                    @if ($pendingApprovalsTotal > 0)
                        <span
                            class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800"
                            data-admin-wallet-approvals-badge
                            data-count="{{ $pendingApprovalsTotal }}"
                        >
                            {{ $pendingApprovalsTotal }}
                        </span>
                    @endif
                </button>
                <button type="button" role="tab" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-admin-wallet-tab="records">Registos</button>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[1.3fr_1fr]" data-admin-wallet-panel="overview">
        <div id="carregamentos-pendentes" class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Vendas</p>
                    <h2 class="brand-title text-3xl text-slate-900">Divis&atilde;o sistema x farm&aacute;cias</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">Comiss&atilde;o sistema: {{ $salesTotals['commission_rate_percent'] }}%</span>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Vendas brutas</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $salesTotals['gross_sales'], 2, ',', '.') }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Quota sistema</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $salesTotals['system_share'], 2, ',', '.') }}</p></div>
                <div class="rounded-2xl border border-lime-200 bg-lime-50 p-4"><p class="text-xs text-lime-700">Quota farm&aacute;cias</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $salesTotals['pharmacy_share'], 2, ',', '.') }}</p></div>
            </div>
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Farm&aacute;cia</th><th class="px-4 py-3 text-right">Pedidos</th><th class="px-4 py-3 text-right">Vendas</th><th class="px-4 py-3 text-right">Sistema</th><th class="px-4 py-3 text-right">Farm&aacute;cia</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($salesAllocationRows as $row)
                            <tr>
                                <td class="px-4 py-3">{{ $row->pharmacy_name }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->orders_count }}</td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $row->gross_sales, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $row->system_share, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold">Kz {{ number_format((float) $row->pharmacy_share, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="5">Sem vendas para calcular a divis&atilde;o.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Lan&ccedil;amento manual</p>
            <h2 class="brand-title text-3xl text-slate-900">Ajuste do sistema</h2>
            <form class="mt-4 grid gap-3" method="POST" action="{{ route('admin.wallet.adjustments.store') }}">
                @csrf
                <select class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" name="direction">
                    <option value="credit">Entrada (cr&eacute;dito)</option>
                    <option value="debit">Sa&iacute;da (d&eacute;bito)</option>
                </select>
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="number" min="1" step="0.01" name="amount" placeholder="Valor em Kz" required />
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="text" name="description" maxlength="255" placeholder="Descri&ccedil;&atilde;o" required />
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" type="submit">Registar lan&ccedil;amento</button>
            </form>
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total em carteiras</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">Kz {{ number_format((float) $walletSummary['wallets_balance_sum'], 2, ',', '.') }}</p>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 hidden" data-admin-wallet-panel="approvals">
        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="brand-title text-3xl text-slate-900">Carregamentos</h2>
                    <span class="text-xs text-slate-500">{{ $topUpRequests->count() }} registos</span>
                </div>
                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 md:hidden" data-admin-wallet-fold-toggle="topups" data-open-label="Ocultar" data-closed-label="Ver tabela">
                    Ocultar
                </button>
            </div>
            <div class="mt-4" data-admin-wallet-fold-panel="topups">
            <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[28rem]">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-3 sm:px-4 py-3 text-left">Utilizador</th><th class="hidden md:table-cell px-3 sm:px-4 py-3 text-left">Destino</th><th class="px-3 sm:px-4 py-3 text-left">Refer&ecirc;ncia</th><th class="px-3 sm:px-4 py-3 text-right">Valor</th><th class="px-3 sm:px-4 py-3 text-left">Estado</th><th class="px-3 sm:px-4 py-3 text-right">A&ccedil;&otilde;es</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($topUpRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="px-3 sm:px-4 py-3">{{ $item->user?->name ?? '-' }}</td>
                                <td class="hidden md:table-cell px-3 sm:px-4 py-3">{{ $item->pharmacy?->name ?? 'Carteira pessoal' }}</td>
                                <td class="px-3 sm:px-4 py-3">
                                    <div class="font-semibold">{{ $item->payment_reference ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->reference_code }}</div>
                                    <div class="mt-1 text-[11px] text-slate-500 md:hidden">{{ $item->pharmacy?->name ?? 'Carteira pessoal' }}</div>
                                </td>
                                <td class="px-3 sm:px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-3 sm:px-4 py-3"><span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span></td>
                                <td class="px-3 sm:px-4 py-3">
                                    @if ($item->status === 'pending')
                                        <div class="text-right text-xs text-slate-500">
                                            Autom&aacute;tico por refer&ecirc;ncia<br />
                                            <span class="text-[11px]">Aguarda confirma&ccedil;&atilde;o do pagamento</span>
                                        </div>
                                    @else
                                        <div class="text-right text-xs text-slate-500">{{ $item->handled_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="6">Sem pedidos de carregamento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="brand-title text-3xl text-slate-900">Levantamentos</h2>
                    <span class="text-xs text-slate-500">{{ $withdrawRequests->count() }} registos</span>
                </div>
                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 md:hidden" data-admin-wallet-fold-toggle="withdrawals" data-open-label="Ocultar" data-closed-label="Ver tabela">
                    Ver tabela
                </button>
            </div>
            <div class="mt-4 hidden md:block" data-admin-wallet-fold-panel="withdrawals">
            <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[28rem]">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-3 sm:px-4 py-3 text-left">Utilizador</th><th class="hidden md:table-cell px-3 sm:px-4 py-3 text-left">Origem</th><th class="px-3 sm:px-4 py-3 text-right">Valor</th><th class="px-3 sm:px-4 py-3 text-left">Estado</th><th class="px-3 sm:px-4 py-3 text-right">A&ccedil;&otilde;es</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($withdrawRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="px-3 sm:px-4 py-3">{{ $item->user?->name ?? '-' }}</td>
                                <td class="hidden md:table-cell px-3 sm:px-4 py-3">{{ $item->pharmacy?->name ?? 'Carteira pessoal' }}</td>
                                <td class="px-3 sm:px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-3 sm:px-4 py-3">
                                    <span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span>
                                    <div class="mt-1 text-[11px] text-slate-500 md:hidden">{{ $item->pharmacy?->name ?? 'Carteira pessoal' }}</div>
                                </td>
                                <td class="px-3 sm:px-4 py-3">
                                    @if (in_array($item->status, ['pending', 'processing'], true))
                                        <div class="flex justify-end gap-2">
                                            @if ($item->status === 'pending')
                                                <form method="POST" action="{{ route('admin.wallet.withdrawals.status', $item) }}">@csrf<input type="hidden" name="action" value="processing" /><button class="rounded-full bg-sky-500 px-3 py-1 text-xs font-semibold text-white" type="submit">Processar</button></form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.wallet.withdrawals.status', $item) }}">@csrf<input type="hidden" name="action" value="paid" /><button class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white" type="submit">Pago</button></form>
                                            <form method="POST" action="{{ route('admin.wallet.withdrawals.status', $item) }}">@csrf<input type="hidden" name="action" value="rejected" /><button class="rounded-full bg-rose-500 px-3 py-1 text-xs font-semibold text-white" type="submit">Rejeitar</button></form>
                                        </div>
                                    @else
                                        <div class="text-right text-xs text-slate-500">{{ $item->handled_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="5">Sem pedidos de levantamento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2 hidden" data-admin-wallet-panel="records">
        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="brand-title text-3xl text-slate-900">Carteiras registadas</h2>
                    <span class="text-xs text-slate-500">{{ $wallets->count() }}</span>
                </div>
                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 md:hidden" data-admin-wallet-fold-toggle="wallets" data-open-label="Ocultar" data-closed-label="Ver tabela">
                    Ocultar
                </button>
            </div>
            <div class="mt-4" data-admin-wallet-fold-panel="wallets">
            <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[28rem]">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Tipo</th><th class="px-4 py-3 text-left">Etiqueta</th><th class="px-4 py-3 text-right">Saldo</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($wallets as $wallet)
                            <tr><td class="px-4 py-3 uppercase">{{ $wallet->owner_type }}</td><td class="px-4 py-3">{{ $wallet->label ?: '-' }}</td><td class="px-4 py-3 text-right font-semibold">Kz {{ number_format((float) $wallet->balance, 2, ',', '.') }}</td></tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="3">Sem carteiras registadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="brand-title text-3xl text-slate-900">Movimentos recentes</h2>
                    <span class="text-xs text-slate-500">{{ $recentTransactions->count() }}</span>
                </div>
                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 md:hidden" data-admin-wallet-fold-toggle="transactions" data-open-label="Ocultar" data-closed-label="Ver tabela">
                    Ver tabela
                </button>
            </div>
            <div class="mt-4 hidden md:block" data-admin-wallet-fold-panel="transactions">
            <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[28rem]">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-3 sm:px-4 py-3 text-left">Carteira</th><th class="hidden sm:table-cell px-3 sm:px-4 py-3 text-left">Tipo</th><th class="px-3 sm:px-4 py-3 text-right">Montante</th><th class="px-3 sm:px-4 py-3 text-left">Data</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($recentTransactions as $tx)
                            <tr>
                                <td class="px-3 sm:px-4 py-3">{{ $tx->walletAccount?->label ?: strtoupper((string) ($tx->walletAccount?->owner_type ?? '')) }}<div class="mt-1 text-[11px] text-slate-500 uppercase sm:hidden">{{ $tx->category }}</div></td>
                                <td class="hidden sm:table-cell px-3 sm:px-4 py-3 uppercase">{{ $tx->category }}</td>
                                <td class="px-3 sm:px-4 py-3 text-right font-semibold {{ $tx->direction === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $tx->direction === 'credit' ? '+' : '-' }}Kz {{ number_format((float) $tx->amount, 2, ',', '.') }}</td>
                                <td class="px-3 sm:px-4 py-3">{{ $tx->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem movimentos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </section>
    <script>
        (() => {
            const tabRoot = document.querySelector('[data-admin-wallet-tabs]');
            if (!tabRoot) return;

            const buttons = Array.from(tabRoot.querySelectorAll('[data-admin-wallet-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-admin-wallet-panel]'));
            const quickButtons = Array.from(document.querySelectorAll('[data-admin-wallet-open-tab]'));
            const foldButtons = Array.from(document.querySelectorAll('[data-admin-wallet-fold-toggle]'));
            const foldPanels = Array.from(document.querySelectorAll('[data-admin-wallet-fold-panel]'));
            const approvalsBadge = tabRoot.querySelector('[data-admin-wallet-approvals-badge]');
            const approvalsBadgeStorageKey = 'medlink_admin_wallet_approvals_seen_count';

            const getApprovalsCurrentCount = () => Number(approvalsBadge?.dataset.count || 0);
            const getSeenApprovalsCount = () => {
                try {
                    return Number(window.localStorage.getItem(approvalsBadgeStorageKey) || 0);
                } catch (error) {
                    return 0;
                }
            };
            const setSeenApprovalsCount = (count) => {
                try {
                    window.localStorage.setItem(approvalsBadgeStorageKey, String(count));
                } catch (error) {
                    // Ignora se o browser bloquear localStorage.
                }
            };
            const syncApprovalsBadge = () => {
                if (!approvalsBadge) return;
                const currentCount = getApprovalsCurrentCount();
                const seenCount = getSeenApprovalsCount();
                approvalsBadge.classList.toggle('hidden', currentCount <= 0 || seenCount >= currentCount);
            };
            const markApprovalsAsSeen = () => {
                const currentCount = getApprovalsCurrentCount();
                if (currentCount <= 0) return;
                setSeenApprovalsCount(currentCount);
                syncApprovalsBadge();
            };

            const setActiveButton = (button, active) => {
                button.setAttribute('aria-selected', String(active));
                button.classList.toggle('bg-slate-900', active);
                button.classList.toggle('border-slate-900', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('bg-white', !active);
                button.classList.toggle('border-slate-300', !active);
                button.classList.toggle('text-slate-700', !active);
            };

            const openTab = (name) => {
                buttons.forEach((button) => setActiveButton(button, button.dataset.adminWalletTab === name));
                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.adminWalletPanel !== name);
                });

                if (name === 'approvals') {
                    markApprovalsAsSeen();
                }
            };

            const isMobile = () => window.matchMedia('(max-width: 767px)').matches;
            const setFoldState = (id, open) => {
                const panel = foldPanels.find((item) => item.dataset.adminWalletFoldPanel === id);
                const button = foldButtons.find((item) => item.dataset.adminWalletFoldToggle === id);
                if (!panel || !button) return;

                panel.classList.toggle('hidden', !open);
                panel.classList.toggle('md:block', id === 'withdrawals' || id === 'transactions');
                button.textContent = open ? (button.dataset.openLabel || 'Ocultar') : (button.dataset.closedLabel || 'Ver tabela');
                button.setAttribute('aria-expanded', String(open));
            };

            const applyMobileFoldDefaults = () => {
                if (!isMobile()) {
                    foldPanels.forEach((panel) => panel.classList.remove('hidden'));
                    foldButtons.forEach((button) => {
                        button.textContent = button.dataset.openLabel || 'Ocultar';
                        button.setAttribute('aria-expanded', 'true');
                    });
                    return;
                }

                setFoldState('topups', true);
                setFoldState('withdrawals', false);
                setFoldState('wallets', true);
                setFoldState('transactions', false);
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => openTab(button.dataset.adminWalletTab));
            });

            quickButtons.forEach((button) => {
                button.addEventListener('click', () => openTab(button.dataset.adminWalletOpenTab));
            });

            foldButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.adminWalletFoldToggle;
                    const panel = foldPanels.find((item) => item.dataset.adminWalletFoldPanel === id);
                    if (!panel) return;
                    const open = panel.classList.contains('hidden');
                    setFoldState(id, open);
                });
            });

            syncApprovalsBadge();
            openTab('overview');
            applyMobileFoldDefaults();
            window.addEventListener('resize', applyMobileFoldDefaults);
        })();
    </script>
@endsection

