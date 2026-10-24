@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
    <div class="card">
        <h2>Tambah Pengguna</h2>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('users._form')
            <button class="btn" type="submit">Simpan</button>
            <a class="btn btn-gray" href="{{ route('users.index') }}">Batal</a>
        </form>
    </div>
@endsection