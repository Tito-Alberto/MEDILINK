@extends('layouts.storefront')

@section('title', 'Minhas compras - Medlink')

@section('content')
    @php
        $statusStyles = [
            'novo' => 'border-amber-200 bg-amber-100 text-amber-700',
            'pendente' => 'border-amber-200 bg-amber-100 text-amber-700',
            'confirmado' => 'border-lime-200 bg-lime-100 text-lime-700',
            'processando' => 'border-sky-200 bg-sky-100 text-sky-700',
            'em processamento' => 'border-sky-200 bg-sky-100 text-sky-700',
            'entregue' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
            'concluido' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
            'concluído' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
            'cancelado' => 'border-rose-200 bg-rose-100 text-rose-700',
            'rejeitado' => 'border-rose-200 bg-rose-100 text-rose-700',
        ];
    @endphp

    <section class="glass rounded-3xl p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-lime-700">Cliente</p>
                <h1 class="brand-title text-4xl text-slate-900">Minhas compras</h1>
                <p class="text-sm text-slate-500">Hist&oacute;rico dos seus pedidos realizados na Medlink.</p>
            </div>
            <a class="inline-flex items-center rounded-full border border-slate-300 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-lime-300 hover:text-slate-900" href="{{ route('storefront.index') }}">
                Continuar comprando
            </a>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-600">
            Total de registos: <span class="font-semibold text-slate-900">{{ $orders->total() }}</span>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white/80">
            <table class="min-w-full text-sm">
                <thead class="bg-white text-xs uppercase tracking-[0.2em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Pedido</th>
                        <th class="px-4 py-3 text-left">Data</th>
                        <th class="px-4 py-3 text-left">Itens</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">A&ccedil;&atilde;o</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse ($orders as $order)
                        @php
                            $normalizedStatus = mb_strtolower(trim((string) $order->status));
                            $statusClass = $statusStyles[$normalizedStatus] ?? 'border-slate-200 bg-slate-100 text-slate-700';
                        @endphp
                        <tr class="hover:bg-lime-50/30">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                #{{ $order->id }}
                                @if ($order->invoice_number)
                                    <div class="mt-1 text-xs font-normal text-slate-500">Fatura: {{ $order->invoice_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ (int) ($order->items_count ?? 0) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst((string) $order->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-lime-700">
                                Kz {{ number_format((float) $order->total, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-lime-300 hover:text-slate-900" href="{{ route('orders.show', $order->id) }}">
                                    Ver pedido
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-slate-500" colspan="6">
                                Nenhum registo de compra encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
@endsection
