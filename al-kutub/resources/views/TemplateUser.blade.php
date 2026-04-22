<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Al-Kutub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/static/images/logo/favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static/images/logo/favicon.png') }}">
    <meta name="theme-color" content="#1B5E3B">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           Android Design System - Al-Kutub Web
           Ref: HomeScreen.kt, LoginScreen.kt colors
           ============================================ */
        :root {
            /* Primary Palette (Android Classic) */
            --primary-color: rgb(27, 94, 59);
            --primary-dark: rgb(26, 74, 48);
            --primary-light: rgb(45, 122, 82);
            --accent-color: rgb(200, 169, 81);

            /* Surface & Background */
            --background-color: rgb(250, 250, 245);
            --card-bg: rgb(255, 255, 255);
            --secondary-color: rgb(248, 245, 239);
            --input-bg: rgb(248, 245, 239);

            /* Text */
            --text-color: rgb(26, 46, 26);
            --text-secondary: rgb(107, 94, 78);
            --light-text: rgb(139, 128, 112);

            /* Borders & Dividers */
            --border-color: rgb(232, 227, 213);
            --card-border: rgb(240, 235, 224);

            /* Functional */
            --error-color: rgb(198, 40, 40);
            --success-color: rgb(46, 125, 50);

            /* Spacing (Android dp mapped to px) */
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 18px;
            --radius-xl: 24px;
            --radius-xxl: 28px;
        }

        /* Dark Mode */
        body.dark-mode {
            --primary-color: rgb(45, 122, 82);
            --primary-dark: rgb(27, 94, 59);
            --primary-light: rgb(61, 155, 106);
            --background-color: rgb(18, 18, 18);
            --card-bg: rgb(30, 30, 30);
            --secondary-color: rgb(26, 26, 26);
            --input-bg: rgb(42, 42, 42);
            --text-color: rgb(240, 235, 224);
            --text-secondary: rgb(176, 168, 152);
            --light-text: rgb(139, 139, 139);
            --border-color: rgb(51, 51, 51);
            --card-border: rgb(42, 42, 42);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
        }

        /* ============================================
           PRELOADER (kept identical)
           ============================================ */
        .app-preloader {
            position: fixed;
            inset: 0;
            background: #efefef;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .app-preloader.hide {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .app-preloader-logo {
            width: min(460px, 80vw);
            height: auto;
            opacity: 0;
            transform: scale(0.85);
            animation: splashLogoIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
        }

        .app-preloader-spinner {
            width: 38px;
            height: 38px;
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

        /* ============================================
           NAVBAR — Android-style Top Bar
           Ref: HomeScreen.kt Header colors
           ============================================ */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light), var(--primary-dark));
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(27, 94, 59, 0.25);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 64px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: rgba(200, 169, 81, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon i {
            color: var(--accent-color);
            font-size: 20px;
        }

        .logo-text {
            color: white;
        }

        .logo-text h2 {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
            margin: 0;
        }

        .logo-text p {
            font-size: 10px;
            opacity: 0.72;
            margin: 0;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 6px;
        }

        .nav-link {
            text-decoration: none;
            color: rgba(255,255,255,0.78);
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            position: relative;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.12);
        }

        .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.18);
        }

        .nav-link i.nav-icon {
            margin-right: 6px;
            font-size: 12px;
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .theme-toggle {
            cursor: pointer;
            font-size: 1rem;
            color: rgba(255,255,255,0.7);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.3s;
        }

        .theme-toggle:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .notif-link {
            font-size: 1rem;
            color: rgba(255,255,255,0.7);
            position: relative;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.3s;
            text-decoration: none;
        }

        .notif-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        /* Search Box - Android style translucent */
        .search-box {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: var(--radius-lg);
            padding: 7px 14px;
            position: relative;
            transition: 0.3s;
        }

        .search-box:focus-within {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.3);
        }

        .search-box i {
            color: rgba(255,255,255,0.62);
            font-size: 13px;
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 160px;
            font-family: 'Poppins', sans-serif;
            color: white;
            font-size: 13px;
            margin-left: 8px;
        }

        .search-box input::placeholder {
            color: rgba(255,255,255,0.5);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: rgba(200, 169, 81, 0.24);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
        }

        .user-avatar:hover {
            background: rgba(200, 169, 81, 0.4);
        }

        .mobile-toggle {
            display: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: white;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .mobile-toggle:hover {
            background: rgba(255,255,255,0.15);
        }

        /* ============================================
           MAIN CONTENT
           ============================================ */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px;
        }

        /* ============================================
           UNIVERSAL CARD STYLES (Android ref)
           ============================================ */
        .book-card-horizontal {
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            width: 350px;
            flex-shrink: 0;
            border: 1px solid var(--border-color);
        }

        .book-cover-horizontal {
            width: 120px;
            height: 165px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .book-card-vertical {
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            height: 180px;
            border: 1px solid var(--border-color);
        }

        .book-cover-vertical {
            width: 120px;
            height: 180px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .book-info-horizontal, .book-info-vertical {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .book-title {
            color: var(--text-color);
            font-weight: 700;
        }

        .book-author, .book-meta, .book-description {
            color: var(--light-text);
        }

        /* ============================================
           BUTTONS (Android rounded style)
           ============================================ */
        .btn {
            padding: 10px 22px;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            box-shadow: 0 4px 10px rgba(27, 94, 59, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(27, 94, 59, 0.3);
        }

        .btn-outline {
            background-color: transparent;
            border: 1.5px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: var(--radius-md);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* ============================================
           FOOTER (Android beige aesthetic)
           ============================================ */
        .footer {
            background-color: var(--card-bg);
            color: var(--text-color);
            padding: 60px 0 30px;
            margin-top: 50px;
            border-top: 1px solid var(--border-color);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-section h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: var(--primary-color);
        }

        .footer-section p {
            font-size: 0.9rem;
            color: var(--light-text);
            line-height: 1.7;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: var(--light-text);
            text-decoration: none;
            transition: color 0.3s, transform 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .copyright {
            max-width: 1200px;
            margin: 40px auto 0;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--light-text);
            font-size: 0.85rem;
        }

        /* Bismillah footer ornament */
        .bismillah-footer {
            text-align: center;
            padding: 28px 20px 0;
        }

        .bismillah-footer h3 {
            font-family: serif;
            color: var(--accent-color);
            font-size: 20px;
            font-weight: normal;
            margin: 0;
        }

        .bismillah-footer p {
            color: var(--light-text);
            font-size: 11px;
            margin-top: 4px;
        }

        /* ============================================
           RESPONSIVE — Mobile-first, Android-feel
           ============================================ */
        @media (max-width: 900px) {
            .nav-menu {
                position: fixed;
                top: 64px;
                left: 0;
                right: 0;
                background: var(--primary-dark);
                flex-direction: column;
                gap: 0;
                padding: 12px 20px;
                transform: translateY(-110%);
                transition: transform 0.3s ease;
                box-shadow: 0 8px 20px rgba(0,0,0,0.2);
                z-index: 999;
            }

            .nav-menu.active {
                transform: translateY(0);
            }

            .nav-link {
                padding: 12px 16px;
                border-radius: var(--radius-sm);
                font-size: 14px;
            }

            .mobile-toggle {
                display: flex;
            }

            .search-box {
                display: none;
            }

            .user-name-text {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .nav-container {
                height: 56px;
                padding: 0 14px;
            }

            .logo-text h2 {
                font-size: 16px;
            }

            .logo-text p {
                font-size: 9px;
            }

            .logo-icon {
                width: 36px;
                height: 36px;
                border-radius: 12px;
            }
            
            .logo-icon i {
                font-size: 16px;
            }

            .container {
                padding: 16px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="app-preloader" id="appPreloader">
        <img src="{{ asset('assets/static/images/logo/splash-logo.png') }}" alt="Al-Kutub Splash" class="app-preloader-logo">
        <div class="app-preloader-spinner"></div>
    </div>

    <!-- Navbar — Android Green Header -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-icon">
                    <img src="{{ asset('assets/static/images/logo/al-kutub-symbol.svg') }}" alt="Al-Kutub" style="width: 26px; height: 26px;">
                </div>
                <div class="logo-text">
                    <h2>Al-Kutub</h2>
                    <p>Perpustakaan Digital</p>
                </div>
            </a>
            
            <ul class="nav-menu">
                <li>
                    <a href="{{ route('home') }}" 
                    class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-home nav-icon"></i>Beranda
                    </a>
                </li>
                <li>
                    <a href="{{route('kategori.index')}}" 
                    class="nav-link {{ request()->routeIs('kategori.index') ? 'active' : '' }}">
                    <i class="fas fa-layer-group nav-icon"></i>Kategori
                    </a>
                </li>
                <li>
                    <a href="{{ route('bookmarks.index') }}" 
                    class="nav-link {{ request()->routeIs('bookmarks.index') ? 'active' : '' }}">
                    <i class="fas fa-bookmark nav-icon"></i>Bookmark
                    </a>
                </li>
                <li>
                    <a href="{{ route('history.index') }}" 
                    class="nav-link {{ request()->routeIs('history.index') ? 'active' : '' }}">
                    <i class="fas fa-history nav-icon"></i>History
                    </a>
                </li>
                <li>
                    <a href="{{ route('reading-notes.index') }}"
                    class="nav-link {{ request()->routeIs('reading-notes.*') ? 'active' : '' }}">
                    <i class="fas fa-sticky-note nav-icon"></i>Catatan
                    </a>
                </li>
                <li>
                    <a href="{{ route('account.edit') }}" 
                    class="nav-link {{ request()->routeIs('account.edit') || request()->routeIs('reading-goals.*') || request()->routeIs('reading-statistics.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog nav-icon"></i>Account
                    </a>
                </li>
            </ul>

            <div class="nav-actions">
                <div class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </div>
                <a href="{{ route('notifications.index') }}" class="notif-link" id="notif-bell">
                    <i class="fas fa-bell"></i>
                    <span id="notif-badge" style="display:none; position:absolute; top: 4px; right: 4px; width: 8px; height: 8px; background: #EF4444; border-radius: 50%; border: 2px solid var(--primary-color);"></span>
                </a>

                <form action="{{ route('home') }}" method="GET" class="search-box" style="position: relative;">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        name="search" 
                        id="search"
                        value="{{ request('search') }}" 
                        placeholder="Cari kitab..."
                        autocomplete="off"
                    >
                    <div id="search-results" 
                        style="position: absolute; top: 45px; left: 0; width: 100%; 
                                background: var(--card-bg); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
                                z-index: 100; display: none; border: 1px solid var(--border-color); overflow: hidden;">
                    </div>
                </form>

                <a href="{{ route('account.edit') }}" class="user-avatar" title="{{ Auth::user()->username }}">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                </a>

                <div class="mobile-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>

        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        @yield('konten')
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Tentang Al-Kutub -->
            <div class="footer-section">
                <h3>Al-Kutub</h3>
                <p>
                    Platform digital untuk membaca, mempelajari, dan mengeksplorasi kitab-kitab klasik & kontemporer.
                    Dibuat untuk para pelajar, santri, dan pecinta ilmu.
                </p>
            </div>

            <!-- Navigasi Cepat -->
            <div class="footer-section">
                <h3>Navigasi</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('kategori.index') }}">Kategori</a></li>
                    <li><a href="{{ route('bookmarks.index') }}">Bookmark</a></li>
                    <li><a href="{{ route('history.index') }}">History</a></li>
                    <li><a href="{{ route('reading-notes.index') }}">Catatan</a></li>
                    <li><a href="{{ route('reading-goals.index') }}">Reading Goals</a></li>
                    <li><a href="{{ route('reading-statistics.index') }}">Reading Statistics</a></li>
                    <li><a href="{{ route('account.edit') }}">Account</a></li>
                </ul>
            </div>
            <div class="footer-section"> 
                <h3>Bantuan</h3> 
                <ul class="footer-links"> 
                    <li><a href="#">FAQ</a></li> 
                    <li><a href="#">Kontak</a></li> 
                    <li><a href="#">Kebijakan Privasi</a></li> 
                </ul> 
            </div>
            <!-- Sosial Media -->
            <div class="footer-section">
                <h3>Sosial Media</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                    <li><a href="#"><i class="fab fa-youtube"></i> YouTube</a></li>
                    <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                    <li><a href="#"><i class="fab fa-tiktok"></i> TikTok</a></li>
                </ul>
            </div>
        </div>

        <div class="bismillah-footer">
            <h3>بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</h3>
            <p>Mulailah belajar dengan nama Allah</p>
        </div>

        <div class="copyright">
            &copy; {{ date('Y') }} Al-Kutub. Dibuat dengan dedikasi untuk pelajar & pencari ilmu. 
        </div>
    </footer>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        window.addEventListener('load', function () {
            const preloader = document.getElementById('appPreloader');
            if (!preloader) return;
            setTimeout(function () {
                preloader.classList.add('hide');
                setTimeout(function () {
                    preloader.remove();
                }, 500);
            }, 2500);
        });

        // Theme Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const icon = themeToggle.querySelector('i');

        // Check local storage
        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                localStorage.setItem('theme', 'light');
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });

        // Mobile menu toggle
        document.querySelector('.mobile-toggle').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
        
        // Active nav link
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Close mobile menu after clicking a link
                document.querySelector('.nav-menu').classList.remove('active');
            });
        });

        // --- REALTIME NOTIFICATION POLLING ---
        let lastNotifId = localStorage.getItem('last_notif_id') || 0;

        function checkNewNotification() {
            $.ajax({
                url: "/api/notifications/latest",
                type: "GET",
                success: function(response) {
                    if (response.success && response.data) {
                        const notif = response.data;
                        if (notif.id > lastNotifId) {
                            if (lastNotifId != 0) { // Don't show on first load
                                showNotifToast(notif);
                                $('#notif-badge').show();
                            }
                            lastNotifId = notif.id;
                            localStorage.setItem('last_notif_id', lastNotifId);
                        }
                    }
                }
            });
        }

        function showNotifToast(notif) {
            if (typeof Toastify === 'undefined') return;
            
            Toastify({
                text: "🔔 " + notif.title + "\n" + notif.message,
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right",
                onClick: function(){
                    window.location.href = "{{ route('notifications.index') }}";
                },
                style: {
                    background: "linear-gradient(to right, #1B5E3B, #2D7A52)",
                    borderRadius: "14px",
                    boxShadow: "0 4px 12px rgba(27,94,59,0.2)",
                    fontFamily: "'Poppins', sans-serif"
                }
            }).showToast();
        }

        // Check every 30 seconds
        setInterval(checkNewNotification, 30000);
        // Initial check
        checkNewNotification();
    </script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelector('.search-box input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#search').on('input', function() {
                let query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('search.ajax') }}",
                        type: "GET",
                        data: { query: query },
                        success: function(data) {
                            let results = '';
                            if (data.length > 0) {
                                results += '<ul style="list-style:none; margin:0; padding:0;">';
                                data.forEach(kitab => {
                                    results += `
                                        <li style="display:flex; align-items:center; gap:10px; padding:10px 14px; border-bottom:1px solid var(--border-color); cursor:pointer; transition: background 0.2s;"
                                            onmouseover="this.style.background='var(--secondary-color)'"
                                            onmouseout="this.style.background='transparent'"
                                            onclick="window.location.href='/kitab/view/${kitab.id_kitab}'">
                                            <img src="/cover/${kitab.cover}" 
                                                alt="cover" 
                                                style="width:48px; height:66px; object-fit:cover; border-radius:8px; border: 1px solid var(--border-color);">
                                            <div>
                                                <strong style="color: var(--text-color); font-size: 13px;">${kitab.judul}</strong><br>
                                                <small style="color: var(--light-text); font-size: 11px;">${kitab.penulis}</small>
                                            </div>
                                        </li>`;
                                });
                                results += '</ul>';
                            } else {
                                results = '<p style="padding:14px; color:var(--light-text); font-size: 13px; text-align: center;">📖 Tidak ada hasil</p>';
                            }
                            $('#search-results').html(results).show();
                        }
                    });
                } else {
                    $('#search-results').hide();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-box').length) {
                    $('#search-results').hide();
                }
            });
        });
    </script>

    <!-- REAL-TIME NOTIFICATION POLLING (AJAX) -->
    <script>
        $(document).ready(function() {
            let lastNotifId = localStorage.getItem('last_notif_id') || 0;

            // Fungsi cek notifikasi
            function checkNotifications() {
                $.ajax({
                    url: '/api/notifications/latest',
                    method: 'GET',
                    headers: { 
                        'Authorization': 'Bearer ' + '{{ Auth::check() ? "session_based" : "guest" }}'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            let notif = response.data;
                            
                            if (notif.id > lastNotifId) {
                                lastNotifId = notif.id;
                                localStorage.setItem('last_notif_id', lastNotifId);

                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter', Swal.stopTimer)
                                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                                    }
                                });

                                Toast.fire({
                                    icon: 'info',
                                    title: notif.title,
                                    text: notif.message,
                                    didDestroy: () => {
                                        if(notif.action_url) window.location.href = notif.action_url;
                                    }
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        console.log('Polling error:', xhr.status);
                    }
                });
            }

            setInterval(checkNotifications, 30000); 
            setTimeout(checkNotifications, 2000);
        });
    </script>


</body>
</html>
