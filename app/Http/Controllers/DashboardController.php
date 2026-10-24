<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $completedSales = Sale::where('status', 'completed');

        $stats = [
            'total_products' => Product::count(),
            'sales_today' => (clone $completedSales)->whereDate('sold_at', today())->sum('total'),
            'sales_month' => (clone $completedSales)
                ->whereYear('sold_at', now()->year)
                ->whereMonth('sold_at', now()->month)
                ->sum('total'),
            'sales_count' => (clone $completedSales)->count(),
            'purchases_count' => Purchase::where('status', 'completed')->count(),
            'low_stock_count' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
        ];

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $recentSales = Sale::with('user')
            ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('user_id', auth()->id()))
            ->latest('sold_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'lowStockProducts', 'recentSales'));
    }
}