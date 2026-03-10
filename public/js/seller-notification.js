// Seller Notifications System - FIXED VERSION
(function() {
    'use strict';

    console.log('🚀 Seller Notifications System - Initializing...');

    const config = {
        refreshInterval: 10000,
        apiEndpoints: {
            notifications: '/seller/api/notifications/all'
        }
    };

    let notificationState = {
        notifications: [],
        unreadCount: 0,
        isOpen: false
    };

    const elements = {
        notificationBtn: document.getElementById('notificationBtn'),
        notifBadge: document.getElementById('notifBadge'),
        notificationDropdown: document.getElementById('notificationDropdown'),
        allNotificationsList: document.getElementById('allNotificationsList')
    };

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

    function setupEventListeners() {
        if (elements.notificationBtn) {
            elements.notificationBtn.addEventListener('click', toggleNotificationDropdown);
            console.log('✅ Click listener added to notification button');
        }
        
        document.addEventListener('click', function(e) {
            if (elements.notificationBtn && elements.notificationDropdown &&
                !elements.notificationBtn.contains(e.target) && 
                !elements.notificationDropdown.contains(e.target)) {
                closeNotificationDropdown();
            }
        });
    }

    function toggleNotificationDropdown(e) {
        e.stopPropagation();
        if (notificationState.isOpen) {
            closeNotificationDropdown();
        } else {
            openNotificationDropdown();
        }
    }

    function openNotificationDropdown() {
        if (elements.notificationDropdown) {
            elements.notificationDropdown.classList.add('active');
            notificationState.isOpen = true;
            console.log('📬 Dropdown opened');
            markAllNotificationsAsRead();
        }
    }

    function closeNotificationDropdown() {
        if (elements.notificationDropdown) {
            elements.notificationDropdown.classList.remove('active');
            notificationState.isOpen = false;
            console.log('📪 Dropdown closed');
        }
    }

    async function fetchNotifications() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('❌ CSRF token not found');
                return;
            }

            console.log('🔄 Fetching from:', config.apiEndpoints.notifications);

            const response = await fetch(config.apiEndpoints.notifications, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                console.error('❌ API Error:', response.status, response.statusText);
                return;
            }

            const data = await response.json();
            console.log('📦 API Response:', data);
            
            if (!data.notifications || !Array.isArray(data.notifications)) {
                console.warn('⚠️ Invalid data structure');
                return;
            }

            notificationState.notifications = data.notifications;
            notificationState.unreadCount = data.unread_count || 0;
            
            console.log('📊 State Updated:', {
                total: notificationState.notifications.length,
                unread: notificationState.unreadCount,
                breakdown: {
                    order: notificationState.notifications.filter(n => n.type === 'order').length,
                    chat: notificationState.notifications.filter(n => n.type === 'chat').length,
                    courier: notificationState.notifications.filter(n => n.type === 'courier').length
                }
            });
            
            updateUI();
            
        } catch (error) {
            console.error('❌ Fetch Error:', error);
        }
    }

    function updateUI() {
        console.log('🎨 Updating UI - Unread:', notificationState.unreadCount);
        
        if (elements.notifBadge) {
            if (notificationState.unreadCount > 0) {
                const displayCount = notificationState.unreadCount > 99 ? '99+' : notificationState.unreadCount;
                elements.notifBadge.textContent = displayCount;
                elements.notifBadge.style.display = 'block';
                console.log('✅ Badge shown:', displayCount);
            } else {
                elements.notifBadge.style.display = 'none';
                console.log('✅ Badge hidden');
            }
        }

        renderAllNotifications();
    }

    function renderAllNotifications() {
        if (!elements.allNotificationsList) {
            console.warn('⚠️ List element not found');
            return;
        }

        if (notificationState.notifications.length === 0) {
            elements.allNotificationsList.innerHTML = `
                <div class="empty-notif">
                    <i class="fas fa-bell"></i>
                    <p>Belum ada notifikasi baru</p>
                </div>
            `;
            console.log('📭 No notifications');
            return;
        }

        console.log('🖼️ Rendering', notificationState.notifications.length, 'notifications');

        const html = notificationState.notifications.map((notif, index) => {
            let iconClass = 'fas fa-clipboard-list';
            let iconType = 'order';
            
            if (notif.type === 'order') {
                iconClass = 'fas fa-clipboard-list';
                iconType = notif.subtype === 'cancelled' ? 'cancelled' : 'order';
            } else if (notif.type === 'chat') {
                iconClass = 'fas fa-comments';
                iconType = 'chat';
            } else if (notif.type === 'courier') {
                iconClass = 'fas fa-truck';
                iconType = 'courier';
                
                if (notif.subtype === 'rejected') {
                    iconClass = 'fas fa-times-circle';
                    iconType = 'courier-rejected';
                } else if (notif.subtype === 'accepted') {
                    iconClass = 'fas fa-check-circle';
                    iconType = 'courier-accepted';
                } else if (notif.subtype === 'delivered') {
                    iconClass = 'fas fa-box-open';
                    iconType = 'courier-delivered';
                } else if (notif.subtype === 'return_pickup') {
                    iconClass = 'fas fa-undo';
                    iconType = 'courier-return';
                } else if (notif.subtype === 'in_transit') {
                    iconClass = 'fas fa-shipping-fast';
                    iconType = 'courier-transit';
                }
            }
            
            console.log(`  [${index}] ${notif.type} (${notif.subtype || 'none'}) - ${notif.title} - Read: ${notif.is_read}`);
            
            return `
                <div class="notif-item ${!notif.is_read ? 'unread' : ''}" onclick="window.location.href='${notif.url}'">
                    <div class="notif-icon ${iconType}">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-title">${escapeHtml(notif.title)}</div>
                        <div class="notif-desc">${escapeHtml(notif.description)}</div>
                        <div class="notif-time"><i class="far fa-clock"></i> ${notif.time}</div>
                    </div>
                </div>
            `;
        }).join('');

        elements.allNotificationsList.innerHTML = html;
        console.log('✅ Notifications rendered');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function startAutoRefresh() {
        console.log('⏰ Auto-refresh: 10s interval');
        setInterval(fetchNotifications, config.refreshInterval);
    }

    async function markAllNotificationsAsRead() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) return;

            const unreadNotifications = notificationState.notifications.filter(n => !n.is_read);
            
            if (unreadNotifications.length === 0) {
                console.log('ℹ️ No unread notifications');
                return;
            }

            console.log('📝 Marking', unreadNotifications.length, 'as read');

            const previousState = JSON.parse(JSON.stringify(notificationState.notifications));
            notificationState.notifications.forEach(notif => {
                notif.is_read = true;
            });
            notificationState.unreadCount = 0;
            updateUI();

            const response = await fetch('/seller/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    notification_ids: unreadNotifications.map(n => n.id)
                })
            });

            if (response.ok) {
                const result = await response.json();
                console.log('✅ Marked as read:', result);
            } else {
                console.error('❌ Failed to mark as read');
                notificationState.notifications = previousState;
                fetchNotifications();
            }
        } catch (error) {
            console.error('❌ Mark read error:', error);
            fetchNotifications();
        }
    }

    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            console.log('👁️ Tab visible, refreshing...');
            fetchNotifications();
        }
    });

    window.addEventListener('focus', function() {
        console.log('🔍 Window focused, refreshing...');
        fetchNotifications();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.SellerNotifications = {
        refresh: fetchNotifications,
        open: openNotificationDropdown,
        close: closeNotificationDropdown,
        getState: () => notificationState,
        forceUpdate: () => {
            console.log('🔄 Force updating...');
            fetchNotifications();
        }
    };

    console.log('✅ Seller Notifications System Loaded');

})();