@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark">Dashboard Analytics</h3>
            <p class="text-subtitle text-muted mb-0">Overview & Real-time statistics.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="refreshDashboard()" class="btn btn-outline-primary shadow-sm btn-sm px-3">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary shadow-sm btn-sm px-3 sticky-btn">
                <i class="bi bi-graph-up"></i> Full Report
            </a>
        </div>
    </div>
</div>

<section class="row">
    {{-- Main Content Column --}}
    <div class="col-12 col-lg-9">

        {{-- Stats Cards Row (Screenshot 1 Style) --}}
        <div class="row">
            {{-- Total Kitab --}}
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Total Kitab</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $total_kitab }}">{{ number_format($total_kitab) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">{{ $total_kategori }} Kategori</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Pengguna --}}
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Total Pengguna</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $total_user }}">{{ number_format($total_user) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">{{ $active_users_today }} Aktif hari ini</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Views --}}
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Total Views</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $total_views }}">{{ number_format($total_views) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">{{ $total_bookmarks }} Bookmarks</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Download --}}
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #f9d423 0%, #ff4e50 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Total Download</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $total_download }}">{{ number_format($total_download) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">Real-time data</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-cloud-download-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Engagement Metrics Row (per Screenshot 1) --}}
        <div class="row">
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="card-body p-1">
                        <div class="bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                            <i class="bi bi-bookmark-heart text-primary fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ $engagementMetrics['avg_bookmarks_per_user'] }}</h5>
                        <small class="text-muted" style="font-size: 0.7rem;">Avg Bookmark/User</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="card-body p-1">
                        <div class="bg-light-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                            <i class="bi bi-book text-success fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ $engagementMetrics['avg_reading_per_user'] }}</h5>
                        <small class="text-muted" style="font-size: 0.7rem;">Avg Reading/User</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="card-body p-1">
                        <div class="bg-light-info rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                            <i class="bi bi-percent text-info fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ $engagementMetrics['retention_rate'] }}%</h5>
                        <small class="text-muted" style="font-size: 0.7rem;">Retention 7 Hari</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="card-body p-1">
                        <div class="bg-light-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                            <i class="bi bi-people text-warning fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ $engagementMetrics['active_users_week'] }}</h5>
                        <small class="text-muted" style="font-size: 0.7rem;">Aktif 7 Hari</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row">
            {{-- User Registration Chart --}}
            <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm animate-fade-in h-100">
                    <div class="card-header border-bottom-0 pb-0 pt-4 px-4 bg-transparent">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-activity text-primary me-2"></i>User Registration Trend</h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="position: relative; height: 250px; width: 100%;">
                            <canvas id="userRegistrationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
             {{-- Category Distribution --}}
            <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm animate-fade-in h-100">
                    <div class="card-header border-bottom-0 pb-0 pt-4 px-4 bg-transparent">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart text-success me-2"></i>Distribusi Kategori</h5>
                    </div>
                    <div class="card-body p-4 d-flex align-items-center justify-content-center">
                        <div style="position: relative; height: 250px; width: 100%;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Tables --}}
        <div class="row">
             <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm animate-fade-in">
                    <div class="card-header bg-transparent pt-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                         <h5 class="fw-bold text-dark mb-0">Top Download Kitab</h5>
                         <span class="badge bg-light text-primary border border-primary">Top 5 (Bulan Ini)</span>
                    </div>
                    <div class="card-body p-0">
                         <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3">#</th>
                                        <th class="py-3">Kitab</th>
                                        <th class="py-3">Penulis</th>
                                        <th class="text-center py-3">Views</th>
                                        <th class="text-center pe-4 py-3">Downloads</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($topDownloads as $index => $td)
                                        <tr>
                                            <td class="ps-4">
                                                @if($index < 3)
                                                    <span class="badge rounded-circle {{ $index === 0 ? 'bg-warning' : ($index === 1 ? 'bg-secondary' : 'bg-brown') }} p-2" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">{{ $index + 1 }}</span>
                                                @else
                                                    <span class="text-muted ps-2">{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-dark">{{ $td['judul'] }}</td>
                                            <td class="text-muted">{{ $td['penulis'] }}</td>
                                            <td class="text-center"><span class="badge bg-light text-dark border">{{ number_format($td['views']) }}</span></td>
                                            <td class="text-center pe-4"><span class="badge bg-success bg-opacity-10 text-success">{{ number_format($td['downloads']) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm animate-fade-in h-100">
                    <div class="card-header bg-transparent pt-4 px-4 border-bottom-0">
                        <h5 class="fw-bold text-dark mb-0">Aktivitas Terbaru</h5>
                    </div>
                     <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                             @forelse ($log_aktivitas as $log)
                                <div class="list-group-item d-flex align-items-center gap-3 py-3 px-4 border-bottom-0">
                                    <div class="avatar avatar-md bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0;">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-0 text-truncate font-size-sm text-dark fw-bold">{{ $log->user->username }}</h6>
                                        <small class="text-muted d-block text-truncate">Membaca {{ $log->kitab->judul ?? 'Kitab' }}</small>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ $log->last_read_at->diffForHumans() }}</small>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted">Belum ada aktivitas.</div>
                            @endforelse
                        </div>
                     </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Sidebar Column --}}
    <div class="col-12 col-lg-3">
        
        {{-- Pengguna Baru --}}
        <div class="card border-0 shadow-sm mb-4 animate-slide-in-right">
            <div class="card-header bg-transparent pt-4 px-4 border-bottom-0">
                <h5 class="fw-bold text-dark mb-0">Pengguna Baru</h5>
            </div>
            <div class="card-body p-0">
                 @forelse ($user_baru as $u)
                    <div class="d-flex align-items-center gap-3 p-3 px-4 border-bottom border-light">
                        <div class="avatar avatar-md">
                            <img src="{{ url('assets/compiled/jpg/1.jpg') }}" class="rounded-circle" style="width:40px;height:40px; object-fit: cover;" alt="user">
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="mb-0 text-dark fw-semibold">{{ $u->username }}</h6>
                            <small class="text-muted">{{ $u->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted">Belum ada user baru.</div>
                @endforelse
            </div>
        </div>

        {{-- Kitab Terpopuler Sidebar --}}
        <div class="card border-0 shadow-sm mb-4 animate-slide-in-right" style="animation-delay: 0.1s;">
            <div class="card-header bg-transparent pt-4 px-4 border-bottom-0">
                <h5 class="fw-bold text-dark mb-0">Kitab Terpopuler</h5>
            </div>
            <div class="card-body pt-2 pb-4 px-4">
                 @forelse ($kitab_populer as $index => $k)
                    @if($index < 5)
                    <div class="mb-3">
                        <h6 class="mb-1 text-dark fw-bold">{{ $k['judul'] }}</h6>
                        <div class="d-flex align-items-center text-muted" style="font-size: 0.85rem;">
                            <i class="bi bi-eye me-1"></i> {{ number_format($k['views']) }} views
                        </div>
                        @if($index < 3)
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, ($k['views'] / max(1, $kitab_populer[0]['views'])) * 100) }}%"></div>
                        </div>
                        @endif
                    </div>
                    @endif
                @empty
                    <div class="text-center text-muted">Belum ada data.</div>
                @endforelse
            </div>
        </div>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. User Registration Chart ---
        const userRegCtx = document.getElementById('userRegistrationChart').getContext('2d');
        
        // Gradient for Line Chart
        let gradientReg = userRegCtx.createLinearGradient(0, 0, 0, 400);
        gradientReg.addColorStop(0, 'rgba(67, 94, 190, 0.2)');
        gradientReg.addColorStop(1, 'rgba(67, 94, 190, 0)');

        new Chart(userRegCtx, {
            type: 'line',
            data: {
                labels: @json($tanggal_user_reg),
                datasets: [{
                    label: 'User Baru',
                    data: @json($grafik_user_reg),
                    borderColor: '#435ebe',
                    backgroundColor: gradientReg,
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#435ebe',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#fff',
                        titleColor: '#2c3e50',
                        bodyColor: '#435ebe',
                        borderColor: '#eef2f3',
                        borderWidth: 1,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f0f0f0' },
                        ticks: { color: '#95a5a6' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#95a5a6' }
                    }
                }
            }
        });

        // --- 2. Category Distribution Chart ---
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryLabels = @json(collect($categoryData)->pluck('kategori'));
        const categoryCounts = @json(collect($categoryData)->pluck('count'));

        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: [
                        '#44A194', '#5EC7B8', '#80CBC4', '#B2DFDB', 
                        '#26A69A', '#009688', '#00796B', '#004D40'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { 
                            usePointStyle: true, 
                            padding: 15,
                            font: { size: 11 },
                            color: '#555'
                        }
                    }
                }
            }
        });

        // Counter Animation
        const counters = document.querySelectorAll('.count-up');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-count'));
            const duration = 1500;
            const step = target / (duration / 16);
            let current = 0;
            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            };
            setTimeout(updateCounter, 300);
        });
    });

    function refreshDashboard() {
        location.reload();
    }
</script>

<style>
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.5rem;
    }

    .bg-brown { background-color: #795548; color: white; }

    /* Animation Keyframes (reused from global or defined here) */
    .animate-card { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.5s forwards; }
    .animate-card:nth-child(1) { animation-delay: 0.1s; }
    .animate-card:nth-child(2) { animation-delay: 0.2s; }
    .animate-card:nth-child(3) { animation-delay: 0.3s; }
    .animate-card:nth-child(4) { animation-delay: 0.4s; }

    .animate-fade-in { opacity: 0; animation: fadeIn 0.8s forwards 0.3s; }
    
    .animate-slide-in-right { opacity: 0; transform: translateX(20px); animation: fadeInRight 0.5s forwards; }

    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { to { opacity: 1; } }
    @keyframes fadeInRight { to { opacity: 1; transform: translateX(0); } }
</style>
@endsection
