@extends('TemplateUser')

@section('konten')
<div class="leaderboard-page">
    <div class="leaderboard-hero">
        <div>
            <p class="leaderboard-eyebrow">Leaderboard</p>
            <h1>Papan Peringkat Santri</h1>
            <p class="leaderboard-subtitle">Berlomba-lombalah dalam kebaikan. Pertahankan streak membacamu dan raih peringkat teratas di komunitas Al-Kutub.</p>
        </div>
        <div class="leaderboard-hero-actions">
            <a href="{{ route('reading-goals.index') }}" class="leaderboard-ghost-btn">
                <i class="fas fa-bullseye"></i> Goals
            </a>
            <a href="{{ route('reading-statistics.index') }}" class="leaderboard-ghost-btn">
                <i class="fas fa-chart-line"></i> Statistik
            </a>
        </div>
    </div>

    <!-- Top 3 Podium (Optional, simple version first) -->
    @if(count($leaderboard) >= 3)
    <div class="podium-container">
        <!-- Rank 2 -->
        <div class="podium-item rank-2">
            <div class="podium-badge"><i class="fas fa-medal" style="color: #C0C0C0;"></i> 2</div>
            <div class="podium-avatar"><i class="fas fa-user"></i></div>
            <strong>{{ $leaderboard[1]['username'] }}</strong>
            <span>{{ number_format($leaderboard[1]['current_streak']) }} hari</span>
            <div class="podium-bar" style="height: 120px;"></div>
        </div>
        <!-- Rank 1 -->
        <div class="podium-item rank-1">
            <div class="podium-badge gold"><i class="fas fa-crown" style="color: #FFD700;"></i> 1</div>
            <div class="podium-avatar gold"><i class="fas fa-user-check"></i></div>
            <strong>{{ $leaderboard[0]['username'] }}</strong>
            <span>{{ number_format($leaderboard[0]['current_streak']) }} hari</span>
            <div class="podium-bar gold" style="height: 160px;"></div>
        </div>
        <!-- Rank 3 -->
        <div class="podium-item rank-3">
            <div class="podium-badge"><i class="fas fa-medal" style="color: #CD7F32;"></i> 3</div>
            <div class="podium-avatar"><i class="fas fa-user"></i></div>
            <strong>{{ $leaderboard[2]['username'] }}</strong>
            <span>{{ number_format($leaderboard[2]['current_streak']) }} hari</span>
            <div class="podium-bar" style="height: 90px;"></div>
        </div>
    </div>
    @endif

    <div class="leaderboard-list">
        @foreach($leaderboard as $index => $entry)
            <div class="leaderboard-item {{ $user->username === $entry['username'] ? 'is-me' : '' }}">
                <div class="leaderboard-rank">
                    @if($index === 0)
                        <i class="fas fa-crown" style="color: #FFD700;"></i>
                    @elseif($index === 1)
                        <span style="color: #C0C0C0; font-weight: bold;">2</span>
                    @elseif($index === 2)
                        <span style="color: #CD7F32; font-weight: bold;">3</span>
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
                <div class="leaderboard-user">
                    <div class="leaderboard-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <strong>{{ $entry['username'] }} {!! $user->username === $entry['username'] ? '<span class="you-badge">(Kamu)</span>' : '' !!}</strong>
                </div>
                <div class="leaderboard-score">
                    <i class="fas fa-fire streak-icon"></i> <strong>{{ number_format($entry['current_streak']) }} <small>hari</small></strong>
                </div>
            </div>
        @endforeach
    </div>

    @if($userRank && $userRank > count($leaderboard))
        <div class="leaderboard-item is-me sticky-bottom">
            <div class="leaderboard-rank">{{ $userRank }}</div>
            <div class="leaderboard-user">
                <div class="leaderboard-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <strong>{{ $user->username }} <span class="you-badge">(Kamu)</span></strong>
            </div>
            <div class="leaderboard-score">
                <i class="fas fa-fire streak-icon"></i> <strong>{{ number_format(optional($userStreak)->current_streak ?? 0) }} <small>hari</small></strong>
            </div>
        </div>
    @endif
</div>

<style>
    .leaderboard-page { display: flex; flex-direction: column; gap: 24px; padding-bottom: 24px; }
    .leaderboard-hero {
        padding: 24px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        background: linear-gradient(135deg, rgba(27,94,59,0.08), rgba(200,169,81,0.08));
        border: 1px solid var(--border-color);
        border-radius: 20px;
    }
    .leaderboard-eyebrow {
        margin: 0 0 6px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--primary-color);
        font-weight: 700;
    }
    .leaderboard-hero h1 { margin: 0; color: var(--text-color); font-size: 1.45rem; font-weight: 800; }
    .leaderboard-subtitle { margin: 8px 0 0; max-width: 620px; color: var(--light-text); }
    .leaderboard-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .leaderboard-ghost-btn {
        padding: 10px 14px; border-radius: 14px; border: 1px solid var(--border-color);
        color: var(--text-color); font-weight: 600; display: inline-flex; align-items: center; gap: 8px;
        background: var(--card-bg); text-decoration: none; font-family: inherit;
    }
    
    /* Podium */
    .podium-container {
        display: flex; justify-content: center; align-items: flex-end; gap: 16px; margin: 20px 0;
    }
    .podium-item {
        display: flex; flex-direction: column; align-items: center; width: 100px;
    }
    .podium-avatar {
        width: 60px; height: 60px; border-radius: 50%; background: var(--background-color); 
        border: 2px solid var(--border-color); display: flex; justify-content: center; align-items: center;
        font-size: 24px; color: var(--light-text); margin-bottom: 8px; position: relative;
    }
    .podium-avatar.gold {
        border-color: #FFD700; width: 70px; height: 70px; font-size: 30px;
        box-shadow: 0 0 15px rgba(255,215,0,0.3); color: #FFD700;
    }
    .podium-badge {
        background: var(--card-bg); padding: 4px 10px; border-radius: 12px; font-weight: bold;
        font-size: 12px; margin-bottom: -15px; z-index: 10; border: 1px solid var(--border-color);
    }
    .podium-badge.gold { border-color: #FFD700; color: #FFD700; }
    .podium-item strong { text-align: center; font-size: 14px; color: var(--text-color); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%; }
    .podium-item span { font-size: 12px; color: var(--primary-color); font-weight: bold; margin-bottom: 8px; }
    .podium-bar {
        width: 100%; background: rgba(27,94,59,0.1); border-top-left-radius: 8px; border-top-right-radius: 8px;
    }
    .podium-bar.gold { background: rgba(200,169,81,0.2); }

    /* List */
    .leaderboard-list {
        display: flex; flex-direction: column; gap: 12px;
    }
    .leaderboard-item {
        display: flex; align-items: center; padding: 16px 20px; border-radius: 16px;
        background: var(--card-bg); border: 1px solid var(--border-color);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .leaderboard-item.is-me {
        background: linear-gradient(135deg, rgba(27,94,59,0.05), rgba(200,169,81,0.05));
        border-color: var(--primary-color);
    }
    .leaderboard-rank {
        width: 40px; font-size: 18px; font-weight: 800; color: var(--light-text); text-align: center;
    }
    .leaderboard-user {
        flex: 1; display: flex; align-items: center; gap: 12px; margin-left: 12px;
    }
    .leaderboard-avatar {
        width: 40px; height: 40px; border-radius: 50%; background: var(--background-color);
        display: flex; justify-content: center; align-items: center; color: var(--light-text);
    }
    .leaderboard-user strong { color: var(--text-color); font-size: 15px; }
    .you-badge { color: var(--primary-color); font-size: 12px; font-weight: 600; margin-left: 4px; }
    .leaderboard-score {
        text-align: right; font-size: 16px; display: flex; align-items: center; gap: 6px;
    }
    .streak-icon { color: #F59E0B; }
    .leaderboard-score strong { color: var(--text-color); }
    .leaderboard-score small { color: var(--light-text); font-weight: normal; }

    .sticky-bottom {
        position: sticky; bottom: 20px; z-index: 100; box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .leaderboard-hero { flex-direction: column; }
    }
</style>
@endsection
