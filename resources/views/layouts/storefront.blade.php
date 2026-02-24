<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Medlink Store')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bebas-neue:400|work-sans:300,400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
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
        <header class="border-b border-slate-200 bg-[#eef8df] px-5 py-6">
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
                            <p class="text-xs uppercase tracking-[0.32em] text-slate-500">Farm&aacute;cia + E-commerce</p>
                        </div>
                    </a>
                    <button id="mobileMenuToggleStore" class="md:hidden rounded-full border border-slate-300 p-2 text-slate-600 hover:border-lime-400 hover:text-lime-700" type="button" aria-expanded="false" aria-controls="mobileMenuStore" aria-label="Abrir menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"></path>
                            <path d="M3 12h18"></path>
                            <path d="M3 18h18"></path>
                        </svg>
                    </button>
                </div>
                <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 md:flex md:justify-self-center">
                    <a class="hover:text-slate-900" href="/#ofertas">Ofertas</a>
                    <a class="hover:text-slate-900" href="{{ route('storefront.pharmacies') }}">Farm&aacute;cias</a>
                    <a class="hover:text-slate-900" href="/produtos">Cat&aacute;logo</a>
                </nav>
                <form class="w-full md:w-[440px] md:col-start-3 md:justify-self-end" action="{{ route('storefront.index') }}" method="GET">
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
                                placeholder="Pesquisar Farm&aacute;cias, medicamentos, equipamentos..."
                            />
                        </div>
                        <div class="flex w-full items-center rounded-full border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 sm:w-44 sm:rounded-none sm:border-0 sm:border-l sm:border-slate-200 sm:bg-transparent">
                            <select class="w-full bg-transparent text-sm text-slate-700 focus:outline-none" name="category">
                                <option value="">Todas as categorias</option>
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
                <div class="hidden flex-wrap items-center justify-end gap-3 text-sm font-semibold text-slate-600 md:flex md:col-start-4 md:justify-self-end">
                    <a class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-amber-300 bg-amber-200 text-amber-700 hover:border-amber-400 hover:bg-amber-100" href="/carrinho" aria-label="Carrinho">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="20" r="1"></circle>
                            <circle cx="17" cy="20" r="1"></circle>
                            <path d="M3 4h2l2.2 10.4a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H7.1"></path>
                        </svg>
                        @if ($cartCount > 0)
                            <span class="absolute -right-1 -top-1 rounded-full bg-lime-400 px-1.5 py-0.5 text-[10px] font-semibold text-slate-900">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                    @auth
                        @if (auth()->user()->pharmacy)
                            <a class="hover:text-slate-900" href="/farmacia">Minha Farm&aacute;cia</a>
                            @if (auth()->user()->pharmacy->status === 'approved')
                                <a class="hover:text-slate-900" href="{{ route('pharmacy.orders.index') }}">Pedidos</a>
                            @endif
                        @endif
                        @if (auth()->user()->is_admin)
                            <a class="hover:text-slate-900" href="/admin/farmacias">Admin</a>
                        @endif
                        <x-notification-bell :notifications="$headerNotifications" />
                        <span class="text-slate-500 font-normal">Ol&aacute;, {{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-full border border-lime-300 bg-lime-400 px-4 py-2 font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                                Sair
                            </button>
                        </form>
                    @else
                        <a class="rounded-full border border-slate-300 px-4 py-2 hover:border-lime-400 hover:text-slate-900" href="/login">Entrar</a>
                        <a class="rounded-full bg-lime-400 px-4 py-2 font-semibold text-slate-900 hover:bg-lime-300" href="/register">Criar conta</a>
                    @endauth
                </div>
            </div>
            <div id="mobileMenuStore" class="mt-4 hidden rounded-2xl border border-slate-200 bg-[#eef8df] p-4 text-sm font-semibold text-slate-600 md:hidden">
                <div class="grid gap-3">
                    <a class="hover:text-slate-900" href="/#ofertas">Ofertas</a>
                    <a class="hover:text-slate-900" href="{{ route('storefront.pharmacies') }}">Farm&aacute;cias</a>
                    <a class="hover:text-slate-900" href="/produtos">Cat&aacute;logo</a>
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
                            {{ $cartCount }}
                        </span>
                    </a>
                    @auth
                        @if (auth()->user()->pharmacy)
                            <a class="hover:text-slate-900" href="/farmacia">Minha Farm&aacute;cia</a>
                            @if (auth()->user()->pharmacy->status === 'approved')
                                <a class="hover:text-slate-900" href="{{ route('pharmacy.orders.index') }}">Pedidos</a>
                            @endif
                        @endif
                        @if (auth()->user()->is_admin)
                            <a class="hover:text-slate-900" href="/admin/farmacias">Admin</a>
                        @endif
                        <div class="flex items-center gap-3">
                            <x-notification-bell :notifications="$headerNotifications" />
                            <span class="text-slate-500 font-normal">Ol&aacute;, {{ auth()->user()->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-full border border-lime-300 bg-lime-400 px-4 py-2 font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                                Sair
                            </button>
                        </form>
                    @else
                        <a class="rounded-full border border-slate-300 px-4 py-2 hover:border-lime-400 hover:text-slate-900" href="/login">Entrar</a>
                        <a class="rounded-full bg-lime-400 px-4 py-2 font-semibold text-slate-900 hover:bg-lime-300" href="/register">Criar conta</a>
                    @endauth
                </div>
            </div>
            <script>
                (() => {
                    const toggle = document.getElementById('mobileMenuToggleStore');
                    const menu = document.getElementById('mobileMenuStore');
                    if (!toggle || !menu) {
                        return;
                    }
                    toggle.addEventListener('click', () => {
                        const isHidden = menu.classList.toggle('hidden');
                        toggle.setAttribute('aria-expanded', String(!isHidden));
                    });
                })();
            </script>
        </header>

        <main class="px-6 pb-16">
            <div class="mx-auto max-w-6xl space-y-6">
                @if (session('status'))
                    <div class="glass rounded-2xl border border-lime-400/30 bg-lime-500/10 p-4 text-sm text-lime-700">
                        {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="glass rounded-2xl border border-rose-400/30 bg-rose-500/10 p-4 text-sm text-rose-700">
                        <p class="font-semibold">Confira os campos:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-[#eef8df] px-6 py-12">
            <div class="mx-auto grid max-w-6xl gap-10 md:grid-cols-[1.2fr_1fr_1fr]">
                <div>
                    <p class="brand-title text-2xl text-slate-900">Medlink</p>
                    <p class="mt-2 text-sm text-slate-600">
                        Farm&aacute;cia + E-commerce para conectar clientes e farm&aacute;cias locais.
                    </p>
                    <p class="mt-4 text-xs text-slate-400">&copy; 2026 Medlink. Todos os direitos reservados.</p>
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
                    <p class="mt-3 text-sm text-slate-500">Endere&ccedil;o: Lubango - Instituto Superior Independente.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>















