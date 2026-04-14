/* Tournament Athletes Management - Alpine.js component */

function tournamentAthletes(config) {
    return {
        // Config injected from Blade
        tournamentId: config.tournamentId,
        storeUrl: config.storeUrl,
        bulkApproveUrl: config.bulkApproveUrl,
        searchUserUrl: config.searchUserUrl,
        indexUrl: config.indexUrl,
        importUrl: config.importUrl,
        importTemplateUrl: config.importTemplateUrl,
        csrfToken: config.csrfToken,

        // State
        athletes: config.athletes || [],
        categories: config.categories || [],
        activeFilter: 'all',
        categoryFilter: '',
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

        // Import modal
        showImportModal: false,
        importFile: null,
        importLoading: false,
        importErrors: [],

        // User search in add modal
        userSearchQuery: '',
        userSearchResults: [],
        userSearchLoading: false,
        userSearchDone: false,

        // Computed: categories map for fast lookup
        get categoryMap() {
            const map = {};
            this.categories.forEach(c => { map[c.id] = c; });
            return map;
        },

        // Computed: filtered athlete list
        get filtered() {
            const q = this.searchQuery.toLowerCase().trim();
            const catId = this.categoryFilter ? parseInt(this.categoryFilter) : null;
            return this.athletes.filter(a => {
                const matchFilter = this.activeFilter === 'all' || a.status === this.activeFilter;
                const matchCategory = !catId || a.category_id === catId;
                const matchSearch = !q
                    || a.athlete_name.toLowerCase().includes(q)
                    || (a.email && a.email.toLowerCase().includes(q));
                return matchFilter && matchCategory && matchSearch;
            });
        },

        // Computed: count per tab (respects category filter)
        get counts() {
            const catId = this.categoryFilter ? parseInt(this.categoryFilter) : null;
            const base = catId ? this.athletes.filter(a => a.category_id === catId) : this.athletes;
            return {
                all:      base.length,
                pending:  base.filter(a => a.status === 'pending').length,
                approved: base.filter(a => a.status === 'approved').length,
                rejected: base.filter(a => a.status === 'rejected').length,
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

        // Search user by email or phone
        async searchUser() {
            const q = this.userSearchQuery.trim();
            if (q.length < 3) {
                this.userSearchResults = [];
                this.userSearchDone = false;
                return;
            }
            this.userSearchLoading = true;
            this.userSearchDone = false;
            try {
                const url = this.searchUserUrl + '?q=' + encodeURIComponent(q);
                const data = await this.apiFetch(url, 'GET');
                this.userSearchResults = data.users || [];
            } catch {
                this.userSearchResults = [];
            } finally {
                this.userSearchLoading = false;
                this.userSearchDone = true;
            }
        },

        selectUser(user) {
            this.form.athlete_name = user.name || '';
            this.form.email = user.email || '';
            this.form.phone = user.phone || '';
            this.clearUserSearch();
        },

        clearUserSearch() {
            this.userSearchQuery = '';
            this.userSearchResults = [];
            this.userSearchLoading = false;
            this.userSearchDone = false;
        },

        // Open add modal
        openAddModal() {
            this.editMode = false;
            this.editId = null;
            this.form = { athlete_name: '', email: '', phone: '', category_id: '', partner_id: '' };
            this.formErrors = {};
            this.clearUserSearch();
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

        // Import Excel
        openImportModal() {
            this.showImportModal = true;
            this.importFile = null;
            this.importErrors = [];
            this.importLoading = false;
        },

        closeImportModal() {
            this.showImportModal = false;
            this.importFile = null;
            this.importErrors = [];
            this.importLoading = false;
            if (this.$refs.importFileInput) {
                this.$refs.importFileInput.value = '';
            }
        },

        async submitImport() {
            if (!this.importFile) return;
            this.importLoading = true;
            this.importErrors = [];

            const formData = new FormData();
            formData.append('file', this.importFile);
            formData.append('_token', this.csrfToken);

            try {
                const res = await fetch(this.importUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                const data = await res.json();

                if (res.status === 422 && Array.isArray(data.errors) && data.errors.length > 0) {
                    this.importErrors = data.errors;
                    return;
                }
                if (!res.ok) {
                    toastr.error(data.message || 'Import thất bại.');
                    return;
                }

                toastr.success(data.message);
                await this.refreshAthletes();
                this.closeImportModal();
            } catch (e) {
                console.error(e);
                toastr.error('Lỗi mạng, vui lòng thử lại.');
            } finally {
                this.importLoading = false;
            }
        },

        async refreshAthletes() {
            try {
                const res = await fetch(this.indexUrl, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                this.athletes = data.athletes || [];
                if (Array.isArray(data.categories)) {
                    this.categories = data.categories;
                }
                // Stale IDs after reload → reset bulk selection
                this.selectedIds = [];
                this.selectAll = false;
            } catch (e) {
                console.error(e);
            }
        },
    };
}
