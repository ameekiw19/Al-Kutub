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

    <!-- 2FA Setup Card -->
    <div class="acc-section-card">
        <h3 class="acc-section-title"><i class="fas fa-shield-alt"></i> Setup Two-Factor Authentication</h3>

        @if (session('error'))
            <div class="tfa-alert danger"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="tfa-alert success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <div class="tfa-alert info" style="margin-bottom: 20px;">
            <i class="fas fa-info-circle"></i>
            <strong>Two-factor authentication</strong> menambah keamanan ekstra ke akun Anda. Setelah memasukkan password, Anda perlu memasukkan kode dari aplikasi authenticator.
        </div>

        <form action="{{ route('2fa.enable') }}" method="POST">
            @csrf

            <!-- Step 1: QR Code -->
            <div class="tfa-step">
                <h4 class="tfa-step-title"><i class="fas fa-qrcode"></i> Langkah 1: Scan QR Code</h4>
                <p style="font-size:13px; color:var(--light-text);">Buka aplikasi authenticator (Google Authenticator, Authy, dll) dan scan QR code ini:</p>

                <div style="text-align:center; margin: 16px 0;">
                    <div class="tfa-qr-wrap">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" alt="QR Code">
                    </div>
                </div>

                <div class="tfa-manual-entry">
                    <small style="color:var(--light-text);"><strong>Tidak bisa scan?</strong> Masukkan kode ini secara manual:</small>
                    <div class="tfa-secret-display">
                        <code>{{ $secretKey }}</code>
                        <button type="button" class="tfa-copy-btn" onclick="copyToClipboard('{{ $secretKey }}')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Verification Code -->
            <div class="tfa-step">
                <h4 class="tfa-step-title"><i class="fas fa-key"></i> Langkah 2: Masukkan Kode Verifikasi</h4>
                <p style="font-size:13px; color:var(--light-text);">Masukkan 6-digit kode dari aplikasi authenticator Anda:</p>

                <div class="acc-form-group">
                    <div class="acc-input-wrap">
                        <i class="fas fa-shield-alt"></i>
                        <input type="text" id="code" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code" style="text-align:center; letter-spacing:4px; font-weight:700; font-size:18px;">
                    </div>
                    <small style="color:var(--light-text); font-size:11px;">Masukkan 6-digit kode dari aplikasi authenticator</small>
                </div>
            </div>

            <!-- Step 3: Backup Codes -->
            <div class="tfa-step" style="border-bottom:none;">
                <h4 class="tfa-step-title"><i class="fas fa-save"></i> Langkah 3: Simpan Backup Codes</h4>
                <div class="tfa-alert warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Penting:</strong> Simpan backup codes ini di tempat aman. Gunakan jika kehilangan akses ke authenticator.
                </div>

                <div class="tfa-backup-grid">
                    @foreach($backupCodes as $index => $code)
                        <div class="tfa-backup-item">
                            <span class="tfa-backup-num">{{ $index + 1 }}</span>
                            <span class="tfa-backup-code">{{ $code }}</span>
                        </div>
                    @endforeach
                </div>

                <label class="tfa-checkbox">
                    <input type="checkbox" id="backup_saved" required>
                    <span>Saya telah menyimpan backup codes di tempat yang aman</span>
                </label>
            </div>

            <button type="submit" class="acc-save-btn">
                <i class="fas fa-shield-alt"></i> Aktifkan Two-Factor Authentication
            </button>
        </form>
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

    .tfa-qr-wrap { display: inline-block; padding: 14px; background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid var(--border-color); }
    .tfa-qr-wrap img { border-radius: 8px; }

    .tfa-manual-entry { background: var(--background-color); padding: 12px; border-radius: 12px; margin-top: 12px; }
    .tfa-secret-display { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
    .tfa-secret-display code { flex: 1; background: var(--card-bg); padding: 8px 12px; border-radius: 10px; font-family: monospace; font-size: 13px; border: 1px solid var(--border-color); word-break: break-all; }
    .tfa-copy-btn { background: var(--primary-color); color: white; border: none; padding: 8px 12px; border-radius: 10px; cursor: pointer; transition: 0.2s; }
    .tfa-copy-btn:hover { transform: translateY(-1px); }

    .tfa-backup-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; margin: 12px 0; }
    .tfa-backup-item { display: flex; align-items: center; gap: 10px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px 12px; transition: 0.2s; }
    .tfa-backup-item:hover { border-color: var(--primary-color); }
    .tfa-backup-num { width: 24px; height: 24px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
    .tfa-backup-code { font-family: monospace; font-weight: 600; font-size: 13px; color: var(--text-color); }

    .tfa-checkbox { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: var(--light-text); margin-top: 12px; cursor: pointer; }
    .tfa-checkbox input { accent-color: var(--primary-color); width: 16px; height: 16px; margin-top: 2px; }
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        Swal.fire({ icon: 'success', title: 'Disalin!', text: 'Secret key disalin ke clipboard', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
    });
}
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
});
</script>
@endsection
