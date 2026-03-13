/**
 * bracket-score-entry.js
 * Alpine.js mixin: match score entry methods.
 */

function bracketScoreEntryMixin() {
    return {
        openScore(matchId) {
            this.scoreMatchId = matchId;
            this.scoreSets = [{ s1: 0, s2: 0 }];
        },

        addSet() {
            if (this.scoreSets.length < 5) {
                this.scoreSets.push({ s1: 0, s2: 0 });
            }
        },

        removeSet() {
            if (this.scoreSets.length > 1) {
                this.scoreSets.pop();
            }
        },

        async saveScore() {
            this.scoreSaving = true;
            try {
                const url = '/tournament-manage/' + this.tournamentSlug + '/matches/' + this.scoreMatchId + '/score';
                const res = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        sets:   this.scoreSets,
                        status: 'completed',
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.scoreMatchId = null;
                    await this.fetchBracket();
                } else {
                    alert(data.message || 'Lưu tỉ số thất bại');
                }
            } catch (e) {
                alert('Lỗi kết nối');
                console.error(e);
            } finally {
                this.scoreSaving = false;
            }
        },
    };
}
