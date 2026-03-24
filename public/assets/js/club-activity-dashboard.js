function caAdminDashboard(config) {
    return {
        courts: [],
        queueList: [],
        playing: [],
        stats: {},
        triggerLoading: false,
        triggerMsg: '',
        triggerSuccess: false,
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
                }
            } catch (e) {}
            this._pollTimer = setTimeout(() => this._poll(), 8000);
        },

        destroy() {
            if (this._pollTimer) clearTimeout(this._pollTimer);
        },

        async triggerMatchmaking() {
            this.triggerLoading = true;
            this.triggerMsg = '';
            try {
                var res = await fetch(config.triggerUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                var data = await res.json();
                this.triggerMsg = data.message || '';
                this.triggerSuccess = data.success || false;
                // Refresh immediately
                await this._poll();
            } catch (e) {
                this.triggerMsg = 'Lỗi kết nối.';
                this.triggerSuccess = false;
            } finally {
                this.triggerLoading = false;
            }
        },

        async endMatch(matchId) {
            if (!confirm('Kết thúc trận đấu này?')) return;
            try {
                var url = config.triggerUrl.replace('trigger-match', 'end-match/' + matchId);
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                await this._poll();
            } catch (e) {}
        }
    };
}
