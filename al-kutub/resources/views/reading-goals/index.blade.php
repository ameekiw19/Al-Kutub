@extends('TemplateUser')

@section('konten')
<div class="goal-page">
    <div class="goal-hero">
        <div>
            <p class="goal-eyebrow">Reading Goals</p>
            <h1>Target baca harian dan mingguanmu</h1>
            <p class="goal-subtitle">Pantau progres halaman, durasi baca, streak, dan pencapaian dalam satu tempat.</p>
        </div>
        <div class="goal-hero-actions">
            <a href="{{ route('reading-notes.index') }}" class="goal-ghost-btn">
                <i class="fas fa-sticky-note"></i> Catatan
            </a>
            <a href="{{ route('reading-statistics.index') }}" class="goal-ghost-btn">
                <i class="fas fa-chart-line"></i> Statistik
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="goal-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="goal-summary-grid">
        <div class="goal-summary-card">
            <span class="goal-summary-label">Kitab Dibaca</span>
            <strong>{{ number_format($summary['books_read']) }}</strong>
        </div>
        <div class="goal-summary-card">
            <span class="goal-summary-label">Halaman Terbaca</span>
            <strong>{{ number_format($summary['pages_read']) }}</strong>
        </div>
        <div class="goal-summary-card">
            <span class="goal-summary-label">Durasi Baca</span>
            <strong>{{ number_format($summary['minutes_read']) }} menit</strong>
        </div>
        <div class="goal-summary-card">
            <span class="goal-summary-label">Catatan</span>
            <strong>{{ number_format($summary['notes_count']) }}</strong>
        </div>
    </div>

    <div class="goal-main-grid">
        <div class="goal-column">
            <div class="goal-card">
                <div class="goal-card-head">
                    <div>
                        <span class="goal-badge">Harian</span>
                        <h2>Goal Hari Ini</h2>
                    </div>
                    <span class="goal-complete {{ $dailyGoal->is_completed ? 'done' : '' }}">
                        {{ $dailyGoal->is_completed ? 'Tercapai' : 'Berjalan' }}
                    </span>
                </div>

                <div class="goal-progress-block">
                    <div class="goal-progress-row">
                        <span>Durasi</span>
                        <strong>{{ number_format($dailyGoal->current_minutes) }}/{{ number_format($dailyGoal->target_minutes) }} menit</strong>
                    </div>
                    <div class="goal-progress-track">
                        <div class="goal-progress-fill" style="width: {{ $dailyGoal->minutes_progress }}%"></div>
                    </div>
                </div>

                <div class="goal-progress-block">
                    <div class="goal-progress-row">
                        <span>Halaman</span>
                        <strong>{{ number_format($dailyGoal->current_pages) }}/{{ number_format($dailyGoal->target_pages) }} halaman</strong>
                    </div>
                    <div class="goal-progress-track">
                        <div class="goal-progress-fill gold" style="width: {{ $dailyGoal->pages_progress }}%"></div>
                    </div>
                </div>

                <div class="goal-meta">
                    <span><i class="fas fa-calendar-day"></i> {{ optional($dailyGoal->start_date)->translatedFormat('d M Y') }}</span>
                    <span><i class="fas fa-bullseye"></i> {{ number_format($dailyGoal->overall_progress) }}%</span>
                </div>
            </div>

            <div class="goal-card">
                <div class="goal-card-head">
                    <div>
                        <span class="goal-badge">Mingguan</span>
                        <h2>Goal Pekan Ini</h2>
                    </div>
                    <span class="goal-complete {{ $weeklyGoal->is_completed ? 'done' : '' }}">
                        {{ $weeklyGoal->is_completed ? 'Tercapai' : 'Berjalan' }}
                    </span>
                </div>

                <div class="goal-progress-block">
                    <div class="goal-progress-row">
                        <span>Durasi</span>
                        <strong>{{ number_format($weeklyGoal->current_minutes) }}/{{ number_format($weeklyGoal->target_minutes) }} menit</strong>
                    </div>
                    <div class="goal-progress-track">
                        <div class="goal-progress-fill" style="width: {{ $weeklyGoal->minutes_progress }}%"></div>
                    </div>
                </div>

                <div class="goal-progress-block">
                    <div class="goal-progress-row">
                        <span>Halaman</span>
                        <strong>{{ number_format($weeklyGoal->current_pages) }}/{{ number_format($weeklyGoal->target_pages) }} halaman</strong>
                    </div>
                    <div class="goal-progress-track">
                        <div class="goal-progress-fill gold" style="width: {{ $weeklyGoal->pages_progress }}%"></div>
                    </div>
                </div>

                <div class="goal-meta">
                    <span><i class="fas fa-calendar-week"></i> {{ optional($weeklyGoal->start_date)->translatedFormat('d M') }} - {{ optional($weeklyGoal->end_date)->translatedFormat('d M Y') }}</span>
                    <span><i class="fas fa-bullseye"></i> {{ number_format($weeklyGoal->overall_progress) }}%</span>
                </div>
            </div>

            <div class="goal-card">
                <div class="goal-card-head">
                    <div>
                        <span class="goal-badge">Pengaturan</span>
                        <h2>Atur Target</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('reading-goals.update') }}" class="goal-form">
                    @csrf
                    @method('PUT')

                    <div class="goal-form-grid">
                        <label>
                            <span>Target harian (menit)</span>
                            <input type="number" name="daily_target_minutes" min="5" max="480" value="{{ old('daily_target_minutes', $dailyGoal->target_minutes) }}" required>
                        </label>
                        <label>
                            <span>Target harian (halaman)</span>
                            <input type="number" name="daily_target_pages" min="1" max="300" value="{{ old('daily_target_pages', $dailyGoal->target_pages) }}" required>
                        </label>
                        <label>
                            <span>Target mingguan (menit)</span>
                            <input type="number" name="weekly_target_minutes" min="30" max="3000" value="{{ old('weekly_target_minutes', $weeklyGoal->target_minutes) }}" required>
                        </label>
                        <label>
                            <span>Target mingguan (halaman)</span>
                            <input type="number" name="weekly_target_pages" min="7" max="1500" value="{{ old('weekly_target_pages', $weeklyGoal->target_pages) }}" required>
                        </label>
                    </div>

                    <button type="submit" class="goal-primary-btn">
                        <i class="fas fa-save"></i> Simpan Target
                    </button>
                </form>
            </div>
        </div>

        <div class="goal-column side">
            <div class="goal-card">
                <div class="goal-card-head">
                    <div>
                        <span class="goal-badge">Streak</span>
                        <h2>Konsistensi Baca</h2>
                    </div>
                </div>

                <div class="streak-hero">
                    <strong>{{ number_format($streak->current_streak) }} hari</strong>
                    <p>{{ $streak->getStatusMessage() }}</p>
                </div>

                <div class="goal-mini-stats">
                    <div>
                        <span>Streak terpanjang</span>
                        <strong>{{ number_format($streak->longest_streak) }} hari</strong>
                    </div>
                    <div>
                        <span>Total hari aktif</span>
                        <strong>{{ number_format($streak->total_days) }} hari</strong>
                    </div>
                    <div>
                        <span>Completion rate</span>
                        <strong>{{ number_format($goalStats['completion_rate']) }}%</strong>
                    </div>
                </div>
            </div>

            <div class="goal-card">
                <div class="goal-card-head">
                    <div>
                        <span class="goal-badge">Pencapaian</span>
                        <h2>Achievement</h2>
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

            <div class="goal-card">
                <div class="goal-card-head">
                    <div>
                        <span class="goal-badge">Aktivitas Terakhir</span>
                        <h2>Sesi Baca Terbaru</h2>
                    </div>
                </div>

                @if($recentHistory->isNotEmpty())
                    <div class="goal-recent-list">
                        @foreach($recentHistory as $history)
                            <a href="{{ route('kitab.read', ['id_kitab' => $history->kitab_id, 'resume' => 1]) }}" class="goal-recent-item">
                                <div>
                                    <strong>{{ optional($history->kitab)->judul ?? 'Kitab' }}</strong>
                                    <p>{{ optional($history->last_read_at)->diffForHumans() }}</p>
                                </div>
                                <span>Hal. {{ max((int) ($history->current_page ?? 0), 1) }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="goal-empty">
                        Belum ada sesi baca yang tercatat.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .goal-page { display: flex; flex-direction: column; gap: 16px; }
    .goal-hero, .goal-card, .goal-summary-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 20px;
    }
    .goal-hero {
        padding: 24px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        background: linear-gradient(135deg, rgba(27,94,59,0.08), rgba(200,169,81,0.08));
    }
    .goal-eyebrow {
        margin: 0 0 6px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--primary-color);
        font-weight: 700;
    }
    .goal-hero h1, .goal-card h2 {
        margin: 0;
        color: var(--text-color);
        font-size: 1.45rem;
        font-weight: 800;
    }
    .goal-subtitle {
        margin: 8px 0 0;
        max-width: 620px;
        color: var(--light-text);
    }
    .goal-hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .goal-ghost-btn, .goal-primary-btn, .goal-recent-item {
        text-decoration: none;
        font-family: inherit;
    }
    .goal-ghost-btn {
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
    .goal-summary-grid, .goal-form-grid {
        display: grid;
        gap: 12px;
    }
    .goal-summary-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .goal-summary-card {
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .goal-summary-label {
        font-size: 12px;
        color: var(--light-text);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .goal-summary-card strong {
        font-size: 1.2rem;
        color: var(--text-color);
    }
    .goal-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.9fr);
        gap: 16px;
    }
    .goal-column {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .goal-card {
        padding: 20px;
    }
    .goal-card-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 18px;
    }
    .goal-badge {
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
    .goal-complete {
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(245,158,11,0.12);
        color: #b45309;
        font-size: 12px;
        font-weight: 700;
    }
    .goal-complete.done {
        background: rgba(34,197,94,0.12);
        color: #15803d;
    }
    .goal-progress-block + .goal-progress-block {
        margin-top: 16px;
    }
    .goal-progress-row, .goal-meta {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
    }
    .goal-progress-row span, .goal-meta span, .achievement-body p, .goal-recent-item p {
        color: var(--light-text);
        font-size: 13px;
        margin: 0;
    }
    .goal-progress-row strong, .goal-recent-item strong {
        color: var(--text-color);
    }
    .goal-progress-track {
        margin-top: 8px;
        height: 10px;
        border-radius: 999px;
        background: rgba(139,128,112,0.14);
        overflow: hidden;
    }
    .goal-progress-track.compact {
        height: 8px;
    }
    .goal-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    }
    .goal-progress-fill.gold {
        background: linear-gradient(135deg, var(--accent-color), #d4b466);
    }
    .goal-meta {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
    }
    .goal-form label {
        display: flex;
        flex-direction: column;
        gap: 8px;
        color: var(--text-color);
        font-weight: 600;
    }
    .goal-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 16px;
    }
    .goal-form input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        background: var(--background-color);
        color: var(--text-color);
        font-family: inherit;
    }
    .goal-primary-btn {
        border: none;
        padding: 12px 16px;
        border-radius: 14px;
        color: white;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .streak-hero {
        padding: 18px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(27,94,59,0.12), rgba(200,169,81,0.16));
        margin-bottom: 14px;
    }
    .streak-hero strong {
        display: block;
        font-size: 1.6rem;
        color: var(--text-color);
        margin-bottom: 4px;
    }
    .streak-hero p {
        margin: 0;
        color: var(--light-text);
    }
    .goal-mini-stats {
        display: grid;
        gap: 12px;
    }
    .goal-mini-stats div {
        padding: 14px;
        border-radius: 16px;
        background: var(--background-color);
    }
    .goal-mini-stats span {
        display: block;
        font-size: 12px;
        color: var(--light-text);
        margin-bottom: 4px;
    }
    .goal-recent-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
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
    .goal-recent-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        padding: 14px;
        border-radius: 16px;
        background: var(--background-color);
        color: inherit;
    }
    .goal-empty, .goal-alert-success {
        padding: 14px 16px;
        border-radius: 16px;
    }
    .goal-empty {
        background: var(--background-color);
        color: var(--light-text);
    }
    .goal-alert-success {
        background: rgba(34,197,94,0.12);
        color: #166534;
        border: 1px solid rgba(34,197,94,0.2);
    }
    @media (max-width: 1024px) {
        .goal-summary-grid,
        .goal-main-grid,
        .goal-form-grid {
            grid-template-columns: 1fr;
        }
        .goal-hero {
            flex-direction: column;
        }
    }
</style>
@endsection
