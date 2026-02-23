@extends('layouts.storefront')

@section('title', 'Carrinho - Medlink')

@section('content')
    <section class="glass rounded-3xl p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Carrinho</p>
                <h1 class="brand-title mt-3 text-4xl text-slate-900">Revise seu pedido</h1>
            </div>
            @if (! empty($items))
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-rose-300">Limpar carrinho</button>
                </form>
            @endif
        </div>

        @if (empty($items))
            <div class="mt-6 text-center text-slate-600">
                <p>Seu carrinho esta vazio.</p>
                <a class="mt-4 inline-flex rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="/produtos">
                    Ver catalogo
                </a>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($items as $item)
                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">{{ $item['product']->name }}</p>
                                <p class="text-sm text-slate-500">Kz {{ number_format($item['product']->price, 2, ',', '.') }} cada</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-slate-500">Total</p>
                                <p class="text-lg font-semibold text-lime-700">Kz {{ number_format($item['line_total'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <form class="flex items-center gap-2" method="POST" action="{{ route('cart.update', $item['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input class="w-20 rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-900 focus:border-lime-400 focus:outline-none"
                                       type="number" min="1" max="99" name="qty" value="{{ $item['qty'] }}" />
                                <button class="rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-lime-300">Atualizar</button>
                            </form>
                            <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-rose-300">Remover</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white/70 p-4">
                <div>
                    <p class="text-sm text-slate-500">Subtotal</p>
                    <p class="text-2xl font-semibold text-slate-900">Kz {{ number_format($subtotal, 2, ',', '.') }}</p>
                </div>
                <div class="flex gap-3">
                    <a class="rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-slate-500" href="/produtos">Continuar comprando</a>
                    <a class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="{{ route('checkout.show') }}">
                        Finalizar pedido
                    </a>
                </div>
            </div>
        @endif
    </section>
@endsection










