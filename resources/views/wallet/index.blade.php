@extends('layouts.storefront')

@section('title', html_entity_decode('Minha Carteira - Medlink', ENT_QUOTES, 'UTF-8'))

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
        $hasPharmacy = (bool) $pharmacy;
        $pendingTopUpExpiresAt = $pendingTopUp?->created_at ? $pendingTopUp->created_at->copy()->addHours(24) : null;
        $pendingTopUpTargetLabel = $pendingTopUp?->pharmacy ? 'Carteira da farmácia' : 'Minha carteira';
    @endphp

    <section class="glass rounded-3xl p-6">
        <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Minha carteira</p>
        <h1 class="brand-title mt-2 text-4xl text-slate-900">Saldo para compras e moedas da plataforma</h1>
        <p class="mt-3 text-sm text-slate-600">
            O utilizador comum usa esta carteira para carregar saldo e comprar medicamentos. Se tiver farm&aacute;cia, tamb&eacute;m ver&aacute; os ativos da farm&aacute;cia e pedidos de transfer&ecirc;ncia.
        </p>
    </section>

    <section class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="glass rounded-3xl p-4 sm:p-5">
            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Saldo para comprar medicamentos</p>
            <p class="mt-2 text-2xl sm:text-3xl font-semibold text-slate-900">Kz {{ number_format((float) ($purchasingBalance ?? $userWallet->balance), 2, ',', '.') }}</p>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Carteira pessoal</p>
        </div>
        @if ($pharmacy)
            <div class="glass rounded-3xl p-4 sm:p-5">
                <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Saldo da farm&aacute;cia</p>
                <p class="mt-2 text-2xl sm:text-3xl font-semibold text-slate-900">Kz {{ number_format((float) ($pharmacyWallet->balance ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">{{ $pharmacy->name }}</p>
            </div>
        @else
            <div class="glass rounded-3xl p-4 sm:p-5">
                <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Estado da conta</p>
                <p class="mt-2 text-base sm:text-lg font-semibold text-slate-900">Conta pessoal ativa</p>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">Sem farm&aacute;cia associada</p>
            </div>
        @endif
        <div class="glass rounded-3xl p-4 sm:p-5">
            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Refer&ecirc;ncia pendente</p>
            <p class="mt-2 text-base sm:text-lg font-semibold text-slate-900 break-all">{{ $pendingTopUp?->reference_code ?? 'Sem pedido pendente' }}</p>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 break-all">{{ $pendingTopUp?->payment_reference ?? 'Crie um pedido abaixo.' }}</p>
        </div>
        <div class="glass rounded-3xl p-4 sm:p-5">
            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">{{ $pharmacy ? 'Pedidos de levantamento' : 'Movimentos' }}</p>
            <p class="mt-2 text-2xl sm:text-3xl font-semibold text-slate-900">{{ $pharmacy ? $withdrawRequests->count() : $transactions->count() }}</p>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">{{ $pharmacy ? 'Transfer&ecirc;ncias pedidas' : 'Registos da carteira' }}</p>
        </div>
    </section>

    <section class="mt-8 glass rounded-3xl p-4 sm:p-5" data-user-wallet-tabs>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-600">Escolha a tarefa: carregar saldo, consultar hist&oacute;rico ou gerir a carteira da farm&aacute;cia.</p>
            </div>
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Sec&ccedil;&otilde;es da minha carteira">
                <button type="button" role="tab" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-user-wallet-tab="topup">Carregar saldo</button>
                <button type="button" role="tab" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-user-wallet-tab="history">Hist&oacute;rico</button>
                @if ($hasPharmacy)
                    <button type="button" role="tab" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-user-wallet-tab="pharmacy">Farm&aacute;cia</button>
                @endif
            </div>
        </div>
    </section>

    @if ($pharmacySalesSummary)
        <section class="mt-6 glass rounded-3xl p-6 hidden" data-user-wallet-panel="pharmacy">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Ativos da farm&aacute;cia</p>
                    <h2 class="brand-title text-3xl text-slate-900">{{ $pharmacy->name }}</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">Comiss&atilde;o sistema: {{ $commissionRatePercent }}%</span>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Pedidos</p><p class="mt-2 text-xl font-semibold">{{ $pharmacySalesSummary['orders_count'] }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Unidades vendidas</p><p class="mt-2 text-xl font-semibold">{{ $pharmacySalesSummary['units_sold'] ?? 0 }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Produtos vendidos</p><p class="mt-2 text-xl font-semibold">{{ $pharmacySalesSummary['products_sold_count'] ?? 0 }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Vendas brutas</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $pharmacySalesSummary['gross_sales'], 2, ',', '.') }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Sistema ({{ $commissionRatePercent }}%)</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $pharmacySalesSummary['system_share'], 2, ',', '.') }}</p></div>
                <div class="rounded-2xl border border-lime-200 bg-lime-50 p-4"><p class="text-xs text-lime-700">Farm&aacute;cia ({{ 100 - $commissionRatePercent }}%)</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $pharmacySalesSummary['pharmacy_share'], 2, ',', '.') }}</p></div>
            </div>
            <p class="mt-4 text-xs text-slate-500">
                Resumo calculado sobre vendas n&atilde;o canceladas/rejeitadas. A taxa/comiss&atilde;o do sistema e a quota da farm&aacute;cia s&atilde;o calculadas automaticamente por produto vendido.
            </p>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Produto</th>
                            <th class="px-4 py-3 text-right">Pedidos</th>
                            <th class="px-4 py-3 text-right">Vendidos</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-right">Sistema</th>
                            <th class="px-4 py-3 text-right">Farm&aacute;cia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse (($pharmacySalesProducts ?? collect()) as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->product_name }}</div>
                                    <div class="text-xs text-slate-500">
                                        Sistema {{ $row->system_percent }}% | Farm&aacute;cia {{ $row->pharmacy_percent }}%
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->orders_count }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->units_sold }}</td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $row->gross_sales, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">Kz {{ number_format((float) $row->system_share, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-lime-700">Kz {{ number_format((float) $row->pharmacy_share, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-4 text-slate-500" colspan="6">Sem vendas registadas para calcular os ativos da farm&aacute;cia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="mt-8" data-user-wallet-panel="topup">
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Carregar saldo</p>
            <h2 class="brand-title text-3xl text-slate-900">Pedido de carregamento</h2>
            <p class="mt-2 text-sm text-slate-600">
                Escolha o destino, informe o valor e confirme o pedido. A refer&ecirc;ncia &eacute; gerada automaticamente e, ao confirmar a refer&ecirc;ncia paga (simula&ccedil;&atilde;o), o saldo entra na carteira sem aprova&ccedil;&atilde;o manual do admin.
            </p>

            <div class="mt-5 grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
                <form class="grid gap-3" method="POST" action="{{ route('wallet.topups.store') }}" data-wallet-topup-form>
                    @csrf

                    <div class="grid gap-2">
                        <label class="text-sm font-medium text-slate-700" for="wallet_topup_target">Destino do carregamento</label>
                        <select
                            id="wallet_topup_target"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900"
                            name="target"
                            data-wallet-topup-target
                        >
                            <option value="user" {{ old('target', 'user') === 'user' ? 'selected' : '' }}>Minha carteira (saldo para compras)</option>
                            @if ($pharmacy)
                                <option value="pharmacy" {{ old('target') === 'pharmacy' ? 'selected' : '' }}>Carteira da farm&aacute;cia ({{ $pharmacy->name }})</option>
                            @endif
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <label class="text-sm font-medium text-slate-700" for="wallet_topup_amount">Valor do carregamento</label>
                        <input
                            id="wallet_topup_amount"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                            type="number"
                            min="100"
                            step="0.01"
                            name="amount"
                            value="{{ old('amount') }}"
                            placeholder="Valor em Kz"
                            required
                            data-wallet-topup-amount
                        />
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white/80 p-4" data-wallet-topup-preview>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Refer&ecirc;ncia autom&aacute;tica (simula&ccedil;&atilde;o)</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900 break-all" data-wallet-topup-preview-reference>REF-AAAAAA-000000</p>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600">
                                24 horas
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-600">
                            V&aacute;lida at&eacute;: <span class="font-semibold text-slate-900" data-wallet-topup-preview-expiry>--/--/---- --:--</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-600">
                            Destino: <span class="font-semibold text-slate-900" data-wallet-topup-preview-target>{{ old('target', 'user') === 'pharmacy' && $pharmacy ? 'Carteira da farmácia' : 'Minha carteira' }}</span>
                        </p>

                        @if (session('wallet_topup_reference') || session('wallet_topup_code'))
                            <div class="mt-3 rounded-xl border border-lime-200 bg-lime-50 px-3 py-2 text-xs text-lime-900">
                                <div><span class="font-semibold">Refer&ecirc;ncia gerada:</span> {{ session('wallet_topup_reference') ?? '-' }}</div>
                                <div><span class="font-semibold">C&oacute;digo interno:</span> {{ session('wallet_topup_code') ?? '-' }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-2">
                        <label class="text-sm font-medium text-slate-700" for="wallet_topup_notes">Observa&ccedil;&otilde;es (opcional)</label>
                        <textarea
                            id="wallet_topup_notes"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                            name="notes"
                            rows="3"
                            placeholder="Observa&ccedil;&otilde;es (opcional)"
                        >{{ old('notes') }}</textarea>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <button class="rounded-xl bg-lime-400 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                            Confirmar pedido
                        </button>
                        <button
                            class="rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:border-rose-400 hover:bg-rose-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"
                            type="submit"
                            form="wallet-topup-cancel-form"
                            @if (! $pendingTopUp) disabled @endif
                            @if ($pendingTopUp) onclick="return confirm('Cancelar o pedido de carregamento pendente e invalidar a referência atual?');" @endif
                        >
                            Cancelar pedido
                        </button>
                    </div>
                </form>
                <form id="wallet-topup-cancel-form" method="POST" action="{{ route('wallet.topups.cancel') }}" class="hidden">
                    @csrf
                    @if ($pendingTopUp)
                        <input type="hidden" name="top_up_request_id" value="{{ $pendingTopUp->id }}" />
                    @endif
                </form>

                <div class="rounded-3xl border border-slate-200 bg-white/70 p-5">
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Simula&ccedil;&atilde;o</p>
                    <h3 class="brand-title text-3xl text-slate-900">Confirmar refer&ecirc;ncia paga</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Use este bot&atilde;o para simular o pagamento por refer&ecirc;ncia e creditar automaticamente a carteira.
                    </p>

                    @if ($pendingTopUp)
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs text-amber-900">
                            <p><span class="font-semibold">Refer&ecirc;ncia:</span> {{ $pendingTopUp->payment_reference }}</p>
                            <p class="mt-1"><span class="font-semibold">Destino:</span> {{ $pendingTopUpTargetLabel }}</p>
                            <p class="mt-1"><span class="font-semibold">Valor:</span> Kz {{ number_format((float) $pendingTopUp->amount, 2, ',', '.') }}</p>
                            <p class="mt-1">
                                <span class="font-semibold">V&aacute;lida at&eacute;:</span>
                                {{ $pendingTopUpExpiresAt?->format('d/m/Y H:i') ?? '24h ap&oacute;s a gera&ccedil;&atilde;o' }}
                            </p>
                        </div>

                        <form class="mt-4" method="POST" action="{{ route('wallet.topups.confirm-reference') }}">
                            @csrf
                            <input type="hidden" name="payment_reference" value="{{ $pendingTopUp->payment_reference }}" />
                            <input type="hidden" name="amount" value="{{ (float) $pendingTopUp->amount }}" />
                            <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" type="submit">
                                Confirmar refer&ecirc;ncia paga
                            </button>
                        </form>
                        <p class="mt-3 text-xs text-slate-500">
                            O saldo &eacute; creditado automaticamente ap&oacute;s esta confirma&ccedil;&atilde;o (simula&ccedil;&atilde;o). O admin n&atilde;o precisa aprovar manualmente.
                        </p>
                    @else
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600">
                            Ainda n&atilde;o existe uma refer&ecirc;ncia pendente. Preencha o valor e clique em <span class="font-semibold text-slate-900">Confirmar pedido</span>.
                        </div>
                        <button class="mt-4 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-400" type="button" disabled>
                            Confirmar refer&ecirc;ncia paga
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($pharmacy)
        <section class="mt-8 glass rounded-3xl p-6 hidden" data-user-wallet-panel="pharmacy">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Transferir fundos</p>
            <h2 class="brand-title text-3xl text-slate-900">Pedido de levantamento / IBAN</h2>
            <p class="mt-2 text-sm text-slate-600">Use esta op&ccedil;&atilde;o para transferir fundos da sua carteira pessoal ou da farm&aacute;cia para a sua conta/IBAN. O admin valida e marca como pago.</p>
            <form class="mt-4 grid gap-3 md:grid-cols-2" method="POST" action="{{ route('wallet.withdrawals.store') }}">
                @csrf
                <select class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" name="target">
                    <option value="user">Minha carteira</option>
                    <option value="pharmacy">Carteira da farm&aacute;cia</option>
                </select>
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="number" min="100" step="0.01" name="amount" placeholder="Valor em Kz" required />
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="text" name="account_holder" placeholder="Titular da conta" required />
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="text" name="bank_name" placeholder="Banco (opcional)" />
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" type="text" name="iban" placeholder="IBAN / N.&ordm; da conta" required />
                <div class="md:col-span-2">
                    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" type="submit">Pedir transfer&ecirc;ncia</button>
                </div>
            </form>
        </section>
    @endif

    <section class="mt-8 grid gap-6 xl:grid-cols-2 hidden" data-user-wallet-panel="history">
        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="brand-title text-3xl text-slate-900">Carregamentos</h2>
                    <span class="text-xs text-slate-500">{{ $topUpRequests->count() }} registos</span>
                </div>
                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 md:hidden" data-user-wallet-fold-toggle="topups" data-open-label="Ocultar" data-closed-label="Ver tabela">
                    Ocultar
                </button>
            </div>
            <div class="mt-4" data-user-wallet-fold-panel="topups">
            <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[28rem]">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="hidden md:table-cell px-3 sm:px-4 py-3 text-left">Destino</th><th class="px-3 sm:px-4 py-3 text-left">Refer&ecirc;ncia</th><th class="px-3 sm:px-4 py-3 text-right">Valor</th><th class="px-3 sm:px-4 py-3 text-left">Estado</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($topUpRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="hidden md:table-cell px-3 sm:px-4 py-3">{{ $item->pharmacy ? 'Farm&aacute;cia' : 'Pessoal' }}</td>
                                <td class="px-3 sm:px-4 py-3"><div class="font-semibold">{{ $item->reference_code }}</div><div class="text-xs text-slate-500">{{ $item->payment_reference }}</div><div class="mt-1 text-[11px] text-slate-500 md:hidden">{{ $item->pharmacy ? 'Farm&aacute;cia' : 'Pessoal' }}</div></td>
                                <td class="px-3 sm:px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-3 sm:px-4 py-3"><span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem pedidos de carregamento.</td></tr>
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
                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 md:hidden" data-user-wallet-fold-toggle="withdrawals" data-open-label="Ocultar" data-closed-label="Ver tabela">
                    Ver tabela
                </button>
            </div>
            <div class="mt-4 hidden md:block" data-user-wallet-fold-panel="withdrawals">
            <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[28rem]">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="hidden md:table-cell px-3 sm:px-4 py-3 text-left">Origem</th><th class="px-3 sm:px-4 py-3 text-left">IBAN</th><th class="px-3 sm:px-4 py-3 text-right">Valor</th><th class="px-3 sm:px-4 py-3 text-left">Estado</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($withdrawRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="hidden md:table-cell px-3 sm:px-4 py-3">{{ $item->pharmacy ? 'Farm&aacute;cia' : 'Pessoal' }}</td>
                                <td class="px-3 sm:px-4 py-3"><div class="font-semibold">{{ $item->iban }}</div><div class="text-xs text-slate-500">{{ $item->account_holder }}</div><div class="mt-1 text-[11px] text-slate-500 md:hidden">{{ $item->pharmacy ? 'Farm&aacute;cia' : 'Pessoal' }}</div></td>
                                <td class="px-3 sm:px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-3 sm:px-4 py-3"><span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem pedidos de levantamento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </section>

    <section class="mt-8 glass rounded-3xl p-6 hidden" data-user-wallet-panel="history">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="brand-title text-3xl text-slate-900">Movimentos recentes</h2>
                <span class="text-xs text-slate-500">{{ $transactions->count() }} movimentos</span>
            </div>
            <button type="button" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 md:hidden" data-user-wallet-fold-toggle="transactions" data-open-label="Ocultar" data-closed-label="Ver tabela">
                Ver tabela
            </button>
        </div>
        <div class="mt-4 hidden md:block" data-user-wallet-fold-panel="transactions">
        <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[28rem]">
            <table class="min-w-full text-sm">
                <thead class="bg-white/90 text-slate-500"><tr><th class="px-3 sm:px-4 py-3 text-left">Carteira</th><th class="hidden sm:table-cell px-3 sm:px-4 py-3 text-left">Tipo</th><th class="hidden lg:table-cell px-3 sm:px-4 py-3 text-left">Descri&ccedil;&atilde;o</th><th class="px-3 sm:px-4 py-3 text-right">Montante</th><th class="px-3 sm:px-4 py-3 text-left">Data</th></tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                    @forelse ($transactions as $tx)
                        <tr>
                            <td class="px-3 sm:px-4 py-3">{{ $tx->walletAccount?->owner_type === 'pharmacy' ? 'Farm&aacute;cia' : 'Pessoal' }}<div class="mt-1 text-[11px] text-slate-500 uppercase sm:hidden">{{ $tx->category }}</div><div class="mt-1 text-[11px] text-slate-500 lg:hidden">{{ $tx->description ?: '-' }}</div></td>
                            <td class="hidden sm:table-cell px-3 sm:px-4 py-3 uppercase">{{ $tx->category }}</td>
                            <td class="hidden lg:table-cell px-3 sm:px-4 py-3">{{ $tx->description ?: '-' }}</td>
                            <td class="px-3 sm:px-4 py-3 text-right font-semibold {{ $tx->direction === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $tx->direction === 'credit' ? '+' : '-' }}Kz {{ number_format((float) $tx->amount, 2, ',', '.') }}</td>
                            <td class="px-3 sm:px-4 py-3">{{ $tx->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-4 text-slate-500" colspan="5">Sem movimentos registados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </section>

    <script>
        (() => {
            const tabRoot = document.querySelector('[data-user-wallet-tabs]');
            if (!tabRoot) return;

            const buttons = Array.from(tabRoot.querySelectorAll('[data-user-wallet-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-user-wallet-panel]'));
            const foldButtons = Array.from(document.querySelectorAll('[data-user-wallet-fold-toggle]'));
            const foldPanels = Array.from(document.querySelectorAll('[data-user-wallet-fold-panel]'));
            const topUpAmountInput = document.querySelector('[data-wallet-topup-amount]');
            const topUpTargetSelect = document.querySelector('[data-wallet-topup-target]');
            const topUpPreviewReference = document.querySelector('[data-wallet-topup-preview-reference]');
            const topUpPreviewExpiry = document.querySelector('[data-wallet-topup-preview-expiry]');
            const topUpPreviewTarget = document.querySelector('[data-wallet-topup-preview-target]');

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
                buttons.forEach((button) => setActiveButton(button, button.dataset.userWalletTab === name));
                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.userWalletPanel !== name);
                });
            };

            const isMobile = () => window.matchMedia('(max-width: 767px)').matches;
            const setFoldState = (id, open) => {
                const panel = foldPanels.find((item) => item.dataset.userWalletFoldPanel === id);
                const button = foldButtons.find((item) => item.dataset.userWalletFoldToggle === id);
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
                setFoldState('transactions', false);
            };

            const formatDateTime = (date) => new Intl.DateTimeFormat('pt-PT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }).format(date);

            const randomDigits = (length) => {
                const max = Math.pow(10, length) - 1;
                return String(Math.floor(Math.random() * max)).padStart(length, '0');
            };

            const buildSimulatedReference = () => {
                const now = new Date();
                const yy = String(now.getFullYear()).slice(-2);
                const mm = String(now.getMonth() + 1).padStart(2, '0');
                const dd = String(now.getDate()).padStart(2, '0');
                return `REF-${yy}${mm}${dd}-${randomDigits(6)}`;
            };

            const refreshTopUpPreview = () => {
                if (!topUpAmountInput || !topUpPreviewReference || !topUpPreviewExpiry || !topUpPreviewTarget) {
                    return;
                }

                const amount = Number(topUpAmountInput.value || 0);
                const hasAmount = Number.isFinite(amount) && amount >= 100;
                const selectedTargetLabel = topUpTargetSelect
                    ? (topUpTargetSelect.options[topUpTargetSelect.selectedIndex]?.text || 'Minha carteira')
                    : 'Minha carteira';

                topUpPreviewTarget.textContent = selectedTargetLabel.replace(/\s+\(.+\)\s*$/, '');

                if (!hasAmount) {
                    topUpPreviewReference.textContent = 'REF-AAAAAA-000000';
                    topUpPreviewExpiry.textContent = '--/--/---- --:--';
                    return;
                }

                const expiresAt = new Date(Date.now() + (24 * 60 * 60 * 1000));
                topUpPreviewReference.textContent = buildSimulatedReference();
                topUpPreviewExpiry.textContent = formatDateTime(expiresAt);
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => openTab(button.dataset.userWalletTab));
            });

            foldButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.userWalletFoldToggle;
                    const panel = foldPanels.find((item) => item.dataset.userWalletFoldPanel === id);
                    if (!panel) return;
                    const open = panel.classList.contains('hidden');
                    setFoldState(id, open);
                });
            });

            if (topUpAmountInput) {
                topUpAmountInput.addEventListener('input', refreshTopUpPreview);
                topUpAmountInput.addEventListener('change', refreshTopUpPreview);
            }

            if (topUpTargetSelect) {
                topUpTargetSelect.addEventListener('change', refreshTopUpPreview);
            }

            openTab('topup');
            applyMobileFoldDefaults();
            refreshTopUpPreview();
            window.addEventListener('resize', () => {
                applyMobileFoldDefaults();
            });
        })();
    </script>
@endsection

