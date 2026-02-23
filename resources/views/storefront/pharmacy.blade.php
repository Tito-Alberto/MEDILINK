@extends('layouts.storefront')

@section('title', $pharmacy->name . ' - Medlink')

@section('content')
    <section class="grid gap-6 md:grid-cols-[1.1fr_0.9fr]">
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Farmácia</p>
            <h1 class="brand-title mt-3 text-4xl text-slate-900">{{ $pharmacy->name }}</h1>
            <p class="mt-3 text-sm text-slate-600">{{ $pharmacy->address ?? 'Endereco indisponivel.' }}</p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                <span class="rounded-full border border-slate-300 px-3 py-1">{{ $pharmacy->email }}</span>
                <span class="rounded-full border border-slate-300 px-3 py-1">{{ $pharmacy->phone }}</span>
            </div>
            <p class="mt-4 text-xs uppercase tracking-[0.2em] text-lime-700">Farmácia aprovada</p>
        </div>
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-amber-300">Resumo</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $products->count() }} produtos ativos</p>
            <p class="mt-2 text-sm text-slate-500">Atualize seu carrinho com ofertas desta Farmácia.</p>
            <a class="mt-6 inline-flex rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="/carrinho">
                Ver carrinho
            </a>
        </div>
    </section>

    <section class="mt-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Catalogo</p>
                <h2 class="brand-title text-4xl text-slate-900">Produtos da Farmácia</h2>
            </div>
            <a class="text-sm text-slate-500 hover:text-slate-900" href="/produtos">Ver todos produtos</a>
        </div>

        @if ($products->isEmpty())
            <div class="glass mt-8 rounded-3xl p-6 text-center">
                <p class="text-lg text-slate-700">Nenhum produto ativo nesta Farmácia.</p>
                <p class="mt-2 text-sm text-slate-500">Volte mais tarde para novas ofertas.</p>
            </div>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $imagePath = $product->image_url ?: 'images/products/placeholder.svg';
                        $imageUrl = str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/')
                            ? $imagePath
                            : asset($imagePath);
                    @endphp
                    <div class="glass fade-in rounded-3xl p-5">
                        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white/90">
                            <img class="h-36 w-full object-cover" src="{{ $imageUrl }}" alt="Imagem de {{ $product->name }}" loading="lazy" />
                        </div>
                        <p class="text-sm text-slate-500">{{ $product->category ?? 'Categoria' }}</p>
                        <p class="text-xl font-semibold text-slate-900">{{ $product->name }}</p>
                        <p class="mt-2 text-2xl font-semibold text-lime-700">
                            Kz {{ number_format($product->price, 2, ',', '.') }}
                        </p>
                        @if ($product->description)
                            <p class="mt-3 text-sm text-slate-500">{{ $product->description }}</p>
                        @endif
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                            <span>Estoque: {{ $product->stock }}</span>
                            <a class="text-lime-700 hover:text-lime-700" href="{{ route('storefront.show', $product) }}">Detalhes</a>
                        </div>
                        <form class="mt-4" method="POST" action="{{ route('cart.add', $product) }}">
                            @csrf
                            <button class="w-full rounded-2xl border border-slate-300 py-2 text-sm hover:border-lime-300">
                                Adicionar ao carrinho
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection









