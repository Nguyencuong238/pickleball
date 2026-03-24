@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/club-activity-members.css') }}">
@endsection

@section('content')
<div class="ca-members-page" x-data="caMembers({
    addUrl: '{{ route('club.members.add', $club->slug) }}',
    oprsUrlBase: '{{ url('clubs/' . $club->slug . '/members-management') }}',
})">
    {{-- Top bar --}}
    <div class="ca-members-topbar">
        <h2 class="ca-members-title">Thành viên - {{ $club->name }}</h2>
        <button class="ca-btn-sm ca-btn-primary" @click="drawerOpen = true">Thêm thành viên</button>
    </div>

    {{-- Search --}}
    <div class="ca-members-search">
        <input type="text" class="ca-search-input" placeholder="Tìm kiếm theo tên..." x-model="search">
    </div>

    {{-- Table --}}
    <div class="ca-members-table-wrap">
        <table class="ca-members-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên</th>
                    <th>Điện thoại</th>
                    <th>Vai trò</th>
                    <th>OPRS ban đầu</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $idx => $member)
                <tr x-show="!search || '{{ strtolower($member->name) }}'.includes(search.toLowerCase())">
                    <td data-label="#">{{ $members->firstItem() + $idx }}</td>
                    <td data-label="Tên">{{ $member->name }}</td>
                    <td data-label="Điện thoại">{{ $member->phone ?? '-' }}</td>
                    <td data-label="Vai trò">{{ $member->pivot->role }}</td>
                    <td data-label="OPRS" class="ca-oprs-cell"
                        x-data="{ editing: false, val: '{{ $member->pivot->initial_oprs ?? '' }}' }">
                        <template x-if="!editing">
                            <span @dblclick="editing = true" class="ca-oprs-value" x-text="val || '-'"></span>
                        </template>
                        <template x-if="editing">
                            <input type="number" step="0.01" class="ca-oprs-input" x-model="val"
                                   @blur="editing = false; updateOprs({{ $member->id }}, val)"
                                   @keydown.enter="editing = false; updateOprs({{ $member->id }}, val)">
                        </template>
                    </td>
                    <td data-label="Trạng thái">
                        <select class="ca-status-select"
                                @change="updateStatus({{ $member->id }}, $event.target.value)">
                            <option value="active" {{ ($member->pivot->member_status ?? 'active') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ ($member->pivot->member_status ?? '') === 'inactive' ? 'selected' : '' }}>Tạm nghỉ</option>
                            <option value="suspended" {{ ($member->pivot->member_status ?? '') === 'suspended' ? 'selected' : '' }}>Đình chỉ</option>
                        </select>
                    </td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $members->links() }}

    {{-- Add member drawer --}}
    <div class="ca-drawer-overlay" x-show="drawerOpen" x-transition.opacity @click="drawerOpen = false"></div>
    <div class="ca-drawer" :class="{ 'is-open': drawerOpen }">
        <div class="ca-drawer-header">
            <h3>Thêm thành viên</h3>
            <button class="ca-drawer-close" @click="drawerOpen = false">&times;</button>
        </div>
        <div class="ca-drawer-body">
            <div class="ca-form-group">
                <label class="ca-label">Số điện thoại</label>
                <input type="tel" class="ca-input" x-model="newPhone" placeholder="0912 345 678">
            </div>
            <div class="ca-form-group">
                <label class="ca-label">OPRS ban đầu</label>
                <input type="number" step="0.01" class="ca-input" x-model="newOprs" placeholder="0.00">
            </div>
            <p class="ca-error-msg" x-show="addError" x-text="addError"></p>
            <p class="ca-success-msg" x-show="addSuccess" x-text="addSuccess"></p>
            <button class="ca-btn-primary ca-btn-full" @click="addMember()" :disabled="addLoading">
                <span x-show="!addLoading">Thêm</span>
                <span x-show="addLoading">Đang thêm...</span>
            </button>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('assets/js/club-activity-members.js') }}"></script>
@endsection
