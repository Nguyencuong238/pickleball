function caAdminDashboard(config) {
    return {
        courts: [],
        queueList: [],
        playing: [],
        stats: {},
        pendingScores: [],
        triggerLoading: false,
        triggerMsg: '',
        triggerSuccess: false,
        endMatchDialog: { show: false, matchId: null, court: null, matchNumber: null },
        _pollTimer: null,

        init() {
            this._poll();
        },

        async _poll() {
            try {
                var res = await fetch(config.stateUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    var data = await res.json();
                    this.courts = data.courts || [];
                    this.queueList = data.queue || [];
                    this.playing = data.playing || [];
                    this.stats = data.stats || {};
                    this.pendingScores = data.pending_scores || [];
                }
            } catch (e) {}
            this._pollTimer = setTimeout(() => this._poll(), 8000);
        },

        destroy() {
            if (this._pollTimer) clearTimeout(this._pollTimer);
        },

        _headers() {
            return {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            };
        },

        async triggerMatchmaking() {
            this.triggerLoading = true;
            this.triggerMsg = '';
            try {
                var res = await fetch(config.triggerUrl, {
                    method: 'POST',
                    headers: this._headers(),
                });
                var data = await res.json();
                this.triggerMsg = data.message || '';
                this.triggerSuccess = data.success || false;
                await this._poll();
            } catch (e) {
                this.triggerMsg = 'L\u1ed7i k\u1ebft n\u1ed1i.';
                this.triggerSuccess = false;
            } finally {
                this.triggerLoading = false;
            }
        },

        endMatch(matchId, court, matchNumber) {
            this.endMatchDialog = { show: true, matchId: matchId, court: court, matchNumber: matchNumber };
        },

        goToScore(matchId) {
            this.endMatchDialog.show = false;
            window.location.href = config.baseUrl + '/matches/' + matchId + '/score';
        },

        async skipScore(matchId) {
            this.endMatchDialog.show = false;
            var url = config.triggerUrl.replace('trigger-match', 'end-match/' + matchId);
            await fetch(url, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ skip_score: true }),
            });
            await this._poll();
        },

        async adminConfirmScore(matchId) {
            var url = config.baseUrl + '/matches/' + matchId + '/confirm-score';
            await fetch(url, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ action: 'confirm' }),
            });
            await this._poll();
        },

        async adminRejectAndEnd(matchId) {
            if (!confirm('K\u1ebft th\u00fac tr\u1eadn kh\u00f4ng \u0111i\u1ec3m?')) return;
            var confirmUrl = config.baseUrl + '/matches/' + matchId + '/confirm-score';
            var rejectRes = await fetch(confirmUrl, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ action: 'reject' }),
            });
            var rejectData = await rejectRes.json();
            if (!rejectData.success) {
                await this._poll();
                return;
            }
            var endUrl = config.triggerUrl.replace('trigger-match', 'end-match/' + matchId);
            await fetch(endUrl, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ skip_score: true }),
            });
            await this._poll();
        }
    };
}
