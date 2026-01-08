{{-- Tiptap Rich Text Editor Component --}}
<div class="tiptap-editor-wrapper">
    {{-- Editor Toolbar --}}
    <div class="editor-toolbar">
        <button type="button" @click="toggleBold()" :class="{ 'active': isBold }" title="Đậm (Ctrl+B)">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/>
                <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/>
            </svg>
        </button>
        <button type="button" @click="toggleItalic()" :class="{ 'active': isItalic }" title="Nghiêng (Ctrl+I)">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="4" x2="10" y2="4"/>
                <line x1="14" y1="20" x2="5" y2="20"/>
                <line x1="15" y1="4" x2="9" y2="20"/>
            </svg>
        </button>
        <button type="button" @click="toggleStrike()" :class="{ 'active': isStrike }" title="Gạch ngang">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.3 4.9c-2.3-.6-4.4-1-6.2-.9-2.7 0-5.3.7-5.3 3.6 0 1.5 1.8 3.3 7.6 3.3"/>
                <path d="M8.5 15c.6.9 1.8 1.7 3.5 1.7 3.1 0 5.3-1.4 5.3-3.7"/>
                <path d="M3 12h18"/>
            </svg>
        </button>
        <span class="toolbar-divider"></span>
        <button type="button" @click="toggleBulletList()" :class="{ 'active': isBulletList }" title="Danh sách">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/>
                <line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/>
                <circle cx="4" cy="6" r="1" fill="currentColor"/>
                <circle cx="4" cy="12" r="1" fill="currentColor"/>
                <circle cx="4" cy="18" r="1" fill="currentColor"/>
            </svg>
        </button>
        <button type="button" @click="toggleOrderedList()" :class="{ 'active': isOrderedList }" title="Danh sách số">
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
        <button type="button" @click="setLink()" :class="{ 'active': isLink }" title="Liên kết">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
            </svg>
        </button>
    </div>

    {{-- Editor Content --}}
    <div x-ref="editorContent" class="editor-content" data-placeholder="Bạn đang nghĩ gì về Pickleball?"></div>
</div>

<style>
.tiptap-editor-wrapper {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    background: var(--bg-color);
}

.editor-toolbar {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-light);
    flex-wrap: wrap;
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
    min-height: 80px;
    max-height: 200px;
    overflow-y: auto;
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: var(--font-size-base);
    line-height: 1.6;
    position: relative;
}

.editor-content:focus {
    outline: none;
}

.editor-content .ProseMirror {
    outline: none;
    min-height: 60px;
}

.editor-content .ProseMirror p {
    margin: 0 0 var(--spacing-sm);
}

.editor-content .ProseMirror p:last-child {
    margin-bottom: 0;
}

.editor-content .ProseMirror ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: var(--spacing-sm) 0;
}

.editor-content .ProseMirror ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin: var(--spacing-sm) 0;
}

.editor-content .ProseMirror li {
    margin: 0.25em 0;
}

.editor-content .ProseMirror li p {
    margin: 0;
}

.editor-content .ProseMirror strong {
    font-weight: 700;
}

.editor-content .ProseMirror em {
    font-style: italic;
}

.editor-content .ProseMirror s {
    text-decoration: line-through;
}

.editor-content .ProseMirror a {
    color: var(--primary-color);
    text-decoration: underline;
}

/* Placeholder */
.editor-content .ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    color: var(--text-light);
    position: absolute;
    pointer-events: none;
}

.editor-content .ProseMirror-focused p.is-editor-empty:first-child::before {
    color: var(--text-secondary);
}
</style>
