@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
    <div class="card">
        <h2>Ubah Pengguna</h2>
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('users._form')
            <button class="btn" type="submit">Perbarui</button>
            <a class="btn btn-gray" href="{{ route('users.index') }}">Batal</a>
        </form>
    </div>
@endsection