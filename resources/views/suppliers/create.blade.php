@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
    <div class="card">
        <h2>Tambah Supplier</h2>
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            @include('suppliers._form')
            <button class="btn" type="submit">Simpan</button>
            <a class="btn btn-gray" href="{{ route('suppliers.index') }}">Batal</a>
        </form>
    </div>
@endsection