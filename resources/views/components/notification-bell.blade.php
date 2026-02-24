@props(['notifications'])

@if ($notifications['enabled'])
    <details class="relative">
        <summary class="list-none cursor-pointer [&::-webkit-details-marker]:hidden">
            <span class="relative flex h-9 w-9 items-center justify-center rounded-full border border-amber-300 bg-amber-200 text-amber-700 hover:border-amber-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                    <path d="M9 17v1a3 3 0 0 0 6 0v-1"></path>
                </svg>
                @if ($notifications['total'] > 0)
                    <span class="absolute -right-1 -top-1 rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">
                        {{ $notifications['total'] }}
                    </span>
                @endif
            </span>
        </summary>
        <div class="absolute right-0 mt-3 w-80 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Notificacoes</p>

            @if ($notifications['pending'])
                <div class="mt-3 rounded-2xl border border-amber-400/30 bg-amber-500/10 p-3 text-sm text-amber-700">
                    Sua Farmácia está pendente de aprovação.
                </div>
            @else
                <div class="mt-3 space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">Pedidos recentes</p>
                            <span class="text-xs text-slate-500">{{ $notifications['order_count'] }} pedidos</span>
                        </div>
                        @if ($notifications['orders']->isEmpty())
                            <p class="mt-2 text-xs text-slate-500">Nenhum pedido recente.</p>
                        @else
                            <ul class="mt-2 space-y-2 text-xs text-slate-600">
                                @foreach ($notifications['orders'] as $order)
                                    <li class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
                                        <span>#{{ $order->id }}</span>
                                        <span>Kz {{ number_format($order->total, 2, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <a class="mt-3 inline-flex text-xs text-lime-700 hover:text-lime-700" href="{{ route('pharmacy.orders.index') }}">
                            Ver pedidos
                        </a>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">Estoque baixo</p>
                            <span class="text-xs text-slate-500">Ate {{ $notifications['threshold'] }} un.</span>
                        </div>
                        @if ($notifications['out_of_stock']->isEmpty() && $notifications['low_stock']->isEmpty())
                            <p class="mt-2 text-xs text-slate-500">Sem alertas de estoque.</p>
                        @else
                            <ul class="mt-2 space-y-2 text-xs text-slate-600">
                                @foreach ($notifications['out_of_stock'] as $product)
                                    <li class="flex items-center justify-between rounded-xl border border-rose-500/30 px-3 py-2">
                                        <span>{{ $product->name }}</span>
                                        <span class="text-rose-700">0 un.</span>
                                    </li>
                                @endforeach
                                @foreach ($notifications['low_stock'] as $product)
                                    <li class="flex items-center justify-between rounded-xl border border-amber-500/30 px-3 py-2">
                                        <span>{{ $product->name }}</span>
                                        <span class="text-amber-700">{{ $product->stock }} un.</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-4 text-xs">
                    <a class="text-lime-700 hover:text-lime-700" href="{{ route('pharmacy.products.index') }}">
                        Ver meus produtos
                    </a>
                    <a class="text-lime-700 hover:text-lime-700" href="{{ route('pharmacy.orders.index') }}">
                        Ver pedidos
                    </a>
                </div>
            @endif
        </div>
    </details>
@endif









