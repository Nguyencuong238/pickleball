/**
 * tournament-draw-group-setup-mixin.js
 * Group setup methods for drawManager Alpine component.
 */

function drawGroupSetupMixin() {
    return {
        async loadConfiguredGroups() {
            if (!this.activeCategoryId) return;
            try {
                const url = this.groupIndexUrl + '?category_id=' + this.activeCategoryId;
                const res  = await fetch(url, {
                    headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success) {
                    this.configuredGroups = data.groups;
                }
            } catch {
                // silent — non-critical
            }
        },

        async setupGroups() {
            if (!this.activeCategoryId) return;
            if (!this.groupCount || this.groupCount < 1) {
                this._showToast('Số bảng phải ít nhất là 1.', 'error');
                return;
            }
            if (!this.groupMaxParticipants || this.groupMaxParticipants < 2) {
                this._showToast('Max VĐV/bảng phải ít nhất là 2.', 'error');
                return;
            }

            this.groupSetupLoading = true;
            try {
                const res  = await fetch(this.groupSetupUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body:    JSON.stringify({
                        category_id:      this.activeCategoryId,
                        count:            this.groupCount,
                        max_participants: this.groupMaxParticipants,
                        advancing_count:  this.advancingCount,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.configuredGroups = data.groups;
                    this._showToast(data.message || 'Đã cấu hình bảng.', 'success');
                } else {
                    this._showToast(data.message || 'Có lỗi xảy ra.', 'error');
                }
            } catch {
                this._showToast('Lỗi kết nối.', 'error');
            } finally {
                this.groupSetupLoading = false;
            }
        },
    };
}
