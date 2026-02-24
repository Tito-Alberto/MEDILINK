@props(['notifications'])

@if (($notifications['enabled'] ?? false))
    <details class="relative">
        <summary class="list-none cursor-pointer [&::-webkit-details-marker]:hidden">
            <span class="relative flex h-9 w-9 items-center justify-center rounded-full border border-sky-300 bg-sky-100 text-sky-700 hover:border-sky-400 hover:bg-sky-50" title="Aprovacoes de farmacias">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 21h18"></path>
                    <path d="M5 21V7l7-4 7 4v14"></path>
                    <path d="M9 10h6"></path>
                    <path d="M9 14h6"></path>
                </svg>
                @if (($notifications['pending_count'] ?? 0) > 0)
                    <span class="absolute -right-1 -top-1 rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">
                        {{ $notifications['pending_count'] }}
                    </span>
                @endif
            </span>
        </summary>

        <div class="absolute right-0 z-50 mt-3 w-80 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
            <div class="flex items-center justify-between">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Admin</p>
                <span class="text-xs text-slate-500">{{ $notifications['pending_count'] ?? 0 }} pendentes</span>
            </div>

            @if (($notifications['pending_count'] ?? 0) === 0)
                <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                    Nenhuma farmácia pendente de aprovação.
                </div>
            @else
                <ul class="mt-3 space-y-2 text-xs text-slate-600">
                    @foreach (($notifications['pending'] ?? collect()) as $pharmacy)
                        <li class="rounded-xl border border-slate-200 px-3 py-2">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-slate-900">{{ $pharmacy->name }}</span>
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                    Pendente
                                </span>
                            </div>
                            <p class="mt-1 text-slate-500">{{ $pharmacy->responsible_name }}</p>
                            <p class="mt-1 text-[10px] text-slate-400">
                                {{ $pharmacy->created_at?->format('d/m/Y') ?? '-' }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif

            <a class="mt-4 inline-flex text-xs text-lime-700 hover:text-lime-700" href="{{ route('admin.pharmacies.index') }}">
                Ir para aprovacoes
            </a>
        </div>
    </details>
@endif
