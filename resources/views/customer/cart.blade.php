<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang - {{ config('app.name') }}</title>
    <link href="{{ asset('css/marketplace.css') }}" rel="stylesheet">
</head>
<body>
    <header class="market-header">
        <a class="market-brand" href="{{ route('customer.dashboard') }}"><span class="market-mark">MK</span><span>{{ config('app.name') }}</span></a>
        <nav class="market-nav" aria-label="Navigasi customer">
            <a href="{{ route('customer.dashboard') }}">Dashboard</a>
            <a href="{{ route('customer.catalog') }}">Cari menu</a>
            <a href="{{ route('customer.orders') }}">Pesanan</a>
            <a class="active" href="{{ route('customer.cart') }}">Keranjang ({{ $cartCount }})</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </nav>
    </header>
    <main class="market-main">
        <section class="market-heading"><h1>Keranjang pesanan</h1><p>Periksa menu dari satu atau beberapa merchant dan jadwalkan pengiriman untuk kantor Anda. Setiap merchant menerima pesanan dan invoice terpisah.</p></section>
        @if (session('status'))<div class="market-notice" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="market-notice error" role="alert">{{ $errors->first() }}</div>@endif

        @if ($products->isEmpty())
            <section class="market-panel market-empty">
                Keranjang masih kosong. <a href="{{ route('customer.catalog') }}"><strong>Cari menu katering</strong></a>
            </section>
        @else
            @foreach ($merchantGroups as $merchantProducts)
                @php($merchant = $merchantProducts->first())
                <section class="market-panel">
                    <h2>{{ $merchant->merchant_name }}</h2>
                    <p class="muted">{{ $merchant->merchant_address }}</p>
                    <div class="table-scroll">
                        <table class="market-table">
                            <thead><tr><th>Menu</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($merchantProducts as $product)
                                    <tr>
                                        <td>{{ $product->nama_barang }}</td>
                                        <td>Rp {{ number_format((float) $product->harga_jual, 0, ',', '.') }}</td>
                                        <td>{{ $product->quantity }} {{ $product->satuan_barang }}</td>
                                        <td>Rp {{ number_format($product->line_total, 0, ',', '.') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('customer.cart.remove', $product->id) }}">
                                                @csrf @method('DELETE')
                                                <button class="market-button danger" type="submit">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="cart-total"><span>Subtotal merchant</span><span>Rp {{ number_format($merchantProducts->sum('line_total'), 0, ',', '.') }}</span></div>
                </section>
            @endforeach
            <section class="market-panel">
                <div class="cart-total"><span>Total seluruh pesanan</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
            </section>

            <form class="market-panel checkout-grid" method="POST" action="{{ route('customer.checkout') }}">
                @csrf
                <label class="market-field">Tanggal pengiriman
                    <input name="delivery_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('delivery_date') }}" required>
                </label>
                <label class="market-field">Alamat pengiriman
                    <input name="delivery_address" maxlength="1000" value="{{ old('delivery_address', $customer->alamat_member ?? '') }}" required>
                </label>
                <label class="market-field field-wide">Catatan untuk katering
                    <textarea name="notes" maxlength="2000" placeholder="Contoh: antar sebelum jam makan siang">{{ old('notes') }}</textarea>
                </label>
                <div class="field-wide"><button class="market-button" type="submit">Checkout ke {{ $merchantGroups->count() }} merchant</button></div>
            </form>
        @endif
    </main>
</body>
</html>
