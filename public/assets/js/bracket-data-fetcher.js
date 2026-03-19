/**
 * bracket-data-fetcher.js
 * Alpine.js mixin: bracket data loading and generation methods.
 */

function bracketDataFetcherMixin() {
    return {
        async fetchBracket() {
            if (!this.activeCategoryId) return;
            this.loading = true;
            try {
                const url = this.dataUrl + '?category_id=' + this.activeCategoryId;
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    this.rounds = data.rounds || [];
                } else {
                    this.rounds = [];
                }
            } catch (e) {
                this.rounds = [];
                console.error('Lỗi kết nối', e);
            } finally {
                this.loading = false;
            }
        },

        async generateBracket() {
            if (!this.activeCategoryId) return;
            this.generating = true;
            try {
                const res = await fetch(this.generateUrl, {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        category_id:        this.activeCategoryId,
                        enable_third_place: this.enableThirdPlace,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    await this.fetchBracket();
                } else {
                    alert(data.message || 'Tạo bracket thất bại');
                }
            } catch (e) {
                alert('Lỗi kết nối');
                console.error(e);
            } finally {
                this.generating = false;
            }
        },
    };
}
