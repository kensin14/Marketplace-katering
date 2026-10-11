<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cari katering - {{ config('app.name') }}</title>
    <link href="{{ asset('css/marketplace.css') }}" rel="stylesheet">
</head>
<body>
    <header class="market-header">
        <a class="market-brand" href="{{ route('customer.dashboard') }}">
            <span class="market-mark">MK</span><span>{{ config('app.name') }}</span>
        </a>
        <nav class="market-nav" aria-label="Navigasi customer">
            <a href="{{ route('customer.dashboard') }}">Dashboard</a>
            <a class="active" href="{{ route('customer.catalog') }}">Cari menu</a>
            <a href="{{ route('customer.orders') }}">Pesanan</a>
            <a href="{{ route('customer.cart') }}">Keranjang ({{ $cartCount }})</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </nav>
    </header>
    <main class="market-main">
        <section class="market-heading">
            <h1>Temukan katering untuk kantor</h1>
            <p>Cari menu berdasarkan nama, jenis makanan, atau lokasi katering.</p>
        </section>

        @if (session('status'))
            <div class="market-notice" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="market-notice error" role="alert">{{ $errors->first() }}</div>
        @endif

        @include('customer.partials.catalog-products')
    </main>
</body>
</html>
