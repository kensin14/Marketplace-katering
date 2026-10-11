<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }} - {{ config('app.name') }}</title>
    <link href="{{ asset('css/marketplace.css') }}" rel="stylesheet">
</head>
<body>
    <header class="market-header">
        <a class="market-brand" href="{{ session('member_id') == $invoice->merchant_id ? route('merchant.dashboard') : route('customer.dashboard') }}">
            <span class="market-mark">MK</span><span>{{ config('app.name') }}</span>
        </a>
        <nav class="market-nav" aria-label="Navigasi invoice">
            @if (session('member_id') == $invoice->customer_id)
                <a href="{{ route('customer.dashboard') }}">Dashboard</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </nav>
    </header>
    <main class="market-main">
        @if (session('status'))<div class="market-notice" role="status">{{ session('status') }}</div>@endif
        <div class="invoice-actions">
            <a class="market-button secondary" href="{{ session('member_id') == $invoice->merchant_id ? route('merchant.orders.index') : route('customer.orders') }}">Kembali ke pesanan</a>
            <button class="market-button" type="button" onclick="window.print()">Cetak invoice</button>
        </div>
        <article class="invoice-card">
            <div class="invoice-top">
                <div>
                    <h1>Invoice katering</h1>
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <p>Tanggal invoice: {{ $generatedAt->locale('id')->translatedFormat('j F Y, H:i') }}</p>
                </div>
                <span class="status-pill {{ $invoice->status }}">{{ $invoice->status === 'pending' ? 'Menunggu' : ($invoice->status === 'accepted' ? 'Diterima' : 'Ditolak') }}</span>
            </div>
            <section class="invoice-details">
                <div>
                    <h2>Katering Merchant</h2>
                    <p><strong>{{ $invoice->merchant_name }}</strong></p>
                    <p>{{ $invoice->merchant_address }}</p>
                    <p>{{ $invoice->merchant_email }}</p>
                    <p>{{ $invoice->merchant_phone }}</p>
                </div>
                <div>
                    <h2>Kantor Customer</h2>
                    <p><strong>{{ $invoice->customer_name }}</strong></p>
                    <p>{{ $invoice->delivery_address }}</p>
                    <p>{{ $invoice->customer_email }}</p>
                    <p>Tanggal pengiriman: {{ \Illuminate\Support\Carbon::parse($invoice->delivery_date)->locale('id')->translatedFormat('j F Y') }}</p>
                </div>
            </section>
            <div class="table-scroll">
                <table class="market-table">
                    <thead><tr><th>Menu</th><th>Harga satuan</th><th>Jumlah</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($invoice->notes)<p class="muted">Catatan: {{ $invoice->notes }}</p>@endif
            <div class="invoice-total">Total: Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</div>
        </article>
    </main>
</body>
</html>
