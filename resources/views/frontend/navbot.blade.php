<nav class="mobile-bottom-nav">
    @auth
        @if(auth()->user()->role === 'courier')
            {{-- Courier Bottom Nav (Green Theme) --}}
            <a href="{{ route('home') }}" class="nav-item {{ Request::routeIs('home') ? 'active' : '' }}">
                <i class="fa fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('kurir.orders') }}" class="nav-item {{ Request::routeIs('kurir.orders') ? 'active' : '' }}">
                <i class="fa fa-box"></i>
                <span>Pesanan</span>
            </a>
                       <a href="{{ route('kurir.history') }}" class="nav-item {{ Request::routeIs('kurir.history') ? 'active' : '' }}">
                <i class="fa fa-history"></i>
                <span>Riwayat</span>
            </a>
            <a href="{{ route('kurir.profile') }}" class="nav-item {{ Request::routeIs('kurir.profile') ? 'active' : '' }}">
                <i class="fa fa-user"></i>
                <span>Saya</span>
            </a>
        @else
            {{-- Customer Bottom Nav (Orange Theme) --}}
            <a href="{{ route('home') }}" class="nav-item {{ Request::routeIs('home') ? 'active' : '' }}">
                <i class="fa fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('customer.order.index') }}" class="nav-item {{ Request::routeIs('customer.order.index') ? 'active' : '' }}">
                <i class="fa fa-shopping-bag"></i>
                <span>Pesanan</span>
            </a>
            <a href="{{ route('customer.chat.index') }}" class="nav-item {{ Request::routeIs('customer.chat.*') ? 'active' : '' }}">
                <i class="fa fa-comments"></i>
                <span>Chat</span>
            </a>
            <a href="{{ route('profile.index') }}" class="nav-item {{ Request::routeIs('profile.*') ? 'active' : '' }}">
                <i class="fa fa-user"></i>
                <span>Saya</span>
            </a>
        @endif
    @endauth
    @guest
        <a href="{{ route('home') }}" class="nav-item {{ Request::routeIs('home') ? 'active' : '' }}">
            <i class="fa fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('seller-request.create') }}" class="nav-item {{ Request::routeIs('seller-request.create') ? 'active' : '' }}">
            <i class="fas fa-store"></i>
            <span>Pengajuan</span>
        </a>
        <a href="{{ route('customer.chat.index') }}" class="nav-item {{ Request::routeIs('customer.chat.*') ? 'active' : '' }}">
            <i class="fa fa-comments"></i>
            <span>Chat</span>
        </a>
        <a href="{{ route('customer.order.index') }}" class="nav-item {{ Request::routeIs('customer.order.index') ? 'active' : '' }}">
            <i class="fa fa-shopping-bag"></i>
            <span>Pesanan</span>
        </a>
        <a href="{{ route('login') }}" class="nav-item {{ Request::routeIs('login') ? 'active' : '' }}">
            <i class="fa fa-user"></i>
            <span>Saya</span>
        </a>
    @endguest
</nav>

<script>
    /**
 * FIX NAVBAR BOTTOM - ENSURE VISIBILITY
 * Force navbar to appear and stay visible
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Function to fix navbar visibility
    function fixNavbarVisibility() {
        const navbar = document.querySelector('.mobile-bottom-nav');
        
        if (!navbar) {
            console.error('❌ Navbar not found!');
            return;
        }
        
        console.log('✅ Navbar found, applying fixes...');
        
        // Force styles
        navbar.style.cssText = `
            position: fixed !important;
            bottom: 0 !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 100% !important;
            max-width: 470px !important;
            background: #ffffff !important;
            display: flex !important;
            justify-content: space-around !important;
            align-items: center !important;
            padding: 8px 0 !important;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.08) !important;
            z-index: 9999 !important;
            border-top: 1px solid #f0f0f0 !important;
            
            visibility: visible !important;
            opacity: 1 !important;
        `;
        
        // Check if mobile view exists
        const mobileView = document.querySelector('.mobile-view');
        if (mobileView) {
            mobileView.style.overflow = 'visible';
            console.log('✅ Mobile view overflow set to visible');
        }
        
        // Add content padding
        const mobileContent = document.querySelector('.mobile-content');
        if (mobileContent) {
            mobileContent.style.paddingBottom = '90px';
        }
        
        // Responsive for mobile devices
        if (window.innerWidth <= 480) {
            navbar.style.maxWidth = '100%';
            navbar.style.left = '0';
            navbar.style.transform = 'none';
            navbar.style.borderRadius = '0';
        }
        
        console.log('✅ Navbar visibility fixed!');
    }
    
    // Apply fixes immediately
    fixNavbarVisibility();
    
    // Apply fixes again after a short delay (in case of dynamic content)
    setTimeout(fixNavbarVisibility, 100);
    setTimeout(fixNavbarVisibility, 500);
    
    // Re-apply on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(fixNavbarVisibility, 250);
    });
    
    // Initialize smooth interactions
    initNavbarInteractions();
    
    console.log('🧭 Bottom Navigation Initialized');
});

/**
 * Initialize navbar interactions
 */
function initNavbarInteractions() {
    const navbar = document.querySelector('.mobile-bottom-nav');
    if (!navbar) return;
    
    const navItems = navbar.querySelectorAll('.nav-item');
    
    // Add ripple effect
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            createRipple(e, this);
            
            // Haptic feedback
            if ('vibrate' in navigator) {
                navigator.vibrate(10);
            }
            
            // Scroll to top if clicking active item
            if (this.classList.contains('active') && window.pageYOffset > 0) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                
                // Bounce animation
                this.style.animation = 'bounce 0.5s ease';
                setTimeout(() => {
                    this.style.animation = '';
                }, 500);
            }
        });
    });
    
    // Add bounce animation keyframes
    if (!document.getElementById('navbar-animations')) {
        const style = document.createElement('style');
        style.id = 'navbar-animations';
        style.textContent = `
            @keyframes ripple {
                to {
                    width: 100px;
                    height: 100px;
                    opacity: 0;
                }
            }
            
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-8px); }
            }
            
            @keyframes slideUpNav {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Create ripple effect
 */
function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    
    ripple.style.cssText = `
        position: absolute;
        left: ${x}px;
        top: ${y}px;
        width: 0;
        height: 0;
       
        background: rgba(238, 77, 45, 0.3);
        transform: translate(-50%, -50%);
        animation: ripple 0.6s ease-out;
        pointer-events: none;
    `;
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
}

/**
 * Debug: Log navbar state
 */
function debugNavbar() {
    const navbar = document.querySelector('.mobile-bottom-nav');
    if (navbar) {
        console.log('Navbar Debug:', {
            display: window.getComputedStyle(navbar).display,
            position: window.getComputedStyle(navbar).position,
            bottom: window.getComputedStyle(navbar).bottom,
            zIndex: window.getComputedStyle(navbar).zIndex,
            visibility: window.getComputedStyle(navbar).visibility,
            opacity: window.getComputedStyle(navbar).opacity,
            width: navbar.offsetWidth,
            height: navbar.offsetHeight
        });
    } else {
        console.error('Navbar element not found!');
    }
}

// Call debug on load
window.addEventListener('load', () => {
    setTimeout(debugNavbar, 1000);
});
</script>