@extends('layouts.storefront')

@section('title', 'Produtos - Medlink')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-xs uppercase tracking-[0.3em] text-lime-700">Painel</p>
        <h1 class="brand-title text-4xl text-slate-900">Produtos</h1>
        <p class="text-sm text-slate-500">Organize estoque, Preços e destaques.</p>
    </div>
    <a href="#" class="rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300">
        Novo produto
    </a>
</div>

<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    @forelse($products ?? [] as $product)
        <div class="glass rounded-3xl p-5 transition hover:border-lime-400/50 hover:shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Produto</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $product->name ?? 'Produto' }}</h3>
                </div>
                <span class="rounded-full bg-lime-500/10 px-3 py-1 text-xs font-semibold text-lime-700">
                    {{ $product->stock ?? 0 }} un.
                </span>
            </div>
            <p class="mt-3 text-sm text-slate-500">
                {{ $product->description ?? 'Descrição não disponível' }}
            </p>
            <div class="mt-5 flex items-center justify-between">
                <span class="text-2xl font-semibold text-lime-700">
                    Kz {{ number_format($product->price ?? 0, 2, ',', '.') }}
                </span>
                <span class="text-xs text-slate-500">Atualizado</span>
            </div>
            <div class="mt-5 flex gap-3">
                <a href="#" class="flex-1 rounded-full border border-slate-300 px-4 py-2 text-center text-sm text-slate-700 hover:border-lime-400 hover:text-slate-900">
                    Editar
                </a>
                <button class="flex-1 rounded-full border border-rose-500/40 px-4 py-2 text-sm text-rose-700 hover:border-rose-400 hover:text-rose-700">
                    Deletar
                </button>
            </div>
        </div>
    @empty
        <div class="glass rounded-3xl p-10 text-center text-slate-500 md:col-span-2 lg:col-span-3">
            Nenhum produto cadastrado.
        </div>
    @endforelse
</div>
@endsection









