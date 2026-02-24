@extends('layouts.storefront')

@section('title', 'Pedido confirmado - Medlink')

@section('content')
    <section class="glass rounded-3xl p-6">
        <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Pedido confirmado</p>
        <h1 class="brand-title mt-3 text-4xl text-slate-900">Obrigado pela compra!</h1>
        <p class="mt-4 text-slate-600">Seu pedido #{{ $order->id }} foi recebido e esta em processamento.</p>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                <p class="text-sm text-slate-500">Cliente</p>
                <p class="text-lg text-slate-900">{{ $order->customer_name }}</p>
                <p class="text-sm text-slate-500">{{ $order->customer_phone }}</p>
                <p class="text-sm text-slate-500">{{ $order->customer_address }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                <p class="text-sm text-slate-500">Status</p>
                <p class="text-lg text-lime-700">{{ ucfirst($order->status) }}</p>
                <p class="text-sm text-slate-500">Total: Kz {{ number_format($order->total, 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            @foreach ($order->items as $item)
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $item->product_name }}</p>
                            <p class="text-sm text-slate-500">{{ $item->quantity }}x</p>
                        </div>
                        <p class="text-lime-700">Kz {{ number_format($item->line_total, 2, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a class="rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="/produtos">Voltar ao catálogo</a>
            <a class="rounded-full border border-slate-300 px-5 py-2 text-sm hover:border-slate-500" href="/carrinho">Novo pedido</a>
        </div>
    </section>
@endsection










