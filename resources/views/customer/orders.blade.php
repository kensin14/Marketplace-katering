<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan saya - {{ config('app.name') }}</title>
    <link href="{{ asset('css/marketplace.css') }}" rel="stylesheet">
</head>
<body>
    <header class="market-header">
        <a class="market-brand" href="{{ route('customer.dashboard') }}"><span class="market-mark">MK</span><span>{{ config('app.name') }}</span></a>
        <nav class="market-nav" aria-label="Navigasi customer">
            <a href="{{ route('customer.dashboard') }}">Dashboard</a>
            <a href="{{ route('customer.catalog') }}">Cari menu</a>
            <a class="active" href="{{ route('customer.orders') }}">Pesanan</a>
            <a href="{{ route('customer.cart') }}">Keranjang ({{ $cartCount }})</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </nav>
    </header>
    <main class="market-main">
        <section class="market-heading"><h1>Pesanan kantor</h1><p>Riwayat dan status pesanan katering Anda.</p></section>
        @if (session('status'))<div class="market-notice" role="status">{{ session('status') }}</div>@endif
        @if ($orders->isNotEmpty())
            <section class="order-list">
                @foreach ($orders as $order)
                    <article class="order-card">
                        <div>
                            <h2>{{ $order->merchant_name }}</h2>
                            <div class="order-meta">
                                <span>{{ $order->invoice_number }}</span>
                                <span>Kirim {{ \Illuminate\Support\Carbon::parse($order->delivery_date)->locale('id')->translatedFormat('j F Y') }}</span>
                                <span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="table-actions">
                            <span class="status-pill {{ $order->status }}">{{ $order->status === 'pending' ? 'Menunggu' : ($order->status === 'accepted' ? 'Diterima' : 'Ditolak') }}</span>
                            <a class="market-button secondary" href="{{ route('customer.orders.invoice', $order->id) }}">Lihat invoice</a>
                        </div>
                    </article>
                @endforeach
            </section>
            @include('partials.pagination', ['paginator' => $orders])
        @else
            <section class="market-panel market-empty">Belum ada pesanan. <a href="{{ route('customer.catalog') }}"><strong>Jelajahi menu katering</strong></a></section>
        @endif
    </main>
</body>
</html>
