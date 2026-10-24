@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">Penjualan</h2>
            <a class="btn" href="{{ route('sales.create') }}">+ Penjualan Baru</a>
        </div>

        <form method="GET" class="row" style="margin-top:16px; justify-content:flex-start">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="No. transaksi..." style="max-width:200px">

            <select name="status" style="padding:10px">
                <option value="">Semua status</option>
                <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding:9px">
            <span>s/d</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="padding:9px">

            <button class="btn" type="submit">Filter</button>
            <a class="btn btn-gray" href="{{ route('sales.index') }}">Reset</a>
        </form>

        @if ($errors->any())
            <div class="alert alert-error" style="margin-top:16px">{{ $errors->first() }}</div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>{{ $sale->sale_number }}</td>
                        <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td>{{ $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</td>
                        <td><a class="btn btn-gray" href="{{ route('sales.show', $sale) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px">{{ $sales->links() }}</div>
    </div>
@endsection