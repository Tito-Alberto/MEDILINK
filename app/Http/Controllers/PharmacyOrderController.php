<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
                'orders.invoice_number',
                DB::raw('SUM(order_items.quantity) as items_count'),
                DB::raw('SUM(order_items.line_total) as pharmacy_total'),
                DB::raw('MAX(orders.delivery_fee) as delivery_fee'),
                DB::raw('MAX(orders.tax_amount) as tax_amount'),
                DB::raw('SUM(CASE WHEN order_items.seen_at IS NULL THEN 1 ELSE 0 END) as unseen_items')
            )
            ->groupBy('orders.id', 'orders.customer_name', 'orders.customer_phone', 'orders.status', 'orders.created_at', 'orders.invoice_number')
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

        $items = $this->orderItemsForPharmacy($pharmacy->id, $orderId);

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

    public function invoice(Request $request, int $orderId)
    {
        $pharmacy = $request->user()->pharmacy;
        $payload = $this->buildInvoicePayload($pharmacy->id, $orderId);

        return view('pharmacy.orders.invoice', $payload + [
            'autoPrint' => (bool) $request->boolean('print'),
        ]);
    }

    public function confirmAndPrint(Request $request, int $orderId)
    {
        $pharmacy = $request->user()->pharmacy;

        if (! $this->orderBelongsToPharmacy($pharmacy->id, $orderId)) {
            abort(404);
        }

        $order = Order::query()->findOrFail($orderId);
        $currentStatus = $this->normalizeStatus((string) $order->status);

        if (in_array($currentStatus, ['cancelado', 'rejeitado'], true)) {
            return redirect()
                ->route('pharmacy.orders.show', $orderId)
                ->withErrors(['status' => 'Nao e possivel confirmar um pedido cancelado ou rejeitado.']);
        }

        if ($currentStatus === 'novo') {
            DB::transaction(function () use ($order) {
                $this->applyOrderStatusChange($order, 'confirmado');
            });
        } elseif (in_array($currentStatus, ['confirmado', 'em_preparacao', 'entregue'], true)) {
            DB::transaction(function () use ($order) {
                $this->ensureInvoiceMetadata($order);
                $order->save();
            });
        } else {
            DB::transaction(function () use ($order) {
                $this->applyOrderStatusChange($order, 'confirmado');
            });
        }

        return back()
            ->with('status', 'Pedido confirmado com sucesso. Factura pronta para impressao.')
            ->with('invoice_modal_url', route('pharmacy.orders.invoice', [
                'order' => $orderId,
                'print' => 1,
                'embed' => 1,
            ]));
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

        return back()->with('status', 'Pedido marcado como nao visto.');
    }

    public function updateStatus(Request $request, int $orderId)
    {
        $pharmacy = $request->user()->pharmacy;

        $data = $request->validate([
            'status' => ['required', 'string', 'in:confirmado,em_preparacao,entregue,cancelado,rejeitado'],
        ]);

        if (! $this->orderBelongsToPharmacy($pharmacy->id, $orderId)) {
            abort(404);
        }

        $order = Order::query()->findOrFail($orderId);
        $oldStatus = $this->normalizeStatus((string) $order->status);
        $newStatus = $this->normalizeStatus((string) $data['status']);

        if ($oldStatus === $newStatus) {
            return back()->with('status', 'O pedido ja esta com esse estado.');
        }

        try {
            DB::transaction(function () use ($order, $newStatus) {
                $this->applyOrderStatusChange($order, $newStatus);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'status' => $e->getMessage(),
            ]);
        }

        $labels = [
            'confirmado' => 'confirmado',
            'em_preparacao' => 'marcado como em preparacao',
            'entregue' => 'marcado como entregue',
            'cancelado' => 'cancelado',
            'rejeitado' => 'rejeitado',
        ];

        return back()->with('status', 'Pedido ' . ($labels[$newStatus] ?? 'atualizado') . ' com sucesso.');
    }

    private function applyOrderStatusChange(Order $order, string $newStatus): void
    {
        $normalized = $this->normalizeStatus($newStatus);

        $order->status = $normalized;

        if ($normalized === 'confirmado') {
            $this->ensureInvoiceMetadata($order);
            $this->notifyCustomerOrderConfirmed($order);
        }

        $order->save();
    }

    private function ensureInvoiceMetadata(Order $order): void
    {
        if (! $order->invoice_number) {
            $order->invoice_number = 'FAC-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
        }

        if (! $order->invoice_date) {
            $order->invoice_date = now();
        }
    }

    private function notifyCustomerOrderConfirmed(Order $order): void
    {
        if (! $order->customer_user_id) {
            return;
        }

        $order->customer_confirmed_notified_at = now();
        $order->customer_confirmed_seen_at = null;
    }

    private function buildInvoicePayload(int $pharmacyId, int $orderId): array
    {
        $items = $this->orderItemsForPharmacy($pharmacyId, $orderId);

        if ($items->isEmpty()) {
            abort(404);
        }

        $order = Order::query()->findOrFail($orderId);
        $total = (float) $items->sum('line_total');
        $pharmacy = auth()->user()?->pharmacy;

        return [
            'order' => $order,
            'items' => $items,
            'total' => $total,
            'pharmacy' => $pharmacy,
        ];
    }

    private function orderItemsForPharmacy(int $pharmacyId, int $orderId)
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $orderId)
            ->where('products.pharmacy_id', $pharmacyId)
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
    }

    private function orderBelongsToPharmacy(int $pharmacyId, int $orderId): bool
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $orderId)
            ->where('products.pharmacy_id', $pharmacyId)
            ->exists();
    }

    private function normalizeStatus(string $status): string
    {
        return mb_strtolower(trim($status));
    }
}
