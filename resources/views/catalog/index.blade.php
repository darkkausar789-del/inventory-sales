@extends('layouts.app')

@section('title', 'Katalog')

@section('content')
    <div class="card" style="margin-bottom:16px">
        <div class="row">
            <h2 style="margin:0">Katalog Produk</h2>
            <a class="btn" href="{{ route('sales.create') }}">Belanja Sekarang</a>
        </div>

        <form method="GET" class="row" style="margin-top:16px; justify-content:flex-start">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk..." style="max-width:260px">
            <select name="category_id" style="padding:10px">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button class="btn" type="submit">Cari</button>
            <a class="btn btn-gray" href="{{ route('catalog.index') }}">Reset</a>
        </form>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px">
        @forelse ($products as $product)
            <div class="card">
                <div style="color:#6b7280; font-size:13px">{{ $product->category->name }}</div>
                <div style="font-weight:bold; margin:6px 0">{{ $product->name }}</div>
                <div style="font-size:18px">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
                @if ($product->stock <= $product->minimum_stock)
                    <div style="color:#d97706; font-size:13px; margin-top:6px">Stok terbatas</div>
                @else
                    <div style="color:#16a34a; font-size:13px; margin-top:6px">Tersedia</div>
                @endif
            </div>
        @empty
            <div class="card">Tidak ada produk yang tersedia.</div>
        @endforelse
    </div>

    <div style="margin-top:16px">{{ $products->links() }}</div>
@endsection