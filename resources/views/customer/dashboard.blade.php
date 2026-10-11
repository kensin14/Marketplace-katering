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
        <a class="market-brand" href="{{ route('customer.dashboard') }}"><span class="market-mark">MK</span><span>{{ config('app.name') }}</span></a>
        <nav class="market-nav" aria-label="Navigasi customer">
            <a class="active" href="{{ route('customer.dashboard') }}">Cari katering</a>
            <a href="{{ route('customer.orders') }}">Pesanan</a>
            <a href="{{ route('customer.cart') }}">Keranjang ({{ $cartCount }})</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </nav>
    </header>
    <main class="market-main">
        <section class="customer-hero">
            <div class="customer-hero-content">
                <span class="customer-eyebrow">MAKAN ENAK, KERJA MAKIN SEMANGAT</span>
                <h1>Pesan makan siang kantor,<br><span>lebih praktis.</span></h1>
                <p>Halo, {{ $customer->company_name ?? $customer->nm_member }}. Temukan menu katering di sekitar Kota Serang.</p>
                <a class="market-button customer-hero-button" href="#menu-kantor">
                    Jelajahi menu <span aria-hidden="true">&rarr;</span>
                </a>
                <div class="customer-hero-meta">
                    <span><span class="hero-meta-icon" aria-hidden="true">⌖</span> Melayani area Kota Serang</span>
                    <span><span class="hero-meta-icon" aria-hidden="true">✓</span> Pesan sesuai kebutuhan kantor</span>
                </div>
            </div>
            <div class="customer-hero-art" aria-hidden="true">
                <div class="hero-art-orbit"></div>
                <div class="hero-art-sun"></div>
                <div class="hero-food-card hero-food-main">
                    <img src="{{ asset('menu-photos/nasi-ayam-bakar.jpg') }}" alt="">
                    <span>Menu pilihan</span>
                    <strong>Lezat untuk tim</strong>
                </div>
                <div class="hero-floating-tag hero-tag-location"><span aria-hidden="true">⌖</span> Kota Serang</div>
                <div class="hero-floating-tag hero-tag-meal"><span aria-hidden="true">✦</span> Fresh &amp; siap saji</div>
            </div>
            <div class="customer-hero-decoration" aria-hidden="true"></div>
        </section>

        <section class="customer-stats" aria-label="Ringkasan menu">
            <div class="customer-stat">
                <span class="customer-stat-icon stat-icon-menu" aria-hidden="true">▤</span>
                <span><strong>{{ $products->count() }}</strong><small>Pilihan menu</small></span>
            </div>
            <div class="customer-stat">
                <span class="customer-stat-icon stat-icon-merchant" aria-hidden="true">⌂</span>
                <span><strong>{{ $merchantCount }}</strong><small>Mitra katering</small></span>
            </div>
            <div class="customer-stat">
                <span class="customer-stat-icon stat-icon-ready" aria-hidden="true">✓</span>
                <span><strong>{{ $availableMenuCount }}</strong><small>Siap dipesan</small></span>
            </div>
            <a class="customer-stat customer-stat-cart" href="{{ route('customer.cart') }}">
                <span class="customer-stat-icon stat-icon-cart" aria-hidden="true">🛒</span>
                <span><strong>{{ $cartCount }}</strong><small>Porsi di keranjang</small></span>
                <span class="stat-arrow" aria-hidden="true">&rarr;</span>
            </a>
        </section>

        <section class="customer-section-heading" id="menu-kantor">
            <div>
                <span class="customer-eyebrow">DARI MITRA KATERING LOKAL</span>
                <h2>Menu untuk hari ini</h2>
                <p>Pilihan masakan rumahan untuk menemani aktivitas kantor.</p>
            </div>
            <a class="customer-all-menus" href="{{ route('customer.catalog') }}">Lihat semua menu <span aria-hidden="true">&rarr;</span></a>
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
