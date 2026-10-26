<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .card {
            background: #fff;
            padding: 32px;
            border-radius: 8px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 6px 0 14px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
        }

        .error {
            color: #dc2626;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Daftar Pelanggan</h2>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif

        <form method="POST" action="{{ route('register.attempt') }}">
            @csrf
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus>

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit">Daftar</button>
        </form>

        <p style="text-align:center; font-size:14px">
            Sudah punya akun? <a href="{{ route('login') }}">Login</a>
        </p>
    </div>
</body>

</html>