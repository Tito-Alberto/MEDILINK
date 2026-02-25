@extends('layouts.storefront')

@section('title', 'Catálogo - Medlink')

@section('content')
    <section class="grid gap-6 md:grid-cols-[1.1fr_0.9fr]">
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Catálogo</p>
            <h1 class="brand-title mt-3 text-4xl text-slate-900">Produtos essenciais para sua Farmácia.</h1>
            <p class="mt-4 text-slate-600">
                Compare Preços, adicione ao carrinho e finalize seu pedido com entrega rapida.
            </p>
            <div class="mt-6 flex flex-wrap gap-3 text-sm text-slate-600">
                <span class="rounded-full border border-slate-300 px-3 py-1">Frete rápido</span>
                <span class="rounded-full border border-slate-300 px-3 py-1">Ofertas do dia</span>
                <span class="rounded-full border border-slate-300 px-3 py-1">Compra segura</span>
            </div>
        </div>
        <div class="glass rounded-3xl p-6">
            <p class="text-sm uppercase tracking-[0.2em] text-amber-300">Dica</p>
            <h2 class="brand-title mt-3 text-3xl text-slate-900">Monte seu carrinho.</h2>
            <p class="mt-4 text-slate-600">Adicione produtos de diferentes Farmácias e finalize tudo em um unico pedido.</p>
            <a class="mt-6 inline-flex rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="/carrinho">
                Ver carrinho
            </a>
        </div>
    </section>

    @if (auth()->check() && auth()->user()->is_admin)
    <div class="mt-8 glass rounded-3xl p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Total geral</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $allTotalCount }} resultados ativos</p>
                <p class="text-sm text-slate-500">{{ $allProductCount }} produtos e {{ $allPharmacyCount }} Farmácias.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                <span class="rounded-full border border-slate-300 px-3 py-1">Catálogo atualizado</span>
                <span class="rounded-full border border-slate-300 px-3 py-1">Farmácias aprovadas</span>
            </div>
        </div>
    </div>

    @endif

    @if (!empty($search))
        <section class="mt-10 space-y-6">
            <div class="glass rounded-3xl p-6">
                <p class="text-xs uppercase tracking-[0.3em] text-lime-700">Pesquisa</p>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <h2 class="brand-title text-3xl text-slate-900">Resultados para "{{ $search }}"</h2>
                    <span class="rounded-full border border-lime-400/40 bg-lime-500/10 px-3 py-1 text-xs uppercase tracking-[0.2em] text-lime-700">
                        {{ $totalCount }} resultados
                    </span>
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $productCount }} produtos e {{ $pharmacyCount }} Farmácias encontrados.
                </p>
            </div>

            @if ($pharmacies->isNotEmpty())
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Farmácias encontradas</h3>
                        <span class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $pharmacies->count() }} resultados</span>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @foreach ($pharmacies as $pharmacy)
                            <div class="rounded-2xl border border-slate-200 bg-white/80 p-5">
                                <p class="text-lg font-semibold text-slate-900">{{ $pharmacy->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $pharmacy->address ?? 'Endereço indisponível' }}</p>
                                <p class="mt-2 text-xs text-lime-700">Farmácia aprovada</p>
                                <a class="mt-3 inline-flex text-xs text-lime-700 hover:text-lime-700" href="{{ route('storefront.pharmacy', $pharmacy) }}">
                                    Ver Farmácia
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    <section class="mt-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Disponiveis agora</p>
                <h2 class="brand-title text-4xl text-slate-900">Produtos ativos</h2>
            </div>
            <div class="text-sm text-slate-500">{{ $productCount }} produtos encontrados.</div>
        </div>

        <form class="mt-6 flex flex-wrap items-center gap-3 text-sm text-slate-600" method="GET" action="{{ route('storefront.index') }}">
            <input type="hidden" name="q" value="{{ $search }}" />
            <x-searchable-select
                name="category"
                :options="$categories"
                :selected="$category"
                label="Categoria"
                placeholder="Buscar categoria"
                all-label="Todas categorias"
            />
            <button class="rounded-full border border-slate-300 px-4 py-2 text-xs uppercase tracking-[0.2em] text-slate-700 hover:border-lime-300" type="submit">
                Filtrar
            </button>
            @if (!empty($search) || !empty($category))
                <a class="rounded-full border border-slate-200 px-4 py-2 text-xs text-slate-500 hover:text-slate-900" href="{{ route('storefront.index') }}">Limpar</a>
            @endif
        </form>

        @if ($products->isEmpty())
            <div class="glass mt-8 rounded-3xl p-6 text-center">
                <p class="text-lg text-slate-700">
                    {{ !empty($search) ? 'Nenhum produto encontrado para esta pesquisa.' : 'Nenhum produto cadastrado ainda.' }}
                </p>
                <p class="mt-2 text-sm text-slate-500">
                    {{ !empty($search) ? 'Tente outro termo ou ajuste sua busca.' : 'Rode o seeder ou cadastre novos produtos.' }}
                </p>
            </div>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <div class="glass fade-in rounded-3xl p-5">
                        @php
                            $imagePath = $product->image_url ?: 'images/products/placeholder.svg';
                            $imageUrl = str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/')
                                ? $imagePath
                                : asset($imagePath);
                            $soldCount = (int) ($product->sold_quantity ?? 0);
                        @endphp
                        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white/90 p-2">
                            <img class="h-40 w-full object-contain" src="{{ $imageUrl }}" alt="Imagem de {{ $product->name }}" loading="lazy" />
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
                            <span>{{ $product->category ?? 'Categoria' }}</span>
                        </div>
                        <p class="text-xl font-semibold text-slate-900">{{ $product->name }}</p>
                        <p class="mt-2 text-2xl font-semibold text-lime-700">
                            Kz {{ number_format($product->price, 2, ',', '.') }}
                        </p>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <p class="text-sm text-slate-500">
                                Farmácia: {{ $product->pharmacy?->name ?? 'Farmácia independente' }}
                            </p>
                            @if ($soldCount > 0)
                                <p class="shrink-0 text-sm font-semibold uppercase tracking-[0.06em] text-lime-600">
                                    {{ $soldCount }} vendidos
                                </p>
                            @endif
                        </div>
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










