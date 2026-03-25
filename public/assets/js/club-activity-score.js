function caScoreSubmit(config) {
    var bestOf = config.bestOf || 1;
    var pointsPerSet = config.pointsPerSet || 21;
    var initialSets = [];
    for (var i = 0; i < bestOf; i++) {
        initialSets.push({ team1: 0, team2: 0 });
    }

    return {
        sets: initialSets,
        bestOf: bestOf,
        pointsPerSet: pointsPerSet,
        loading: false,
        error: '',

        increment(setIdx, team) {
            if (this.sets[setIdx][team] < this.pointsPerSet) {
                this.sets[setIdx][team]++;
            }
        },

        decrement(setIdx, team) {
            if (this.sets[setIdx][team] > 0) {
                this.sets[setIdx][team]--;
            }
        },

        get showSet3() {
            if (this.bestOf < 3) return false;
            var s1Winner = this.getSetWinner(0);
            var s2Winner = this.getSetWinner(1);
            return s1Winner && s2Winner && s1Winner !== s2Winner;
        },

        getSetWinner(idx) {
            var s = this.sets[idx];
            if (s.team1 === 0 && s.team2 === 0) return null;
            if (s.team1 > s.team2) return 'team1';
            if (s.team2 > s.team1) return 'team2';
            return null;
        },

        get winner() {
            if (this.bestOf === 1) {
                return this.getSetWinner(0);
            }
            // Best of 3
            var t1Wins = 0;
            var t2Wins = 0;
            var setsToCheck = this.showSet3 ? 3 : 2;
            for (var i = 0; i < setsToCheck; i++) {
                var w = this.getSetWinner(i);
                if (w === 'team1') t1Wins++;
                if (w === 'team2') t2Wins++;
            }
            if (t1Wins >= 2) return 'team1';
            if (t2Wins >= 2) return 'team2';
            return null;
        },

        get isValid() {
            if (!this.winner) return false;
            if (this.bestOf === 1) {
                var s = this.sets[0];
                return s.team1 !== s.team2 && (s.team1 > 0 || s.team2 > 0);
            }
            // Best of 3
            var setsToCheck = this.showSet3 ? 3 : 2;
            for (var i = 0; i < setsToCheck; i++) {
                var s = this.sets[i];
                if (s.team1 === s.team2) return false;
                if (s.team1 === 0 && s.team2 === 0) return false;
            }
            return true;
        },

        async submit() {
            if (!this.isValid) return;
            this.loading = true;
            this.error = '';

            var setsToSend;
            if (this.bestOf === 1) {
                setsToSend = [this.sets[0]];
            } else {
                setsToSend = this.showSet3
                    ? this.sets
                    : [this.sets[0], this.sets[1]];
            }

            try {
                var res = await fetch(config.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ set_scores: setsToSend }),
                });
                var data = await res.json();
                if (data.success) {
                    window.location.href = config.queueUrl;
                } else {
                    this.error = data.message || 'L\u1ed7i g\u1eedi \u0111i\u1ec3m. Vui l\u00f2ng th\u1eed l\u1ea1i.';
                }
            } catch (e) {
                this.error = 'Kh\u00f4ng th\u1ec3 k\u1ebft n\u1ed1i. Vui l\u00f2ng th\u1eed l\u1ea1i.';
            } finally {
                this.loading = false;
            }
        }
    };
}
