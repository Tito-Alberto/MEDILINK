@extends('layouts.storefront')

@section('title', 'Farm&aacute;cias - Medlink')

@section('content')
    <section class="glass rounded-3xl p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                @if ($isAdminView)
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Gest&atilde;o</p>
                    <h1 class="brand-title mt-2 text-4xl text-slate-900">Todas as farm&aacute;cias registadas</h1>
                    <p class="mt-3 text-sm text-slate-600">
                        Consulte o estado de cada farm&aacute;cia e remova registos quando necess&aacute;rio.
                    </p>
                @else
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Parceiros</p>
                    <h1 class="brand-title mt-2 text-4xl text-slate-900">Farm&aacute;cias registadas</h1>
                    <p class="mt-3 text-sm text-slate-600">
                        Lista de farm&aacute;cias aprovadas dispon&iacute;veis na plataforma.
                    </p>
                @endif
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-right">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total</p>
                <p class="text-2xl font-semibold text-slate-900">{{ $pharmacies->count() }}</p>
            </div>
        </div>
    </section>

    @if ($pharmacies->isEmpty())
        <div class="glass mt-8 rounded-3xl p-6 text-center">
            <p class="text-lg text-slate-700">N&atilde;o existem farm&aacute;cias registadas para apresentar.</p>
        </div>
    @else
        <div class="mt-8 grid gap-5 md:grid-cols-2">
            @foreach ($pharmacies as $pharmacy)
                @php
                    $statusLabel = match ($pharmacy->status) {
                        'approved' => 'APROVADO',
                        'pending' => 'PENDENTE',
                        'rejected' => 'REJEITADO',
                        default => strtoupper((string) $pharmacy->status),
                    };

                    $statusClass = match ($pharmacy->status) {
                        'approved' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
                        'pending' => 'border-amber-200 bg-amber-100 text-amber-700',
                        'rejected' => 'border-rose-200 bg-rose-100 text-rose-700',
                        default => 'border-slate-300 bg-slate-100 text-slate-700',
                    };
                @endphp

                <article class="glass rounded-3xl p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Farm&aacute;cia #{{ $pharmacy->id }}</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $pharmacy->name }}</h2>
                            <p class="mt-2 text-sm text-slate-600">{{ $pharmacy->responsible_name }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $pharmacy->phone }}
                                @if ($pharmacy->email)
                                    &middot; {{ $pharmacy->email }}
                                @endif
                            </p>
                        </div>
                        <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-2 text-sm text-slate-600">
                        <p><span class="text-slate-500">Morada:</span> {{ $pharmacy->address ?: 'N&atilde;o indicada' }}</p>
                        @if (!empty($pharmacy->nif))
                            <p><span class="text-slate-500">NIF:</span> {{ $pharmacy->nif }}</p>
                        @endif
                        <p><span class="text-slate-500">Produtos ativos:</span> {{ $pharmacy->products_count ?? 0 }}</p>
                        <p><span class="text-slate-500">Registada em:</span> {{ $pharmacy->created_at?->format('d/m/Y') ?? '-' }}</p>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        @if ($pharmacy->status === 'approved')
                            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('storefront.pharmacy', $pharmacy) }}">
                                Ver p&aacute;gina da farm&aacute;cia
                            </a>
                        @elseif ($isAdminView)
                            <span class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-400">
                                Sem p&aacute;gina p&uacute;blica
                            </span>
                        @endif

                        @if ($isAdminView)
                            <form method="POST" action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" onsubmit="return confirm('Pretende remover esta farm&aacute;cia? Esta a&ccedil;&atilde;o desativa os produtos e elimina o registo da farm&aacute;cia.');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:border-rose-400 hover:bg-rose-100" type="submit">
                                    Remover farm&aacute;cia
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection