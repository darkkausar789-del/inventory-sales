<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $user = $request->user();

        $sales = Sale::with('user')
            // staff hanya melihat transaksinya sendiri
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->when($request->q, fn($q, $s) => $q->where('sale_number', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('sold_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('sold_at', '<=', $d))
            ->latest('sold_at')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $productOptions = Product::orderBy('name')->get()->map(fn($p) => [
            'id' => $p->id,
            'label' => $p->sku . ' - ' . $p->name
                . ' (stok ' . $p->stock . ') - Rp '
                . number_format($p->selling_price, 0, ',', '.'),
        ])->values();

        return view('sales.create', compact('productOptions'));
    }

    public function store(Request $request)
    {
        // harga TIDAK diminta dari browser; hanya produk dan kuantitas
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Tambahkan minimal satu item.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dipilih dua kali.',
            'items.*.quantity.min' => 'Kuantitas harus lebih dari 0.',
        ]);

        $sale = DB::transaction(function () use ($data, $request) {
            $sale = Sale::create([
                'user_id' => $request->user()->id,
                'sale_number' => 'SL-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            // urutkan agar penguncian baris selalu berurutan (mengurangi risiko deadlock)
            $items = collect($data['items'])->sortBy('product_id');
            $total = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup "
                            . "(tersedia {$product->stock}, diminta {$qty}).",
                    ]);
                }

                $subtotal = $product->selling_price * $qty; // harga dari database

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Penjualan berhasil disimpan dan stok berkurang.');
    }

    public function show(Request $request, Sale $sale)
    {
        abort_unless(
            $request->user()->isAdmin() || $sale->user_id === $request->user()->id,
            403
        );

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {
                $sale = Sale::lockForUpdate()->findOrFail($sale->id);

                if ($sale->status !== 'completed') {
                    throw ValidationException::withMessages([
                        'cancel' => 'Transaksi ini sudah dibatalkan.',
                    ]);
                }

                foreach ($sale->items as $item) {
                    Product::withTrashed()
                        ->lockForUpdate()
                        ->findOrFail($item->product_id)
                        ->increment('stock', $item->quantity);
                }

                $sale->update(['status' => 'cancelled']);
            });
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Penjualan dibatalkan dan stok dikembalikan.');
    }
}