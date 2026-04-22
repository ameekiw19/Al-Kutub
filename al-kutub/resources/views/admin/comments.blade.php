@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark">Moderasi Komentar</h3>
            <p class="text-subtitle text-muted mb-0">Monitor ulasan dan feedback dari pengguna.</p>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Komentar</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row mb-4">
    {{-- Summary Cards --}}
    <div class="col-12 col-md-4 mb-3">
        <div class="card border-0 shadow-sm animate-card h-100 overflow-hidden" style="background: linear-gradient(135deg, #44A194 0%, #207d72 100%);">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small mb-1">TOTAL KOMENTAR</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($total_comments) }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded p-2">
                        <i class="bi bi-chat-left-dots-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card border-0 shadow-sm animate-card h-100 overflow-hidden" style="background: linear-gradient(135deg, #44A194 0%, #5EC7B8 100%); animation-delay: 0.1s">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small mb-1">ULASAN KITAB</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($kitab_comments) }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded p-2">
                        <i class="bi bi-book-half fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card border-0 shadow-sm animate-card h-100 overflow-hidden" style="background: linear-gradient(135deg, #26A69A 0%, #80CBC4 100%); animation-delay: 0.2s">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small mb-1">SARAN UMUM</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($general_feedback) }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded p-2">
                        <i class="bi bi-lightning-charge-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm animate-fade-in">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-dark">Komentar Terbaru</h5>
        <div class="badge bg-light-primary text-primary px-3 py-2 border border-primary-subtle">
            {{ count($comments) }} Data Terbaru
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted fw-bold small">PENGGUNA</th>
                        <th class="py-3 text-muted fw-bold small">ISI KOMENTAR</th>
                        <th class="py-3 text-muted fw-bold small">TERHADAP</th>
                        <th class="py-3 text-muted fw-bold small">WAKTU</th>
                        <th class="text-center pe-4 py-3 text-muted fw-bold small">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                        <tr class="comment-row" id="comment-{{ $comment->id_comment }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <div class="avatar-content bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                            {{ strtoupper(substr($comment->user->username ?? 'U', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark small">{{ $comment->user->username ?? 'Unknown' }}</h6>
                                        <small class="text-muted text-xs">{{ $comment->user->email ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-wrap text-dark small" style="max-width: 400px; line-height: 1.4;">
                                    {{ $comment->isi_comment }}
                                </div>
                            </td>
                            <td>
                                @if($comment->id_kitab)
                                    <span class="badge bg-light-primary text-primary border border-primary-subtle rounded-pill">
                                        <i class="bi bi-book me-1"></i> {{ $comment->kitab->judul ?? 'Kitab Terhapus' }}
                                    </span>
                                @else
                                    <span class="badge bg-light-secondary text-secondary border border-secondary-subtle rounded-pill">
                                        <i class="bi bi-info-circle me-1"></i> Feedback Umum
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small">{{ $comment->created_at->diffForHumans() }}</div>
                                <div class="text-muted text-xs opacity-75">{{ $comment->created_at->format('d M, H:i') }}</div>
                            </td>
                            <td class="text-center pe-4">
                                <button onclick="deleteComment({{ $comment->id_comment }})" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                                    <i class="bi bi-chat-dots text-muted fs-2"></i>
                                </div>
                                <p class="text-muted fw-semibold">Belum ada komentar atau saran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($comments->hasPages())
        <div class="card-footer bg-white border-top py-3 text-center">
            <div class="d-inline-block">
                {{ $comments->links() }}
            </div>
        </div>
    @endif
</div>

<script>
    function deleteComment(id) {
        Swal.fire({
            title: 'Hapus komentar?',
            text: "Data yang dihapus tidak dapat dipulihkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'btn btn-danger px-4 py-2',
                cancelButton: 'btn btn-secondary px-4 py-2 ms-2'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    customClass: { popup: 'rounded-4 border-0' }
                });

                fetch(`{{ url('admin/delete-comment') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-4 border-0' }
                        });
                        
                        // Remove row with animation
                        const row = document.getElementById(`comment-${id}`);
                        if(row) {
                            row.style.transition = 'all 0.5s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                row.remove();
                                if (document.querySelectorAll('.comment-row').length === 0) location.reload();
                            }, 500);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan.',
                            customClass: { popup: 'rounded-4 border-0' }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: 'Terjadi kesalahan jaringan.',
                        customClass: { popup: 'rounded-4 border-0' }
                    });
                });
            }
        });
    }
</script>

<style>
    .animate-card { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.5s ease forwards; }
    .animate-fade-in { opacity: 0; animation: fadeIn 0.8s ease forwards; animation-delay: 0.3s; }
    
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { to { opacity: 1; } }

    .table thead th { border-bottom: none; font-size: 0.7rem; }
    .table tbody tr:hover { background-color: rgba(68, 161, 148, 0.02); }
    .text-xs { font-size: 0.7rem; }
    
    .pagination { margin-bottom: 0; }
    .page-link { color: var(--primary-color); border: none; padding: 0.5rem 0.8rem; margin: 0 2px; border-radius: 6px; }
    .page-item.active .page-link { background-color: var(--primary-color); }
</style>
@endsection
