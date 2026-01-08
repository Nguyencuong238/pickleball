# Phase 07: Testing & Polish

**Priority:** Normal
**Status:** Pending
**Depends on:** Phase 06

---

## Context

Final phase to test all features, fix bugs, and polish UX. Ensure permissions work correctly and UI is responsive.

---

## Testing Checklist

### Database & Migrations

- [ ] All migrations run without error
- [ ] Rollback works correctly
- [ ] Foreign key constraints work (cascade delete)
- [ ] Indexes exist for performance

### Models & Relationships

- [ ] ClubPost -> Club relationship works
- [ ] ClubPost -> User (author) relationship works
- [ ] ClubPost -> Media relationship works
- [ ] ClubPost -> Reactions relationship works
- [ ] ClubPost -> Comments relationship works
- [ ] ClubPostComment -> Replies (self) relationship works
- [ ] Scopes (pinned, public, feed) work correctly

### Authorization (Policies)

Test with different users:

**Creator/Admin:**
- [ ] Can create posts
- [ ] Can edit any post
- [ ] Can delete any post
- [ ] Can pin/unpin posts
- [ ] Can react to posts
- [ ] Can comment on posts
- [ ] Can delete any comment

**Moderator:**
- [ ] Can create posts
- [ ] Can edit own posts only
- [ ] Cannot edit others' posts
- [ ] Can delete any post
- [ ] Cannot pin posts
- [ ] Can react and comment
- [ ] Can delete any comment

**Member:**
- [ ] Cannot create posts
- [ ] Cannot edit any posts
- [ ] Cannot delete any posts
- [ ] Cannot pin posts
- [ ] Can react to posts
- [ ] Can comment on posts
- [ ] Can only delete own comments

**Guest (not logged in):**
- [ ] Can view public posts
- [ ] Cannot view members_only posts
- [ ] Cannot react
- [ ] Cannot comment

### Controllers & API

**ClubPostController:**
- [ ] GET /clubs/{club}/posts - lists posts with pagination
- [ ] POST /clubs/{club}/posts - creates post with media
- [ ] PUT /clubs/{club}/posts/{post} - updates post
- [ ] DELETE /clubs/{club}/posts/{post} - soft deletes
- [ ] POST /clubs/{club}/posts/{post}/pin - toggles pin

**ClubPostReactionController:**
- [ ] POST /club-posts/{post}/reactions - adds/changes reaction
- [ ] DELETE /club-posts/{post}/reactions - removes reaction
- [ ] Same reaction type = removes
- [ ] Different reaction type = changes

**ClubPostCommentController:**
- [ ] GET /club-posts/{post}/comments - lists with replies
- [ ] POST /club-posts/{post}/comments - creates comment
- [ ] POST with parent_id = creates reply
- [ ] PUT /club-post-comments/{comment} - updates
- [ ] DELETE /club-post-comments/{comment} - soft deletes
- [ ] Only 1 level of replies allowed

### Media Handling

**Images:**
- [ ] Upload up to 10 images
- [ ] Validates mime types (jpg, png, gif, webp)
- [ ] Validates max size (5MB each)
- [ ] Stores in correct path
- [ ] Displays in post

**Video:**
- [ ] Upload 1 video
- [ ] Validates mime types (mp4, mov, webm)
- [ ] Validates max size (50MB)
- [ ] Stores and plays correctly

**YouTube:**
- [ ] Validates YouTube URL format
- [ ] Extracts embed URL correctly
- [ ] Displays embedded player

### Frontend UX

**Club Page:**
- [ ] New design loads correctly
- [ ] Cover image displays
- [ ] Stats (members, events) show correctly
- [ ] Tabs work (Timeline, About, Events, Members)
- [ ] Sidebar shows management team
- [ ] Sidebar shows members grid
- [ ] Sidebar shows upcoming events

**Post Feed:**
- [ ] Posts load on page load
- [ ] Infinite scroll works
- [ ] Pinned posts appear first
- [ ] Post author info displays
- [ ] Post content renders HTML correctly
- [ ] Media displays (images, video, youtube)
- [ ] Reaction counts display
- [ ] Comment count displays

**Create Post:**
- [ ] Create card shows for management
- [ ] Create card hidden for members
- [ ] Modal opens on click
- [ ] Editor accepts input
- [ ] Visibility selector works
- [ ] Image upload preview works
- [ ] Video upload preview works
- [ ] YouTube URL preview works
- [ ] Submit creates post
- [ ] Post appears in feed immediately
- [ ] Modal closes and resets

**Edit Post:**
- [ ] Edit button appears for authorized users
- [ ] Modal opens with existing content
- [ ] Visibility pre-selected
- [ ] Existing media shown
- [ ] Can remove existing media
- [ ] Can add new media
- [ ] Submit updates post
- [ ] Post updates in feed

**Delete Post:**
- [ ] Delete button appears for authorized users
- [ ] Confirmation prompt shows
- [ ] Post removed from feed on confirm

**Pin Post:**
- [ ] Pin button appears for admin only
- [ ] Toggle works (pin/unpin)
- [ ] Pinned badge shows
- [ ] Pinned posts sort to top

**Reactions:**
- [ ] Hover shows reaction picker
- [ ] Click adds reaction
- [ ] Click same removes reaction
- [ ] Click different changes reaction
- [ ] Counts update immediately
- [ ] Active state shows user's reaction

**Comments:**
- [ ] Click "Comment" expands section
- [ ] Comments load via AJAX
- [ ] Comment input works
- [ ] Submit adds comment to list
- [ ] Reply button works
- [ ] Reply shows under parent
- [ ] Delete button for authorized
- [ ] Count updates

### Responsive Design

- [ ] Desktop (>1024px) - two column layout
- [ ] Tablet (768-1024px) - single column, sidebar below
- [ ] Mobile (<768px) - optimized for touch
- [ ] Modal works on mobile
- [ ] Touch gestures work for reactions

### Performance

- [ ] Posts load within 500ms
- [ ] Images lazy load
- [ ] No memory leaks from Alpine components
- [ ] No console errors

---

## Bug Fixes Checklist

Document any bugs found and track fixes:

| Bug | Status | Fix |
|-----|--------|-----|
| - | - | - |

---

## Polish Items

### UI Polish
- [ ] Consistent spacing and alignment
- [ ] Hover states on all interactive elements
- [ ] Loading states during API calls
- [ ] Error states with clear messages
- [ ] Success feedback (toast/flash)
- [ ] Smooth animations (modal open/close)

### Content Polish
- [ ] Vietnamese text without diacritics (consistent with codebase)
- [ ] Clear error messages in Vietnamese
- [ ] Placeholder text appropriate

### Accessibility
- [ ] Keyboard navigation works
- [ ] Focus states visible
- [ ] Alt text on images
- [ ] ARIA labels where needed

---

## Deployment Checklist

Before deploying to production:

- [ ] Run migrations: `php artisan migrate`
- [ ] Clear caches: `php artisan cache:clear && php artisan config:clear && php artisan route:clear`
- [ ] Create storage link: `php artisan storage:link`
- [ ] Set permissions on storage: `chmod -R 775 storage`
- [ ] Build assets if needed: `npm run build`
- [ ] Test on staging environment first

---

## Documentation

- [ ] Update CLAUDE.md if needed
- [ ] Update codebase-summary.md with new models/controllers
- [ ] Document any new patterns used

---

## Success Criteria

- [ ] All checklist items pass
- [ ] No console errors
- [ ] No PHP errors/warnings
- [ ] Feature works end-to-end
- [ ] Responsive on all devices
- [ ] Performance acceptable

---

## Rollback Plan

If issues found in production:

1. Rollback migrations: `php artisan migrate:rollback --step=5`
2. Remove new files if needed
3. Clear caches
4. Restore old show.blade.php from git

---

## Post-Launch Monitoring

- [ ] Monitor error logs for first 24 hours
- [ ] Check database query performance
- [ ] Watch for user-reported issues
- [ ] Address any edge cases found

---

## Future Enhancements (Post-MVP)

Not for initial release but documented for later:

1. **Post Scheduling** - Schedule posts for future publish
2. **Photo Albums** - Organize photos into albums
3. **Mentions** - @mention other members
4. **Hashtags** - Categorize posts
5. **Notifications** - Notify on reactions/comments
6. **Share to Social** - Share posts to Facebook/Zalo
7. **Post Analytics** - View counts, engagement stats
8. **Moderation Queue** - Review flagged content
