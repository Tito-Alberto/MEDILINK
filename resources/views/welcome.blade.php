<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Medlink - Ecommerce de Farmácias</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bebas-neue:400|work-sans:300,400,500,600,700&display=swap" rel="stylesheet" />

        <script src="https://cdn.tailwindcss.com"></script>
    <style>\n        html {\n            scroll-behavior: smooth;\n        }\n        section[id] {\n            scroll-margin-top: 96px;\n        }\n        :root {
            --brand-ink: #0f172a;
            --brand-ocean: #22c55e;
            --brand-lime: #a3e635;
            --brand-amber: #fbbf24;
            --brand-rose: #f97316;
        }
        body {
            font-family: "Work Sans", ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #ffffff;
            color: #0f172a;
        }
        .brand-title {
            font-family: "Bebas Neue", "Work Sans", sans-serif;
            letter-spacing: 0.04em;
        }
        .glass {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.45);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(12px);
        }
        .card-glow {
            box-shadow: 0 18px 40px rgba(34, 197, 94, 0.25);
        }
        .floaty {
            animation: floaty 6s ease-in-out infinite;
        }
        @keyframes floaty {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .fade-in {
            animation: fade-in 0.9s ease forwards;
            opacity: 0;
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="antialiased">
    @php
        $cartCount = array_sum(session('cart', []));
    @endphp
    <div class="min-h-screen">
        <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-[#eef8df] px-5 py-5">
            <div class="grid max-w-none grid-cols-1 items-center gap-5 md:grid-cols-[auto_1fr_auto_auto]">
                <div class="flex w-full items-center justify-between md:w-auto">
                    <a class="flex items-center gap-3" href="/">
                        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-lime-400 to-emerald-500 p-[2px]">
                            <div class="flex h-full w-full items-center justify-center rounded-2xl bg-white text-xl font-semibold">
                                M
                            </div>
                        </div>
                        <div>
                            <p class="brand-title text-3xl text-slate-900">Medlink</p>
                            <p class="text-xs uppercase tracking-[0.32em] text-slate-500">Farmácia + Ecommerce</p>
                        </div>
                    </a>
                    <button id="mobileMenuToggle" class="md:hidden rounded-full border border-slate-300 p-2 text-slate-600 hover:border-lime-400 hover:text-lime-700" type="button" aria-expanded="false" aria-controls="mobileMenu" aria-label="Abrir menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"></path>
                            <path d="M3 12h18"></path>
                            <path d="M3 18h18"></path>
                        </svg>
                    </button>
                </div>
                <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 md:flex md:justify-self-center">
                    <a class="hover:text-slate-900" href="#ofertas">Ofertas</a>
                    <a class="hover:text-slate-900" href="#farmacias">Farmácias</a>
                    <a class="hover:text-slate-900" href="#como">Como funciona</a>
                    <a class="hover:text-slate-900" href="/produtos">Catalogo</a>
                </nav>
                <form class="w-full md:w-[440px] md:justify-self-end" action="/produtos" method="GET">
                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:gap-0 sm:rounded-full sm:border sm:border-slate-300 sm:bg-white/80 sm:focus-within:border-lime-400">
                        <div class="flex w-full flex-1 items-center rounded-full border border-slate-300 bg-white/80 px-4 py-2 text-sm text-slate-900 sm:rounded-none sm:border-0 sm:bg-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                            <input
                                class="w-full bg-transparent text-sm text-slate-900 placeholder:text-slate-500 focus:outline-none"
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Pesquisar Farmácias, medicamentos, equipamentos..."
                            />
                        </div>
                        <div class="flex w-full items-center rounded-full border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 sm:w-44 sm:rounded-none sm:border-0 sm:border-l sm:border-slate-200 sm:bg-transparent">
                            <select class="w-full bg-transparent text-sm text-slate-700 focus:outline-none" name="category">
                                <option value="">Todas categorias</option>
                                @foreach (($headerCategories ?? collect()) as $category)
                                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="ml-2 text-slate-500 hover:text-lime-700" type="submit" aria-label="Pesquisar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="m20 20-3.5-3.5"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="hidden flex-wrap items-center justify-end gap-3 text-sm text-slate-600 md:flex md:justify-self-end">
                    <a class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-amber-300 bg-amber-200 text-amber-700 hover:border-amber-400 hover:bg-amber-100" href="/carrinho" aria-label="Carrinho">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="20" r="1"></circle>
                            <circle cx="17" cy="20" r="1"></circle>
                            <path d="M3 4h2l2.2 10.4a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H7.1"></path>
                        </svg>
                        @if (($cartCount ?? 0) > 0)
                            <span class="absolute -right-1 -top-1 rounded-full bg-lime-400 px-1.5 py-0.5 text-[10px] font-semibold text-slate-900">
                                {{ $cartCount ?? 0 }}
                            </span>
                        @endif
                    </a>
                    @auth
                        @if (auth()->user()->pharmacy)
                            <a class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-700 hover:border-lime-300" href="/farmacia">
                                Minha Farmácia
                            </a>
                        @endif
                        @if (auth()->user()->pharmacy && auth()->user()->pharmacy->status === 'approved')
                            <a class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.orders.index') }}">
                                Pedidos
                            </a>
                        @endif
                        <x-notification-bell :notifications="$headerNotifications" />
                        <span class="text-sm text-slate-500">Ola, {{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-full border border-lime-300 bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                                Sair
                            </button>
                        </form>
                    @else
                        <a class="rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-lime-400 hover:text-slate-900" href="/login">Entrar</a>
                        <a class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="/register">Criar conta</a>
                    @endauth
                </div>
            </div>
            <div id="mobileMenu" class="mt-4 hidden rounded-2xl border border-slate-200 bg-[#eef8df] p-4 text-sm font-semibold text-slate-600 md:hidden">
                <div class="grid gap-3">
                    <a class="flex items-center justify-between rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="/carrinho">
                        <span class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="20" r="1"></circle>
                                <circle cx="17" cy="20" r="1"></circle>
                                <path d="M3 4h2l2.2 10.4a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H7.1"></path>
                            </svg>
                            Carrinho
                        </span>
                        <span class="rounded-full bg-lime-400 px-2 py-0.5 text-[10px] font-semibold text-slate-900">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </a>
                    <a class="hover:text-slate-900" href="#ofertas">Ofertas</a>
                    <a class="hover:text-slate-900" href="#farmacias">Farmácias</a>
                    <a class="hover:text-slate-900" href="#como">Como funciona</a>
                    <a class="hover:text-slate-900" href="/produtos">Catalogo</a>
                    @auth
                        @if (auth()->user()->pharmacy)
                            <a class="hover:text-slate-900" href="/farmacia">Minha Farmácia</a>
                        @endif
                        @if (auth()->user()->pharmacy && auth()->user()->pharmacy->status === 'approved')
                            <a class="hover:text-slate-900" href="{{ route('pharmacy.orders.index') }}">Pedidos</a>
                        @endif
                        <div class="flex items-center gap-3">
                            <x-notification-bell :notifications="$headerNotifications" />
                            <span class="text-slate-500">Ola, {{ auth()->user()->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-full border border-lime-300 bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                                Sair
                            </button>
                        </form>
                    @else
                        <a class="rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-lime-400 hover:text-slate-900" href="/login">Entrar</a>
                        <a class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="/register">Criar conta</a>
                    @endauth
                </div>
            </div>
            <script>
                (() => {
                    const toggle = document.getElementById('mobileMenuToggle');
                    const menu = document.getElementById('mobileMenu');
                    if (!toggle || !menu) {
                        return;
                    }
                    toggle.addEventListener('click', () => {
                        const isHidden = menu.classList.toggle('hidden');
                        toggle.setAttribute('aria-expanded', String(!isHidden));
                    });
                })();

                document.addEventListener('DOMContentLoaded', () => {
                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const carousels = document.querySelectorAll('[data-banner-carousel]');

                    carousels.forEach((carousel) => {
                        const slides = Array.from(carousel.querySelectorAll('[data-banner-slide]'));
                        const dots = Array.from(carousel.querySelectorAll('[data-banner-dot]'));
                        const intervalMs = Number(carousel.dataset.interval || 3800);

                        if (slides.length === 0) {
                            return;
                        }

                        let activeIndex = 0;
                        let timer = null;

                        const render = (nextIndex) => {
                            activeIndex = (nextIndex + slides.length) % slides.length;

                            slides.forEach((slide, index) => {
                                const isActive = index === activeIndex;
                                slide.classList.toggle('opacity-100', isActive);
                                slide.classList.toggle('opacity-0', !isActive);
                                slide.setAttribute('aria-hidden', String(!isActive));
                            });

                            dots.forEach((dot, index) => {
                                const isActive = index === activeIndex;
                                dot.classList.toggle('bg-white', isActive);
                                dot.classList.toggle('bg-white/40', !isActive);
                                dot.setAttribute('aria-current', isActive ? 'true' : 'false');
                            });
                        };

                        const stop = () => {
                            if (timer) {
                                window.clearInterval(timer);
                                timer = null;
                            }
                        };

                        const start = () => {
                            if (prefersReducedMotion || slides.length < 2 || timer) {
                                return;
                            }
                            timer = window.setInterval(() => render(activeIndex + 1), intervalMs);
                        };

                        dots.forEach((dot, index) => {
                            dot.addEventListener('click', () => {
                                render(index);
                                stop();
                                start();
                            });
                        });

                        carousel.addEventListener('mouseenter', stop);
                        carousel.addEventListener('mouseleave', start);
                        carousel.addEventListener('focusin', stop);
                        carousel.addEventListener('focusout', start);

                        render(0);
                        start();
                    });
                });
            </script>
        </header>

        <main class="px-6 pb-16">
            <section class="mx-auto grid max-w-6xl gap-10 md:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-6">
                    <p class="inline-flex items-center gap-2 rounded-full bg-lime-500/10 px-4 py-2 text-sm text-lime-700">
                        <span class="h-2 w-2 rounded-full bg-lime-400"></span>
                        Ecommerce completo para Farmácias locais
                    </p>
                    <h1 class="brand-title text-5xl text-slate-900 md:text-6xl">
                        O marketplace onde Farmácias vendem mais, e clientes compram rapido.
                    </h1>
                    <p class="text-lg text-slate-600">
                        Medlink conecta Farmácias a novos clientes. Cadastre sua Farmácia, publique seus produtos e
                        venda com carrinho, login e ofertas em destaque. O publico visualiza os produtos e compra
                        somente apos autenticar.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a class="rounded-full bg-lime-400 px-6 py-3 font-semibold text-slate-900 hover:bg-lime-300" href="{{ auth()->check() ? route('pharmacy.create') : route('register') }}">
                            Cadastrar Farmácia
                        </a>
                        <a class="rounded-full border border-slate-600 px-6 py-3 text-slate-700 hover:border-lime-300 hover:text-slate-900" href="/login">
                            Entrar para comprar
                        </a>
                        <a class="rounded-full border border-slate-300 px-6 py-3 text-slate-600 hover:border-slate-500" href="#ofertas">
                            Ver melhores ofertas
                        </a>
                    </div>
                    <div class="grid gap-4 pt-4 md:grid-cols-3">
                        <div class="glass rounded-2xl p-4">
                            <p class="text-2xl font-semibold text-slate-900">{{ $allPharmacyCount ?? 0 }}</p>
                            <p class="text-sm text-slate-500">Farmácias cadastradas</p>
                        </div>
                        <div class="glass rounded-2xl p-4">
                            <p class="text-2xl font-semibold text-slate-900">{{ $allProductCount ?? 0 }}</p>
                            <p class="text-sm text-slate-500">Produtos ativos</p>
                        </div>
                        <div class="glass rounded-2xl p-4">
                            <p class="text-2xl font-semibold text-slate-900">{{ $adminCount ?? 0 }}</p>
                            <p class="text-sm text-slate-500">Admins ativos</p>
                        </div>
                    </div>                   <div class="mt-6 glass rounded-2xl p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total geral</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $allTotalCount ?? 0 }} resultados ativos</p>
                        <p class="text-sm text-slate-500">{{ $allProductCount ?? 0 }} produtos e {{ $allPharmacyCount ?? 0 }} Farmácias.</p>
                    </div>
                </div>

                @php
                    $bannerImages = collect(glob(public_path('images/baner/*.{jpg,jpeg,png,webp,avif}'), GLOB_BRACE) ?: [])
                        ->sort()
                        ->map(fn ($path) => asset('images/baner/' . basename($path)))
                        ->values();
                @endphp
                <div class="glass relative min-h-[430px] overflow-hidden rounded-3xl p-0">
                    <div class="absolute inset-0 p-3">
                        @if ($bannerImages->isNotEmpty())
                            <div class="relative h-full overflow-hidden rounded-2xl border border-white/20 shadow-sm" data-banner-carousel data-interval="3800">
                                @foreach ($bannerImages->take(8) as $bannerImage)
                                    <div
                                        class="absolute inset-0 flex items-end justify-center transition-opacity duration-700 ease-out md:justify-end {{ $loop->first ? 'opacity-100' : 'opacity-0' }}"
                                        data-banner-slide
                                        aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                                    >
                                        <img class="h-auto w-auto max-h-[92%] max-w-[92%] rounded-xl object-contain" src="{{ $bannerImage }}" alt="Banner Medlink {{ $loop->iteration }}">
                                    </div>
                                @endforeach

                                @if ($bannerImages->count() > 1)
                                    <div class="absolute bottom-4 right-4 z-10 flex items-center gap-2 rounded-full border border-white/20 bg-slate-900/25 px-2 py-1 backdrop-blur-sm">
                                        @foreach ($bannerImages->take(8) as $bannerImage)
                                            <button
                                                type="button"
                                                class="h-2.5 w-2.5 rounded-full transition-colors {{ $loop->first ? 'bg-white' : 'bg-white/40' }}"
                                                data-banner-dot
                                                aria-label="Ir para imagem {{ $loop->iteration }}"
                                                aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                            ></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="h-full rounded-2xl bg-gradient-to-br from-lime-200 to-white"></div>
                        @endif
                    </div>

                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/45 via-slate-900/10 to-white/0"></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(163,230,53,0.18),transparent_60%)]"></div>

                    <div class="relative flex min-h-[430px] flex-col justify-between p-6">
                        <div class="inline-flex w-fit items-center rounded-full border border-white/25 bg-white/35 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-900 backdrop-blur-sm">
                            Banner destaque
                        </div>

                        <div class="max-w-sm rounded-2xl border border-white/35 bg-white/70 p-5 shadow-sm backdrop-blur-sm">
                            <p class="text-sm text-slate-700">Compra online de medicamentos</p>
                            <h3 class="mt-2 text-2xl font-semibold leading-tight text-slate-900">
                                Compare opcoes e compre com rapidez
                            </h3>
                            <p class="mt-2 text-sm text-slate-700">
                                Ofertas, retirada e entregas em um so lugar para facilitar sua compra.
                            </p>
                            <a class="mt-4 inline-flex items-center rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="#ofertas">
                                Ver ofertas
                            </a>
                        </div>
                    </div>
                </div>
            </section>

                                    <section id="ofertas" class="mx-auto mt-16 max-w-6xl">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Melhores ofertas</p>
                        <h2 class="brand-title text-4xl text-slate-900">Produtos em destaque</h2>
                    </div>
                    <div class="text-sm text-slate-500">Compre aqui!.</div>
                </div>
                @php
                    $featuredProducts = $featuredProducts ?? collect();
                    $fallbackProducts = collect([
                        [
                            'name' => 'Vitamina C 1g',
                            'price' => 1990,
                            'category' => 'Imunidade',
                            'image_url' => 'images/products/vitamina-c.svg',
                        ],
                        [
                            'name' => 'Protetor Solar FPS 50',
                            'price' => 4200,
                            'category' => 'Dermocosmeticos',
                            'image_url' => 'images/products/placeholder.svg',
                        ],
                        [
                            'name' => 'Hidratante Corporal',
                            'price' => 2750,
                            'category' => 'Cuidados diarios',
                            'image_url' => 'images/products/placeholder.svg',
                        ],
                        [
                            'name' => 'Kit Termometro + Oximetro',
                            'price' => 12990,
                            'category' => 'Equipamentos',
                            'image_url' => 'images/products/placeholder.svg',
                        ],
                        [
                            'name' => 'Mascara Facial 50un',
                            'price' => 2490,
                            'category' => 'Protecao',
                            'image_url' => 'images/products/placeholder.svg',
                        ],
                        [
                            'name' => 'Dorflex 30 comprimidos',
                            'price' => 1780,
                            'category' => 'Analgesicos',
                            'image_url' => 'images/products/placeholder.svg',
                        ],
                    ]);
                    $cards = $featuredProducts->isEmpty() ? $fallbackProducts : $featuredProducts;
                @endphp
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($cards as $product)
                        @php
                            $imagePath = data_get($product, 'image_url') ?: 'images/products/placeholder.svg';
                            $imageUrl = str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/')
                                ? $imagePath
                                : asset($imagePath);
                            $name = data_get($product, 'name', 'Produto');
                            $price = data_get($product, 'price', 0);
                            $category = data_get($product, 'category', 'Oferta especial');
                            $productId = data_get($product, 'id');
                            $detailUrl = $productId ? route('storefront.show', $productId) : url('/produtos');
                        @endphp
                        <div class="glass fade-in rounded-3xl p-5" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                            <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white/90 p-2">
                                <img class="h-36 w-full object-contain" src="{{ $imageUrl }}" alt="Imagem de {{ $name }}" loading="lazy" />
                            </div>
                            <p class="text-sm text-slate-500">{{ $name }}</p>
                            <p class="text-2xl font-semibold text-slate-900">Kz {{ number_format($price, 2, ',', '.') }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                Farmácia: {{ data_get($product, 'pharmacy.name') ?? 'Farmácia parceira' }}
                            </p>
                            <p class="text-xs text-lime-700">{{ $category }}</p>
                            <a class="mt-4 block w-full rounded-2xl border border-slate-300 py-2 text-center text-sm hover:border-lime-300" href="{{ $detailUrl }}">Ver detalhes</a>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="farmacias" class="mx-auto mt-16 max-w-6xl">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-amber-300">Farmácias com Preços baixos</p>
                        <h2 class="brand-title text-4xl text-slate-900">Parceiros em destaque</h2>
                    </div>
                    <a class="text-sm text-amber-700 hover:text-amber-700" href="{{ auth()->check() ? route('pharmacy.create') : route('register') }}">
                        Quero cadastrar minha Farmácia
                    </a>
                </div>
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    @php
                        $featuredPharmacies = $featuredPharmacies ?? collect();
                    @endphp
                    @forelse ($featuredPharmacies as $pharmacy)
                        @php
                            $contact = $pharmacy->phone ?: $pharmacy->email;
                            $contactLabel = $contact ? 'Contato: ' . $contact : 'Contato nao informado';
                            $address = $pharmacy->address ?: 'Endereco nao informado';
                        @endphp
                        <div class="glass rounded-3xl p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xl font-semibold text-slate-900">{{ $pharmacy->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $address }}</p>
                                </div>
                                <div class="rounded-full bg-lime-400/20 px-3 py-1 text-xs text-lime-700">
                                    {{ $pharmacy->products_count > 0 ? $pharmacy->products_count . ' produtos' : 'Sem produtos' }}
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-slate-600">{{ $contactLabel }}</p>
                            <a class="mt-5 inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm hover:border-lime-300" href="{{ route('storefront.pharmacy', $pharmacy) }}">
                                Ver catalogo
                            </a>
                        </div>
                    @empty
                        <div class="glass rounded-3xl p-6 md:col-span-2">
                            <p class="text-sm text-slate-600">Ainda nao temos Farmácias aprovadas. Seja a primeira a cadastrar.</p>
                            <a class="mt-5 inline-flex rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" href="{{ auth()->check() ? route('pharmacy.create') : route('register') }}">
                                Cadastrar Farmácia
                            </a>
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="como" class="mx-auto mt-16 max-w-6xl">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Como funciona</p>
                        <h2 class="brand-title text-4xl text-slate-900">Fluxo completo para admin, Farmácias e publico</h2>
                    </div>
                </div>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <div class="glass rounded-3xl p-6">
                        <p class="text-sm text-lime-700">Admin</p>
                        <p class="mt-3 text-xl font-semibold text-slate-900">Controle total</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-600">
                            <li>Valida novas Farmácias</li>
                            <li>Destaca ofertas e campanhas</li>
                            <li>Gerencia categorias e banners</li>
                        </ul>
                    </div>
                    <div class="glass rounded-3xl p-6">
                        <p class="text-sm text-lime-700">Farmácias</p>
                        <p class="mt-3 text-xl font-semibold text-slate-900">Venda com autonomia</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-600">
                            <li>Cadastro rapido de produtos</li>
                            <li>Precificacao e estoque online</li>
                            <li>Recebe pedidos e confirma entrega</li>
                        </ul>
                    </div>
                    <div class="glass rounded-3xl p-6">
                        <p class="text-sm text-lime-700">Publico</p>
                        <p class="mt-3 text-xl font-semibold text-slate-900">Compra segura</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-600">
                            <li>Pesquisa ofertas e compara Preços</li>
                            <li>Login antes de finalizar compra</li>
                            <li>Carrinho simples e rapido</li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-[#eef8df] px-6 py-12">
            <div class="mx-auto grid max-w-6xl gap-10 md:grid-cols-[1.2fr_1fr_1fr]">
                <div>
                    <p class="brand-title text-2xl text-slate-900">Medlink</p>
                    <p class="mt-2 text-sm text-slate-600">
                        Farm&aacute;cia + Ecommerce para conectar clientes e farm&aacute;cias locais.
                    </p>
                    <p class="mt-4 text-xs text-slate-400">2026 Medlink. Todos os direitos reservados.</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Contato</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-500">
                        <li>Email: <span class="text-slate-700">contato@medlink.ao</span></li>
                        <li>Suporte: <span class="text-slate-700">suporte@medlink.ao</span></li>
                        <li>Telefone/WhatsApp: <span class="text-slate-700">+244 900 000 000</span></li>
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Atendimento</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-500">
                        <li>Segunda a Sexta: 08:00 - 18:00</li>
                        <li>S&aacute;bado: 08:00 - 13:00</li>
                        <li>Domingo: Fechado</li>
                    </ul>
                    <p class="mt-3 text-sm text-slate-500">Endere&ccedil;o: Lubango - Instituto Sup&eacute;rior Independente.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>



