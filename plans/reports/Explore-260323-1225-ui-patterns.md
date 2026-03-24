# Exploration Report: UI Patterns Analysis

**Date:** 2026-03-23  
**Focus:** CSS naming conventions, responsive design, Alpine.js patterns, AJAX integrations

---

## 1. CSS Patterns Overview

### CSS File Organization
- **Main style files:** `styles.css`, `style.css`, `booking.css`
- **Feature-specific:** `tournaments.css`, `courts.css`, `styles-club.css`, `booking.css`
- **Component libraries:** `tournament-dashboard/` subdirectory with modular CSS files
- **Pattern:** Feature-based organization (e.g., `styles-coaches.css`, `styles-courses.css`)

### CSS Variables & Design System

Located in `:root` in `style.css` and `styles.css`:

```css
/* Primary Colors */
--primary-color: #00D9B5 (Turquoise)
--primary-dark: #00B89A
--primary-light: #33E3C6

/* Secondary Colors */
--secondary-color: #0099CC (Blue)
--secondary-dark: #007AA3
--secondary-light: #33ADDB

/* Accents */
--accent-orange: #FF8E53
--accent-red: #FF6B6B
--accent-yellow: #FFD93D
--accent-purple: #9D84B7

/* Neutral palette */
--text-primary: #1A1A1A
--text-secondary: #666666
--text-light: #999999
--bg-white: #FFFFFF
--bg-light: #F8F9FA
--bg-dark: #1A1A1A
--border-color: #E5E5E5

/* Spacing scale */
--spacing-xs: 0.5rem
--spacing-sm: 1rem
--spacing-md: 1.5rem
--spacing-lg: 2rem
--spacing-xl: 3rem
--spacing-xxl: 4rem

/* Typography stack */
--font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif
--font-size-xs: 0.75rem
--font-size-sm: 0.875rem
--font-size-base: 1rem
--font-size-lg: 1.125rem
--font-size-xl: 1.25rem
--font-size-2xl: 1.5rem
--font-size-3xl: 2rem
--font-size-4xl: 2.5rem
--font-size-5xl: 3rem

/* Border radius scale */
--radius-sm: 0.5rem
--radius-md: 0.75rem
--radius-lg: 1rem
--radius-xl: 1.5rem

/* Shadows */
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05)
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1)
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1)
--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1)

/* Transitions */
--transition-fast: 150ms ease-in-out
--transition-base: 300ms ease-in-out
--transition-slow: 500ms ease-in-out
```

### CSS Naming Convention

**BEM-inspired approach** with kebab-case:
- `.club-cover` - Block (main component)
- `.club-avatar` - Element (sub-component)
- `.club-avatar.verified-badge` - Element within element
- `.event-card` - Main card component
- `.event-title-link` - Sub-element with purpose
- `.booking-card` - Feature-specific card
- `.step-indicator`, `.step`, `.step-number` - Hierarchical naming
- `.td-` prefix for tournament-dashboard components (e.g., `.td-sidebar`, `.td-nav-item`, `.td-status`)

**Pattern:** `{component}-{sub-element}[-{variant}]`

### Responsive Breakpoints

Mobile-first approach with three main breakpoints:

```css
1024px (tablet/landscape) - @media (max-width: 1024px)
768px  (tablet/portrait)  - @media (max-width: 768px)
480px  (mobile)           - @media (max-width: 480px)
```

**Examples:**
- Desktop grid: `grid-template-columns: repeat(4, 1fr)`
- Tablet (1024px): `grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))`
- Mobile tablet (768px): Further reduced layouts
- Mobile (480px): Single column, full-width layouts

### Layout Patterns

**Container:**
- `.container` class for max-width constrained layouts
- Uses CSS Grid and Flexbox

**Grid patterns:**
- `grid-template-columns: repeat(auto-fill, minmax(350px, 1fr))` for tournament cards
- `grid-template-columns: repeat(2, 1fr)` for 2-column layouts
- Responsive: adjusts to 1-2 columns on mobile

**Flexbox patterns:**
- `.nav-link` - horizontal navigation
- `.club-header-info` - flex with gap (horizontal alignment)
- `.club-stats-row` - flex-wrap for responsive stats
- `-direction: column` used for mobile stacking

---

## 2. Alpine.js Patterns

### Data Structure Convention

**Function-based factories** that return x-data object:

```javascript
function tournamentAthletes(config) {
    return {
        // Config injected from Blade (passed via x-data props)
        tournamentId: config.tournamentId,
        storeUrl: config.storeUrl,
        csrfToken: config.csrfToken,

        // State properties
        athletes: config.athletes || [],
        activeFilter: 'all',
        searchQuery: '',
        selectedIds: [],
        showModal: false,
        editMode: false,

        // Computed properties using get
        get filtered() {
            // Filtering logic
        },
        get counts() {
            // Calculation logic
        },

        // Methods
        async apiFetch(url, method, body) { },
        openAddModal() { },
        async submitPost() { },
    };
}
```

### Alpine Binding Patterns

**Directives used:**
- `x-data="functionName(config)"` - Initialize component
- `x-model="propertyName"` - Two-way binding for inputs
- `x-show="condition"` - CSS display toggle
- `x-if="condition"` - DOM removal (heavy operations)
- `x-text="expression"` - Text interpolation
- `x-for="item in array"` - Loop rendering
- `x-cloak` - Hide content during Alpine init
- `:key="uniqueId"` - Vue-like keying for loops
- `:class="{ active: isActive }"` - Conditional classes
- `@click="method()"` - Event handling
- `@change="method()"` - Form change events
- `@submit.prevent="method()"` - Form submission prevention
- `@click.self="condition"` - Modal backdrop close
- `@click.away="close()"` - Click outside handling

### Data Patterns

**Configuration injection from Blade:**
```javascript
x-data="tournamentAthletes({
    tournamentId: '{{ $tournament->slug }}',
    storeUrl: '{{ route('tournament-manage.athletes.store', $tournament) }}',
    athletes: {{ $athletesJson }},  // JSON array
    categories: {{ $categoriesJson }} // JSON array
})"
```

**State management:**
- Filters: `activeFilter`, `categoryFilter`, `statusFilter`
- Search: `searchQuery`, `userSearchQuery`
- Modal states: `showModal`, `showCreateModal`, `editMode`
- Form data: `form: { field1: '', field2: '' }`
- Arrays: `selectedIds: []`, `athletes: []`
- Loading: `loading`, `submitting`, `userSearchLoading`

### Computed Properties Pattern

```javascript
get filtered() {
    const q = this.searchQuery.toLowerCase().trim();
    const catId = this.categoryFilter ? parseInt(this.categoryFilter) : null;
    return this.athletes.filter(a => {
        const matchFilter = this.activeFilter === 'all' || a.status === this.activeFilter;
        const matchCategory = !catId || a.category_id === catId;
        const matchSearch = !q || a.athlete_name.toLowerCase().includes(q);
        return matchFilter && matchCategory && matchSearch;
    });
}
```

---

## 3. AJAX & Fetch Patterns

### Fetch Helper Pattern

```javascript
async apiFetch(url, method, body) {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
            'Accept': 'application/json',
        },
        body: body ? JSON.stringify(body) : undefined,
    });
    return res.json();
}
```

### API Module Pattern (MatchesApi)

Standalone fetch utilities:
```javascript
const MatchesApi = {
    async loadMatches(indexUrl, categoryId, filter) {
        const params = new URLSearchParams();
        if (categoryId) params.set('category', categoryId);
        if (filter && filter !== 'all') params.set('status', filter);
        const res = await fetch(`${indexUrl}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        return res.json();
    },
    async submitScore(scoreUrl, csrf, sets, status) { },
    async deleteMatch(deleteUrl, csrf) { },
    async updateSchedule(url, csrf, matchDate, matchTime) { },
    async generateMatches(createGroupsUrl, csrf, categoryId, bestOf) { }
};
```

### AJAX Headers
- `'X-CSRF-TOKEN'` for Laravel CSRF protection
- `'X-Requested-With': 'XMLHttpRequest'` for XHR identification
- `'Content-Type': 'application/json'`
- `'Accept': 'application/json'`

---

## 4. Common UI Components

### Cards
- `.club-avatar` with verified badge
- `.booking-card` for form sections
- `.event-card` with date badge
- `.td-card` for dashboard cards
- `.td-stat-card` for statistics

**Pattern:** White background, `border-radius: var(--radius-lg)`, `box-shadow: var(--shadow-md)`

### Tables
- Step indicators (booking flow)
- Athlete lists with tabs
- Tournament rankings tables
- Status badge system

**Classes:**
- `.step-indicator` / `.step`
- `.tab` / `.tab.active`
- `.td-status` with status variants

### Modals
- `.modal-overlay` with backdrop
- `.modal-content` centered container
- `.modal-header` / `.modal-body` / `.modal-footer`
- Click-away to close pattern

**Pattern:**
```html
<div class="modal-overlay" :class="{ 'active': showModal }" @click.self="showModal = false">
    <div class="modal-content">
        <button class="modal-close" @click="showModal = false"></button>
    </div>
</div>
```

### Filter/Tab Systems
- `.at-filter-bar` for status tabs (all, pending, approved, rejected)
- `.filter-select` for dropdown filters
- Tab counter pattern: `Tab Name (<span x-text="count"></span>)`

### Header & Navigation
- Fixed header with scroll effect (`.header.scrolled`)
- Mobile menu toggle (`.mobile-menu-toggle`)
- Dropdown menus with arrow expansion
- User profile dropdown (`.user-dropdown-container`)

### Forms
- `.form-group` for field containers
- `.form-label` for labels
- `.form-select` / `.form-input` patterns
- Inline validation with `.form-errors` display

### Loading States
- `.loading-spinner` with SVG animation
- Template conditionals: `<template x-if="loading">`
- Button disabled states

### Status Badges
- `.td-status` (`.td-status-active`, `.td-status-pending`, etc.)
- Color coding: green (active), yellow (pending), red (cancelled)
- Small font with rounded pill shape

---

## 5. Layout Structure

### Standard Page Layout
```
<header class="header">
    Navigation
</header>

<main>
    <section class="page-section">
        <div class="container">
            Content
        </div>
    </section>
</main>

<footer class="footer">
    Footer content
</footer>
```

### Dashboard Layout (Tournament Dashboard)
```
.td-layout (flex column)
├── .td-sidebar (240px, hidden on mobile)
│   ├── .td-sidebar-header
│   ├── .td-progress-bar
│   └── .td-nav (navigation items)
└── .td-content (main content area)
    ├── .td-card (repeating sections)
    ├── .td-stats-grid (2 columns)
    └── Tables/Lists
```

### Mobile-facing Layouts
- Sidebar hidden at 1024px breakpoint
- Single-column content
- Full-width cards
- Sticky headers
- Bottom action buttons

---

## 6. Typography & Text Patterns

- **Font:** Inter (fallback system fonts)
- **Base size:** 1rem (16px)
- **Line height:** 1.6
- **Text truncation:** `white-space: nowrap; overflow: hidden; text-overflow: ellipsis;`
- **Smoothing:** `scroll-behavior: smooth`

**Hierarchy:**
- Headings: Use `--font-size-3xl` to `--font-size-5xl`
- Body text: `--font-size-base` (1rem)
- Labels: `--font-size-sm` to `--font-size-lg`
- Small text: `--font-size-xs`

---

## 7. Color & Visibility Patterns

### Status Colors
- **Primary/Success:** `#00D9B5` (turquoise)
- **Secondary/Info:** `#0099CC` (blue)
- **Warning:** `#FFD93D` (yellow)
- **Danger:** `#FF6B6B` (red)
- **Neutral/Disabled:** `#E5E5E5` (light gray)

### Dark Mode Consideration
- `--bg-dark: #1A1A1A` defined but not widely used
- Current design is light-mode primary
- No dark mode toggle observed

---

## 8. Transition & Animation Patterns

**Defined transitions:**
```css
--transition-fast: 150ms ease-in-out
--transition-base: 300ms ease-in-out
--transition-slow: 500ms ease-in-out
```

**Applied to:**
- Hover states on links and buttons
- Modal open/close
- Navigation menu collapse/expand
- Form input focus states
- Color changes on status badges

**Special animations:**
- Mobile menu: cubic-bezier(0.4, 0, 0.2, 1) easing
- Spinner: SVG rotation animation
- Progress bars: width transition

---

## 9. Form & Input Patterns

**Input styling:**
- Border: `1px solid var(--border-color)`
- Border-radius: `var(--radius-md)` to `var(--radius-lg)`
- Padding: `var(--spacing-sm)` to `var(--spacing-md)`
- Focus states typically use primary color or outline

**Validation:**
- Error display: separate `formErrors: {}` object
- Error messages: red text or alert styling
- Field-level error classes: optional

**File inputs:**
- Hidden native inputs with label click triggers
- Preview images/videos in grid
- Remove buttons on each preview item

---

## 10. Key Implementation Details

### Alpine.js Integration with Blade
- Config passed via `x-data="function(config)"` with Laravel route helpers
- JSON encoding of backend data: `{{ $athletesJson }}`
- CSRF token from meta tag: `document.querySelector('meta[name=csrf-token]').content`

### Event Handling Strategy
- Form submissions use `@submit.prevent` to avoid page reload
- Click handlers delegated to Alpine methods
- Modal backdrop uses `@click.self` to prevent child click bubbling
- Dropdown close uses `@click.away` pattern

### Search/Filter Pattern
```javascript
// Text search with debounce consideration (not explicitly shown)
x-model="searchQuery"
// Category/status filtering with immediate effect
@change="filterEvents()"
// Computed filtered list
:for="item in filtered"
```

### Template Rendering
- `<template x-if="condition">` for conditional rendering (removes DOM)
- `<template x-for="item in array" :key="item.id">` for loops
- `<template x-if="status === 'value'">` for status-based rendering
- Key binding with `:key="item.id"` for proper list diffing

---

## 11. Files Organization Summary

**CSS Files:**
- `/public/assets/css/style.css` - Main design system & base styles
- `/public/assets/css/styles.css` - Alternative main styles (duplicate variables)
- `/public/assets/css/booking.css` - Booking flow specific
- `/public/assets/css/tournaments.css` - Tournament cards & pages
- `/public/assets/css/styles-*.css` - Feature-specific (club, courses, coaches, etc.)
- `/public/assets/css/tournament-dashboard/*.css` - Modular dashboard components

**JavaScript Files:**
- `/public/assets/js/script.js` - Vanilla JS (menu toggle, scroll effects)
- `/public/assets/js/tournament-*.js` - Alpine.js mixins & components
- `/public/assets/js/bracket-*.js` - Bracket/match management
- `/public/assets/js/*-api.js` - Fetch helper modules

**Blade Templates:**
- `resources/views/layouts/front.blade.php` - Main layout with CSS/JS includes
- `resources/views/home-yard/tournaments/*.blade.php` - Tournament management
- `resources/views/clubs/*.blade.php` - Club features
- `resources/views/front/**/*.blade.php` - Front-facing pages

---

## Summary Table

| Aspect | Pattern |
|--------|---------|
| **CSS Naming** | BEM-inspired, kebab-case with feature prefixes (td-, at-, etc.) |
| **Design System** | CSS variables for colors, spacing, typography, shadows, transitions |
| **Breakpoints** | 1024px (tablet), 768px (portrait), 480px (mobile) |
| **Primary Color** | Turquoise (#00D9B5) |
| **Responsive** | Mobile-first, grid/flex layouts |
| **Alpine Pattern** | Function factories returning x-data objects with state, computed, methods |
| **State Management** | Local component state, no global store |
| **AJAX** | fetch() with JSON, CSRF headers, helper modules (MatchesApi) |
| **Forms** | Alpine x-model binding, @submit.prevent, validation errors object |
| **Navigation** | Hamburger menu on mobile, dropdown menus with arrow expansion |
| **Card System** | White bg, shadow-md, radius-lg, consistent padding |
| **Status Display** | Badges with color coding, tab counters |
| **Fonts** | Inter system font stack |
| **Transitions** | 150ms/300ms/500ms ease-in-out |

