/**
 * SHOPEE HOME - SMOOTH INTERACTIONS
 * Smooth animations, banner slider, scroll effects
 */

// ==========================================
// BANNER AUTO SLIDER
// ==========================================
class BannerSlider {
    constructor() {
        this.slider = document.getElementById('bannerSlider');
        this.dotsContainer = document.getElementById('bannerDots');
        
        if (!this.slider) return;
        
        this.slides = this.slider.querySelectorAll('.banner-slide');
        this.currentSlide = 0;
        this.autoPlayInterval = null;
        
        this.init();
    }
    
    init() {
        this.createDots();
        this.startAutoPlay();
        this.addTouchSupport();
    }
    
    createDots() {
        this.slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('banner-dot');
            if (index === 0) dot.classList.add('active');
            
            dot.addEventListener('click', () => this.goToSlide(index));
            this.dotsContainer.appendChild(dot);
        });
        
        this.dots = this.dotsContainer.querySelectorAll('.banner-dot');
    }
    
    goToSlide(index) {
        // Remove active class from current slide and dot
        this.slides[this.currentSlide].classList.remove('active');
        this.dots[this.currentSlide].classList.remove('active');
        
        // Add active class to new slide and dot
        this.currentSlide = index;
        this.slides[this.currentSlide].classList.add('active');
        this.dots[this.currentSlide].classList.add('active');
        
        // Reset auto play
        this.resetAutoPlay();
    }
    
    nextSlide() {
        const next = (this.currentSlide + 1) % this.slides.length;
        this.goToSlide(next);
    }
    
    prevSlide() {
        const prev = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.goToSlide(prev);
    }
    
    startAutoPlay() {
        this.autoPlayInterval = setInterval(() => {
            this.nextSlide();
        }, 4000); // Change slide every 4 seconds
    }
    
    resetAutoPlay() {
        clearInterval(this.autoPlayInterval);
        this.startAutoPlay();
    }
    
    addTouchSupport() {
        let startX = 0;
        let endX = 0;
        
        this.slider.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });
        
        this.slider.addEventListener('touchmove', (e) => {
            endX = e.touches[0].clientX;
        });
        
        this.slider.addEventListener('touchend', () => {
            const diff = startX - endX;
            
            if (Math.abs(diff) > 50) { // Minimum swipe distance
                if (diff > 0) {
                    this.nextSlide(); // Swipe left
                } else {
                    this.prevSlide(); // Swipe right
                }
            }
        });
    }
}

// ==========================================
// BACK TO TOP BUTTON
// ==========================================
class BackToTop {
    constructor() {
        this.button = document.getElementById('backToTop');
        if (!this.button) return;
        
        this.init();
    }
    
    init() {
        // Show/hide button on scroll
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                this.button.classList.add('show');
            } else {
                this.button.classList.remove('show');
            }
        });
        
        // Smooth scroll to top
        this.button.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ==========================================
// PRODUCT CARD ANIMATIONS
// ==========================================
class ProductAnimations {
    constructor() {
        this.observeProducts();
    }
    
    observeProducts() {
        const options = {
            root: null,
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 50); // Stagger animation
                    
                    observer.unobserve(entry.target);
                }
            });
        }, options);
        
        // Observe all product cards
        const productCards = document.querySelectorAll('.product-card-shopee');
        productCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.4s ease';
            observer.observe(card);
        });
    }
}

// ==========================================
// SEARCH BAR ENHANCEMENTS
// ==========================================
class SearchBar {
    constructor() {
        this.searchBar = document.querySelector('.shopee-search-bar');
        this.searchInput = this.searchBar?.querySelector('.search-input');
        
        if (!this.searchBar) return;
        
        this.init();
    }
    
    init() {
        // Add focus animation
        this.searchInput.addEventListener('focus', () => {
            this.searchBar.style.transform = 'scale(1.02)';
        });
        
        this.searchInput.addEventListener('blur', () => {
            this.searchBar.style.transform = 'scale(1)';
        });
        
        // Clear button
        if (this.searchInput.value) {
            this.addClearButton();
        }
        
        this.searchInput.addEventListener('input', () => {
            if (this.searchInput.value) {
                this.addClearButton();
            } else {
                this.removeClearButton();
            }
        });
    }
    
    addClearButton() {
        if (this.searchBar.querySelector('.clear-btn')) return;
        
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'clear-btn';
        clearBtn.innerHTML = '<i class="bi bi-x-circle-fill"></i>';
        clearBtn.style.cssText = `
            background: none;
            border: none;
            color: #999;
            font-size: 16px;
            cursor: pointer;
            padding: 4px 8px;
            transition: all 0.3s ease;
        `;
        
        clearBtn.addEventListener('click', () => {
            this.searchInput.value = '';
            this.searchInput.focus();
            this.removeClearButton();
        });
        
        this.searchBar.insertBefore(clearBtn, this.searchBar.querySelector('.search-btn'));
    }
    
    removeClearButton() {
        const clearBtn = this.searchBar.querySelector('.clear-btn');
        if (clearBtn) {
            clearBtn.remove();
        }
    }
}

// ==========================================
// CATEGORY SMOOTH SCROLL
// ==========================================
class CategoryScroll {
    constructor() {
        this.categoryGrid = document.querySelector('.category-grid');
        if (!this.categoryGrid) return;
        
        this.init();
    }
    
    init() {
        // Add smooth horizontal scroll with mouse wheel
        this.categoryGrid.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                this.categoryGrid.scrollLeft += e.deltaY;
            }
        });
    }
}

// ==========================================
// TAB ACTIVE INDICATOR ANIMATION
// ==========================================
class TabIndicator {
    constructor() {
        this.tabs = document.querySelector('.shopee-tabs');
        if (!this.tabs) return;
        
        this.init();
    }
    
    init() {
        const activeTab = this.tabs.querySelector('.tab-item.active');
        if (!activeTab) return;
        
        // Add ripple effect on click
        const tabItems = this.tabs.querySelectorAll('.tab-item');
        tabItems.forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.createRipple(e, tab);
            });
        });
    }
    
    createRipple(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            background: rgba(238, 77, 45, 0.3);
            left: ${x}px;
            top: ${y}px;
            pointer-events: none;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
        `;
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
}

// ==========================================
// PRODUCT CARD HOVER EFFECTS
// ==========================================
class ProductHoverEffects {
    constructor() {
        this.addHoverEffects();
    }
    
    addHoverEffects() {
        const productCards = document.querySelectorAll('.product-card-shopee');
        
        productCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.addShineEffect(card);
            });
        });
    }
    
    addShineEffect(card) {
        const shine = document.createElement('div');
        shine.style.cssText = `
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.3), 
                transparent
            );
            transform: skewX(-20deg);
            animation: shine 0.6s ease;
            pointer-events: none;
        `;
        
        card.style.position = 'relative';
        card.style.overflow = 'hidden';
        card.appendChild(shine);
        
        setTimeout(() => shine.remove(), 600);
    }
}

// ==========================================
// LAZY LOAD IMAGES
// ==========================================
class LazyLoadImages {
    constructor() {
        this.images = document.querySelectorAll('img[data-src]');
        this.init();
    }
    
    init() {
        const options = {
            root: null,
            threshold: 0,
            rootMargin: '50px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        }, options);
        
        this.images.forEach(img => observer.observe(img));
    }
}

// ==========================================
// ADD RIPPLE ANIMATION CSS
// ==========================================
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes shine {
        from {
            left: -100%;
        }
        to {
            left: 200%;
        }
    }
`;
document.head.appendChild(style);

// ==========================================
// INITIALIZE ALL
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all features
    new BannerSlider();
    new BackToTop();
    new ProductAnimations();
    new SearchBar();
    new CategoryScroll();
    new TabIndicator();
    new ProductHoverEffects();
    new LazyLoadImages();
    
    // Add smooth page load animation
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
    
    console.log('🛍️ Shopee Home Initialized');
});

// ==========================================
// PERFORMANCE OPTIMIZATION
// ==========================================

// Debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Optimize scroll performance
window.addEventListener('scroll', debounce(() => {
    // Your scroll handler here
}, 100));

// Prefetch links on hover
document.querySelectorAll('a').forEach(link => {
    link.addEventListener('mouseenter', () => {
        const href = link.getAttribute('href');
        if (href && href.startsWith('/')) {
            const prefetchLink = document.createElement('link');
            prefetchLink.rel = 'prefetch';
            prefetchLink.href = href;
            document.head.appendChild(prefetchLink);
        }
    });
});