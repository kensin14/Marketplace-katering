<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - {{ config('app.name') }}</title>
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body class="login-page">
    <main class="auth-page">
        <section class="auth-card" aria-labelledby="login-title">
            <div class="auth-brand">
                <div class="brand-mark" aria-hidden="true">MK</div>
                <h1 id="login-title">{{ config('app.name') }}</h1>
                <p class="auth-description login-instruction">Silahkan login terlebih dahulu.</p>
            </div>

            @if (session('status'))
                <div class="form-message form-message-success" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="form-message form-message-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <label for="user">Username</label>
                <input id="user" type="text" name="user" placeholder="Masukkan username"
                    autocomplete="username" value="{{ old('user') }}" required autofocus>

                <label for="pass">Password</label>
                <input id="pass" type="password" name="pass" placeholder="Masukkan password"
                    autocomplete="current-password" required>

                <button class="auth-button" type="submit">Masuk</button>
            </form>

            <p class="auth-footer">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
        </section>
    </main>
</body>
</html>
