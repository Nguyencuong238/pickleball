/**
 * tournament-matches.js
 * Alpine.js component for match management UI.
 * Depends on: tournament-matches-api.js (MatchesApi), tournament-matches-schedule-mixin.js
 */

function matchManager(config) {
    return Object.assign({
        tournamentId:     config.tournamentId,
        indexUrl:         config.indexUrl,
        createGroupsUrl:  config.createGroupsUrl,
        scheduleUrl:      config.scheduleUrl,
        csrf:             config.csrf,

        categories:       [],
        activeCategoryId: null,
        activeFilter:     'all',
        groups:           [],
        stats:            { total: 0, completed: 0, in_progress: 0, scheduled: 0 },

        bestOf:           3,
        expandedMatchId:  null,
        loadingGenerate:  false,
        loadingScore:     {},
        loadingDelete:    {},
        scheduleForms:    {},
        loadingSchedule:  {},
        confirmDeleteId:  null,
        scoreForms:       {},
        pollingTimer:     null,

        init() {
            this.loadMatches();
            this.pollingTimer = setInterval(() => {
                if (!document.hidden) this.loadMatches(true);
            }, 15000);
        },

        destroy() {
            clearInterval(this.pollingTimer);
        },

        async loadMatches(silent = false) {
            try {
                const data = await MatchesApi.loadMatches(
                    this.indexUrl, this.activeCategoryId, this.activeFilter
                );
                if (!data.success) return;

                this.categories = data.categories || [];
                this.stats      = data.stats || this.stats;

                if (!this.activeCategoryId && this.categories.length > 0) {
                    this.activeCategoryId = this.categories[0].category_id;
                }
                this.rebuildGroups();
            } catch (e) {
                if (!silent) toastr.error('Không thể tải danh sách trận đấu');
            }
        },

        rebuildGroups() {
            const cat  = this.categories.find(c => c.category_id === this.activeCategoryId);
            this.groups = cat ? (cat.groups || []) : [];

            for (const g of this.groups) {
                for (const m of (g.matches || [])) {
                    if (!this.scheduleForms[m.id]) {
                        this.scheduleForms[m.id] = { date: m.match_date || '', time: m.match_time || '' };
                    }
                    if (!this.scoreForms[m.id]) {
                        const rows = [];
                        const bestOf = m.best_of || 3;
                        const existing = m.set_scores || [];
                        for (let i = 0; i < bestOf; i++) {
                            rows.push({
                                s1: existing[i] ? String(existing[i].athlete1_score) : '',
                                s2: existing[i] ? String(existing[i].athlete2_score) : '',
                            });
                        }
                        this.scoreForms[m.id] = rows;
                    }
                }
            }
        },

        selectCategory(id) {
            this.activeCategoryId = id;
            this.expandedMatchId  = null;
            this.rebuildGroups();
            this.loadMatches();
        },

        setFilter(f) {
            this.activeFilter    = f;
            this.expandedMatchId = null;
            this.loadMatches();
        },

        toggleExpand(match) {
            if (this.expandedMatchId === match.id) { this.expandedMatchId = null; return; }
            this.expandedMatchId = match.id;

            const bestOf   = match.best_of || 3;
            const existing = match.set_scores || [];
            const rows     = [];
            for (let i = 0; i < bestOf; i++) {
                rows.push({
                    s1: existing[i] ? String(existing[i].athlete1_score) : '',
                    s2: existing[i] ? String(existing[i].athlete2_score) : '',
                });
            }
            this.scoreForms[match.id]    = rows;
            this.scheduleForms[match.id] = { date: match.match_date || '', time: match.match_time || '' };
        },

        scoreFormFor(matchId) {
            return this.scoreForms[matchId] || [];
        },

        async submitScore(matchId) {
            const form = this.scoreForms[matchId] || [];
            const sets = form.filter(r => r.s1 !== '' && r.s2 !== '');

            if (sets.length === 0) { toastr.warning('Vui lòng nhập ít nhất 1 set'); return; }

            this.loadingScore[matchId] = true;
            try {
                const data = await MatchesApi.submitScore(
                    this.scoreUrl(matchId), this.csrf, sets, 'completed'
                );
                if (data.success) {
                    toastr.success('Cập nhật tỉ số thành công');
                    this.expandedMatchId = null;
                    await this.loadMatches();
                } else {
                    toastr.error(data.message || 'Cập nhật thất bại');
                }
            } catch (e) {
                toastr.error('Lỗi kết nối máy chủ');
            } finally {
                this.loadingScore[matchId] = false;
            }
        },

        scoreUrl(matchId) {
            return this.indexUrl.replace(/\/$/, '') + '/' + matchId + '/score';
        },

        matchNumberUrl(matchId) {
            return this.indexUrl.replace(/\/$/, '') + '/' + matchId + '/match-number';
        },

        async saveMatchNumber(matchId, newValue, oldValue) {
            newValue = newValue.trim();
            if (!newValue || newValue === oldValue) return;
            try {
                const data = await MatchesApi.updateMatchNumber(
                    this.matchNumberUrl(matchId), this.csrf, newValue
                );
                if (data.success) {
                    toastr.success('Đã cập nhật số thứ tự');
                    await this.loadMatches();
                } else {
                    toastr.error(data.message || 'Cập nhật thất bại');
                }
            } catch (e) {
                toastr.error('Lỗi kết nối máy chủ');
            }
        },

        askDelete(id)    { this.confirmDeleteId = id; },
        cancelDelete()   { this.confirmDeleteId = null; },

        async confirmDelete(matchId) {
            this.confirmDeleteId = null;
            this.loadingDelete[matchId] = true;
            try {
                const url  = this.indexUrl.replace(/\/$/, '') + '/' + matchId;
                const data = await MatchesApi.deleteMatch(url, this.csrf);
                if (data.success) {
                    toastr.success('Đã xóa trận đấu');
                    await this.loadMatches();
                } else {
                    toastr.error(data.message || 'Xóa thất bại');
                }
            } catch (e) {
                toastr.error('Lỗi kết nối máy chủ');
            } finally {
                this.loadingDelete[matchId] = false;
            }
        },

        async generateMatches() {
            if (!this.activeCategoryId) { toastr.warning('Chọn nội dung thi đấu trước'); return; }
            if (!confirm('Tạo trận đấu vòng bảng cho nội dung này?')) return;

            this.loadingGenerate = true;
            try {
                const data = await MatchesApi.generateMatches(
                    this.createGroupsUrl, this.csrf, this.activeCategoryId, this.bestOf
                );
                if (data.success) {
                    toastr.success(data.message || 'Đã tạo trận đấu');
                    await this.loadMatches();
                } else {
                    toastr.error(data.message || 'Tạo trận đấu thất bại');
                }
            } catch (e) {
                toastr.error('Lỗi kết nối máy chủ');
            } finally {
                this.loadingGenerate = false;
            }
        },

        get activeCategoryName() {
            const cat = this.categories.find(c => c.category_id === this.activeCategoryId);
            return cat ? cat.category_name : '';
        },

        get hasMatches() {
            return this.groups.some(g => g.matches && g.matches.length > 0);
        },

        get progressPct() {
            return this.stats.total ? Math.round((this.stats.completed / this.stats.total) * 100) : 0;
        },

        statusLabel(s) {
            return { scheduled: 'Chưa đấu', in_progress: 'Đang đấu', completed: 'Hoàn thành', cancelled: 'Hủy' }[s] || s;
        },

        filteredGroups() {
            if (this.activeFilter === 'all') return this.groups;
            return this.groups.map(g => ({
                ...g,
                matches: (g.matches || []).filter(m => m.status === this.activeFilter),
            })).filter(g => g.matches.length > 0);
        },
    }, matchesScheduleMixin());
}
