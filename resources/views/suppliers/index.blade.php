@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">Supplier</h2>
            <a class="btn" href="{{ route('suppliers.create') }}">+ Tambah</a>
        </div>

        <form method="GET" style="margin-top:16px">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari supplier...">
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>{{ $supplier->email ?? '-' }}</td>
                        <td>
                            <a class="btn btn-warning" href="{{ route('suppliers.edit', $supplier) }}">Edit</a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" style="display:inline"
                                onsubmit="return confirm('Hapus supplier ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px">{{ $suppliers->links() }}</div>
    </div>
@endsection