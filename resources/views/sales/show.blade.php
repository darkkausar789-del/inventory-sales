@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">{{ $sale->sale_number }}</h2>
            <span>{{ $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span>
        </div>

        <table>
            <tr>
                <th>Tanggal</th>
                <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Petugas</th>
                <td>{{ $sale->user->name }}</td>
            </tr>
        </table>

        <h3>Item</h3>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align:right">Total</th>
                    <th>Rp {{ number_format($sale->total, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>

        <br>
        <a class="btn btn-gray" href="{{ route('sales.index') }}">Kembali</a>

        @if (auth()->user()->isAdmin() && $sale->status === 'completed')
            <form method="POST" action="{{ route('sales.cancel', $sale) }}" style="display:inline"
                onsubmit="return confirm('Batalkan penjualan ini? Stok akan dikembalikan.')">
                @csrf
                <button class="btn btn-danger" type="submit">Batalkan Transaksi</button>
            </form>
        @endif
    </div>
@endsection