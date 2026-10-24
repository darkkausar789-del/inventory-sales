<label>Nama Supplier</label>
<input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required>
@error('name')
<div class="error">{{ $message }}</div> @enderror

<label>Telepon</label>
<input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}">
@error('phone')
<div class="error">{{ $message }}</div> @enderror

<label>Email</label>
<input type="text" name="email" value="{{ old('email', $supplier->email ?? '') }}">
@error('email')
<div class="error">{{ $message }}</div> @enderror
<br>