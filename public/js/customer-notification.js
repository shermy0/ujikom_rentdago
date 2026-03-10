// Customer Notifications System
(function () {
    'use strict';

    console.log('🚀 Customer Notifications System - Initializing...');

    const config = {
        refreshInterval: 10000, // 10 detik
        apiEndpoints: {
            notifications: '/api/notifications/all',
            markAllRead: '/api/notifications/mark-all-read'
        }
    };

    let notificationState = {
        notifications: [],
        unreadCount: 0,
        isOpen: false
    };

    const elements = {
        notificationBtn: document.getElementById('customerNotificationBtn'),
        notifBadge: document.getElementById('customerNotifBadge'),
        notificationDropdown: document.getElementById('customerNotificationDropdown'),
        allNotificationsList: document.getElementById('customerAllNotificationsList')
    };

    /* ======================
     * INIT
     * ====================== */
    function init() {
        console.log('📱 Checking DOM elements...');

        if (!elements.notificationBtn || !elements.notificationDropdown) {
            console.error('❌ Required notification elements not found');
            return;
        }

        console.log('✅ DOM elements found');
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

            const text = await response.text();

            if (!text) {
                console.warn('⚠️ Empty response body');
                return;
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('❌ Invalid JSON:', text);
                return;
            }

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
                <div class="empty-notif">
                    <i class="fas fa-bell"></i>
                    <p>Belum ada notifikasi</p>
                </div>
            `;
            return;
        }

        const html = notificationState.notifications.map(notif => {
            let icon = 'fas fa-shopping-bag';

            switch (notif.subtype) {
                case 'pending_payment': icon = 'fas fa-clock'; break;
                case 'payment_success': icon = 'fas fa-check-circle'; break;
                case 'in_transit': icon = 'fas fa-truck'; break;
                case 'arrived': icon = 'fas fa-location-dot'; break;
                case 'completed': icon = 'fas fa-check-double'; break;
                case 'cancelled': icon = 'fas fa-times-circle'; break;
                case 'penalty': icon = 'fas fa-exclamation-triangle'; break;
                case 'penalty_paid': icon = 'fas fa-receipt'; break;
            }

            return `
                <div class="notif-item ${!notif.is_read ? 'unread' : ''}"
                     onclick="window.location.href='${notif.url}'">
                    <div class="notif-icon">
                        <i class="${icon}"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-title">${escapeHtml(notif.title)}</div>
                        <div class="notif-desc">${escapeHtml(notif.description)}</div>
                        <div class="notif-time">
                            <i class="far fa-clock"></i> ${notif.time}
                        </div>
                    </div>
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
    window.CustomerNotifications = {
        refresh: fetchNotifications,
        state: () => notificationState
    };

    console.log('✅ Customer Notifications System Loaded');
})();
