@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="card">
        <h2>Tambah Kategori</h2>
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            @include('categories._form')
            <button class="btn" type="submit">Simpan</button>
            <a class="btn btn-gray" href="{{ route('categories.index') }}">Batal</a>
        </form>
    </div>
@endsection