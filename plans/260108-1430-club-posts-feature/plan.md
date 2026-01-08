# Club Posts Feature - Implementation Plan

**Date:** 2026-01-08
**Status:** Implementation Complete
**Spec Reference:** `docs/club-posts-feature-spec.md`
**UI Reference:** `resources/views/clubs/newpage.blade.php`

---

## Overview

Implement Club Posts feature for `/clubs/{slug}` page - a Facebook-style feed system allowing club management to post announcements and members to interact via reactions and comments.

---

## Phases Summary

| Phase | Name | Status | Priority |
|-------|------|--------|----------|
| 01 | Database Schema | Completed | Critical |
| 02 | Backend Models & Relationships | Completed | Critical |
| 03 | Authorization Policies | Completed | Critical |
| 04 | Core Controllers & Services | Completed | Critical |
| 05 | Frontend Views & Alpine.js | Completed | Critical |
| 06 | Tiptap Editor Integration | Completed | High |
| 07 | Testing & Polish | Completed | Normal |

---

## Phase Details

- [Phase 01: Database Schema](./phase-01-database-schema.md)
- [Phase 02: Models & Relationships](./phase-02-models-relationships.md)
- [Phase 03: Authorization Policies](./phase-03-authorization.md)
- [Phase 04: Controllers & Services](./phase-04-controllers-services.md)
- [Phase 05: Frontend Views](./phase-05-frontend-views.md)
- [Phase 06: Tiptap Integration](./phase-06-tiptap-integration.md)
- [Phase 07: Testing & Polish](./phase-07-testing-polish.md)

---

## Key Decisions

1. **Follow existing patterns** - VideoComment/VideoLike models as reference
2. **Service layer** - ClubPostMediaService for media handling (follows ProfileService pattern)
3. **Alpine.js** - Inline components in Blade (no separate JS files per codebase pattern)
4. **Tiptap** - CDN-based integration (minimal build complexity)
5. **HTMLPurifier** - For content sanitization via composer package

---

## Dependencies

- mews/purifier (composer) - HTML sanitization
- @tiptap/core (CDN) - Rich text editor

---

## Critical Files to Create

```
app/
├── Http/Controllers/Front/
│   ├── ClubPostController.php
│   ├── ClubPostReactionController.php
│   └── ClubPostCommentController.php
├── Http/Requests/
│   ├── StoreClubPostRequest.php
│   └── UpdateClubPostRequest.php
├── Models/
│   ├── ClubPost.php
│   ├── ClubPostMedia.php
│   ├── ClubPostReaction.php
│   └── ClubPostComment.php
├── Policies/
│   ├── ClubPostPolicy.php
│   └── ClubPostCommentPolicy.php
├── Services/
│   └── ClubPostMediaService.php

config/
└── club_posts.php

database/migrations/
├── 2026_01_08_000001_add_moderator_role_to_club_members.php
├── 2026_01_08_000002_create_club_posts_table.php
├── 2026_01_08_000003_create_club_post_media_table.php
├── 2026_01_08_000004_create_club_post_reactions_table.php
└── 2026_01_08_000005_create_club_post_comments_table.php

resources/views/clubs/
├── show.blade.php (replace with newpage design)
└── posts/
    ├── _feed.blade.php
    ├── _post-card.blade.php
    ├── _create-modal.blade.php
    └── _comments.blade.php
```

---

## Critical Files to Modify

```
app/Http/Controllers/ClubController.php  # Update show() method
app/Models/Club.php                       # Add posts() relationship
routes/web.php                            # Add post routes
public/assets/css/styles-club.css         # Already has styles
```

---

## Unresolved Questions

None - spec is comprehensive and patterns are clear from codebase analysis.
