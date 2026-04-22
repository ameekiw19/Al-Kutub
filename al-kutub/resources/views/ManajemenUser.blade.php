@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark">Manajemen Pengguna</h3>
            <p class="text-subtitle text-muted mb-0">Kelola akses, role, dan informasi akun pengguna.</p>
        </div>
        <div class="d-flex gap-2">
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4 animate-fade-in">
    <div class="col-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(45deg, #44A194, #207d72);">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small mb-1">TOTAL USER</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalUsers) }}</h3>
                    </div>
                    <div class="stats-icon bg-white bg-opacity-20 rounded p-2">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(45deg, #5EC7B8, #44A194);">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small mb-1">ADMINISTRATOR</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalAdmins) }}</h3>
                    </div>
                    <div class="stats-icon bg-white bg-opacity-20 rounded p-2">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(45deg, #26A69A, #80CBC4);">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small mb-1">USER BARU (BULAN INI)</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($newUsersThisMonth) }}</h3>
                    </div>
                    <div class="stats-icon bg-white bg-opacity-20 rounded p-2">
                        <i class="bi bi-person-plus-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(45deg, #80CBC4, #B2DFDB);">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small mb-1">PENGGUNA AKTIF</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($activeUsersToday) }}</h3>
                    </div>
                    <div class="stats-icon bg-white bg-opacity-20 rounded p-2 text-dark">
                        <i class="bi bi-lightning-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Table Card --}}
<div class="card border-0 shadow-sm animate-fade-in" style="animation-delay: 0.2s">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-dark">Daftar Pengguna</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-light-primary btn-sm rounded-pill px-3 fw-bold border border-primary-subtle">
                <i class="bi bi-download me-1"></i> Export
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted fw-bold small">PENGGUNA</th>
                        <th class="py-3 text-muted fw-bold small">KONTAK</th>
                        <th class="py-3 text-muted fw-bold small">ROLE</th>
                        <th class="py-3 text-muted fw-bold small">STATISTIK</th>
                        <th class="py-3 text-muted fw-bold small text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="user-row" id="user-{{ $user->id }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md me-3">
                                        <div class="avatar-content bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                                            {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $user->username }}</h6>
                                        <span class="text-muted small">ID: #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small"><i class="bi bi-envelope me-1 opacity-50"></i> {{ $user->email }}</span>
                                    <span class="text-muted text-xs"><i class="bi bi-telephone me-1 opacity-50"></i> {{ $user->phone ?? 'Belum Diatur' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-light-danger text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-shield-check me-1"></i> Admin
                                    </span>
                                @else
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-light-primary text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                            <i class="bi bi-person me-1"></i> User
                                        </span>
                                        @if($user->is_verified_by_admin)
                                            <span class="badge bg-light-success text-success border border-success-subtle rounded-pill px-3 py-1">
                                                <i class="bi bi-patch-check me-1"></i> Terverifikasi
                                            </span>
                                        @else
                                            <span class="badge bg-light-warning text-warning border border-warning-subtle rounded-pill px-3 py-1">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Verifikasi
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-bold">{{ $user->bookmarks_count }} Bookmarks</span>
                                    <small class="text-muted text-xs">Bergabung {{ $user->created_at->format('d M Y') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button onclick="ViewUser({{ $user->id }})" class="btn btn-sm btn-outline-primary border-0 rounded-circle" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-dark border-0 rounded-circle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><h6 class="dropdown-header">Ubah Role</h6></li>
                                            @if($user->role === 'admin')
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="UpdateRole({{ $user->id }}, 'user')"><i class="bi bi-person me-2"></i> Jadikan User Biasa</a></li>
                                            @else
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="UpdateRole({{ $user->id }}, 'admin')"><i class="bi bi-shield-lock me-2"></i> Jadikan Admin</a></li>
                                                @if(!$user->is_verified_by_admin)
                                                    <li><a class="dropdown-item text-success" href="javascript:void(0)" onclick="VerifyUser({{ $user->id }})"><i class="bi bi-patch-check me-2"></i> Verifikasi Akun</a></li>
                                                @endif
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="DeleteUser({{ $user->id }})"><i class="bi bi-trash me-2"></i> Hapus Akun</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detail Modal or Slide-over (Optional but adds finesse) --}}
<!-- ... later refinement if needed ... -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#userTable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari pengguna...",
                lengthMenu: "_MENU_",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ user",
                paginate: {
                    next: '<i class="bi bi-chevron-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>'
                }
            },
            dom: '<"d-flex justify-content-between align-items-center p-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>t<"d-flex justify-content-between align-items-center p-3"ip>'
        });
    });

    function DeleteUser(id) {
        Swal.fire({
            title: 'Hapus User?',
            text: "Data user, bookmark, dan history baca akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'btn btn-danger px-4 py-2 me-2',
                cancelButton: 'btn btn-secondary px-4 py-2'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('admin/delete-user') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
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
                        document.getElementById(`user-${id}`).remove();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            customClass: { popup: 'rounded-4 border-0' }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
                });
            }
        });
    }

    function UpdateRole(id, role) {
        Swal.fire({
            title: 'Ubah Role?',
            text: `Apakah anda yakin ingin menjadikan user ini sebagai ${role}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#44A194',
            confirmButtonText: 'Ya, Ubah!',
            customClass: {
                popup: 'rounded-4 border-0',
                confirmButton: 'btn btn-primary px-4 py-2 me-2',
                cancelButton: 'btn btn-light px-4 py-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('admin/update-user-role') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ role: role })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-4 border-0' }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            customClass: { popup: 'rounded-4 border-0' }
                        });
                    }
                });
            }
        });
    }

    function VerifyUser(id) {
        Swal.fire({
            title: 'Verifikasi akun user?',
            text: 'User ini akan bisa login setelah diverifikasi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Ya, Verifikasi!',
            customClass: {
                popup: 'rounded-4 border-0',
                confirmButton: 'btn btn-success px-4 py-2 me-2',
                cancelButton: 'btn btn-light px-4 py-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('admin/verify-user') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-4 border-0' }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            customClass: { popup: 'rounded-4 border-0' }
                        });
                    }
                });
            }
        });
    }

    function ViewUser(id) {
        // Implementasi modal detail user jika diperlukan
        Swal.fire({
            title: 'Info User',
            text: 'Fitur detail user sedang dikembangkan.',
            icon: 'info',
            customClass: { popup: 'rounded-4 border-0' }
        });
    }
</script>

<style>
    .animate-fade-in { opacity: 0; animation: fadeIn 0.8s ease forwards; }
    @keyframes fadeIn { to { opacity: 1; } }

    .table thead th { border-bottom: none; font-size: 0.75rem; letter-spacing: 0.05em; }
    .user-row { transition: all 0.2s ease; }
    .user-row:hover { background-color: rgba(68, 161, 148, 0.02); }
    
    .text-xs { font-size: 0.7rem; }
    
    /* DataTable customization */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 12px;
        padding: 8px 15px;
        border: 2px solid #e2e8f0;
        outline: none;
        transition: 0.3s;
    }
    .dataTables_wrapper .dataTables_filter input:focus { 
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(68, 161, 148, 0.1);
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: 5px; border: none !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: var(--primary-color) !important; color: white !important; }
</style>
@endsection
