/**
 * Alpine mixin cho phép BTC nhập manual_rank_override khi nhiều VĐV còn tied sau tier 1-4.
 * Yêu cầu host component cung cấp:
 *   - this.rankOverrideUrl (URL template chứa __GROUP__ placeholder)
 *   - this.activeCategoryId
 *   - this.loadRankings(categoryId)
 *   - this.groups (mảng group, mỗi group có standings[] với is_tied/manual_rank_override)
 */
function rankOverrideMixin() {
    return {
        overrideSaving: false,
        overrideError: '',

        groupHasTied(group) {
            if (!group || !Array.isArray(group.standings)) return false;
            return group.standings.some(s => s && s.is_tied);
        },

        async saveOverrides(groupId) {
            const group = this.groups.find(g => g.group_id === groupId);
            if (!group) return;

            const overrides = group.standings
                .filter(s => s && s.is_tied)
                .map(s => ({
                    athlete_id: s.athlete_id,
                    rank: this._readOverrideValue(groupId, s.athlete_id),
                }));

            if (overrides.length === 0) return;

            this.overrideSaving = true;
            this.overrideError = '';

            try {
                const url = this.rankOverrideUrl.replace('__GROUP__', groupId);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ overrides }),
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();
                this.groups = data.groups || [];
                this.lastUpdatedAt = Date.now();
                this.secondsSinceUpdate = 0;
            } catch (e) {
                console.error('Save rank overrides error:', e);
                this.overrideError = 'Không lưu được thứ tự bốc thăm. Vui lòng thử lại.';
            } finally {
                this.overrideSaving = false;
            }
        },

        _readOverrideValue(groupId, athleteId) {
            const selector = `.rank-override-input[data-group-id="${groupId}"][data-athlete-id="${athleteId}"]`;
            const input = document.querySelector(selector);
            if (!input) return null;
            const raw = input.value.trim();
            if (raw === '') return null;
            const n = parseInt(raw, 10);
            return Number.isFinite(n) && n >= 1 ? n : null;
        },
    };
}
