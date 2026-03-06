# Phase 2: Auto-post Logic

## Priority: HIGH | Status: TODO | Effort: M

## Overview
When a ClubActivity is created, auto-create a ClubPost with activity info. When activity is deleted, soft-delete the linked post.

## Implementation Steps

### 1. ClubActivityController::store - Auto-create post after activity creation

After `$activity = $club->activities()->create($validated)`, create a ClubPost:

```php
ClubPost::create([
    'club_id' => $club->id,
    'user_id' => Auth::id(),
    'club_activity_id' => $activity->id,
    'content' => $this->buildActivityPostContent($activity),
    'visibility' => 'public',
]);
```

### 2. Build post content helper

Create `buildActivityPostContent(ClubActivity $activity)` method in controller:

```php
private function buildActivityPostContent(ClubActivity $activity): string
{
    $date = \Carbon\Carbon::parse($activity->activity_date)->format('d/m/Y H:i');
    $location = $activity->location ? "<p>[LOCATION] {$activity->location}</p>" : '';
    $description = $activity->description ? "<p>{$activity->description}</p>" : '';

    return "<p><strong>[CALENDAR] {$activity->title}</strong></p>"
         . "<p>[CLOCK] {$date}</p>"
         . $location
         . $description;
}
```

### 3. ClubActivityController::destroy - Hard-delete linked post

No manual delete needed - `cascadeOnDelete` on FK handles it automatically when activity is hard-deleted.
If ClubPost uses SoftDeletes, need to `forceDelete()` before activity delete:
```php
if ($activity->post) {
    $activity->post->forceDelete();
}
```

<!-- Updated: Validation Session 1 - Add update sync logic, post is read-only -->

### 4. ClubActivityController::update - Sync post content

After `$activity->update($validated)`, sync linked post:
```php
if ($activity->post) {
    $activity->post->update([
        'content' => $this->buildActivityPostContent($activity->fresh()),
    ]);
}
```

## Related Files
- `app/Http/Controllers/ClubActivityController.php` (edit: store, update, destroy)

## TODO
- [ ] Add `buildActivityPostContent()` method
- [ ] Add auto-create post in `store()` method
- [ ] Add sync post content in `update()` method
- [ ] Add auto-delete post in `destroy()` method
- [ ] Import ClubPost model

## Success Criteria
- Creating an activity auto-creates a post in Bảng tin
- Updating an activity auto-syncs the linked post content
- Deleting an activity hard-deletes the linked post
- Post content includes activity title, date, location
