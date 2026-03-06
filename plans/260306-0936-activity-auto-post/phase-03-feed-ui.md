# Phase 3: Feed UI - Activity Card

## Priority: MEDIUM | Status: TODO | Effort: M

## Overview
Render activity-linked posts with a special card style in the feed, showing activity details and a "Xem chi tiet" (View details) link.

## Implementation Steps

### 1. Update feed template (_feed.blade.php)

Inside the `<template x-for="post in posts">` loop, add a conditional block for activity posts:

```html
<template x-if="post.club_activity_id && post.activity">
    <div class="activity-card">
        <!-- Activity-specific card with date badge, title, location, link -->
    </div>
</template>
```

The activity card should display:
- Activity type badge (one_off/recurring/competition)
- Activity date with formatted badge
- Title as link to activity detail page
- Location if available
- Participants count / max
- "Xem chi tiet" button linking to `/clubs/{slug}/activities/{activityId}`

### 2. Update ClubPostController::index (Front) to eager-load activity

In `Front/ClubPostController.php`, update the query:

```php
$query = $club->posts()
    ->with(['author', 'media', 'reactions', 'activity'])
    ->withCount('allComments')
    ->feed();
```

### 3. Update Alpine.js postFeed component (_scripts.blade.php)

No JS changes needed if activity data is included in JSON response. The template handles rendering conditionally.

### 4. Add CSS for activity card

Style the `.activity-card` inside the post to differentiate from regular posts - use a colored left border and activity-specific layout.

## Related Files
- `resources/views/clubs/posts/_feed.blade.php` (edit)
- `resources/views/clubs/posts/_scripts.blade.php` (edit - if needed)
- `app/Http/Controllers/Front/ClubPostController.php` (edit: index)
- CSS file for activity card styling

## TODO
- [ ] Add activity eager-loading to post feed query
- [ ] Add activity card template in _feed.blade.php
- [ ] Add CSS styling for activity card
- [ ] Test rendering in feed

<!-- Updated: Validation Session 1 - Hide edit/delete for activity-linked posts (read-only) -->

### 5. Hide edit/delete for activity posts

In the post menu dropdown, add condition to skip edit/delete buttons when `post.club_activity_id` is set:
```html
<template x-if="!post.club_activity_id">
    <!-- existing edit/delete menu items -->
</template>
```

## Success Criteria
- Activity posts show with distinct card styling in Bảng tin
- Card displays: date, title, location, type
- "Xem chi tiet" button links to activity detail page
- Regular posts render unchanged
- Activity posts have no edit/delete buttons (read-only)
