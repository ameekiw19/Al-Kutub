@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark">Edit Detail Kitab</h3>
            <p class="text-subtitle text-muted mb-0">Perbarui informasi dan file untuk "{{ $kitab->judul }}".</p>
        </div>
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kitab.index') }}">Kitab</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section">
    <form id="kitabForm" enctype="multipart/form-data" class="animate-form">
        @csrf
        <input type="hidden" name="id_kitab" value="{{ $kitab->id_kitab }}">

        <div class="card border-0 shadow-sm animate-fade-in">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Kitab</h5>
                    <span class="badge bg-light text-muted border px-3">ID: #{{ $kitab->id_kitab }}</span>
                </div>
            </div>
            
            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- LEFT COLUMN: Media Updates --}}
                    <div class="col-lg-4">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-dark mb-3 small text-uppercase">Media Pustaka</h6>
                            
                            {{-- Cover Update --}}
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">SAMPUL SAAT INI</label>
                                <div class="text-center mb-3">
                                    <div class="d-inline-block position-relative rounded-3 overflow-hidden shadow-sm border bg-white p-1">
                                        <img src="{{ asset('cover/'.$kitab->cover) }}" alt="Current Cover" class="img-fluid rounded" style="height: 180px; object-fit: cover;">
                                        <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-50 text-white text-xs py-1 text-center">
                                            Cover Aktif
                                        </div>
                                    </div>
                                </div>
                                <div class="filepond-wrapper">
                                    <input type="file" name="cover" class="image-preview-filepond" accept="image/*">
                                </div>
                                <p class="text-muted text-center small mt-2 fst-italic">
                                    Upload gambar baru untuk mengganti.
                                </p>
                            </div>

                            <hr class="border-secondary opacity-10">

                            {{-- PDF Update --}}
                            <div class="mb-2">
                                <label class="form-label text-muted small fw-bold">FILE PDF</label>
                                @if($kitab->file_pdf)
                                    <div class="d-flex align-items-center bg-white border p-2 rounded mb-3 shadow-sm">
                                        <div class="bg-danger bg-opacity-10 text-danger p-2 rounded me-2">
                                            <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <span class="d-block text-truncate small fw-bold text-dark" title="{{ $kitab->file_pdf }}">{{ $kitab->file_pdf }}</span>
                                            <a href="{{ asset('pdf/'.$kitab->file_pdf) }}" target="_blank" class="text-xs text-primary text-decoration-none">Lihat File</a>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" name="file_pdf" class="pdf-filepond" accept="application/pdf">
                                <p class="text-muted small text-center mt-2 mb-0">Upload PDF baru untuk mengganti.</p>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Metadata --}}
                    <div class="col-lg-8">
                        <div class="ps-lg-3">
                            <h6 class="fw-bold text-dark mb-3 small text-uppercase">Informasi Detail</h6>

                            {{-- Title --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted small">JUDUL KITAB</label>
                                <div class="input-wrapper position-relative">
                                    <input type="text" class="form-control form-control-lg fw-bold" id="judul" name="judul" value="{{ $kitab->judul }}" required style="padding-left: 48px;">
                                    <i class="bi bi-book position-absolute text-muted fs-5" style="left: 16px; top: 50%; transform: translateY(-50%);"></i>
                                </div>
                            </div>

                            {{-- Author & Language --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-muted small">PENULIS / PENGARANG</label>
                                    <div class="input-wrapper position-relative">
                                        <input type="text" class="form-control" id="penulis" name="penulis" value="{{ $kitab->penulis }}" required style="padding-left: 45px;">
                                        <i class="bi bi-person position-absolute text-muted" style="left: 16px; top: 50%; transform: translateY(-50%);"></i>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-muted small">BAHASA</label>
                                    <div class="input-wrapper position-relative">
                                        <select class="form-select" id="bahasa" name="bahasa" required style="padding-left: 45px;">
                                            <option value="indonesia" {{ $kitab->bahasa == 'indonesia' ? 'selected' : '' }}>Bahasa Indonesia</option>
                                            <option value="arab" {{ $kitab->bahasa == 'arab' ? 'selected' : '' }}>Bahasa Arab</option>
                                            <option value="inggris" {{ $kitab->bahasa == 'inggris' ? 'selected' : '' }}>Bahasa Inggris</option>
                                        </select>
                                        <i class="bi bi-translate position-absolute text-muted" style="left: 16px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Category --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted small">KATEGORI</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $categories = $categories ?? \App\Models\CategoryKatalog::getActiveForSelect();
                                    @endphp
                                    @foreach($categories as $key => $val)
                                        <input type="radio" class="btn-check" name="kategori" id="cat-{{ $key }}" value="{{ $key }}" {{ $kitab->kategori == $key ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-primary rounded-pill px-3 py-1 small fw-bold" for="cat-{{ $key }}">{{ $val }}</label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted small">DESKRIPSI / SINOPSIS</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" required>{{ $kitab->deskripsi }}</textarea>
                            </div>

                            <div class="mt-4 p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 small text-uppercase">Transcript Otomatis</h6>
                                        <p class="text-muted small mb-0">
                                            PDF kitab akan dipecah otomatis menjadi segmen ringkasan, bab, dan halaman.
                                        </p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-success" id="btnRegenerateTranscript">
                                            <i class="bi bi-magic me-1"></i> Generate Ulang
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-sm-6 col-lg-2">
                                        <div class="bg-white border rounded-3 p-3 h-100">
                                            <div class="text-muted small">Total Segmen</div>
                                            <div class="fs-4 fw-bold" id="transcriptCount">{{ $transcriptStats['count'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-2">
                                        <div class="bg-white border rounded-3 p-3 h-100">
                                            <div class="text-muted small">Ringkasan</div>
                                            <div class="fs-4 fw-bold" id="transcriptSummaryCount">{{ $transcriptStats['summary_segments'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-2">
                                        <div class="bg-white border rounded-3 p-3 h-100">
                                            <div class="text-muted small">Bab</div>
                                            <div class="fs-4 fw-bold" id="transcriptChapterCount">{{ $transcriptStats['chapter_segments'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-2">
                                        <div class="bg-white border rounded-3 p-3 h-100">
                                            <div class="text-muted small">Halaman</div>
                                            <div class="fs-4 fw-bold" id="transcriptPageCount">{{ $transcriptStats['page_segments'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="small text-uppercase text-muted">Preview Hasil Import</strong>
                                        <span class="badge bg-light text-dark border">{{ $transcriptStats['toc_segments'] ?? 0 }} daftar isi</span>
                                    </div>
                                    <div id="transcriptPreviewList">
                                        @forelse(($transcriptSegments ?? collect()) as $segment)
                                            <div class="bg-white border rounded-3 p-3 mb-2">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div>
                                                        <div class="fw-bold text-dark">
                                                            {{ $segment->title ?: ucfirst($segment->transcript_type) }}
                                                        </div>
                                                        <div class="text-muted small">
                                                            {{ strtoupper($segment->transcript_type) }}
                                                            @if($segment->page_start)
                                                                · halaman {{ $segment->page_start }}@if($segment->page_end && $segment->page_end !== $segment->page_start)-{{ $segment->page_end }}@endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">{{ optional($segment->updated_at)->diffForHumans() }}</small>
                                                </div>
                                                <div class="small text-muted mt-2">
                                                    <strong class="text-dark d-block mb-1">Terjemahan</strong>
                                                    {{ \Illuminate\Support\Str::limit($segment->content_translation ?: $segment->content, 220) }}
                                                </div>
                                                @if($segment->content_arabic)
                                                    <div class="small text-muted mt-2">
                                                        <strong class="text-dark d-block mb-1">Arab</strong>
                                                        <div dir="rtl" class="text-end">{{ \Illuminate\Support\Str::limit($segment->content_arabic, 180) }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="bg-white border rounded-3 p-3 text-muted small">
                                                Transcript belum dibuat. Klik tombol generate ulang untuk mulai mengimpor isi PDF.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light py-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.kitab.index') }}" class="btn btn-link text-muted text-decoration-none fw-bold small">
                        <i class="bi bi-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5 fw-bold" id="submitBtn">
                        <i class="bi bi-check-circle-fill me-2"></i> <span class="btn-text">Simpan Perubahan</span>
                        <div class="spinner-border spinner-border-sm ms-2" role="status" id="btnLoader" style="display: none;"></div>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<style>
    .focus-within-primary:focus-within {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 0.25rem rgba(68, 161, 148, 0.1) !important;
    }
    .transition-all { transition: all 0.3s ease; }
    .text-xs { font-size: 0.7rem; }
    
    .animate-card-left { opacity: 0; transform: translateX(-30px); animation: fadeInSide 0.6s ease forwards; }
    .animate-card-right { opacity: 0; transform: translateX(30px); animation: fadeInSide 0.6s ease forwards; animation-delay: 0.2s; }
    @keyframes fadeInSide { to { opacity: 1; transform: translateX(0); } }

    .filepond--panel-root { background-color: #f8fafc; }
    .bg-light-primary { background-color: rgba(68, 161, 148, 0.05); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const transcriptImportUrl = @json(route('admin.kitab.import-transcript', ['id_kitab' => $kitab->id_kitab]));

        // Initialize FilePond
        FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize, FilePondPluginFileValidateType);
        
        FilePond.create(document.querySelector('.image-preview-filepond'), {
            labelIdle: '<i class="bi bi-upload fs-4 d-block mb-1"></i> Ganti Sampul',
            imagePreviewHeight: 200,
            stylePanelLayout: 'compact',
        });

        FilePond.create(document.querySelector('.pdf-filepond'), {
            labelIdle: '<i class="bi bi-file-pdf fs-4 d-block mb-1"></i> Pilih PDF Baru',
        });

        // AJAX Submission
        document.getElementById('kitabForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const loader = document.getElementById('btnLoader');
            const text = btn.querySelector('.btn-text');

            btn.disabled = true;
            text.style.opacity = '0.5';
            loader.style.display = 'inline-block';

            const formData = new FormData(this);
            const id = "{{ $kitab->id_kitab }}";

            fetch(`{{ route('admin.kitab.update', ['id_kitab' => '__KITAB_ID__']) }}`.replace('__KITAB_ID__', id), {
                method: "POST",
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw data;

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Diperbarui!',
                    text: 'Data kitab telah berhasil diperbarui di sistem.',
                    confirmButtonColor: '#44A194',
                    customClass: { popup: 'rounded-4 border-0' }
                }).then(() => {
                    window.location.href = "{{ route('admin.kitab.index') }}";
                });
            })
            .catch(error => {
                btn.disabled = false;
                text.style.opacity = '1';
                loader.style.display = 'none';

                let errorMsg = error.message || 'Terjadi kesalahan sistem.';
                if (error.errors) {
                    errorMsg = Object.values(error.errors).flat().join('<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Update Gagal',
                    html: errorMsg,
                    confirmButtonColor: '#dc3545',
                    customClass: { popup: 'rounded-4 border-0' }
                });
            });
        });

        document.getElementById('btnRegenerateTranscript')?.addEventListener('click', function() {
            Swal.fire({
                title: 'Generate ulang transcript?',
                text: 'Segmen lama akan diganti dengan hasil import terbaru dari PDF.',
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

                fetch(transcriptImportUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error(data.message || 'Gagal generate transcript');

                    document.getElementById('transcriptCount').textContent = data.data.total_segments;
                    document.getElementById('transcriptSummaryCount').textContent = data.data.summary_segments;
                    document.getElementById('transcriptChapterCount').textContent = data.data.chapter_segments;
                    document.getElementById('transcriptPageCount').textContent = data.data.page_segments;

                    Swal.fire({
                        icon: 'success',
                        title: 'Transcript diperbarui',
                        html: `${data.data.page_segments} halaman, ${data.data.chapter_segments} bab, total ${data.data.total_segments} segmen.`,
                        confirmButtonColor: '#198754'
                    }).then(() => window.location.reload());
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: error.message || 'Transcript belum berhasil diperbarui.',
                        confirmButtonColor: '#dc3545'
                    });
                });
            });
        });

    });
</script>
@endsection
