@extends('layouts.storefront')

@section('title', 'Checkout - Medlink')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Checkout</p>
            <h1 class="brand-title mt-3 text-4xl text-slate-900">Informe seus dados</h1>
            <form class="mt-6 space-y-4" method="POST" action="{{ route('checkout.place') }}">
                @csrf
                <div>
                    <label class="text-sm text-slate-600" for="customer_name">Nome completo</label>
                    <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none"
                           type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" placeholder="Ex: Ana Souza" required />
                </div>
                <div>
                    <label class="text-sm text-slate-600" for="customer_phone">Telefone</label>
                    <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none"
                           type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" placeholder="(00) 00000-0000" required />
                </div>
                <div>
                    <label class="text-sm text-slate-600" for="customer_address">Endereço de entrega</label>
                    <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none"
                           type="text" name="customer_address" id="customer_address" value="{{ old('customer_address') }}" placeholder="Rua, numero, bairro" required />
                </div>
                <div>
                    <label class="text-sm text-slate-600" for="notes">Observacoes</label>
                    <textarea class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none"
                              name="notes" id="notes" rows="3" placeholder="Opcional">{{ old('notes') }}</textarea>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Forma de pagamento</p>
                    @if (auth()->check())
                        @php
                            $walletBalance = (float) ($walletPurchaseBalance ?? 0);
                            $walletSufficient = $walletBalance >= (float) $total;
                            $selectedPaymentMethod = old('payment_method', $walletSufficient ? 'wallet' : 'cash');
                        @endphp
                        <p class="mt-2 text-sm text-slate-600">
                            Saldo da sua carteira: <span class="font-semibold text-slate-900">Kz {{ number_format($walletBalance, 2, ',', '.') }}</span>
                        </p>
                        <label class="mt-3 flex items-start gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm text-slate-700">
                            <input class="mt-1" type="radio" name="payment_method" value="wallet" {{ $selectedPaymentMethod === 'wallet' ? 'checked' : '' }} {{ $walletSufficient ? '' : 'disabled' }} />
                            <span>
                                <span class="block font-semibold text-slate-900">Pagar com Minha carteira</span>
                                <span class="block text-xs text-slate-500">
                                    {{ $walletSufficient ? 'O valor será debitado automaticamente da sua carteira.' : 'Saldo insuficiente para este pedido.' }}
                                </span>
                            </span>
                        </label>
                        <label class="mt-2 flex items-start gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm text-slate-700">
                            <input class="mt-1" type="radio" name="payment_method" value="cash" {{ $selectedPaymentMethod === 'cash' ? 'checked' : '' }} />
                            <span>
                                <span class="block font-semibold text-slate-900">Pagamento normal</span>
                                <span class="block text-xs text-slate-500">A compra é registada sem débito da carteira do cliente.</span>
                            </span>
                        </label>
                        @if (! $walletSufficient)
                            <p class="mt-2 text-xs text-amber-700">
                                Carregue a sua carteira em <a class="font-semibold underline" href="{{ route('wallet.index') }}">Minha carteira</a> para pagar com saldo.
                            </p>
                        @endif
                    @else
                        <input type="hidden" name="payment_method" value="cash" />
                        <p class="mt-2 text-sm text-slate-600">
                            Inicie sessão para usar o saldo da sua carteira em compras. Sem sessão, o pedido segue como pagamento normal.
                        </p>
                        <a class="mt-3 inline-flex rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:border-lime-300" href="{{ route('login') }}">
                            Entrar para usar a carteira
                        </a>
                    @endif
                </div>
                <button class="w-full rounded-2xl bg-lime-400 py-3 font-semibold text-slate-900 hover:bg-lime-300">
                    Confirmar pedido
                </button>
            </form>
        </div>
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-amber-300">Resumo</p>
            <h2 class="brand-title mt-3 text-3xl text-slate-900">Itens no carrinho</h2>
            <div class="mt-6 space-y-4">
                @foreach ($items as $item)
                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                        <p class="font-semibold text-slate-900">{{ $item['product']->name }}</p>
                        <p class="text-sm text-slate-500">{{ $item['qty'] }}x</p>
                        <p class="text-sm text-lime-700">Kz {{ number_format($item['line_total'], 2, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 space-y-2 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span>Kz {{ number_format($subtotal, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Entrega</span>
                    <span>Kz {{ number_format($deliveryFee, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-base font-semibold text-slate-900">
                    <span>Total</span>
                    <span>Kz {{ number_format($total, 2, ',', '.') }}</span>
                </div>
            </div>
            @auth
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white/70 p-4 text-sm">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Saldo da carteira</span>
                        <span class="font-semibold text-slate-900">Kz {{ number_format((float) ($walletPurchaseBalance ?? 0), 2, ',', '.') }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs {{ ((float) ($walletPurchaseBalance ?? 0) >= (float) $total) ? 'text-emerald-700' : 'text-amber-700' }}">
                        <span>{{ ((float) ($walletPurchaseBalance ?? 0) >= (float) $total) ? 'Saldo suficiente para pagar com carteira' : 'Saldo insuficiente para pagar com carteira' }}</span>
                    </div>
                </div>
            @endauth
            <a class="mt-6 inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-slate-500" href="/carrinho">
                Voltar ao carrinho
            </a>
        </div>
    </section>
@endsection










