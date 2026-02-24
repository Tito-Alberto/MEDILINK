@extends('layouts.storefront')

@section('title', 'Status da Farmácia - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Sua Farmácia</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Acompanhe o status do cadastro da sua Farmácia.
                </p>
            </div>
            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="/produtos">
                Ver produtos
            </a>
        </div>

        @if (! $pharmacy)
            <div class="mt-8 rounded-2xl border border-lime-400/30 bg-lime-500/10 p-6 text-sm text-lime-700">
                <p class="text-lg font-semibold text-slate-900">Nenhuma Farmácia cadastrada.</p>
                <p class="mt-2">Quando quiser, envie o cadastro da Farmácia para aprovação.</p>
                <a class="mt-4 inline-flex rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="{{ route('pharmacy.create') }}">
                    Cadastrar Farmácia
                </a>
            </div>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-6">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Dados enviados</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-900">{{ $pharmacy->name }}</h2>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <p><span class="text-slate-500">Responsável:</span> {{ $pharmacy->responsible_name }}</p>
                        <p><span class="text-slate-500">NIF:</span> {{ $pharmacy->nif ?? 'Não informado' }}</p>
                        <p><span class="text-slate-500">Telefone:</span> {{ $pharmacy->phone }}</p>
                        <p><span class="text-slate-500">Email:</span> {{ $pharmacy->email }}</p>
                        <p><span class="text-slate-500">Endereço:</span> {{ $pharmacy->address ?? 'Não informado' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-6">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</p>
                    @if ($pharmacy->status === 'approved')
                        <p class="mt-3 text-2xl font-semibold text-lime-700">Aprovada</p>
                        <p class="mt-2 text-sm text-slate-600">Sua Farmácia esta ativa. Ja pode publicar produtos.</p>
                        <a class="mt-4 inline-flex rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="{{ route('pharmacy.products.index') }}">
                            Gerir produtos
                        </a>
                    @elseif ($pharmacy->status === 'rejected')
                        <p class="mt-3 text-2xl font-semibold text-rose-300">Recusada</p>
                        <p class="mt-2 text-sm text-slate-600">Entre em contato com o admin para ajustar o cadastro.</p>
                    @else
                        <p class="mt-3 text-2xl font-semibold text-amber-300">Pendente</p>
                        <p class="mt-2 text-sm text-slate-600">Estamos avaliando sua solicitação.</p>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white/90 p-4 text-xs text-slate-500">
                        <p>Cadastro enviado em {{ $pharmacy->created_at?->format('d/m/Y') ?? '-' }}.</p>
                        <p>Ultima atualizacao em {{ $pharmacy->updated_at?->format('d/m/Y') ?? '-' }}.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection









