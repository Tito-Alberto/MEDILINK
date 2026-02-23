<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public static function options(): array
    {
        $path = storage_path('app/category-options.json');
        if (! File::exists($path)) {
            return [];
        }

        $data = json_decode(File::get($path), true);
        return is_array($data) ? $data : [];
    }

    public static function syncFromProducts(): array
    {
        $categories = Product::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $path = storage_path('app/category-options.json');
        File::put($path, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $categories;
    }
}
