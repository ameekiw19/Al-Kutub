@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark">Tambah Kitab Baru</h3>
            <p class="text-subtitle text-muted mb-0">Lengkapi detail informasi untuk publikasi kitab baru.</p>
        </div>
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kitab.index') }}">Kitab</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section">
    <form id="kitabForm" enctype="multipart/form-data" class="animate-form">
        @csrf
        
        <div class="card border-0 shadow-sm animate-fade-in">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-journal-plus me-2 text-primary"></i>Formulir Tambah Kitab</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- LEFT COLUMN: Media Uploads --}}
                    <div class="col-lg-4">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-dark mb-3 small text-uppercase">Media Pustaka</h6>
                            
                            {{-- Cover Upload --}}
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">SAMPUL KITAB</label>
                                <div class="filepond-wrapper">
                                    <input type="file" name="cover" class="tambah-kitab-cover" accept="image/*" required>
                                </div>
                                <p class="text-muted text-center small mt-2 fst-italic">
                                    Format: JPG/PNG, Rasio potret disarankan.
                                </p>
                            </div>

                            <hr class="border-secondary opacity-10">

                            {{-- PDF Upload --}}
                            <div class="mb-2">
                                <label class="form-label text-muted small fw-bold">FILE PDF</label>
                                <input type="file" name="file_pdf" class="tambah-kitab-pdf" accept="application/pdf" required>
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
                                    <input type="text" class="form-control form-control-lg" id="judul" name="judul" placeholder="Masukkan judul lengkap..." required style="padding-left: 48px; font-weight: 600;">
                                    <i class="bi bi-book position-absolute text-muted fs-5" style="left: 16px; top: 50%; transform: translateY(-50%);"></i>
                                </div>
                            </div>

                            {{-- Author & Language --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-muted small">PENULIS / PENGARANG</label>
                                    <div class="input-wrapper position-relative">
                                        <input type="text" class="form-control" id="penulis" name="penulis" placeholder="Nama penulis..." required style="padding-left: 45px;">
                                        <i class="bi bi-person position-absolute text-muted" style="left: 16px; top: 50%; transform: translateY(-50%);"></i>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-muted small">BAHASA</label>
                                    <div class="input-wrapper position-relative">
                                        <select class="form-select" id="bahasa" name="bahasa" required style="padding-left: 45px;">
                                            <option value="" disabled selected>Pilih Bahasa</option>
                                            <option value="indonesia">Bahasa Indonesia</option>
                                            <option value="arab">Bahasa Arab</option>
                                            <option value="inggris">Bahasa Inggris</option>
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
                                        <input type="radio" class="btn-check" name="kategori" id="cat-{{ $key }}" value="{{ $key }}" required>
                                        <label class="btn btn-outline-primary rounded-pill px-3 py-1 small fw-bold" for="cat-{{ $key }}">{{ $val }}</label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted small">DESKRIPSI / SINOPSIS</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" placeholder="Tuliskan ringkasan isi kitab..." required></textarea>
                                <div class="text-end mt-1">
                                    <small class="text-muted"><span id="charCount">0</span> karakter</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-light py-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.kitab.index') }}" class="btn btn-link text-muted text-decoration-none fw-bold small">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5 fw-bold" id="submitBtn">
                        <i class="bi bi-send-fill me-2"></i> <span class="btn-text">Publikasikan</span>
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
    
    .animate-card-left { opacity: 0; transform: translateX(-30px); animation: fadeInSide 0.6s ease forwards; }
    .animate-card-right { opacity: 0; transform: translateX(30px); animation: fadeInSide 0.6s ease forwards; animation-delay: 0.2s; }
    @keyframes fadeInSide { to { opacity: 1; transform: translateX(0); } }

    /* FilePond Tweaks */
    .filepond--panel-root { background-color: #f8fafc; }
    .filepond--drop-label { color: #64748b; font-weight: 600; }
    
    /* Custom Radio Buttons for Categories */
    .btn-check:checked + .btn-outline-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character counter
        const textarea = document.getElementById('deskripsi');
        const countSpan = document.getElementById('charCount');
        textarea.addEventListener('input', () => {
            countSpan.textContent = textarea.value.length;
        });

        // Initialize FilePond untuk form Tambah Kitab (class unik agar tidak konflik dengan filepond.js global)
        FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize, FilePondPluginFileValidateType);
        
        const coverInput = document.querySelector('.tambah-kitab-cover');
        if (coverInput) {
            FilePond.create(coverInput, {
                labelIdle: '<i class="bi bi-upload fs-2 d-block mb-2"></i> Upload Sampul',
                imagePreviewHeight: 250,
                stylePanelLayout: 'compact',
                styleLoadIndicatorPosition: 'center bottom',
                styleProgressIndicatorPosition: 'right bottom',
                styleButtonRemoveItemPosition: 'left bottom',
                styleButtonProcessItemPosition: 'right bottom',
                credits: null,
                allowImagePreview: true,
                acceptedFileTypes: ['image/png', 'image/jpg', 'image/jpeg'],
                maxFileSize: '2MB',
                storeAsFile: true,
            });
        }

        const pdfInput = document.querySelector('.tambah-kitab-pdf');
        if (pdfInput) {
            FilePond.create(pdfInput, {
                labelIdle: '<i class="bi bi-file-pdf fs-2 d-block mb-2"></i> Pilih File PDF',
                credits: null,
                allowImagePreview: false,
                acceptedFileTypes: ['application/pdf'],
                maxFileSize: '10MB',
                storeAsFile: true,
            });
        }

        // AJAX Submission
        document.getElementById('kitabForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const loader = document.getElementById('btnLoader');
            const text = btn.querySelector('.btn-text');

            // Client-side validation (FilePond menyimpan file di input asli)
            const coverInput = document.querySelector('input[name="cover"]');
            const pdfInput = document.querySelector('input[name="file_pdf"]');
            const coverFile = coverInput && coverInput.files ? coverInput.files[0] : null;
            const pdfFile = pdfInput && pdfInput.files ? pdfInput.files[0] : null;
            
            // Validate text fields
            const judul = document.querySelector('input[name="judul"]').value.trim();
            const penulis = document.querySelector('input[name="penulis"]').value.trim();
            const deskripsi = document.querySelector('textarea[name="deskripsi"]').value.trim();
            const kategori = document.querySelector('input[name="kategori"]:checked');
            const bahasa = document.querySelector('select[name="bahasa"]').value;
            
            console.log('Form validation:', {
                judul, penulis, deskripsi, 
                kategori: kategori ? kategori.value : null,
                bahasa,
                hasCover: !!coverFile,
                hasPdf: !!pdfFile
            });
            
            if (!judul || !penulis || !deskripsi || !kategori || !bahasa) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: `
                        <div style="text-align: left;">
                            ${!judul ? '• Judul kitab harus diisi<br>' : ''}
                            ${!penulis ? '• Penulis harus diisi<br>' : ''}
                            ${!deskripsi ? '• Deskripsi harus diisi<br>' : ''}
                            ${!kategori ? '• Kategori harus dipilih<br>' : ''}
                            ${!bahasa ? '• Bahasa harus dipilih<br>' : ''}
                        </div>
                    `,
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            if (!coverFile || !pdfFile) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'File PDF dan Cover harus diupload!',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Check file sizes
            if (pdfFile.size > 10 * 1024 * 1024) { // 10MB
                Swal.fire({
                    icon: 'error', 
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file PDF maksimal 10MB',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (coverFile.size > 2 * 1024 * 1024) { // 2MB
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar', 
                    text: 'Ukuran file cover maksimal 2MB',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Check file types
            if (!pdfFile.type.includes('pdf')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Tidak Valid',
                    text: 'File PDF harus berekstensi .pdf',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (!coverFile.type.includes('image/')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Tidak Valid',
                    text: 'File cover harus berupa gambar (JPG/PNG)',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            btn.disabled = true;
            text.style.opacity = '0.5';
            loader.style.display = 'inline-block';

            const formData = new FormData(this);

            // Debug FormData contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Add file info for debugging
            console.log('Uploading files:', {
                pdf: pdfFile.name,
                pdf_size: (pdfFile.size / 1024 / 1024).toFixed(2) + 'MB',
                cover: coverFile.name,
                cover_size: (coverFile.size / 1024 / 1024).toFixed(2) + 'MB'
            });

            fetch("{{ route('admin.kitab.store') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                
                console.log('Server response:', data);
                
                if (!response.ok) {
                    throw data;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Kitab baru telah dipublikasikan dan notifikasi telah dikirim ke pengguna.',
                    confirmButtonColor: '#44A194',
                    customClass: { popup: 'rounded-4 border-0' }
                }).then(() => {
                    window.location.href = "{{ route('admin.kitab.index') }}";
                });
            })
            .catch(error => {
                console.error('Upload error:', error);
                
                btn.disabled = false;
                text.style.opacity = '1';
                loader.style.display = 'none';

                let errorMsg = error.message || 'Terjadi kesalahan sistem.';
                
                if (error.errors) {
                    // Format validation errors
                    const errorMessages = [];
                    Object.keys(error.errors).forEach(key => {
                        const messages = error.errors[key];
                        if (Array.isArray(messages)) {
                            errorMessages.push(...messages);
                        } else {
                            errorMessages.push(messages);
                        }
                    });
                    errorMsg = errorMessages.join('<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Upload Gagal',
                    html: errorMsg,
                    confirmButtonColor: '#dc3545',
                    customClass: { popup: 'rounded-4 border-0' }
                });
            });
        });
    });
</script>
@endsection
