<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
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

        $purchases = Purchase::with(['supplier', 'user'])
            // staff hanya melihat transaksinya sendiri
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->when($request->q, fn($q, $s) => $q->where('purchase_number', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('purchased_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('purchased_at', '<=', $d))
            ->latest('purchased_at')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $productOptions = Product::orderBy('name')->get()->map(fn($p) => [
            'id' => $p->id,
            'label' => $p->sku . ' - ' . $p->name . ' (stok ' . $p->stock . ')',
            'cost' => $p->purchase_price,
        ])->values();

        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'productOptions' => $productOptions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Tambahkan minimal satu item.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dipilih dua kali.',
            'items.*.quantity.min' => 'Kuantitas harus lebih dari 0.',
        ]);

        $purchase = DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()->id,
                'purchase_number' => 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $subtotal = $qty * $item['unit_cost'];

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $qty);
                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian berhasil disimpan dan stok bertambah.');
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_unless(
            $request->user()->isAdmin() || $purchase->user_id === $request->user()->id,
            403
        );

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        try {
            DB::transaction(function () use ($purchase) {
                $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);

                if ($purchase->status !== 'completed') {
                    throw ValidationException::withMessages([
                        'cancel' => 'Transaksi ini sudah dibatalkan.',
                    ]);
                }

                foreach ($purchase->items as $item) {
                    $product = Product::withTrashed()->lockForUpdate()->findOrFail($item->product_id);

                    // stok tidak boleh menjadi negatif
                    if ($product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'cancel' => "Stok {$product->name} tidak cukup untuk membatalkan pembelian ini "
                                . "(stok {$product->stock}, dibutuhkan {$item->quantity}).",
                        ]);
                    }

                    $product->decrement('stock', $item->quantity);
                }

                $purchase->update(['status' => 'cancelled']);
            });
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Pembelian dibatalkan dan stok dikembalikan.');
    }
}