@extends('layouts.app')

@section('title', 'Ubah Kategori')

@section('content')
    <div class="card">
        <h2>Ubah Kategori</h2>
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('categories._form')
            <button class="btn" type="submit">Perbarui</button>
            <a class="btn btn-gray" href="{{ route('categories.index') }}">Batal</a>
        </form>
    </div>
@endsection