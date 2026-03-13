/**
 * tournament-draw-reset-mixin.js
 * Reset draw methods for drawManager Alpine component.
 */

function drawResetMixin() {
    return {
        async resetDraw() {
            if (!confirm('Reset sẽ xóa kết quả bốc thăm. Tiếp tục?')) return;

            this.loading = true;
            try {
                const res = await fetch(this.resetUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body:    JSON.stringify({ category_id: this.activeCategoryId }),
                });
                const data = await res.json();

                if (data.requires_force) {
                    this.loading = false;
                    if (!confirm(data.message)) return;
                    return this._forceReset();
                }

                if (data.success) {
                    this.groups   = [];
                    this.isDrawn  = false;
                    this.view     = 'seeding';
                    this._updateCategoryStatus(false);
                    this.athletes = [];
                    this._initSeedingList();
                    this._showToast('Đã reset kết quả bốc thăm.', 'success');
                } else {
                    this._showToast(data.message || 'Có lỗi xảy ra.', 'error');
                }
            } catch {
                this._showToast('Lỗi kết nối.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async _forceReset() {
            this.loading = true;
            try {
                const res = await fetch(this.resetUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body:    JSON.stringify({ category_id: this.activeCategoryId, force: true }),
                });
                const data = await res.json();

                if (data.success) {
                    this.groups   = [];
                    this.isDrawn  = false;
                    this.view     = 'seeding';
                    this._updateCategoryStatus(false);
                    this.athletes = [];
                    this._initSeedingList();
                    this._showToast('Đã reset kết quả bốc thăm.', 'success');
                } else {
                    this._showToast(data.message || 'Có lỗi xảy ra.', 'error');
                }
            } catch {
                this._showToast('Lỗi kết nối.', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
