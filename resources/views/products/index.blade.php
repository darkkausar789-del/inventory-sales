@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">Produk</h2>
            @if (auth()->user()->isAdmin())
                <a class="btn" href="{{ route('products.create') }}">+ Tambah</a>
            @endif
        </div>

        <form method="GET" class="row" style="margin-top:16px; justify-content:flex-start">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari SKU / nama..."
                style="max-width:260px">

            <select name="category_id" style="padding:10px">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <label>
                <input type="checkbox" name="low" value="1" @checked(request()->boolean('low'))>
                Stok rendah
            </label>

            <button class="btn" type="submit">Filter</button>
            <a class="btn btn-gray" href="{{ route('products.index') }}">Reset</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        <td>
                            {{ $product->stock }}
                            @if ($product->stock <= $product->minimum_stock)
                                <span style="color:#dc2626; font-size:12px">(rendah)</span>
                            @endif
                        </td>
                        <td>
                            <a class="btn btn-gray" href="{{ route('products.show', $product) }}">Detail</a>
                            @if (auth()->user()->isAdmin())
                                <a class="btn btn-warning" href="{{ route('products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" style="display:inline"
                                    onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px">{{ $products->links() }}</div>
    </div>
@endsection