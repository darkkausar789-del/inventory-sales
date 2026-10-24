@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h2>Dashboard</h2>
    <p>Halo, <strong>{{ auth()->user()->name }}</strong>.</p>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px">
        <div class="card">
            <div style="color:#6b7280; font-size:14px">Total Produk</div>
            <div style="font-size:28px; font-weight:bold">{{ $stats['total_products'] }}</div>
        </div>
        <div class="card">
            <div style="color:#6b7280; font-size:14px">Penjualan Hari Ini</div>
            <div style="font-size:24px; font-weight:bold">Rp {{ number_format($stats['sales_today'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div style="color:#6b7280; font-size:14px">Penjualan Bulan Ini</div>
            <div style="font-size:24px; font-weight:bold">Rp {{ number_format($stats['sales_month'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div style="color:#6b7280; font-size:14px">Transaksi Penjualan</div>
            <div style="font-size:28px; font-weight:bold">{{ $stats['sales_count'] }}</div>
        </div>
        <div class="card">
            <div style="color:#6b7280; font-size:14px">Transaksi Pembelian</div>
            <div style="font-size:28px; font-weight:bold">{{ $stats['purchases_count'] }}</div>
        </div>
        <div class="card">
            <div style="color:#6b7280; font-size:14px">Produk Stok Rendah</div>
            <div
                style="font-size:28px; font-weight:bold; color:{{ $stats['low_stock_count'] > 0 ? '#dc2626' : '#111827' }}">
                {{ $stats['low_stock_count'] }}
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px">
        <div class="row">
            <h3 style="margin:0">Stok Rendah</h3>
            <a class="btn btn-gray" href="{{ route('products.index', ['low' => 1]) }}">Lihat semua</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Minimum</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lowStockProducts as $product)
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td style="color:#dc2626">{{ $product->stock }}</td>
                        <td>{{ $product->minimum_stock }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Semua stok aman.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="row">
            <h3 style="margin:0">Penjualan Terbaru</h3>
            <a class="btn btn-gray" href="{{ route('sales.index') }}">Lihat semua</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentSales as $sale)
                    <tr>
                        <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->sale_number }}</a></td>
                        <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td>{{ $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada penjualan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection