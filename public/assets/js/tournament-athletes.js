/* Tournament Athletes Management - Alpine.js component */

function tournamentAthletes(config) {
    return {
        // Config injected from Blade
        tournamentId: config.tournamentId,
        storeUrl: config.storeUrl,
        bulkApproveUrl: config.bulkApproveUrl,
        csrfToken: config.csrfToken,

        // State
        athletes: config.athletes || [],
        categories: config.categories || [],
        activeFilter: 'all',
        searchQuery: '',
        selectedIds: [],
        selectAll: false,

        // Add/edit modal
        showModal: false,
        editMode: false,
        editId: null,
        form: { athlete_name: '', email: '', phone: '', category_id: '', partner_id: '' },
        formErrors: {},
        submitting: false,

        // Computed: categories map for fast lookup
        get categoryMap() {
            const map = {};
            this.categories.forEach(c => { map[c.id] = c; });
            return map;
        },

        // Computed: filtered athlete list
        get filtered() {
            const q = this.searchQuery.toLowerCase().trim();
            return this.athletes.filter(a => {
                const matchFilter = this.activeFilter === 'all' || a.status === this.activeFilter;
                const matchSearch = !q
                    || a.athlete_name.toLowerCase().includes(q)
                    || (a.email && a.email.toLowerCase().includes(q));
                return matchFilter && matchSearch;
            });
        },

        // Computed: count per tab
        get counts() {
            return {
                all:      this.athletes.length,
                pending:  this.athletes.filter(a => a.status === 'pending').length,
                approved: this.athletes.filter(a => a.status === 'approved').length,
                rejected: this.athletes.filter(a => a.status === 'rejected').length,
            };
        },

        // Computed: available partners for doubles (same category, no partner yet or same partner)
        get availablePartners() {
            const catId = parseInt(this.form.category_id);
            if (!catId) return [];
            const cat = this.categoryMap[catId];
            if (!cat || !this.isDoublesType(cat.category_type)) return [];
            return this.athletes.filter(a =>
                a.category_id === catId
                && a.id !== this.editId
                && (!a.partner_id || a.partner_id === this.editId)
            );
        },

        isDoublesType(type) {
            return ['double_men', 'double_women', 'double_mixed'].includes(type);
        },

        get selectedCategoryIsDoubles() {
            const catId = parseInt(this.form.category_id);
            if (!catId) return false;
            const cat = this.categoryMap[catId];
            return cat ? this.isDoublesType(cat.category_type) : false;
        },

        // Fetch helper
        async apiFetch(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        },

        // Approve single athlete
        async approveAthlete(athlete) {
            const url = `/tournament-manage/${this.tournamentId}/athletes/${athlete.id}/approve`;
            try {
                await this.apiFetch(url, 'POST');
                athlete.status = 'approved';
                toastr.success('Đã duyệt vận động viên.');
            } catch {
                toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
            }
        },

        // Reject single athlete
        async rejectAthlete(athlete) {
            const url = `/tournament-manage/${this.tournamentId}/athletes/${athlete.id}/reject`;
            try {
                await this.apiFetch(url, 'POST');
                athlete.status = 'rejected';
                toastr.success('Đã từ chối vận động viên.');
            } catch {
                toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
            }
        },

        // Remove athlete with confirmation
        async removeAthlete(athlete) {
            if (!confirm(`Xóa vận động viên "${athlete.athlete_name}"?`)) return;
            const url = `/tournament-manage/${this.tournamentId}/athletes/${athlete.id}`;
            try {
                await this.apiFetch(url, 'DELETE');
                this.athletes = this.athletes.filter(a => a.id !== athlete.id);
                // Clear partner reference
                this.athletes.forEach(a => {
                    if (a.partner_id === athlete.id) {
                        a.partner_id = null;
                        a.partner_name = null;
                    }
                });
                this.selectedIds = this.selectedIds.filter(id => id !== athlete.id);
                toastr.success('Đã xóa vận động viên.');
            } catch {
                toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
            }
        },

        // Open add modal
        openAddModal() {
            this.editMode = false;
            this.editId = null;
            this.form = { athlete_name: '', email: '', phone: '', category_id: '', partner_id: '' };
            this.formErrors = {};
            this.showModal = true;
        },

        // Open edit modal
        openEditModal(athlete) {
            this.editMode = true;
            this.editId = athlete.id;
            this.form = {
                athlete_name: athlete.athlete_name,
                email: athlete.email,
                phone: athlete.phone || '',
                category_id: String(athlete.category_id),
                partner_id: athlete.partner_id ? String(athlete.partner_id) : '',
            };
            this.formErrors = {};
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.formErrors = {};
        },

        // Submit add or edit
        async submitForm() {
            this.formErrors = {};
            this.submitting = true;
            try {
                const body = {
                    athlete_name: this.form.athlete_name,
                    email: this.form.email,
                    phone: this.form.phone,
                    category_id: parseInt(this.form.category_id),
                    partner_id: this.form.partner_id ? parseInt(this.form.partner_id) : null,
                };

                if (this.editMode) {
                    const url = `/tournament-manage/${this.tournamentId}/athletes/${this.editId}`;
                    const res = await this.apiFetch(url, 'PUT', body);
                    const idx = this.athletes.findIndex(a => a.id === this.editId);
                    if (idx !== -1) this.athletes[idx] = res.athlete;
                    toastr.success('Cập nhật thông tin thành công.');
                } else {
                    const res = await this.apiFetch(this.storeUrl, 'POST', body);
                    this.athletes.unshift(res.athlete);
                    toastr.success('Thêm vận động viên thành công.');
                }
                this.closeModal();
            } catch (err) {
                if (err && err.errors) {
                    this.formErrors = err.errors;
                } else {
                    toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
                }
            } finally {
                this.submitting = false;
            }
        },

        // Checkbox selection
        toggleSelect(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx === -1) this.selectedIds.push(id);
            else this.selectedIds.splice(idx, 1);
        },

        toggleSelectAll() {
            this.selectAll = !this.selectAll;
            this.selectedIds = this.selectAll ? this.filtered.map(a => a.id) : [];
        },

        isSelected(id) {
            return this.selectedIds.includes(id);
        },

        // Bulk approve
        async bulkApprove() {
            if (!this.selectedIds.length) return;
            if (!confirm(`Duyệt ${this.selectedIds.length} vận động viên đã chọn?`)) return;
            try {
                const res = await this.apiFetch(this.bulkApproveUrl, 'POST', { ids: this.selectedIds });
                this.athletes.forEach(a => {
                    if (this.selectedIds.includes(a.id)) a.status = 'approved';
                });
                this.selectedIds = [];
                this.selectAll = false;
                toastr.success(res.message);
            } catch {
                toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
            }
        },

        // Bulk remove
        async bulkRemove() {
            if (!this.selectedIds.length) return;
            if (!confirm(`Xóa ${this.selectedIds.length} vận động viên đã chọn?`)) return;
            const ids = [...this.selectedIds];
            let failed = 0;
            for (const id of ids) {
                try {
                    await this.apiFetch(
                        `/tournament-manage/${this.tournamentId}/athletes/${id}`,
                        'DELETE'
                    );
                    this.athletes = this.athletes.filter(a => a.id !== id);
                } catch {
                    failed++;
                }
            }
            this.selectedIds = [];
            this.selectAll = false;
            if (failed === 0) toastr.success('Đã xóa các vận động viên đã chọn.');
            else toastr.warning(`Xóa xong, ${failed} mục thất bại.`);
        },

        statusLabel(status) {
            return { pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' }[status] || status;
        },

        paymentLabel(status) {
            return { unpaid: 'Chưa thanh toán', paid: 'Đã thanh toán' }[status] || status;
        },
    };
}
