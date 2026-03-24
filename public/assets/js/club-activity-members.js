function caMembers(config) {
    return {
        search: '',
        drawerOpen: false,
        newPhone: '',
        newOprs: '',
        addLoading: false,
        addError: '',
        addSuccess: '',

        async addMember() {
            if (!this.newPhone.trim()) {
                this.addError = 'Vui lòng nhập số điện thoại.';
                return;
            }
            this.addLoading = true;
            this.addError = '';
            this.addSuccess = '';
            try {
                var res = await fetch(config.addUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        phone: this.newPhone.replace(/\s/g, ''),
                        initial_oprs: this.newOprs || null,
                    }),
                });
                var data = await res.json();
                if (data.success) {
                    this.addSuccess = data.message;
                    this.newPhone = '';
                    this.newOprs = '';
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    this.addError = data.message || 'Lỗi thêm thành viên.';
                }
            } catch (e) {
                this.addError = 'Lỗi kết nối.';
            } finally {
                this.addLoading = false;
            }
        },

        async updateOprs(userId, value) {
            try {
                await fetch(config.oprsUrlBase + '/' + userId + '/oprs', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ initial_oprs: value }),
                });
            } catch (e) {}
        },

        async updateStatus(userId, status) {
            try {
                await fetch(config.oprsUrlBase + '/' + userId + '/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ member_status: status }),
                });
            } catch (e) {}
        }
    };
}
