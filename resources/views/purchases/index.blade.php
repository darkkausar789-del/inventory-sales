@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">Pembelian (Stock-in)</h2>
            <a class="btn" href="{{ route('purchases.create') }}">+ Pembelian Baru</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-error" style="margin-top:16px">{{ $errors->first() }}</div>
        @endif

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
            <a class="btn btn-gray" href="{{ route('purchases.index') }}">Reset</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Petugas</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_number }}</td>
                        <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->user->name }}</td>
                        <td>Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                        <td>{{ $purchase->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</td>
                        <td><a class="btn btn-gray" href="{{ route('purchases.show', $purchase) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px">{{ $purchases->links() }}</div>
    </div>
@endsection