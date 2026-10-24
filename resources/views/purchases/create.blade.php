@extends('layouts.app')

@section('title', 'Pembelian Baru')

@section('content')
    <div class="card">
        <h2>Pembelian Baru</h2>

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf

            <label>Supplier</label>
            <select name="supplier_id" required style="width:100%; padding:10px; margin:6px 0 14px">
                <option value="">-- Pilih supplier --</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>

            <label>Item</label>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kuantitas</th>
                        <th>Harga Beli (satuan)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>

            <button type="button" class="btn btn-gray" id="add-row" style="margin:12px 0">+ Tambah item</button>
            <br>
            <button class="btn" type="submit">Simpan Pembelian</button>
            <a class="btn btn-gray" href="{{ route('purchases.index') }}">Batal</a>
        </form>
    </div>

    <script>
        const products = @json($productOptions);
        const oldItems = @json(old('items', []));
        const initialRows = oldItems.length ? oldItems : [{ product_id: '', quantity: 1, unit_cost: '' }];
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
            qty.style.cssText = 'width:90px; padding:8px';

            const cost = document.createElement('input');
            cost.type = 'number'; cost.min = 0; cost.step = '0.01'; cost.required = true;
            cost.name = `items[${index}][unit_cost]`;
            cost.value = data.unit_cost ?? '';
            cost.style.cssText = 'width:140px; padding:8px';

            // isi harga beli otomatis dari data produk saat dipilih
            select.addEventListener('change', () => {
                const p = products.find(x => String(x.id) === select.value);
                if (p && cost.value === '') cost.value = p.cost;
            });

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger';
            remove.textContent = 'X';
            remove.addEventListener('click', () => {
                if (body.children.length > 1) tr.remove();
            });

            [select, qty, cost, remove].forEach(el => {
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