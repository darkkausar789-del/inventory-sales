@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
    <div class="card">
        <h2>{{ $product->name }}</h2>
        <table>
            <tr>
                <th>SKU</th>
                <td>{{ $product->sku }}</td>
            </tr>
            <tr>
                <th>Kategori</th>
                <td>{{ $product->category->name }}</td>
            </tr>
            <tr>
                <th>Harga Beli</th>
                <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Harga Jual</th>
                <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Stok</th>
                <td>{{ $product->stock }}</td>
            </tr>
            <tr>
                <th>Stok Minimum</th>
                <td>{{ $product->minimum_stock }}</td>
            </tr>
        </table>
        <br>
        <a class="btn btn-gray" href="{{ route('products.index') }}">Kembali</a>
    </div>
@endsection