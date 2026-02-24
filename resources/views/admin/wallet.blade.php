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
    @endphp

    <section class="glass rounded-3xl p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Carteira</p>
                <h1 class="brand-title mt-2 text-4xl text-slate-900">Carteira do sistema e gest&atilde;o de fundos</h1>
                <p class="mt-3 max-w-4xl text-sm text-slate-600">
                    Administra carregamentos, levantamentos, saldos das carteiras e a divis&atilde;o estimada das vendas entre sistema e farm&aacute;cias.
                </p>
            </div>
            <form class="grid gap-2 rounded-2xl border border-slate-200 bg-white/70 p-4" method="GET" action="{{ route('admin.wallet.index') }}">
                <label class="grid gap-1 text-sm text-slate-600">
                    <span>Filtrar por estado</span>
                    <select class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" name="status">
                        <option value="">Todos</option>
                        <option value="pending" @selected($statusFilter === 'pending')>Pendente</option>
                        <option value="approved" @selected($statusFilter === 'approved')>Aprovado</option>
                        <option value="rejected" @selected($statusFilter === 'rejected')>Rejeitado</option>
                        <option value="processing" @selected($statusFilter === 'processing')>Em processamento</option>
                        <option value="paid" @selected($statusFilter === 'paid')>Pago</option>
                    </select>
                </label>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">Filtrar</button>
                    <a class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-700" href="{{ route('admin.wallet.index') }}">Limpar</a>
                </div>
            </form>
        </div>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="glass rounded-3xl p-5"><p class="text-xs text-slate-500 uppercase tracking-[0.2em]">Saldo sistema</p><p class="mt-2 text-3xl font-semibold">Kz {{ number_format((float) $systemWallet->balance, 2, ',', '.') }}</p></div>
        <div class="glass rounded-3xl p-5"><p class="text-xs text-slate-500 uppercase tracking-[0.2em]">Carteiras</p><p class="mt-2 text-3xl font-semibold">{{ $walletSummary['wallets_count'] }}</p><p class="mt-1 text-sm text-slate-500">Utilizadores {{ $walletSummary['users_wallets_count'] }} | Farm&aacute;cias {{ $walletSummary['pharmacies_wallets_count'] }}</p></div>
        <div class="glass rounded-3xl p-5"><p class="text-xs text-slate-500 uppercase tracking-[0.2em]">Carregamentos pendentes</p><p class="mt-2 text-3xl font-semibold">{{ $walletSummary['pending_topups_count'] }}</p></div>
        <div class="glass rounded-3xl p-5"><p class="text-xs text-slate-500 uppercase tracking-[0.2em]">Levantamentos pendentes</p><p class="mt-2 text-3xl font-semibold">{{ $walletSummary['pending_withdrawals_count'] }}</p></div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[1.3fr_1fr]">
        <div class="glass rounded-3xl p-6">
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
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">LanÃ§amento manual</p>
            <h2 class="brand-title text-3xl text-slate-900">Ajuste do sistema</h2>
            <form class="mt-4 grid gap-3" method="POST" action="{{ route('admin.wallet.adjustments.store') }}">
                @csrf
                <select class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" name="direction">
                    <option value="credit">Entrada (crÃ©dito)</option>
                    <option value="debit">SaÃ­da (dÃ©bito)</option>
                </select>
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="number" min="1" step="0.01" name="amount" placeholder="Valor em Kz" required />
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="text" name="description" maxlength="255" placeholder="Descri&ccedil;&atilde;o" required />
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" type="submit">Registar lanÃ§amento</button>
            </form>
            <div class="mt-6 border-t border-slate-200 pt-5">
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Gerar refer&ecirc;ncia</p>
                <h3 class="mt-1 text-base font-semibold text-slate-900">Carregamento para utilizador</h3>
                <form class="mt-3 grid gap-3" method="POST" action="{{ route('admin.wallet.topups.generate') }}">
                    @csrf
                    <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="text" name="user_lookup" placeholder="ID ou email do utilizador" required />
                    <select class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" name="target">
                        <option value="user">Carteira pessoal</option>
                        <option value="pharmacy">Carteira da farm&aacute;cia</option>
                    </select>
                    <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="number" min="100" step="0.01" name="amount" placeholder="Valor em Kz" required />
                    <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="text" name="notes" placeholder="Observa&ccedil;&otilde;es (opcional)" />
                    <button class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">Gerar refer&ecirc;ncia para utilizador</button>
                </form>
            </div>
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total em carteiras</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">Kz {{ number_format((float) $walletSummary['wallets_balance_sum'], 2, ',', '.') }}</p>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="brand-title text-3xl text-slate-900">Carregamentos</h2>
                <span class="text-xs text-slate-500">{{ $topUpRequests->count() }} registos</span>
            </div>
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Utilizador</th><th class="px-4 py-3 text-left">Destino</th><th class="px-4 py-3 text-left">Refer&ecirc;ncia</th><th class="px-4 py-3 text-right">Valor</th><th class="px-4 py-3 text-left">Estado</th><th class="px-4 py-3 text-right">AÃ§Ãµes</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($topUpRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $item->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->pharmacy?->name ?? 'Carteira pessoal' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $item->payment_reference ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->reference_code }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span></td>
                                <td class="px-4 py-3">
                                    @if ($item->status === 'pending')
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.wallet.topups.approve', $item) }}">@csrf<button class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white" type="submit">Aprovar</button></form>
                                            <form method="POST" action="{{ route('admin.wallet.topups.reject', $item) }}">@csrf<button class="rounded-full bg-rose-500 px-3 py-1 text-xs font-semibold text-white" type="submit">Rejeitar</button></form>
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

        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="brand-title text-3xl text-slate-900">Levantamentos</h2>
                <span class="text-xs text-slate-500">{{ $withdrawRequests->count() }} registos</span>
            </div>
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Utilizador</th><th class="px-4 py-3 text-left">Origem</th><th class="px-4 py-3 text-right">Valor</th><th class="px-4 py-3 text-left">Estado</th><th class="px-4 py-3 text-right">AÃ§Ãµes</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($withdrawRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $item->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->pharmacy?->name ?? 'Carteira pessoal' }}</td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span></td>
                                <td class="px-4 py-3">
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
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="brand-title text-3xl text-slate-900">Carteiras registadas</h2><span class="text-xs text-slate-500">{{ $wallets->count() }}</span></div>
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
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

        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="brand-title text-3xl text-slate-900">Movimentos recentes</h2><span class="text-xs text-slate-500">{{ $recentTransactions->count() }}</span></div>
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Carteira</th><th class="px-4 py-3 text-left">Tipo</th><th class="px-4 py-3 text-right">Montante</th><th class="px-4 py-3 text-left">Data</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($recentTransactions as $tx)
                            <tr>
                                <td class="px-4 py-3">{{ $tx->walletAccount?->label ?: strtoupper((string) ($tx->walletAccount?->owner_type ?? '')) }}</td>
                                <td class="px-4 py-3 uppercase">{{ $tx->category }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $tx->direction === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $tx->direction === 'credit' ? '+' : '-' }}Kz {{ number_format((float) $tx->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $tx->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem movimentos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

