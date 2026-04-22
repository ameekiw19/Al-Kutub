@extends('TemplateUser')

@section('konten')
<style>
    /* ============================================
       KITAB DETAIL - Android KitabDetailScreen.kt
       ============================================ */

    /* --- HERO HEADER --- */
    .detail-hero {
        position: relative;
        width: calc(100% + 40px);
        margin: -24px -20px 0 -20px;
        height: 360px;
        overflow: hidden;
    }

    .detail-hero-bg {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detail-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.28) 0%, rgba(0,0,0,0.36) 40%, rgba(0,0,0,0.72) 100%);
    }

    .detail-hero-nav {
        position: absolute;
        top: 16px;
        left: 20px;
        right: 20px;
        display: flex;
        justify-content: space-between;
        z-index: 2;
    }

    .hero-icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: rgba(255,255,255,0.16);
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        font-size: 16px;
        text-decoration: none;
    }

    .hero-icon-btn:hover {
        background: rgba(255,255,255,0.3);
        color: white;
    }

    .detail-hero-info {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        display: flex;
        gap: 16px;
        align-items: flex-end;
        z-index: 2;
    }

    .hero-cover-card {
        width: 112px;
        height: 162px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        flex-shrink: 0;
        background: linear-gradient(135deg, #EEF4EF, #DCEBDD);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-cover-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-cover-card .placeholder-icon {
        font-size: 40px;
        color: rgba(27, 94, 59, 0.24);
    }
    
    .hero-text-info {
        flex: 1;
        padding-bottom: 8px;
    }

    .hero-kategori-badge {
        display: inline-block;
        background: var(--accent-color);
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 12px;
    }

    .hero-title {
        color: white;
        font-size: 22px;
        line-height: 28px;
        font-weight: 800;
        margin: 10px 0 4px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .hero-author {
        color: rgba(255,255,255,0.82);
        font-size: 13px;
    }

    /* --- STATS OVERVIEW CARD (Overlapping) --- */
    .stats-overview-card {
        background: var(--card-bg);
        border-radius: 22px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        margin: -18px 0 0 0;
        padding: 18px 12px;
        display: flex;
        justify-content: space-evenly;
        position: relative;
        z-index: 3;
    }

    .stats-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .stats-item .stats-icon {
        color: var(--accent-color);
        font-size: 16px;
    }

    .stats-item .stats-value {
        color: var(--text-color);
        font-size: 16px;
        font-weight: 800;
    }

    .stats-item .stats-label {
        color: var(--light-text);
        font-size: 11px;
    }

    /* --- ACTION BUTTONS ROW --- */
    .action-buttons-row {
        display: flex;
        gap: 10px;
        margin: 16px 0;
    }

    .btn-action-read {
        flex: 1;
        height: 52px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
        border: none;
        border-radius: 18px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .btn-action-read:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(27, 94, 59, 0.25);
    }

    .btn-action-bookmark {
        width: 52px;
        height: 52px;
        background: var(--card-bg);
        border: 1.5px solid var(--border-color);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
        font-size: 18px;
        color: var(--light-text);
    }

    .btn-action-bookmark.active {
        background: #E8F5E9;
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .btn-action-bookmark:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .btn-action-share {
        width: 52px;
        height: 52px;
        background: var(--card-bg);
        border: 1.5px solid var(--border-color);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
        font-size: 18px;
        color: var(--light-text);
    }

    .btn-action-share:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    /* --- SECTION CARDS --- */
    .section-card {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 16px;
        margin-bottom: 14px;
    }

    .section-card-title {
        color: var(--text-color);
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .desc-text {
        font-size: 13px;
        line-height: 20px;
        color: var(--text-secondary, #6B5E4E);
    }

    .desc-toggle {
        color: var(--primary-color);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        background: none;
        padding: 0;
        margin-top: 10px;
        font-family: 'Poppins', sans-serif;
    }

    /* Detail Info Rows */
    .detail-info-row {
        display: flex;
        align-items: center;
        background: var(--background-color);
        border-radius: 16px;
        padding: 12px;
        margin-bottom: 10px;
    }

    .detail-info-row:last-child {
        margin-bottom: 0;
    }

    .info-row-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        margin-right: 12px;
    }

    .info-row-label {
        font-size: 11px;
        color: var(--light-text);
    }

    .info-row-value {
        font-size: 13px;
        color: var(--text-color);
        font-weight: 600;
    }

    /* Tags */
    .tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag-chip {
        background: #F0F7F3;
        color: var(--primary-color);
        font-size: 12px;
        font-weight: 600;
        padding: 8px 12px;
        border-radius: 12px;
    }

    body.dark-mode .tag-chip {
        background: rgba(27, 94, 59, 0.15);
    }

    /* --- RATING & REVIEW SECTION --- */
    .rating-review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .rating-review-header h3 {
        color: var(--text-color);
        font-size: 16px;
        font-weight: 800;
        margin: 0;
    }

    .rating-summary-inline {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rating-summary-inline .star-icon {
        color: var(--accent-color);
        font-size: 14px;
    }

    .rating-summary-inline .rating-val {
        font-weight: 700;
        font-size: 15px;
        color: var(--text-color);
    }

    .rating-summary-inline .rating-cnt {
        font-size: 12px;
        color: var(--light-text);
    }

    /* Big Rating Card */
    .rating-big-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        margin-bottom: 14px;
    }

    .rating-big-score {
        font-size: 40px;
        font-weight: 800;
        color: var(--text-color);
    }

    .rating-big-stars {
        color: var(--accent-color);
        font-size: 18px;
        margin: 6px 0;
    }

    .rating-big-count {
        font-size: 12px;
        color: var(--light-text);
    }

    .your-rating {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--border-color);
    }

    .your-rating p {
        font-size: 13px;
        color: var(--light-text);
        margin-bottom: 8px;
    }

    /* Star Input */
    .star-rating-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 5px;
        margin-bottom: 0;
    }

    .star-rating-input input { display: none; }

    .star-rating-input label {
        color: var(--border-color);
        font-size: 24px;
        cursor: pointer;
        transition: 0.15s;
    }

    .star-rating-input input:checked ~ label,
    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label {
        color: var(--accent-color);
    }

    /* Review List */
    .review-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 14px;
    }

    .review-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 16px;
        display: flex;
        gap: 12px;
        transition: 0.2s;
        animation: fadeIn 0.4s ease-out;
    }

    .review-card.new-comment {
        animation: slideInRight 0.5s ease-out;
    }

    .review-avatar {
        width: 40px;
        height: 40px;
        background: rgba(200, 169, 81, 0.2);
        color: var(--accent-color);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .review-content { flex: 1; }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .review-user {
        font-weight: 700;
        font-size: 13px;
        color: var(--text-color);
    }

    .review-stars {
        color: var(--accent-color);
        font-size: 12px;
    }

    .review-text {
        color: var(--text-color);
        line-height: 1.5;
        font-size: 13px;
    }

    /* Comment Form */
    .review-form-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 18px;
        position: relative;
        overflow: hidden;
    }

    .review-form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 2px;
        background: var(--primary-color);
        transition: left 0.3s ease;
    }

    .review-form-card:focus-within::before {
        left: 0;
    }

    .review-textarea {
        width: 100%;
        padding: 14px;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        background: var(--background-color);
        color: var(--text-color);
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        resize: vertical;
        min-height: 90px;
        margin-bottom: 14px;
        transition: 0.3s;
    }

    .review-textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(27, 94, 59, 0.08);
    }

    .btn-submit-review {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
        border: none;
        border-radius: 14px;
        padding: 12px 24px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Poppins', sans-serif;
    }

    .btn-submit-review:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(27, 94, 59, 0.2);
    }

    /* Related Books */
    .related-scroll {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 8px;
        scrollbar-width: none;
    }

    .related-scroll::-webkit-scrollbar { display: none; }

    .related-book-card {
        min-width: 120px;
        width: 120px;
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 10px;
        text-decoration: none;
        transition: 0.2s;
    }

    .related-book-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .related-book-cover {
        width: 100%;
        height: 140px;
        border-radius: 10px;
        overflow: hidden;
        background: #eef2f5;
        margin-bottom: 8px;
    }

    .related-book-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-book-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-color);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.3;
    }

    .related-book-author {
        font-size: 11px;
        color: var(--light-text);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Auto Refresh Indicator */
    .auto-refresh-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: var(--primary-color);
        color: white;
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 12px;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1000;
    }

    .auto-refresh-indicator.show {
        opacity: 1;
    }

    /* Animations */
    @keyframes slideInRight {
        from { transform: translateX(30px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .detail-hero {
            height: 300px;
            margin-left: -14px;
            margin-right: -14px;
            width: calc(100% + 28px);
        }

        .hero-title {
            font-size: 18px;
            line-height: 24px;
        }

        .hero-cover-card {
            width: 90px;
            height: 130px;
            border-radius: 16px;
        }

        .stats-overview-card {
            margin-left: 0;
            margin-right: 0;
            padding: 14px 8px;
        }

        .action-buttons-row {
            flex-wrap: wrap;
        }
    }

    /* Placeholder cover */
    .placeholder-cover {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
        font-size: 2rem;
    }

</style>

<!-- HERO HEADER -->
<div class="detail-hero">
    @if($kitab->cover)
        <img src="{{ asset('cover/' . $kitab->cover) }}" alt="{{ $kitab->judul }}" class="detail-hero-bg">
    @else
        <div class="detail-hero-bg" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));"></div>
    @endif
    <div class="detail-hero-overlay"></div>

    <div class="detail-hero-nav">
        <a href="{{ route('home') }}" class="hero-icon-btn"><i class="fas fa-arrow-left"></i></a>
        <button class="hero-icon-btn" id="btnShare"><i class="fas fa-share-alt"></i></button>
    </div>

    <div class="detail-hero-info">
        <div class="hero-cover-card">
            @if($kitab->cover)
                <img src="{{ asset('cover/' . $kitab->cover) }}" alt="{{ $kitab->judul }}">
            @else
                <i class="fas fa-book-open placeholder-icon"></i>
            @endif
        </div>
        <div class="hero-text-info">
            <span class="hero-kategori-badge">{{ $kitab->kategori }}</span>
            <h1 class="hero-title">{{ $kitab->judul }}</h1>
            <p class="hero-author">{{ $kitab->penulis }}</p>
        </div>
    </div>
</div>

<!-- STATS OVERVIEW CARD -->
<div class="stats-overview-card">
    <div class="stats-item">
        <i class="fas fa-star stats-icon"></i>
        <span class="stats-value rating-score">{{ $averageRating }}</span>
        <span class="stats-label">Rating</span>
    </div>
    <div class="stats-item">
        <i class="fas fa-eye stats-icon"></i>
        <span class="stats-value">{{ number_format($kitab->views ?? 0) }}</span>
        <span class="stats-label">Pembaca</span>
    </div>
    <div class="stats-item">
        <i class="fas fa-download stats-icon"></i>
        <span class="stats-value">{{ number_format($kitab->downloads ?? 0) }}</span>
        <span class="stats-label">Unduhan</span>
    </div>
    <div class="stats-item">
        <i class="fas fa-comment stats-icon"></i>
        <span class="stats-value">{{ count($komentar) }}</span>
        <span class="stats-label">Ulasan</span>
    </div>
</div>

<!-- ACTION BUTTONS -->
<div class="action-buttons-row">
    <button class="btn-action-read" onclick="window.location.href='{{ route('kitab.read', $kitab->id_kitab) }}'">
        <i class="fas fa-book-open"></i> Baca Kitab
    </button>
    <button class="btn-action-bookmark {{ $isBookmarked ? 'active' : '' }}" id="btnBookmark">
        <i class="{{ $isBookmarked ? 'fas' : 'far' }} fa-bookmark"></i>
    </button>
    <button class="btn-action-share" id="btnShare2">
        <i class="fas fa-share-alt"></i>
    </button>
</div>

<!-- ABOUT SECTION -->
<div class="section-card">
    <h3 class="section-card-title">Tentang Kitab</h3>
    <p class="desc-text" id="descText">{{ $kitab->deskripsi }}</p>
    <button class="desc-toggle" id="descToggle">Selengkapnya</button>
</div>

<!-- DETAIL INFO SECTION -->
<div class="section-card">
    <h3 class="section-card-title">Detail Kitab</h3>
    <div class="detail-info-row">
        <div class="info-row-icon" style="background: #F0F7F3; color: var(--primary-color);"><i class="fas fa-tag"></i></div>
        <div><div class="info-row-label">Kategori</div><div class="info-row-value">{{ $kitab->kategori }}</div></div>
    </div>
    <div class="detail-info-row">
        <div class="info-row-icon" style="background: #E3F2FD; color: #1565C0;"><i class="fas fa-language"></i></div>
        <div><div class="info-row-label">Bahasa</div><div class="info-row-value">{{ $kitab->bahasa ?? 'Arab' }}</div></div>
    </div>
    <div class="detail-info-row">
        <div class="info-row-icon" style="background: #FFF8E1; color: var(--accent-color);"><i class="fas fa-eye"></i></div>
        <div><div class="info-row-label">Dilihat</div><div class="info-row-value">{{ number_format($kitab->views ?? 0) }} kali</div></div>
    </div>
    <div class="detail-info-row">
        <div class="info-row-icon" style="background: #F3E5F5; color: #7B1FA2;"><i class="fas fa-download"></i></div>
        <div><div class="info-row-label">Diunduh</div><div class="info-row-value">{{ number_format($kitab->downloads ?? 0) }} kali</div></div>
    </div>
</div>

<!-- TAGS SECTION -->
<div class="section-card">
    <h3 class="section-card-title">Tag</h3>
    <div class="tags-container">
        <span class="tag-chip">#{{ strtolower($kitab->kategori) }}</span>
        <span class="tag-chip">#{{ strtolower($kitab->bahasa ?? 'arab') }}</span>
        <span class="tag-chip">#alkutub</span>
        <span class="tag-chip">#kitab</span>
    </div>
</div>

<!-- RATING & REVIEW -->
<div class="rating-review-header">
    <h3>Rating & Ulasan</h3>
    <div class="rating-summary-inline">
        <i class="fas fa-star star-icon"></i>
        <span class="rating-val rating-score">{{ $averageRating }}</span>
        <span class="rating-cnt rating-count">({{ $ratingsCount }})</span>
    </div>
</div>

<div class="rating-big-card">
    <div class="rating-big-score rating-score">{{ $averageRating }}</div>
    <div class="rating-big-stars rating-stars">
        @for($i = 1; $i <= 5; $i++)
            @if($i <= round($averageRating))
                <i class="fas fa-star"></i>
            @else
                <i class="far fa-star"></i>
            @endif
        @endfor
    </div>
    <div class="rating-big-count rating-count">Berdasarkan {{ $ratingsCount }} penilaian</div>

    <div class="your-rating">
        <p>Berikan penilaian Anda</p>
        <div class="star-rating-input" id="ratingInputTop">
            <input type="radio" id="topStar5" name="top_rating" value="5" {{ $userRating == 5 ? 'checked' : '' }} /><label for="topStar5"><i class="fas fa-star"></i></label>
            <input type="radio" id="topStar4" name="top_rating" value="4" {{ $userRating == 4 ? 'checked' : '' }} /><label for="topStar4"><i class="fas fa-star"></i></label>
            <input type="radio" id="topStar3" name="top_rating" value="3" {{ $userRating == 3 ? 'checked' : '' }} /><label for="topStar3"><i class="fas fa-star"></i></label>
            <input type="radio" id="topStar2" name="top_rating" value="2" {{ $userRating == 2 ? 'checked' : '' }} /><label for="topStar2"><i class="fas fa-star"></i></label>
            <input type="radio" id="topStar1" name="top_rating" value="1" {{ $userRating == 1 ? 'checked' : '' }} /><label for="topStar1"><i class="fas fa-star"></i></label>
        </div>
    </div>
</div>

<!-- REVIEW LIST -->
<div class="review-list" id="reviewList">
    @forelse($komentar as $komen)
        <div class="review-card">
            <div class="review-avatar">
                {{ strtoupper(substr($komen->user->username, 0, 1)) }}
            </div>
            <div class="review-content">
                <div class="review-header">
                    <span class="review-user">{{ $komen->user->username }}</span>
                    <div class="review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= (int) ($komen->user_rating ?? 0))
                                <i class="fas fa-star"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                </div>
                <p class="review-text">{{ $komen->isi_comment }}</p>
                <small style="color:var(--light-text); font-size:0.75rem;">{{ $komen->created_at->diffForHumans() }}</small>
            </div>
        </div>
    @empty
        <div class="review-card" data-empty-state="true" style="justify-content:center; border-style:dashed;">
            <p style="color:var(--light-text)">Belum ada ulasan. Jadilah yang pertama!</p>
        </div>
    @endforelse
</div>

<!-- RELATED BOOKS -->
@if(isset($related) && $related->count() > 0)
<div class="section-card">
    <h3 class="section-card-title">Kitab Terkait</h3>
    <div class="related-scroll">
        @foreach($related as $r)
        <a href="{{ route('kitab.view', $r->id_kitab) }}" class="related-book-card">
            <div class="related-book-cover">
                @if($r->cover)
                    <img src="{{ asset('cover/' . $r->cover) }}" alt="{{ $r->judul }}">
                @else
                    <div class="placeholder-cover"><i class="fas fa-book"></i></div>
                @endif
            </div>
            <h4 class="related-book-title">{{ \Illuminate\Support\Str::limit($r->judul, 30) }}</h4>
            <p class="related-book-author">{{ $r->penulis }}</p>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- WRITE REVIEW -->
<div class="section-card" style="margin-top: 4px;">
    <h3 class="section-card-title">Tulis Ulasan</h3>
    <div class="review-form-card" style="border: none; padding: 0; box-shadow: none;">
        <form action="{{ route('kitab.comment', $kitab->id_kitab) }}" method="POST" id="commentForm">
            @csrf
            <div class="star-rating-input">
                <input type="radio" id="star5" name="rating" value="5" {{ $userRating == 5 ? 'checked' : '' }} /><label for="star5"><i class="fas fa-star"></i></label>
                <input type="radio" id="star4" name="rating" value="4" {{ $userRating == 4 ? 'checked' : '' }} /><label for="star4"><i class="fas fa-star"></i></label>
                <input type="radio" id="star3" name="rating" value="3" {{ $userRating == 3 ? 'checked' : '' }} /><label for="star3"><i class="fas fa-star"></i></label>
                <input type="radio" id="star2" name="rating" value="2" {{ $userRating == 2 ? 'checked' : '' }} /><label for="star2"><i class="fas fa-star"></i></label>
                <input type="radio" id="star1" name="rating" value="1" {{ $userRating == 1 ? 'checked' : '' }} /><label for="star1"><i class="fas fa-star"></i></label>
            </div>
            
            <textarea name="isi_komentar" class="review-textarea" placeholder="Bagikan pendapat Anda tentang kitab ini..." required></textarea>
            
            <button type="submit" class="btn-submit-review">
                <i class="fas fa-paper-plane"></i> Kirim Ulasan
            </button>
        </form>
    </div>
</div>

<!-- Auto-refresh indicator -->
<div class="auto-refresh-indicator" id="refreshIndicator">
    <i class="fas fa-sync-alt"></i> Memperbarui...
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Description toggle
        const descText = document.getElementById('descText');
        const descToggle = document.getElementById('descToggle');
        const kitabId = {{ $kitab->id_kitab }};
        let expanded = false;

        if (descText && descText.scrollHeight <= descText.offsetHeight + 5) {
            descToggle.style.display = 'none';
        } else if (descText) {
            descText.style.display = '-webkit-box';
            descText.style.webkitLineClamp = '4';
            descText.style.webkitBoxOrient = 'vertical';
            descText.style.overflow = 'hidden';
        }

        if (descToggle) {
            descToggle.addEventListener('click', function() {
                expanded = !expanded;
                if (expanded) {
                    descText.style.webkitLineClamp = 'unset';
                    descText.style.overflow = 'visible';
                    descToggle.textContent = 'Tampilkan lebih sedikit';
                } else {
                    descText.style.webkitLineClamp = '4';
                    descText.style.overflow = 'hidden';
                    descToggle.textContent = 'Selengkapnya';
                }
            });
        }

        if (!window.jQuery) {
            console.error('jQuery gagal dimuat untuk halaman ulasan kitab.');
            return;
        }

        const $ = window.jQuery;
        const commentsFetchUrl = "{{ route('kitab.comments.fetch', $kitab->id_kitab) }}";

        // Share button (both)
        function doShare() {
            var url = "{{ url(route('kitab.view', $kitab->id_kitab)) }}";
            var title = "{{ addslashes($kitab->judul) }}";
            if (navigator.share) {
                navigator.share({ title: title, url: url }).then(function() {
                    Swal.fire({ icon: 'success', title: 'Berhasil dibagikan!', showConfirmButton: false, timer: 1500, toast: true, position: 'top-end' });
                }).catch(function() {});
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    Swal.fire({ icon: 'success', title: 'Link disalin!', showConfirmButton: false, timer: 1500, toast: true, position: 'top-end' });
                }).catch(function() {
                    Swal.fire({ icon: 'error', title: 'Gagal menyalin link', showConfirmButton: false, timer: 1500, toast: true, position: 'top-end' });
                });
            }
        }
        
        $('#btnShare, #btnShare2').on('click', doShare);

        // Handle Bookmark Toggle
        $('#btnBookmark').on('click', function() {
            let btn = $(this);
            let icon = btn.find('i');
            let isBookmarked = btn.hasClass('active');
            let url = isBookmarked 
                ? "{{ route('kitab.bookmark.delete', $kitab->id_kitab) }}" 
                : "{{ route('kitab.bookmark', $kitab->id_kitab) }}";
            let method = isBookmarked ? 'DELETE' : 'POST';

            icon.removeClass('fa-bookmark').addClass('fa-spinner fa-spin');
            
            $.ajax({
                url: url,
                method: method,
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (method === 'POST') {
                        btn.addClass('active');
                        icon.removeClass('far fa-spinner fa-spin').addClass('fas fa-bookmark');
                        Swal.fire({ icon: 'success', title: 'Disimpan!', text: 'Kitab ditambahkan ke koleksi.', showConfirmButton: false, timer: 1500 });
                    } else {
                        btn.removeClass('active');
                        icon.removeClass('fas fa-spinner fa-spin').addClass('far fa-bookmark');
                        Swal.fire({ icon: 'info', title: 'Dihapus', text: 'Kitab dihapus dari koleksi.', showConfirmButton: false, timer: 1500 });
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan. Coba lagi nanti.');
                    icon.removeClass('fa-spinner fa-spin').addClass(isBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark');
                }
            });
        });

        // Handle Rating Click (both top card and form)
        $('.star-rating-input input').on('change', function() {
            let rating = $(this).val();
            let url = "{{ route('kitab.rate', $kitab->id_kitab) }}";

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    rating: rating
                },
                success: function(response) {
                    if (response.success) {
                        $('.rating-score').text(response.averageRating);
                        $('.rating-count').text('Berdasarkan ' + response.ratingsCount + ' penilaian');
                        $('.rating-cnt').text('(' + response.ratingsCount + ')');
                        $('.rating-big-stars, .rating-summary .rating-stars').html(generateAverageStarHtml(response.averageRating));
                        
                        Swal.fire({ icon: 'success', title: 'Terima Kasih!', text: response.message, showConfirmButton: false, timer: 1500 });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Gagal mengirim penilaian. ' + (xhr.responseJSON ? xhr.responseJSON.message : '') });
                }
            });
        });

        // Handle Comment Submission (AJAX)
        $('#commentForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let url = form.attr('action');
            let submitBtn = form.find('button[type="submit"]');
            let originalBtnText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
            
            let formData = new FormData(form[0]);
            let data = Object.fromEntries(formData);
            
            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        let newComment = `
                            <div class="review-card" style="animation: slideInRight 0.5s ease-out;">
                                <div class="review-avatar">
                                    ${response.comment.avatar}
                                </div>
                                <div class="review-content">
                                    <div class="review-header">
                                        <span class="review-user">${response.comment.username}</span>
                                        <div class="review-stars">
                                            ${generateStarHtml(response.comment.rating || 0)}
                                        </div>
                                    </div>
                                    <p class="review-text">${response.comment.text}</p>
                                    <small style="color:var(--light-text); font-size:0.75rem;">
                                        <i class="fas fa-clock"></i> ${response.comment.date}
                                    </small>
                                </div>
                            </div>
                        `;
                        
                        $('.review-list').prepend(newComment);
                        form.find('textarea[name="isi_komentar"]').val('');
                        form.find('input[name="rating"]').prop('checked', false);
                        $('.review-list .review-card[data-empty-state="true"]').remove();

                        if (response.averageRating !== undefined && response.ratingsCount !== undefined) {
                            $('.rating-score').text(response.averageRating);
                            $('.rating-count').text('Berdasarkan ' + response.ratingsCount + ' penilaian');
                            $('.rating-cnt').text('(' + response.ratingsCount + ')');
                            $('.rating-big-stars').html(generateAverageStarHtml(response.averageRating));
                        }

                        Swal.fire({ icon: 'success', title: 'Terima Kasih!', text: 'Ulasan Anda berhasil dikirim.', showConfirmButton: false, timer: 1500, toast: true, position: 'top-end' });
                        
                        $('html, body').animate({ scrollTop: $('.review-list').offset().top - 100 }, 500);
                        refreshComments(kitabId);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: response.message || 'Gagal mengirim ulasan.' });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal mengirim ulasan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                    Swal.fire({ icon: 'error', title: 'Oops...', text: errorMessage });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });
        
        function generateStarHtml(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fas fa-star" style="color:var(--accent-color)"></i>';
                } else {
                    stars += '<i class="far fa-star" style="color:var(--accent-color)"></i>';
                }
            }
            return stars;
        }

        function generateAverageStarHtml(average) {
            const rounded = Math.round(Number(average) || 0);
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rounded) {
                    stars += '<i class="fas fa-star"></i>';
                } else {
                    stars += '<i class="far fa-star"></i>';
                }
            }
            return stars;
        }
        
        let autoRefreshInterval;
        let isRefreshing = false;
        
        function startAutoRefresh() {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(function() {
                if (!isRefreshing) {
                    refreshComments(kitabId);
                }
            }, 8000);
        }
        
        function refreshComments(kitabId) {
            if (isRefreshing) return;
            
            isRefreshing = true;
            $('#refreshIndicator').addClass('show');
            
            $.ajax({
                url: commentsFetchUrl,
                method: 'GET',
                timeout: 10000,
                success: function(response) {
                    if (response.success && response.comments) {
                        updateCommentsList(response.comments, true);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Auto-refresh failed:', error);
                },
                complete: function() {
                    isRefreshing = false;
                    setTimeout(function() {
                        $('#refreshIndicator').removeClass('show');
                    }, 1000);
                }
            });
        }
        
        function updateCommentsList(comments, isAutoRefresh = false) {
            let currentComments = $('.review-list .review-card').not('[data-empty-state="true"]').length;
            let newCommentsHtml = '';
            
            if (comments.length === 0) {
                newCommentsHtml = `
                    <div class="review-card" data-empty-state="true" style="justify-content:center; border-style:dashed;">
                        <p style="color:var(--light-text)">Belum ada ulasan. Jadilah yang pertama!</p>
                    </div>
                `;
            } else {
                comments.forEach(function(comment) {
                    newCommentsHtml += `
                        <div class="review-card ${isAutoRefresh && currentComments < comments.length ? 'new-comment' : ''}">
                            <div class="review-avatar">
                                ${comment.avatar}
                            </div>
                            <div class="review-content">
                                <div class="review-header">
                                    <span class="review-user">${comment.username}</span>
                                    <div class="review-stars">
                                        ${generateStarHtml(comment.rating || 0)}
                                    </div>
                                </div>
                                <p class="review-text">${comment.text}</p>
                                <small style="color:var(--light-text); font-size:0.75rem;">
                                    <i class="fas fa-clock"></i> ${comment.date}
                                </small>
                            </div>
                        </div>
                    `;
                });
            }
            
            $('.review-list').html(newCommentsHtml);
            
            if (isAutoRefresh && currentComments < comments.length) {
                let newCount = comments.length - currentComments;
                if (newCount > 0) {
                    showNewCommentsNotification(newCount);
                }
            }
        }
        
        function showNewCommentsNotification(count) {
            let message = count === 1 ? '1 ulasan baru' : `${count} ulasan baru`;
            Swal.fire({ icon: 'info', title: 'Update!', text: message, showConfirmButton: false, timer: 2000, toast: true, position: 'top-end' });
        }
        
        startAutoRefresh();
        
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(autoRefreshInterval);
            } else {
                startAutoRefresh();
            }
        });
        
        function manualRefresh() {
            refreshComments(kitabId);
        }

        window.manualReviewRefresh = manualRefresh;
        refreshComments(kitabId);
    });
</script>
@endsection
