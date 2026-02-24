@extends('layouts.storefront')

@section('title', $product->name . ' - Medlink')

@section('content')
    <section class="grid gap-6 md:grid-cols-[1.1fr_0.9fr]">
        <div class="glass rounded-3xl p-6">
            @php
                $imagePath = $product->image_url ?: 'images/products/placeholder.svg';
                $imageUrl = str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/')
                    ? $imagePath
                    : asset($imagePath);
            @endphp
            <div class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white/90 p-3">
                <img class="h-64 w-full object-contain" src="{{ $imageUrl }}" alt="Imagem de {{ $product->name }}" loading="lazy" />
            </div>
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Detalhes</p>
            <h1 class="brand-title mt-3 text-4xl text-slate-900">{{ $product->name }}</h1>
            <p class="mt-4 text-slate-600">{{ $product->description ?? 'Sem descrição detalhada.' }}</p>
            <div class="mt-4 text-sm text-slate-500">
                {{ $product->pharmacy?->name ?? 'Farmácia independente' }}
            </div>
            <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                <span class="rounded-full border border-slate-300 px-3 py-1">{{ $product->category ?? 'Categoria' }}</span>
                <span class="rounded-full border border-slate-300 px-3 py-1">Estoque: {{ $product->stock }}</span>
            </div>
        </div>
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-amber-300">Preço</p>
            <p class="mt-3 text-3xl font-semibold text-lime-700">Kz {{ number_format($product->price, 2, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">Entrega rapida e suporte 24h.</p>
            <form class="mt-6 space-y-3" method="POST" action="{{ route('cart.add', $product) }}">
                @csrf
                <div>
                    <label class="text-sm text-slate-600" for="qty">Quantidade</label>
                    <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none"
                           type="number" min="1" max="99" name="qty" id="qty" value="1" />
                </div>
                <button class="w-full rounded-2xl bg-lime-400 py-3 font-semibold text-slate-900 hover:bg-lime-300">
                    Adicionar ao carrinho
                </button>
                <a class="block text-center text-sm text-slate-600 hover:text-slate-900" href="/produtos">Voltar ao catálogo</a>
            </form>
        </div>
    </section>
@endsection










