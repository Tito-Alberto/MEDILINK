<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $reportCategoryOptions = [
            '' => 'Todas Categorias',
            'sales_by_pharmacy' => 'Resumo de vendas por farmácia',
            'orders_summary' => 'Resumo de pedidos',
            'customers_with_orders' => 'Clientes que fizeram pedidos',
            'users_registrations' => 'Cadastros de utilizadores',
            'approved_pharmacies' => 'Farmácias aprovadas',
            'pharmacies_with_products' => 'Farmácias que registaram produtos',
            'stock_by_pharmacy' => 'Stock por farmácia',
            'registered_pharmacies' => 'Farmácias registadas',
            'orders_list' => 'Lista de vendas / pedidos',
        ];

        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'q' => ['nullable', 'string', 'max:255'],
            'report_category' => ['nullable', 'string', 'max:100'],
        ]);

        $queryText = trim((string) ($filters['q'] ?? ''));
        $selectedReportCategory = trim((string) ($filters['report_category'] ?? ''));
        if (! array_key_exists($selectedReportCategory, $reportCategoryOptions)) {
            $selectedReportCategory = '';
        }
        $dateFrom = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $dateTo = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : null;
        $hasFilters = $queryText !== '' || $dateFrom !== null || $dateTo !== null || $selectedReportCategory !== '';

        $applyDateRange = function ($query, string $column = 'created_at') use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->where($column, '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where($column, '<=', $dateTo);
            }

            return $query;
        };

        $usersBase = User::query();
        $applyDateRange($usersBase, 'created_at');
        if ($queryText !== '') {
            $like = '%' . $queryText . '%';
            $usersBase->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        $users = (clone $usersBase)
            ->latest()
            ->limit(80)
            ->get(['id', 'name', 'email', 'is_admin', 'created_at']);

        $ordersBase = Order::query();
        $applyDateRange($ordersBase, 'created_at');
        if ($queryText !== '') {
            $like = '%' . $queryText . '%';
            $ordersBase->where(function ($query) use ($like, $queryText) {
                $query->where('customer_name', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like)
                    ->orWhere('customer_address', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhere('invoice_number', 'like', $like);

                if (is_numeric($queryText)) {
                    $query->orWhere('id', (int) $queryText);
                }
            });
        }

        $orders = (clone $ordersBase)
            ->latest()
            ->limit(120)
            ->get([
                'id',
                'customer_name',
                'customer_phone',
                'customer_address',
                'status',
                'subtotal',
                'delivery_fee',
                'tax_amount',
                'total',
                'created_at',
            ]);

        $customersWithOrders = (clone $ordersBase)
            ->select('customer_name', 'customer_phone')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as total_spent')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->groupBy('customer_name', 'customer_phone')
            ->orderByDesc('orders_count')
            ->orderByDesc('last_order_at')
            ->limit(80)
            ->get();

        $pharmaciesBase = Pharmacy::query();
        $applyDateRange($pharmaciesBase, 'created_at');
        if ($queryText !== '') {
            $like = '%' . $queryText . '%';
            $pharmaciesBase->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('responsible_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('nif', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhere('address', 'like', $like);
            });
        }

        $pharmacies = (clone $pharmaciesBase)
            ->withCount([
                'products as total_products_count',
                'products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->withSum([
                'products as active_stock_sum' => function ($query) {
                    $query->where('is_active', true);
                },
            ], 'stock')
            ->latest()
            ->limit(120)
            ->get();

        $approvedPharmaciesBase = Pharmacy::query()
            ->where('status', 'approved')
            ->whereNotNull('approved_at');
        $applyDateRange($approvedPharmaciesBase, 'approved_at');
        if ($queryText !== '') {
            $like = '%' . $queryText . '%';
            $approvedPharmaciesBase->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('responsible_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('nif', 'like', $like);
            });
        }

        $approvedPharmacies = (clone $approvedPharmaciesBase)
            ->orderByDesc('approved_at')
            ->limit(80)
            ->get(['id', 'name', 'responsible_name', 'email', 'phone', 'approved_at']);

        $salesByPharmacy = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('pharmacies', 'pharmacies.id', '=', 'products.pharmacy_id')
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->where('orders.created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->where('orders.created_at', '<=', $dateTo);
            })
            ->when($queryText !== '', function ($query) use ($queryText) {
                $like = '%' . $queryText . '%';
                $query->where(function ($sub) use ($like, $queryText) {
                    $sub->where('pharmacies.name', 'like', $like)
                        ->orWhere('order_items.product_name', 'like', $like)
                        ->orWhere('orders.customer_name', 'like', $like);

                    if (is_numeric($queryText)) {
                        $sub->orWhere('orders.id', (int) $queryText);
                    }
                });
            })
            ->selectRaw("COALESCE(pharmacies.name, 'Sem farmacia') as pharmacy_name")
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->selectRaw('SUM(order_items.line_total) as sales_total')
            ->groupBy('pharmacies.id', 'pharmacies.name')
            ->orderByDesc('sales_total')
            ->limit(100)
            ->get();

        $productRegistrationsByPharmacy = DB::table('products')
            ->leftJoin('pharmacies', 'pharmacies.id', '=', 'products.pharmacy_id')
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->where('products.created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->where('products.created_at', '<=', $dateTo);
            })
            ->when($queryText !== '', function ($query) use ($queryText) {
                $like = '%' . $queryText . '%';
                $query->where(function ($sub) use ($like) {
                    $sub->where('pharmacies.name', 'like', $like)
                        ->orWhere('products.name', 'like', $like)
                        ->orWhere('products.category', 'like', $like);
                });
            })
            ->selectRaw("COALESCE(pharmacies.name, 'Sem farmacia') as pharmacy_name")
            ->selectRaw('COUNT(products.id) as products_registered')
            ->selectRaw('SUM(CASE WHEN products.is_active = 1 THEN 1 ELSE 0 END) as active_products')
            ->selectRaw('MAX(products.created_at) as last_product_at')
            ->groupBy('pharmacies.id', 'pharmacies.name')
            ->orderByDesc('products_registered')
            ->limit(100)
            ->get();

        $stockByPharmacy = DB::table('pharmacies')
            ->leftJoin('products', 'products.pharmacy_id', '=', 'pharmacies.id')
            ->when($queryText !== '', function ($query) use ($queryText) {
                $like = '%' . $queryText . '%';
                $query->where(function ($sub) use ($like) {
                    $sub->where('pharmacies.name', 'like', $like)
                        ->orWhere('pharmacies.responsible_name', 'like', $like)
                        ->orWhere('pharmacies.status', 'like', $like);
                });
            })
            ->select('pharmacies.id', 'pharmacies.name', 'pharmacies.status')
            ->selectRaw('COUNT(products.id) as products_total')
            ->selectRaw('SUM(CASE WHEN products.is_active = 1 THEN 1 ELSE 0 END) as active_products')
            ->selectRaw('COALESCE(SUM(CASE WHEN products.is_active = 1 THEN products.stock ELSE 0 END), 0) as stock_total')
            ->selectRaw('SUM(CASE WHEN products.is_active = 1 AND products.stock = 0 THEN 1 ELSE 0 END) as out_of_stock_count')
            ->selectRaw('SUM(CASE WHEN products.is_active = 1 AND products.stock BETWEEN 1 AND 5 THEN 1 ELSE 0 END) as low_stock_count')
            ->groupBy('pharmacies.id', 'pharmacies.name', 'pharmacies.status')
            ->orderBy('pharmacies.name')
            ->limit(120)
            ->get();

        $orderTotals = [
            'orders_count' => (clone $ordersBase)->count(),
            'subtotal_sum' => (float) ((clone $ordersBase)->sum('subtotal') ?? 0),
            'tax_sum' => (float) ((clone $ordersBase)->sum('tax_amount') ?? 0),
            'delivery_sum' => (float) ((clone $ordersBase)->sum('delivery_fee') ?? 0),
            'total_sum' => (float) ((clone $ordersBase)->sum('total') ?? 0),
        ];

        $summary = [
            'users_count' => (clone $usersBase)->count(),
            'admin_users_count' => (clone $usersBase)->where('is_admin', true)->count(),
            'pharmacies_count' => (clone $pharmaciesBase)->count(),
            'pharmacies_approved_count' => (clone $pharmaciesBase)->where('status', 'approved')->count(),
            'pharmacies_pending_count' => (clone $pharmaciesBase)->where('status', 'pending')->count(),
            'pharmacies_rejected_count' => (clone $pharmaciesBase)->where('status', 'rejected')->count(),
            'products_count' => (clone Product::query())
                ->when($dateFrom, fn($query) => $query->where('created_at', '>=', $dateFrom))
                ->when($dateTo, fn($query) => $query->where('created_at', '<=', $dateTo))
                ->when($queryText !== '', function ($query) use ($queryText) {
                    $like = '%' . $queryText . '%';
                    $query->where(function ($sub) use ($like) {
                        $sub->where('name', 'like', $like)
                            ->orWhere('category', 'like', $like);
                    });
                })
                ->count(),
            'active_products_count' => (clone Product::query())
                ->where('is_active', true)
                ->when($dateFrom, fn($query) => $query->where('created_at', '>=', $dateFrom))
                ->when($dateTo, fn($query) => $query->where('created_at', '<=', $dateTo))
                ->when($queryText !== '', function ($query) use ($queryText) {
                    $like = '%' . $queryText . '%';
                    $query->where(function ($sub) use ($like) {
                        $sub->where('name', 'like', $like)
                            ->orWhere('category', 'like', $like);
                    });
                })
                ->count(),
            'orders_count' => $orderTotals['orders_count'],
            'sales_total' => $orderTotals['total_sum'],
            'customers_count' => $customersWithOrders->count(),
            'approved_pharmacies_in_period' => (clone $approvedPharmaciesBase)->count(),
        ];

        return view('admin.reports', [
            'filters' => [
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'q' => $queryText,
                'report_category' => $selectedReportCategory,
            ],
            'hasFilters' => $hasFilters,
            'reportCategoryOptions' => $reportCategoryOptions,
            'summary' => $summary,
            'orderTotals' => $orderTotals,
            'users' => $users,
            'orders' => $orders,
            'customersWithOrders' => $customersWithOrders,
            'pharmacies' => $pharmacies,
            'approvedPharmacies' => $approvedPharmacies,
            'salesByPharmacy' => $salesByPharmacy,
            'productRegistrationsByPharmacy' => $productRegistrationsByPharmacy,
            'stockByPharmacy' => $stockByPharmacy,
        ]);
    }
}
