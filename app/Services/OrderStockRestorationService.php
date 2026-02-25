<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderStockRestorationService
{
    public function restoreOrderStock(Order $order): void
    {
        $quantitiesByProduct = DB::table('order_items')
            ->where('order_id', $order->id)
            ->selectRaw('product_id, SUM(quantity) as qty_total')
            ->groupBy('product_id')
            ->pluck('qty_total', 'product_id');

        if ($quantitiesByProduct->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($quantitiesByProduct) {
            foreach ($quantitiesByProduct as $productId => $qtyTotal) {
                $productId = (int) $productId;
                $qtyTotal = (int) $qtyTotal;

                if ($productId <= 0 || $qtyTotal <= 0) {
                    continue;
                }

                /** @var Product|null $product */
                $product = Product::query()->lockForUpdate()->find($productId);
                if (! $product) {
                    continue;
                }

                $product->stock = max(0, (int) $product->stock) + $qtyTotal;
                $product->save();
            }
        });
    }
}

