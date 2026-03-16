/**
 * bracket-swap-editor.js
 * Alpine.js mixin: bracket slot swap / edit mode methods.
 */

function bracketSwapEditorMixin() {
    return {
        toggleEditMode() {
            this.editMode = !this.editMode;
            this.clearSwap();
        },

        selectSlot(matchId, slot, athleteName, athleteId) {
            if (!this.editMode) return;
            var match = this.findMatch(matchId);
            if (!match || match.status !== 'scheduled') return;

            if (!this.swapState.active) {
                this.swapState = { active: true, matchId1: matchId, slot1: slot, name1: athleteName, matchId2: null, slot2: null, name2: '' };
            } else {
                if (this.swapState.matchId1 === matchId && this.swapState.slot1 === slot) {
                    this.clearSwap();
                    return;
                }
                this.swapState.matchId2 = matchId;
                this.swapState.slot2 = slot;
                this.swapState.name2 = athleteName;
                this.confirmSwap();
            }
        },

        async confirmSwap() {
            var name1 = this.swapState.name1 || 'Ch\u01B0a x\u00E1c \u0111\u1ECBnh';
            var name2 = this.swapState.name2 || 'Ch\u01B0a x\u00E1c \u0111\u1ECBnh';
            var msg = '\u0110\u1ED5i v\u1ECB tr\u00ED ' + name1 + ' v\u00E0 ' + name2 + '?';
            if (!confirm(msg)) { this.clearSwap(); return; }

            try {
                var swapUrl = this.generateUrl.replace('/generate', '/swap');
                var res = await fetch(swapUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        match_id_1: this.swapState.matchId1,
                        slot_1:     this.swapState.slot1,
                        match_id_2: this.swapState.matchId2,
                        slot_2:     this.swapState.slot2,
                    }),
                });
                var data = await res.json();
                if (data.success) {
                    await this.fetchBracket();
                } else {
                    alert(data.message || 'Đổi vị trí thất bại');
                }
            } catch (e) {
                alert('Lỗi kết nối');
            } finally {
                this.clearSwap();
            }
        },

        clearSwap() {
            this.swapState = { active: false, matchId1: null, slot1: null, name1: '', matchId2: null, slot2: null, name2: '' };
        },

        findMatch(matchId) {
            for (var i = 0; i < this.rounds.length; i++) {
                var found = this.rounds[i].matches.find(function(m) { return m.id === matchId; });
                if (found) return found;
            }
            return null;
        },
    };
}
