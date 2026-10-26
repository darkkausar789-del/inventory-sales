@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">Pengguna</h2>
            <a class="btn" href="{{ route('users.create') }}">+ Tambah</a>
        </div>

        <form method="GET" style="margin-top:16px">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama / email...">
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ['admin' => 'Owner', 'staff' => 'Staff', 'customer' => 'Pelanggan'][$user->role] ?? $user->role }}
                        </td>
                        <td>
                            <a class="btn btn-warning" href="{{ route('users.edit', $user) }}">Edit</a>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline"
                                    onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px">{{ $users->links() }}</div>
    </div>
@endsection