<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.storefront', 'welcome'], function ($view) {
            $user = Auth::user();
            $notifications = [
                'enabled' => false,
                'pending' => false,
                'total' => 0,
                'order_count' => 0,
                'orders' => collect(),
                'out_of_stock' => collect(),
                'low_stock' => collect(),
                'threshold' => 5,
            ];

            if ($user && $user->pharmacy) {
                $pharmacy = $user->pharmacy;
                $notifications['enabled'] = true;

                if ($pharmacy->status !== 'approved') {
                    $notifications['pending'] = true;
                } else {
                    $threshold = 5;
                    $notifications['threshold'] = $threshold;

                    $outOfStock = Product::query()
                        ->where('pharmacy_id', $pharmacy->id)
                        ->where('stock', 0)
                        ->orderBy('name')
                        ->limit(5)
                        ->get();

                    $lowStock = Product::query()
                        ->where('pharmacy_id', $pharmacy->id)
                        ->whereBetween('stock', [1, $threshold])
                        ->orderBy('stock')
                        ->limit(5)
                        ->get();

                    $ordersQuery = DB::table('orders')
                        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->join('products', 'order_items.product_id', '=', 'products.id')
                        ->where('products.pharmacy_id', $pharmacy->id)
                        ->whereNull('order_items.seen_at');

                    $orderCount = (clone $ordersQuery)
                        ->distinct('orders.id')
                        ->count('orders.id');

                    $orders = (clone $ordersQuery)
                        ->select('orders.id', 'orders.total', 'orders.status', 'orders.created_at')
                        ->distinct()
                        ->orderByDesc('orders.created_at')
                        ->limit(5)
                        ->get();

                    $notifications['order_count'] = $orderCount;
                    $notifications['orders'] = $orders;
                    $notifications['out_of_stock'] = $outOfStock;
                    $notifications['low_stock'] = $lowStock;
                    $notifications['total'] = $orderCount + $outOfStock->count() + $lowStock->count();
                }
            }

            $defaultCategories = config('medlink.categories', []);
            $existingCategories = Product::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('pharmacy_id')
                        ->orWhereHas('pharmacy', function ($sub) {
                            $sub->where('status', 'approved');
                        });
                })
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
            $categories = collect(array_unique(array_filter(array_merge($defaultCategories, $existingCategories))))
                ->sort()
                ->values();

            $view->with('headerNotifications', $notifications);
            $view->with('headerCategories', $categories);
        });
    }
}
