@extends('layouts.app')

@section('title', 'Ubah Produk')

@section('content')
    <div class="card">
        <h2>Ubah Produk</h2>
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')
            @include('products._form')
            <button class="btn" type="submit">Perbarui</button>
            <a class="btn btn-gray" href="{{ route('products.index') }}">Batal</a>
        </form>
    </div>
@endsection