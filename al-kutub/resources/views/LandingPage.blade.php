<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Al-Kutub - Platform Baca Kitab Digital</title>
    <meta name="description" content="Akses ribuan kitab klasik dan kontemporer dari berbagai bidang ilmu Islam dengan mudah dan gratis">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/static/images/logo/favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static/images/logo/favicon.png') }}">
    <meta name="theme-color" content="#1B5E3B">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: rgb(27, 94, 59);
            --primary-light: rgb(45, 122, 82);
            --primary-dark: rgb(26, 74, 48);
            --accent: rgb(200, 169, 81);
            --bg: rgb(250, 250, 245);
            --card: rgb(255, 255, 255);
            --text: rgb(26, 46, 26);
            --text-light: rgb(139, 128, 112);
            --border: rgb(232, 227, 213);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--bg); color: var(--text); line-height: 1.6; overflow-x: hidden; }

        /* PRELOADER */
        .landing-preloader {
            position: fixed; inset: 0; background: #efefef; z-index: 9999;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px;
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .landing-preloader.hide { opacity: 0; visibility: hidden; pointer-events: none; }
        .landing-preloader-logo { width: min(460px, 82vw); height: auto; opacity: 0; transform: scale(0.85); animation: splashLogoIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards; }
        .landing-preloader-spinner { width: 38px; height: 38px; border-radius: 999px; border: 3px solid rgba(116,200,77,0.25); border-top-color: #74c84d; opacity: 0; animation: spin 0.9s linear infinite, splashFadeIn 0.4s ease 0.6s forwards; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes splashLogoIn { to { opacity: 1; transform: scale(1); } }
        @keyframes splashFadeIn { to { opacity: 1; } }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* HEADER */
        .lp-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light), var(--primary-dark));
            padding: 0; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 4px 15px rgba(27,94,59,0.25);
        }
        .lp-header-inner { display: flex; justify-content: space-between; align-items: center; height: 64px; }
        .lp-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .lp-logo-icon { width: 42px; height: 42px; background: rgba(200,169,81,0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center; }
        .lp-logo-icon i { color: var(--accent); font-size: 20px; }
        .lp-logo-text h2 { color: white; font-size: 18px; font-weight: 800; margin: 0; line-height: 1.1; }
        .lp-logo-text p { color: rgba(255,255,255,0.72); font-size: 10px; margin: 0; }
        .lp-nav { display: flex; list-style: none; gap: 6px; }
        .lp-nav a { text-decoration: none; color: rgba(255,255,255,0.78); font-weight: 600; font-size: 13px; padding: 8px 14px; border-radius: 10px; transition: 0.3s; }
        .lp-nav a:hover { color: white; background: rgba(255,255,255,0.12); }
        .lp-auth { display: flex; gap: 10px; }
        .lp-btn-login { background: transparent; color: white; border: 1.5px solid var(--accent); padding: 8px 18px; border-radius: 14px; font-weight: 600; font-size: 13px; cursor: pointer; transition: 0.3s; text-decoration: none; }
        .lp-btn-login:hover { background: var(--accent); color: white; transform: translateY(-2px); }
        .lp-btn-register { background: var(--accent); color: white; border: none; padding: 8px 18px; border-radius: 14px; font-weight: 600; font-size: 13px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(200,169,81,0.3); text-decoration: none; }
        .lp-btn-register:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(200,169,81,0.4); }

        /* HERO */
        .lp-hero {
            padding: 80px 0 100px;
            background: linear-gradient(135deg, rgba(27,94,59,0.96), rgba(45,122,82,0.94));
            color: white; text-align: center; position: relative; overflow: hidden;
        }
        .lp-hero::after {
            content: 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم';
            position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
            font-family: serif; font-size: 20px; color: rgba(200,169,81,0.3);
        }
        .lp-hero h1 { font-size: clamp(28px, 5vw, 48px); font-weight: 800; margin-bottom: 16px; line-height: 1.2; }
        .lp-hero p { font-size: clamp(14px, 2vw, 18px); opacity: 0.85; max-width: 600px; margin: 0 auto 36px; }
        .lp-hero-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .lp-hero-btn-primary { background: var(--accent); color: white; border: none; padding: 14px 32px; border-radius: 18px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 14px rgba(200,169,81,0.3); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .lp-hero-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(200,169,81,0.4); color: white; }
        .lp-hero-btn-secondary { background: transparent; color: white; border: 2px solid var(--accent); padding: 14px 32px; border-radius: 18px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .lp-hero-btn-secondary:hover { background: var(--accent); transform: translateY(-3px); color: white; }

        /* FEATURES */
        .lp-features { padding: 80px 0; background: var(--card); }
        .lp-section-head { text-align: center; margin-bottom: 50px; }
        .lp-section-head h2 { font-size: clamp(24px, 3vw, 36px); font-weight: 800; color: var(--primary); margin-bottom: 8px; }
        .lp-section-head p { font-size: 15px; color: var(--text-light); max-width: 500px; margin: 0 auto; }
        .lp-features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .lp-feat-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 20px;
            padding: 28px 24px; text-align: center; transition: 0.3s;
        }
        .lp-feat-card:hover { transform: translateY(-6px); box-shadow: 0 8px 24px rgba(27,94,59,0.08); }
        .lp-feat-icon {
            width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px; color: white; font-size: 24px;
        }
        .lp-feat-card h3 { font-size: 17px; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .lp-feat-card p { font-size: 13px; color: var(--text-light); line-height: 1.6; }

        /* POPULAR BOOKS */
        .lp-popular { padding: 80px 0; background: var(--bg); }
        .lp-books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .lp-book-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 16px;
            padding: 12px; transition: 0.25s; cursor: pointer;
        }
        .lp-book-card:hover { transform: translateY(-4px); box-shadow: 0 6px 16px rgba(0,0,0,0.06); }
        .lp-book-cover { width: 100%; height: 172px; border-radius: 12px; overflow: hidden; margin-bottom: 10px; background: #eef2f5; position: relative; }
        .lp-book-cover img { width: 100%; height: 100%; object-fit: cover; }
        .lp-book-cat { position: absolute; top: 8px; left: 8px; background: var(--accent); color: white; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 8px; }
        .lp-book-title { font-size: 14px; font-weight: 700; color: var(--text); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 4px; }
        .lp-book-author { font-size: 12px; color: var(--text-light); }
        .lp-view-all { text-align: center; }

        /* STATS */
        .lp-stats {
            padding: 60px 0;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }
        .lp-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 24px; text-align: center; }
        .lp-stat h3 { font-size: 40px; font-weight: 800; margin-bottom: 4px; }
        .lp-stat p { font-size: 14px; opacity: 0.8; }

        /* CTA */
        .lp-cta { padding: 80px 0; background: var(--card); text-align: center; }
        .lp-cta h2 { font-size: clamp(24px, 3vw, 36px); font-weight: 800; color: var(--primary); margin-bottom: 12px; }
        .lp-cta p { font-size: 15px; color: var(--text-light); max-width: 500px; margin: 0 auto 28px; }

        /* FOOTER */
        .lp-footer {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white; padding: 60px 0 20px;
        }
        .lp-footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 36px; margin-bottom: 36px; }
        .lp-footer-section h3 { margin-bottom: 16px; font-size: 16px; color: var(--accent); font-weight: 700; }
        .lp-footer-section p, .lp-footer-section a { color: rgba(255,255,255,0.65); margin-bottom: 10px; display: block; text-decoration: none; font-size: 13px; transition: 0.3s; }
        .lp-footer-section a:hover { color: var(--accent); transform: translateX(4px); }
        .lp-social { display: flex; gap: 10px; margin-top: 14px; }
        .lp-social a { width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .lp-social a:hover { background: var(--accent); transform: translateY(-3px); }
        .lp-copyright { text-align: center; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.45); font-size: 12px; }
        .lp-bismillah { text-align: center; padding: 24px 0 0; }
        .lp-bismillah h3 { font-family: serif; color: var(--accent); font-size: 18px; font-weight: normal; opacity: 0.7; margin: 0; }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .lp-nav { display: none; }
        }
        @media (max-width: 576px) {
            .lp-header-inner { height: 56px; }
            .lp-hero { padding: 50px 0 70px; }
            .lp-features-grid { grid-template-columns: 1fr; }
            .lp-stats-grid { grid-template-columns: 1fr 1fr; }
            .lp-auth { gap: 6px; }
            .lp-btn-login, .lp-btn-register { padding: 6px 12px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="landing-preloader" id="landingPreloader">
        <img src="{{ asset('assets/static/images/logo/splash-logo.png') }}" alt="Al-Kutub Splash" class="landing-preloader-logo">
        <div class="landing-preloader-spinner"></div>
    </div>

    <!-- Header -->
    <header class="lp-header">
        <div class="container">
            <div class="lp-header-inner">
                <a href="/" class="lp-logo">
                    <div class="lp-logo-icon"><img src="{{ asset('assets/static/images/logo/al-kutub-symbol.svg') }}" alt="Al-Kutub" style="width: 26px; height: 26px;"></div>
                    <div class="lp-logo-text"><h2>Al-Kutub</h2><p>Perpustakaan Digital</p></div>
                </a>
                <ul class="lp-nav">
                    <li><a href="#">Beranda</a></li>
                    <li><a href="#features">Fitur</a></li>
                    <li><a href="#koleksi">Koleksi</a></li>
                    <li><a href="#tentang">Tentang</a></li>
                </ul>
                <div class="lp-auth">
                    <a href="{{ route('login') }}" class="lp-btn-login">Masuk</a>
                    <a href="{{ route('register') }}" class="lp-btn-register">Daftar</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="lp-hero">
        <div class="container">
            <h1>Jelajahi Khazanah Ilmu Islam dalam Genggaman Anda</h1>
            <p>Akses ribuan kitab klasik dan kontemporer dari berbagai bidang ilmu Islam dengan mudah dan gratis</p>
            <div class="lp-hero-btns">
                <a href="{{ route('register') }}" class="lp-hero-btn-primary"><i class="fas fa-rocket"></i> Mulai Gratis</a>
                <a href="#features" class="lp-hero-btn-secondary"><i class="fas fa-info-circle"></i> Pelajari Lebih</a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="lp-features" id="features">
        <div class="container">
            <div class="lp-section-head">
                <h2>Mengapa Memilih Al-Kutub?</h2>
                <p>Platform terbaik untuk membaca dan mempelajari kitab-kitab Islam</p>
            </div>
            <div class="lp-features-grid">
                <div class="lp-feat-card"><div class="lp-feat-icon"><i class="fas fa-book-open"></i></div><h3>Akses Gratis</h3><p>Nikmati akses gratis ke ribuan kitab Islam klasik dan kontemporer</p></div>
                <div class="lp-feat-card"><div class="lp-feat-icon"><i class="fas fa-mobile-alt"></i></div><h3>Multi Platform</h3><p>Baca kitab di web maupun aplikasi Android dengan desain yang konsisten</p></div>
                <div class="lp-feat-card"><div class="lp-feat-icon"><i class="fas fa-search"></i></div><h3>Pencarian Cepat</h3><p>Temukan kitab yang Anda cari dengan pencarian canggih</p></div>
                <div class="lp-feat-card"><div class="lp-feat-icon"><i class="fas fa-bookmark"></i></div><h3>Bookmark</h3><p>Simpan kitab favorit untuk dibaca nanti</p></div>
                <div class="lp-feat-card"><div class="lp-feat-icon"><i class="fas fa-download"></i></div><h3>Unduh Offline</h3><p>Download kitab untuk dibaca secara offline</p></div>
                <div class="lp-feat-card"><div class="lp-feat-icon"><i class="fas fa-layer-group"></i></div><h3>Kategori Lengkap</h3><p>Jelajahi berdasarkan Fiqih, Hadits, Tafsir, dan lainnya</p></div>
            </div>
        </div>
    </section>

    <!-- Popular Books -->
    <section class="lp-popular" id="koleksi">
        <div class="container">
            <div class="lp-section-head">
                <h2>Kitab Populer</h2>
                <p>Kitab-kitab terpopuler yang banyak dibaca pengguna</p>
            </div>
            <div class="lp-books-grid">
                <div class="lp-book-card"><div class="lp-book-cover"><img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80" alt="Shahih Al-Bukhari"><span class="lp-book-cat">Hadits</span></div><h3 class="lp-book-title">Shahih Al-Bukhari</h3><p class="lp-book-author">Imam Al-Bukhari</p></div>
                <div class="lp-book-card"><div class="lp-book-cover"><img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=600&q=80" alt="Riyadhus Shalihin"><span class="lp-book-cat">Akhlak</span></div><h3 class="lp-book-title">Riyadhus Shalihin</h3><p class="lp-book-author">Imam An-Nawawi</p></div>
                <div class="lp-book-card"><div class="lp-book-cover"><img src="https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&w=600&q=80" alt="Al-Muwaththa"><span class="lp-book-cat">Fiqih</span></div><h3 class="lp-book-title">Al-Muwaththa</h3><p class="lp-book-author">Imam Malik</p></div>
                <div class="lp-book-card"><div class="lp-book-cover"><img src="https://images.unsplash.com/photo-1589998059171-988d887df646?auto=format&fit=crop&w=600&q=80" alt="Bulughul Maram"><span class="lp-book-cat">Hadits</span></div><h3 class="lp-book-title">Bulughul Maram</h3><p class="lp-book-author">Ibnu Hajar Al-Asqalani</p></div>
            </div>
            <div class="lp-view-all">
                <a href="{{ route('login') }}" class="lp-hero-btn-primary"><i class="fas fa-book-open"></i> Lihat Semua Kitab</a>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="lp-stats" id="tentang">
        <div class="container">
            <div class="lp-stats-grid">
                <div class="lp-stat"><h3>2,500+</h3><p>Kitab Tersedia</p></div>
                <div class="lp-stat"><h3>50,000+</h3><p>Pengguna Aktif</p></div>
                <div class="lp-stat"><h3>100+</h3><p>Kategori Kitab</p></div>
                <div class="lp-stat"><h3>15+</h3><p>Bahasa Tersedia</p></div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="lp-cta">
        <div class="container">
            <h2>Siap Memulai Perjalanan Ilmu Anda?</h2>
            <p>Bergabunglah dengan ribuan pembaca lainnya dan jelajahi khazanah ilmu Islam</p>
            <a href="{{ route('register') }}" class="lp-hero-btn-primary"><i class="fas fa-user-plus"></i> Daftar Sekarang — Gratis!</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="lp-footer">
        <div class="container">
            <div class="lp-footer-grid">
                <div class="lp-footer-section">
                    <h3>Tentang Al-Kutub</h3>
                    <p>Platform digital untuk membaca dan mempelajari kitab-kitab Islam klasik dan kontemporer.</p>
                    <div class="lp-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="lp-footer-section"><h3>Kategori</h3><a href="#">Tafsir</a><a href="#">Hadits</a><a href="#">Fiqih</a><a href="#">Aqidah</a><a href="#">Bahasa Arab</a></div>
                <div class="lp-footer-section"><h3>Tautan</h3><a href="#">Beranda</a><a href="#">Koleksi</a><a href="#">Tentang</a><a href="#">Kebijakan Privasi</a></div>
                <div class="lp-footer-section"><h3>Kontak</h3><a href="#"><i class="fas fa-envelope"></i> info@al-kutub.com</a><a href="#"><i class="fas fa-phone"></i> +62 812 3456 7890</a><a href="#"><i class="fas fa-map-marker-alt"></i> Indonesia</a></div>
            </div>
            <div class="lp-bismillah"><h3>بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</h3></div>
            <div class="lp-copyright">&copy; {{ date('Y') }} Al-Kutub. Dibuat dengan dedikasi untuk pelajar & pencari ilmu.</div>
        </div>
    </footer>

    <script>
        window.addEventListener('load', function () {
            const preloader = document.getElementById('landingPreloader');
            if (!preloader) return;
            setTimeout(function () {
                preloader.classList.add('hide');
                setTimeout(function () { preloader.remove(); }, 500);
            }, 2500);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const statItems = document.querySelectorAll('.lp-stat h3');
            const animateValue = (element, start, end, duration) => {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = value.toLocaleString() + '+';
                    if (progress < 1) window.requestAnimationFrame(step);
                };
                window.requestAnimationFrame(step);
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateValue(statItems[0], 0, 2500, 2000);
                        animateValue(statItems[1], 0, 50000, 2000);
                        animateValue(statItems[2], 0, 100, 1500);
                        animateValue(statItems[3], 0, 15, 1500);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            const statsSection = document.querySelector('.lp-stats');
            if (statsSection) observer.observe(statsSection);
        });
    </script>
</body>
</html>
