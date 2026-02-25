@extends('layouts.storefront')

@section('title', 'Detalhes do pedido - Medlink')

@section('content')
    @php
        $orderStatus = mb_strtolower(trim((string) $order->status));
        $statusLabels = [
            'novo' => 'NOVO',
            'confirmado' => 'CONFIRMADO',
            'em_preparacao' => 'EM PREPARAÇÃO',
            'entregue' => 'ENTREGUE',
            'cancelado' => 'CANCELADO',
            'rejeitado' => 'REJEITADO',
        ];
        $statusBadgeClasses = [
            'novo' => 'bg-amber-100 text-amber-800 border-amber-200',
            'confirmado' => 'bg-sky-100 text-sky-800 border-sky-200',
            'em_preparacao' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'entregue' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'cancelado' => 'bg-rose-100 text-rose-800 border-rose-200',
            'rejeitado' => 'bg-rose-100 text-rose-800 border-rose-200',
        ];
        $isFinalStatus = in_array($orderStatus, ['cancelado', 'rejeitado', 'entregue'], true);
    @endphp

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
                <p class="mt-2 text-sm text-slate-500">
                    Estado:
                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses[$orderStatus] ?? 'border-slate-300 text-slate-700' }}">
                        {{ $statusLabels[$orderStatus] ?? strtoupper((string) $order->status) }}
                    </span>
                </p>
                <p class="text-sm text-slate-500">Data: <span class="text-slate-700">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</span></p>
                <p class="mt-3 text-2xl font-semibold text-lime-700">Kz {{ number_format($total, 2, ',', '.') }}</p>
                @if ($order->notes)
                    <p class="mt-2 text-sm text-slate-500">Obs: {{ $order->notes }}</p>
                @endif
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($orderStatus === 'novo')
                        <form method="POST" action="{{ route('pharmacy.orders.confirm', $order->id) }}">
                            @csrf
                            <button class="rounded-full border border-lime-300 bg-lime-400 px-4 py-2 text-xs font-semibold text-slate-900 hover:bg-lime-300" type="submit" onclick="return confirm('Confirmar este pedido e imprimir a factura do cliente?');">
                                Confirmar e imprimir factura
                            </button>
                        </form>
                    @elseif (in_array($orderStatus, ['confirmado', 'em_preparacao', 'entregue'], true))
                        <a class="rounded-full border border-slate-300 px-4 py-2 text-xs text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.orders.invoice', ['order' => $order->id, 'embed' => 1]) }}" data-open-invoice-modal-url="{{ route('pharmacy.orders.invoice', ['order' => $order->id, 'embed' => 1]) }}">
                            Imprimir factura
                        </a>
                    @endif
                    <form method="POST" action="{{ route('pharmacy.orders.unseen', $order->id) }}">
                        @csrf
                        <button class="rounded-full border border-amber-400/50 px-4 py-2 text-xs text-amber-700 hover:border-amber-300" type="submit">
                            Marcar como não visto
                        </button>
                    </form>
                    @if (! $isFinalStatus)
                        <form method="POST" action="{{ route('pharmacy.orders.status', $order->id) }}">
                            @csrf
                            <input type="hidden" name="status" value="cancelado" />
                            <button class="rounded-full border border-rose-300 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 hover:border-rose-400" type="submit" onclick="return confirm('Cancelar este pedido? O sistema vai estornar automaticamente os valores da carteira.');">
                                Cancelar pedido
                            </button>
                        </form>
                        <form method="POST" action="{{ route('pharmacy.orders.status', $order->id) }}">
                            @csrf
                            <input type="hidden" name="status" value="rejeitado" />
                            <button class="rounded-full border border-rose-500 bg-rose-500 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-600" type="submit" onclick="return confirm('Rejeitar este pedido? O sistema vai estornar automaticamente os valores da carteira.');">
                                Rejeitar pedido
                            </button>
                        </form>
                    @endif
                </div>
                @if (! $isFinalStatus)
                    <p class="mt-3 text-xs text-slate-500">
                        Ao cancelar ou rejeitar, o pedido completo &eacute; atualizado e os cr&eacute;ditos da carteira s&atilde;o estornados automaticamente.
                    </p>
                @endif
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
    <x-pharmacy-invoice-modal />
@endsection









