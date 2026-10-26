<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    private function product(array $o = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Umum']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'C-1',
            'name' => 'Barang Uji',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'minimum_stock' => 2,
        ], $o));
    }

    public function test_register_always_creates_a_customer_even_if_role_is_injected(): void
    {
        $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', // upaya menyuntikkan role
        ]);

        $this->assertSame('customer', User::where('email', 'budi@example.com')->first()->role);
    }

    public function test_customer_cannot_open_internal_pages(): void
    {
        $customer = $this->customer();

        foreach (['products.index', 'purchases.index', 'categories.index', 'suppliers.index', 'users.index'] as $route) {
            $this->actingAs($customer)->get(route($route))->assertForbidden();
        }
    }

    public function test_catalog_hides_out_of_stock_products_and_purchase_price(): void
    {
        $this->product(['sku' => 'ADA-1', 'name' => 'Barang Tersedia', 'stock' => 5, 'purchase_price' => 987654]);
        $this->product(['sku' => 'HBS-1', 'name' => 'Barang Habis', 'stock' => 0]);

        $this->actingAs($this->customer())
            ->get(route('catalog.index'))
            ->assertSee('Barang Tersedia')
            ->assertDontSee('Barang Habis')
            ->assertDontSee('987654');
    }

    public function test_customer_can_buy_multiple_items_and_stock_decreases(): void
    {
        $a = $this->product(['sku' => 'A-1', 'name' => 'A', 'stock' => 10, 'selling_price' => 2000]);
        $b = $this->product(['sku' => 'B-1', 'name' => 'B', 'stock' => 5, 'selling_price' => 3000]);

        $this->actingAs($this->customer())->post(route('sales.store'), [
            'items' => [
                ['product_id' => $a->id, 'quantity' => 2],
                ['product_id' => $b->id, 'quantity' => 1],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(8, $a->fresh()->stock);
        $this->assertSame(4, $b->fresh()->stock);
        $this->assertEquals(7000, Sale::first()->total); // 2x2000 + 1x3000
        $this->assertDatabaseCount('sale_items', 2);
    }

    public function test_out_of_stock_product_cannot_be_bought(): void
    {
        $p = $this->product(['stock' => 0]);

        $this->actingAs($this->customer())->post(route('sales.store'), [
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_deleted_product_cannot_be_bought(): void
    {
        $p = $this->product();
        $p->delete();

        $this->actingAs($this->customer())->post(route('sales.store'), [
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('items.0.product_id');

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_customer_cannot_cancel_an_order_but_owner_can_and_stock_returns(): void
    {
        $customer = $this->customer();
        $p = $this->product(['stock' => 10]);

        $this->actingAs($customer)->post(route('sales.store'), [
            'items' => [['product_id' => $p->id, 'quantity' => 3]],
        ]);
        $sale = Sale::first();
        $this->assertSame(7, $p->fresh()->stock);

        $this->actingAs($customer)->post(route('sales.cancel', $sale))->assertForbidden();
        $this->assertSame('completed', $sale->fresh()->status);

        $owner = User::factory()->create(['role' => 'admin']);
        $this->actingAs($owner)->post(route('sales.cancel', $sale));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertSame(10, $p->fresh()->stock);
    }
}