/**
 * tournament-draw-manual-sortable-mixin.js
 * Manual draw + SortableJS drag-drop methods for drawManager Alpine component.
 *
 * APPROACH — onEnd DOM-read (DOM-authoritative sync):
 *   Instead of tracking add/remove/reorder events with fragile index math,
 *   we listen to onEnd (fires once after each drag completes) and re-read the
 *   actual DOM to rebuild all arrays. This is immune to:
 *     - x-if placeholder elements offsetting evt.oldIndex / evt.newIndex
 *     - race conditions between SortableJS DOM moves and Alpine re-renders
 *     - cross-container class mismatches (.draw-athlete-item vs .draw-group-athlete)
 *   After syncing, we write through the REACTIVE proxy (not Alpine.raw()) so that
 *   Alpine re-renders counters (x-text) and placeholders (x-if) correctly.
 */

function drawManualSortableMixin() {
    return {
        async openManualDraw() {
            this.loading = true;
            try {
                const url  = this.manualUrl + '?category_id=' + this.activeCategoryId;
                const res  = await fetch(url, {
                    headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (data.success) {
                    this.isDoubles    = data.is_double;
                    this.unassigned   = (data.athletes || []).filter(a => !a.group_id);
                    this.manualGroups = {};

                    (data.groups || []).forEach(g => {
                        const assigned = (data.athletes || []).filter(a => a.group_id === g.id);
                        this.manualGroups[g.id] = {
                            id: g.id, name: g.group_name, max: g.max_participants,
                            athletes: assigned,
                        };
                    });

                    this.view = 'manual';
                    this.$nextTick(() => this._initManualSortables());
                } else {
                    this._showToast(data.message || 'Có lỗi xảy ra.', 'error');
                }
            } catch {
                this._showToast('Lỗi tải dữ liệu thủ công.', 'error');
            } finally {
                this.loading = false;
            }
        },

        _initManualSortables() {
            this._manualSortables.forEach(s => s.destroy());
            this._manualSortables = [];

            // Single onEnd handler per container — fires once after each drag completes.
            // At this point the DOM fully reflects the user's intended arrangement.
            const makeOptions = () => ({
                group:     'manual-draw',
                animation: 150,
                handle:    '.drag-handle',
                onEnd:     () => this._syncDataFromDOM(),
            });

            const unassignedEl = document.getElementById('manual-unassigned');
            if (unassignedEl) {
                this._manualSortables.push(new Sortable(unassignedEl, makeOptions()));
            }

            Object.keys(this.manualGroups).forEach(gid => {
                const el = document.getElementById('manual-group-' + gid);
                if (el) {
                    this._manualSortables.push(new Sortable(el, makeOptions()));
                }
            });
        },

        // Build a flat map of { athleteId => athleteObject } from current data state.
        // Used for O(1) DOM-element-to-data-object lookup in _syncDataFromDOM.
        _buildAthleteMap() {
            const map = {};
            const register = (a) => {
                map[String(a.athlete1_id ?? a.id)] = a;
            };
            Alpine.raw(this.unassigned).forEach(register);
            Object.values(this.manualGroups).forEach(g => Alpine.raw(g.athletes).forEach(register));
            return map;
        },

        // Re-read every container's DOM after a drag and write the results back into
        // Alpine's reactive arrays. Using reactive splice (not Alpine.raw) means Alpine
        // will re-render counters and placeholders automatically.
        _syncDataFromDOM() {
            const athleteMap = this._buildAthleteMap();
            const selector   = '.draw-athlete-item, .draw-group-athlete';

            // Sync unassigned panel
            const unassignedEl = document.getElementById('manual-unassigned');
            if (unassignedEl) {
                const fresh = [];
                unassignedEl.querySelectorAll(selector).forEach(li => {
                    const a = athleteMap[li.dataset.athleteId];
                    if (a) fresh.push(a);
                });
                this.unassigned.splice(0, this.unassigned.length, ...fresh);
            }

            // Sync each group
            Object.keys(this.manualGroups).forEach(gid => {
                const el = document.getElementById('manual-group-' + gid);
                if (!el) return;
                const fresh = [];
                el.querySelectorAll(selector).forEach(li => {
                    const a = athleteMap[li.dataset.athleteId];
                    if (a) fresh.push(a);
                });
                this.manualGroups[gid].athletes.splice(
                    0, this.manualGroups[gid].athletes.length, ...fresh
                );
            });
        },

        manualGroupList() {
            return Object.values(this.manualGroups);
        },

        async saveManualDraw() {
            const assignments = {};

            Object.entries(this.manualGroups).forEach(([gid, group]) => {
                assignments[gid] = group.athletes.map((a, idx) => ({
                    athlete_id: this.isDoubles ? a.athlete1_id : a.id,
                    partner_id: this.isDoubles ? (a.athlete2_id || null) : null,
                    draw_order: idx + 1,
                }));
            });

            this.loading = true;
            try {
                const res = await fetch(this.manualSaveUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body:    JSON.stringify({ category_id: this.activeCategoryId, assignments }),
                });
                const data = await res.json();

                if (data.success) {
                    this._showToast(data.message, 'success');
                    this.loadResults();
                } else {
                    this._showToast(data.message || 'Có lỗi xảy ra.', 'error');
                }
            } catch {
                this._showToast('Lỗi kết nối.', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
