/**
 * tournament-matches-schedule-mixin.js
 * Schedule editing methods (date/time) for matchManager Alpine component.
 * Depends on: tournament-matches-api.js (MatchesApi)
 */

function matchesScheduleMixin() {
    return {
        async saveSchedule(matchId) {
            this.loadingSchedule[matchId] = true;
            const form = this.scheduleForms[matchId];
            const url  = this.scheduleUrl.replace('__ID__', matchId);
            try {
                const data = await MatchesApi.updateSchedule(url, this.csrf, form.date, form.time);
                if (data.success) {
                    const match = this._findMatch(matchId);
                    if (match) {
                        match.match_date = data.match.match_date;
                        match.match_time = data.match.match_time;
                    }
                    toastr.success('Đã lưu lịch thi đấu.');
                } else {
                    toastr.error(data.message || 'Có lỗi xảy ra.');
                }
            } catch (e) {
                toastr.error('Lỗi kết nối máy chủ.');
            } finally {
                this.loadingSchedule[matchId] = false;
            }
        },

        _findMatch(matchId) {
            for (const cat of this.categories) {
                for (const grp of (cat.groups || [])) {
                    const m = (grp.matches || []).find(x => x.id === matchId);
                    if (m) return m;
                }
            }
            return null;
        },
    };
}
