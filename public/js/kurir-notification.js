/**
 * Courier Order Badge Management
 * Shows count of PENDING tasks on the Orders navigation icon
 */

(function () {
    'use strict';

    const CourierOrderBadge = {
        // Configuration
        config: {
            pollInterval: 10000, // 10 seconds
            orderNavSelector: '.mobile-bottom-nav a[href*="orders"]', // Matches courier nav structure
            badgeSelector: '.order-badge',
            apiUrl: '/kurir/api/orders/pending-count'
        },

        // Initialize
        init() {
            // console.log('✅ Courier Order Badge: Initializing...');

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.start();
                });
            } else {
                this.start();
            }
        },

        start() {
            // console.log('🚀 Courier Order Badge: Starting...');
            this.loadPendingCount();
            this.startPolling();
            this.setupEventListeners();
        },

        // Load pending count from server
        loadPendingCount() {
            $.ajax({
                url: this.config.apiUrl,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: (response) => {
                    if (response.success && typeof response.count !== 'undefined') {
                        this.updateBadge(response.count);
                    }
                },
                error: (xhr) => {
                    console.error('❌ Courier Badge Error', xhr.status);
                }
            });
        },

        // Update badge display
        updateBadge(count) {
            const orderNavItem = $(this.config.orderNavSelector);
            let badge = orderNavItem.find(this.config.badgeSelector);

            if (count > 0) {
                const displayCount = count > 99 ? '99+' : count;

                if (badge.length > 0) {
                    badge.text(displayCount).show();
                } else {
                    // Start styling matches Seller badge (adjust class/style as needed)
                    // Ensure navbot.blade.php has the badge span or we prepend/append it here?
                    // Implementation plan said we'd add HTML to navbot, but let's be robust.
                    if (badge.length === 0) {
                         // Fallback if not physically present in HTML
                         orderNavItem.find('i').after(`<span class="badge order-badge" style="
                            position: absolute;
                            top: 5px;
                            right: 25px;
                            background: #ef4444;
                            color: white;
                            font-size: 10px;
                            padding: 2px 6px;
                            border-radius: 10px;
                            min-width: 18px;
                         ">${displayCount}</span>`);
                    }
                }
            } else {
                if (badge.length > 0) {
                    badge.hide();
                }
            }
        },

        // Start polling
        startPolling() {
            setInterval(() => {
                this.loadPendingCount();
            }, this.config.pollInterval);
        },

        // Setup event listeners
        setupEventListeners() {
            // Refresh on visibility change
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.loadPendingCount();
                }
            });
        }
    };

    // Initialize
    CourierOrderBadge.init();

    // Expose Global
    window.CourierOrderBadge = CourierOrderBadge;

})();
