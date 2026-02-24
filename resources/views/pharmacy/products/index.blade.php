@extends('layouts.storefront')

@section('title', 'Meus produtos - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Meus produtos</h1>
                <p class="mt-2 text-sm text-slate-600">Gerencie os produtos da sua Farmácia aprovada.</p>
            </div>
            <a class="rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="{{ route('pharmacy.products.create') }}">
                Novo produto
            </a>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($products as $product)
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-6">
                    @php
                        $imagePath = $product->image_url ?: 'images/products/placeholder.svg';
                        $imageUrl = str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/')
                            ? $imagePath
                            : asset($imagePath);
                    @endphp
                    <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white/90">
                        <img class="h-32 w-full object-cover" src="{{ $imageUrl }}" alt="Imagem de {{ $product->name }}" loading="lazy" />
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $product->category ?? 'Sem categoria' }}</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $product->name }}</h2>
                            <p class="mt-2 text-sm text-slate-600">{{ $product->description ?? 'Sem descrição.' }}</p>
                        </div>
                        <span class="rounded-full border border-slate-300 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-600">
                            {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
                        <span>Preço</span>
                        <span class="text-lg font-semibold text-lime-700">Kz {{ number_format($product->price, 2, ',', '.') }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-sm text-slate-500">
                        <span>Estoque</span>
                        <span>{{ $product->stock }} un.</span>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.products.edit', $product) }}">
                            Editar
                        </a>
                        <form method="POST" action="{{ route('pharmacy.products.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full border border-rose-400/50 px-4 py-2 text-sm text-rose-700 hover:border-rose-300" type="submit">
                                Remover
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-8 text-center text-slate-600 md:col-span-2 xl:col-span-3">
                    <p>Nenhum produto cadastrado ainda.</p>
                    <a class="mt-4 inline-flex rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="{{ route('pharmacy.products.create') }}">
                        Criar primeiro produto
                    </a>
                </div>
            @endforelse
        </div>
    </div>
@endsection









