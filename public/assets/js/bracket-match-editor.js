/**
 * bracket-match-editor.js
 * Alpine.js mixin: match editing modal methods.
 */

function bracketMatchEditorMixin() {
    return {
        editMatchId: null,
        editEligibleAthletes: [],
        editLoading: false,
        editSaving: false,
        editCascadeCount: 0,
        editForm: {
            athlete1_id: '',
            athlete2_id: '',
            match_time: '',
            best_of: '',
            notes: '',
        },

        async openMatchEditor(matchId) {
            this.editMatchId = matchId;
            this.editLoading = true;
            this.editCascadeCount = 0;
            this.editEligibleAthletes = [];

            var match = this.findMatch(matchId);

            // Reset form with non-athlete fields first
            this.editForm = {
                athlete1_id: '',
                athlete2_id: '',
                match_time: match ? (match.match_time || '') : '',
                best_of: match && match.best_of ? String(match.best_of) : '',
                notes: match ? (match.notes || '') : '',
            };

            try {
                var url = this.dataUrl.replace('/data', '/eligible-athletes') + '?match_id=' + matchId;
                var res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                var data = await res.json();
                if (data.success) {
                    var list = data.athletes || [];
                    // Include current athletes in list (they belong to this match)
                    if (match && match.athlete1 && !list.find(function(a) { return a.id === match.athlete1.id; })) {
                        list.unshift({
                            id: match.athlete1.id,
                            name: match.athlete1.name,
                            partner_name: match.athlete1.partner_name,
                            pair_name: match.athlete1.pair_name || match.athlete1.name,
                            seed: match.athlete1.seed,
                        });
                    }
                    if (match && match.athlete2 && !list.find(function(a) { return a.id === match.athlete2.id; })) {
                        list.unshift({
                            id: match.athlete2.id,
                            name: match.athlete2.name,
                            partner_name: match.athlete2.partner_name,
                            pair_name: match.athlete2.pair_name || match.athlete2.name,
                            seed: match.athlete2.seed,
                        });
                    }
                    this.editEligibleAthletes = list;
                }
            } catch (e) {
                console.error('Failed to load eligible athletes', e);
            } finally {
                this.editLoading = false;
                // Set athlete IDs after loading=false so options exist when x-model binds
                var self = this;
                this.$nextTick(function() {
                    if (match && match.athlete1) {
                        self.editForm.athlete1_id = String(match.athlete1.id);
                    }
                    if (match && match.athlete2) {
                        self.editForm.athlete2_id = String(match.athlete2.id);
                    }
                });
            }
        },

        async saveMatchEdit() {
            this.editSaving = true;
            try {
                var url = this.dataUrl.replace('/data', '/update-match');
                var body = { match_id: this.editMatchId };

                if (this.editForm.athlete1_id !== undefined) {
                    body.athlete1_id = this.editForm.athlete1_id || null;
                }
                if (this.editForm.athlete2_id !== undefined) {
                    body.athlete2_id = this.editForm.athlete2_id || null;
                }
                if (this.editForm.match_time) {
                    body.match_time = this.editForm.match_time;
                }
                if (this.editForm.best_of) {
                    body.best_of = parseInt(this.editForm.best_of);
                }
                if (this.editForm.notes !== undefined) {
                    body.notes = this.editForm.notes;
                }

                var res = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
                var data = await res.json();

                if (data.needs_confirm && data.affected_count > 0) {
                    this.editCascadeCount = data.affected_count;
                    if (confirm(data.message + ' Bạn có muốn tiếp tục?')) {
                        body.confirm_cascade = true;
                        var res2 = await fetch(url, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(body),
                        });
                        var data2 = await res2.json();
                        if (data2.success) {
                            this.editMatchId = null;
                            this.editCascadeCount = 0;
                            await this.fetchBracket();
                        } else {
                            alert(data2.message || 'Lưu thất bại');
                        }
                    }
                    this.editSaving = false;
                    return;
                }

                if (data.success) {
                    this.editMatchId = null;
                    this.editCascadeCount = 0;
                    await this.fetchBracket();
                } else {
                    alert(data.message || 'Lưu thất bại');
                }
            } catch (e) {
                alert('Lỗi kết nối');
                console.error(e);
            } finally {
                this.editSaving = false;
            }
        },
    };
}
