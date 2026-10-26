<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->where('stock', '>', 0) // produk habis tidak ditampilkan
            ->when($request->q, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}