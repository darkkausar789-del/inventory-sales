<label>Nama</label>
<input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required>
@error('name')
<div class="error">{{ $message }}</div> @enderror

<label>Email</label>
<input type="text" name="email" value="{{ old('email', $user->email ?? '') }}" required>
@error('email')
<div class="error">{{ $message }}</div> @enderror

<label>Role</label>
<select name="role" required style="width:100%; padding:10px; margin:6px 0 14px">
    <option value="staff" @selected(old('role', $user->role ?? 'staff') === 'staff')>Staff</option>
    <option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Admin / Owner</option>
</select>
@error('role')
<div class="error">{{ $message }}</div> @enderror

<label>Password @isset($user) <small>(kosongkan jika tidak diubah)</small> @endisset</label>
<input type="password" name="password" @empty($user) required @endempty>
@error('password')
<div class="error">{{ $message }}</div> @enderror

<label>Konfirmasi Password</label>
<input type="password" name="password_confirmation" @empty($user) required @endempty>
<br>