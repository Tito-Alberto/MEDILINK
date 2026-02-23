<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function show()
    {
        [$items, $subtotal] = $this->buildCartItems();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('status', 'Seu carrinho está vazio.');
        }

        return view('storefront.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryFee' => 0.0,
            'total' => $subtotal,
        ]);
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        [$items, $subtotal] = $this->buildCartItems();
        if (empty($items)) {
            return redirect()->route('cart.index')->with('status', 'Seu carrinho está vazio.');
        }

        $order = DB::transaction(function () use ($data, $items, $subtotal) {
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'total' => $subtotal,
                'status' => 'novo',
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'unit_price' => $item['product']->price,
                    'quantity' => $item['qty'],
                    'line_total' => $item['line_total'],
                ]);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('orders.show', $order);
    }

    public function showOrder(Order $order)
    {
        $order->load('items');

        return view('storefront.success', [
            'order' => $order,
        ]);
    }

    private function buildCartItems(): array
    {
        $cart = session()->get('cart', []);
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
