@extends('TemplateUser')

@section('konten')

@php
    $totalBooks = \App\Models\Kitab::count();
    try {
        $totalCategories = \App\Models\CategoryKatalog::where('is_active', true)->count();

        if ($totalCategories === 0) {
            $totalCategories = \App\Models\Kitab::query()
                ->whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->distinct()
                ->count('kategori');
        }
    } catch (\Throwable $e) {
        $totalCategories = \App\Models\Kitab::query()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->count('kategori');
    }
    $totalReaders = \App\Models\Kitab::sum('views') ?: 0;
    
    function formatCompact($value) {
        if ($value >= 1000000) return round($value / 1000000, 1) . 'M';
        if ($value >= 1000) return round($value / 1000, 1) . 'K';
        return $value;
    }
@endphp

<!-- Android-Style Home Header -->
<div class="home-header">
    <div class="home-header-top">
        <div class="home-header-titles">
            <span class="home-date">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM') }}</span>
            <h1 class="home-greeting">Ahlan, {{ strtok(Auth::user()->username, " ") }}!</h1>
            <p class="home-bismillah">بِسْمِ اللَّهِ — Mari mulai belajar hari ini</p>
        </div>
        <a href="{{ route('account.edit') }}" class="home-avatar" title="Lihat Profil">
            {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
        </a>
    </div>

    <div class="home-stats-row">
        <div class="home-stat-card">
            <span class="stat-emoji">📚</span>
            <strong class="stat-value">{{ $totalBooks }}+</strong>
            <span class="stat-label">Total Kitab</span>
        </div>
        <div class="home-stat-card">
            <span class="stat-emoji">🗂️</span>
            <strong class="stat-value">{{ $totalCategories }}</strong>
            <span class="stat-label">Kategori</span>
        </div>
        <div class="home-stat-card">
            <span class="stat-emoji">👥</span>
            <strong class="stat-value">{{ formatCompact($totalReaders) }}</strong>
            <span class="stat-label">Pembaca</span>
        </div>
    </div>
</div>

<div class="home-content">

    <!-- Continue Reading Section (History) -->
    @if(isset($history) && count($history) > 0)
    <section class="home-section">
        <div class="section-header">
            <div class="section-title-wrap">
                <i class="fas fa-history section-icon"></i>
                <h2 class="section-title">Terakhir Dibaca</h2>
            </div>
            <a href="{{ route('history.index') }}" class="section-action">Lihat Semua <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="horizontal-scroll-container pb-2">
            @foreach ($history as $h)
                @if($h->kitab)
                <a href="{{ route('kitab.read', ['id_kitab' => $h->kitab->id_kitab, 'resume' => 1]) }}" class="history-item-card">
                    <div class="history-item-img">
                        @if($h->kitab->cover)
                            <img src="{{ asset('cover/'.$h->kitab->cover) }}" alt="{{ $h->kitab->judul }}">
                        @else
                            <div class="placeholder"><i class="fas fa-book"></i></div>
                        @endif
                    </div>
                    <div class="history-item-info">
                        <h3>{{ \Illuminate\Support\Str::limit($h->kitab->judul, 25) }}</h3>
                        <p>Lanjut membaca →</p>
                    </div>
                </a>
                @endif
            @endforeach
        </div>
    </section>
    @endif

    <!-- Popular Books Section (Grid View) -->
    <section class="home-section">
        <div class="section-header">
            <div class="section-title-wrap">
                <i class="fas fa-chart-line section-icon"></i>
                <h2 class="section-title">Kitab Populer</h2>
            </div>
        </div>
        
        <div class="horizontal-scroll-container pb-2 px-1">
            @forelse ($populer as $k)
                <div class="home-grid-card">
                    <a href="{{ route('kitab.view', $k->id_kitab) }}" class="card-link">
                        <div class="card-cover">
                            @if($k->cover)
                                <img src="{{ asset('cover/'.$k->cover) }}" alt="{{ $k->judul }}">
                            @else
                                <div class="placeholder"><i class="fas fa-book"></i></div>
                            @endif
                        </div>
                    </a>
                    
                    <a href="{{ route('kitab.view', $k->id_kitab) }}" class="card-title-link">
                        <h3 class="card-title">{{ $k->judul }}</h3>
                    </a>
                    <p class="card-author">{{ $k->penulis }}</p>
                    
                    <div class="card-actions">
                        <span class="card-views">{{ formatCompact($k->views ?? 0) }} pembaca</span>
                        <button class="btn-bookmark bookmark-btn {{ in_array($k->id_kitab, $bookmarkedIds ?? []) ? 'active' : '' }}" data-id="{{ $k->id_kitab }}">
                            @if(in_array($k->id_kitab, $bookmarkedIds ?? []))
                                <i class="fas fa-bookmark"></i>
                            @else
                                <i class="far fa-bookmark"></i>
                            @endif
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state">Belum ada kitab populer</div>
            @endforelse
        </div>
    </section>

    <!-- Rekomendasi 
    @if(isset($rekomendasi) && $rekomendasi->count() > 0)
    <section class="home-section">
        ... matching style above...
    </section>
    @endif
    -->

    <!-- All Books Section (List View) -->
    <section class="home-section">
        <div class="section-header">
            <div class="section-title-wrap">
                <i class="fas fa-book-open section-icon"></i>
                <h2 class="section-title">Semua Kitab</h2>
            </div>
        </div>

        <!-- Advanced Filter Toolbar -->
        <form method="GET" action="{{ route('home') }}" class="filter-toolbar" id="filterForm">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <div class="filter-row">
                <div class="filter-group">
                    <label for="filter-kategori"><i class="fas fa-layer-group"></i> Kategori</label>
                    <select name="kategori" id="filter-kategori" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua</option>
                        @foreach(['Aqidah','Tauhid','Fiqih','Hadis','Tafsir','Bahasa Arab','Qowaid Lughah'] as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-bahasa"><i class="fas fa-language"></i> Bahasa</label>
                    <select name="bahasa" id="filter-bahasa" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua</option>
                        @php
                            $languages = \App\Models\Kitab::select('bahasa')->distinct()->pluck('bahasa')->filter();
                        @endphp
                        @foreach($languages as $lang)
                            <option value="{{ $lang }}" {{ request('bahasa') == $lang ? 'selected' : '' }}>{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-sort"><i class="fas fa-sort"></i> Urutan</label>
                    <select name="sort" id="filter-sort" onchange="document.getElementById('filterForm').submit()">
                        <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Populer</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>A-Z</option>
                    </select>
                </div>
                
                @if(request('kategori') || request('bahasa') || request('penulis') || request('sort') != 'newest')
                    <a href="{{ route('home', request('search') ? ['search' => request('search')] : []) }}" class="filter-reset-btn">
                        <i class="fas fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="books-list-container">
            @forelse($kitab as $item)
                <div class="home-list-card" onclick="window.location.href='{{ route('kitab.view', $item->id_kitab) }}'">
                    <div class="list-cover">
                        @if($item->cover)
                            <img src="{{ asset('cover/'.$item->cover) }}" alt="{{ $item->judul }}">
                        @else
                            <div class="placeholder"><i class="fas fa-book"></i></div>
                        @endif
                    </div>
                    
                    <div class="list-info">
                        <h3 class="list-title">{{ $item->judul }}</h3>
                        <p class="list-author">{{ $item->penulis }}</p>
                        <p class="list-meta">{{ $item->kategori }} • {{ $item->bahasa }}</p>
                    </div>

                    <!-- Stop propagation on bookmark button -->
                    <button class="btn-bookmark list-bookmark bookmark-btn {{ in_array($item->id_kitab, $bookmarkedIds ?? []) ? 'active' : '' }}" data-id="{{ $item->id_kitab }}" onclick="event.stopPropagation();">
                        @if(in_array($item->id_kitab, $bookmarkedIds ?? []))
                            <i class="fas fa-bookmark"></i>
                        @else
                            <i class="far fa-bookmark"></i>
                        @endif
                    </button>
                    <i class="fas fa-chevron-right list-arrow"></i>
                </div>
            @empty
                <div class="empty-state">
                    <div style="font-size: 3rem; margin-bottom: 10px;">📖</div>
                    <h3>Kitab tidak ditemukan</h3>
                    <p>Coba kata kunci atau filter lain</p>
                </div>
            @endforelse
        </div>

        @if($kitab->hasPages())
        <div class="pagination-wrap">
            {{ $kitab->links() }}
        </div>
        @endif
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.bookmark-btn');

    buttons.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            const kitabId = this.dataset.id;
            const isBookmarked = this.classList.contains('active');
            const url = isBookmarked 
                ? `/kitab/bookmark/delete/${kitabId}`
                : `/kitab/bookmark/${kitabId}`;
            const method = isBookmarked ? 'DELETE' : 'POST';
            
            const icon = this.querySelector('i');
            const originalClass = icon.className;
            
            this.disabled = true;
            this.classList.toggle('active');
            
            if (isBookmarked) {
                 icon.className = 'far fa-bookmark';
            } else {
                 icon.className = 'fas fa-bookmark';
                 icon.style.transform = 'scale(1.3)';
                 icon.style.transition = 'transform 0.2s';
                 setTimeout(() => icon.style.transform = 'scale(1)', 200);
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    }
                });

                if (!response.ok) throw new Error('Server Error');

            } catch (error) {
                console.error('Error:', error);
                this.classList.toggle('active');
                icon.className = originalClass;
                 Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Gagal memperbarui bookmark!',
                    confirmButtonColor: '#1B5E3B'
                });
            } finally {
                this.disabled = false;
            }
        });
    });
});
</script>

<style>
/* ============================================
   ANDROID UX/UI: HomeScreen.kt Matchings
   ============================================ */

.home-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light), var(--primary-dark));
    margin: -24px -20px 24px -20px; /* Negate container padding */
    padding: 24px 20px 32px 20px;
    border-bottom-left-radius: 24px;
    border-bottom-right-radius: 24px;
    box-shadow: 0 4px 15px rgba(27, 94, 59, 0.15);
}

.home-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.home-date {
    color: rgba(255, 255, 255, 0.72);
    font-size: 13px;
    font-weight: 500;
}

.home-greeting {
    color: #FFFFFF;
    font-size: 26px;
    font-weight: 800;
    margin: 4px 0 2px 0;
    letter-spacing: -0.5px;
}

.home-bismillah {
    color: rgba(255, 255, 255, 0.65);
    font-size: 13px;
}

.home-avatar {
    width: 48px;
    height: 48px;
    background: rgba(200, 169, 81, 0.24);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-color);
    font-size: 20px;
    font-weight: 800;
    text-decoration: none;
    transition: 0.3s;
}

.home-avatar:hover {
    background: rgba(200, 169, 81, 0.4);
    transform: scale(1.05);
}

.home-stats-row {
    display: flex;
    gap: 12px;
}

.home-stat-card {
    flex: 1;
    background: rgba(255, 255, 255, 0.11);
    border-radius: 16px;
    padding: 12px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.stat-emoji {
    font-size: 20px;
    margin-bottom: 4px;
}

.stat-value {
    color: var(--accent-color);
    font-size: 15px;
    font-weight: 800;
}

.stat-label {
    color: rgba(255, 255, 255, 0.65);
    font-size: 11px;
    margin-top: 2px;
}

/* ============================================
   SECTIONS
   ============================================ */
.home-content {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-icon {
    color: var(--accent-color);
    font-size: 20px;
}

.section-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-color);
    margin: 0;
}

.section-action {
    color: var(--primary-color);
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
}

.section-action:hover {
    text-decoration: underline;
}

/* SCROLL CONTAINERS */
.horizontal-scroll-container {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 15px;
    margin: 0 -10px;
    padding-left: 10px;
    padding-right: 10px;
}

.horizontal-scroll-container::-webkit-scrollbar {
    display: none;
}

/* ============================================
   GRID BOOK CARD (Matches HomeGridBookCard.kt)
   ============================================ */
.home-grid-card {
    width: 156px;
    min-width: 156px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.card-cover {
    width: 100%;
    height: 172px;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 10px;
    background: #eef2f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-cover i {
    font-size: 40px;
    color: #ccc;
}

.card-title-link {
    text-decoration: none;
}

.card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-color);
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 4px;
}

.card-author {
    font-size: 12px;
    color: var(--light-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 8px;
    flex-grow: 1;
}

.card-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.card-views {
    font-size: 11px;
    font-weight: 600;
    color: var(--primary-color);
}

.btn-bookmark {
    background: none;
    border: none;
    color: var(--light-text);
    font-size: 16px;
    cursor: pointer;
    padding: 0;
    transition: 0.2s;
}

.btn-bookmark.active {
    color: var(--primary-color);
}

/* ============================================
   HISTORY CARD
   ============================================ */
.history-item-card {
    min-width: 240px;
    width: 240px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--primary-color);
    padding: 12px;
    border-radius: 16px;
    text-decoration: none;
    transition: 0.3s;
}

.history-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(27, 94, 59, 0.2);
}

.history-item-img {
    width: 48px;
    height: 64px;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(255,255,255,0.2);
}

.history-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.history-item-info h3 {
    color: white;
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.history-item-info p {
    color: var(--accent-color);
    font-size: 12px;
    font-weight: 600;
    margin: 0;
}

/* ============================================
   LIST BOOK CARD (Matches HomeListBookCard.kt)
   ============================================ */
.books-list-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.home-list-card {
    display: flex;
    align-items: center;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 12px;
    cursor: pointer;
    transition: 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.home-list-card:hover {
    border-color: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.list-cover {
    width: 68px;
    height: 94px;
    border-radius: 10px;
    background: #eef2f5;
    overflow: hidden;
    margin-right: 14px;
    flex-shrink: 0;
}

.list-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.list-info {
    flex-grow: 1;
    overflow: hidden;
}

.list-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-color);
    margin-bottom: 4px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.list-author {
    font-size: 13px;
    color: var(--light-text);
    margin-bottom: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-meta {
    font-size: 12px;
    color: var(--primary-color);
    font-weight: 500;
}

.list-bookmark {
    padding: 12px;
    margin: 0 4px;
}

.list-arrow {
    color: var(--light-text);
    font-size: 16px;
    padding-right: 4px;
}

/* ============================================
   FILTER TOOLBAR
   ============================================ */
.filter-toolbar {
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    background: transparent;
}

.filter-group {
    flex: 1;
    min-width: 140px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-group label i {
    color: var(--primary-color);
}

.filter-group select {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-color);
    outline: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.filter-group select:focus {
    border-color: var(--primary-color);
}

.filter-reset-btn {
    align-self: flex-end;
    background: #FEE2E2;
    color: #DC2626;
    border-radius: 12px;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: 0.2s;
}

.filter-reset-btn:hover {
    background: #FCA5A5;
}

/* ============================================
   EMPTY STATE & PAGINATION
   ============================================ */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: var(--card-bg);
    border-radius: 16px;
    border: 1px dashed var(--border-color);
    color: var(--light-text);
}

.empty-state h3 {
    color: var(--text-color);
    margin-bottom: 4px;
}

.pagination-wrap {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}

@media (max-width: 768px) {
    .home-header {
        border-radius: 0;
        margin-left: -14px;
        margin-right: -14px;
    }
    .filter-row {
        flex-direction: column;
    }
    .filter-reset-btn {
        width: 100%;
        margin-top: 8px;
    }
}
</style>

@endsection
