/**
 * bracket-score-entry.js
 * Alpine.js mixin: match score entry methods.
 */

function bracketScoreEntryMixin() {
    return {
        openScore(matchId) {
            this.scoreMatchId = matchId;
            // Load existing scores if match has set_scores
            const match = this.findMatch(matchId);
            if (match && match.set_scores && match.set_scores.length > 0) {
                this.scoreSets = match.set_scores.map(function(s) {
                    return { s1: s.athlete1_score || 0, s2: s.athlete2_score || 0 };
                });
            } else {
                this.scoreSets = [{ s1: 0, s2: 0 }];
            }
        },

        findMatch(matchId) {
            for (var i = 0; i < this.rounds.length; i++) {
                var matches = this.rounds[i].matches || [];
                for (var j = 0; j < matches.length; j++) {
                    if (matches[j].id === matchId) return matches[j];
                }
            }
            return null;
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
