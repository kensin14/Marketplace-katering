<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - {{ config('app.name') }}</title>
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card" aria-labelledby="register-title">
            <div class="auth-brand">
                <div class="brand-mark" aria-hidden="true">MK</div>
                <p class="eyebrow">MULAI SEKARANG</p>
                <h1 id="register-title">Buat akun</h1>
                <p class="auth-description">Daftar untuk mulai menggunakan {{ config('app.name') }}.</p>
            </div>

            @if ($errors->any())
                <div class="form-message form-message-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <label for="name">Nama kantor atau perusahaan katering</label>
                <input id="name" type="text" name="name" placeholder="Nama kantor / katering" maxlength="255"
                    autocomplete="name" value="{{ old('name') }}" required autofocus>

                <label for="user">Username</label>
                <input id="user" type="text" name="user" placeholder="Buat username" maxlength="255"
                    autocomplete="username" value="{{ old('user') }}" required>

                <label for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="nama@email.com" maxlength="255"
                    autocomplete="email" value="{{ old('email') }}" required>

                <label for="telepon">Nomor kontak</label>
                <input id="telepon" type="tel" name="telepon" placeholder="Nomor telepon/WhatsApp" maxlength="255"
                    autocomplete="tel" value="{{ old('telepon') }}">

                <label for="description">Deskripsi katering (khusus merchant)</label>
                <textarea id="description" name="description" rows="3" maxlength="2000"
                    placeholder="Ceritakan layanan katering Anda">{{ old('description') }}</textarea>

                <label for="alamat_member">Alamat</label>
                <textarea id="alamat_member" name="alamat_member" rows="3" maxlength="1000"
                    autocomplete="street-address" required>{{ old('alamat_member') }}</textarea>

                <label for="role">Daftar sebagai</label>
                <select id="role" name="role" required>
                    <option value="">Pilih jenis akun</option>
                    <option value="merchant" @selected(old('role') === 'merchant')>Katering Merchant</option>
                    <option value="customer" @selected(old('role') === 'customer')>Kantor Customer</option>
                </select>

                <label for="pass">Password</label>
                <input id="pass" type="password" name="pass" placeholder="Minimal 6 karakter"
                    minlength="6" autocomplete="new-password" required>

                <label for="confirm_pass">Konfirmasi password</label>
                <input id="confirm_pass" type="password" name="confirm_pass" placeholder="Ulangi password"
                    minlength="6" autocomplete="new-password" required>

                <button class="auth-button" type="submit">Daftar</button>
            </form>

            <p class="auth-footer">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        </section>
    </main>
</body>
</html>
