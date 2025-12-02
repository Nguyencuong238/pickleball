// Mobile Menu Toggle
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const navMenu = document.querySelector('.nav-menu');
const navActions = document.querySelector('.nav-actions');
const body = document.body;

// Toggle mobile menu
if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', () => {
        mobileMenuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
        //navActions.classList.toggle('active');
        body.classList.toggle('menu-open');
    });
}

// Close mobile menu when clicking on a link
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        mobileMenuToggle.classList.remove('active');
        navMenu.classList.remove('active');
        //navActions.classList.remove('active');
        body.classList.remove('menu-open');
    });
});

// Header scroll effect
const header = document.querySelector('.header');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
});

// Active nav link on scroll
const sections = document.querySelectorAll('section[id]');

function updateActiveNavLink() {
    const scrollPosition = window.scrollY + 150;
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                    link.classList.add('active');
                }
            });
        }
    });
}

window.addEventListener('scroll', updateActiveNavLink);

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            const headerOffset = 80;
            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Intersection Observer for fade-in animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe cards and sections
const cardsToObserve = document.querySelectorAll(
    '.tournament-card, .court-card, .social-card, .news-card'
);

cardsToObserve.forEach(card => {
    observer.observe(card);
});

// Form validation for CTA section (disabled - using inline handler in home.blade.php)
// const ctaForm = document.querySelector('.cta-form');
// const ctaInput = document.querySelector('.cta-input');
// const ctaButton = document.querySelector('.cta-form .btn');

// if (ctaForm) {
//     ctaButton.addEventListener('click', (e) => {
//         e.preventDefault();
        
//         const email = ctaInput.value.trim();
        
//         if (!email) {
//             alert('Vui lòng nhập địa chỉ email của bạn');
//             ctaInput.focus();
//             return;
//         }
        
//         // Simple email validation
//         const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//         if (!emailRegex.test(email)) {
//             alert('Vui lòng nhập địa chỉ email hợp lệ');
//             ctaInput.focus();
//             return;
//         }
        
//         // Success - clear input and redirect to register
//         ctaInput.value = '';
//         window.location.href = '/register?email=' + encodeURIComponent(email);
//     });
// }

// Lazy loading images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    const images = document.querySelectorAll('img[data-src]');
    images.forEach(img => imageObserver.observe(img));
}

// Add loading state to buttons
const allButtons = document.querySelectorAll('.btn');

// allButtons.forEach(button => {
//     if (button.textContent.includes('Đăng ký') || button.textContent.includes('Tham gia')) {
//         button.addEventListener('click', function(e) {
//             // Prevent default for demo purposes
//             if (!this.closest('form')) {
//                 e.preventDefault();
//             }
            
//             const originalText = this.textContent;
//             this.textContent = 'Đang xử lý...';
//             this.disabled = true;
            
//             // Simulate loading
//             setTimeout(() => {
//                 this.textContent = originalText;
//                 this.disabled = false;
                
//                 // Show success message for demo
//                 if (!this.closest('.cta-form')) {
//                     alert('Đăng ký thành công! Chúng tôi sẽ liên hệ với bạn sớm.');
//                 }
//             }, 1500);
//         });
//     }
// });

// Add stats counter animation
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = formatNumber(target);
            clearInterval(timer);
        } else {
            element.textContent = formatNumber(Math.floor(start));
        }
    }, 16);
}

function formatNumber(num) {
    if (num >= 1000) {
        return (num / 1000).toFixed(1).replace('.0', '') + 'K';
    }
    return num.toString();
}

// Animate stats when they come into view
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statNumbers = entry.target.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const text = stat.textContent;
                const number = parseInt(text.replace(/[^0-9]/g, ''));
                
                if (!isNaN(number)) {
                    stat.textContent = '0';
                    animateCounter(stat, number);
                }
            });
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const heroStats = document.querySelector('.hero-stats');
if (heroStats) {
    statsObserver.observe(heroStats);
}

// Add parallax effect to hero background
const heroBackground = document.querySelector('.hero-background');

if (heroBackground) {
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * 0.5;
        
        if (scrolled < window.innerHeight) {
            heroBackground.style.transform = `translateY(${rate}px)`;
        }
    });
}

// Console message
console.log('%c🎾 Welcome to OnePickleball.vn! 🎾', 
    'font-size: 20px; font-weight: bold; color: #00D9B5;');
console.log('%cDeveloped with ❤️ for the Pickleball community', 
    'font-size: 12px; color: #666;');
