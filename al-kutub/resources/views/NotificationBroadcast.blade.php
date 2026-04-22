@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark">Broadcast Notifikasi</h3>
            <p class="text-subtitle text-muted mb-0">Kirim pengumuman penting ke seluruh pengguna.</p>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    {{-- Form Section --}}
    <div class="col-12 col-lg-6 mb-4">
        <div class="card border-0 shadow-sm animate-card-left h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Buat Pengumuman</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.notifications.send') }}" method="POST" id="notifForm">
                    @csrf
                    <div class="form-group mb-4">
                        <label for="title" class="form-label fw-bold text-muted small">JUDUL NOTIFIKASI</label>
                        <input type="text" class="form-control form-control-lg px-3 py-3" id="title" name="title" placeholder="Contoh: Update Kitab Terbaru!" required>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Max 100 karakter.</div>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label for="message" class="form-label fw-bold text-muted small">ISI PESAN</label>
                        <textarea class="form-control form-control-lg px-3 py-3" id="message" name="message" rows="5" placeholder="Tulis rincian pengumuman di sini..." required></textarea>
                    </div>

                    <div class="form-group mb-4">
                        <label for="action_url" class="form-label fw-bold text-muted small">URL AKSI (OPSIONAL)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text border-0">/</span>
                            <input type="text" class="form-control px-3 py-3" id="action_url" name="action_url" placeholder="home">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Halaman yang dibuka user saat notifikasi di-klik.</div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-send-fill"></i> Kirim Broadcast Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- History Section --}}
    <div class="col-12 col-lg-6 mb-4">
        <div class="card border-0 shadow-sm animate-card-right h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Terakhir</h5>
                <span class="badge bg-light-primary text-primary px-3">{{ count($notifications) }} Dikirim</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-bold small">WAKTU & JUDUL</th>
                                <th class="py-3 text-muted fw-bold small">PESAN</th>
                                <th class="text-center pe-4 py-3 text-muted fw-bold small">TIPE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notif)
                                <tr>
                                    <td class="ps-4">
                                        <small class="text-muted d-block mb-1">{{ $notif->created_at->format('d M Y, H:i') }}</small>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $notif->title }}</h6>
                                    </td>
                                    <td>
                                        <p class="text-muted small mb-0 line-clamp-2" title="{{ $notif->message }}">{{ $notif->message }}</p>
                                    </td>
                                    <td class="text-center pe-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1">Manual</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                                            <i class="bi bi-megaphone text-muted fs-2"></i>
                                        </div>
                                        <p class="text-muted fw-semibold">Belum ada riwayat broadcast.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false,
            customClass: { popup: 'rounded-4 border-0' }
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
            customClass: { popup: 'rounded-4 border-0', confirmButton: 'btn btn-primary px-4' },
            buttonsStyling: false
        });
    });
</script>
@endif

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    
    .animate-card-left { opacity: 0; transform: translateX(-20px); animation: fadeInSide 0.6s ease forwards; }
    .animate-card-right { opacity: 0; transform: translateX(20px); animation: fadeInSide 0.6s ease forwards; animation-delay: 0.2s; }
    
    @keyframes fadeInSide { to { opacity: 1; transform: translateX(0); } }

    .form-control:focus {
        background-color: transparent !important;
        box-shadow: 0 0 0 3px rgba(68, 161, 148, 0.1);
        border: 2px solid var(--primary-color) !important;
    }

    .table thead th { border-bottom: none; font-size: 0.7rem; }
    .table tbody tr:hover { background-color: rgba(68, 161, 148, 0.02); }
</style>
@endsection
