@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
    <div class="card">
        <h2>Tambah Produk</h2>
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            @include('products._form')
            <button class="btn" type="submit">Simpan</button>
            <a class="btn btn-gray" href="{{ route('products.index') }}">Batal</a>
        </form>
    </div>
@endsection