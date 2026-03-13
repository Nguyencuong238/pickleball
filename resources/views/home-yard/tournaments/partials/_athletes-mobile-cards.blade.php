{{-- Athletes mobile card layout --}}
<div class="at-cards-mobile">
    <template x-if="filtered.length === 0">
        <div class="at-empty">
            <div class="at-empty-icon">&#128100;</div>
            <div class="at-empty-text">Không có vận động viên nào.</div>
        </div>
    </template>
    <template x-for="athlete in filtered" :key="'m' + athlete.id">
        <div class="at-card-mobile">
            <div class="at-card-mobile-header">
                <div>
                    <div class="at-card-mobile-name" x-text="athlete.athlete_name"></div>
                    <div class="at-card-mobile-cat" x-text="athlete.category_name"></div>
                </div>
                <span class="at-badge"
                      :class="{
                          'at-badge-pending':  athlete.status === 'pending',
                          'at-badge-approved': athlete.status === 'approved',
                          'at-badge-rejected': athlete.status === 'rejected'
                      }"
                      x-text="statusLabel(athlete.status)">
                </span>
            </div>

            <div class="at-card-mobile-meta">
                <span x-text="athlete.email"></span>
                <span x-show="athlete.phone" x-text="'| ' + athlete.phone"></span>
                <span x-show="athlete.is_doubles && athlete.partner_name"
                      x-text="'Đối tác: ' + athlete.partner_name"></span>
                <span class="at-badge"
                      :class="{
                          'at-badge-paid':   athlete.payment_status === 'paid',
                          'at-badge-unpaid': athlete.payment_status !== 'paid'
                      }"
                      x-text="paymentLabel(athlete.payment_status)">
                </span>
            </div>

            <div class="at-card-mobile-actions">
                <button class="at-action-btn at-btn-approve"
                        x-show="athlete.status !== 'approved'"
                        @click="approveAthlete(athlete)">
                    Duyệt
                </button>
                <button class="at-action-btn at-btn-reject"
                        x-show="athlete.status !== 'rejected'"
                        @click="rejectAthlete(athlete)">
                    Từ chối
                </button>
                <button class="at-action-btn at-btn-edit"
                        @click="openEditModal(athlete)">
                    Sửa
                </button>
                <button class="at-action-btn at-btn-remove"
                        @click="removeAthlete(athlete)">
                    Xóa
                </button>
            </div>
        </div>
    </template>
</div>
