<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory & Sales')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f3f4f6;
            color: #111827;
        }

        nav {
            background: #1f2937;
            padding: 12px 24px;
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        nav a {
            color: #e5e7eb;
            text-decoration: none;
        }

        nav a:hover {
            color: #fff;
        }

        nav .spacer {
            flex: 1;
        }

        nav button {
            background: #dc2626;
            color: #fff;
            border: 0;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        main {
            max-width: 960px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .card {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        input[type=text],
        input[type=search] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-warning {
            background: #d97706;
        }

        .btn-danger {
            background: #dc2626;
        }

        .btn-gray {
            background: #6b7280;
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: -8px;
            margin-bottom: 10px;
        }

        .row {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <nav>
        <a href="{{ route('dashboard') }}"><strong>Inventory</strong></a>

        @if (auth()->user()->isCustomer())
            <a href="{{ route('catalog.index') }}">Katalog</a>
            <a href="{{ route('sales.create') }}">Belanja</a>
            <a href="{{ route('sales.index') }}">Pesanan Saya</a>
        @else
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('products.index') }}">Produk</a>
            <a href="{{ route('purchases.index') }}">Pembelian</a>
            <a href="{{ route('sales.index') }}">Penjualan</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('categories.index') }}">Kategori</a>
                <a href="{{ route('suppliers.index') }}">Supplier</a>
                <a href="{{ route('users.index') }}">Pengguna</a>
            @endif
        @endif
        <span class="spacer"></span>
        <span style="color:#9ca3af">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </nav>

    <main>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>

</html>