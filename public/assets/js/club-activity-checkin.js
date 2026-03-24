function caCheckin(config) {
    return {
        step: 1,
        phone: '',
        user: null,
        loading: false,
        error: '',
        form: { name: '', gender: null },

        formatPhone() {
            let digits = this.phone.replace(/\D/g, '');
            if (digits.length > 10) digits = digits.slice(0, 10);
            if (digits.length > 4 && digits.length <= 7) {
                this.phone = digits.slice(0, 4) + ' ' + digits.slice(4);
            } else if (digits.length > 7) {
                this.phone = digits.slice(0, 4) + ' ' + digits.slice(4, 7) + ' ' + digits.slice(7);
            } else {
                this.phone = digits;
            }
        },

        async lookup() {
            const raw = this.phone.replace(/\s/g, '');
            if (raw.length < 10) {
                this.error = 'Vui lòng nhập số điện thoại hợp lệ.';
                return;
            }
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch(config.lookupUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ phone: raw }),
                });
                if (!res.ok) {
                    const err = await res.json();
                    this.error = err.message || 'Lỗi hệ thống. Vui lòng thử lại.';
                    return;
                }
                const data = await res.json();
                if (data.found) {
                    this.user = data.user;
                    this.step = 2;
                } else {
                    this.form.name = '';
                    this.form.gender = null;
                    this.step = 3;
                }
            } catch {
                this.error = 'Không thể kết nối. Vui lòng thử lại.';
            } finally {
                this.loading = false;
            }
        },

        async confirm() {
            if (!this.user) return;
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch(config.confirmUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ user_id: this.user.id }),
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    this.error = data.message || 'Lỗi check-in. Vui lòng thử lại.';
                }
            } catch {
                this.error = 'Không thể kết nối. Vui lòng thử lại.';
            } finally {
                this.loading = false;
            }
        },

        async register() {
            if (!this.form.name.trim()) {
                this.error = 'Vui lòng nhập họ và tên.';
                return;
            }
            this.loading = true;
            this.error = '';
            try {
                const raw = this.phone.replace(/\s/g, '');
                const res = await fetch(config.registerUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        name: this.form.name.trim(),
                        phone: raw,
                        gender: this.form.gender,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    this.error = data.message || 'Lỗi đăng ký. Vui lòng thử lại.';
                }
            } catch {
                this.error = 'Không thể kết nối. Vui lòng thử lại.';
            } finally {
                this.loading = false;
            }
        },
    };
}
