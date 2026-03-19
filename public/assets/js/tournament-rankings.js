/**
 * Tournament Rankings Alpine.js component
 * Handles category switching, group standings, sortable columns, auto-refresh
 */
function rankingsManager(config) {
    return {
        tournamentId: config.tournamentId,
        categoryRankingsUrl: config.categoryRankingsUrl,
        tournamentStatus: config.tournamentStatus,

        // State
        activeCategoryId: config.defaultCategoryId || null,
        activeView: 'by_group', // 'by_group' | 'overall'
        groups: [],
        loading: false,
        lastUpdatedAt: null,
        secondsSinceUpdate: 0,
        pollTimer: null,
        tickTimer: null,

        // Sort state per group (keyed by group_id)
        sortState: {},

        init() {
            if (this.activeCategoryId) {
                this.loadRankings(this.activeCategoryId);
            }
            this.startTick();
            if (this.tournamentStatus === 'ongoing') {
                this.startPolling();
            }
        },

        destroy() {
            this.stopPolling();
            this.stopTick();
        },

        selectCategory(categoryId) {
            if (this.activeCategoryId === categoryId) return;
            this.activeCategoryId = categoryId;
            this.groups = [];
            this.sortState = {};
            this.loadRankings(categoryId);
        },

        async loadRankings(categoryId) {
            this.loading = true;
            try {
                const url = this.categoryRankingsUrl.replace('__CATEGORY__', categoryId);
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.groups = data.groups || [];
                this.lastUpdatedAt = Date.now();
                this.secondsSinceUpdate = 0;
            } catch (e) {
                console.error('Rankings load error:', e);
            } finally {
                this.loading = false;
            }
        },

        // Sort a specific group's standings
        sortGroup(groupId, field) {
            const state = this.sortState[groupId];
            let dir = 'desc';
            if (state && state.field === field) {
                dir = state.dir === 'desc' ? 'asc' : 'desc';
            }
            this.sortState[groupId] = { field, dir };

            const group = this.groups.find(g => g.group_id === groupId);
            if (!group) return;

            group.standings = [...group.standings].sort((a, b) => {
                let av = a[field];
                let bv = b[field];
                if (typeof av === 'string') av = av.toLowerCase();
                if (typeof bv === 'string') bv = bv.toLowerCase();
                if (av < bv) return dir === 'asc' ? -1 : 1;
                if (av > bv) return dir === 'asc' ? 1 : -1;
                return 0;
            });
        },

        getSortDir(groupId, field) {
            const state = this.sortState[groupId];
            if (!state || state.field !== field) return null;
            return state.dir;
        },

        getSortArrow(groupId, field) {
            const dir = this.getSortDir(groupId, field);
            if (dir === 'asc') return '\u25b2';
            if (dir === 'desc') return '\u25bc';
            return '\u25bc';
        },

        isSorted(groupId, field) {
            const state = this.sortState[groupId];
            return state && state.field === field;
        },

        // Overall view: flatten all groups, sort by points then set_diff
        get overallStandings() {
            const flat = [];
            for (const group of this.groups) {
                for (const s of group.standings) {
                    const existing = flat.find(r => r.athlete_id === s.athlete_id);
                    if (!existing) {
                        flat.push({ ...s });
                    }
                }
            }
            flat.sort((a, b) => {
                if (b.points !== a.points) return b.points - a.points;
                if (b.set_diff !== a.set_diff) return b.set_diff - a.set_diff;
                return b.won - a.won;
            });
            return flat.map((s, i) => ({ ...s, rank: i + 1 }));
        },

        // Is the last advancing row for a group (to draw dashed separator)
        isLastAdvancing(group, index) {
            const adv = group.advancing_count || 0;
            if (adv === 0) return false;
            return index === adv - 1 && group.standings.length > adv;
        },

        getRankClass(rank) {
            if (rank === 1) return 'rank-1';
            if (rank === 2) return 'rank-2';
            if (rank === 3) return 'rank-3';
            return '';
        },

        get lastUpdatedLabel() {
            if (!this.lastUpdatedAt) return '';
            const s = this.secondsSinceUpdate;
            if (s < 60) return `Cập nhật ${s}s trước`;
            return `Cập nhật ${Math.floor(s / 60)}ph trước`;
        },

        startPolling() {
            this.pollTimer = setInterval(() => {
                if (this.activeCategoryId) {
                    this.loadRankings(this.activeCategoryId);
                }
            }, 15000);
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        startTick() {
            this.tickTimer = setInterval(() => {
                if (this.lastUpdatedAt) {
                    this.secondsSinceUpdate = Math.floor((Date.now() - this.lastUpdatedAt) / 1000);
                }
            }, 1000);
        },

        stopTick() {
            if (this.tickTimer) {
                clearInterval(this.tickTimer);
                this.tickTimer = null;
            }
        },
    };
}
