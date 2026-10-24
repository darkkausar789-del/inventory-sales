<label>SKU</label>
<input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
@error('sku')
<div class="error">{{ $message }}</div> @enderror

<label>Nama Produk</label>
<input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required>
@error('name')
<div class="error">{{ $message }}</div> @enderror

<label>Kategori</label>
<select name="category_id" required style="width:100%; padding:10px; margin:6px 0 14px">
    <option value="">-- Pilih kategori --</option>
    @foreach ($categories as $category)
        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>
            {{ $category->name }}
        </option>
    @endforeach
</select>
@error('category_id')
<div class="error">{{ $message }}</div> @enderror

<label>Harga Beli</label>
<input type="text" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? '') }}" required>
@error('purchase_price')
<div class="error">{{ $message }}</div> @enderror

<label>Harga Jual</label>
<input type="text" name="selling_price" value="{{ old('selling_price', $product->selling_price ?? '') }}" required>
@error('selling_price')
<div class="error">{{ $message }}</div> @enderror

@if (!isset($product))
    <label>Stok Awal</label>
    <input type="text" name="stock" value="{{ old('stock', 0) }}" required>
    @error('stock')
    <div class="error">{{ $message }}</div> @enderror
@else
    <label>Stok Saat Ini</label>
    <input type="text" value="{{ $product->stock }}" disabled>
    <div style="font-size:12px; color:#6b7280; margin:-8px 0 14px">
        Stok berubah otomatis lewat transaksi pembelian dan penjualan.
    </div>
@endif

<label>Stok Minimum</label>
<input type="text" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" required>
@error('minimum_stock')
<div class="error">{{ $message }}</div> @enderror
<br>