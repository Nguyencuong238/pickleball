// Tournament Detail Page JavaScript

// Helper functions (can be defined outside DOMContentLoaded)
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Đã sao chép link!', 'success');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Đã sao chép link!', 'success');
    } catch (err) {
        console.error('Fallback: Could not copy text', err);
        showNotification('Không thể sao chép link', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        font-weight: 600;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function updateCountdown() {
    const deadline = new Date('2025-11-30T23:59:59').getTime();
    const now = new Date().getTime();
    const timeLeft = deadline - now;
    
    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
    
    const urgencyBadge = document.querySelector('.urgency-badge');
    if (urgencyBadge && days > 0) {
        urgencyBadge.textContent = `⏰ Còn ${days} ngày`;
    }
}

function handleSidebarSticky() {
    const sidebar = document.querySelector('.detail-sidebar');
    if (!sidebar || window.innerWidth <= 1024) return;
    
    const footer = document.querySelector('.footer');
    const sidebarRect = sidebar.getBoundingClientRect();
    const footerRect = footer.getBoundingClientRect();
    
    // Stop sticky before footer
    // if (footerRect.top < window.innerHeight) {
    //     sidebar.style.position = 'absolute';
    //     sidebar.style.bottom = '0';
    //     sidebar.style.top = 'auto';
    // } else {
    //     sidebar.style.position = 'sticky';
    //     sidebar.style.top = '100px';
    //     sidebar.style.bottom = 'auto';
    // }
}

function addMobileLabels() {
    if (window.innerWidth <= 768) {
        const scheduleRows = document.querySelectorAll('.schedule-row:not(.schedule-header)');
        scheduleRows.forEach(row => {
            const cells = row.querySelectorAll('div');
            const labels = ['Giờ:', 'Sân:', 'Trận đấu:', 'Vòng:'];
            cells.forEach((cell, index) => {
                if (labels[index]) {
                    cell.setAttribute('data-label', labels[index]);
                }
            });
        });
    }
}

function printTournamentInfo() {
    window.print();
}

// Main initialization when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tournament detail page loading...');

    // Tab Functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    console.log('Tab buttons found:', tabButtons.length);
    console.log('Tab panes found:', tabPanes.length);
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.dataset.tab;
            
            console.log('Tab clicked:', targetTab);
            
            // Remove active class from all tabs and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding pane
            this.classList.add('active');
            const targetPane = document.getElementById(targetTab);
            if (targetPane) {
                targetPane.classList.add('active');
            }
            
            // Smooth scroll to tab content on mobile
            if (window.innerWidth <= 768) {
                const tabContent = document.querySelector('.tab-content');
                if (tabContent) {
                    tabContent.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }
        });
    });

    // Share Functionality
    const shareButtons = document.querySelectorAll('.share-btn');

    shareButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tournamentTitle = document.querySelector('.hero-title').textContent;
            const tournamentUrl = window.location.href;
            
            if (button.classList.contains('facebook')) {
                const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(tournamentUrl)}`;
                window.open(facebookUrl, '_blank', 'width=600,height=400');
            } else if (button.classList.contains('zalo')) {
                const zaloUrl = `https://zalo.me/share?url=${encodeURIComponent(tournamentUrl)}&title=${encodeURIComponent(tournamentTitle)}`;
                window.open(zaloUrl, '_blank', 'width=600,height=400');
            } else if (button.classList.contains('copy')) {
                copyToClipboard(tournamentUrl);
            }
        });
    });

    // Share button in hero section
    const heroShareBtn = document.querySelector('.hero-actions .btn-white');
    if (heroShareBtn) {
        heroShareBtn.addEventListener('click', () => {
            // Show share options (could be a modal)
            alert('Chức năng chia sẻ đang được phát triển!');
        });
    }

    // Registration Button - Now handled by modal in blade template
    // Code removed as modal functionality is now in the blade file

    // Save Tournament Button
    const saveButton = document.querySelector('.hero-actions .btn-secondary');
    if (saveButton) {
        let isSaved = false;
        
        saveButton.addEventListener('click', () => {
            isSaved = !isSaved;
            
            if (isSaved) {
                saveButton.innerHTML = `
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                        <line x1="4" y1="22" x2="4" y2="15"/>
                    </svg>
                    Đã lưu
                `;
                showNotification('Đã lưu giải đấu!', 'success');
            } else {
                saveButton.innerHTML = `
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                        <line x1="4" y1="22" x2="4" y2="15"/>
                    </svg>
                    Lưu giải đấu
                `;
                showNotification('Đã bỏ lưu', 'info');
            }
        });
    }

    // Contact Button
    const contactButton = document.querySelector('.contact-card .btn-outline');
    if (contactButton) {
        contactButton.addEventListener('click', () => {
            showNotification('Đang kết nối với BTC...', 'info');
            // In real app, open chat widget or redirect to contact page
        });
    }

    // Animate progress bar on load
    const progressFill = document.querySelector('.progress-fill');
    if (progressFill) {
        const targetWidth = progressFill.style.width;
        progressFill.style.width = '0';
        
        setTimeout(() => {
            progressFill.style.width = targetWidth;
        }, 500);
    }

    // Animate stats on scroll
    const tournamentStatsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statCards = entry.target.querySelectorAll('.stat-card');
                statCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
                tournamentStatsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    const statsBar = document.querySelector('.stats-bar');
    if (statsBar) {
        // Set initial state
        const statCards = statsBar.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
        });
        
        tournamentStatsObserver.observe(statsBar);
    }

    // Animate content cards
    const contentObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                contentObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.content-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.5s ease';
        contentObserver.observe(card);
    });

    // Timeline animation
    const timelineObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const items = entry.target.querySelectorAll('.timeline-item');
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateX(0)';
                    }, index * 200);
                });
                timelineObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    const timeline = document.querySelector('.timeline');
    if (timeline) {
        const items = timeline.querySelectorAll('.timeline-item');
        items.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            item.style.transition = 'all 0.5s ease';
        });
        timelineObserver.observe(timeline);
    }

    // Update countdown on load
    updateCountdown();

    // Smooth scroll for related tournament links
    document.querySelectorAll('.related-item').forEach(link => {
        link.addEventListener('click', (e) => {
            // In real app, this would navigate to the tournament detail page
            e.preventDefault();
            showNotification('Đang chuyển đến giải đấu...', 'info');
        });
    });

    // Add data labels for mobile schedule table
    addMobileLabels();

    // Gallery lightbox effect (simplified)
    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('click', () => {
            // In real app, open lightbox with full-size image
            showNotification('Gallery đang được phát triển!', 'info');
        });
    });

    // Track which tab user is viewing (for analytics)
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.dataset.tab;
            console.log('User viewing tab:', tabName);
            // In real app, send to analytics
        });
    });

    // FAQ accordion (if FAQ section is added)
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const faqItem = question.parentElement;
            faqItem.classList.toggle('active');
        });
    });

    // Initialize tooltips (if any)
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = e.target.dataset.tooltip;
            tooltip.style.cssText = `
                position: absolute;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 0.5rem 0.75rem;
                border-radius: 0.25rem;
                font-size: 0.875rem;
                z-index: 10000;
                pointer-events: none;
                white-space: nowrap;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            
            element.addEventListener('mouseleave', () => {
                document.body.removeChild(tooltip);
            }, { once: true });
        });
    });

    // Add print button functionality if exists
    const printButton = document.querySelector('[data-action="print"]');
    if (printButton) {
        printButton.addEventListener('click', printTournamentInfo);
    }

    console.log('Tournament detail page loaded successfully! 🎾');
});

// Add animation styles to head
const animationStyles = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = animationStyles;
document.head.appendChild(styleSheet);

// Update countdown every hour
setInterval(updateCountdown, 3600000);

// Sidebar sticky on scroll
window.addEventListener('scroll', handleSidebarSticky);
window.addEventListener('resize', handleSidebarSticky);

// Mobile labels on load and resize
window.addEventListener('resize', addMobileLabels);

// Add structured data for SEO (JSON-LD)
document.addEventListener('DOMContentLoaded', function() {
    const titleElement = document.querySelector('.hero-title');
    if (titleElement) {
        const structuredData = {
            "@context": "https://schema.org",
            "@type": "SportsEvent",
            "name": titleElement.textContent,
            "startDate": "2025-12-15",
            "endDate": "2025-12-17",
            "location": {
                "@type": "Place",
                "name": "Sân Rạch Chiếc Sport Complex",
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "Quận 2",
                    "addressRegion": "TP.HCM",
                    "addressCountry": "VN"
                }
            },
            "offers": {
                "@type": "Offer",
                "price": "500000",
                "priceCurrency": "VND"
            }
        };

        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.text = JSON.stringify(structuredData);
        document.head.appendChild(script);
    }
});
