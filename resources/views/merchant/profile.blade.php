<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil katering - {{ config('app.name') }}</title>
    <link href="{{ asset('css/marketplace.css') }}" rel="stylesheet">
</head>
<body>
    <header class="market-header">
        <a class="market-brand" href="{{ route('merchant.dashboard') }}">
            <span class="market-mark">MK</span><span>{{ config('app.name') }}</span>
        </a>
        <nav class="market-nav" aria-label="Navigasi merchant">
            <a href="{{ route('merchant.dashboard') }}">Dashboard</a>
            <a href="{{ route('merchant.orders.index') }}">Pesanan</a>
            <a class="active" href="{{ route('merchant.profile.edit') }}">Profil katering</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </nav>
    </header>
    <main class="market-main">
        <section class="market-heading">
            <h1>Profil Katering Merchant</h1>
            <p>Informasi profil ini akan membantu kantor mengenal katering Anda.</p>
        </section>
        @if (session('status'))<div class="market-notice" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="market-notice error" role="alert">{{ $errors->first() }}</div>@endif
        <form class="market-panel checkout-grid" method="POST" action="{{ route('merchant.profile.update') }}">
            @csrf
            @method('PUT')
            <label class="market-field field-wide">Nama perusahaan katering
                <input name="company_name" maxlength="255" value="{{ old('company_name', $merchant->company_name ?: $merchant->nm_member) }}" required>
            </label>
            <label class="market-field">Email kontak
                <input name="email" type="email" maxlength="255" value="{{ old('email', $merchant->email) }}" required>
            </label>
            <label class="market-field">Telepon / WhatsApp
                <input name="telepon" type="tel" maxlength="255" value="{{ old('telepon', $merchant->telepon) }}" required>
            </label>
            <label class="market-field field-wide">Alamat katering
                <textarea name="alamat_member" rows="3" maxlength="1000" required>{{ old('alamat_member', $merchant->alamat_member) }}</textarea>
            </label>
            <label class="market-field field-wide">Deskripsi katering
                <textarea name="description" rows="5" maxlength="2000" required>{{ old('description', $merchant->description) }}</textarea>
            </label>
            <div class="field-wide"><button class="market-button" type="submit">Simpan profil</button></div>
        </form>
    </main>
</body>
</html>
