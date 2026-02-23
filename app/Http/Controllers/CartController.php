<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        [$items, $subtotal] = $this->buildCartItems();

        return view('storefront.cart', [
            'items' => $items,
            'subtotal' => $subtotal,
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $qty = $data['qty'] ?? 1;
        $cart = $this->getCart();
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $qty;
        $this->storeCart($cart);

        return back()->with('status', 'Produto adicionado ao carrinho.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart = $this->getCart();
        $cart[$product->id] = $data['qty'];
        $this->storeCart($cart);

        return back()->with('status', 'Quantidade atualizada.');
    }

    public function remove(Product $product)
    {
        $cart = $this->getCart();
        unset($cart[$product->id]);
        $this->storeCart($cart);

        return back()->with('status', 'Produto removido.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('status', 'Carrinho esvaziado.');
    }

    private function getCart(): array
    {
        return session()->get('cart', []);
    }

    private function storeCart(array $cart): void
    {
        session(['cart' => $cart]);
    }

    private function buildCartItems(): array
    {
        $cart = $this->getCart();
        if (empty($cart)) {
            return [[], 0.0];
        }

        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $productId => $qty) {
            if (! $products->has($productId)) {
                continue;
            }

            $product = $products->get($productId);
            $lineTotal = (float) $product->price * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'line_total' => $lineTotal,
            ];
        }

        return [$items, round($subtotal, 2)];
    }
}
