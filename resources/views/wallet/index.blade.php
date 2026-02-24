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
    @endphp

    <section class="glass rounded-3xl p-6">
        <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Minha carteira</p>
        <h1 class="brand-title mt-2 text-4xl text-slate-900">Saldo para compras e moedas da plataforma</h1>
        <p class="mt-3 text-sm text-slate-600">
            O utilizador comum usa esta carteira para carregar saldo e comprar medicamentos. Se tiver farm&aacute;cia, tamb&eacute;m ver&aacute; os ativos da farm&aacute;cia e pedidos de transfer&ecirc;ncia.
        </p>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="glass rounded-3xl p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Saldo para comprar medicamentos</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">Kz {{ number_format((float) ($purchasingBalance ?? $userWallet->balance), 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-slate-500">Carteira pessoal</p>
        </div>
        @if ($pharmacy)
            <div class="glass rounded-3xl p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Saldo da farm&aacute;cia</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">Kz {{ number_format((float) ($pharmacyWallet->balance ?? 0), 2, ',', '.') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $pharmacy->name }}</p>
            </div>
        @else
            <div class="glass rounded-3xl p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Estado da conta</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">Conta pessoal ativa</p>
                <p class="mt-1 text-sm text-slate-500">Sem farm&aacute;cia associada</p>
            </div>
        @endif
        <div class="glass rounded-3xl p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Refer&ecirc;ncia pendente</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $pendingTopUp?->reference_code ?? 'Sem pedido pendente' }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $pendingTopUp?->payment_reference ?? 'Crie um pedido abaixo.' }}</p>
        </div>
        <div class="glass rounded-3xl p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $pharmacy ? 'Pedidos de levantamento' : 'Movimentos' }}</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $pharmacy ? $withdrawRequests->count() : $transactions->count() }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $pharmacy ? 'Transfer&ecirc;ncias pedidas' : 'Registos da carteira' }}</p>
        </div>
    </section>

    @if ($pharmacySalesSummary)
        <section class="mt-6 glass rounded-3xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Ativos da farm&aacute;cia</p>
                    <h2 class="brand-title text-3xl text-slate-900">{{ $pharmacy->name }}</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">Comiss&atilde;o sistema: {{ $commissionRatePercent }}%</span>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Pedidos</p><p class="mt-2 text-xl font-semibold">{{ $pharmacySalesSummary['orders_count'] }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Vendas brutas</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $pharmacySalesSummary['gross_sales'], 2, ',', '.') }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-500">Sistema</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $pharmacySalesSummary['system_share'], 2, ',', '.') }}</p></div>
                <div class="rounded-2xl border border-lime-200 bg-lime-50 p-4"><p class="text-xs text-lime-700">Farm&aacute;cia</p><p class="mt-2 text-xl font-semibold">Kz {{ number_format((float) $pharmacySalesSummary['pharmacy_share'], 2, ',', '.') }}</p></div>
            </div>
        </section>
    @endif

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Comprar moeda</p>
            <h2 class="brand-title text-3xl text-slate-900">Pedido de carregamento</h2>
            <p class="mt-2 text-sm text-slate-600">Peça uma referência (ou receba uma referência gerada pelo admin) e use-a para carregar saldo da carteira.</p>
            <form class="mt-4 grid gap-3" method="POST" action="{{ route('wallet.topups.store') }}">
                @csrf
                <select class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm" name="target">
                    <option value="user">Minha carteira</option>
                    @if ($pharmacy)<option value="pharmacy">Carteira da farm&aacute;cia</option>@endif
                </select>
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="number" min="100" step="0.01" name="amount" placeholder="Valor em Kz" required />
                <textarea class="rounded-xl border border-slate-300 px-3 py-2 text-sm" name="notes" rows="2" placeholder="Observa&ccedil;&otilde;es (opcional)"></textarea>
                <button class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">Gerar refer&ecirc;ncia</button>
            </form>
        </div>

        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Pagamento por refer&ecirc;ncia</p>
            <h2 class="brand-title text-3xl text-slate-900">Cr&eacute;dito autom&aacute;tico</h2>
            <p class="mt-2 text-sm text-slate-600">Depois do pagamento por referência, confirme a referência para simular a integração automática e creditar o valor na carteira.</p>
            <form class="mt-4 grid gap-3" method="POST" action="{{ route('wallet.topups.confirm-reference') }}">
                @csrf
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="text" name="payment_reference" value="{{ old('payment_reference', $pendingTopUp?->payment_reference) }}" placeholder="Referência de pagamento (ex.: REF-260224-123456)" required />
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm" type="number" min="100" step="0.01" name="amount" value="{{ old('amount', $pendingTopUp?->amount) }}" placeholder="Valor pago (Kz)" />
                @if ($pendingTopUp)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        Referência pendente: <span class="font-semibold">{{ $pendingTopUp->payment_reference }}</span> | Valor: Kz {{ number_format((float) $pendingTopUp->amount, 2, ',', '.') }}
                    </div>
                @endif
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" type="submit">Confirmar pagamento por refer&ecirc;ncia</button>
            </form>
        </div>
    </section>

    @if ($pharmacy)
        <section class="mt-8 glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Transferir fundos</p>
            <h2 class="brand-title text-3xl text-slate-900">Pedido de levantamento / IBAN</h2>
            <p class="mt-2 text-sm text-slate-600">Use esta opção para transferir fundos da sua carteira pessoal ou da farmácia para conta/IBAN. O admin valida e marca como pago.</p>
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

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="brand-title text-3xl text-slate-900">Carregamentos</h2>
                <span class="text-xs text-slate-500">{{ $topUpRequests->count() }} registos</span>
            </div>
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Destino</th><th class="px-4 py-3 text-left">Refer&ecirc;ncia</th><th class="px-4 py-3 text-right">Valor</th><th class="px-4 py-3 text-left">Estado</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($topUpRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $item->pharmacy ? 'Farm&aacute;cia' : 'Pessoal' }}</td>
                                <td class="px-4 py-3"><div class="font-semibold">{{ $item->reference_code }}</div><div class="text-xs text-slate-500">{{ $item->payment_reference }}</div></td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem pedidos de carregamento.</td></tr>
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
                    <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Origem</th><th class="px-4 py-3 text-left">IBAN</th><th class="px-4 py-3 text-right">Valor</th><th class="px-4 py-3 text-left">Estado</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($withdrawRequests as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $item->pharmacy ? 'Farm&aacute;cia' : 'Pessoal' }}</td>
                                <td class="px-4 py-3"><div class="font-semibold">{{ $item->iban }}</div><div class="text-xs text-slate-500">{{ $item->account_holder }}</div></td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $statusLabels[$status] ?? strtoupper($status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem pedidos de levantamento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mt-8 glass rounded-3xl p-6">
        <div class="flex items-center justify-between gap-3">
            <h2 class="brand-title text-3xl text-slate-900">Movimentos recentes</h2>
            <span class="text-xs text-slate-500">{{ $transactions->count() }} movimentos</span>
        </div>
        <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full text-sm">
                <thead class="bg-white/90 text-slate-500"><tr><th class="px-4 py-3 text-left">Carteira</th><th class="px-4 py-3 text-left">Tipo</th><th class="px-4 py-3 text-left">Descri&ccedil;&atilde;o</th><th class="px-4 py-3 text-right">Montante</th><th class="px-4 py-3 text-left">Data</th></tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                    @forelse ($transactions as $tx)
                        <tr>
                            <td class="px-4 py-3">{{ $tx->walletAccount?->owner_type === 'pharmacy' ? 'Farm&aacute;cia' : 'Pessoal' }}</td>
                            <td class="px-4 py-3 uppercase">{{ $tx->category }}</td>
                            <td class="px-4 py-3">{{ $tx->description ?: '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $tx->direction === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $tx->direction === 'credit' ? '+' : '-' }}Kz {{ number_format((float) $tx->amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $tx->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-4 text-slate-500" colspan="5">Sem movimentos registados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
