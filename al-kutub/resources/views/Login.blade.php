<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Al-Kutub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/static/images/logo/favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static/images/logo/favicon.png') }}">
    <meta name="theme-color" content="#1B5E3B">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{url('./assets/compiled/css/al-kutub-design-system.css')}}">
    @include('partials.design-tokens')
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

        /* Preloader Styles */
        .auth-preloader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: #efefef;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .auth-preloader.hide {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .auth-preloader-logo {
            width: min(420px, 78vw);
            height: auto;
            opacity: 0;
            transform: scale(0.85);
            animation: splashLogoIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
        }

        .auth-preloader-spinner {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 3px solid rgba(116, 200, 77, 0.25);
            border-top-color: #74c84d;
            animation: spin 0.9s linear infinite;
            opacity: 0;
            animation: spin 0.9s linear infinite, splashFadeIn 0.4s ease 0.6s forwards;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes splashLogoIn {
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes splashFadeIn {
            to { opacity: 1; }
        }

        /* Android Header */
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
            margin: 0;
            font-size: 25px;
            font-weight: 700;
            z-index: 1;
        }

        .header-container > p {
            margin: 4px 0 0;
            font-size: 14px;
            opacity: 0.78;
            z-index: 1;
        }

        /* Overlapping Card */
        .card-container {
            width: 100%;
            max-width: 440px;
            background: var(--android-box-bg);
            border-radius: 28px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            margin-top: -60px;
            z-index: 2;
            border: 1px solid #F0EBE0;
            box-sizing: border-box;
            margin-left: 20px;
            margin-right: 20px;
        }

        .alert {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .alert-error {
            background: var(--android-error-bg);
            border: 1px solid var(--android-error-border);
            color: var(--android-error-text);
        }

        .alert-success {
            background: var(--android-success-bg);
            border: 1px solid var(--android-success-border);
            color: var(--android-success-text);
        }

        .alert-info {
            background: #F0F4FF;
            border: 1px solid #D6E4FF;
            color: #1D4ED8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--android-text-label);
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--android-text-muted);
            transition: 0.3s;
            cursor: pointer;
        }
        
        .input-wrapper i.left-icon {
            left: 16px;
            right: auto;
            cursor: default;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 1px solid var(--android-input-border);
            border-radius: 12px;
            font-size: 15px;
            transition: 0.3s;
            color: var(--android-text-primary);
            background: var(--android-input-bg);
        }
        
        .form-input.no-left-icon {
            padding-left: 16px;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--android-header-start);
            background: var(--android-input-bg);
        }

        .forgot-link {
            display: block;
            text-align: right;
            color: var(--android-header-start);
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--android-header-start), var(--android-header-mid));
            color: white;
            border: none;
            border-radius: 18px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(27, 94, 59, 0.2);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(27, 94, 59, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: var(--android-text-muted);
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--android-divider);
        }

        .divider:not(:empty)::before {
            margin-right: .5em;
        }

        .divider:not(:empty)::after {
            margin-left: .5em;
        }

        .footer-link {
            text-align: center;
            color: #6B5E4E;
            font-size: 14px;
        }

        .footer-link a {
            color: var(--android-header-start);
            text-decoration: none;
            font-weight: 700;
        }

        /* Bismillah Text */
        .bismillah-text {
            margin-top: 30px;
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .bismillah-text h3 {
            font-family: serif;
            color: var(--android-gold);
            font-size: 22px;
            margin: 0;
            font-weight: normal;
        }

        .bismillah-text p {
            color: var(--android-text-muted);
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="auth-preloader" id="authPreloader">
        <img src="{{ asset('assets/static/images/logo/splash-logo.png') }}" alt="Al-Kutub Splash" class="auth-preloader-logo">
        <div class="auth-preloader-spinner"></div>
    </div>

    <!-- Header Section -->
    <div class="header-container">
        <div class="pattern-bg"></div>
        <div class="header-logo-wrapper">
            <div class="header-icon">
                <img src="{{ asset('assets/static/images/logo/al-kutub-symbol.svg') }}" alt="Al-Kutub" style="width: 32px; height: 32px;">
            </div>
            <div class="header-title">
                <h2>Al-Kutub</h2>
                <p>Perpustakaan Kitab Ulama Digital</p>
            </div>
        </div>
        <h1>Selamat Datang</h1>
        <p>Masuk untuk melanjutkan perjalanan ilmu</p>
    </div>

    <!-- Card Section -->
    <div class="card-container">
        @if(session('error'))
        <div class="alert alert-error" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorMessage">{{ session('error') }}</span>
            @if(session('countdown'))
            <div style="margin-top: 10px; width: 100%;">
                <div style="font-size: 12px; margin-bottom: 5px;">Silakan tunggu:</div>
                <div id="countdownTimer" style="font-size: 20px; font-weight: bold; color: var(--android-error-text);">
                    {{ session('countdown') }}s
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(session('message'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('message') }}</span>
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
        @endif

        <form action="{{ route('login.action') }}" method="POST" @if(session('countdown')) style="pointer-events: none; opacity: 0.6;" @endif>
            @csrf
            <div class="form-group">
                <label>Username / Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-user left-icon"></i>
                    <input type="text" name="username" class="form-input" placeholder="contoh@email.com" required @if(session('countdown')) disabled @endif>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 10px;">
                <label>Kata Sandi</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock left-icon"></i>
                    <input type="password" name="password" id="passwordField" class="form-input" placeholder="••••••••" required @if(session('countdown')) disabled @endif>
                    <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                </div>
            </div>
            
            <a href="#" class="forgot-link" onclick="alert('Fitur lupa password akan segera tersedia!'); return false;">Lupa kata sandi?</a>

            <button type="submit" class="btn-submit" @if(session('countdown')) disabled style="opacity: 0.5; background: #8BAD9A; cursor: not-allowed;" @endif>
                Masuk
            </button>

            <div class="divider">atau</div>

            <div class="footer-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </div>
        </form>
    </div>

    <!-- Bismillah Footer Section -->
    <div class="bismillah-text">
        <h3>بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</h3>
        <p>Mulailah belajar dengan nama Allah</p>
    </div>

    <script>
        window.addEventListener('load', function () {
            const preloader = document.getElementById('authPreloader');
            if (!preloader) return;
            setTimeout(function () {
                preloader.classList.add('hide');
                setTimeout(function () {
                    preloader.remove();
                }, 500);
            }, 2500);
        });

        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('passwordField');

        if(togglePassword && passwordField) {
            togglePassword.addEventListener('click', function () {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });
        }
    </script>

    @if(session('countdown'))
    <script>
        // Real-time countdown timer
        (function() {
            var countdownElement = document.getElementById('countdownTimer');
            var errorAlert = document.getElementById('errorAlert');
            var errorMessage = document.getElementById('errorMessage');
            
            @if(session('countdown_timestamp'))
            var targetTime = {{ session('countdown_timestamp') }} * 1000;
            @else
            var targetTime = Date.now() + ({{ session('countdown') }} * 1000);
            @endif
            
            function updateCountdown() {
                var now = Date.now();
                var remaining = Math.max(0, Math.floor((targetTime - now) / 1000));
                
                if (countdownElement) {
                    if (remaining > 0) {
                        if (remaining > 60) {
                            var minutes = Math.floor(remaining / 60);
                            var seconds = remaining % 60;
                            countdownElement.textContent = minutes + 'm ' + seconds + 's';
                        } else {
                            countdownElement.textContent = remaining + 's';
                        }
                    } else {
                        countdownElement.textContent = '✓ Waktu Habis!';
                        countdownElement.style.color = '#2E7D32';
                        
                        if (errorMessage) {
                            errorMessage.textContent = 'Anda sekarang bisa login kembali.';
                            errorAlert.className = 'alert alert-success';
                        }
                        
                        var submitBtn = document.querySelector('.btn-submit');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                            submitBtn.style.background = 'linear-gradient(135deg, var(--android-header-start), var(--android-header-mid))';
                            submitBtn.style.cursor = 'pointer';
                        }
                    }
                }
            }
            
            setInterval(updateCountdown, 1000);
            updateCountdown();
        })();
    </script>
    @endif
</body>
</html>
