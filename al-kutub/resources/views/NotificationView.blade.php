@extends('TemplateUser')

@section('konten')
<div class="notif-page">
    <div class="page-header-bar">
        <div class="page-header-left">
            <i class="fas fa-bell page-header-icon"></i>
            <h2 class="page-header-title">Notifikasi</h2>
        </div>
        <div class="notif-header-actions">
            <span class="notif-unread-badge" id="page-unread-count">{{ $unreadCount ?? 0 }} Belum Dibaca</span>
            <button
                type="button"
                id="btn-mark-all-read"
                class="notif-mark-all-btn"
                onclick="markAllNotificationsAsRead()"
                {{ (($unreadCount ?? 0) > 0) ? '' : 'disabled' }}
            >
                <i class="fas fa-check-double"></i> Tandai Dibaca
            </button>
        </div>
    </div>

    <div class="notif-list">
        @forelse($notifications as $notif)
            <div class="notif-card {{ $notif->read_at ? 'is-read' : 'is-unread' }}"
                 data-notification-id="{{ $notif->id }}"
                 data-read="{{ $notif->read_at ? '1' : '0' }}">
                <div class="notif-icon-circle {{ $notif->type ?? 'info' }}">
                    @if(isset($notif->type) && $notif->type == 'new_kitab')
                        <i class="fas fa-book"></i>
                    @elseif(isset($notif->type) && $notif->type == 'promo')
                        <i class="fas fa-percent"></i>
                    @elseif(isset($notif->type) && $notif->type == 'system')
                        <i class="fas fa-cog"></i>
                    @else
                        <i class="fas fa-info-circle"></i>
                    @endif
                </div>
                
                <div class="notif-body">
                    <div class="notif-top-row">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <h3 class="notif-card-title">{{ $notif->title }}</h3>
                            @if(!$notif->read_at)
                                <span class="notif-new-badge">Baru</span>
                            @endif
                        </div>
                        <span class="notif-time">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="notif-msg">{{ $notif->message }}</p>

                    <div class="notif-actions">
                        @if(!$notif->read_at)
                            <button type="button" class="btn-mark-read" onclick="markNotificationAsRead({{ $notif->id }}, this.closest('.notif-card'))">
                                Tandai dibaca
                            </button>
                        @endif

                        @if(isset($notif->action_url) && $notif->action_url)
                            <a href="{{ $notif->action_url }}" class="notif-detail-link" onclick="markNotificationAsRead({{ $notif->id }}, this.closest('.notif-card'), true)">
                                Lihat Detail <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state-box">
                <div style="font-size: 3rem; margin-bottom: 12px;">🔔</div>
                <h3>Tidak ada notifikasi</h3>
                <p>Belum ada informasi terbaru untuk Anda</p>
                <a href="{{ route('home') }}" class="btn-action-primary" style="margin-top: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-sync-alt"></i> Refresh
                </a>
            </div>
        @endforelse
    </div>
</div>

<style>
    .notif-page { max-width: 800px; margin: 0 auto; }
    .page-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .page-header-left { display: flex; align-items: center; gap: 10px; }
    .page-header-icon { color: var(--accent-color); font-size: 22px; }
    .page-header-title { font-size: 20px; font-weight: 800; color: var(--text-color); margin: 0; }

    .notif-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .notif-unread-badge {
        background: #EF4444; color: white; padding: 4px 12px;
        border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .notif-mark-all-btn {
        border: 1.5px solid var(--primary-color); color: var(--primary-color);
        background: transparent; border-radius: 12px; padding: 6px 14px;
        font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;
        font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px;
    }
    .notif-mark-all-btn:hover:not(:disabled) { background: rgba(27,94,59,0.08); }
    .notif-mark-all-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    .notif-list { display: flex; flex-direction: column; gap: 12px; }

    .notif-card {
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 16px; padding: 16px; display: flex; gap: 14px;
        transition: 0.2s; position: relative;
    }
    .notif-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    .notif-card.is-unread { border-left: 4px solid var(--primary-color); background: #F0F7F3; }
    body.dark-mode .notif-card.is-unread { background: rgba(27,94,59,0.1); }
    .notif-card.is-read { opacity: 0.8; }

    .notif-icon-circle {
        width: 44px; height: 44px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .notif-icon-circle.new_kitab { background: #F0F7F3; color: var(--primary-color); }
    .notif-icon-circle.promo { background: #FFF8E1; color: var(--accent-color); }
    .notif-icon-circle.system { background: #F3F4F6; color: #6B7280; }
    .notif-icon-circle.info { background: #F0F7F3; color: var(--primary-color); }

    .notif-body { flex: 1; }
    .notif-top-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
    .notif-card-title { font-size: 14px; font-weight: 700; color: var(--text-color); margin: 0; }
    .notif-new-badge {
        background: var(--primary-color); color: white; font-size: 10px;
        font-weight: 700; border-radius: 8px; padding: 2px 8px;
    }
    .notif-time { font-size: 11px; color: var(--light-text); white-space: nowrap; margin-left: 10px; }
    .notif-msg { font-size: 13px; color: var(--light-text); line-height: 1.5; margin-bottom: 8px; }

    .notif-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .btn-mark-read {
        border: 1px solid var(--primary-color); color: var(--primary-color);
        background: transparent; border-radius: 10px; padding: 5px 12px;
        font-size: 11px; font-weight: 600; cursor: pointer; transition: 0.2s;
        font-family: 'Poppins', sans-serif;
    }
    .btn-mark-read:hover { background: rgba(27,94,59,0.08); }
    .notif-detail-link {
        color: var(--accent-color); font-weight: 600; font-size: 12px;
        text-decoration: none; display: flex; align-items: center; gap: 4px;
    }
    .notif-detail-link:hover { text-decoration: underline; }

    .empty-state-box {
        text-align: center; padding: 50px 20px; background: var(--card-bg);
        border-radius: 16px; border: 1px dashed var(--border-color); color: var(--light-text);
    }
    .empty-state-box h3 { color: var(--text-color); margin-bottom: 4px; }

    .btn-action-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white; border: none; border-radius: 14px; padding: 10px 20px;
        font-weight: 600; font-size: 14px; cursor: pointer; transition: 0.3s;
        font-family: 'Poppins', sans-serif;
    }
    .btn-action-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(27,94,59,0.2); color: white; }

    @media (max-width: 576px) {
        .notif-card { flex-direction: column; gap: 10px; }
        .notif-icon-circle { width: 36px; height: 36px; font-size: 14px; border-radius: 12px; }
        .notif-top-row { flex-direction: column; gap: 4px; }
        .notif-time { margin-left: 0; }
    }
</style>

<script>
    async function markNotificationAsRead(notificationId, cardElement, fireAndForget = false) {
        if (!cardElement || cardElement.dataset.read === '1') return true;

        const endpoint = "{{ url('/notifications') }}/" + notificationId + "/read";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const options = {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            keepalive: fireAndForget
        };

        try {
            const res = await fetch(endpoint, options);
            if (!res.ok) return true;
            const payload = await res.json();
            if (!payload.success) return true;

            applyReadStateToCard(cardElement);
            updateUnreadUi(payload.data?.unread_count ?? null);

            window.dispatchEvent(new CustomEvent('notifications:updated', {
                detail: { unreadCount: payload.data?.unread_count ?? null }
            }));
        } catch (e) {}

        return true;
    }

    function applyReadStateToCard(cardElement) {
        if (!cardElement) return;
        cardElement.dataset.read = '1';
        cardElement.classList.remove('is-unread');
        cardElement.classList.add('is-read');
        const markBtn = cardElement.querySelector('.btn-mark-read');
        if (markBtn) markBtn.remove();
        const badge = cardElement.querySelector('.notif-new-badge');
        if (badge) badge.remove();
    }

    function updateUnreadUi(unreadCount) {
        const pageUnread = document.getElementById('page-unread-count');
        const markAllBtn = document.getElementById('btn-mark-all-read');
        if (typeof unreadCount !== 'undefined' && unreadCount !== null) {
            const unreadInt = Number(unreadCount) || 0;
            if (pageUnread) pageUnread.textContent = unreadInt + " Belum Dibaca";
            if (markAllBtn) markAllBtn.disabled = unreadInt <= 0;
            return unreadInt;
        }
        const unreadCards = document.querySelectorAll('.notif-card[data-read="0"]').length;
        if (pageUnread) pageUnread.textContent = unreadCards + " Belum Dibaca";
        if (markAllBtn) markAllBtn.disabled = unreadCards <= 0;
        return unreadCards;
    }

    async function markAllNotificationsAsRead() {
        const button = document.getElementById('btn-mark-all-read');
        if (!button || button.disabled) return;

        const endpoint = "{{ route('notifications.read-all') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const originalText = button.innerHTML;

        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('Failed request');
            const payload = await res.json();
            if (!payload.success) throw new Error(payload.message || 'Failed');

            const unreadCards = document.querySelectorAll('.notif-card[data-read="0"]');
            unreadCards.forEach((card) => applyReadStateToCard(card));

            const unreadCount = updateUnreadUi(payload.data?.unread_count ?? 0);
            window.dispatchEvent(new CustomEvent('notifications:updated', {
                detail: { unreadCount: unreadCount }
            }));
        } catch (e) {
            button.disabled = false;
        } finally {
            button.innerHTML = originalText;
            updateUnreadUi();
        }
    }
</script>
@endsection
