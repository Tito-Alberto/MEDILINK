<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home()
    {
        $baseQuery = Product::query()
            ->with('pharmacy')
            ->withSum(['orderItems as sold_quantity' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereIn('status', ['confirmado', 'em_preparacao', 'entregue']);
                });
            }], 'quantity')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('pharmacy_id')
                    ->orWhereHas('pharmacy', function ($sub) {
                        $sub->where('status', 'approved');
                    });
            });

        $featuredProducts = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $quickProducts = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        $productsByCategory = (clone $baseQuery)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderByDesc('created_at')
            ->take(36)
            ->get()
            ->groupBy(function ($product) {
                return trim((string) $product->category);
            })
            ->map(function ($items) {
                return $items->take(4)->values();
            })
            ->take(6);

        $featuredPharmacies = Pharmacy::query()
            ->where('status', 'approved')
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(4)
            ->get();

        $allProductCount = (clone $baseQuery)->count();
        $allPharmacyCount = Pharmacy::query()
            ->where('status', 'approved')
            ->count();
        $adminCount = User::query()
            ->where('is_admin', true)
            ->count();

        return view('welcome', [
            'featuredProducts' => $featuredProducts,
            'productsByCategory' => $productsByCategory,
            'quickProducts' => $quickProducts,
            'quickProduct' => $quickProducts->first(),
            'featuredPharmacies' => $featuredPharmacies,
            'allProductCount' => $allProductCount,
            'allPharmacyCount' => $allPharmacyCount,
            'allTotalCount' => $allProductCount + $allPharmacyCount,
            'adminCount' => $adminCount,
        ]);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $category = trim((string) $request->input('category', ''));
        $like = $search !== '' ? '%' . $search . '%' : null;

        $baseQuery = Product::query()
            ->with('pharmacy')
            ->withSum(['orderItems as sold_quantity' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereIn('status', ['confirmado', 'em_preparacao', 'entregue']);
                });
            }], 'quantity')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('pharmacy_id')
                    ->orWhereHas('pharmacy', function ($sub) {
                        $sub->where('status', 'approved');
                    });
            });

        $defaultCategories = config('medlink.categories', []);
        $existingCategories = (clone $baseQuery)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
        $categories = collect(array_unique(array_filter(array_merge($defaultCategories, $existingCategories))))
            ->sort()
            ->values();

        $productsQuery = (clone $baseQuery)
            ->when($search !== '', function ($query) use ($like) {
                $query->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('category', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('pharmacy', function ($pharmacyQuery) use ($like) {
                            $pharmacyQuery->where('status', 'approved')
                                ->where('name', 'like', $like);
                        });
                });
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->orderByDesc('created_at');

        $products = $productsQuery->get();

        $pharmacies = collect();
        if ($search !== '') {
            $pharmacies = Pharmacy::query()
                ->where('status', 'approved')
                ->where('name', 'like', $like)
                ->orderBy('name')
                ->take(6)
                ->get();
        }

        $allProductCount = (clone $baseQuery)->count();
        $allPharmacyCount = Pharmacy::query()
            ->where('status', 'approved')
            ->count();

        return view('storefront.index', [
            'products' => $products,
            'pharmacies' => $pharmacies,
            'search' => $search,
            'category' => $category,
            'categories' => $categories,
            'productCount' => $products->count(),
            'pharmacyCount' => $pharmacies->count(),
            'totalCount' => $products->count() + $pharmacies->count(),
            'allProductCount' => $allProductCount,
            'allPharmacyCount' => $allPharmacyCount,
            'allTotalCount' => $allProductCount + $allPharmacyCount,
        ]);
    }

    public function pharmacy(Pharmacy $pharmacy)
    {
        if ($pharmacy->status !== 'approved') {
            abort(404);
        }

        $products = $pharmacy->products()
            ->withSum(['orderItems as sold_quantity' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereIn('status', ['confirmado', 'em_preparacao', 'entregue']);
                });
            }], 'quantity')
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('storefront.pharmacy', [
            'pharmacy' => $pharmacy,
            'products' => $products,
        ]);
    }

    public function pharmacies(Request $request)
    {
        $isAdmin = (bool) ($request->user()?->is_admin);

        $pharmacies = Pharmacy::query()
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->when(! $isAdmin, function ($query) {
                $query->where('status', 'approved');
            })
            ->orderBy('name')
            ->get();

        return view('storefront.pharmacies', [
            'pharmacies' => $pharmacies,
            'isAdminView' => $isAdmin,
        ]);
    }

    public function show(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }

        if ($product->pharmacy && $product->pharmacy->status !== 'approved') {
            abort(404);
        }

        return view('storefront.show', [
            'product' => $product,
        ]);
    }
}
