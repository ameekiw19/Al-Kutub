<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Al-Kutub</title>
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
            max-width: 520px;
            background: var(--ak-color-surface);
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            padding: 28px;
        }
        h1 { margin: 0 0 10px; font-size: 24px; }
        p { margin: 0 0 16px; color: var(--ak-color-on-surface-variant); line-height: 1.6; }
        .muted { font-size: 14px; color: var(--ak-color-on-surface-variant); margin-bottom: 18px; }
        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 14px;
        }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-info { background: #dbeafe; color: #1e40af; }
        .alert-error { background: #fee2e2; color: #b91c1c; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 11px 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary {
            background: var(--ak-color-primary);
            color: var(--ak-color-on-primary);
        }
        .btn-secondary {
            background: var(--ak-color-surface-variant);
            color: var(--ak-color-on-surface);
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Verifikasi Email</h1>
        <p>Kami sudah mengirim link verifikasi ke email Anda. Silakan cek inbox atau folder spam.</p>
        @if(!empty($email))
            <p class="muted">Email tujuan: <strong>{{ $email }}</strong></p>
        @endif

        @if(session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('verification.resend') }}" method="POST">
            @csrf
            <input type="hidden" name="verification_token" value="{{ $verificationToken }}">
            <button type="submit" class="btn btn-primary" @if(empty($verificationToken)) disabled @endif>
                Kirim Ulang Link Verifikasi
            </button>
        </form>

        @if(empty($verificationToken))
            <p class="muted">Token verifikasi tidak tersedia. Silakan login ulang untuk mendapatkan token baru.</p>
        @endif

        <div class="actions">
            <a href="{{ route('login') }}" class="btn btn-secondary">Kembali ke Login</a>
            <a href="{{ route('register') }}" class="btn btn-secondary">Daftar Ulang</a>
        </div>
    </div>
</body>
</html>
