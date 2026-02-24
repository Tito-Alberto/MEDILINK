<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Medlink - Cadastro</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bebas-neue:400|work-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
    <style>
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
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen px-6 py-10">
        <div class="mx-auto flex max-w-5xl items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-lime-400 to-emerald-500 p-[2px]">
                    <div class="flex h-full w-full items-center justify-center rounded-2xl bg-white text-lg font-semibold">
                        M
                    </div>
                </div>
                <div>
                    <p class="brand-title text-2xl text-slate-900">Medlink</p>
                    <p class="text-xs uppercase tracking-[0.32em] text-slate-500">Cadastro</p>
                </div>
            </div>
            <a class="text-sm text-slate-600 hover:text-slate-900" href="/">Voltar ao inicio</a>
        </div>

        <div class="mx-auto mt-12 grid max-w-5xl gap-10 md:grid-cols-[1.1fr_0.9fr]">
            <div class="glass rounded-3xl p-8">
                <h1 class="brand-title text-4xl text-slate-900">Criar cadastro</h1>
                <p class="mt-3 text-sm text-slate-600">
                    Faca um cadastro simples. Se quiser cadastrar uma Farmácia, voce faz isso depois.
                </p>
                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-rose-400/30 bg-rose-500/10 p-4 text-sm text-rose-700">
                        <p class="font-semibold">Confira os campos:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="mt-6 space-y-4" method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Nome completo</label>
                        <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="name" value="{{ old('name') }}" placeholder="Seu nome" required />
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Email</label>
                        <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="email" name="email" value="{{ old('email') }}" placeholder="voce@email.com" required />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Senha</label>
                            <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="password" name="password" placeholder="********" required />
                        </div>
                        <div>
                            <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Confirmar senha</label>
                            <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="password" name="password_confirmation" placeholder="********" required />
                        </div>
                    </div>
                    <button class="w-full rounded-2xl bg-lime-400 py-3 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                        Criar conta
                    </button>
                </form>
                <p class="mt-4 text-sm text-slate-500">
                    Ja tem conta?
                    <a class="text-lime-700 hover:text-lime-700" href="/login">Entrar</a>
                </p>
            </div>

            <div class="flex flex-col justify-center gap-6">
                <div class="glass rounded-3xl p-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Aprovação admin</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">Farmácias passam por validacao.</p>
                    <p class="mt-2 text-sm text-slate-600">Cadastre primeiro sua conta, depois envie o pedido da Farmácia.</p>
                </div>
                <div class="glass rounded-3xl p-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-amber-300">Público</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">Visitantes veem produtos sem login.</p>
                    <p class="mt-2 text-sm text-slate-600">Para comprar, o cliente precisa autenticar.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>














