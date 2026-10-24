@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">Kategori</h2>
            <a class="btn" href="{{ route('categories.create') }}">+ Tambah</a>
        </div>

        <form method="GET" style="margin-top:16px">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kategori...">
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jumlah Produk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td>
                            <a class="btn btn-warning" href="{{ route('categories.edit', $category) }}">Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" style="display:inline"
                                onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px">{{ $categories->links() }}</div>
    </div>
@endsection