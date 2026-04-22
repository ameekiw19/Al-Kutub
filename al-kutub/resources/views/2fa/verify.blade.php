<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2FA - Al-Kutub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --android-bg: #FAFAF5;
            --android-header-start: #1B5E3B;
            --android-header-mid: #2D7A52;
            --android-header-end: #1A4A30;
            --android-gold: #C8A951;
            --android-box-bg: #FFFFFF;
            --android-input-bg: #F8F5EF;
            --android-input-border: #E8E3D5;
            --android-text-primary: #1A2E1A;
            --android-text-muted: #8B8070;
            --android-text-label: #3D2C1E;
            --android-divider: #E8E3D5;
            --android-error-bg: #FFF3F3;
            --android-error-border: #FFCDD2;
            --android-error-text: #C62828;
            --android-success-bg: #F4FBF7;
            --android-success-border: #C8E6C9;
            --android-success-text: #2E7D32;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--android-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header-container {
            width: 100%;
            padding: 40px 20px 100px;
            background: linear-gradient(135deg, var(--android-header-start), var(--android-header-mid), var(--android-header-end));
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: white;
            box-shadow: 0 4px 15px rgba(27, 94, 59, 0.3);
        }

        .pattern-bg {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(var(--android-gold) 1.5px, transparent 1.5px);
            background-size: 40px 40px;
            opacity: 0.1;
            z-index: 0;
        }

        .header-logo-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            z-index: 1;
        }

        .header-icon {
            width: 54px;
            height: 54px;
            background: rgba(200, 169, 81, 0.2);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-icon i {
            color: var(--android-gold);
            font-size: 28px;
        }

        .header-title {
            text-align: left;
        }

        .header-title h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
        }

        .header-title p {
            margin: 0;
            font-size: 12px;
            opacity: 0.72;
        }

        .header-container h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            z-index: 1;
        }

        .header-container p.subtitle {
            font-size: 13px;
            opacity: 0.85;
            max-width: 320px;
            line-height: 1.5;
            z-index: 1;
        }

        .form-wrapper {
            background: var(--android-box-bg);
            width: 90%;
            max-width: 420px;
            border-radius: 28px;
            padding: 32px 28px;
            margin-top: -65px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            z-index: 2;
            border: 1px solid var(--android-input-border);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background-color: var(--android-error-bg);
            border: 1px solid var(--android-error-border);
            color: var(--android-error-text);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--android-text-label);
            margin-bottom: 8px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--android-text-muted);
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px 16px 48px;
            background: var(--android-input-bg);
            border: 1.5px solid var(--android-input-border);
            border-radius: 18px;
            font-size: 24px;
            letter-spacing: 8px;
            font-weight: 700;
            color: var(--android-text-primary);
            transition: all 0.3s ease;
            text-align: center;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--android-header-mid);
            background: var(--android-box-bg);
            box-shadow: 0 0 0 4px rgba(45, 122, 82, 0.1);
        }

        .form-control::placeholder {
            color: var(--android-text-muted);
            opacity: 0.5;
            font-weight: 400;
            letter-spacing: normal;
            font-size: 15px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--android-header-start), var(--android-header-mid));
            color: white;
            border: none;
            border-radius: 18px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(27, 94, 59, 0.2);
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(27, 94, 59, 0.3);
            background: linear-gradient(135deg, var(--android-header-mid), var(--android-header-end));
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: var(--android-text-muted);
            font-size: 13px;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: var(--android-header-mid);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="pattern-bg"></div>
        <div class="header-logo-wrapper">
            <div class="header-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="header-title">
                <h2>Al-Kutub</h2>
                <p>Otentikasi Dua Langkah</p>
            </div>
        </div>
        <h1>Verifikasi Keamanan</h1>
        <p class="subtitle">Silakan masukkan kode 6 digit dari aplikasi authenticator Anda untuk melanjutkan.</p>
    </div>

    <div class="form-wrapper">
        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('2fa.verify.post') }}" method="POST" id="2fa-form">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Kode Verifikasi</label>
                <div class="input-group">
                    <i class="fas fa-key input-icon"></i>
                    <input type="text" 
                           class="form-control" 
                           id="code" 
                           name="code" 
                           placeholder="000000"
                           maxlength="8" 
                           autocomplete="one-time-code"
                           autofocus>
                </div>
                <div style="text-align:center; font-size:11px; color:var(--android-text-muted); margin-top:8px;">
                    Masukkan 6-digit kode atau 8-digit backup code
                </div>
            </div>

            <button type="submit" class="submit-btn" id="btnSubmit">
                Konfirmasi Kode
            </button>
        </form>

        <a href="{{ url('/login') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
    </div>

    <script>
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9A-Z]/ig, '').toUpperCase();
            
            if (this.value.length === 6 || this.value.length === 8) {
                document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memverifikasi...';
                document.getElementById('2fa-form').submit();
            }
        });

        document.getElementById('code').focus();

        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>
