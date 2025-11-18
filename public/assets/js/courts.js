<<<<<<< HEAD
// Courts Page JavaScript

// View Mode Toggle (Grid / List / Map)
const viewModeButtons = document.querySelectorAll('.view-mode-btn');
const courtsGrid = document.getElementById('courtsGrid');
const courtsMap = document.getElementById('courtsMap');

viewModeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const view = btn.dataset.view;
        
        // Update active state
        viewModeButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Toggle views
        if (view === 'grid') {
            courtsGrid.classList.add('active');
            courtsGrid.classList.remove('list-view');
            courtsMap.classList.remove('active');
        } else if (view === 'list') {
            courtsGrid.classList.add('active', 'list-view');
            courtsMap.classList.remove('active');
        } else if (view === 'map') {
            courtsGrid.classList.remove('active');
            courtsMap.classList.add('active');
            // Initialize map if needed
            initializeMap();
        }
    });
});

// Map Initialization (placeholder for Google Maps API)
function initializeMap() {
    console.log('Initializing map...');
    // In production, initialize Google Maps here
    // Example:
    // const map = new google.maps.Map(document.getElementById('courtsMap'), {
    //     center: { lat: 10.8231, lng: 106.6297 }, // Ho Chi Minh City
    //     zoom: 12
    // });
}

// Main Search
const mainSearchInput = document.querySelector('.main-search-input');
const locationSelect = document.querySelector('.location-select');
const searchBtn = document.querySelector('.search-btn');

if (searchBtn) {
    searchBtn.addEventListener('click', () => {
        performSearch();
    });
}

if (mainSearchInput) {
    mainSearchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
}

function performSearch() {
    const searchTerm = mainSearchInput.value.toLowerCase();
    const location = locationSelect.value;
    
    console.log('Searching:', { searchTerm, location });
    
    // Filter court cards
    const courtCards = document.querySelectorAll('.court-card');
    let visibleCount = 0;
    
    courtCards.forEach(card => {
        const courtName = card.querySelector('.court-name').textContent.toLowerCase();
        const courtLocation = card.querySelector('.court-location span').textContent.toLowerCase();
        
        const matchesSearch = !searchTerm || courtName.includes(searchTerm) || courtLocation.includes(searchTerm);
        const matchesLocation = !location || courtLocation.includes(location);
        
        if (matchesSearch && matchesLocation) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update result count
    updateResultCount(visibleCount);
}

function updateResultCount(count) {
    const resultText = document.querySelector('.result-text');
    if (resultText) {
        resultText.innerHTML = `Tìm thấy <strong>${count} sân</strong>`;
    }
}

// Mobile Filter Toggle
const filterMobileBtn = document.querySelector('.filter-mobile-btn');
const courtsSidebar = document.querySelector('.courts-sidebar');

if (filterMobileBtn) {
    filterMobileBtn.addEventListener('click', () => {
        courtsSidebar.classList.toggle('show-mobile');
        
        // Create/remove overlay
        let overlay = document.querySelector('.filter-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'filter-overlay active';
            overlay.addEventListener('click', () => {
                courtsSidebar.classList.remove('show-mobile');
                overlay.remove();
            });
            document.body.appendChild(overlay);
        } else {
            overlay.remove();
        }
    });
}

// Filter Reset
const filterReset = document.querySelector('.filter-reset');
if (filterReset) {
    filterReset.addEventListener('click', () => {
        // Reset all filters
        document.querySelectorAll('.filter-checkbox input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        document.querySelectorAll('.filter-radio input[type="radio"]').forEach(radio => {
            if (radio.value === '') {
                radio.checked = true;
            }
        });
        
        // Reset price inputs
        document.querySelectorAll('.price-input').forEach(input => {
            input.value = '';
        });
        
        // Reset range sliders
        const rangeMin = document.querySelector('.range-min');
        const rangeMax = document.querySelector('.range-max');
        if (rangeMin) rangeMin.value = rangeMin.min;
        if (rangeMax) rangeMax.value = rangeMax.max;
        
        // Apply filters
        applyFilters();
    });
}

// Price Range Slider
const rangeMin = document.querySelector('.range-min');
const rangeMax = document.querySelector('.range-max');
const priceInputs = document.querySelectorAll('.price-input');

if (rangeMin && rangeMax) {
    rangeMin.addEventListener('input', () => {
        if (parseInt(rangeMin.value) > parseInt(rangeMax.value)) {
            rangeMin.value = rangeMax.value;
        }
        updatePriceInputs();
    });
    
    rangeMax.addEventListener('input', () => {
        if (parseInt(rangeMax.value) < parseInt(rangeMin.value)) {
            rangeMax.value = rangeMin.value;
        }
        updatePriceInputs();
    });
}

function updatePriceInputs() {
    if (priceInputs.length >= 2) {
        priceInputs[0].value = rangeMin.value;
        priceInputs[1].value = rangeMax.value;
    }
}

// Sync price inputs with sliders
priceInputs.forEach((input, index) => {
    input.addEventListener('change', () => {
        if (index === 0 && rangeMin) {
            rangeMin.value = input.value;
        } else if (index === 1 && rangeMax) {
            rangeMax.value = input.value;
        }
    });
});

// Apply Filters Button
const filterApply = document.querySelector('.filter-apply');
if (filterApply) {
    filterApply.addEventListener('click', () => {
        applyFilters();
        
        // Close mobile filter
        if (window.innerWidth <= 1024) {
            courtsSidebar.classList.remove('show-mobile');
            const overlay = document.querySelector('.filter-overlay');
            if (overlay) overlay.remove();
        }
        
        // Scroll to results
        courtsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

function applyFilters() {
    const courtCards = document.querySelectorAll('.court-card');
    let visibleCount = 0;
    
    // Get filter values
    const priceMin = parseInt(priceInputs[0]?.value) || 0;
    const priceMax = parseInt(priceInputs[1]?.value) || Infinity;
    
    const selectedRatings = Array.from(document.querySelectorAll('.filter-options .filter-checkbox input:checked'))
        .map(cb => cb.parentElement.querySelector('.rating-stars')?.textContent)
        .filter(Boolean);
    
    const selectedFacilities = Array.from(document.querySelectorAll('.filter-group:nth-child(4) .filter-checkbox input:checked'))
        .map(cb => cb.parentElement.textContent.trim());
    
    courtCards.forEach(card => {
        let visible = true;
        
        // Price filter
        const priceText = card.querySelector('.price-value').textContent;
        const prices = priceText.match(/\d+/g);
        if (prices) {
            const minPrice = parseInt(prices[0]) * 1000;
            const maxPrice = parseInt(prices[1]) * 1000;
            if (maxPrice < priceMin || minPrice > priceMax) {
                visible = false;
            }
        }
        
        // Rating filter
        if (selectedRatings.length > 0) {
            const rating = parseFloat(card.querySelector('.rating-value').textContent);
            let matchesRating = false;
            selectedRatings.forEach(selected => {
                if (selected.includes('5.0') && rating === 5.0) matchesRating = true;
                if (selected.includes('4.0+') && rating >= 4.0) matchesRating = true;
                if (selected.includes('3.0+') && rating >= 3.0) matchesRating = true;
            });
            if (!matchesRating) visible = false;
        }
        
        // Facilities filter
        if (selectedFacilities.length > 0) {
            const cardFacilities = Array.from(card.querySelectorAll('.feature-tag'))
                .map(tag => tag.textContent.trim());
            
            const hasAllFacilities = selectedFacilities.every(facility => 
                cardFacilities.some(cardFacility => cardFacility.includes(facility.split(' ')[0]))
            );
            
            if (!hasAllFacilities) visible = false;
        }
        
        if (visible) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    updateResultCount(visibleCount);
}

// Favorite Functionality
const favoriteButtons = document.querySelectorAll('.favorite-btn');

favoriteButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        btn.classList.toggle('active');
        
        const courtName = btn.closest('.court-card').querySelector('.court-name').textContent;
        
        if (btn.classList.contains('active')) {
            showNotification(`Đã thêm "${courtName}" vào danh sách yêu thích`, 'success');
            saveFavorite(courtName);
        } else {
            showNotification(`Đã xóa "${courtName}" khỏi danh sách yêu thích`, 'info');
            removeFavorite(courtName);
        }
    });
});

function saveFavorite(courtName) {
    let favorites = JSON.parse(localStorage.getItem('favoriteCourts') || '[]');
    if (!favorites.includes(courtName)) {
        favorites.push(courtName);
        localStorage.setItem('favoriteCourts', JSON.stringify(favorites));
    }
}

function removeFavorite(courtName) {
    let favorites = JSON.parse(localStorage.getItem('favoriteCourts') || '[]');
    favorites = favorites.filter(name => name !== courtName);
    localStorage.setItem('favoriteCourts', JSON.stringify(favorites));
}

function loadFavorites() {
    const favorites = JSON.parse(localStorage.getItem('favoriteCourts') || '[]');
    favoriteButtons.forEach(btn => {
        const courtName = btn.closest('.court-card').querySelector('.court-name').textContent;
        if (favorites.includes(courtName)) {
            btn.classList.add('active');
        }
    });
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
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
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Animate cards on scroll
const cardObserverOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const cardObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, index * 50);
            cardObserver.unobserve(entry.target);
        }
    });
}, cardObserverOptions);

document.querySelectorAll('.court-card').forEach((card) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    cardObserver.observe(card);
});

// Stats Counter Animation
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statBoxes = entry.target.querySelectorAll('.stat-box');
            statBoxes.forEach((box, index) => {
                const number = box.querySelector('.stat-number');
                const originalText = number.textContent;
                let targetValue = parseFloat(originalText);
                
                setTimeout(() => {
                    if (originalText.includes('.')) {
                        // For decimal numbers like 4.7
                        let current = 0;
                        const increment = targetValue / 50;
                        const interval = setInterval(() => {
                            current += increment;
                            if (current >= targetValue) {
                                number.textContent = targetValue.toFixed(1);
                                clearInterval(interval);
                            } else {
                                number.textContent = current.toFixed(1);
                            }
                        }, 20);
                    } else if (originalText.includes('+')) {
                        // For numbers with +
                        const numValue = parseInt(originalText);
                        animateValue(number, 0, numValue, 1500);
                        setTimeout(() => {
                            number.textContent = originalText;
                        }, 1500);
                    } else {
                        // For regular numbers
                        animateValue(number, 0, targetValue, 1500);
                    }
                }, index * 200);
            });
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const quickStats = document.querySelector('.quick-stats');
if (quickStats) {
    statsObserver.observe(quickStats);
}

// Pagination
const paginationNumbers = document.querySelectorAll('.pagination-number');
const prevBtn = document.querySelector('.pagination-prev');
const nextBtn = document.querySelector('.pagination-next');

let currentPage = 1;
const totalPages = 8;

paginationNumbers.forEach(btn => {
    btn.addEventListener('click', () => {
        const pageNum = parseInt(btn.textContent);
        goToPage(pageNum);
    });
});

if (prevBtn) {
    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            goToPage(currentPage - 1);
        }
    });
}

if (nextBtn) {
    nextBtn.addEventListener('click', () => {
        if (currentPage < totalPages) {
            goToPage(currentPage + 1);
        }
    });
}

function goToPage(pageNum) {
    currentPage = pageNum;
    
    paginationNumbers.forEach(btn => {
        btn.classList.remove('active');
        if (parseInt(btn.textContent) === pageNum) {
            btn.classList.add('active');
        }
    });
    
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;
    
    // Scroll to top of results
    courtsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    console.log('Loading page:', pageNum);
}

// Initialize
loadFavorites();
updateResultCount(document.querySelectorAll('.court-card').length);

console.log('Courts page loaded successfully! 🏟️');
=======
>>>>>>> 0911c1a8edf7d23a92f251dcd398a3d5463842db
