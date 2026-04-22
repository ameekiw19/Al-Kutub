@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark">Detail Kitab</h3>
            <p class="text-subtitle text-muted mb-0">Informasi mendalam dan performa kitab.</p>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/manejemenkitab') }}">Kitab</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section">
    {{-- Header Section: Cover & Key Info --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 animate-fade-in">
        <div class="card-body p-0">
            <div class="row g-0">
                {{-- Left: Cover with Gradient Overlay --}}
                <div class="col-md-4 col-lg-3 position-relative bg-light d-flex align-items-center justify-content-center p-4">
                    <div class="kitab-cover-wrapper shadow-lg rounded-3 overflow-hidden">
                        <img src="{{ asset('cover/' . $kitab->cover) }}" alt="Cover" class="img-fluid" style="height: 350px; object-fit: cover;">
                        <div class="cover-overlay"></div>
                    </div>
                </div>
                {{-- Right: Info --}}
                <div class="col-md-8 col-lg-9 p-4 p-lg-5 d-flex flex-column justify-content-center">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-2">
                                <i class="bi bi-tag-fill me-1"></i> {{ strtoupper($kitab->kategori) }}
                            </span>
                            <h2 class="fw-bold text-dark mb-1">{{ $kitab->judul }}</h2>
                            <p class="text-muted fs-5 mb-0">Karya <span class="text-primary fw-bold">{{ $kitab->penulis }}</span></p>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-icon btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="{{ url('admin/editkitab/' . $kitab->id_kitab) }}"><i class="bi bi-pencil me-2 text-warning"></i> Edit Data</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="DeleteKitab('{{ $kitab->id_kitab }}')"><i class="bi bi-trash me-2"></i> Hapus Kitab</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-sm-3">
                            <div class="stats-mini-card p-3 rounded-3 bg-light border-start border-primary border-4">
                                <div class="text-muted small fw-bold mb-1">VIEWS</div>
                                <div class="h4 fw-bold mb-0 text-dark">{{ number_format($kitab->views) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="stats-mini-card p-3 rounded-3 bg-light border-start border-success border-4">
                                <div class="text-muted small fw-bold mb-1">DOWNLOADS</div>
                                <div class="h4 fw-bold mb-0 text-dark">{{ number_format($kitab->downloads) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="stats-mini-card p-3 rounded-3 bg-light border-start border-info border-4">
                                <div class="text-muted small fw-bold mb-1">BOOKMARKS</div>
                                <div class="h4 fw-bold mb-0 text-dark">{{ number_format($total_bookmarks) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="stats-mini-card p-3 rounded-3 bg-light border-start border-warning border-4">
                                <div class="text-muted small fw-bold mb-1">BOOKMARK RATE</div>
                                <div class="h4 fw-bold mb-0 text-dark">{{ $bookmark_rate }}%</div>
                            </div>
                        </div>
                    </div>

                    <div class="description-section mb-4">
                        <h6 class="fw-bold text-dark mb-2">Deskripsi Singkat:</h6>
                        <p class="text-muted" style="line-height: 1.7;">{{ $kitab->deskripsi }}</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-auto">
                        <a href="{{ asset('pdf/' . $kitab->file_pdf) }}" target="_blank" class="btn btn-primary px-4 py-2 fw-bold shadow-sm rounded-3">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i> Preview PDF
                        </a>
                        <a href="{{ url('admin/editkitab/' . $kitab->id_kitab) }}" class="btn btn-outline-warning px-4 py-2 fw-bold rounded-3">
                            <i class="bi bi-pencil-square me-2"></i> Edit Kitab
                        </a>
                        <a href="{{ url('admin/manejemenkitab') }}" class="btn btn-light text-muted px-4 py-2 fw-bold rounded-3 border">
                            <i class="bi bi-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Analytics & Reviews --}}
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 animate-fade-in" style="animation-delay: 0.2s">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 text-dark"><i class="bi bi-graph-up text-primary me-2"></i>Tren Aktivitas (30 Hari)</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar3 me-1"></i> Period
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" style="min-height: 350px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 animate-fade-in" style="animation-delay: 0.4s">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 text-dark"><i class="bi bi-chat-left-dots text-primary me-2"></i>Ulasan Terbaru</h5>
                    <a href="{{ route('admin.comments') }}" class="btn btn-sm btn-light text-primary fw-bold">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="review-list custom-scrollbar" style="max-height: 400px; overflow-y: auto;">
                        @forelse($reviews as $review)
                            <div class="p-3 border-bottom hover-bg-light transition-all">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($review->user->username ?? 'U', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold text-dark small">{{ $review->user->username ?? 'User' }}</h6>
                                        <small class="text-muted text-xs">{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted small line-clamp-3">{{ $review->isi_comment }}</p>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-chat-square-dots text-muted opacity-25 fs-1 mb-3 d-block"></i>
                                <p class="text-muted small">Belum ada ulasan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 animate-fade-in" style="animation-delay: 0.5s">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0 text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Revisi
            </h5>
            <span class="badge bg-light text-muted border">{{ isset($revisions) ? $revisions->count() : 0 }} revisi</span>
        </div>
        <div class="card-body p-0">
            @if(isset($revisions) && $revisions->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($revisions as $revision)
                        <div class="list-group-item py-3 px-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-dark">
                                        v{{ $revision->version_no }} - {{ strtoupper(str_replace('_', ' ', $revision->action)) }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Oleh: {{ optional($revision->actor)->username ?? 'System' }}
                                        @if($revision->note)
                                            • Catatan: {{ $revision->note }}
                                        @endif
                                    </div>
                                    @if(is_array($revision->changed_fields) && count($revision->changed_fields) > 0)
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            @foreach($revision->changed_fields as $field)
                                                <span class="badge bg-light-primary text-primary border border-primary-subtle">{{ $field }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">{{ optional($revision->created_at)->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted">Belum ada riwayat revisi.</div>
            @endif
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        // Gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(68, 161, 148, 0.4)');
        gradient.addColorStop(1, 'rgba(68, 161, 148, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($analytics_labels),
                datasets: [{
                    label: 'Sesi Membaca',
                    data: @json($analytics_data),
                    borderColor: '#44A194',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#44A194',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
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
                        titleColor: '#111',
                        bodyColor: '#666',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f1f1' },
                        ticks: { stepSize: 1, color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });
    });

    function DeleteKitab(id_kitab) {
        Swal.fire({
            title: 'Hapus Kitab?',
            text: "Seluruh data kitab dan file terkait akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'btn btn-danger px-4 py-2 btn-lg',
                cancelButton: 'btn btn-secondary px-4 py-2 btn-lg ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    customClass: { popup: 'rounded-4 border-0' }
                });

                fetch(`{{ url('admin/deletekitab') }}/${id_kitab}`, {
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error(data.message || 'Gagal menghapus');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Kitab telah dihapus.',
                        showConfirmButton: false,
                        timer: 1500,
                        customClass: { popup: 'rounded-4 border-0' }
                    }).then(() => {
                        window.location.href = "{{ url('admin/manejemenkitab') }}";
                    });
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message,
                        customClass: { popup: 'rounded-4 border-0' }
                    });
                });
            }
        });
    }
</script>

<style>
    .kitab-cover-wrapper { position: relative; border-radius: 1rem; overflow: hidden; }
    .cover-overlay {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.3));
    }
    
    .stats-mini-card { transition: transform 0.3s ease; }
    .stats-mini-card:hover { transform: translateY(-3px); }

    .animate-fade-in { opacity: 0; animation: fadeIn 0.8s ease forwards; }
    @keyframes fadeIn { to { opacity: 1; } }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }

    .hover-bg-light:hover { background-color: rgba(68, 161, 148, 0.05); }
    .transition-all { transition: all 0.2s ease; }

    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--primary-color); }
</style>
@endsection
