// Courier Notifications System
(function () {
    'use strict';

    console.log('🚀 Courier Notifications System - Initializing...');

    const config = {
        refreshInterval: 10000, // 10 seconds
        apiEndpoints: {
            notifications: '/kurir/api/notifications/all',
            markAllRead: '/kurir/api/notifications/mark-all-read'
        }
    };

    let notificationState = {
        notifications: [],
        unreadCount: 0,
        isOpen: false
    };

    const elements = {
        notificationBtn: document.getElementById('courierNotificationBtn'),
        notifBadge: document.getElementById('courierNotifBadge'),
        notificationDropdown: document.getElementById('courierNotificationDropdown'),
        allNotificationsList: document.getElementById('courierAllNotificationsList')
    };

    /* ======================
     * INIT
     * ====================== */
    function init() {
        console.log('📱 Checking Courier DOM elements...');

        if (!elements.notificationBtn || !elements.notificationDropdown) {
            console.warn('⚠️ Courier notification elements not found. Logic skipped.');
            return;
        }

        console.log('✅ Courier DOM elements found');
        setupEventListeners();
        fetchNotifications();
        startAutoRefresh();
    }

    /* ======================
     * EVENT LISTENERS
     * ====================== */
    function setupEventListeners() {
        elements.notificationBtn.addEventListener('click', toggleNotificationDropdown);

        document.addEventListener('click', function (e) {
            if (
                !elements.notificationBtn.contains(e.target) &&
                !elements.notificationDropdown.contains(e.target)
            ) {
                closeNotificationDropdown();
            }
        });
    }

    function toggleNotificationDropdown(e) {
        e.stopPropagation();
        notificationState.isOpen ? closeNotificationDropdown() : openNotificationDropdown();
    }

    function openNotificationDropdown() {
        elements.notificationDropdown.classList.add('active');
        notificationState.isOpen = true;
        console.log('📬 Dropdown opened');
        markAllNotificationsAsRead();
    }

    function closeNotificationDropdown() {
        elements.notificationDropdown.classList.remove('active');
        notificationState.isOpen = false;
        console.log('📪 Dropdown closed');
    }

    /* ======================
     * FETCH NOTIFICATIONS
     * ====================== */
    async function fetchNotifications() {
        try {
            console.log('🔄 Fetching:', config.apiEndpoints.notifications);

            const response = await fetch(config.apiEndpoints.notifications, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // 🔒 Session expired
            if (response.status === 401) {
                console.warn('🔒 Unauthorized, redirecting to login');
                window.location.href = '/login';
                return;
            }

            if (!response.ok) {
                console.error('❌ API Error:', response.status);
                return;
            }

            const data = await response.json();

            if (!Array.isArray(data.notifications)) {
                console.warn('⚠️ Invalid response structure');
                return;
            }

            notificationState.notifications = data.notifications;
            notificationState.unreadCount = data.unread_count || 0;

            console.log('📊 Updated state:', {
                total: notificationState.notifications.length,
                unread: notificationState.unreadCount
            });

            updateUI();
        } catch (error) {
            console.error('❌ Fetch Error:', error);
        }
    }

    /* ======================
     * UI UPDATE
     * ====================== */
    function updateUI() {
        if (elements.notifBadge) {
            if (notificationState.unreadCount > 0) {
                const count =
                    notificationState.unreadCount > 99
                        ? '99+'
                        : notificationState.unreadCount;

                elements.notifBadge.textContent = count;
                elements.notifBadge.style.display = 'block';
            } else {
                elements.notifBadge.style.display = 'none';
            }
        }

        renderAllNotifications();
    }

    function renderAllNotifications() {
        if (!elements.allNotificationsList) return;

        if (notificationState.notifications.length === 0) {
            elements.allNotificationsList.innerHTML = `
                <div class="empty-notif" style="padding: 20px; text-align: center; color: #999;">
                    <i class="fas fa-bell" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Belum ada notifikasi baru</p>
                </div>
            `;
            return;
        }

        const html = notificationState.notifications.map(notif => {
            let icon = 'fas fa-box';

            switch (notif.subtype) {
                case 'new_assignment': icon = 'fas fa-truck-loading'; break;
                default: icon = 'fas fa-bell'; break;
            }

            return `
                <div class="notif-item ${!notif.is_read ? 'unread' : ''}"
                     onclick="window.location.href='${notif.url}'"
                     style="padding: 12px; border-bottom: 1px solid #f1f1f1; cursor: pointer; display: flex; align-items: flex-start; gap: 12px; ${!notif.is_read ? 'background-color: #f0f9ff;' : ''}">
                    <div class="notif-icon" style="background: #e3f2fd; color: #1976d2; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="${icon}"></i>
                    </div>
                    <div class="notif-body" style="flex: 1;">
                        <div class="notif-title" style="font-weight: 600; font-size: 14px; margin-bottom: 4px;">${escapeHtml(notif.title)}</div>
                        <div class="notif-desc" style="font-size: 13px; color: #666; margin-bottom: 6px; line-height: 1.4;">${escapeHtml(notif.description)}</div>
                        <div class="notif-time" style="font-size: 11px; color: #999;">
                            <i class="far fa-clock"></i> ${notif.time}
                        </div>
                    </div>
                    ${!notif.is_read ? '<div class="unread-dot" style="width: 8px; height: 8px; background: #1976d2; border-radius: 50%;"></div>' : ''}
                </div>
            `;
        }).join('');

        elements.allNotificationsList.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /* ======================
     * AUTO REFRESH
     * ====================== */
    function startAutoRefresh() {
        setInterval(fetchNotifications, config.refreshInterval);
    }

    /* ======================
     * MARK ALL READ
     * ====================== */
    async function markAllNotificationsAsRead() {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) return;

        const unread = notificationState.notifications.filter(n => !n.is_read);
        if (unread.length === 0) return;

        // Optimistic UI
        notificationState.notifications.forEach(n => n.is_read = true);
        notificationState.unreadCount = 0;
        updateUI();

        try {
            await fetch(config.apiEndpoints.markAllRead, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    notification_ids: unread.map(n => n.id)
                })
            });
        } catch (e) {
            console.error('❌ Mark read failed', e);
            fetchNotifications();
        }
    }

    /* ======================
     * VISIBILITY EVENTS
     * ====================== */
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) fetchNotifications();
    });

    window.addEventListener('focus', fetchNotifications);

    /* ======================
     * START
     * ====================== */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Debug helper
    window.CourierNotifications = {
        refresh: fetchNotifications,
        state: () => notificationState
    };

    console.log('✅ Courier Notifications System Loaded');
})();
