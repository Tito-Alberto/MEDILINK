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
            <a class="mt-6 inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-slate-500" href="/carrinho">
                Voltar ao carrinho
            </a>
        </div>
    </section>
@endsection










