# Auto-post Activity to Bảng tin (News Feed)

## Overview
When a club activity is created, automatically create a corresponding post in "Bảng tin" tab so club members can see it in their feed without switching tabs.

## Status: READY

## Architecture

**Approach:** Add a nullable `club_activity_id` FK to `club_posts` table. After creating a ClubActivity, auto-create a ClubPost with formatted activity details and a link to the activity detail page.

**Flow:**
1. Manager creates activity (ClubActivityController::store)
2. System auto-creates ClubPost linked to the activity
3. Post appears in Bảng tin feed with activity card styling
4. If activity is deleted, the linked post is also hard-deleted

## Phases

| # | Phase | Status | Effort |
|---|-------|--------|--------|
| 1 | [DB Migration](phase-01-migration.md) | TODO | S |
| 2 | [Auto-post Logic](phase-02-auto-post-logic.md) | TODO | M |
| 3 | [Feed UI - Activity Card](phase-03-feed-ui.md) | TODO | M |

## Key Files
- `app/Http/Controllers/ClubActivityController.php` - store/destroy methods
- `app/Models/ClubPost.php` - add relationship
- `app/Models/ClubActivity.php` - add relationship
- `resources/views/clubs/posts/_feed.blade.php` - activity card in feed
- `resources/views/clubs/posts/_scripts.blade.php` - Alpine.js rendering

## Dependencies
- None (builds on existing activity + post systems)

## Validation Log

### Session 1 — 2026-03-06
**Trigger:** Initial plan validation
**Questions asked:** 3

#### Questions & Answers

1. **[Architecture]** Khi activity bi xoa, post lien ket trong Bang tin nen xu ly nhu nao?
   - Options: Soft-delete post | Giu post, xoa lien ket (nullOnDelete)
   - **Answer:** Hard-delete post (updated per user request)
   - **Rationale:** Keep feed clean, no orphan posts. Hard delete for simplicity.

2. **[Sync]** Khi activity duoc cap nhat (doi title, ngay, dia diem), post trong Bang tin co tu dong cap nhat theo khong?
   - Options: Co, tu dong sync | Khong, giu nguyen post cu
   - **Answer:** Co, tu dong sync
   - **Rationale:** Ensures consistent info. Need to add sync logic in ClubActivityController::update.

3. **[Scope]** Post tu dong tao tu activity co cho phep user chinh sua/xoa thu cong trong Bang tin khong?
   - Options: Khong cho sua/xoa | Cho phep sua/xoa binh thuong
   - **Answer:** Khong cho sua/xoa (read-only)
   - **Rationale:** Prevents data inconsistency. Hide edit/delete buttons for activity-linked posts in feed UI.

#### Confirmed Decisions
- Delete behavior: Hard-delete linked post when activity deleted
- Update sync: Auto-update post content when activity is updated
- Editability: Activity posts are read-only in feed (no manual edit/delete)

#### Action Items
- [ ] Phase 1: Change nullOnDelete to manual soft-delete approach (keep FK reference)
- [ ] Phase 2: Add sync logic in update() method
- [ ] Phase 3: Hide edit/delete buttons for activity-linked posts

#### Impact on Phases
- Phase 1: Use `cascadeOnDelete` on FK so post auto-deletes when activity deleted
- Phase 2: Add update sync logic in `ClubActivityController::update`. Load `post` relationship before updating content.
- Phase 3: Add condition to hide edit/delete menu for posts where `club_activity_id` is not null
