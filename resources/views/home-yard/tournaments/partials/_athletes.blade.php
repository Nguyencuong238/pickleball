{{-- Athletes Management Partial --}}
@php
    $athletesJson   = json_encode($athletes->map(fn($a) => [
        'id'             => $a->id,
        'athlete_name'   => $a->athlete_name,
        'email'          => $a->email,
        'phone'          => $a->phone,
        'category_id'    => $a->category_id,
        'category_name'  => $a->category->category_name ?? '',
        'category_type'  => $a->category->category_type ?? '',
        'is_doubles'     => $a->category ? $a->category->isDoubles() : false,
        'status'         => $a->status,
        'payment_status' => $a->payment_status,
        'partner_id'     => $a->partner_id,
        'partner_name'   => $a->partner->athlete_name ?? null,
    ]));
    $categoriesJson = json_encode($categories->map(fn($c) => [
        'id'            => $c->id,
        'category_name' => $c->category_name,
        'category_type' => $c->category_type,
    ]));
@endphp

<div x-data="tournamentAthletes({
    tournamentId: '{{ $tournament->slug }}',
    storeUrl: '{{ route('tournament-manage.athletes.store', $tournament) }}',
    bulkApproveUrl: '{{ route('tournament-manage.athletes.bulkApprove', $tournament) }}',
    csrfToken: document.querySelector('meta[name=csrf-token]').content,
    athletes: {{ $athletesJson }},
    categories: {{ $categoriesJson }}
})">

    {{-- Toolbar --}}
    <div class="at-toolbar">
        <div class="at-search-wrap">
            <span class="at-search-icon">&#128269;</span>
            <input type="text"
                   class="at-search-input"
                   placeholder="Tìm theo tên, email..."
                   x-model="searchQuery">
        </div>
        <button class="td-btn td-btn-primary" @click="openAddModal">
            + Thêm vận động viên
        </button>
    </div>

    {{-- Filter tabs --}}
    <div class="at-filter-bar">
        <button class="at-filter-tab" :class="{ active: activeFilter === 'all' }" @click="activeFilter = 'all'">
            Tất cả (<span x-text="counts.all"></span>)
        </button>
        <button class="at-filter-tab tab-pending" :class="{ active: activeFilter === 'pending' }" @click="activeFilter = 'pending'">
            Chờ duyệt (<span x-text="counts.pending"></span>)
        </button>
        <button class="at-filter-tab tab-approved" :class="{ active: activeFilter === 'approved' }" @click="activeFilter = 'approved'">
            Đã duyệt (<span x-text="counts.approved"></span>)
        </button>
        <button class="at-filter-tab tab-rejected" :class="{ active: activeFilter === 'rejected' }" @click="activeFilter = 'rejected'">
            Từ chối (<span x-text="counts.rejected"></span>)
        </button>
    </div>

    {{-- Bulk action bar --}}
    <div class="at-bulk-bar" x-show="selectedIds.length > 0" x-cloak>
        <span x-text="selectedIds.length + ' vận động viên đã chọn'"></span>
        <button class="td-btn td-btn-ghost" style="color:#fff;background:rgba(255,255,255,0.15);" @click="bulkApprove">
            Duyệt tất cả
        </button>
        <button class="td-btn td-btn-danger" @click="bulkRemove">
            Xóa đã chọn
        </button>
        <button class="td-btn td-btn-ghost" style="color:#94a3b8;" @click="selectedIds = []; selectAll = false;">
            Hủy
        </button>
    </div>

    {{-- Desktop table --}}
    <div class="at-table-wrap">
        <table class="at-table">
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" :checked="selectAll" @change="toggleSelectAll">
                    </th>
                    <th>Vận động viên</th>
                    <th>Nội dung</th>
                    <th>Đối tác</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="filtered.length === 0">
                    <tr>
                        <td colspan="7" class="at-empty">
                            <div class="at-empty-icon">&#128100;</div>
                            <div class="at-empty-text">Không có vận động viên nào.</div>
                        </td>
                    </tr>
                </template>
                <template x-for="athlete in filtered" :key="athlete.id">
                    <tr>
                        <td>
                            <input type="checkbox"
                                   :checked="isSelected(athlete.id)"
                                   @change="toggleSelect(athlete.id)">
                        </td>
                        <td>
                            <div class="at-name" x-text="athlete.athlete_name"></div>
                            <div class="at-sub" x-text="athlete.email"></div>
                            <div class="at-sub" x-show="athlete.phone" x-text="athlete.phone"></div>
                        </td>
                        <td x-text="athlete.category_name"></td>
                        <td>
                            <span x-show="athlete.is_doubles && athlete.partner_name" x-text="athlete.partner_name"></span>
                            <span x-show="!athlete.is_doubles || !athlete.partner_name" style="color:#94a3b8;">—</span>
                        </td>
                        <td>
                            <span class="at-badge"
                                  :class="{
                                      'at-badge-pending':  athlete.status === 'pending',
                                      'at-badge-approved': athlete.status === 'approved',
                                      'at-badge-rejected': athlete.status === 'rejected'
                                  }"
                                  x-text="statusLabel(athlete.status)">
                            </span>
                        </td>
                        <td>
                            <span class="at-badge"
                                  :class="{
                                      'at-badge-paid':   athlete.payment_status === 'paid',
                                      'at-badge-unpaid': athlete.payment_status !== 'paid'
                                  }"
                                  x-text="paymentLabel(athlete.payment_status)">
                            </span>
                        </td>
                        <td>
                            <div class="at-row-actions">
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
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    @include('home-yard.tournaments.partials._athletes-mobile-cards')

    {{-- Add / Edit Modal --}}
    @include('home-yard.tournaments.partials._athletes-modal')

</div>
