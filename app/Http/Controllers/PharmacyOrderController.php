<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PharmacyOrderController extends Controller
{
    public function index(Request $request)
    {
        $pharmacy = $request->user()->pharmacy;
        $status = (string) $request->input('status', '');

        $ordersQuery = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.pharmacy_id', $pharmacy->id)
            ->when($status !== '', function ($query) use ($status) {
                $query->where('orders.status', $status);
            });

        $orders = (clone $ordersQuery)
            ->select(
                'orders.id',
                'orders.customer_name',
                'orders.customer_phone',
                'orders.status',
                'orders.created_at',
                DB::raw('SUM(order_items.quantity) as items_count'),
                DB::raw('SUM(order_items.line_total) as pharmacy_total'),
                DB::raw('SUM(CASE WHEN order_items.seen_at IS NULL THEN 1 ELSE 0 END) as unseen_items')
            )
            ->groupBy('orders.id', 'orders.customer_name', 'orders.customer_phone', 'orders.status', 'orders.created_at')
            ->orderByDesc('orders.created_at')
            ->get();

        $statuses = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.pharmacy_id', $pharmacy->id)
            ->distinct()
            ->orderBy('orders.status')
            ->pluck('orders.status');

        return view('pharmacy.orders.index', [
            'orders' => $orders,
            'statuses' => $statuses,
            'status' => $status,
        ]);
    }

    public function show(Request $request, int $orderId)
    {
        $pharmacy = $request->user()->pharmacy;

        $items = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $orderId)
            ->where('products.pharmacy_id', $pharmacy->id)
            ->select(
                'order_items.id',
                'order_items.product_id',
                'order_items.product_name',
                'order_items.unit_price',
                'order_items.quantity',
                'order_items.line_total',
                'order_items.seen_at',
                'products.image_url'
            )
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $order = DB::table('orders')
            ->where('id', $orderId)
            ->first();

        $productIds = $items->pluck('product_id')->unique()->all();

        DB::table('order_items')
            ->where('order_id', $orderId)
            ->whereIn('product_id', $productIds)
            ->whereNull('seen_at')
            ->update(['seen_at' => Carbon::now()]);

        $total = $items->sum('line_total');

        return view('pharmacy.orders.show', [
            'order' => $order,
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function markUnseen(Request $request, int $orderId)
    {
        $pharmacy = $request->user()->pharmacy;

        $items = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $orderId)
            ->where('products.pharmacy_id', $pharmacy->id)
            ->select('order_items.product_id')
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $productIds = $items->pluck('product_id')->unique()->all();

        DB::table('order_items')
            ->where('order_id', $orderId)
            ->whereIn('product_id', $productIds)
            ->update(['seen_at' => null]);

        return back()->with('status', 'Pedido marcado como não visto.');
    }

    public function updateStatus(Request $request, int $orderId)
    {
        $pharmacy = $request->user()->pharmacy;

        $data = $request->validate([
            'status' => ['required', 'string', 'in:confirmado,em_preparacao,entregue,cancelado,rejeitado'],
        ]);

        $belongsToPharmacy = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $orderId)
            ->where('products.pharmacy_id', $pharmacy->id)
            ->exists();

        if (! $belongsToPharmacy) {
            abort(404);
        }

        $order = Order::query()->findOrFail($orderId);
        $oldStatus = mb_strtolower(trim((string) $order->status));
        $newStatus = mb_strtolower(trim((string) $data['status']));

        if ($oldStatus === $newStatus) {
            return back()->with('status', 'O pedido já está com esse estado.');
        }

        try {
            DB::transaction(function () use ($order, $newStatus) {
                $order->status = $newStatus;
                $order->save();
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'status' => $e->getMessage(),
            ]);
        }

        $labels = [
            'confirmado' => 'confirmado',
            'em_preparacao' => 'marcado como em preparação',
            'entregue' => 'marcado como entregue',
            'cancelado' => 'cancelado',
            'rejeitado' => 'rejeitado',
        ];

        return back()->with('status', 'Pedido ' . ($labels[$newStatus] ?? 'atualizado') . ' com sucesso.');
    }
}
