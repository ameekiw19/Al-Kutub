@extends('TemplateUser')

@section('konten')

<div class="page-header-bar">
    <div class="page-header-left">
        <i class="fas fa-history page-header-icon"></i>
        <h2 class="page-header-title">Riwayat Bacaan</h2>
    </div>
    
    @if ($histories && $histories->count() > 0)
    <form action="{{ route('history.clear') }}" method="POST" class="clear-history-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-danger-outline clear-history-btn">
            <i class="fas fa-trash-alt"></i> Hapus Semua
        </button>
    </form>
    @endif
</div>

<div class="history-list">
    @if ($histories && $histories->count() > 0)
        @foreach ($histories as $history)
            @if ($history->kitab)
            <div class="hist-card" onclick="window.location.href='{{ route('kitab.read', ['id_kitab' => $history->kitab->id_kitab, 'resume' => 1]) }}'">
                <div class="hist-cover">
                    @if($history->kitab->cover)
                        <img src="{{ asset('cover/'.$history->kitab->cover) }}" alt="{{ $history->kitab->judul }}">
                    @else
                        <div class="hist-placeholder"><i class="fas fa-book"></i></div>
                    @endif
                </div>
                <div class="hist-info">
                    <h3 class="hist-title">{{ $history->kitab->judul }}</h3>
                    <p class="hist-author">{{ $history->kitab->penulis }}</p>
                    <div class="hist-meta">
                        <i class="far fa-clock"></i> {{ $history->last_read_at ? $history->last_read_at->diffForHumans() : 'Baru saja' }}
                    </div>
                </div>
                <div class="hist-action">
                    <span class="hist-continue">Lanjut <i class="fas fa-chevron-right"></i></span>
                </div>
            </div>
            @endif
        @endforeach
    @else
        <div class="empty-state-box">
            <div style="font-size: 3rem; margin-bottom: 12px;">📚</div>
            <h3>Belum ada riwayat</h3>
            <p>Mulai baca kitab pertama Anda</p>
            <a href="{{ route('home') }}" class="btn-action-primary" style="margin-top: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-compass"></i> Mulai Membaca
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

    .history-list { display: flex; flex-direction: column; gap: 12px; }

    .hist-card {
        display: flex; align-items: center;
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 16px; padding: 12px;
        cursor: pointer; transition: 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .hist-card:hover {
        border-color: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .hist-cover {
        width: 68px; height: 94px; border-radius: 10px;
        background: #eef2f5; overflow: hidden; margin-right: 14px; flex-shrink: 0;
    }
    .hist-cover img { width: 100%; height: 100%; object-fit: cover; }
    .hist-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white; font-size: 1.5rem;
    }

    .hist-info { flex-grow: 1; overflow: hidden; }
    .hist-title {
        font-size: 15px; font-weight: 700; color: var(--text-color);
        margin-bottom: 4px; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .hist-author {
        font-size: 13px; color: var(--light-text); margin-bottom: 6px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .hist-meta {
        font-size: 12px; color: var(--primary-color); font-weight: 600;
        display: flex; align-items: center; gap: 5px;
    }

    .hist-action { margin-left: 12px; flex-shrink: 0; }
    .hist-continue {
        color: var(--accent-color); font-size: 12px; font-weight: 700;
        display: flex; align-items: center; gap: 4px;
    }

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
        .hist-cover { width: 56px; height: 78px; }
        .hist-title { font-size: 14px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clearBtn = document.querySelector('.clear-history-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Hapus Semua Riwayat?',
                    text: 'Data bacaan Anda tidak akan bisa dikembalikan!',
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
        }
    });
</script>
@endsection
