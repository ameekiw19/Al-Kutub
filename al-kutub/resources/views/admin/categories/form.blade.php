@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark">{{ isset($category) ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>
            <p class="text-subtitle text-muted mb-0">{{ isset($category) ? 'Perbarui data kategori.' : 'Tambahkan kategori kitab baru.' }}</p>
        </div>
        <div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
</div>

<section class="section">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ isset($category) ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}" method="POST">
                @csrf
                @if(isset($category))
                    @method('PUT')
                @endif

                <div class="mb-4">
                    <label class="form-label fw-bold">Nama Kategori</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name', $category->name ?? '') }}" placeholder="Contoh: Aqidah" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Slug</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                        value="{{ old('slug', $category->slug ?? '') }}" placeholder="Contoh: aqidah (kosongkan untuk auto-generate)">
                    <small class="text-muted">Slug digunakan di database kitab. Kosongkan untuk otomatis dari nama.</small>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Urutan Tampil</label>
                    <input type="number" name="sort_order" class="form-control" min="0" 
                        value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Kategori aktif (tampil di form kitab)</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>{{ isset($category) ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
