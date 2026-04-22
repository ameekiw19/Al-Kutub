@extends('TemplateUser')

@section('konten')

<div class="page-header-bar">
    <div class="page-header-left">
        <i class="fas fa-layer-group page-header-icon"></i>
        <h2 class="page-header-title">Kategori Kitab</h2>
    </div>
    <div class="filter-wrapper">
        <label for="filter-bahasa"><i class="fas fa-language"></i></label>
        <select id="filter-bahasa" class="filter-select">
            <option value="">Semua Bahasa</option>
            <option value="arab">Arab</option>
            <option value="indonesia">Indonesia</option>
        </select>
    </div>
</div>

{{-- Swipeable Category Buttons --}}
<div class="category-chips">
    <button class="chip active" data-kategori="">Semua</button>
    @foreach($kategori as $kat)
    <button class="chip" data-kategori="{{ $kat }}">{{ ucwords(str_replace('-', ' ', $kat)) }}</button>
    @endforeach
</div>

{{-- Grid Kitab --}}
<div class="katalog-grid" id="books-container">
    @if($kitab->count() > 0)
        @foreach($kitab as $item)
            <div class="katalog-card" onclick="window.location.href='{{ route('kitab.view', $item->id_kitab) }}'">
                <div class="katalog-cover">
                    @if($item->cover)
                        <img src="{{ asset('cover/'.$item->cover) }}" alt="{{ $item->judul }}">
                    @else
                        <div class="katalog-placeholder"><i class="fas fa-book"></i></div>
                    @endif
                </div>
                <h3 class="katalog-title">{{ \Illuminate\Support\Str::limit($item->judul, 35) }}</h3>
                <p class="katalog-author">{{ $item->penulis }}</p>
                <div class="katalog-footer">
                    <span class="katalog-badge">{{ ucwords(str_replace('-', ' ', $item->kategori)) }}</span>
                    <button class="katalog-bm bookmark-btn {{ in_array($item->id_kitab, $bookmarkedIds ?? []) ? 'active' : '' }}" data-id="{{ $item->id_kitab }}" onclick="event.stopPropagation();">
                        <i class="{{ in_array($item->id_kitab, $bookmarkedIds ?? []) ? 'fas' : 'far' }} fa-bookmark"></i>
                    </button>
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state-box">
            <div style="font-size: 3rem; margin-bottom: 12px;">📖</div>
            <h3>Belum ada kitab</h3>
            <p>Coba pilih kategori lain</p>
        </div>
    @endif
</div>

@if(isset($kitab) && $kitab->hasPages())
<div class="pagination-wrap">
    {{ $kitab->links() }}
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const kategoriButtons = document.querySelectorAll('.chip');
        const bahasaSelect = document.querySelector('#filter-bahasa');
        const container = document.querySelector('#books-container');
        let selectedKategori = '';
        let selectedBahasa = '';

        function toggleBookmarkUI(btn, isAdded) {
            const icon = btn.querySelector('i');
            if (isAdded) {
                btn.classList.add('active');
                icon.className = 'fas fa-bookmark';
            } else {
                btn.classList.remove('active');
                icon.className = 'far fa-bookmark';
            }
        }

        container.addEventListener('click', async (e) => {
            const btn = e.target.closest('.bookmark-btn');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();

            const id = btn.dataset.id;
            const isActive = btn.classList.contains('active');
            toggleBookmarkUI(btn, !isActive);

            try {
                const res = await fetch(`/bookmark/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                });
                if (res.ok) {
                    const data = await res.json();
                    toggleBookmarkUI(btn, data.status === 'added');
                } else {
                    toggleBookmarkUI(btn, isActive);
                    if (res.status === 401) alert('Silakan login terlebih dahulu.');
                }
            } catch (err) {
                console.error('Bookmark error:', err);
                toggleBookmarkUI(btn, isActive);
            }
        });

        async function fetchKitab() {
            const url = `/kategori/filter?kategori=${selectedKategori}&bahasa=${selectedBahasa}`;
            try {
                const res = await fetch(url);
                const data = await res.json();
                container.innerHTML = '';

                if (!data.kitab || data.kitab.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state-box">
                            <div style="font-size: 3rem; margin-bottom: 12px;">📖</div>
                            <h3>Tidak ada kitab ditemukan</h3>
                            <p>Coba filter lain</p>
                        </div>`;
                    return;
                }

                data.kitab.forEach(k => {
                    const isBookmarked = data.bookmarkedIds && data.bookmarkedIds.includes(k.id_kitab);
                    const html = `
                        <div class="katalog-card" onclick="window.location.href='/kitab/view/${k.id_kitab}'">
                            <div class="katalog-cover">
                                <img src="/cover/${k.cover || 'default.png'}" alt="${k.judul}">
                            </div>
                            <h3 class="katalog-title">${k.judul}</h3>
                            <p class="katalog-author">${k.penulis}</p>
                            <div class="katalog-footer">
                                <span class="katalog-badge" style="text-transform:capitalize">${k.kategori.replace(/-/g, ' ')}</span>
                                <button class="katalog-bm bookmark-btn ${isBookmarked ? 'active' : ''}" data-id="${k.id_kitab}" onclick="event.stopPropagation();">
                                    <i class="${isBookmarked ? 'fas' : 'far'} fa-bookmark"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    container.innerHTML += html;
                });
            } catch (error) {
                console.error('Error fetching:', error);
                container.innerHTML = `<p style="text-align:center; color:red; grid-column: 1/-1;">Gagal memuat data.</p>`;
            }
        }

        kategoriButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                kategoriButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedKategori = btn.dataset.kategori;
                fetchKitab();
            });
        });

        bahasaSelect.addEventListener('change', (e) => {
            selectedBahasa = e.target.value;
            fetchKitab();
        });
    });
</script>

<style>
    .page-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .page-header-icon {
        color: var(--accent-color);
        font-size: 22px;
    }
    .page-header-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-color);
        margin: 0;
    }
    .filter-wrapper { display: flex; align-items: center; gap: 8px; }
    .filter-select {
        padding: 8px 14px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--card-bg);
        color: var(--text-color);
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 500;
        outline: none;
    }
    .filter-select:focus { border-color: var(--primary-color); }

    /* Chips */
    .category-chips {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 16px;
        margin-bottom: 20px;
        scrollbar-width: none;
    }
    .category-chips::-webkit-scrollbar { display: none; }
    .chip {
        padding: 8px 20px;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        background: var(--card-bg);
        color: var(--text-color);
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
        cursor: pointer;
        transition: 0.2s;
        font-family: 'Poppins', sans-serif;
    }
    .chip:hover, .chip.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(27, 94, 59, 0.2);
    }

    /* Grid */
    .katalog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
    }
    .katalog-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 12px;
        cursor: pointer;
        transition: 0.25s;
        display: flex;
        flex-direction: column;
    }
    .katalog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-color: var(--primary-light);
    }
    .katalog-cover {
        width: 100%;
        height: 180px;
        border-radius: 12px;
        overflow: hidden;
        background: #eef2f5;
        margin-bottom: 10px;
    }
    .katalog-cover img { width: 100%; height: 100%; object-fit: cover; }
    .katalog-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white; font-size: 2rem;
    }
    .katalog-title {
        font-size: 14px; font-weight: 700; color: var(--text-color);
        line-height: 1.3; margin-bottom: 4px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .katalog-author {
        font-size: 12px; color: var(--light-text); margin-bottom: 8px; flex-grow: 1;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .katalog-footer {
        display: flex; justify-content: space-between; align-items: center; margin-top: auto;
    }
    .katalog-badge {
        font-size: 10px; font-weight: 600; padding: 4px 8px;
        border-radius: 8px; background: #F0F7F3; color: var(--primary-color);
        text-transform: capitalize;
    }
    body.dark-mode .katalog-badge { background: rgba(27,94,59,0.15); }
    .katalog-bm {
        background: none; border: none; color: var(--light-text);
        font-size: 16px; cursor: pointer; padding: 0; transition: 0.2s;
    }
    .katalog-bm.active { color: var(--primary-color); }

    .empty-state-box {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 20px;
        background: var(--card-bg);
        border-radius: 16px;
        border: 1px dashed var(--border-color);
        color: var(--light-text);
    }
    .empty-state-box h3 { color: var(--text-color); margin-bottom: 4px; }

    .pagination-wrap { margin-top: 30px; display: flex; justify-content: center; }

    @media (max-width: 480px) {
        .katalog-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .katalog-cover { height: 140px; }
    }
</style>

@endsection