@extends('layouts.app')

@section('title', 'Ubah Supplier')

@section('content')
    <div class="card">
        <h2>Ubah Supplier</h2>
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            @include('suppliers._form')
            <button class="btn" type="submit">Perbarui</button>
            <a class="btn btn-gray" href="{{ route('suppliers.index') }}">Batal</a>
        </form>
    </div>
@endsection