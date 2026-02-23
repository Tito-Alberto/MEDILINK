@extends('layouts.storefront')

@section('title', 'Pedidos da Farmácia - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Pedidos da Farmácia</h1>
                <p class="mt-2 text-sm text-slate-600">Pedidos que contem produtos da sua Farmácia.</p>
            </div>
            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.products.index') }}">
                Ver produtos
            </a>
        </div>

        <form class="mt-6 flex flex-wrap items-center gap-3 text-sm text-slate-600" method="GET" action="{{ route('pharmacy.orders.index') }}">
            <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</label>
            <x-searchable-select
                name="status"
                :options="$statuses"
                :selected="$status"
                label=""
                placeholder="Filtrar status"
                all-label="Todos status"
                id="orders-status-filter"
            />
            <button class="rounded-full border border-slate-300 px-4 py-2 text-xs uppercase tracking-[0.2em] text-slate-700 hover:border-lime-300" type="submit">
                Filtrar
            </button>
            @if (!empty($status))
                <a class="rounded-full border border-slate-200 px-4 py-2 text-xs text-slate-500 hover:text-slate-900" href="{{ route('pharmacy.orders.index') }}">Limpar</a>
            @endif
        </form>

        @if ($orders->isEmpty())
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white/80 p-8 text-center text-slate-600">
                Nenhum pedido encontrado ainda.
            </div>
        @else
            <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-800 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Pedido</th>
                            <th class="px-4 py-3 text-left">Cliente</th>
                            <th class="px-4 py-3 text-left">Contato</th>
                            <th class="px-4 py-3 text-left">Itens</th>
                            <th class="px-4 py-3 text-left">Total</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Data</th>
                            <th class="px-4 py-3 text-left">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-white/40 text-slate-700">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-white/80">
                                <td class="px-4 py-3 font-semibold">
                                    #{{ $order->id }}
                                    @if ($order->unseen_items > 0)
                                        <span class="ml-2 rounded-full bg-lime-500/20 px-2 py-0.5 text-[10px] uppercase tracking-[0.2em] text-lime-700">
                                            Novo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $order->customer_phone }}</td>
                                <td class="px-4 py-3">{{ $order->items_count }}</td>
                                <td class="px-4 py-3 text-lime-700">Kz {{ number_format($order->pharmacy_total, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full border border-slate-300 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-600">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    <a class="text-lime-700 hover:text-lime-700" href="{{ route('pharmacy.orders.show', $order->id) }}">
                                        Ver detalhes
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection









