@extends('TemplateUser')

@section('konten')
<div class="acc-page">
    <!-- Profile Header Card -->
    <div class="acc-profile-card">
        <div class="acc-profile-bg"></div>
        <div class="acc-profile-body">
            <div class="acc-avatar" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                <i class="fas fa-shield-alt" style="font-size: 28px;"></i>
            </div>
            <h3 class="acc-name">Two-Factor Auth</h3>
            <p class="acc-email">Keamanan Ekstra</p>
            <span class="acc-role-badge" style="background:#FFF8E1; color:#F59E0B;">Security</span>
        </div>
        <div class="acc-quick-actions">
            <a href="{{ url('/home') }}" class="acc-action-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- 2FA Management Card -->
    <div class="acc-section-card">
        <h3 class="acc-section-title"><i class="fas fa-shield-alt"></i> Pengaturan Two-Factor Authentication</h3>

        @if (session('error'))
            <div class="tfa-alert danger"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="tfa-alert success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <!-- Status Section -->
        <div class="tfa-step">
            <h4 class="tfa-step-title"><i class="fas fa-info-circle"></i> Status 2FA</h4>
            @if($twoFactorAuth && $twoFactorAuth->is_enabled)
                <div class="tfa-status-box active">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>2FA Aktif</strong>
                        <small style="display:block;">Akun Anda dilindungi dengan keamanan ekstra</small>
                    </div>
                </div>
                <div style="margin-top:10px; font-size:12px; color:var(--light-text);">
                    <p>Diaktifkan: {{ $twoFactorAuth->enabled_at ? $twoFactorAuth->enabled_at->format('d M Y, H:i') : 'Tidak diketahui' }}</p>
                    <p>Terakhir digunakan: {{ $twoFactorAuth->last_used_at ? $twoFactorAuth->last_used_at->format('d M Y, H:i') : 'Belum pernah' }}</p>
                </div>
            @else
                <div class="tfa-status-box inactive">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>2FA Tidak Aktif</strong>
                        <small style="display:block;">Akun Anda rentan terhadap serangan</small>
                    </div>
                </div>
            @endif
        </div>

        @if($twoFactorAuth && $twoFactorAuth->is_enabled)
            <!-- Backup Codes Section -->
            <div class="tfa-step">
                <h4 class="tfa-step-title"><i class="fas fa-key"></i> Backup Codes</h4>
                <div class="tfa-alert info">
                    <i class="fas fa-info-circle"></i>
                    Backup codes adalah kode 8-digit yang bisa digunakan jika Anda kehilangan akses ke aplikasi authenticator.
                </div>

                <div class="tfa-backup-grid">
                    @foreach($backupCodes as $index => $code)
                        <div class="tfa-backup-item">
                            <span class="tfa-backup-num">{{ $index + 1 }}</span>
                            <span class="tfa-backup-code">{{ $code }}</span>
                        </div>
                    @endforeach
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
                    <button type="button" class="acc-action-btn green" onclick="downloadBackupCodes()" style="flex:1;">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <form action="{{ route('2fa.regenerate-backup-codes') }}" method="POST" style="flex:1;">
                        @csrf
                        <button type="submit" class="acc-action-btn" style="width:100%;" onclick="return confirm('Apakah Anda yakin ingin generate backup codes baru? Kode lama tidak akan berlaku lagi.')">
                            <i class="fas fa-sync"></i> Generate Ulang
                        </button>
                    </form>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="tfa-step">
                <h4 class="tfa-step-title"><i class="fas fa-lightbulb"></i> Tips Keamanan</h4>
                <div class="tfa-tips">
                    <div class="tfa-tip"><i class="fas fa-check" style="color:var(--primary-color);"></i> <span>Simpan backup codes di tempat yang aman dan terpisah</span></div>
                    <div class="tfa-tip"><i class="fas fa-check" style="color:var(--primary-color);"></i> <span>Gunakan aplikasi authenticator yang terpercaya</span></div>
                    <div class="tfa-tip"><i class="fas fa-check" style="color:var(--primary-color);"></i> <span>Perbarui backup codes jika dicurigai bocor</span></div>
                    <div class="tfa-tip"><i class="fas fa-check" style="color:var(--primary-color);"></i> <span>Enable 2FA di semua akun penting Anda</span></div>
                </div>
            </div>

            <!-- Disable 2FA -->
            <div class="tfa-step" style="border-bottom:none;">
                <h4 class="tfa-step-title" style="color:#EF4444;"><i class="fas fa-exclamation-triangle" style="color:#EF4444;"></i> Nonaktifkan 2FA</h4>
                <div class="tfa-alert warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Menonaktifkan 2FA akan mengurangi keamanan akun Anda.
                </div>
                <button type="button" class="acc-action-btn danger" id="btnShowDisable" style="width:100%;">
                    <i class="fas fa-times-circle"></i> Nonaktifkan 2FA
                </button>

                <!-- Inline disable form (hidden by default) -->
                <div id="disableFormWrap" style="display:none; margin-top:14px;">
                    <form action="{{ route('2fa.disable') }}" method="POST">
                        @csrf
                        <div class="acc-form-group">
                            <label>Password</label>
                            <div class="acc-input-wrap">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" required placeholder="Masukkan password">
                            </div>
                        </div>
                        <div class="acc-form-group">
                            <label>Kode 2FA</label>
                            <div class="acc-input-wrap">
                                <i class="fas fa-shield-alt"></i>
                                <input type="text" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code">
                            </div>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button type="button" class="acc-action-btn" style="flex:1;" onclick="document.getElementById('disableFormWrap').style.display='none';">Batal</button>
                            <button type="submit" class="acc-action-btn danger" style="flex:1;"><i class="fas fa-times-circle"></i> Nonaktifkan</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Enable 2FA -->
            <div class="tfa-step" style="border-bottom:none;">
                <div class="tfa-alert info">
                    <i class="fas fa-info-circle"></i>
                    Lindungi akun Anda dengan keamanan ekstra. 2FA membutuhkan kode dari aplikasi authenticator selain password.
                </div>
                <a href="{{ route('2fa.setup') }}" class="acc-save-btn" style="text-decoration:none; display:flex;">
                    <i class="fas fa-shield-alt"></i> Aktifkan Two-Factor Authentication
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    .tfa-step { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); }
    .tfa-step-title { font-size: 14px; font-weight: 700; color: var(--primary-color); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .tfa-step-title i { color: var(--accent-color); }

    .tfa-alert { padding: 12px 14px; border-radius: 14px; font-size: 13px; display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
    .tfa-alert.info { background: #F0F7F3; color: var(--primary-color); }
    .tfa-alert.success { background: #F0FDF4; color: #16A34A; }
    .tfa-alert.danger { background: #FEF2F2; color: #EF4444; }
    .tfa-alert.warning { background: #FFFBEB; color: #D97706; }
    body.dark-mode .tfa-alert.info { background: rgba(27,94,59,0.1); }

    .tfa-status-box { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 14px; font-size: 13px; }
    .tfa-status-box.active { background: #F0F7F3; color: var(--primary-color); }
    .tfa-status-box.inactive { background: #FFFBEB; color: #D97706; }
    .tfa-status-box i { font-size: 24px; }
    body.dark-mode .tfa-status-box.active { background: rgba(27,94,59,0.1); }

    .tfa-backup-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; margin: 12px 0; }
    .tfa-backup-item { display: flex; align-items: center; gap: 10px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px 12px; transition: 0.2s; }
    .tfa-backup-item:hover { border-color: var(--primary-color); }
    .tfa-backup-num { width: 24px; height: 24px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
    .tfa-backup-code { font-family: monospace; font-weight: 600; font-size: 13px; color: var(--text-color); }

    .tfa-tips { display: flex; flex-direction: column; gap: 8px; }
    .tfa-tip { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text-color); }
</style>

<script>
document.getElementById('btnShowDisable')?.addEventListener('click', function() {
    document.getElementById('disableFormWrap').style.display = 'block';
    this.style.display = 'none';
});

@if($twoFactorAuth && $twoFactorAuth->is_enabled)
function downloadBackupCodes() {
    const codes = [
        @foreach($backupCodes as $index => $code)
            '{{ $index + 1 }}. {{ $code }}',
        @endforeach
    ];

    const text = 'Al-Kutub Backup Codes\n' +
                 'Generated: ' + new Date().toLocaleString() + '\n' +
                 'User: {{ Auth::user()->email }}\n\n' +
                 codes.join('\n') + '\n\n' +
                 'Keep these codes in a secure location. Each code can only be used once.';

    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'alkutub-backup-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
@endif
</script>
@endsection
