// Tournaments Page JavaScript

// View Toggle (Grid/List)
const viewButtons = document.querySelectorAll('.view-btn');
const tournamentsGrid = document.getElementById('tournamentsGrid');

viewButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const view = btn.dataset.view;
        
        // Update active state
        viewButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Toggle grid class
        if (view === 'list') {
            tournamentsGrid.classList.add('list-view');
        } else {
            tournamentsGrid.classList.remove('list-view');
        }
    });
});

// Filter Reset
const filterReset = document.querySelector('.filter-reset');
if (filterReset) {
    filterReset.addEventListener('click', () => {
        // Reset all checkboxes
        document.querySelectorAll('.filter-checkbox input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        // Reset first two checkboxes (Đang mở, Sắp mở)
        const statusCheckboxes = document.querySelectorAll('.filter-checkbox input[type="checkbox"]');
        if (statusCheckboxes.length >= 2) {
            statusCheckboxes[0].checked = true;
            statusCheckboxes[1].checked = true;
        }
        
        // Reset radio buttons
        document.querySelectorAll('.filter-radio input[type="radio"]').forEach(radio => {
            if (radio.value === '') {
                radio.checked = true;
            }
        });
        
        // Reset selects
        document.querySelectorAll('.filter-select').forEach(select => {
            select.selectedIndex = 0;
        });
        
        // Reset inputs
        document.querySelectorAll('.filter-search, .filter-date').forEach(input => {
            input.value = '';
        });
        
        // Update active filters
        updateActiveFilters();
    });
}

// Search Filter
const searchInput = document.querySelector('.filter-search');
if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            console.log('Searching for:', e.target.value);
            // Implement search logic here
            filterTournaments();
        }, 500);
    });
}

// Status Filter
const statusCheckboxes = document.querySelectorAll('.filter-options .filter-checkbox input[type="checkbox"]');
statusCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        updateActiveFilters();
        filterTournaments();
    });
});

// Location Filter
const locationSelect = document.querySelector('.filter-select');
if (locationSelect) {
    locationSelect.addEventListener('change', () => {
        filterTournaments();
    });
}

// Apply Filters Button
const applyFilterBtn = document.querySelector('.filter-apply');
if (applyFilterBtn) {
    applyFilterBtn.addEventListener('click', () => {
        filterTournaments();
        // Smooth scroll to results
        document.querySelector('.tournaments-main').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    });
}

// Update Active Filters Display
function updateActiveFilters() {
    const activeFiltersContainer = document.querySelector('.active-filters');
    if (!activeFiltersContainer) return;
    
    activeFiltersContainer.innerHTML = '';
    
    // Get checked status filters
    const checkedStatuses = Array.from(statusCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.parentElement.querySelector('span:nth-child(3)').textContent);
    
    // Add filter tags
    checkedStatuses.forEach(status => {
        const tag = document.createElement('span');
        tag.className = 'filter-tag';
        tag.innerHTML = `
            ${status}
            <button class="tag-remove">&times;</button>
        `;
        
        // Add remove functionality
        tag.querySelector('.tag-remove').addEventListener('click', () => {
            // Find and uncheck the corresponding checkbox
            statusCheckboxes.forEach(cb => {
                const label = cb.parentElement.querySelector('span:nth-child(3)').textContent;
                if (label === status) {
                    cb.checked = false;
                }
            });
            updateActiveFilters();
            filterTournaments();
        });
        
        activeFiltersContainer.appendChild(tag);
    });
}

// Filter Tournaments Function
function filterTournaments() {
    console.log('Filtering tournaments...');
    
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const selectedLocation = locationSelect ? locationSelect.value : '';
    
    const checkedStatuses = Array.from(statusCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.parentElement.querySelector('span:nth-child(3)').textContent);
    
    // Update result count
    const resultCount = document.querySelector('.result-count');
    if (resultCount) {
        // In real implementation, this would be the actual filtered count
        const count = Math.floor(Math.random() * 30) + 20;
        resultCount.textContent = count;
    }
    
    // Here you would filter the tournament cards based on criteria
    // For now, we'll just log the filters
    console.log({
        searchTerm,
        selectedLocation,
        checkedStatuses
    });
}

// Sort Functionality
const sortSelect = document.querySelector('.sort-select');

function applySortFilter() {
    const form = document.getElementById('filterForm');
    if (form && sortSelect) {
        const sortValue = sortSelect.value;
        
        // Create hidden input for sort if it doesn't exist
        let sortInput = form.querySelector('input[name="sort"]');
        if (!sortInput) {
            sortInput = document.createElement('input');
            sortInput.type = 'hidden';
            sortInput.name = 'sort';
            form.appendChild(sortInput);
        }
        sortInput.value = sortValue;
        
        // Submit the form
        form.submit();
    }
}

if (sortSelect) {
    sortSelect.addEventListener('change', applySortFilter);
}

// Pagination
const paginationNumbers = document.querySelectorAll('.pagination-number');
const prevBtn = document.querySelector('.pagination-prev');
const nextBtn = document.querySelector('.pagination-next');

let currentPage = 1;
const totalPages = 10;

paginationNumbers.forEach(btn => {
    btn.addEventListener('click', () => {
        if (!btn.classList.contains('active')) {
            const pageNum = parseInt(btn.textContent);
            goToPage(pageNum);
        }
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
    
    // Update active state
    paginationNumbers.forEach(btn => {
        btn.classList.remove('active');
        if (parseInt(btn.textContent) === pageNum) {
            btn.classList.add('active');
        }
    });
    
    // Update prev/next button states
    if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
    }
    if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages;
    }
    
    // Scroll to top of results
    document.querySelector('.tournaments-main').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
    
    // In real implementation, fetch new data for this page
    console.log('Loading page:', pageNum);
}

// Mobile Filter Toggle
function createMobileFilterToggle() {
    if (window.innerWidth <= 1024) {
        const sidebar = document.querySelector('.tournaments-sidebar');
        const toolbar = document.querySelector('.tournaments-toolbar');
        
        if (!document.querySelector('.mobile-filter-toggle')) {
            const filterToggle = document.createElement('button');
            filterToggle.className = 'btn btn-outline mobile-filter-toggle';
            filterToggle.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="4" y1="21" x2="4" y2="14"/>
                    <line x1="4" y1="10" x2="4" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12" y2="3"/>
                    <line x1="20" y1="21" x2="20" y2="16"/>
                    <line x1="20" y1="12" x2="20" y2="3"/>
                </svg>
                Bộ lọc
            `;
            
            toolbar.insertBefore(filterToggle, toolbar.firstChild);
            
            filterToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show-mobile');
                
                // Create overlay
                if (!document.querySelector('.filter-overlay')) {
                    const overlay = document.createElement('div');
                    overlay.className = 'filter-overlay';
                    overlay.addEventListener('click', () => {
                        sidebar.classList.remove('show-mobile');
                        overlay.remove();
                    });
                    document.body.appendChild(overlay);
                } else {
                    document.querySelector('.filter-overlay').remove();
                }
            });
        }
    }
}

// Add mobile styles dynamically
const mobileStyles = `
    @media (max-width: 1024px) {
        .tournaments-sidebar {
            position: fixed;
            left: -100%;
            top: 0;
            bottom: 0;
            width: 320px;
            max-width: 90%;
            background: white;
            z-index: 1001;
            overflow-y: auto;
            transition: left 0.3s ease;
            padding: 1rem;
        }
        
        .tournaments-sidebar.show-mobile {
            left: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .filter-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .mobile-filter-toggle {
            display: flex !important;
            align-items: center;
            gap: 0.5rem;
        }
        
        .mobile-filter-toggle .icon {
            width: 18px;
            height: 18px;
        }
    }
    
    @media (min-width: 1025px) {
        .mobile-filter-toggle {
            display: none !important;
        }
    }
`;

// Inject mobile styles
const styleSheet = document.createElement('style');
styleSheet.textContent = mobileStyles;
document.head.appendChild(styleSheet);

// Initialize mobile filter toggle
createMobileFilterToggle();
window.addEventListener('resize', createMobileFilterToggle);

// Initialize active filters on page load
updateActiveFilters();

// Animate tournament cards on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, index * 100);
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Set initial state and observe
document.querySelectorAll('.tournament-item').forEach((item) => {
    item.style.opacity = '0';
    item.style.transform = 'translateY(20px)';
    item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(item);
});

// Quick Stats Counter Animation
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = formatStatNumber(value, end);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function formatStatNumber(num, max) {
    if (max >= 1000000000) {
        return (num / 1000000000).toFixed(1) + ' tỷ';
    } else if (max >= 1000) {
        return (num / 1000).toFixed(0).replace(/\.0$/, '') + 'K';
    }
    return num.toString();
}

// Animate stats on scroll
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statBoxes = entry.target.querySelectorAll('.stat-box');
            statBoxes.forEach((box, index) => {
                const number = box.querySelector('.stat-number');
                const originalText = number.textContent;
                let targetValue = 0;
                
                // Parse the target value
                if (originalText.includes('tỷ')) {
                    targetValue = parseFloat(originalText) * 1000000000;
                } else if (originalText.includes('K')) {
                    targetValue = parseFloat(originalText) * 1000;
                } else {
                    targetValue = parseInt(originalText.replace(/[^0-9]/g, ''));
                }
                
                setTimeout(() => {
                    animateValue(number, 0, targetValue, 1500);
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

// Prevent default on register buttons in tournament cards
document.querySelectorAll('.tournament-footer .btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        alert('Chức năng đăng ký đang được phát triển!');
    });
});

console.log('Tournaments page loaded successfully! 🎾');
