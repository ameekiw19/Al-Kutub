<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Al-Kutub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('./assets/compiled/css/al-kutub-design-system.css') }}">
    @include('partials.design-tokens')
    <style>
        * { box-sizing: border-box; font-family: var(--ak-font-family-primary); }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: var(--ak-color-background);
            color: var(--ak-color-on-surface);
            padding: 20px;
        }
        .card {
            width: 100%;
            max-width: 500px;
            background: var(--ak-color-surface);
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            padding: 28px;
        }
        h1 { margin: 0 0 10px; font-size: 24px; }
        p { margin: 0 0 18px; color: var(--ak-color-on-surface-variant); line-height: 1.6; }
        .field-label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; }
        .field {
            width: 100%;
            height: 46px;
            border: 1px solid var(--ak-color-outline);
            border-radius: 10px;
            padding: 0 12px;
            font-size: 14px;
            margin-bottom: 14px;
        }
        .btn-primary {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 11px 14px;
            font-weight: 600;
            cursor: pointer;
            background: var(--ak-color-primary);
            color: var(--ak-color-on-primary);
        }
        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 14px;
            background: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Reset Password</h1>
        <p>Masukkan password baru untuk akun Anda.</p>

        @if($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="field-label" for="email">Email</label>
            <input id="email" type="email" name="email" class="field" value="{{ old('email', $email) }}" required>

            <label class="field-label" for="password">Password Baru</label>
            <input id="password" type="password" name="password" class="field" minlength="8" required>

            <label class="field-label" for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="field" minlength="8" required>

            <button type="submit" class="btn-primary">Simpan Password Baru</button>
        </form>
    </div>
</body>
</html>
