@extends('layouts.storefront')

@section('title', 'Detalhes do pedido - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Pedido #{{ $order->id }}</h1>
                <p class="mt-2 text-sm text-slate-600">Itens vendidos pela sua Farmácia.</p>
            </div>
            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.orders.index') }}">
                Voltar aos pedidos
            </a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white/80 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Cliente</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $order->customer_name }}</p>
                <p class="text-sm text-slate-500">{{ $order->customer_phone }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $order->customer_address }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/80 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Resumo</p>
                <p class="mt-2 text-sm text-slate-500">Status: <span class="text-slate-700">{{ $order->status }}</span></p>
                <p class="text-sm text-slate-500">Data: <span class="text-slate-700">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</span></p>
                <p class="mt-3 text-2xl font-semibold text-lime-700">Kz {{ number_format($total, 2, ',', '.') }}</p>
                @if ($order->notes)
                    <p class="mt-2 text-sm text-slate-500">Obs: {{ $order->notes }}</p>
                @endif
                <form class="mt-4" method="POST" action="{{ route('pharmacy.orders.unseen', $order->id) }}">
                    @csrf
                    <button class="rounded-full border border-amber-400/50 px-4 py-2 text-xs text-amber-700 hover:border-amber-300" type="submit">
                        Marcar como nao visto
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-800 text-sm">
                <thead class="bg-white/90 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Produto</th>
                        <th class="px-4 py-3 text-left">Quantidade</th>
                        <th class="px-4 py-3 text-left">Preço</th>
                        <th class="px-4 py-3 text-left">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 bg-white/40 text-slate-700">
                    @foreach ($items as $item)
                        <tr class="hover:bg-white/80">
                            <td class="px-4 py-3">{{ $item->product_name }}</td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-slate-500">Kz {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-lime-700">Kz {{ number_format($item->line_total, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection









