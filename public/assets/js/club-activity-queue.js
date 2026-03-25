function caQueue(config) {
    return {
        config: config,
        queue: [],
        courts: [],
        myStatus: null,
        lastUpdated: null,
        isStale: false,
        endingMatch: false,
        _pollTimer: null,

        init() {
            this._poll();
        },

        async _poll() {
            try {
                var res = await fetch(config.statusUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error(res.status);
                var data = await res.json();
                this.queue = data.queue || [];
                this.courts = data.courts || [];
                this.myStatus = data.my_status;
                this.lastUpdated = Date.now();
                this.isStale = false;
            } catch (e) {
                this.isStale = true;
            } finally {
                this._pollTimer = setTimeout(() => this._poll(), 12000);
            }
        },

        destroy() {
            if (this._pollTimer) clearTimeout(this._pollTimer);
        },

        get estimatedWait() {
            if (!this.myStatus || !this.myStatus.queue_position) return null;
            var avg = config.avgMatchDuration || 15;
            return Math.ceil(this.myStatus.queue_position / (config.courtsCount * 4)) * avg;
        },

        async playerEndMatch() {
            if (!this.myStatus || !this.myStatus.current_match_id) return;
            this.endingMatch = true;
            try {
                var url = config.playerEndMatchUrlBase + '/' + this.myStatus.current_match_id;
                var res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                var data = await res.json();
                if (data.success && data.score_url) {
                    window.location.href = data.score_url;
                    return;
                }
            } catch (e) {}
            this.endingMatch = false;
        }
    };
}
