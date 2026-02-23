<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PharmacyProductController extends Controller
{
    public function index(Request $request)
    {
        $pharmacy = $request->user()->pharmacy;

        return view('pharmacy.products.index', [
            'products' => Product::where('pharmacy_id', $pharmacy->id)->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('pharmacy.products.create', [
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $pharmacy = $request->user()->pharmacy;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'image_file' => ['required', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imageUrl = $this->storeProductImage($request->file('image_file'));

        Product::create([
            'pharmacy_id' => $pharmacy->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category' => $data['category'] ?? null,
            'image_url' => $imageUrl,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('pharmacy.products.index')
            ->with('status', 'Produto criado com sucesso.');
    }

    public function edit(Request $request, Product $product)
    {
        $this->assertOwnership($request, $product);

        return view('pharmacy.products.edit', [
            'product' => $product,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->assertOwnership($request, $product);

        $imageFileRules = ['nullable', 'image', 'max:2048'];

        if (! $product->image_url) {
            $imageFileRules[] = 'required';
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'image_file' => $imageFileRules,
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imageUrl = $product->image_url;

        if ($request->hasFile('image_file')) {
            $imageUrl = $this->storeProductImage($request->file('image_file'));
        }

        $product->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category' => $data['category'] ?? null,
            'image_url' => $imageUrl,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('pharmacy.products.index')
            ->with('status', 'Produto atualizado.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->assertOwnership($request, $product);

        $product->delete();

        return redirect()
            ->route('pharmacy.products.index')
            ->with('status', 'Produto removido.');
    }

    private function assertOwnership(Request $request, Product $product): void
    {
        $pharmacy = $request->user()->pharmacy;

        if (! $pharmacy || $product->pharmacy_id !== $pharmacy->id) {
            abort(403);
        }
    }

    private function categoryOptions(): array
    {
        $defaults = config('medlink.categories', []);
        $existing = Product::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $merged = array_values(array_unique(array_filter(array_merge($defaults, $existing))));
        natcasesort($merged);

        return array_values($merged);
    }

    private function storeProductImage($file): string
    {
        $directory = public_path('uploads/products');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = uniqid('product_', true) . '.' . $extension;
        $file->move($directory, $filename);

        return 'uploads/products/' . $filename;
    }
}
