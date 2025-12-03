@extends('layouts.front')

@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 2rem 0;
        color: white;
    }

    .page-breadcrumb {
        font-size: 0.875rem;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }

    .page-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    .create-section {
        padding: 2rem 0;
    }

    .create-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        max-width: 700px;
        margin: 0 auto;
        overflow: hidden;
    }

    .create-card-header {
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        padding: 1.5rem;
        color: white;
        text-align: center;
    }

    .create-card-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .create-card-body {
        padding: 2rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    .match-type-selector {
        display: flex;
        gap: 1rem;
    }

    .match-type-option {
        flex: 1;
        padding: 1.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .match-type-option:hover {
        border-color: var(--primary-color);
    }

    .match-type-option.selected {
        border-color: var(--primary-color);
        background: rgba(0, 217, 181, 0.05);
    }

    .match-type-option input {
        display: none;
    }

    .match-type-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .match-type-label {
        font-weight: 700;
        color: #1e293b;
    }

    .match-type-desc {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    .user-search-container {
        position: relative;
    }

    .user-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 0.5rem 0.5rem;
        max-height: 250px;
        overflow-y: auto;
        z-index: 100;
        display: none;
    }

    .user-search-results.show {
        display: block;
    }

    .user-search-item {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .user-search-item:hover {
        background: #f8fafc;
    }

    .user-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .user-details {
        flex: 1;
    }

    .user-name-sm {
        font-weight: 600;
        color: #1e293b;
    }

    .user-meta-sm {
        font-size: 0.75rem;
        color: #64748b;
    }

    .selected-user {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
    }

    .selected-user .remove-btn {
        margin-left: auto;
        color: #ef4444;
        cursor: pointer;
        font-size: 1.25rem;
    }

    .partner-fields {
        display: none;
    }

    .partner-fields.show {
        display: block;
    }

    .form-row {
        display: flex;
        gap: 1rem;
    }

    .form-row .form-group {
        flex: 1;
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 700;
    }

    .form-hint {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    @media (max-width: 640px) {
        .match-type-selector {
            flex-direction: column;
        }

        .form-row {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('ocr.index') }}">OCR</a> /
            <a href="{{ route('ocr.matches.index') }}">Tran Dau</a> /
            Tao Moi
        </p>
        <h1 class="page-title">[PLUS] Tao Tran Dau Moi</h1>
    </div>
</section>

<section class="create-section">
    <div class="container">
        <div class="create-card">
            <div class="create-card-header">
                <h2>[GAME] Thach Dau Elo</h2>
            </div>
            <div class="create-card-body">
                <form action="{{ route('api.ocr.matches.store') }}" method="POST" id="createMatchForm">
                    @csrf

                    {{-- Match Type --}}
                    <div class="form-section">
                        <h3 class="form-section-title">[TYPE] Loai Tran Dau</h3>
                        <div class="match-type-selector">
                            <label class="match-type-option selected" data-type="singles">
                                <input type="radio" name="match_type" value="singles" checked>
                                <div class="match-type-icon">[1v1]</div>
                                <div class="match-type-label">Tran Don</div>
                                <div class="match-type-desc">1 vs 1</div>
                            </label>
                            <label class="match-type-option" data-type="doubles">
                                <input type="radio" name="match_type" value="doubles">
                                <div class="match-type-icon">[2v2]</div>
                                <div class="match-type-label">Tran Doi</div>
                                <div class="match-type-desc">2 vs 2</div>
                            </label>
                        </div>
                    </div>

                    {{-- Opponent Selection --}}
                    <div class="form-section">
                        <h3 class="form-section-title">[USERS] Chon Doi Thu</h3>

                        <div class="form-group">
                            <label class="form-label">Doi Thu <span class="required">*</span></label>
                            <div class="user-search-container">
                                <input type="text" class="form-control" id="opponentSearch"
                                       placeholder="Tim kiem theo ten hoac email..." autocomplete="off">
                                <div class="user-search-results" id="opponentResults"></div>
                            </div>
                            <input type="hidden" name="opponent_id" id="opponentId" required>
                            <div id="selectedOpponent" class="selected-user" style="display: none;">
                                <div class="user-avatar-sm" id="opponentAvatar"></div>
                                <div class="user-details">
                                    <div class="user-name-sm" id="opponentName"></div>
                                    <div class="user-meta-sm" id="opponentMeta"></div>
                                </div>
                                <span class="remove-btn" onclick="removeOpponent()">[X]</span>
                            </div>
                        </div>

                        <div class="partner-fields" id="partnerFields">
                            <div class="form-group">
                                <label class="form-label">Dong Doi Cua Ban</label>
                                <div class="user-search-container">
                                    <input type="text" class="form-control" id="partnerSearch"
                                           placeholder="Tim kiem dong doi..." autocomplete="off">
                                    <div class="user-search-results" id="partnerResults"></div>
                                </div>
                                <input type="hidden" name="challenger_partner_id" id="partnerId">
                                <div id="selectedPartner" class="selected-user" style="display: none;">
                                    <div class="user-avatar-sm" id="partnerAvatar"></div>
                                    <div class="user-details">
                                        <div class="user-name-sm" id="partnerName"></div>
                                        <div class="user-meta-sm" id="partnerMeta"></div>
                                    </div>
                                    <span class="remove-btn" onclick="removePartner()">[X]</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Dong Doi Cua Doi Thu</label>
                                <div class="user-search-container">
                                    <input type="text" class="form-control" id="opponentPartnerSearch"
                                           placeholder="Tim kiem dong doi doi thu..." autocomplete="off">
                                    <div class="user-search-results" id="opponentPartnerResults"></div>
                                </div>
                                <input type="hidden" name="opponent_partner_id" id="opponentPartnerId">
                                <div id="selectedOpponentPartner" class="selected-user" style="display: none;">
                                    <div class="user-avatar-sm" id="opponentPartnerAvatar"></div>
                                    <div class="user-details">
                                        <div class="user-name-sm" id="opponentPartnerName"></div>
                                        <div class="user-meta-sm" id="opponentPartnerMeta"></div>
                                    </div>
                                    <span class="remove-btn" onclick="removeOpponentPartner()">[X]</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Match Details --}}
                    <div class="form-section">
                        <h3 class="form-section-title">[INFO] Chi Tiet Tran Dau</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Ngay Thi Dau</label>
                                <input type="date" class="form-control" name="scheduled_date"
                                       min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Gio Thi Dau</label>
                                <input type="time" class="form-control" name="scheduled_time">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Dia Diem</label>
                            <input type="text" class="form-control" name="location"
                                   placeholder="VD: San Pickleball ABC, Quan 1">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ghi Chu</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="Thong tin them ve tran dau..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary submit-btn">
                        Gui Loi Thach Dau
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<script>
    // Match type selection
    document.querySelectorAll('.match-type-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.match-type-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');

            const type = this.dataset.type;
            const partnerFields = document.getElementById('partnerFields');

            if (type === 'doubles') {
                partnerFields.classList.add('show');
            } else {
                partnerFields.classList.remove('show');
            }
        });
    });

    // User search functionality
    function setupUserSearch(inputId, resultsId, hiddenId, selectedId, avatarId, nameId, metaId) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        const hidden = document.getElementById(hiddenId);
        const selected = document.getElementById(selectedId);
        const avatar = document.getElementById(avatarId);
        const name = document.getElementById(nameId);
        const meta = document.getElementById(metaId);

        let debounceTimer;

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                results.classList.remove('show');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('ocr.search-users') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(users => {
                        if (users.length === 0) {
                            results.innerHTML = '<div style="padding: 1rem; color: #94a3b8; text-align: center;">Khong tim thay</div>';
                        } else {
                            results.innerHTML = users.map(user => `
                                <div class="user-search-item" data-user='${JSON.stringify(user)}'>
                                    <div class="user-avatar-sm">${user.name.charAt(0).toUpperCase()}</div>
                                    <div class="user-details">
                                        <div class="user-name-sm">${user.name}</div>
                                        <div class="user-meta-sm">Elo: ${user.elo_rating} | ${user.elo_rank || 'Unranked'}</div>
                                    </div>
                                </div>
                            `).join('');

                            results.querySelectorAll('.user-search-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    const user = JSON.parse(this.dataset.user);
                                    hidden.value = user.id;
                                    avatar.textContent = user.name.charAt(0).toUpperCase();
                                    name.textContent = user.name;
                                    meta.textContent = `Elo: ${user.elo_rating} | ${user.elo_rank || 'Unranked'}`;
                                    selected.style.display = 'flex';
                                    input.style.display = 'none';
                                    results.classList.remove('show');
                                });
                            });
                        }
                        results.classList.add('show');
                    });
            }, 300);
        });

        input.addEventListener('blur', function() {
            setTimeout(() => results.classList.remove('show'), 200);
        });

        input.addEventListener('focus', function() {
            if (this.value.length >= 2) {
                results.classList.add('show');
            }
        });
    }

    // Setup search for all user fields
    setupUserSearch('opponentSearch', 'opponentResults', 'opponentId', 'selectedOpponent', 'opponentAvatar', 'opponentName', 'opponentMeta');
    setupUserSearch('partnerSearch', 'partnerResults', 'partnerId', 'selectedPartner', 'partnerAvatar', 'partnerName', 'partnerMeta');
    setupUserSearch('opponentPartnerSearch', 'opponentPartnerResults', 'opponentPartnerId', 'selectedOpponentPartner', 'opponentPartnerAvatar', 'opponentPartnerName', 'opponentPartnerMeta');

    // Remove selected users
    function removeOpponent() {
        document.getElementById('opponentId').value = '';
        document.getElementById('selectedOpponent').style.display = 'none';
        document.getElementById('opponentSearch').style.display = 'block';
        document.getElementById('opponentSearch').value = '';
    }

    function removePartner() {
        document.getElementById('partnerId').value = '';
        document.getElementById('selectedPartner').style.display = 'none';
        document.getElementById('partnerSearch').style.display = 'block';
        document.getElementById('partnerSearch').value = '';
    }

    function removeOpponentPartner() {
        document.getElementById('opponentPartnerId').value = '';
        document.getElementById('selectedOpponentPartner').style.display = 'none';
        document.getElementById('opponentPartnerSearch').style.display = 'block';
        document.getElementById('opponentPartnerSearch').value = '';
    }

    // Form validation
    document.getElementById('createMatchForm').addEventListener('submit', function(e) {
        const opponentId = document.getElementById('opponentId').value;
        if (!opponentId) {
            e.preventDefault();
            alert('Vui long chon doi thu');
            return false;
        }

        const matchType = document.querySelector('input[name="match_type"]:checked').value;
        if (matchType === 'doubles') {
            const partnerId = document.getElementById('partnerId').value;
            const opponentPartnerId = document.getElementById('opponentPartnerId').value;
            if (!partnerId || !opponentPartnerId) {
                e.preventDefault();
                alert('Vui long chon dong doi cho ca hai doi');
                return false;
            }
        }
    });
</script>
@endsection
