<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan katering - {{ config('app.name') }}</title>
    <link href="{{ asset('css/marketplace.css') }}" rel="stylesheet">
</head>
<body>
    <header class="market-header">
        <a class="market-brand" href="{{ route('merchant.dashboard') }}"><span class="market-mark">MK</span><span>{{ config('app.name') }}</span></a>
        <nav class="market-nav" aria-label="Navigasi merchant">
            <a href="{{ route('merchant.dashboard') }}">Dashboard</a>
            <a class="active" href="{{ route('merchant.orders.index') }}">Pesanan katering</a>
            <a href="{{ route('merchant.profile.edit') }}">Profil katering</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </nav>
    </header>
    <main class="market-main">
        <section class="market-heading"><h1>Pesanan katering</h1><p>Kelola pesanan yang dibuat oleh kantor customer.</p></section>
        @if (session('status'))<div class="market-notice" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="market-notice error" role="alert">{{ $errors->first() }}</div>@endif
        @if ($orders->isNotEmpty())
            <section class="order-list">
                @foreach ($orders as $order)
                    <article class="order-card">
                        <div>
                            <h2>{{ $order->customer_name }} <span class="status-pill {{ $order->status }}">{{ $order->status === 'pending' ? 'Menunggu' : ($order->status === 'accepted' ? 'Diterima' : 'Ditolak') }}</span></h2>
                            <div class="order-meta">
                                <span>{{ $order->invoice_number }}</span>
                                <span>Kirim {{ \Illuminate\Support\Carbon::parse($order->delivery_date)->locale('id')->translatedFormat('j F Y') }}</span>
                                <span>{{ $order->delivery_address }}</span>
                                <span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if ($order->notes)<p class="muted">Catatan: {{ $order->notes }}</p>@endif
                        </div>
                        <div class="table-actions">
                            <a class="market-button secondary" href="{{ route('merchant.orders.invoice', $order->id) }}">Invoice</a>
                            @if ($order->status === 'pending')
                                <form method="POST" action="{{ route('merchant.orders.status', $order->id) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="accepted">
                                    <button class="market-button" type="submit">Terima</button>
                                </form>
                                <form method="POST" action="{{ route('merchant.orders.status', $order->id) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="market-button danger" type="submit">Tolak</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>
            @include('partials.pagination', ['paginator' => $orders])
        @else
            <section class="market-panel market-empty">Belum ada pesanan masuk.</section>
        @endif
    </main>
</body>
</html>
