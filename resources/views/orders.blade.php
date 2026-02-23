@extends('layouts.storefront')

@section('title', 'Pedidos - Medlink')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-xs uppercase tracking-[0.3em] text-lime-700">Painel</p>
        <h1 class="brand-title text-4xl text-slate-900">Pedidos</h1>
        <p class="text-sm text-slate-500">Gerencie pedidos e acompanhe o status das entregas.</p>
    </div>
    <a href="#" class="rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300">
        Novo pedido
    </a>
</div>

<div class="glass overflow-hidden rounded-3xl">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-white/90 text-xs uppercase tracking-widest text-slate-500">
                <tr>
                    <th class="px-6 py-4 text-left">ID</th>
                    <th class="px-6 py-4 text-left">Cliente</th>
                    <th class="px-6 py-4 text-left">Total</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Data</th>
                    <th class="px-6 py-4 text-left">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($orders ?? [] as $order)
                    <tr class="transition hover:bg-white/80">
                        <td class="px-6 py-4 font-semibold text-slate-900">#{{ $order->id ?? '-' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $order->user->name ?? 'Usuario' }}</td>
                        <td class="px-6 py-4 font-semibold text-lime-700">
                            Kz {{ number_format($order->total ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full bg-lime-500/10 px-3 py-1 text-xs font-semibold text-lime-700">
                                {{ $order->status ?? 'Pendente' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $order->created_at ? $order->created_at->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 space-x-3 text-sm">
                            <a href="#" class="text-lime-700 hover:text-lime-700">Ver</a>
                            <a href="#" class="text-amber-700 hover:text-amber-700">Editar</a>
                            <button class="text-rose-700 hover:text-rose-700">Deletar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            Nenhum pedido cadastrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection









