@extends('TemplateUser')

@section('konten')
<div class="stats-page">
    <div class="stats-hero">
        <div>
            <p class="stats-eyebrow">Reading Statistics</p>
            <h1>Ringkasan performa baca kamu</h1>
            <p class="stats-subtitle">Lihat perkembangan buku, halaman, durasi, streak, kategori favorit, dan aktivitas terbaru.</p>
        </div>
        <div class="stats-hero-actions">
            <a href="{{ route('reading-goals.index') }}" class="stats-ghost-btn">
                <i class="fas fa-bullseye"></i> Goals
            </a>
            <a href="{{ route('reading-notes.index') }}" class="stats-ghost-btn">
                <i class="fas fa-sticky-note"></i> Catatan
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stats-card">
            <span>Total Kitab</span>
            <strong>{{ number_format($totalBooksRead) }}</strong>
            <small>kitab pernah dibuka</small>
        </div>
        <div class="stats-card">
            <span>Halaman Terbaca</span>
            <strong>{{ number_format($totalPagesRead) }}</strong>
            <small>berdasarkan progress terakhir</small>
        </div>
        <div class="stats-card">
            <span>Durasi Baca</span>
            <strong>{{ number_format($totalMinutesRead) }} menit</strong>
            <small>akumulasi sesi membaca</small>
        </div>
        <div class="stats-card">
            <span>Hari Aktif</span>
            <strong>{{ number_format($daysActive) }}</strong>
            <small>{{ number_format($streak->current_streak) }} hari streak saat ini</small>
        </div>
    </div>

    <div class="stats-main-grid">
        <div class="stats-column">
            <div class="stats-panel">
                <div class="stats-panel-head">
                    <div>
                        <span class="stats-badge">Ringkasan</span>
                        <h2>Overview</h2>
                    </div>
                </div>

                <div class="stats-overview-grid">
                    <div class="stats-mini-card">
                        <span>Rata-rata halaman per hari</span>
                        <strong>{{ number_format($averagePagesPerDay, 1) }}</strong>
                    </div>
                    <div class="stats-mini-card">
                        <span>Kitab bulan ini</span>
                        <strong>{{ number_format($thisMonthBooks) }}</strong>
                    </div>
                    <div class="stats-mini-card">
                        <span>Catatan baca</span>
                        <strong>{{ number_format($notesCount) }}</strong>
                    </div>
                    <div class="stats-mini-card">
                        <span>Goal selesai</span>
                        <strong>{{ number_format($goalStats['completed_goals']) }}/{{ number_format($goalStats['total_goals']) }}</strong>
                    </div>
                </div>
            </div>

            <div class="stats-panel">
                <div class="stats-panel-head">
                    <div>
                        <span class="stats-badge">Progress Bulanan</span>
                        <h2>6 Bulan Terakhir</h2>
                    </div>
                </div>

                <div class="monthly-list">
                    @foreach($monthlyProgress as $month)
                        <div class="monthly-item">
                            <div class="monthly-top">
                                <strong>{{ $month['label'] }}</strong>
                                <span>{{ number_format($month['pages']) }} halaman</span>
                            </div>
                            <div class="monthly-track">
                                <div class="monthly-fill" style="width: {{ round(($month['pages'] / $maxMonthlyPages) * 100, 2) }}%"></div>
                            </div>
                            <div class="monthly-meta">
                                <span>{{ number_format($month['books']) }} kitab</span>
                                <span>{{ number_format($month['minutes']) }} menit</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="stats-panel">
                <div class="stats-panel-head">
                    <div>
                        <span class="stats-badge">Kategori Favorit</span>
                        <h2>Distribusi Bacaan</h2>
                    </div>
                </div>

                @if($categoryBreakdown->isNotEmpty())
                    <div class="category-list">
                        @foreach($categoryBreakdown as $category)
                            <div class="category-item">
                                <div>
                                    <strong>{{ $category['label'] }}</strong>
                                    <p>{{ number_format($category['books']) }} kitab • {{ number_format($category['minutes']) }} menit</p>
                                </div>
                                <span>{{ number_format($category['pages']) }} hlm</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="stats-empty">Belum ada kategori yang cukup untuk dianalisis.</div>
                @endif
            </div>
        </div>

        <div class="stats-column side">
            <div class="stats-panel streak-panel">
                <div class="stats-panel-head">
                    <div>
                        <span class="stats-badge">Streak</span>
                        <h2>Konsistensi</h2>
                    </div>
                </div>

                <strong class="streak-number">{{ number_format($streak->current_streak) }} hari</strong>
                <p class="streak-copy">{{ $streak->getStatusMessage() }}</p>

                <div class="streak-grid">
                    <div>
                        <span>Terpanjang</span>
                        <strong>{{ number_format($streak->longest_streak) }} hari</strong>
                    </div>
                    <div>
                        <span>Total hari aktif</span>
                        <strong>{{ number_format($streak->total_days) }} hari</strong>
                    </div>
                    <div>
                        <span>Completion goals</span>
                        <strong>{{ number_format($goalStats['completion_rate']) }}%</strong>
                    </div>
                </div>
            </div>

            <div class="stats-panel">
                <div class="stats-panel-head">
                    <div>
                        <span class="stats-badge">Achievement</span>
                        <h2>Pencapaian</h2>
                    </div>
                </div>

                <div class="achievement-gallery">
                    @foreach($achievements as $achievement)
                        <div class="achievement-card {{ $achievement['unlocked'] ? 'unlocked' : 'locked' }}" title="{{ $achievement['description'] }}">
                            <div class="achievement-icon-wrapper">
                                <i class="fas {{ $achievement['icon'] }}"></i>
                                @if(!$achievement['unlocked'])
                                    <div class="lock-overlay"><i class="fas fa-lock"></i></div>
                                @endif
                            </div>
                            <div class="achievement-info">
                                <strong>{{ $achievement['name'] }}</strong>
                                <div class="achievement-progress-track">
                                    <div class="achievement-progress-fill" style="width: {{ $achievement['progress'] }}%"></div>
                                </div>
                                <small>{{ $achievement['progress'] }}%</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="stats-panel">
                <div class="stats-panel-head">
                    <div>
                        <span class="stats-badge">Aktivitas Terbaru</span>
                        <h2>Sesi Baca</h2>
                    </div>
                </div>

                @if($recentSessions->isNotEmpty())
                    <div class="session-list">
                        @foreach($recentSessions as $session)
                            <a href="{{ route('kitab.read', ['id_kitab' => $session->kitab_id, 'resume' => 1]) }}" class="session-item">
                                <div>
                                    <strong>{{ optional($session->kitab)->judul ?? 'Kitab' }}</strong>
                                    <p>{{ optional($session->kitab)->kategori ?? 'Tanpa kategori' }} • {{ optional($session->last_read_at)->diffForHumans() }}</p>
                                </div>
                                <span>{{ max((int) ($session->current_page ?? 0), 1) }} hlm</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="stats-empty">Belum ada sesi baca yang tercatat.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .stats-page { display: flex; flex-direction: column; gap: 16px; }
    .stats-hero, .stats-card, .stats-panel {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 20px;
    }
    .stats-hero {
        padding: 24px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        background: linear-gradient(135deg, rgba(27,94,59,0.08), rgba(200,169,81,0.08));
    }
    .stats-eyebrow {
        margin: 0 0 6px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--primary-color);
        font-weight: 700;
    }
    .stats-hero h1, .stats-panel h2 {
        margin: 0;
        color: var(--text-color);
        font-size: 1.45rem;
        font-weight: 800;
    }
    .stats-subtitle {
        margin: 8px 0 0;
        max-width: 620px;
        color: var(--light-text);
    }
    .stats-hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .stats-ghost-btn, .session-item {
        text-decoration: none;
        font-family: inherit;
    }
    .stats-ghost-btn {
        padding: 10px 14px;
        border-radius: 14px;
        border: 1px solid var(--border-color);
        color: var(--text-color);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--card-bg);
    }
    .stats-grid, .stats-overview-grid, .stats-main-grid, .streak-grid {
        display: grid;
        gap: 12px;
    }
    .stats-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .stats-card {
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .stats-card span, .stats-mini-card span, .streak-grid span {
        color: var(--light-text);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .stats-card strong {
        color: var(--text-color);
        font-size: 1.2rem;
    }
    .stats-card small {
        color: var(--light-text);
    }
    .stats-main-grid {
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.9fr);
    }
    .stats-column {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .stats-panel {
        padding: 20px;
    }
    .stats-panel-head {
        margin-bottom: 16px;
    }
    .stats-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(27,94,59,0.1);
        color: var(--primary-color);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
    }
    .stats-overview-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .stats-mini-card {
        padding: 16px;
        border-radius: 16px;
        background: var(--background-color);
    }
    .stats-mini-card strong, .category-item strong, .session-item strong, .achievement-content strong {
        display: block;
        color: var(--text-color);
        margin-top: 4px;
    }
    .monthly-list, .category-list, .session-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .monthly-item, .category-item, .session-item {
        padding: 14px;
        border-radius: 16px;
        background: var(--background-color);
    }
    .monthly-top, .monthly-meta, .category-item, .session-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }
    .monthly-top span, .monthly-meta span, .category-item span, .category-item p, .session-item p, .streak-copy {
        margin: 0;
        color: var(--light-text);
        font-size: 13px;
    }
    .monthly-track {
        height: 10px;
        border-radius: 999px;
        background: rgba(139,128,112,0.14);
        overflow: hidden;
        margin: 8px 0;
    }
    .monthly-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    }
    .streak-panel {
        background: linear-gradient(135deg, rgba(27,94,59,0.1), rgba(200,169,81,0.14));
    }
    .streak-number {
        display: block;
        font-size: 2rem;
        color: var(--text-color);
        margin-bottom: 6px;
    }
    .streak-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 14px;
    }
    .streak-grid div {
        padding: 14px;
        border-radius: 16px;
        background: rgba(255,255,255,0.45);
    }
    .streak-grid strong {
        display: block;
        margin-top: 4px;
        color: var(--text-color);
    }
    .achievement-gallery { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .achievement-card {
        padding: 16px 12px; border-radius: 16px; background: var(--background-color);
        display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px;
        border: 1px solid var(--border-color); cursor: default;
    }
    .achievement-card.unlocked {
        background: linear-gradient(135deg, rgba(27,94,59,0.05), rgba(200,169,81,0.05));
        border-color: rgba(200,169,81,0.4);
    }
    .achievement-card.locked { opacity: 0.6; filter: grayscale(100%); }
    .achievement-icon-wrapper {
        width: 48px; height: 48px; border-radius: 50%; background: rgba(27,94,59,0.1);
        color: var(--primary-color); display: flex; align-items: center; justify-content: center;
        font-size: 20px; position: relative;
    }
    .achievement-card.unlocked .achievement-icon-wrapper {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white; box-shadow: 0 4px 10px rgba(27,94,59,0.2);
    }
    .lock-overlay {
        position: absolute; bottom: -2px; right: -2px; width: 20px; height: 20px; background: var(--card-bg);
        border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px;
        color: var(--light-text); box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .achievement-info { width: 100%; display: flex; flex-direction: column; gap: 6px; }
    .achievement-info strong { font-size: 12px; color: var(--text-color); margin: 0; line-height: 1.2; }
    .achievement-progress-track { height: 6px; border-radius: 999px; background: rgba(139,128,112,0.14); overflow: hidden; }
    .achievement-progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); }
    .achievement-info small { font-size: 11px; color: var(--light-text); margin: 0; }
    .stats-empty {
        padding: 14px 16px;
        border-radius: 16px;
        background: var(--background-color);
        color: var(--light-text);
    }
    .session-item {
        color: inherit;
    }
    @media (max-width: 1024px) {
        .stats-grid,
        .stats-main-grid,
        .stats-overview-grid,
        .streak-grid {
            grid-template-columns: 1fr;
        }
        .stats-hero {
            flex-direction: column;
        }
    }
</style>
@endsection
