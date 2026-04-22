@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark">Manajemen Kategori</h3>
            <p class="text-subtitle text-muted mb-0">Kelola kategori kitab untuk filter dan organisasi.</p>
        </div>
        <div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary shadow-sm fw-bold px-4 py-2">
                <i class="bi bi-plus-lg me-2"></i>Tambah Kategori
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<section class="section">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-uppercase text-muted small fw-bold">#</th>
                            <th class="px-4 py-3 text-uppercase text-muted small fw-bold">Nama</th>
                            <th class="px-4 py-3 text-uppercase text-muted small fw-bold">Slug</th>
                            <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-center">Urutan</th>
                            <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-center">Jumlah Kitab</th>
                            <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-center">Status</th>
                            <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td class="px-4 py-3">{{ $cat->id }}</td>
                            <td class="px-4 py-3 fw-bold">{{ $cat->name }}</td>
                            <td class="px-4 py-3"><code>{{ $cat->slug }}</code></td>
                            <td class="px-4 py-3 text-center">{{ $cat->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                {{ \App\Models\Kitab::where('kategori', $cat->slug)->count() }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($cat->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('admin.categories.edit', $cat->id) }}" class="btn btn-sm btn-light-warning text-warning" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light-danger text-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada kategori. <a href="{{ route('admin.categories.create') }}">Tambah kategori pertama</a></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
