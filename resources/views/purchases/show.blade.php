@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
    <div class="card">
        <div class="row">
            <h2 style="margin:0">{{ $purchase->purchase_number }}</h2>
            <span>{{ $purchase->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span>
        </div>

        <table>
            <tr>
                <th>Tanggal</th>
                <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Supplier</th>
                <td>{{ $purchase->supplier->name }}</td>
            </tr>
            <tr>
                <th>Petugas</th>
                <td>{{ $purchase->user->name }}</td>
            </tr>
        </table>

        <h3>Item</h3>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga Beli</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align:right">Total</th>
                    <th>Rp {{ number_format($purchase->total, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>

        <br>
        <a class="btn btn-gray" href="{{ route('purchases.index') }}">Kembali</a>

        @if (auth()->user()->isAdmin() && $purchase->status === 'completed')
            <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" style="display:inline"
                onsubmit="return confirm('Batalkan pembelian ini? Stok akan dikurangi kembali.')">
                @csrf
                <button class="btn btn-danger" type="submit">Batalkan Transaksi</button>
            </form>
        @endif
    </div>
@endsection