/**
 * tournament-draw.js
 * Alpine.js component for draw/seeding management.
 * Requires (load before this file): SortableJS, tournament-draw-group-setup-mixin.js,
 *   tournament-draw-reset-mixin.js, tournament-draw-manual-sortable-mixin.js
 */

function drawManager(config) {
    return Object.assign(
        {
            tournamentId:  config.tournamentId,
            categories:    config.categories || [],
            executeUrl:    config.executeUrl,
            resultsUrl:    config.resultsUrl,
            resetUrl:      config.resetUrl,
            manualUrl:     config.manualUrl,
            manualSaveUrl: config.manualSaveUrl,
            groupIndexUrl: config.groupIndexUrl,
            groupSetupUrl: config.groupSetupUrl,
            csrf:          config.csrf,

            activeCategoryId: null,
            activeCategory:   null,
            drawMethod:       'random',
            view:             'seeding', // 'seeding' | 'results' | 'manual'

            athletes:    [],
            groups:      [],
            isDrawn:     false,
            isDoubles:   false,
            loading:     false,
            manualGroups: {},
            unassigned:  [],

            configuredGroups:     [],
            groupCount:           2,
            groupMaxParticipants: 4,
            advancingCount:       1,
            groupSetupLoading:    false,

            _seedingSortable: null,
            _manualSortables: [],

            init() {
                if (this.categories.length > 0) {
                    this.selectCategory(this.categories[0].id);
                }
            },

            selectCategory(categoryId) {
                this.activeCategoryId = categoryId;
                this.activeCategory   = this.categories.find(c => c.id === categoryId) || null;
                this.athletes         = [];
                this.groups           = [];
                this.isDrawn          = false;
                this.configuredGroups = [];

                if (this.activeCategory && this.activeCategory.is_drawn) {
                    this.view = 'results';
                    this.loadResults();
                } else {
                    this.view = 'seeding';
                    this._initSeedingList();
                    this.loadConfiguredGroups();
                }
            },

            // ── Seeding list ──────────────────────────────────────────

            _initSeedingList() {
                this.$nextTick(() => {
                    const el = document.getElementById('draw-seeding-list');
                    if (!el || typeof Sortable === 'undefined') return;
                    if (this._seedingSortable) this._seedingSortable.destroy();
                    this._seedingSortable = new Sortable(el, {
                        handle:    '.drag-handle',
                        animation: 150,
                        onEnd:     () => this._reindexSeeds(),
                    });
                });
            },

            _reindexSeeds() {
                this.athletes.forEach((a, i) => { a.seed_number = i + 1; });
            },

            shuffleAthletes() {
                for (let i = this.athletes.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [this.athletes[i], this.athletes[j]] = [this.athletes[j], this.athletes[i]];
                }
                this._reindexSeeds();
            },

            // ── Execute draw ──────────────────────────────────────────

            async executeDraw() {
                if (!this.activeCategoryId) return;
                if (this.isDrawn) {
                    if (!confirm('Bốc thăm đã thực hiện. Bạn muốn thực hiện lại?')) return;
                }

                this.loading = true;
                try {
                    const res = await fetch(this.executeUrl, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                        body:    JSON.stringify({ category_id: this.activeCategoryId, method: this.drawMethod }),
                    });
                    const data = await res.json();

                    if (data.success) {
                        this.groups  = data.groups;
                        this.isDrawn = true;
                        this.view    = 'results';
                        this._updateCategoryStatus(true);
                        this._showToast('Bốc thăm thành công.', 'success');
                    } else {
                        this._showToast(data.message || 'Có lỗi xảy ra.', 'error');
                    }
                } catch {
                    this._showToast('Lỗi kết nối.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            // ── Load results ──────────────────────────────────────────

            async loadResults() {
                if (!this.activeCategoryId) return;
                this.loading = true;
                try {
                    const url = this.resultsUrl + '?category_id=' + this.activeCategoryId;
                    const res  = await fetch(url, {
                        headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    });
                    const data = await res.json();

                    if (data.success) {
                        this.groups    = data.groups;
                        this.isDrawn   = data.is_drawn;
                        this.isDoubles = data.is_doubles;
                        this.view      = data.is_drawn ? 'results' : 'seeding';
                        if (!data.is_drawn) this._initSeedingList();
                    }
                } catch {
                    this._showToast('Lỗi tải kết quả.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            showSeedingFromResults() {
                const allAthletes = [];
                for (const group of this.groups) {
                    for (const a of (group.athletes || [])) allAthletes.push(a);
                }
                allAthletes.sort((a, b) => (a.seed_number || a.draw_order || 0) - (b.seed_number || b.draw_order || 0));
                this.athletes = allAthletes;
                this.view = 'seeding';
                this._initSeedingList();
            },

            // ── Helpers ───────────────────────────────────────────────

            _updateCategoryStatus(isDrawn) {
                const cat = this.categories.find(c => c.id === this.activeCategoryId);
                if (cat) cat.is_drawn = isDrawn;
            },

            _showToast(message, type) {
                if (typeof toastr !== 'undefined') {
                    toastr[type](message);
                } else {
                    alert(message);
                }
            },

            displayName(athlete) {
                if (this.isDoubles) {
                    return (athlete.pair_name || athlete.athlete1_name + ' / ' + (athlete.athlete2_name || ''));
                }
                return athlete.athlete_name || athlete.name || '';
            },
        },
        drawGroupSetupMixin(),
        drawResetMixin(),
        drawManualSortableMixin()
    );
}
