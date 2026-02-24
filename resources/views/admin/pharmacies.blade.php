@extends('layouts.storefront')

@section('title', 'Aprova&ccedil;&atilde;o de Farm&aacute;cias - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Aprova&ccedil;&atilde;o de Farm&aacute;cias</h1>
                <p class="mt-2 text-sm text-slate-600">Gerencie solicita&ccedil;&otilde;es pendentes e hist&oacute;rico.</p>
            </div>
            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="/farmacia">
                Minha Farm&aacute;cia
            </a>
        </div>

        <div class="mt-8">
            <h2 class="text-lg font-semibold text-slate-900">Pendentes</h2>
            @if ($pending->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Nenhuma solicita&ccedil;&atilde;o pendente.</p>
            @else
                <div class="mt-4 space-y-4">
                    @foreach ($pending as $pharmacy)
                        <div class="rounded-2xl border border-slate-200 bg-white/90 p-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pedido #{{ $pharmacy->id }}</p>
                                    <p class="mt-2 text-xl font-semibold text-slate-900">{{ $pharmacy->name }}</p>
                                    <p class="mt-1 text-sm text-slate-600">{{ $pharmacy->responsible_name }} &middot; {{ $pharmacy->phone }}</p>
                                    <p class="text-sm text-slate-500">{{ $pharmacy->email }}</p>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <form method="POST" action="{{ route('admin.pharmacies.approve', $pharmacy) }}">
                                        @csrf
                                        <button class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                                            Aprovar
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pharmacies.reject', $pharmacy) }}">
                                        @csrf
                                        <button class="rounded-full border border-rose-400/50 px-4 py-2 text-sm text-rose-700 hover:border-rose-300" type="submit">
                                            Recusar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">Enviado em {{ $pharmacy->created_at?->format('d/m/Y') ?? '-' }}.</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-10 border-t border-slate-200 pt-8">
            <h2 class="text-lg font-semibold text-slate-900">Hist&oacute;rico</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-800 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Farm&aacute;cia</th>
                            <th class="px-4 py-3 text-left">Respons&aacute;vel</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Atualizado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-white/40 text-slate-700">
                        @foreach ($pharmacies as $pharmacy)
                            <tr>
                                <td class="px-4 py-3">{{ $pharmacy->name }}</td>
                                <td class="px-4 py-3">{{ $pharmacy->responsible_name }}</td>
                                <td class="px-4 py-3">
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
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $pharmacy->updated_at?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
