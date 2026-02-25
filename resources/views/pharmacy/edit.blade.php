@extends('layouts.storefront')

@section('title', 'Editar Farmácia - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Editar Farmácia</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Atualize os dados da sua farmácia.
                </p>
            </div>
            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.status') }}">
                Ver status
            </a>
        </div>

        <form class="mt-8 grid gap-5 md:grid-cols-2" method="POST" action="{{ route('pharmacy.update') }}">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Nome da Farmácia</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="name" value="{{ old('name', $pharmacy->name) }}" placeholder="Ex: Farmácia Central" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Responsável</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="responsible_name" value="{{ old('responsible_name', $pharmacy->responsible_name) }}" placeholder="Nome completo" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">NIF</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="nif" value="{{ old('nif', $pharmacy->nif) }}" placeholder="Ex: 5000000000" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Telefone</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="phone" value="{{ old('phone', $pharmacy->phone) }}" placeholder="(+244) 900 000 000" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Email da Farmácia</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="email" name="email" value="{{ old('email', $pharmacy->email) }}" placeholder="contato@farmacia.com" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Endereço (opcional)</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="address" value="{{ old('address', $pharmacy->address) }}" placeholder="Rua, bairro, cidade" />
            </div>
            <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-4">
                <p class="text-xs text-slate-500">
                    Atualize os dados da farmácia sempre que necessário.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a class="rounded-2xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.status') }}">
                        Cancelar
                    </a>
                    <button class="rounded-2xl bg-lime-400 px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                        Guardar alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
