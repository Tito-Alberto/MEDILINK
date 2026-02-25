<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function markOrderNotificationsSeen(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $customerMarked = 0;
        $pharmacyOrderItemsMarked = 0;

        if (
            Schema::hasColumn('orders', 'customer_user_id')
            && Schema::hasColumn('orders', 'customer_confirmed_notified_at')
            && Schema::hasColumn('orders', 'customer_confirmed_seen_at')
        ) {
            $customerMarked = Order::query()
                ->where('customer_user_id', $user->id)
                ->whereNotNull('customer_confirmed_notified_at')
                ->whereNull('customer_confirmed_seen_at')
                ->update([
                    'customer_confirmed_seen_at' => now(),
                ]);
        }

        if ($user->pharmacy && $user->pharmacy->status === 'approved') {
            $orderItemIds = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.pharmacy_id', $user->pharmacy->id)
                ->whereNull('order_items.seen_at')
                ->pluck('order_items.id');

            if ($orderItemIds->isNotEmpty()) {
                $pharmacyOrderItemsMarked = DB::table('order_items')
                    ->whereIn('id', $orderItemIds->all())
                    ->update([
                        'seen_at' => now(),
                    ]);
            }
        }

        return response()->json([
            'ok' => true,
            'customer_marked' => (int) $customerMarked,
            'pharmacy_order_items_marked' => (int) $pharmacyOrderItemsMarked,
        ]);
    }
}

