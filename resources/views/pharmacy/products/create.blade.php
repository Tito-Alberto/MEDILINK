@extends('layouts.storefront')

@section('title', 'Novo produto - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Novo produto</h1>
                <p class="mt-2 text-sm text-slate-600">Adicione um produto a sua Farmácia.</p>
            </div>
            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.products.index') }}">
                Voltar
            </a>
        </div>

        <form class="mt-8 grid gap-5 md:grid-cols-2" method="POST" action="{{ route('pharmacy.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Nome</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="name" value="{{ old('name') }}" placeholder="Ex: Paracetamol 500mg" required />
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Descricao</label>
                <textarea class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" name="description" rows="3" placeholder="Detalhes do produto">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Preço (Kz)</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Estoque</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required />
            </div>
            <div>
                <x-searchable-select
                    name="category"
                    :options="$categories"
                    :selected="old('category')"
                    label="Categoria"
                    placeholder="Buscar categoria"
                    all-label="Sem categoria"
                    id="product-category-create"
                />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Imagem (Upload)</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 file:mr-3 file:rounded-full file:border-0 file:bg-lime-400 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-900 hover:file:bg-lime-300" type="file" name="image_file" accept="image/*" required />
                <p class="mt-2 text-xs text-slate-500">Envie uma imagem do produto (obrigatorio).</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pre-visualizacao</p>
                <div id="productImagePreviewWrap" class="mt-3 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white/90">
                    <img id="productImagePreview" class="h-48 w-full object-cover" alt="Pre-visualizacao da imagem" />
                </div>
                <button id="productImageClear" class="mt-3 hidden rounded-full border border-slate-300 px-4 py-2 text-xs text-slate-700 hover:border-lime-300" type="button">
                    Limpar imagem
                </button>
            </div>
            <div class="md:col-span-2 flex items-center justify-between">
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input class="h-4 w-4 rounded border-slate-300 bg-white text-lime-400 focus:ring-lime-400" type="checkbox" name="is_active" value="1" checked />
                    Produto ativo
                </label>
                <button class="rounded-2xl bg-lime-400 px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                    Salvar produto
                </button>
            </div>
        </form>
        <script>
            (() => {
                const fileInput = document.querySelector('input[name="image_file"]');
                const previewWrap = document.getElementById('productImagePreviewWrap');
                const previewImg = document.getElementById('productImagePreview');
                const clearButton = document.getElementById('productImageClear');

                if (!fileInput || !previewWrap || !previewImg || !clearButton) {
                    return;
                }

                const showPreview = (src) => {
                    previewImg.src = src;
                    previewWrap.classList.remove('hidden');
                    clearButton.classList.remove('hidden');
                };

                const hidePreview = () => {
                    previewImg.removeAttribute('src');
                    previewWrap.classList.add('hidden');
                    clearButton.classList.add('hidden');
                };

                fileInput.addEventListener('change', () => {
                    if (fileInput.files && fileInput.files[0]) {
                        const reader = new FileReader();
                        reader.onload = (event) => showPreview(event.target.result);
                        reader.readAsDataURL(fileInput.files[0]);
                    } else {
                        hidePreview();
                    }
                });

                clearButton.addEventListener('click', () => {
                    fileInput.value = '';
                    hidePreview();
                });
            })();
        </script>
    </div>
@endsection









