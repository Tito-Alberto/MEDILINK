@extends('layouts.storefront')

@section('title', 'Editar produto - Medlink')

@section('content')
    <div class="glass rounded-3xl p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="brand-title text-3xl text-slate-900">Editar produto</h1>
                <p class="mt-2 text-sm text-slate-600">Atualize as informacoes do produto.</p>
            </div>
            <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('pharmacy.products.index') }}">
                Voltar
            </a>
        </div>

        <form class="mt-8 grid gap-5 md:grid-cols-2" method="POST" action="{{ route('pharmacy.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if (! $product->image_url)
                <div class="md:col-span-2 rounded-2xl border border-amber-400/30 bg-amber-500/10 p-4 text-sm text-amber-700">
                    Este produto ainda não tem imagem. Envie um upload para salvar.
                </div>
            @endif
            @if ($product->image_url)
                @php
                    $imagePath = $product->image_url;
                    $imageUrl = str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/')
                        ? $imagePath
                        : asset($imagePath);
                @endphp
                <div class="md:col-span-2">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Imagem atual</p>
                    <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white/90">
                        <img class="h-48 w-full object-cover" src="{{ $imageUrl }}" alt="Imagem de {{ $product->name }}" />
                    </div>
                </div>
            @endif
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Nome</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="text" name="name" value="{{ old('name', $product->name) }}" required />
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Descrição</label>
                <textarea class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Preço (Kz)</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Estoque</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none" type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required />
            </div>
            <div>
                <x-searchable-select
                    name="category"
                    :options="$categories"
                    :selected="old('category', $product->category)"
                    label="Categoria"
                    placeholder="Buscar categoria"
                    all-label="Sem categoria"
                    id="product-category-edit"
                />
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.2em] text-slate-500">Imagem (Upload)</label>
                <input class="mt-2 w-full rounded-2xl border border-slate-300 bg-white/80 px-4 py-3 text-sm text-slate-900 file:mr-3 file:rounded-full file:border-0 file:bg-lime-400 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-900 hover:file:bg-lime-300" type="file" name="image_file" accept="image/*" />
                <p class="mt-2 text-xs text-slate-500">Envie uma nova imagem para substituir a atual.</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pre-visualizacao da nova imagem</p>
                <div id="productEditImagePreviewWrap" class="mt-3 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white/90">
                    <img id="productEditImagePreview" class="h-48 w-full object-cover" alt="Pre-visualizacao da nova imagem" />
                </div>
                <button id="productEditImageClear" class="mt-3 hidden rounded-full border border-slate-300 px-4 py-2 text-xs text-slate-700 hover:border-lime-300" type="button">
                    Limpar imagem
                </button>
            </div>
            <div class="md:col-span-2 flex items-center justify-between">
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input class="h-4 w-4 rounded border-slate-300 bg-white text-lime-400 focus:ring-lime-400" type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} />
                    Produto ativo
                </label>
                <button class="rounded-2xl bg-lime-400 px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">
                    Salvar alteracoes
                </button>
            </div>
        </form>
        <script>
            (() => {
                const fileInput = document.querySelector('input[name="image_file"]');
                const previewWrap = document.getElementById('productEditImagePreviewWrap');
                const previewImg = document.getElementById('productEditImagePreview');
                const clearButton = document.getElementById('productEditImageClear');

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









