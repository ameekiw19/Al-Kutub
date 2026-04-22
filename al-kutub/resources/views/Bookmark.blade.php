@extends('TemplateUser')

@section('konten')

<div class="page-header-bar">
    <div class="page-header-left">
        <i class="fas fa-bookmark page-header-icon"></i>
        <h2 class="page-header-title">Bookmark Saya</h2>
    </div>
    
    @if ($bookmarks && $bookmarks->count() > 0)
    <form action="{{ route('bookmarks.clear') }}" method="POST" class="clear-bookmarks-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-danger-outline clear-bookmarks-btn">
            <i class="fas fa-trash-alt"></i> Hapus Semua
        </button>
    </form>
    @endif
</div>

<div class="bm-list">
    @if ($bookmarks && $bookmarks->count() > 0)
        @foreach ($bookmarks as $item)
            <div class="bm-card" onclick="window.location.href='{{ route('kitab.view', $item->kitab->id_kitab) }}'">
                <div class="bm-cover">
                    @if($item->kitab->cover)
                        <img src="{{ asset('cover/' . $item->kitab->cover) }}" alt="{{ $item->kitab->judul }}">
                    @else
                        <div class="bm-placeholder"><i class="fas fa-book"></i></div>
                    @endif
                </div>
                
                <div class="bm-info">
                    <h3 class="bm-title">{{ $item->kitab->judul }}</h3>
                    <p class="bm-author">{{ $item->kitab->penulis }}</p>
                    <p class="bm-meta">{{ $item->kitab->kategori }} • {{ $item->kitab->bahasa ?? 'Arab' }}</p>
                </div>

                <div class="bm-actions" onclick="event.stopPropagation();">
                    <form action="{{ route('kitab.bookmark.delete', $item->id_kitab) }}" method="POST" class="delete-bookmark-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bm-delete-btn" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <i class="fas fa-chevron-right bm-arrow"></i>
            </div>
        @endforeach
    @else
        <div class="empty-state-box">
            <div style="font-size: 3rem; margin-bottom: 12px;">📑</div>
            <h3>Belum ada bookmark</h3>
            <p>Simpan kitab favorit Anda di sini</p>
            <a href="{{ route('home') }}" class="btn-action-primary" style="margin-top: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-compass"></i> Jelajahi Kitab
            </a>
        </div>
    @endif
</div>

<style>
    .page-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .page-header-left { display: flex; align-items: center; gap: 10px; }
    .page-header-icon { color: var(--accent-color); font-size: 22px; }
    .page-header-title { font-size: 20px; font-weight: 800; color: var(--text-color); margin: 0; }

    .btn-danger-outline {
        background: #FEF2F2; border: 1.5px solid #EF4444; color: #DC2626;
        padding: 8px 16px; border-radius: 14px; font-weight: 600; font-size: 13px;
        cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 6px;
        font-family: 'Poppins', sans-serif;
    }
    .btn-danger-outline:hover { background: #EF4444; color: white; transform: translateY(-2px); }

    .bm-list { display: flex; flex-direction: column; gap: 12px; }

    .bm-card {
        display: flex; align-items: center;
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 16px; padding: 12px;
        cursor: pointer; transition: 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .bm-card:hover {
        border-color: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .bm-cover {
        width: 68px; height: 94px; border-radius: 10px;
        background: #eef2f5; overflow: hidden; margin-right: 14px; flex-shrink: 0;
    }
    .bm-cover img { width: 100%; height: 100%; object-fit: cover; }
    .bm-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); color: white; font-size: 1.5rem;
    }

    .bm-info { flex-grow: 1; overflow: hidden; }
    .bm-title {
        font-size: 15px; font-weight: 700; color: var(--text-color);
        margin-bottom: 4px; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .bm-author {
        font-size: 13px; color: var(--light-text); margin-bottom: 6px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .bm-meta { font-size: 12px; color: var(--primary-color); font-weight: 500; }

    .bm-actions { margin: 0 8px; }
    .bm-delete-btn {
        background: none; border: none; color: #EF4444; font-size: 14px;
        cursor: pointer; width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; transition: 0.2s;
    }
    .bm-delete-btn:hover { background: #FEE2E2; }
    .bm-arrow { color: var(--light-text); font-size: 14px; }

    .empty-state-box {
        text-align: center; padding: 50px 20px;
        background: var(--card-bg); border-radius: 16px;
        border: 1px dashed var(--border-color); color: var(--light-text);
    }
    .empty-state-box h3 { color: var(--text-color); margin-bottom: 4px; }

    .btn-action-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white; border: none; border-radius: 14px;
        padding: 10px 20px; font-weight: 600; font-size: 14px;
        cursor: pointer; transition: 0.3s; font-family: 'Poppins', sans-serif;
    }
    .btn-action-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(27,94,59,0.2); color: white; }

    @media (max-width: 480px) {
        .bm-cover { width: 56px; height: 78px; }
        .bm-title { font-size: 14px; }
        .bm-author { font-size: 12px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-bookmark-form');
        if(deleteForms) {
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Hapus bookmark?',
                        text: 'Yakin ingin menghapus kitab ini dari bookmark?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        }

        const clearBtn = document.querySelector('.clear-bookmarks-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Hapus semua?',
                    text: 'Semua bookmark akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus Semua',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endsection
