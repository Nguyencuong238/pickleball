/* Tournament Dashboard - Alpine.js Components */

function tournamentIndex() {
    return {
        search: '',
        statusFilter: '',
        get filtered() {
            return Array.from(document.querySelectorAll('[data-tournament]')).filter(el => {
                const name = el.dataset.name ? el.dataset.name.toLowerCase() : '';
                const status = el.dataset.status || '';
                const matchSearch = !this.search || name.includes(this.search.toLowerCase());
                const matchStatus = !this.statusFilter || status === this.statusFilter;
                return matchSearch && matchStatus;
            });
        },
        filterCards() {
            const cards = document.querySelectorAll('[data-tournament]');
            const empty = document.getElementById('td-empty-state');
            let visible = 0;
            cards.forEach(el => {
                const name = (el.dataset.name || '').toLowerCase();
                const status = el.dataset.status || '';
                const matchSearch = !this.search || name.includes(this.search.toLowerCase());
                const matchStatus = !this.statusFilter || status === this.statusFilter;
                const show = matchSearch && matchStatus;
                el.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (empty) empty.style.display = visible === 0 ? '' : 'none';
        }
    };
}

function tournamentForm(existingCategories) {
    return {
        categories: existingCategories || [],
        bannerPreview: null,

        addCategory() {
            this.categories.push({
                id: null,
                category_name: '',
                category_type: 'single_men',
                age_group: '',
                max_participants: 16,
                prize_money: 0,
            });
        },

        removeCategory(index) {
            this.categories.splice(index, 1);
        },

        handleBannerChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => { this.bannerPreview = e.target.result; };
            reader.readAsDataURL(file);
        },

        validateForm() {
            if (this.categories.length === 0) {
                alert('Vui lòng thêm ít nhất một hạng mục thi đấu');
                return false;
            }
            for (let i = 0; i < this.categories.length; i++) {
                if (!this.categories[i].category_name.trim()) {
                    alert('Tên hạng mục không được để trống');
                    return false;
                }
            }
            return true;
        }
    };
}

function tournamentDashboard(activeSection) {
    return {
        active: activeSection || 'overview',
    };
}
