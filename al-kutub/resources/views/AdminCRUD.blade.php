@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark">Manajemen Kitab</h3>
            <p class="text-subtitle text-muted mb-0">Kelola katalog kitab dan perpustakaan digital.</p>
        </div>
        <div>
            <a href="{{ route('admin.kitab.create') }}" class="btn btn-primary shadow-sm fw-bold px-4 py-2">
                <i class="bi bi-plus-lg me-2"></i>Tambah Kitab
            </a>
            <button type="button" class="btn btn-outline-success shadow-sm fw-bold px-4 py-2 ms-2" id="btnImportAllTranscript">
                <i class="bi bi-magic me-2"></i>Import Transcript Semua
            </button>
        </div>
    </div>
</div>

<section class="section">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-auto">
                    <ul class="nav nav-pills" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold small" id="pills-indo-tab" data-bs-toggle="pill" data-bs-target="#pills-indo" type="button" role="tab">
                                <i class="bi bi-flag-fill me-1"></i> Bahasa Indonesia
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold small" id="pills-arab-tab" data-bs-toggle="pill" data-bs-target="#pills-arab" type="button" role="tab">
                                <i class="bi bi-translate me-1"></i> Bahasa Arab
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-4">
                    <div class="input-wrapper position-relative">
                        <input type="text" class="form-control" placeholder="Cari judul atau penulis..." id="searchKitab" style="padding-left: 40px;">
                        <i class="bi bi-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Bulk Action Bar --}}
        <div id="bulkActionBar" class="card-header bg-primary bg-opacity-10 border-bottom py-2 d-none">
                <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted small"><span id="selectedCount">0</span> item dipilih</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnBulkTranscript" title="Generate Transcript Terpilih">
                        <i class="bi bi-magic me-1"></i>Generate Transcript
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnBulkExport" title="Export CSV">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="btnBulkDelete" title="Hapus Terpilih">
                        <i class="bi bi-trash me-1"></i>Hapus Terpilih
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBulkCancel">Batal</button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="pills-tabContent">
                {{-- TAB INDONESIA --}}
                <div class="tab-pane fade show active" id="pills-indo" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="selectAllIndo" title="Pilih Semua">
                                    </th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold" style="width: 40%;">Info Kitab</th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold">Kategori</th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-center">Statistik</th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-indo">
                                @forelse($kitabs->whereIn('bahasa', ['Indonesia', 'indonesia']) as $kitab)
                                    <tr class="kitab-row" data-id="{{ $kitab->id_kitab }}" data-title="{{ strtolower($kitab->judul . ' ' . $kitab->penulis) }}">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" class="form-check-input kitab-checkbox" value="{{ $kitab->id_kitab }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <img src="{{ asset('cover/' . $kitab->cover) }}" class="rounded shadow-sm" style="width: 45px; height: 65px; object-fit: cover;">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $kitab->judul }}</h6>
                                                    <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $kitab->penulis }}</small>
                                                    <div class="small text-muted mt-1" id="transcript-meta-{{ $kitab->id_kitab }}">
                                                        <i class="bi bi-file-earmark-text me-1"></i>Transcript: {{ (int) ($kitab->transcript_segments_count ?? 0) }} segmen
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-light-primary text-primary border border-primary-subtle rounded-pill px-3">
                                                {{ $kitab->kategori }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="d-flex justify-content-center gap-3">
                                                <span class="text-muted small" title="Views"><i class="bi bi-eye me-1"></i>{{ $kitab->views ?? 0 }}</span>
                                                <span class="text-muted small" title="Downloads"><i class="bi bi-download me-1"></i>{{ $kitab->downloads ?? 0 }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.kitab.edit', ['id_kitab' => $kitab->id_kitab]) }}" class="btn btn-sm btn-light-warning text-warning shadow-sm" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-light-success text-success shadow-sm btn-import-transcript" data-id="{{ $kitab->id_kitab }}" data-title="{{ $kitab->judul }}" data-bs-toggle="tooltip" title="Generate Transcript">
                                                    <i class="bi bi-magic"></i>
                                                </button>
                                                <button type="button" onclick="DeleteKitab('{{ $kitab->id_kitab }}')" class="btn btn-sm btn-light-danger text-danger shadow-sm btn-delete-kitab" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <a href="{{ route('admin.kitab.show', ['id_kitab' => $kitab->id_kitab]) }}" class="btn btn-sm btn-light-info text-info shadow-sm" data-bs-toggle="tooltip" title="Detail">
                                                    <i class="bi bi-info-circle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Belum ada kitab berbahasa Indonesia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB ARAB --}}
                <div class="tab-pane fade" id="pills-arab" role="tabpanel">
                     <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="selectAllArab" title="Pilih Semua">
                                    </th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold" style="width: 40%;">Info Kitab</th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold">Kategori</th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-center">Statistik</th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-arab">
                                @forelse($kitabs->whereIn('bahasa', ['Arab', 'arab']) as $kitab)
                                    <tr class="kitab-row" data-id="{{ $kitab->id_kitab }}" data-title="{{ strtolower($kitab->judul . ' ' . $kitab->penulis) }}">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" class="form-check-input kitab-checkbox" value="{{ $kitab->id_kitab }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <img src="{{ asset('cover/' . $kitab->cover) }}" class="rounded shadow-sm" style="width: 45px; height: 65px; object-fit: cover;">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $kitab->judul }}</h6>
                                                    <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $kitab->penulis }}</small>
                                                    <div class="small text-muted mt-1" id="transcript-meta-{{ $kitab->id_kitab }}">
                                                        <i class="bi bi-file-earmark-text me-1"></i>Transcript: {{ (int) ($kitab->transcript_segments_count ?? 0) }} segmen
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-light-success text-success border border-success-subtle rounded-pill px-3">
                                                {{ $kitab->kategori }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="d-flex justify-content-center gap-3">
                                                <span class="text-muted small" title="Views"><i class="bi bi-eye me-1"></i>{{ $kitab->views ?? 0 }}</span>
                                                <span class="text-muted small" title="Downloads"><i class="bi bi-download me-1"></i>{{ $kitab->downloads ?? 0 }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.kitab.edit', ['id_kitab' => $kitab->id_kitab]) }}" class="btn btn-sm btn-light-warning text-warning shadow-sm" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-light-success text-success shadow-sm btn-import-transcript" data-id="{{ $kitab->id_kitab }}" data-title="{{ $kitab->judul }}" data-bs-toggle="tooltip" title="Generate Transcript">
                                                    <i class="bi bi-magic"></i>
                                                </button>
                                                <button type="button" onclick="DeleteKitab('{{ $kitab->id_kitab }}')" class="btn btn-sm btn-light-danger text-danger shadow-sm btn-delete-kitab" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <a href="{{ route('admin.kitab.show', ['id_kitab' => $kitab->id_kitab]) }}" class="btn btn-sm btn-light-info text-info shadow-sm" data-bs-toggle="tooltip" title="Detail">
                                                    <i class="bi bi-info-circle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Belum ada kitab berbahasa Arab.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    const importTranscriptUrlTemplate = @json(route('admin.kitab.import-transcript', ['id_kitab' => '__KITAB_ID__']));
    const bulkImportTranscriptUrl = @json(route('admin.kitab.bulk-import-transcripts'));

    // Search Functionality
    document.getElementById('searchKitab').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let items = document.querySelectorAll('.kitab-row');
        
        items.forEach(function(item) {
            let title = item.getAttribute('data-title');
            if (title.includes(filter)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Bulk Actions
    function updateBulkBar() {
        const checked = document.querySelectorAll('.kitab-checkbox:checked');
        const bar = document.getElementById('bulkActionBar');
        const countEl = document.getElementById('selectedCount');
        if (countEl) countEl.textContent = checked.length;
        if (bar) {
            bar.classList.toggle('d-none', checked.length === 0);
        }
    }

    document.querySelectorAll('.kitab-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    document.getElementById('selectAllIndo')?.addEventListener('change', function() {
        document.querySelectorAll('#table-indo .kitab-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
    document.getElementById('selectAllArab')?.addEventListener('change', function() {
        document.querySelectorAll('#table-arab .kitab-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    document.getElementById('btnBulkCancel')?.addEventListener('click', function() {
        document.querySelectorAll('.kitab-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAllIndo').checked = false;
        document.getElementById('selectAllArab').checked = false;
        updateBulkBar();
    });

    document.getElementById('btnBulkDelete')?.addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('.kitab-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Pilih minimal 1 kitab' });
            return;
        }
        Swal.fire({
            title: 'Hapus ' + ids.length + ' kitab?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('admin.kitab.bulk-delete') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids: ids.map(Number) })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        ids.forEach(id => {
                            const row = document.querySelector(`.kitab-row[data-id="${id}"]`);
                            if (row) row.remove();
                        });
                        document.getElementById('btnBulkCancel').click();
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message });
                        if (document.querySelectorAll('.kitab-row').length === 0) location.reload();
                    } else throw new Error(data.message || 'Gagal');
                })
                .catch(err => Swal.fire({ icon: 'error', title: 'Gagal', text: err.message }));
            }
        });
    });

    document.getElementById('btnBulkExport')?.addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('.kitab-checkbox:checked')).map(cb => cb.value);
        const url = ids.length > 0 
            ? "{{ route('admin.kitab.bulk-export') }}?ids=" + ids.join(',')
            : "{{ route('admin.kitab.bulk-export') }}";
        window.open(url, '_blank', 'noopener');
    });

    document.querySelectorAll('.btn-import-transcript').forEach(btn => {
        btn.addEventListener('click', function() {
            importTranscript(this.dataset.id, this.dataset.title || 'Kitab');
        });
    });

    document.getElementById('btnBulkTranscript')?.addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('.kitab-checkbox:checked')).map(cb => Number(cb.value));
        if (ids.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Pilih minimal 1 kitab' });
            return;
        }

        bulkImportTranscripts(ids, true);
    });

    document.getElementById('btnImportAllTranscript')?.addEventListener('click', function() {
        bulkImportTranscripts([], true);
    });

    function importTranscript(idKitab, title) {
        Swal.fire({
            title: 'Generate transcript?',
            text: `PDF untuk "${title}" akan diproses ulang menjadi transcript halaman dan bab.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Generate',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Memproses transcript...',
                text: 'Mohon tunggu, PDF sedang diekstrak.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                customClass: { popup: 'rounded-4 border-0' }
            });

            fetch(importTranscriptUrlTemplate.replace('__KITAB_ID__', idKitab), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal memproses transcript');
                }

                const meta = document.getElementById(`transcript-meta-${idKitab}`);
                if (meta && data.data) {
                    meta.innerHTML = `<i class="bi bi-file-earmark-text me-1"></i>Transcript: ${data.data.total_segments} segmen`;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Transcript berhasil dibuat',
                    html: `${data.data.page_segments} halaman, ${data.data.chapter_segments} bab, total ${data.data.total_segments} segmen.`,
                    confirmButtonColor: '#198754'
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message || 'Transcript belum bisa dibuat.',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
    }

    function bulkImportTranscripts(ids, force) {
        const targetLabel = ids.length > 0 ? `${ids.length} kitab terpilih` : 'semua kitab';

        Swal.fire({
            title: 'Generate transcript massal?',
            text: `Sistem akan memproses ${targetLabel}. Langkah ini cocok untuk merapikan kitab yang sudah pernah diinput.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Proses Sekarang',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Memproses semua transcript...',
                text: 'PDF sedang dianalisis satu per satu.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                customClass: { popup: 'rounded-4 border-0' }
            });

            fetch(bulkImportTranscriptUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ids,
                    force
                })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok && response.status !== 207) {
                    throw new Error(data.message || 'Gagal memproses transcript massal.');
                }

                if (data.data && Array.isArray(data.data.successes)) {
                    data.data.successes.forEach(item => {
                        const meta = document.getElementById(`transcript-meta-${item.id_kitab}`);
                        if (meta) {
                            meta.innerHTML = `<i class="bi bi-file-earmark-text me-1"></i>Transcript: ${item.total_segments} segmen`;
                        }
                    });
                }

                Swal.fire({
                    icon: data.data.failed_count > 0 ? 'warning' : 'success',
                    title: 'Import transcript selesai',
                    html: `Berhasil: ${data.data.success_count}<br>Gagal: ${data.data.failed_count}<br>Skip: ${data.data.skipped_count}`,
                    confirmButtonColor: '#198754'
                }).then(() => {
                    if (data.data.failed_count === 0) {
                        updateBulkBar();
                    }
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message || 'Transcript massal belum bisa diproses.',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
    }

    function DeleteKitab(id_kitab) {
        Swal.fire({
            title: 'Hapus Kitab?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'btn btn-danger btn-lg px-4',
                cancelButton: 'btn btn-secondary btn-lg px-4 ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Loading State
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                     customClass: { popup: 'rounded-4 border-0' }
                });

                fetch(`{{ route('admin.kitab.destroy', ['id_kitab' => '__KITAB_ID__']) }}`.replace('__KITAB_ID__', id_kitab), {
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error(data.message || 'Gagal menghapus data');

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                         customClass: { popup: 'rounded-4 border-0' }
                    });

                    // Remove item from DOM with animation
                    const deletedRow = document.querySelector(`.kitab-row[data-id="${id_kitab}"]`);
                    if (deletedRow) {
                        deletedRow.style.transition = 'all 0.4s ease';
                        deletedRow.style.opacity = '0';
                        deletedRow.style.transform = 'scale(0.95)';
                        setTimeout(() => deletedRow.remove(), 400);
                    }
                    setTimeout(() => {
                        if (document.querySelectorAll('.kitab-row').length === 0) location.reload();
                    }, 500);

                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message || 'Terjadi kesalahan koneksi',
                        confirmButtonText: 'OK',
                        customClass: {
                             popup: 'rounded-4 border-0',
                             confirmButton: 'btn btn-primary px-4'
                        },
                         buttonsStyling: false
                    });
                });
            }
        });
    }
</script>

<style>
    /* Card Animations */
    .animate-card-left { animation: slideInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateX(-20px); }
    .animate-card-right { animation: slideInRight 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateX(20px); animation-delay: 0.1s; }
    
    @keyframes slideInLeft { to { opacity: 1; transform: translateX(0); } }
    @keyframes slideInRight { to { opacity: 1; transform: translateX(0); } }

    /* Hover Effects */
    .hover-shadow-sm:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; transform: translateY(-2px); border-color: var(--primary-color) !important; }
    .transition-all { transition: all 0.3s ease; }

    /* Custom Scrollbar for dropdowns if needed */
    .dropdown-menu { border-radius: 0.5rem; }

    /* Badge tweaks */
    .text-xs { font-size: 0.75rem; }
    
    /* Decoration */
    .animate-pulse { animation: pulse-soft 3s infinite; }
    @keyframes pulse-soft {
        0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
    }
</style>
@endsection
