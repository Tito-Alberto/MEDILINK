@props(['notifications'])

@if (($notifications['enabled'] ?? false))
    <details class="relative">
        <summary class="list-none cursor-pointer [&::-webkit-details-marker]:hidden">
            <span class="relative flex h-9 w-9 items-center justify-center rounded-full border border-lime-300 bg-lime-100 text-lime-700 hover:border-lime-400 hover:bg-lime-50" title="Notificacoes de pedidos">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                    <path d="M9 17v1a3 3 0 0 0 6 0v-1"></path>
                </svg>
                @if (($notifications['total'] ?? 0) > 0)
                    <span class="absolute -right-1 -top-1 rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">
                        {{ $notifications['total'] }}
                    </span>
                @endif
            </span>
        </summary>

        <div class="absolute right-0 z-50 mt-3 w-80 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
            <div class="flex items-center justify-between">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pedidos</p>
                <span class="text-xs text-slate-500">{{ $notifications['total'] ?? 0 }} novos</span>
            </div>

            @if (($notifications['orders'] ?? collect())->isEmpty())
                <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                    Sem notificacoes novas.
                </div>
            @else
                <ul class="mt-3 space-y-2 text-xs text-slate-600">
                    @foreach ($notifications['orders'] as $order)
                        <li>
                            <a class="block rounded-xl border border-slate-200 px-3 py-2 hover:border-lime-300 hover:bg-lime-50/40" href="{{ route('orders.show', $order->id) }}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-900">Pedido #{{ $order->id }}</span>
                                    <span class="text-lime-700">Kz {{ number_format((float) $order->total, 2, ',', '.') }}</span>
                                </div>
                                <p class="mt-1 text-slate-500">
                                    Confirmado. Toque para ver os detalhes.
                                </p>
                                <p class="mt-1 text-[10px] text-slate-400">
                                    {{ $order->customer_confirmed_notified_at?->format('d/m/Y H:i') ?? ($order->created_at?->format('d/m/Y H:i') ?? '-') }}
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </details>
@endif
