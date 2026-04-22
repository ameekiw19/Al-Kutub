<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Al-Kutub</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/static/images/logo/favicon.svg') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/static/images/logo/favicon-dark.svg') }}" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" href="{{ asset('assets/static/images/logo/favicon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static/images/logo/favicon-dark.png') }}" media="(prefers-color-scheme: dark)">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/static/images/logo/favicon-192.png') }}">
    <meta name="theme-color" content="#1B5E3B">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{url('https://kit.fontawesome.com/2e37d4b90b.js')}}" crossorigin="anonymous"></script>

    <link rel="stylesheet" crossorigin href="{{url('./assets/compiled/css/app.css')}}">
    <link rel="stylesheet" crossorigin href="{{url('./assets/compiled/css/app-dark.css')}}">
    <link rel="stylesheet" crossorigin href="{{url('./assets/compiled/css/iconly.css')}}">

    <link rel="stylesheet" href="{{url('assets/extensions/filepond/filepond.css')}}">
    <link rel="stylesheet" href="{{url('assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css')}}">
    <link rel="stylesheet" href="{{url('assets/extensions/toastify-js/src/toastify.css')}}">
    <link rel="stylesheet" href="{{url('assets/extensions/sweetalert2/sweetalert2.min.css')}}">
    <link rel="stylesheet" crossorigin href="{{url('./assets/compiled/css/extra-component-sweetalert.css')}}">
    <link rel="stylesheet" href="{{url('assets/extensions/simple-datatables/style.css')}}">
    <link rel="stylesheet" crossorigin href="{{url('./assets/compiled/css/table-datatable.css')}}">
    <link rel="stylesheet" href="{{url('assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}">
    <link rel="stylesheet" crossorigin href="{{url('./assets/compiled/css/table-datatable-jquery.css')}}">
    <link rel="stylesheet" href="{{url('./assets/compiled/css/custom-admin.css')}}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body>
    <script src="{{url('assets/static/js/initTheme.js')}}"></script>
    <div id="app">
        <!-- Sidebar -->
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active shadow-sm border-end" style="border-color: var(--border-color) !important;">
                <div class="sidebar-header position-relative pt-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center px-4">
                        <div class="logo d-flex align-items-center gap-3">
                            <a href="{{ route('admin.home') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-4"
                                      style="width: 40px; height: 40px; background: var(--primary-color);">
                                    <img src="{{ asset('assets/static/images/logo/al-kutub-symbol.svg') }}"
                                         alt="Al-Kutub"
                                         style="width: 22px; height: 22px; filter: brightness(0) invert(1);">
                                </span>
                                <span class="text-dark fw-bold fs-5 lh-1 d-none d-lg-block">Al-Kutub</span>
                            </a>
                        </div>
                        <div class="theme-toggle d-flex gap-2 align-items-center mt-2 d-xl-none">
                            <i class="bi bi-sun-fill fs-5" id="icon-light"></i>
                            <div class="form-check form-switch fs-6">
                                <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                                <label class="form-check-label"></label>
                            </div>
                            <i class="bi bi-moon-fill fs-5" id="icon-dark"></i>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-menu px-3">
                    <ul class="menu mt-3">
                        <li class="sidebar-title text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 1px;">Home</li>
                        
                        <li class="sidebar-item {{ request()->routeIs('admin.home') ? 'active' : '' }}">
                            <a href="{{ route('admin.home') }}" class='sidebar-link rounded-4'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Ringkasan</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class='sidebar-link rounded-4'>
                                <i class="bi bi-graph-up-arrow"></i>
                                <span>Analitik Lengkap</span>
                            </a>
                        </li>

                        <li class="sidebar-title text-muted text-uppercase fw-semibold mt-4" style="font-size: 0.75rem; letter-spacing: 1px;">Manajemen</li>

                        <li class="sidebar-item {{ request()->routeIs('admin.kitab.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kitab.index') }}" class='sidebar-link rounded-4'>
                                <i class="fas fa-book"></i>
                                <span>Manajemen Kitab</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/categories*') ? 'active' : '' }}">
                            <a href="{{ route('admin.categories.index') }}" class='sidebar-link rounded-4'>
                                <i class="bi bi-tags-fill"></i>
                                <span>Manajemen Kategori</span>
                            </a>
                        </li>
                        
                        <li class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index') }}" class='sidebar-link rounded-4'>
                                <i class="fas fa-users"></i>
                                <span>Manajemen Users</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/comments') ? 'active' : '' }}">
                            <a href="{{ url('/admin/comments') }}" class='sidebar-link rounded-4'>
                                <i class="fas fa-comments"></i>
                                <span>Comments</span>
                            </a>
                        </li>

                        <li class="sidebar-title text-muted text-uppercase fw-semibold mt-4" style="font-size: 0.75rem; letter-spacing: 1px;">Sistem</li>

                        <li class="sidebar-item {{ request()->is('admin/notifications') ? 'active' : '' }}">
                            <a href="{{ route('admin.notifications') }}" class='sidebar-link rounded-4'>
                                <i class="fas fa-bell"></i>
                                <span>Broadcast</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/audit*') ? 'active' : '' }}">
                            <a href="{{ route('admin.audit.index') }}" class='sidebar-link rounded-4'>
                                <i class="fas fa-history"></i>
                                <span>Audit Logs</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="main" class='layout-navbar navbar-fixed'>
            <header class="mb-0 shadow-sm border-bottom" style="background: var(--card-bg);">
                <nav class="navbar navbar-expand navbar-light navbar-top px-4 py-3">
                    <div class="container-fluid px-0">
                        <!-- Burger Menu for Mobile -->
                        <a href="#" class="burger-btn d-block">
                            <i class="bi bi-justify fs-3 text-dark"></i>
                        </a>

                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <!-- Search Bar -->
                            <div class="header-search ms-md-4 ms-2 d-none d-md-block" style="flex: 0 1 350px;">
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light text-muted rounded-start-4 ps-3">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-0 bg-light rounded-end-4 py-2 ps-2" placeholder="Cari di admin..." style="font-size: 0.9rem;">
                                </div>
                            </div>

                            <ul class="navbar-nav ms-auto mb-lg-0 align-items-center gap-3">
                                <!-- Theme Toggle -->
                                <li class="nav-item d-none d-xl-flex">
                                    <div class="theme-toggle d-flex gap-2 align-items-center">
                                        <i class="bi bi-sun-fill text-muted fs-5" id="icon-light"></i>
                                        <div class="form-check form-switch fs-6">
                                            <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                                            <label class="form-check-label"></label>
                                        </div>
                                        <i class="bi bi-moon-fill text-muted fs-5" id="icon-dark"></i>
                                    </div>
                                </li>

                                <!-- Notification Bell -->
                                <li class="nav-item dropdown me-2">
                                    <a class="nav-link active text-dark position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bi bi-bell bi-sub fs-5 text-muted'></i>
                                        <span class="position-absolute top-25 start-75 translate-middle p-1 bg-danger border border-light rounded-circle">
                                            <span class="visually-hidden">New alerts</span>
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 border-top border-primary border-3" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <h6 class="dropdown-header">Notifications</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="#">No new notification</a></li>
                                    </ul>
                                </li>
                                
                                @if (Auth::user() && Auth::user()->role === 'admin')
                                <div class="dropdown">
                                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false" class="text-decoration-none">
                                        <div class="user-menu d-flex align-items-center gap-2 bg-light px-3 py-1 rounded-pill transition-all">
                                            <div class="user-name text-end me-1 d-none d-md-block">
                                                <h6 class="mb-0 text-dark fs-6">{{ Auth::user()->username }}</h6>
                                                <p class="mb-0 text-sm text-muted" style="font-size: 0.75rem;">Administrator</p>
                                            </div>
                                            <div class="user-img d-flex align-items-center">
                                                <div class="avatar avatar-md border border-2 border-white shadow-sm">
                                                    <img src="{{ url('assets/compiled/jpg/1.jpg') }}" alt="User">
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 border-top border-primary border-3" aria-labelledby="dropdownMenuButton" style="min-width: 11rem;">
                                        <li>
                                            <h6 class="dropdown-header">Hello, {{ Auth::user()->username }}!</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="/2fa/setup"><i class="icon-mid bi bi-shield-check me-2 text-primary"></i> 2FA Settings</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ url('logout') }}"><i class="icon-mid bi bi-box-arrow-left me-2 text-danger"></i> Logout</a></li>
                                    </ul>
                                </div>
                                @endif
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>

            <div id="main-content" class="pt-4 px-4 pb-4">
                <div class="page-content">
                    @yield('isi')
                </div>

                <!-- Footer -->
                <footer class="bg-transparent border-top-0 py-4 mt-5">
                    <div class="container-fluid px-0">
                        <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.85rem;">
                            <div class="float-start">
                                <p class="mb-0">2023 &copy; Al-Kutub Admin</p>
                            </div>
                            <div class="float-end">
                                <p class="mb-0">Crafted with <span class="text-danger"><i class="bi bi-heart-fill"></i></span></p>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{url('assets/static/js/components/dark.js')}}"></script>
    <script src="{{url('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
    <script src="{{url('assets/compiled/js/app.js')}}"></script>
    <script src="{{url('assets/extensions/filepond/filepond.js')}}"></script>
    <script src="{{url('assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')}}"></script>
    <script src="{{url('assets/extensions/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')}}"></script>
    <script src="{{url('assets/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
    <script src="{{url('assets/extensions/toastify-js/src/toastify.js')}}"></script>
    <script src="{{url('assets/static/js/pages/filepond.js')}}"></script>
    <script src="{{url('assets/extensions/sweetalert2/sweetalert2.min.js')}}"></script>
    <script src="{{url('assets/static/js/pages/sweetalert2.js')}}"></script>
    <script src="{{url('assets/extensions/jquery/jquery.min.js')}}"></script>
    <script src="{{url('assets/extensions/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{url('assets/extensions/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
    <script src="{{url('assets/static/js/pages/datatables.js')}}"></script>
    <script src="{{url('assets/extensions/simple-datatables/umd/simple-datatables.js')}}"></script>
    <script src="{{url('assets/static/js/pages/simple-datatables.js')}}"></script>

    <!-- Global Styles for Admin Redesign -->
    <style>
        :root {
            --primary-color: rgb(27, 94, 59);
            --primary-dark: rgb(26, 74, 48);
            --secondary-color: rgb(248, 245, 239);
            --text-color: rgb(26, 46, 26);
            --light-text: rgb(139, 128, 112);
            --bg-color: rgb(250, 250, 245);
            --card-bg: rgb(255, 255, 255);
            --border-color: rgb(232, 227, 213);
            --border-radius: 16px;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
        }

        /* Sidebar Styling Fixes */
        .sidebar-wrapper {
            background-color: var(--card-bg);
        }
        
        .sidebar-menu .sidebar-item.active .sidebar-link {
            background-color: var(--primary-color) !important;
            box-shadow: 0 4px 12px rgba(27, 94, 59, 0.2);
            color: #fff !important;
        }
        
        .sidebar-menu .sidebar-link {
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s;
            margin-bottom: 0.2rem;
            padding: 0.7rem 1rem;
        }
        
        .sidebar-menu .sidebar-link:hover {
            background-color: rgba(27, 94, 59, 0.05);
            color: var(--primary-color) !important;
        }
        
        .sidebar-menu .sidebar-item.active .sidebar-link i {
            color: #fff !important;
        }

        /* Top Navbar Styling */
        .navbar-top {
            background: var(--card-bg) !important;
        }

        /* Form Controls (Matched with User Login Style) */
        .form-control, .form-select {
            padding: 10px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: 0.3s;
            color: var(--text-color);
            background-color: transparent;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(27, 94, 59, 0.1);
        }
        
        /* Input Group Override */
        .input-group-text {
            border: 2px solid #e2e8f0;
            border-right: none;
            background-color: transparent;
            color: var(--light-text);
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            background: var(--card-bg);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }
         
        .card-body {
            padding: 1.5rem;
        }

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(27, 94, 59, 0.3);
        }

        .user-menu:hover {
            background-color: #e9ecef !important;
            cursor: pointer;
        }

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] body {
            background-color: #121212;
            color: #e0e0e0;
        }
        
        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .sidebar-wrapper,
        [data-bs-theme="dark"] header.mb-0 {
            background-color: #1e1e1e !important;
            border-color: #333 !important;
        }
        
        [data-bs-theme="dark"] .sidebar-menu .sidebar-link {
            color: #bbb;
        }
        
        [data-bs-theme="dark"] .sidebar-menu .sidebar-link:hover {
            color: #fff !important;
            background: rgba(255,255,255,0.05);
        }
        
        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .input-group-text {
            border-color: #333;
            color: #e0e0e0;
            background-color: #2a2a2a !important;
        }
        
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus,
        [data-bs-theme="dark"] .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
        }

        [data-bs-theme="dark"] .user-menu {
            background-color: #2a2a2a !important;
        }
        
        [data-bs-theme="dark"] .user-menu .text-dark {
            color: #e0e0e0 !important;
        }
        
        [data-bs-theme="dark"] .user-menu:hover {
            background-color: #333 !important;
        }
    </style>
</body>
</html>
