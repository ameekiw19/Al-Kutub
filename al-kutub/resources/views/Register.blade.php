<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Al-Kutub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/static/images/logo/favicon.svg') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/static/images/logo/favicon-dark.svg') }}" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" href="{{ asset('assets/static/images/logo/favicon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static/images/logo/favicon-dark.png') }}" media="(prefers-color-scheme: dark)">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/static/images/logo/favicon-192.png') }}">
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
            position: relative;
            z-index: 1;
        }

        .auth-preloader-surface {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            isolation: isolate;
        }

        .auth-preloader-surface::before {
            content: "";
            position: absolute;
            inset: -44px -72px;
            background: radial-gradient(ellipse at center,
                rgba(250, 248, 242, 0.86) 0%,
                rgba(250, 248, 242, 0.36) 38%,
                rgba(250, 248, 242, 0.1) 58%,
                rgba(250, 248, 242, 0) 78%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .auth-preloader-logo-dark {
            display: none;
        }

        .auth-preloader-spinner {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 3px solid rgba(116, 200, 77, 0.25);
            border-top-color: #74c84d;
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

        @media (prefers-color-scheme: dark) {
            .auth-preloader {
                background: #101611;
            }

            .auth-preloader-surface::before {
                opacity: 1;
            }

            .auth-preloader-logo-light {
                display: none;
            }

            .auth-preloader-logo-dark {
                display: block;
            }

            .auth-preloader-logo {
                filter: drop-shadow(0 10px 28px rgba(0, 0, 0, 0.24));
            }

            .auth-preloader-spinner {
                border-color: rgba(126, 217, 87, 0.24);
                border-top-color: #7ed957;
            }
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
            max-width: 480px;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 500px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--android-text-label);
            font-weight: 600;
            font-size: 13px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.left-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--android-text-muted);
            transition: 0.3s;
            font-size: 14px;
        }
        
        .input-wrapper textarea ~ i.left-icon {
            top: 22px;
            transform: none;
        }

        .form-input {
            width: 100%;
            padding: 14px 14px 14px 40px;
            border: 1px solid var(--android-input-border);
            border-radius: 12px;
            font-size: 14px;
            transition: 0.3s;
            color: var(--android-text-primary);
            background: var(--android-input-bg);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--android-header-start);
            background: var(--android-input-bg);
        }

        textarea.form-input {
            resize: vertical;
            min-height: 80px;
        }

        .char-counter {
            text-align: right;
            font-size: 11px;
            color: var(--android-text-muted);
            margin-top: 4px;
        }

        .terms {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 25px;
            font-size: 13px;
            color: var(--android-text-muted);
        }

        .terms input {
            accent-color: var(--android-header-start);
            width: 16px;
            height: 16px;
            margin-top: 2px;
        }

        .terms a {
            color: var(--android-header-start);
            text-decoration: none;
            font-weight: 600;
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
            margin-bottom: 40px;
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
        <div class="auth-preloader-surface">
            <img src="{{ asset('assets/static/images/logo/splash-logo.png') }}" alt="Al-Kutub Splash" class="auth-preloader-logo auth-preloader-logo-light">
            <img src="{{ asset('assets/static/images/logo/splash-logo-dark.png') }}" alt="Al-Kutub Splash Dark" class="auth-preloader-logo auth-preloader-logo-dark" aria-hidden="true">
        </div>
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
        <h1>Buat Akun Baru</h1>
        <p>Silakan isi data diri Anda untuk mendaftar.</p>
    </div>

    <!-- Card Section -->
    <div class="card-container">
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

        <form action="{{ route('register.action') }}" method="POST">
            @csrf
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user left-icon"></i>
                        <input type="text" name="username" class="form-input" placeholder="Buat username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nomor HP</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone left-icon"></i>
                        <input type="text" name="phone" class="form-input" placeholder="08xxx" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope left-icon"></i>
                    <input type="email" name="email" class="form-input" placeholder="alamat@email.com" required>
                </div>
            </div>

            <div class="form-group">
                <label>Kata Sandi</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock left-icon"></i>
                    <input type="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required>
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi Diri (Opsional)</label>
                <div class="input-wrapper">
                    <i class="fas fa-edit left-icon" style="top: 22px;"></i>
                    <textarea name="description" id="description" class="form-input" placeholder="Ceritakan sedikit tentang Anda..."></textarea>
                </div>
                <div id="charCounter" class="char-counter">0/50 karakter</div>
            </div>

            <div class="terms">
                <input type="checkbox" id="agreeTerms" required>
                <label for="agreeTerms">Saya setuju dengan <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a>.</label>
            </div>

            <button type="submit" class="btn-submit">
                Daftar Sekarang
            </button>

            <div class="divider">atau</div>

            <div class="footer-link">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk Disini</a>
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

        // Textarea Char Counter
        const descInput = document.getElementById('description');
        const counter = document.getElementById('charCounter');
        
        if(descInput){
            descInput.addEventListener('input', function() {
                counter.innerText = this.value.length + '/50 karakter';
                if(this.value.length < 50) {
                    counter.style.color = '#C62828'; // Android Error Red
                } else {
                    counter.style.color = 'var(--android-text-muted)';
                }
            });
        }
    </script>
</body>
</html>
