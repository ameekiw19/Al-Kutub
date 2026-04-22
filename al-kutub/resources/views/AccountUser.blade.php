@extends('TemplateUser')

@section('konten')
<div class="acc-page">
    <!-- Profile Header Card -->
    <div class="acc-profile-card">
        <div class="acc-profile-bg"></div>
        <div class="acc-profile-body">
            <div class="acc-avatar">
                <span>{{ strtoupper(substr(Auth::user()->username, 0, 1)) }}</span>
            </div>
            <h3 class="acc-name">{{ Auth::user()->username }}</h3>
            <p class="acc-email">{{ Auth::user()->email }}</p>
            <span class="acc-role-badge">Member</span>
        </div>

        <!-- Quick Actions -->
        <div class="acc-quick-actions">
            @if(Auth::user()->hasTwoFactorEnabled())
                <div class="acc-2fa-status active">
                    <i class="fas fa-shield-alt"></i>
                    <span>2FA Aktif</span>
                </div>
                <a href="{{ route('2fa.manage') }}" class="acc-action-btn">
                    <i class="fas fa-cog"></i> Kelola 2FA
                </a>
            @else
                <div class="acc-2fa-status inactive">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>2FA Nonaktif</span>
                </div>
                <a href="{{ route('2fa.setup') }}" class="acc-action-btn green">
                    <i class="fas fa-shield-alt"></i> Aktifkan 2FA
                </a>
            @endif

            <a href="{{ url('logout') }}" class="acc-action-btn danger">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>

    <div class="acc-feature-grid">
        <a href="{{ route('notifications.index') }}" class="acc-feature-card">
            <div class="acc-feature-icon"><i class="fas fa-bell"></i></div>
            <div>
                <strong>Notifikasi</strong>
                <p>Lihat update kitab dan info terbaru</p>
            </div>
        </a>
        <a href="{{ route('reading-notes.index') }}" class="acc-feature-card">
            <div class="acc-feature-icon"><i class="fas fa-sticky-note"></i></div>
            <div>
                <strong>Catatan Baca</strong>
                <p>Kelola highlight dan catatan pribadimu</p>
            </div>
        </a>
        <a href="{{ route('reading-goals.index') }}" class="acc-feature-card">
            <div class="acc-feature-icon"><i class="fas fa-bullseye"></i></div>
            <div>
                <strong>Reading Goals</strong>
                <p>Atur target baca harian dan mingguan</p>
            </div>
        </a>
        <a href="{{ route('reading-statistics.index') }}" class="acc-feature-card">
            <div class="acc-feature-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <strong>Reading Statistics</strong>
                <p>Pantau progres, streak, dan performa baca</p>
            </div>
        </a>
    </div>

    <div class="acc-insight-strip">
        <div class="acc-insight-item">
            <span>Kitab Dibaca</span>
            <strong>{{ number_format($kitabDibaca ?? 0) }}</strong>
        </div>
        <div class="acc-insight-item">
            <span>Bookmark</span>
            <strong>{{ number_format($bookmark ?? 0) }}</strong>
        </div>
        <div class="acc-insight-item">
            <span>Komentar</span>
            <strong>{{ number_format($komentar ?? 0) }}</strong>
        </div>
    </div>

    <!-- Edit Profile Section Card -->
    <div class="acc-section-card">
        <h3 class="acc-section-title"><i class="fas fa-user-edit"></i> Edit Profil</h3>
        <form action="{{ route('user.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="acc-form-group">
                <label>Username</label>
                <div class="acc-input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" value="{{ Auth::user()->username }}" required>
                </div>
            </div>

            <div class="acc-form-group">
                <label>Email</label>
                <div class="acc-input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" value="{{ Auth::user()->email }}" required>
                </div>
            </div>

            <div class="acc-form-group">
                <label>Password Baru <small style="color:var(--light-text)">(opsional)</small></label>
                <div class="acc-input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">
                </div>
            </div>

            <div class="acc-form-group">
                <label>Konfirmasi Password</label>
                <div class="acc-input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password_confirmation" placeholder="Ulangi password baru">
                </div>
            </div>

            <button type="submit" class="acc-save-btn">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<style>
    .acc-page { max-width: 600px; margin: 0 auto; }

    /* Profile Card */
    .acc-profile-card {
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 20px; overflow: hidden; margin-bottom: 16px;
    }
    .acc-profile-bg {
        height: 100px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light), var(--primary-color));
        position: relative;
    }
    .acc-profile-bg::after {
        content: 'بِسْمِ اللَّهِ'; position: absolute; bottom: 10px; right: 16px;
        font-family: serif; font-size: 16px; color: rgba(200,169,81,0.25);
    }
    .acc-profile-body { text-align: center; padding: 0 20px 20px; }
    .acc-avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white; font-size: 32px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        margin: -40px auto 12px; border: 4px solid var(--card-bg);
        box-shadow: 0 4px 12px rgba(27,94,59,0.2);
    }
    .acc-name { font-size: 18px; font-weight: 700; color: var(--text-color); margin-bottom: 4px; }
    .acc-email { font-size: 13px; color: var(--light-text); margin-bottom: 10px; }
    .acc-role-badge {
        display: inline-block; background: #F0F7F3; color: var(--primary-color);
        padding: 4px 14px; border-radius: 12px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    body.dark-mode .acc-role-badge { background: rgba(27,94,59,0.15); }

    /* Quick Actions */
    .acc-quick-actions {
        padding: 16px 20px; border-top: 1px solid var(--border-color);
        display: flex; flex-direction: column; gap: 10px;
    }
    .acc-2fa-status {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-radius: 12px; font-size: 13px; font-weight: 600;
    }
    .acc-2fa-status.active { background: #F0F7F3; color: var(--primary-color); }
    .acc-2fa-status.inactive { background: #FFF8E1; color: #F59E0B; }
    body.dark-mode .acc-2fa-status.active { background: rgba(27,94,59,0.15); }
    body.dark-mode .acc-2fa-status.inactive { background: rgba(245,158,11,0.1); }

    .acc-action-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px; border-radius: 14px; font-size: 13px; font-weight: 600;
        text-decoration: none; transition: 0.2s; cursor: pointer;
        background: var(--background-color); color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    .acc-action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.04); }
    .acc-action-btn.green { background: var(--primary-color); color: white; border-color: var(--primary-color); }
    .acc-action-btn.green:hover { box-shadow: 0 4px 12px rgba(27,94,59,0.25); }
    .acc-action-btn.danger { background: #FEF2F2; color: #EF4444; border-color: #FECACA; }
    .acc-action-btn.danger:hover { background: #EF4444; color: white; border-color: #EF4444; }

    .acc-feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .acc-feature-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 16px;
        display: flex;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: 0.2s;
    }
    .acc-feature-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.05);
    }
    .acc-feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: rgba(27,94,59,0.1);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .acc-feature-card strong {
        display: block;
        color: var(--text-color);
        margin-bottom: 4px;
    }
    .acc-feature-card p {
        margin: 0;
        color: var(--light-text);
        font-size: 12px;
        line-height: 1.5;
    }

    .acc-insight-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .acc-insight-item {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 16px;
    }
    .acc-insight-item span {
        display: block;
        font-size: 12px;
        color: var(--light-text);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .acc-insight-item strong {
        color: var(--text-color);
        font-size: 1.1rem;
    }

    /* Section Card */
    .acc-section-card {
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 20px; padding: 20px; margin-bottom: 16px;
    }
    .acc-section-title {
        font-size: 16px; font-weight: 700; color: var(--text-color);
        margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }
    .acc-section-title i { color: var(--accent-color); }

    .acc-form-group { margin-bottom: 16px; }
    .acc-form-group label {
        display: block; margin-bottom: 6px; font-size: 13px;
        font-weight: 600; color: var(--text-color);
    }
    .acc-input-wrap {
        position: relative;
    }
    .acc-input-wrap i {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: var(--light-text); font-size: 14px;
    }
    .acc-input-wrap input {
        width: 100%; padding: 12px 14px 12px 40px;
        border: 1px solid var(--border-color); border-radius: 14px;
        font-size: 14px; background: var(--background-color);
        color: var(--text-color); font-family: 'Poppins', sans-serif;
        transition: 0.3s;
    }
    .acc-input-wrap input:focus {
        outline: none; border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(27,94,59,0.08);
    }

    .acc-save-btn {
        width: 100%; padding: 14px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white; border: none; border-radius: 16px;
        font-size: 14px; font-weight: 700; cursor: pointer;
        transition: 0.3s; display: flex; align-items: center;
        justify-content: center; gap: 8px;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 4px 12px rgba(27,94,59,0.2);
        margin-top: 6px;
    }
    .acc-save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(27,94,59,0.3); }

    @media (max-width: 480px) {
        .acc-feature-grid,
        .acc-insight-strip { grid-template-columns: 1fr; }
        .acc-avatar { width: 64px; height: 64px; font-size: 24px; margin-top: -32px; }
        .acc-name { font-size: 16px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                confirmButtonColor: '#1B5E3B'
            });
        @endif
    });
</script>
@endsection
