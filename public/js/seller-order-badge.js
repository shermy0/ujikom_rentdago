/**
 * Seller Order Badge Management
 * Shows count of CONFIRMED orders on the Orders navigation icon
 * Badge persists until order status changes from 'confirmed' to 'ongoing'
 */

(function () {
    'use strict';

    const OrderBadge = {
        // Configuration
        config: {
            pollInterval: 10000, // ✅ 10 seconds for faster updates
            orderNavSelector: '.seller-bottom-nav a[href*="orders"]',
            badgeSelector: '.order-badge',
            apiUrl: '/seller/api/orders/unread-count',
            markReadUrl: '/seller/api/orders/mark-read',
            markSingleReadUrl: '/seller/api/orders/{id}/mark-read'
        },

        // Initialize
        init() {
            console.log('✅ Order Badge: Initializing...');

            // Wait for DOM to be fully ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.start();
                });
            } else {
                this.start();
            }
        },

        start() {
            console.log('🚀 Order Badge: Starting...');
            this.loadUnreadCount();
            this.startPolling();
            this.setupEventListeners();
        },

        // Load unread order count from server
        loadUnreadCount() {
            $.ajax({
                url: this.config.apiUrl,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: (response) => {
                    console.log('✅ Order Badge: API Response', response);
                    if (response.success && typeof response.count !== 'undefined') {
                        this.updateBadge(response.count);
                    } else {
                        console.warn('⚠️ Order Badge: Invalid response format', response);
                    }
                },
                error: (xhr) => {
                    console.error('❌ Order Badge: Error loading count', {
                        status: xhr.status,
                        response: xhr.responseText
                    });
                }
            });
        },

        // Update badge display
        updateBadge(count) {
            const orderNavItem = $(this.config.orderNavSelector);
            let badge = orderNavItem.find(this.config.badgeSelector);

            console.log('🎯 Order Badge: Updating badge with count:', count);
            console.log('📍 Order Nav Item found:', orderNavItem.length);
            console.log('🏷️ Badge found:', badge.length);

            if (count > 0) {
                const displayCount = count > 99 ? '99+' : count;

                if (badge.length > 0) {
                    // Badge exists, update it
                    badge.text(displayCount).show();
                    console.log('✅ Order Badge: Badge updated to', displayCount);
                } else {
                    // Badge doesn't exist, create it
                    orderNavItem.append(`<span class="badge order-badge">${displayCount}</span>`);
                    console.log('✅ Order Badge: Badge created with count', displayCount);
                }
            } else {
                // No unread orders, hide badge
                if (badge.length > 0) {
                    badge.hide();
                    console.log('✅ Order Badge: Badge hidden (count = 0)');
                }
            }
        },

        // Start polling for updates
        startPolling() {
            setInterval(() => {
                console.log('🔄 Order Badge: Auto-refresh triggered');
                this.loadUnreadCount();
            }, this.config.pollInterval);
            console.log('⏰ Order Badge: Polling started (every 10s)');
        },

        // Setup event listeners
        setupEventListeners() {
            const self = this;

            // ✅ REMOVED mark-as-read when clicking Orders nav
            // Badge now persists until order status changes from 'confirmed' to 'ongoing'
            // No need to mark as read anymore

            // Listen for custom events from other scripts
            $(document).on('orderStatusChanged', () => {
                console.log('📢 Order Badge: Order status changed event');
                this.loadUnreadCount();
            });

            $(document).on('newOrderReceived', () => {
                console.log('📢 Order Badge: New order received event');
                this.loadUnreadCount();
            });

            $(document).on('orderPaid', () => {
                console.log('📢 Order Badge: Order paid event');
                this.loadUnreadCount();
            });

            // Listen for page visibility changes
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    console.log('👁️ Order Badge: Page visible, refreshing count');
                    this.loadUnreadCount();
                }
            });

            // Listen for window focus
            window.addEventListener('focus', () => {
                console.log('🔍 Order Badge: Window focused, refreshing count');
                this.loadUnreadCount();
            });
        },

        // Mark orders as read when user visits orders page
        markOrdersAsRead() {
            console.log('📝 Order Badge: Marking all orders as read');

            $.ajax({
                url: this.config.markReadUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: (response) => {
                    console.log('✅ Order Badge: Marked as read', response);
                    this.updateBadge(0);
                },
                error: (xhr) => {
                    console.error('❌ Order Badge: Error marking as read', xhr);
                }
            });
        },

        // Mark single order as read (call this when viewing order detail)
        markSingleOrderAsRead(orderId) {
            const url = this.config.markSingleReadUrl.replace('{id}', orderId);

            console.log('📝 Order Badge: Marking order', orderId, 'as read');

            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: (response) => {
                    console.log('✅ Order Badge: Order marked as read', response);
                    this.loadUnreadCount(); // Refresh count
                },
                error: (xhr) => {
                    console.error('❌ Order Badge: Error marking order as read', xhr);
                }
            });
        }
    };

    // Initialize
    OrderBadge.init();

    // Expose to window for manual refresh if needed
    window.SellerOrderBadge = OrderBadge;

    console.log('✅ Seller Order Badge System Loaded');

})();