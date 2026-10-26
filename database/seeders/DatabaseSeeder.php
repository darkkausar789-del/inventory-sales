<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo (password: password)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Owner', 'password' => 'password', 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff Gudang', 'password' => 'password', 'role' => 'staff']
        );
        
        User::firstOrCreate(
            ['email' => 'pelanggan@example.com'],
            ['name' => 'Pelanggan Demo', 'password' => 'password', 'role' => 'customer']
        );

        // Kategori
        $minuman = Category::firstOrCreate(['name' => 'Minuman']);
        $makanan = Category::firstOrCreate(['name' => 'Makanan']);
        $atk = Category::firstOrCreate(['name' => 'Alat Tulis']);

        // Supplier
        Supplier::firstOrCreate(
            ['name' => 'PT Sumber Makmur'],
            ['phone' => '081234567890', 'email' => 'sales@sumbermakmur.test']
        );
        Supplier::firstOrCreate(
            ['name' => 'CV Berkah Jaya'],
            ['phone' => '082233445566', 'email' => 'order@berkahjaya.test']
        );

        // Produk
        $products = [
            ['MNM-001', 'Air Mineral 600ml', $minuman->id, 2000, 3500, 100, 20],
            ['MNM-002', 'Teh Botol 350ml', $minuman->id, 3500, 5000, 80, 20],
            ['MKN-001', 'Mie Instan Goreng', $makanan->id, 2500, 3500, 150, 30],
            ['MKN-002', 'Biskuit Cokelat', $makanan->id, 6000, 8500, 5, 10],
            ['ATK-001', 'Pulpen Hitam', $atk->id, 1500, 2500, 200, 50],
            ['ATK-002', 'Buku Tulis 38 Lembar', $atk->id, 3000, 4500, 8, 15],
        ];

        foreach ($products as [$sku, $name, $categoryId, $buy, $sell, $stock, $min]) {
            Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'category_id' => $categoryId,
                    'purchase_price' => $buy,
                    'selling_price' => $sell,
                    'stock' => $stock,
                    'minimum_stock' => $min,
                ]
            );
        }
    }
}