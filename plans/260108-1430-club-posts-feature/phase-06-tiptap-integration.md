# Phase 06: Tiptap Editor Integration

**Priority:** High
**Status:** Pending
**Depends on:** Phase 05

---

## Context

Integrate Tiptap rich text editor for post content. Using CDN approach to minimize build complexity. Features needed:
- Basic formatting (bold, italic, strikethrough)
- Links
- Lists (bullet, numbered)
- Placeholder text

---

## Related Files

**Modify:**
- `resources/views/clubs/posts/_create-modal.blade.php` - Replace contenteditable div
- `resources/views/layouts/front.blade.php` - Add Tiptap CDN (if not using separate stack)

**Create:**
- `resources/views/clubs/posts/_editor.blade.php` - Tiptap component

---

## Implementation Steps

### Step 1: Create Editor Component

```blade
{{-- resources/views/clubs/posts/_editor.blade.php --}}
<div x-data="tiptapEditor()" x-init="initEditor()">
    <!-- Editor Toolbar -->
    <div class="editor-toolbar">
        <button type="button" @click="toggleBold()" :class="{ 'active': isBold }" title="Dam (Ctrl+B)">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/>
                <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/>
            </svg>
        </button>
        <button type="button" @click="toggleItalic()" :class="{ 'active': isItalic }" title="Nghieng (Ctrl+I)">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="4" x2="10" y2="4"/>
                <line x1="14" y1="20" x2="5" y2="20"/>
                <line x1="15" y1="4" x2="9" y2="20"/>
            </svg>
        </button>
        <button type="button" @click="toggleStrike()" :class="{ 'active': isStrike }" title="Gach ngang">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.3 4.9c-2.3-.6-4.4-1-6.2-.9-2.7 0-5.3.7-5.3 3.6 0 1.5 1.8 3.3 7.6 3.3"/>
                <path d="M8.5 15c.6.9 1.8 1.7 3.5 1.7 3.1 0 5.3-1.4 5.3-3.7"/>
                <path d="M3 12h18"/>
            </svg>
        </button>
        <span class="toolbar-divider"></span>
        <button type="button" @click="toggleBulletList()" :class="{ 'active': isBulletList }" title="Danh sach">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/>
                <line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/>
                <circle cx="4" cy="6" r="1" fill="currentColor"/>
                <circle cx="4" cy="12" r="1" fill="currentColor"/>
                <circle cx="4" cy="18" r="1" fill="currentColor"/>
            </svg>
        </button>
        <button type="button" @click="toggleOrderedList()" :class="{ 'active': isOrderedList }" title="Danh sach so">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="10" y1="6" x2="21" y2="6"/>
                <line x1="10" y1="12" x2="21" y2="12"/>
                <line x1="10" y1="18" x2="21" y2="18"/>
                <text x="4" y="8" font-size="8" fill="currentColor">1</text>
                <text x="4" y="14" font-size="8" fill="currentColor">2</text>
                <text x="4" y="20" font-size="8" fill="currentColor">3</text>
            </svg>
        </button>
        <span class="toolbar-divider"></span>
        <button type="button" @click="setLink()" :class="{ 'active': isLink }" title="Lien ket">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
            </svg>
        </button>
    </div>

    <!-- Editor Content -->
    <div id="editor-content" class="editor-content" x-ref="editorContent"></div>
</div>

<style>
.editor-toolbar {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-light);
    border-radius: var(--radius-md) var(--radius-md) 0 0;
}

.editor-toolbar button {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-base);
}

.editor-toolbar button:hover {
    background: var(--border-color);
    color: var(--text-primary);
}

.editor-toolbar button.active {
    background: rgba(0, 217, 181, 0.15);
    color: var(--primary-color);
}

.toolbar-divider {
    width: 1px;
    height: 24px;
    background: var(--border-color);
    margin: 0 4px;
}

.editor-content {
    min-height: 120px;
    max-height: 400px;
    overflow-y: auto;
    padding: var(--spacing-md);
    font-size: var(--font-size-base);
    line-height: 1.6;
}

.editor-content:focus {
    outline: none;
}

.editor-content p {
    margin: 0 0 var(--spacing-sm);
}

.editor-content p:last-child {
    margin-bottom: 0;
}

.editor-content ul,
.editor-content ol {
    padding-left: 1.5rem;
    margin: var(--spacing-sm) 0;
}

.editor-content a {
    color: var(--primary-color);
    text-decoration: underline;
}

/* Placeholder */
.editor-content.is-empty:before {
    content: attr(data-placeholder);
    color: var(--text-light);
    position: absolute;
    pointer-events: none;
}

.editor-content:focus.is-empty:before {
    color: var(--text-secondary);
}
</style>
```

### Step 2: Add Tiptap CDN Scripts

Add to `_scripts.blade.php` or inline:

```blade
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2.1.13/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.1.13/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-link@2.1.13/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-placeholder@2.1.13/dist/index.umd.min.js"></script>

<script>
function tiptapEditor() {
    return {
        editor: null,
        isBold: false,
        isItalic: false,
        isStrike: false,
        isBulletList: false,
        isOrderedList: false,
        isLink: false,

        initEditor() {
            const { Editor } = window.TiptapCore;
            const StarterKit = window.TiptapStarterKit.StarterKit;
            const Link = window.TiptapLink.Link;
            const Placeholder = window.TiptapPlaceholder.Placeholder;

            this.editor = new Editor({
                element: this.$refs.editorContent,
                extensions: [
                    StarterKit.configure({
                        heading: false,
                        codeBlock: false,
                        code: false,
                        blockquote: false,
                        horizontalRule: false,
                    }),
                    Link.configure({
                        openOnClick: false,
                        HTMLAttributes: {
                            target: '_blank',
                            rel: 'noopener noreferrer',
                        },
                    }),
                    Placeholder.configure({
                        placeholder: 'Ban dang nghi gi ve Pickleball?',
                    }),
                ],
                content: this.$parent?.content || '',
                onUpdate: ({ editor }) => {
                    // Sync content to parent Alpine component
                    if (this.$parent) {
                        this.$parent.content = editor.getHTML();
                    }
                    this.updateActiveStates();
                },
                onSelectionUpdate: () => {
                    this.updateActiveStates();
                },
            });

            // Handle placeholder class
            this.updatePlaceholderClass();
        },

        updateActiveStates() {
            if (!this.editor) return;
            this.isBold = this.editor.isActive('bold');
            this.isItalic = this.editor.isActive('italic');
            this.isStrike = this.editor.isActive('strike');
            this.isBulletList = this.editor.isActive('bulletList');
            this.isOrderedList = this.editor.isActive('orderedList');
            this.isLink = this.editor.isActive('link');
        },

        updatePlaceholderClass() {
            const element = this.$refs.editorContent;
            if (!element) return;
            const isEmpty = this.editor.isEmpty;
            element.classList.toggle('is-empty', isEmpty);
        },

        toggleBold() {
            this.editor.chain().focus().toggleBold().run();
        },

        toggleItalic() {
            this.editor.chain().focus().toggleItalic().run();
        },

        toggleStrike() {
            this.editor.chain().focus().toggleStrike().run();
        },

        toggleBulletList() {
            this.editor.chain().focus().toggleBulletList().run();
        },

        toggleOrderedList() {
            this.editor.chain().focus().toggleOrderedList().run();
        },

        setLink() {
            if (this.editor.isActive('link')) {
                this.editor.chain().focus().unsetLink().run();
                return;
            }

            const url = prompt('Nhap URL:');
            if (url) {
                this.editor.chain().focus().setLink({ href: url }).run();
            }
        },

        setContent(html) {
            if (this.editor) {
                this.editor.commands.setContent(html);
            }
        },

        getContent() {
            return this.editor ? this.editor.getHTML() : '';
        },

        clearContent() {
            if (this.editor) {
                this.editor.commands.clearContent();
            }
        },

        destroy() {
            if (this.editor) {
                this.editor.destroy();
            }
        }
    };
}
</script>
```

### Step 3: Update Create Modal to Use Editor

Replace the contenteditable div in `_create-modal.blade.php`:

```blade
<!-- Old -->
<div id="editor" class="post-editor" contenteditable="true" ...></div>

<!-- New -->
@include('clubs.posts._editor')
```

### Step 4: Update Submit Logic

Modify the `submitPost` function in `_scripts.blade.php` to get content from Tiptap:

```javascript
async submitPost() {
    // Get content from Tiptap editor if available
    const editorComponent = document.querySelector('[x-data*="tiptapEditor"]');
    if (editorComponent && editorComponent.__x) {
        this.content = editorComponent.__x.$data.getContent();
    }

    if (!this.content.trim() || this.submitting) return;
    // ... rest of function
}
```

### Step 5: Handle Edit Mode

When editing a post, set the editor content:

```javascript
editPost(post) {
    this.editingPost = post;
    this.content = post.content;
    this.visibility = post.visibility;
    this.openModal = true;

    // Wait for modal to open, then set editor content
    this.$nextTick(() => {
        const editorComponent = document.querySelector('[x-data*="tiptapEditor"]');
        if (editorComponent && editorComponent.__x) {
            editorComponent.__x.$data.setContent(post.content);
        }
    });
}
```

### Step 6: Clear Editor on Modal Close

```javascript
closeModal() {
    this.openModal = false;
    this.editingPost = null;
    this.content = '';
    // ... rest

    // Clear editor
    const editorComponent = document.querySelector('[x-data*="tiptapEditor"]');
    if (editorComponent && editorComponent.__x) {
        editorComponent.__x.$data.clearContent();
    }
}
```

---

## Alternative: HTMLPurifier for Server-Side Sanitization

If Tiptap integration is complex, ensure server-side sanitization with HTMLPurifier:

### Install Package

```bash
composer require mews/purifier
```

### Configuration

```bash
php artisan vendor:publish --provider="Mews\Purifier\PurifierServiceProvider"
```

### Update config/purifier.php

```php
'club_posts' => [
    'HTML.Allowed' => 'p,br,strong,em,s,a[href|target],ul,ol,li',
    'AutoFormat.RemoveEmpty' => true,
    'HTML.TargetBlank' => true,
],
```

### Use in Controller

```php
use Mews\Purifier\Facades\Purifier;

private function sanitizeContent(string $content): string
{
    return Purifier::clean($content, 'club_posts');
}
```

---

## Todo List

- [ ] Create _editor.blade.php component
- [ ] Add Tiptap CDN scripts
- [ ] Create tiptapEditor Alpine component
- [ ] Update _create-modal.blade.php to use editor
- [ ] Update submit/edit logic for Tiptap
- [ ] Install mews/purifier for server-side sanitization
- [ ] Configure purifier for allowed tags
- [ ] Test editor formatting (bold, italic, lists, links)
- [ ] Test content saves correctly
- [ ] Test edit mode loads existing content

---

## Success Criteria

- [ ] Editor renders with toolbar
- [ ] Bold/italic/strike toggles work
- [ ] Lists (bullet/ordered) work
- [ ] Links can be added/removed
- [ ] Content syncs to form
- [ ] Existing content loads when editing
- [ ] Server sanitizes HTML properly

---

## Fallback Option

If CDN approach fails, use simple contenteditable with basic buttons (no Tiptap). The server-side sanitization will still clean the HTML.

---

## Next Steps

Proceed to Phase 07: Testing & Polish
