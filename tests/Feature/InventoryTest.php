<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    // ---------- helper ----------

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => 'staff']);
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Umum']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'SKU-001',
            'name' => 'Produk Uji',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'minimum_stock' => 2,
        ], $overrides));
    }

    // ---------- 1. AUTH ----------

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = $this->staff();

        $this->post('/login', ['email' => $user->email, 'password' => 'salah-banget'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_with_correct_password_works(): void
    {
        $user = $this->staff();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    // ---------- 2. PRODUCT ----------

    public function test_duplicate_sku_is_rejected_by_validation(): void
    {
        $product = $this->makeProduct(['sku' => 'DUP-1']);

        $this->actingAs($this->admin())
            ->post(route('products.store'), [
                'sku' => 'DUP-1',
                'name' => 'Lain',
                'category_id' => $product->category_id,
                'purchase_price' => 100,
                'selling_price' => 200,
                'stock' => 0,
                'minimum_stock' => 0,
            ])
            ->assertSessionHasErrors('sku');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_duplicate_sku_is_also_blocked_by_database_constraint(): void
    {
        $this->makeProduct(['sku' => 'DUP-2']);

        $this->expectException(QueryException::class);
        $this->makeProduct(['sku' => 'DUP-2']); // lewati validasi, langsung ke database
    }

    public function test_negative_price_is_rejected(): void
    {
        $category = Category::create(['name' => 'Umum']);

        $this->actingAs($this->admin())
            ->post(route('products.store'), [
                'sku' => 'NEG-1',
                'name' => 'Harga Negatif',
                'category_id' => $category->id,
                'purchase_price' => -100,
                'selling_price' => 200,
                'stock' => 0,
                'minimum_stock' => 0,
            ])
            ->assertSessionHasErrors('purchase_price');

        $this->assertDatabaseCount('products', 0);
    }

    // ---------- 3. PURCHASE ----------

    public function test_successful_purchase_increases_stock(): void
    {
        $product = $this->makeProduct(['stock' => 10]);
        $supplier = Supplier::create(['name' => 'Supplier Uji']);

        $this->actingAs($this->staff())
            ->post(route('purchases.store'), [
                'supplier_id' => $supplier->id,
                'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000]],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(15, $product->fresh()->stock);
        $this->assertEquals(5000, Purchase::first()->total);
    }

    public function test_purchase_with_zero_quantity_is_rejected(): void
    {
        $product = $this->makeProduct();
        $supplier = Supplier::create(['name' => 'Supplier Uji']);

        $this->actingAs($this->staff())
            ->post(route('purchases.store'), [
                'supplier_id' => $supplier->id,
                'items' => [['product_id' => $product->id, 'quantity' => 0, 'unit_cost' => 1000]],
            ])
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertDatabaseCount('purchases', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    // ---------- 4. SALE ----------

    public function test_successful_sale_decreases_stock_and_total_is_calculated_by_server(): void
    {
        $product = $this->makeProduct(['stock' => 10, 'selling_price' => 2000]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), [
                // unit_price palsu dari browser harus diabaikan
                'items' => [['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1]],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertEquals(6000, Sale::first()->total); // 3 x 2000 dari database
    }

    // ---------- 5. INSUFFICIENT STOCK ----------

    public function test_sale_with_insufficient_stock_is_rejected_without_changes(): void
    {
        $product = $this->makeProduct(['stock' => 2]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), [
                'items' => [['product_id' => $product->id, 'quantity' => 5]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    // ---------- 6. ATOMICITY ----------

    public function test_failed_item_rolls_back_the_whole_sale(): void
    {
        $a = $this->makeProduct(['sku' => 'A-1', 'name' => 'Produk A', 'stock' => 10]);
        $b = $this->makeProduct(['sku' => 'B-1', 'name' => 'Produk B', 'stock' => 1]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), [
                'items' => [
                    ['product_id' => $a->id, 'quantity' => 5],  // cukup
                    ['product_id' => $b->id, 'quantity' => 99], // tidak cukup
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(10, $a->fresh()->stock); // stok A tidak ikut berkurang
        $this->assertSame(1, $b->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_cancelling_a_sale_restores_stock(): void
    {
        $product = $this->makeProduct(['stock' => 10]);
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ]);
        $this->assertSame(6, $product->fresh()->stock);

        $sale = Sale::first();

        $this->actingAs($this->admin())->post(route('sales.cancel', $sale));

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $sale->fresh()->status);
    }

    public function test_purchase_cannot_be_cancelled_if_its_stock_was_already_sold(): void
    {
        $product = $this->makeProduct(['stock' => 10]);
        $supplier = Supplier::create(['name' => 'Supplier Uji']);
        $staff = $this->staff();

        // beli 5 -> stok 15
        $this->actingAs($staff)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000]],
        ]);
        // jual 12 -> stok 3
        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 12]],
        ]);
        $this->assertSame(3, $product->fresh()->stock);

        $purchase = Purchase::first();

        // membatalkan pembelian butuh 5, stok hanya 3 -> ditolak
        $this->actingAs($this->admin())
            ->post(route('purchases.cancel', $purchase))
            ->assertSessionHas('error');

        $this->assertSame('completed', $purchase->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    // ---------- 7. AUTHORIZATION ----------

    public function test_staff_cannot_access_admin_pages(): void
    {
        $staff = $this->staff();

        foreach (['categories.index', 'suppliers.index', 'users.index', 'products.create'] as $route) {
            $this->actingAs($staff)->get(route($route))->assertForbidden();
        }
    }

    public function test_admin_can_access_admin_pages(): void
    {
        $admin = $this->admin();

        foreach (['categories.index', 'suppliers.index', 'users.index', 'products.create'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_staff_cannot_cancel_a_transaction(): void
    {
        $staff = $this->staff();
        $sale = Sale::create([
            'user_id' => $staff->id,
            'sale_number' => 'SL-TEST',
            'total' => 0,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        $this->actingAs($staff)->post(route('sales.cancel', $sale))->assertForbidden();

        $this->assertSame('completed', $sale->fresh()->status);
    }

    public function test_staff_cannot_view_another_users_sale(): void
    {
        $sale = Sale::create([
            'user_id' => $this->admin()->id,
            'sale_number' => 'SL-MILIK-ADMIN',
            'total' => 0,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        $this->actingAs($this->staff())->get(route('sales.show', $sale))->assertForbidden();
    }

    // ---------- 8. SEARCH, FILTER, PAGINATION ----------

    public function test_product_search_filters_by_name_or_sku(): void
    {
        $this->makeProduct(['sku' => 'BRS-1', 'name' => 'Beras Premium']);
        $this->makeProduct(['sku' => 'MYK-1', 'name' => 'Minyak Goreng']);

        $this->actingAs($this->staff())
            ->get(route('products.index', ['q' => 'Beras']))
            ->assertSee('Beras Premium')
            ->assertDontSee('Minyak Goreng');

        $this->actingAs($this->staff())
            ->get(route('products.index', ['q' => 'MYK']))
            ->assertSee('Minyak Goreng')
            ->assertDontSee('Beras Premium');
    }

    public function test_low_stock_filter_only_shows_products_at_or_below_minimum(): void
    {
        $this->makeProduct(['sku' => 'LOW-1', 'name' => 'Barang Sedikit', 'stock' => 1, 'minimum_stock' => 5]);
        $this->makeProduct(['sku' => 'HI-1', 'name' => 'Barang Banyak', 'stock' => 50, 'minimum_stock' => 5]);

        $this->actingAs($this->staff())
            ->get(route('products.index', ['low' => 1]))
            ->assertSee('Barang Sedikit')
            ->assertDontSee('Barang Banyak');
    }

    public function test_product_list_is_paginated(): void
    {
        foreach (range(1, 12) as $i) {
            $this->makeProduct(['sku' => "P-{$i}", 'name' => "Produk {$i}"]);
        }

        $this->actingAs($this->staff())
            ->get(route('products.index'))
            ->assertViewHas('products', fn($p) => $p->count() === 10 && $p->total() === 12);
    }

    public function test_sales_can_be_filtered_by_date(): void
    {
        $admin = $this->admin();

        Sale::create([
            'user_id' => $admin->id,
            'sale_number' => 'SL-LAMA',
            'total' => 0,
            'status' => 'completed',
            'sold_at' => '2026-01-10 10:00:00'
        ]);
        Sale::create([
            'user_id' => $admin->id,
            'sale_number' => 'SL-BARU',
            'total' => 0,
            'status' => 'completed',
            'sold_at' => '2026-03-10 10:00:00'
        ]);

        $this->actingAs($admin)
            ->get(route('sales.index', ['date_from' => '2026-03-01']))
            ->assertSee('SL-BARU')
            ->assertDontSee('SL-LAMA');
    }

    // ---------- DASHBOARD ----------

    public function test_dashboard_numbers_come_from_database_and_ignore_cancelled_sales(): void
    {
        $admin = $this->admin();

        Sale::create([
            'user_id' => $admin->id,
            'sale_number' => 'SL-OK',
            'total' => 5000,
            'status' => 'completed',
            'sold_at' => now()
        ]);
        Sale::create([
            'user_id' => $admin->id,
            'sale_number' => 'SL-BATAL',
            'total' => 9000,
            'status' => 'cancelled',
            'sold_at' => now()
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn($s) => $s['sales_count'] === 1
                && (float) $s['sales_today'] === 5000.0);
    }
}