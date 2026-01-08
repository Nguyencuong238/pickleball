# Club Posts Feature - Final Specification

> **Document Status:** Final Draft
> **Created:** 2026-01-08
> **Ready for Implementation:** Yes

---

## 1. Overview

Tinh nang bai viet cho Club trong he thong Pickleball Platform. Cho phep ban dieu hanh dang bai, thanh vien tuong tac qua reactions va comments.

**Reference UI:** `resources/views/clubs/newpage.blade.php`

---

## 2. Decisions Summary

### 2.1 Permissions

| Role | Trong DB | Post | Edit Own | Edit Any | Delete Own | Delete Any | Pin | Comment | React |
|------|----------|------|----------|----------|------------|------------|-----|---------|-------|
| Creator | `creator` | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] |
| Admin | `admin` | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] | [CHECK] |
| Moderator | `moderator` (new) | [CHECK] | [CHECK] | [X] | [CHECK] | [CHECK] | [X] | [CHECK] | [CHECK] |
| Member | `member` | [X] | - | [X] | - | [X] | [X] | [CHECK] | [CHECK] |

**Note:** "Ban dieu hanh" = Creator + Admin + Moderator (co quyen post)

### 2.2 Technical Stack

| Aspect | Decision |
|--------|----------|
| Frontend | Blade + Alpine.js |
| Rich Text Editor | Tiptap (Bold, Italic, Link, List only) |
| Infinite Scroll | Alpine.js + Intersection Observer |
| Storage | Laravel Filesystem (configurable: local/s3) |

### 2.3 Content & Media

| Aspect | Decision |
|--------|----------|
| Content format | HTML (sanitized) |
| Character limit | 5000 |
| Media type | Exclusive (images OR video OR youtube) |
| Max images | 10 files |
| Max image size | 5MB/file |
| Max video | 1 file |
| Max video size | 50MB |
| Video thumbnail | Skip (browser native) |
| YouTube | Embed URL only, no metadata fetch |

### 2.4 Interactions

| Aspect | Decision |
|--------|----------|
| Reactions | 3 types: like, love, fire |
| Reaction display | Grouped icons + count |
| Comments | 1-level replies |
| Post edit | Always editable + "edited" badge |
| Share | Copy link only |

### 2.5 Data & Access

| Aspect | Decision |
|--------|----------|
| Soft delete | Yes (posts & comments) |
| Media on delete | Keep until force delete |
| Feed algorithm | Weighted (pinned first) + recent |
| Pagination | Infinite scroll |
| Guest access | Can view public posts |
| Visibility options | public, members_only |

### 2.6 Excluded from Scope

| Feature | Status |
|---------|--------|
| Rate limiting | Not needed |
| Report/Flag content | Skip |
| Mention (@user) | Phase 2 |
| Hashtags | Phase 2 |
| Admin panel for posts | Skip (manage from frontend) |
| Real-time updates | Not needed |
| Notifications | Not needed |

---

## 3. Database Schema

### 3.1 Alter: club_members (add moderator role)

```sql
ALTER TABLE club_members
MODIFY COLUMN role ENUM('creator', 'admin', 'moderator', 'member') DEFAULT 'member';
```

### 3.2 New: club_posts

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| club_id | bigint FK | -> clubs |
| user_id | bigint FK | -> users (author) |
| content | text | HTML content (sanitized) |
| visibility | enum | public, members_only |
| is_pinned | boolean | default false |
| pinned_at | timestamp | nullable |
| pinned_by | bigint FK | -> users, nullable |
| edited_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | soft delete |

**Indexes:** `(club_id, is_pinned, created_at)`

### 3.3 New: club_post_media

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| club_post_id | bigint FK | -> club_posts |
| type | enum | image, video, youtube |
| path | string | nullable, for uploads |
| disk | string | local, s3 |
| youtube_url | string | nullable |
| size | int | bytes, nullable |
| order | tinyint | display order |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.4 New: club_post_reactions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| club_post_id | bigint FK | -> club_posts |
| user_id | bigint FK | -> users |
| type | enum | like, love, fire |
| created_at | timestamp | |
| updated_at | timestamp | |

**Unique:** `(club_post_id, user_id)`

### 3.5 New: club_post_comments

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| club_post_id | bigint FK | -> club_posts |
| user_id | bigint FK | -> users |
| parent_id | bigint FK | -> self, nullable (for 1-level replies) |
| content | text | plain text |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | soft delete |

**Index:** `(club_post_id, created_at)`

---

## 4. API Endpoints

### 4.1 Posts

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/clubs/{slug}/posts` | List posts (paginated) | Optional |
| GET | `/clubs/{slug}/posts/{id}` | Single post detail | Optional |
| POST | `/clubs/{slug}/posts` | Create post | Required + Permission |
| PUT | `/clubs/{slug}/posts/{id}` | Update post | Required + Owner/Admin |
| DELETE | `/clubs/{slug}/posts/{id}` | Soft delete post | Required + Permission |
| POST | `/clubs/{slug}/posts/{id}/pin` | Toggle pin | Required + Admin |

### 4.2 Reactions

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/club-posts/{id}/reactions` | Add/change reaction | Required + Member |
| DELETE | `/club-posts/{id}/reactions` | Remove reaction | Required |

### 4.3 Comments

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/club-posts/{id}/comments` | List comments | Optional |
| POST | `/club-posts/{id}/comments` | Add comment | Required + Member |
| PUT | `/club-post-comments/{id}` | Edit comment | Required + Owner |
| DELETE | `/club-post-comments/{id}` | Delete comment | Required + Permission |

---

## 5. File Structure

```
app/
├── Http/
│   ├── Controllers/Front/
│   │   ├── ClubPostController.php
│   │   ├── ClubPostReactionController.php
│   │   └── ClubPostCommentController.php
│   └── Requests/
│       ├── StoreClubPostRequest.php
│       └── UpdateClubPostRequest.php
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
├── xxxx_add_moderator_role_to_club_members.php
├── xxxx_create_club_posts_table.php
├── xxxx_create_club_post_media_table.php
├── xxxx_create_club_post_reactions_table.php
└── xxxx_create_club_post_comments_table.php

resources/
├── views/clubs/
│   ├── show.blade.php (main club page)
│   └── posts/
│       ├── _feed.blade.php
│       ├── _post-card.blade.php
│       ├── _create-modal.blade.php
│       └── _comments.blade.php
├── js/
│   └── components/
│       ├── post-composer.js
│       ├── media-uploader.js
│       ├── post-feed.js
│       └── tiptap-editor.js
└── css/
    └── (extend existing styles-club.css)
```

---

## 6. Configuration

```php
// config/club_posts.php
return [
    'disk' => env('CLUB_POSTS_DISK', 'public'),

    'content' => [
        'max_length' => 5000,
        'allowed_tags' => '<p><br><strong><em><s><a><ul><ol><li>',
    ],

    'images' => [
        'max_count' => 10,
        'max_size' => 5 * 1024, // 5MB in KB
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ],

    'videos' => [
        'max_count' => 1,
        'max_size' => 50 * 1024, // 50MB in KB
        'allowed_mimes' => ['mp4', 'mov', 'webm'],
    ],

    'feed' => [
        'per_page' => 10,
    ],
];
```

---

## 7. Alpine.js Components

### 7.1 Post Composer (create/edit modal)
- Tiptap editor integration
- Media type selector (images/video/youtube - exclusive)
- Image upload: drag-drop, preview, reorder, remove
- Video upload: single file, preview
- YouTube URL input: paste URL, show embed preview
- Visibility selector dropdown
- Submit/Cancel buttons
- Loading state during upload

### 7.2 Media Uploader
- Drag & drop zone
- File browser trigger
- Client-side validation (size, type, count)
- Upload progress indicator
- Preview grid with remove button
- Reorder capability (images only)

### 7.3 Post Feed
- Initial load on page render
- Infinite scroll with Intersection Observer
- Loading skeleton/indicator
- "No more posts" end message
- Optimistic UI updates for reactions

### 7.4 Post Card
- Author info with role badge (Admin/HLV/Thanh vien)
- Verified icon for club official posts
- Content render (sanitized HTML)
- Media display:
  - Images: grid layout (1, 2, or multi)
  - Video: HTML5 player
  - YouTube: iframe embed
- Reaction bar with picker dropdown
- Comment section toggle
- Action buttons: Edit, Delete, Pin (conditional by permission)
- Share button (copy link)
- "Da chinh sua" badge with timestamp
- Relative time display ("2 gio truoc")

### 7.5 Comment Section
- Expandable/collapsible
- Comments list with avatars
- Reply button -> inline reply form
- Nested replies (1-level only)
- Load more comments button
- Delete button (conditional)
- Inline comment input with emoji/photo buttons (photo optional Phase 2)

---

## 8. Security

### 8.1 HTML Sanitization
- Server-side sanitization required
- Use HTMLPurifier or custom sanitizer
- Whitelist tags: `<p>, <br>, <strong>, <em>, <s>, <a>, <ul>, <ol>, <li>`
- Whitelist attributes: `<a href target rel>`
- Force `rel="noopener noreferrer"` on external links
- Block: `<script>, <iframe>, <style>`, all event handlers (`onclick`, `onerror`, etc.)

### 8.2 File Upload Security
- Validate MIME type server-side (don't trust client)
- Validate file size server-side
- Generate unique random filenames
- Store in configured disk (local public or S3)
- Set proper permissions

### 8.3 Authorization
- Policy-based authorization for all actions
- Check club membership status
- Check user role in club
- Verify ownership for edit operations

---

## 9. SEO

For public posts:

- Unique URL: `/clubs/{slug}/posts/{id}`
- Meta title: `{post_excerpt} - {club_name} | Pickleball Vietnam`
- Meta description: First 160 chars of post content
- Open Graph tags for social sharing

---

## 10. Implementation Phases

### Phase 1 - MVP
- [ ] Database migrations (5 files)
- [ ] Models with relationships
- [ ] Policies for authorization
- [ ] Config file
- [ ] ClubPostMediaService
- [ ] Controllers (CRUD operations)
- [ ] Form requests with validation
- [ ] Blade views (feed, post-card, create-modal)
- [ ] Alpine.js components
- [ ] Tiptap editor integration
- [ ] Image upload with preview
- [ ] Single reaction toggle (like)
- [ ] Comments (flat, no replies yet)
- [ ] Pin/Unpin functionality
- [ ] Infinite scroll
- [ ] Basic SEO meta tags
- [ ] Copy link share

### Phase 2 - Enhancement
- [ ] Comment replies (1-level nesting)
- [ ] Video upload
- [ ] YouTube embed
- [ ] Edit post with "edited" badge
- [ ] Multiple reaction types with picker
- [ ] Image lightbox/gallery view

### Phase 3 - Polish
- [ ] Mention @username
- [ ] Hashtags with linking
- [ ] Link preview cards
- [ ] Performance optimization
- [ ] Image lazy loading

---

## 11. UI Components Reference

Based on mockup in `newpage.blade.php`:

### Create Post Card
- User avatar (left)
- Input trigger button ("Ban dang nghi gi ve Pickleball?")
- Quick action buttons: [PHOTO] Anh, [VIDEO] Video, [CALENDAR] Su kien

### Post Card Structure
```
+------------------------------------------+
| [PIN BADGE - if pinned]                  |
+------------------------------------------+
| [AVATAR] Author Name [VERIFIED] [BADGE]  |
|          2 gio truoc * [GLOBE] Cong khai |
|                                    [...] |
+------------------------------------------+
| Post content text...                     |
| With HTML formatting support             |
+------------------------------------------+
| [IMAGE/VIDEO/YOUTUBE MEDIA]              |
+------------------------------------------+
| [LIKE][HEART][FIRE] 234    56 binh luan  |
+------------------------------------------+
| [LIKE] Thich | [COMMENT] Binh luan | [SHARE] Chia se |
+------------------------------------------+
| [COMMENTS SECTION - expandable]          |
+------------------------------------------+
```

### Role Badges
- Admin: Yellow background `#FFD93D`
- HLV (Coach): Blue background `#0099CC`
- Thanh vien: Green background `#00D9B5`

### Reaction Icons
- Like: [THUMBSUP]
- Love: [HEART]
- Fire: [FIRE]

---

## 12. Testing Considerations

### Unit Tests
- Model relationships
- Policy authorization rules
- Media service upload/delete
- Content sanitization

### Feature Tests
- Create post with various media types
- Edit post
- Delete post (soft delete)
- Pin/unpin post
- Add/remove reactions
- Add/delete comments
- Comment replies
- Feed pagination
- Permission checks

### Browser Tests (optional)
- Infinite scroll behavior
- Media upload flow
- Tiptap editor functionality

---

## 13. Performance Considerations

- Eager load relationships in feed query
- Index on `(club_id, is_pinned, created_at)`
- Paginate comments (load more)
- Lazy load images below fold
- Cache reaction counts (optional)

---

## 14. Future Considerations

- Video transcoding for cross-browser support
- CDN integration (CloudFront, Cloudflare)
- Image optimization/resize on upload
- Push notifications
- Post analytics (views, engagement metrics)
- Scheduled posts
- Draft posts

---

## Appendix: Existing Database Reference

### clubs table
- id, user_id, name, slug, description, image, founded_date, objectives, type, status

### club_members table
- id, club_id, user_id, role (creator/admin/member -> add moderator), joined_at

### Related Models
- `App\Models\Club`
- `App\Models\ClubActivity` (different from posts - for events)
- `App\Models\ClubJoinRequest`
