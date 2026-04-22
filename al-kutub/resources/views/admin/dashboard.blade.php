@extends('Template')

@section('title', 'Dashboard Analytics - Admin Al-Kutub')

@section('isi')
<div class="page-heading mb-4">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-first">
                <h3><i class="bi bi-graph-up-arrow text-primary me-2"></i>Full Analytics Dashboard</h3>
                <p class="text-subtitle text-muted">Statistik dan data lengkap platform Al-Kutub</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <div class="float-end d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-cloud-arrow-down"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('overview')"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Overview</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('users')"><i class="bi bi-people me-2"></i>Users</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('kitabs')"><i class="bi bi-book me-2"></i>Kitabs</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('history')"><i class="bi bi-clock-history me-2"></i>History</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overview Cards -->
<section class="section">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm analytics-card" style="border-left: 4px solid #435ebe !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-people-fill" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Pengguna</h6>
                            <h3 class="mb-0 fw-bold" id="total-users">-</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm analytics-card" style="border-left: 4px solid #34a853 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                            <i class="bi bi-book-fill" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Kitab</h6>
                            <h3 class="mb-0 fw-bold" id="total-kitabs">-</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm analytics-card" style="border-left: 4px solid #4facfe !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                            <i class="bi bi-eye-fill" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Views</h6>
                            <h3 class="mb-0 fw-bold" id="total-views">-</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm analytics-card" style="border-left: 4px solid #f5576c !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                            <i class="bi bi-cloud-arrow-down-fill" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Downloads</h6>
                            <h3 class="mb-0 fw-bold" id="total-downloads">-</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Engagement Metrics Cards -->
<section class="section">
    <div class="row">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-person-check text-primary" style="font-size: 1.5rem;"></i>
                    <h4 class="fw-bold mb-0 mt-1" id="active-users-today">-</h4>
                    <small class="text-muted">Aktif Hari Ini</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-person-plus text-success" style="font-size: 1.5rem;"></i>
                    <h4 class="fw-bold mb-0 mt-1" id="new-users-month">-</h4>
                    <small class="text-muted">User Baru Bulan Ini</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-bookmark-heart text-warning" style="font-size: 1.5rem;"></i>
                    <h4 class="fw-bold mb-0 mt-1" id="total-bookmarks">-</h4>
                    <small class="text-muted">Total Bookmarks</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-clock-history text-info" style="font-size: 1.5rem;"></i>
                    <h4 class="fw-bold mb-0 mt-1" id="total-reading-time">-</h4>
                    <small class="text-muted">Waktu Baca (menit)</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Engagement Insights -->
<section class="section">
    <div class="row">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea15, #764ba215);">
                <div class="card-body text-center py-3">
                    <h5 class="fw-bold mb-0 text-primary" id="avg-bookmarks">-</h5>
                    <small class="text-muted">Avg Bookmark/User</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #34a85315, #00f2fe15);">
                <div class="card-body text-center py-3">
                    <h5 class="fw-bold mb-0 text-success" id="avg-reading">-</h5>
                    <small class="text-muted">Avg Reading/User</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe15, #00f2fe15);">
                <div class="card-body text-center py-3">
                    <h5 class="fw-bold mb-0 text-info" id="retention-rate">-</h5>
                    <small class="text-muted">Retention Rate (7 Hari)</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fa709a15, #fee14015);">
                <div class="card-body text-center py-3">
                    <h5 class="fw-bold mb-0 text-danger" id="active-week">-</h5>
                    <small class="text-muted">User Aktif 7 Hari</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Charts Section -->
<section class="section">
    <div class="row">
        <!-- User Registration Chart -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-graph-up text-primary me-2"></i>User Registration Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="userRegistrationChart" height="150"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Category Distribution -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-pie-chart text-success me-2"></i>Distribusi Kategori</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Activity Charts -->
<section class="section">
    <div class="row">
        <!-- Kitab Views Chart -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-bar-chart text-info me-2"></i>Aktivitas Membaca Harian (30 Hari)</h5>
                </div>
                <div class="card-body">
                    <canvas id="kitabViewsChart" height="150"></canvas>
                </div>
            </div>
        </div>
        
        <!-- User Activity -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-activity text-danger me-2"></i>User Aktif (7 Hari)</h5>
                </div>
                <div class="card-body">
                    <canvas id="userActivityChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Downloads Section -->
<section class="section">
    <div class="row">
        <!-- Top Downloads Chart -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cloud-arrow-down text-success me-2"></i>Top Downloaded Kitabs</h5>
                    <span class="badge bg-success">Top 10</span>
                </div>
                <div class="card-body">
                    <canvas id="topDownloadsChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Downloads by Category -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-diagram-3 text-warning me-2"></i>Downloads per Kategori</h5>
                </div>
                <div class="card-body">
                    <canvas id="downloadsByCategoryChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Kitabs & Stats -->
<section class="section">
    <div class="row">
        <!-- Popular Kitabs -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-fire text-danger me-2"></i>Kitab Paling Populer</h5>
                    <span class="badge bg-danger">By Views</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th class="text-center">Views</th>
                                    <th class="text-center">Downloads</th>
                                </tr>
                            </thead>
                            <tbody id="popular-kitabs-table">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Stats -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-trophy text-warning me-2"></i>Top Pembaca</h5>
                </div>
                <div class="card-body" id="top-readers-container">
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Loading...
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-bookmark-star text-primary me-2"></i>Most Bookmarked</h5>
                </div>
                <div class="card-body" id="most-bookmarked-container">
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let charts = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

// Load all dashboard data
function loadDashboardData() {
    loadOverviewStats();
    loadUserRegistrationChart();
    loadKitabViewsChart();
    loadPopularKitabs();
    loadCategoryDistribution();
    loadUserActivityChart();
    loadReadingStats();
    loadTopDownloads();
    loadEngagementMetrics();
}

// Load overview statistics
function loadOverviewStats() {
    fetch('/admin/dashboard/stats/overview')
        .then(response => response.json())
        .then(data => {
            animateCounter('total-users', data.total_users);
            animateCounter('total-kitabs', data.total_kitab);
            animateCounter('total-views', data.total_views);
            animateCounter('total-downloads', data.total_downloads);
            animateCounter('active-users-today', data.active_users_today);
            animateCounter('new-users-month', data.new_users_this_month);
            animateCounter('total-bookmarks', data.total_bookmarks);
        })
        .catch(error => console.error('Error loading overview stats:', error));
}

// Animate counter
function animateCounter(elementId, target) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const duration = 1500;
    const step = target / (duration / 16);
    let current = 0;
    
    const update = () => {
        current += step;
        if (current < target) {
            el.textContent = Math.floor(current).toLocaleString();
            requestAnimationFrame(update);
        } else {
            el.textContent = target.toLocaleString();
        }
    };
    update();
}

// Load user registration chart
function loadUserRegistrationChart() {
    fetch('/admin/dashboard/stats/user-registration')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('userRegistrationChart').getContext('2d');
            if (charts.userRegistration) charts.userRegistration.destroy();
            
            charts.userRegistration = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'User Baru',
                        data: data.data,
                        borderColor: '#435ebe',
                        backgroundColor: 'rgba(67, 94, 190, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#435ebe',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        })
        .catch(error => console.error('Error loading user registration chart:', error));
}

// Load kitab views chart
function loadKitabViewsChart() {
    fetch('/admin/dashboard/stats/kitab-views')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('kitabViewsChart').getContext('2d');
            if (charts.kitabViews) charts.kitabViews.destroy();
            
            charts.kitabViews = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Sesi Membaca',
                        data: data.data,
                        backgroundColor: 'rgba(79, 172, 254, 0.7)',
                        borderColor: '#4facfe',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        })
        .catch(error => console.error('Error loading kitab views chart:', error));
}

// Load popular kitabs
function loadPopularKitabs() {
    fetch('/admin/dashboard/stats/popular-kitabs')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('popular-kitabs-table');
            tbody.innerHTML = '';
            
            data.forEach((kitab, index) => {
                let rankBadge = '';
                if (index === 0) rankBadge = '<span class="badge bg-warning text-dark">🥇</span>';
                else if (index === 1) rankBadge = '<span class="badge bg-secondary">🥈</span>';
                else if (index === 2) rankBadge = '<span class="badge bg-dark">🥉</span>';
                else rankBadge = `<span class="text-muted">${index + 1}</span>`;

                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${rankBadge}</td>
                    <td class="fw-semibold">${kitab.judul}</td>
                    <td class="text-muted">${kitab.penulis}</td>
                    <td class="text-center"><span class="badge bg-light-primary text-primary">${kitab.views.toLocaleString()}</span></td>
                    <td class="text-center"><span class="badge bg-light-success text-success">${kitab.downloads.toLocaleString()}</span></td>
                `;
            });
        })
        .catch(error => console.error('Error loading popular kitabs:', error));
}

// Load category distribution
function loadCategoryDistribution() {
    fetch('/admin/dashboard/stats/category-distribution')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('categoryChart').getContext('2d');
            if (charts.category) charts.category.destroy();
            
            charts.category = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.kategori),
                    datasets: [{
                        data: data.map(item => item.count),
                        backgroundColor: ['#667eea', '#f5576c', '#4facfe', '#34a853', '#fa709a', '#9333ea', '#ec4899', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading category distribution:', error));
}

// Load user activity chart
function loadUserActivityChart() {
    fetch('/admin/dashboard/stats/user-activity')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('userActivityChart').getContext('2d');
            if (charts.userActivity) charts.userActivity.destroy();
            
            charts.userActivity = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'User Aktif',
                        data: data.data,
                        backgroundColor: 'rgba(234, 67, 53, 0.7)',
                        borderColor: '#ea4335',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        })
        .catch(error => console.error('Error loading user activity chart:', error));
}

// Load reading statistics
function loadReadingStats() {
    fetch('/admin/dashboard/stats/reading-stats')
        .then(response => response.json())
        .then(data => {
            animateCounter('total-reading-time', data.total_reading_time);
        })
        .catch(error => console.error('Error loading reading stats:', error));
}

// Load top downloads
function loadTopDownloads() {
    fetch('/admin/dashboard/stats/top-downloads')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('topDownloadsChart').getContext('2d');
            if (charts.topDownloads) charts.topDownloads.destroy();

            charts.topDownloads = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(k => k.judul.length > 20 ? k.judul.substring(0, 20) + '...' : k.judul),
                    datasets: [{
                        label: 'Downloads',
                        data: data.map(k => k.downloads),
                        backgroundColor: 'rgba(52, 168, 83, 0.7)',
                        borderColor: '#34a853',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        })
        .catch(error => console.error('Error loading top downloads:', error));

    // Also load Downloads by Category
    fetch('/admin/dashboard/stats/downloads-trend')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('downloadsByCategoryChart').getContext('2d');
            if (charts.downloadsByCategory) charts.downloadsByCategory.destroy();

            const catData = data.by_category || [];
            charts.downloadsByCategory = new Chart(ctx, {
                type: 'polarArea',
                data: {
                    labels: catData.map(c => c.kategori),
                    datasets: [{
                        data: catData.map(c => c.total_downloads),
                        backgroundColor: [
                            'rgba(102, 126, 234, 0.7)', 'rgba(245, 87, 108, 0.7)', 
                            'rgba(79, 172, 254, 0.7)', 'rgba(250, 112, 154, 0.7)',
                            'rgba(254, 225, 64, 0.7)', 'rgba(52, 168, 83, 0.7)',
                            'rgba(147, 51, 234, 0.7)', 'rgba(236, 72, 153, 0.7)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading downloads trend:', error));
}

// Load engagement metrics
function loadEngagementMetrics() {
    fetch('/admin/dashboard/stats/engagement')
        .then(response => response.json())
        .then(data => {
            document.getElementById('avg-bookmarks').textContent = data.avg_bookmarks_per_user;
            document.getElementById('avg-reading').textContent = data.avg_reading_per_user;
            document.getElementById('retention-rate').textContent = data.retention_rate + '%';
            document.getElementById('active-week').textContent = data.active_users_week;

            // Top readers
            const readersContainer = document.getElementById('top-readers-container');
            if (data.top_readers && data.top_readers.length > 0) {
                readersContainer.innerHTML = data.top_readers.map((reader, i) => `
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <span class="badge ${i === 0 ? 'bg-success' : 'bg-light text-muted'} me-2 rounded-circle p-2">${i + 1}</span>
                            <span class="fw-semibold">${reader.username}</span>
                        </div>
                        <span class="badge bg-light-success text-success">${reader.sessions} sesi</span>
                    </div>
                `).join('');
            } else {
                readersContainer.innerHTML = '<p class="text-muted text-center mb-0">Belum ada data.</p>';
            }

            // Most bookmarked
            const bookmarkContainer = document.getElementById('most-bookmarked-container');
            if (data.most_bookmarked) {
                bookmarkContainer.innerHTML = `
                    <div class="text-center">
                        <h6 class="fw-bold text-primary">${data.most_bookmarked.judul}</h6>
                        <small class="text-muted d-block">${data.most_bookmarked.penulis}</small>
                        <span class="badge bg-warning text-dark mt-2">
                            <i class="bi bi-bookmark-fill me-1"></i>${data.most_bookmarked.bookmark_count} bookmark
                        </span>
                    </div>
                `;
            } else {
                bookmarkContainer.innerHTML = '<p class="text-muted text-center mb-0">Belum ada data.</p>';
            }
        })
        .catch(error => console.error('Error loading engagement metrics:', error));
}

// Refresh dashboard
function refreshDashboard() {
    fetch('/admin/dashboard/clear-cache', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(() => {
        loadDashboardData();
        showNotification('Dashboard berhasil di-refresh!', 'success');
    })
    .catch(error => {
        console.error('Error refreshing dashboard:', error);
        showNotification('Error saat refresh dashboard', 'danger');
    });
}

// Export data
function exportData(type) {
    const url = `/admin/dashboard/export?type=${type}`;
    window.open(url, '_blank');
}

// Show notification
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3 shadow-lg border-0`;
    toast.style.zIndex = '9999';
    toast.style.animation = 'slideInRight 0.3s ease';
    toast.innerHTML = `<i class="bi bi-check-circle me-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<style>
    .analytics-card { 
        transition: transform 0.3s ease, box-shadow 0.3s ease; 
        border-left: 4px solid transparent !important;
    }
    .analytics-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
    }
    @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
</style>
@endsection
