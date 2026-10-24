@extends('layouts.app')

@section('title', 'Penjualan Baru')

@section('content')
    <div class="card">
        <h2>Penjualan Baru</h2>

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('sales.store') }}">
            @csrf

            <label>Item</label>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kuantitas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>

            <p style="font-size:13px; color:#6b7280">
                Harga dan total dihitung otomatis oleh sistem saat disimpan.
            </p>

            <button type="button" class="btn btn-gray" id="add-row" style="margin:4px 0 12px">+ Tambah item</button>
            <br>
            <button class="btn" type="submit">Simpan Penjualan</button>
            <a class="btn btn-gray" href="{{ route('sales.index') }}">Batal</a>
        </form>
    </div>

    <script>
        const products = @json($productOptions);
        const oldItems = @json(old('items', []));
        const initialRows = oldItems.length ? oldItems : [{ product_id: '', quantity: 1 }];

        const body = document.getElementById('items-body');
        let index = 0;

        function addRow(data = {}) {
            const tr = document.createElement('tr');

            const select = document.createElement('select');
            select.name = `items[${index}][product_id]`;
            select.required = true;
            select.style.cssText = 'width:100%; padding:8px';
            select.add(new Option('-- Pilih produk --', ''));
            products.forEach(p => {
                const opt = new Option(p.label, p.id);
                if (String(data.product_id) === String(p.id)) opt.selected = true;
                select.add(opt);
            });

            const qty = document.createElement('input');
            qty.type = 'number'; qty.min = 1; qty.required = true;
            qty.name = `items[${index}][quantity]`;
            qty.value = data.quantity ?? 1;
            qty.style.cssText = 'width:100px; padding:8px';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger';
            remove.textContent = 'X';
            remove.addEventListener('click', () => {
                if (body.children.length > 1) tr.remove();
            });

            [select, qty, remove].forEach(el => {
                const td = document.createElement('td');
                td.appendChild(el);
                tr.appendChild(td);
            });

            body.appendChild(tr);
            index++;
        }

        document.getElementById('add-row').addEventListener('click', () => addRow());
        initialRows.forEach(row => addRow(row));
    </script>
@endsection