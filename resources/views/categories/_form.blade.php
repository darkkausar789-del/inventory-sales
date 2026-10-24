<label>Nama Kategori</label>
<input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required>
@error('name')
    <div class="error">{{ $message }}</div>
@enderror
<br>