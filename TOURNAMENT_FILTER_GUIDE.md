# Tournament Filter Implementation Guide

## Overview
Complete filtering system for the tournaments page with support for:
- **Search** (Tìm kiếm) - Search tournaments by name
- **Status** (Trạng thái) - Filter by tournament status (Open, Coming Soon, Ongoing, Ended)
- **Location** (Địa điểm) - Filter by location
- **Tournament Rank** (Trình độ) - Filter by difficulty level (Beginner, Intermediate, Advanced, Professional)
- **Date Range** (Thời gian) - Filter by start and end dates
- **Prize Range** (Giải thưởng) - Filter by prize money ranges

## Backend Implementation

### Controller: `app/Http/Controllers/Front/HomeController.php`

The `tournaments()` method now handles:
- **Search filtering**: Searches tournament names
- **Status filtering**: Filters by tournament status
- **Location filtering**: Filters by location
- **Tournament rank filtering**: Filters by difficulty level (beginner, intermediate, advanced, professional)
- **Date range filtering**: Filters tournaments between start and end dates
- **Prize range filtering**: Low (<100M), Mid (100-300M), High (>300M)
- **Sorting**: Multiple sort options (date, prize, name) in both directions
- **Pagination**: Preserves all filter parameters across pages

### Request Parameters

Form submissions use the following parameters:

```
GET /tournaments

Parameters:
- search          : Tournament name search term
- statuses[]      : Array of statuses (open, coming_soon, ongoing, ended)
- location        : Location string
- ranks[]         : Array of ranks (beginner, intermediate, advanced, professional)
- start_date      : Start date (Y-m-d format)
- end_date        : End date (Y-m-d format)
- prize_range     : Prize range (low, mid, high)
- sort            : Sort option (date-asc, date-desc, prize-asc, prize-desc, name-asc, name-desc)
```

## Frontend Implementation

### View: `resources/views/front/tournaments.blade.php`

The filter form is a `<form>` element with:
- Dynamic location dropdown populated from database
- All filter inputs properly bound with form names
- Filter state preservation using `$filters` array from controller
- Sorting dropdown with change handler
- Pagination links that preserve filter parameters

### JavaScript: `public/assets/js/tournaments.js`

Enhanced with:
- `applySortFilter()` function for sorting functionality
- Form submission preservation of all filters
- Mobile filter toggle functionality
- Animation and UI interactions

## Usage

### For Users
1. Select desired filters from the sidebar
2. Click "Áp dụng bộ lọc" (Apply Filters) button
3. Results update with filtering applied
4. Use sorting dropdown to reorder results
5. Click "Xóa bộ lọc" (Clear Filters) to reset all filters
6. Navigate between pages using pagination (filters preserved)

### For Developers

To add a new filter:
1. Add filter input to the view with proper `name` attribute
2. Handle the filter in the controller's `tournaments()` method
3. Add filter state preservation in the view using `$filters` array

Example:
```php
// In controller
if ($request->has('new_filter') && $request->new_filter) {
    $query->where('column_name', $request->new_filter);
}

// In view (preserve state)
value="{{ $filters['new_filter'] ?? '' }}"
```

## Database Considerations

Tournament model fields used:
- `name` - Tournament name
- `status` - Tournament status (active)
- `location` - Location string
- `tournament_rank` - Difficulty level
- `start_date` - Tournament start date
- `end_date` - Tournament end date
- `prizes` - Prize money

## Filtering Logic

### Status Filtering
- **open** (Đang mở đăng ký): start_date > now
- **coming_soon** (Sắp mở): start_date > now + 30 days
- **ongoing** (Đang diễn ra): start_date <= now AND end_date >= now
- **ended** (Đã kết thúc): end_date < now

### Prize Filtering
- **low**: prizes < 100,000,000
- **mid**: prizes >= 100,000,000 AND <= 300,000,000
- **high**: prizes > 300,000,000

### Sorting Options
- date-asc: Start date ascending (nearest first)
- date-desc: Start date descending (farthest first)
- prize-desc: Prize amount descending (highest first)
- prize-asc: Prize amount ascending (lowest first)
- name-asc: Name alphabetically ascending
- name-desc: Name alphabetically descending

## Performance Notes

- Filters are applied server-side using Eloquent ORM
- Pagination uses Laravel's built-in paginator
- Locations are dynamically fetched from unique database values
- Filter counts shown in UI are calculated at page load

## Testing

Test the filters by:
1. Navigating to `/tournaments`
2. Selecting various filter combinations
3. Verifying results update correctly
4. Checking pagination preserves filters
5. Testing sort functionality
6. Verifying clear filters button resets all selections

## Future Enhancements

- AJAX filtering for real-time updates without page reload
- Filter count badges that update based on selections
- Saved filter presets
- Advanced search with operators
- Filter by competition format
- Multi-location filtering
