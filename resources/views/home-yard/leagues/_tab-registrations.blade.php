<!-- Status Filter Bar -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
    <button class="reg-filter-btn active" onclick="filterRegistrations('all', this)">Tất cả</button>
    <button class="reg-filter-btn" onclick="filterRegistrations('pending', this)">Chờ duyệt</button>
    <button class="reg-filter-btn" onclick="filterRegistrations('approved', this)">Đã duyệt</button>
    <button class="reg-filter-btn" onclick="filterRegistrations('rejected', this)">Từ chối</button>
</div>

<style>
    .reg-filter-btn { padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; color: #6b7280; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.2s; }
    .reg-filter-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
    .reg-filter-btn:hover { border-color: var(--primary-color); }
</style>

<!-- Registration List -->
<div id="registrationLoading" style="text-align: center; padding: 30px; color: #9ca3af;">
    <i class="fas fa-spinner fa-spin"></i> Đang tải...
</div>
<div id="registrationList"></div>
<div id="registrationEmpty" style="display:none; text-align:center; padding:40px; color:#9ca3af;">
    <i class="fas fa-clipboard-list" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
    Chưa có đăng ký nào
</div>

<!-- Registration Link -->
@if($league->status === 'registration')
<div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 10px; border: 1px solid #bae6fd;">
    <div style="font-weight: 600; color: #0369a1; margin-bottom: 8px;"><i class="fas fa-link"></i> Link đăng ký công khai</div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <input type="text" id="regLinkInput" value="{{ route('leagues.register', $league) }}" readonly style="flex:1; padding:8px 12px; border:1px solid #e2e8f0; border-radius:6px; font-size:0.85rem; background:#fff;">
        <button onclick="copyRegLink()" style="padding:8px 14px; background:var(--primary-color); color:white; border:none; border-radius:6px; cursor:pointer; font-size:0.85rem; white-space:nowrap;">
            <i class="fas fa-copy"></i> Sao chép
        </button>
    </div>
</div>
@endif

<!-- Approve/Reject Modal -->
<div id="regActionModal" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1100; display:none; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; padding:25px; width:90%; max-width:450px; margin:auto;">
        <h4 id="regActionTitle" style="margin:0 0 15px; color:#1e293b;"></h4>
        <div class="reg-field">
            <label style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b; font-size:0.9rem;" id="regNoteLabel">Ghi chú</label>
            <textarea id="regActionNote" rows="3" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; box-sizing:border-box;" placeholder="Ghi chú cho VĐV..."></textarea>
        </div>
        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:15px;">
            <button onclick="closeRegActionModal()" style="padding:8px 18px; background:#e2e8f0; color:#1e293b; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Hủy</button>
            <button id="regActionConfirmBtn" onclick="confirmRegAction()" style="padding:8px 18px; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Xác nhận</button>
        </div>
    </div>
</div>

<script>
var regCurrentFilter = 'all';
var regActionType = '';
var regActionId = null;
var regLeagueSlug = @js($league->slug);
var regCsrfToken = document.querySelector('meta[name="csrf-token"]').content;

function filterRegistrations(status, btn) {
    document.querySelectorAll('.reg-filter-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    regCurrentFilter = status;
    fetchRegistrations(status);
}

function fetchRegistrations(status) {
    document.getElementById('registrationLoading').style.display = 'block';
    document.getElementById('registrationList').innerHTML = '';
    document.getElementById('registrationEmpty').style.display = 'none';

    var url = '/homeyard/leagues/' + regLeagueSlug + '/registrations';
    if (status && status !== 'all') url += '?status=' + status;

    fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': regCsrfToken } })
    .then(function(r) { return r.json(); })
    .then(function(response) {
        document.getElementById('registrationLoading').style.display = 'none';
        var data = response.data || [];
        if (data.length === 0) {
            document.getElementById('registrationEmpty').style.display = 'block';
            return;
        }
        renderRegistrations(data);
    })
    .catch(function() {
        document.getElementById('registrationLoading').style.display = 'none';
        document.getElementById('registrationList').innerHTML = '<p style="color:#ef4444; text-align:center;">Lỗi tải dữ liệu</p>';
    });
}

function renderRegistrations(registrations) {
    var container = document.getElementById('registrationList');
    container.innerHTML = '';

    registrations.forEach(function(reg) {
        var statusColors = { pending: '#f59e0b', approved: '#22c55e', rejected: '#ef4444' };
        var statusLabels = { pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' };
        var statusColor = statusColors[reg.status] || '#6b7280';
        var statusLabel = statusLabels[reg.status] || reg.status;
        var createdDate = new Date(reg.created_at).toLocaleDateString('vi-VN');

        var card = document.createElement('div');
        card.style.cssText = 'border:1px solid #e2e8f0; border-radius:10px; margin-bottom:12px; overflow:hidden;';

        // Header
        var header = document.createElement('div');
        header.style.cssText = 'padding:12px 16px; background:#f8fafc; display:flex; justify-content:space-between; align-items:center; cursor:pointer; flex-wrap:wrap; gap:8px;';
        header.onclick = function() {
            var body = card.querySelector('.reg-body');
            body.style.display = body.style.display === 'none' ? 'block' : 'none';
        };
        header.innerHTML = '<div style="display:flex; align-items:center; gap:10px;">' +
            '<span style="background:' + statusColor + '; color:white; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:600;">' + statusLabel + '</span>' +
            '<span style="font-weight:600; color:#1e293b;">Nhóm #' + reg.id + '</span>' +
            '<span style="color:#9ca3af; font-size:0.85rem;">' + createdDate + '</span>' +
            '<span style="color:#6b7280; font-size:0.85rem;">' + reg.players.length + ' VĐV</span>' +
            '</div><i class="fas fa-chevron-down" style="color:#9ca3af;"></i>';

        // Body (collapsed by default)
        var body = document.createElement('div');
        body.className = 'reg-body';
        body.style.cssText = 'display:none; border-top:1px solid #e2e8f0; padding:16px;';

        // Payment proof
        if (reg.payment_proof) {
            body.innerHTML += '<div style="margin-bottom:15px;"><strong style="font-size:0.85rem; color:#475569;">Ảnh chuyển khoản:</strong><br>' +
                '<a href="' + window.storageUrl + reg.payment_proof + '" target="_blank"><img src="' + window.storageUrl + reg.payment_proof + '" style="max-width:200px; max-height:150px; border-radius:8px; margin-top:8px; border:1px solid #e2e8f0; cursor:pointer;"></a></div>';
        }

        // Players table
        var playersHtml = '<table style="width:100%; border-collapse:collapse; font-size:0.85rem; margin-bottom:15px;">' +
            '<thead><tr style="background:#fafafa;">' +
            '<th style="padding:8px 12px; text-align:left; color:#475569;">Tên</th>' +
            '<th style="padding:8px 12px; text-align:left; color:#475569;">SĐT</th>' +
            '<th style="padding:8px 12px; text-align:left; color:#475569;">Giới tính</th>' +
            '<th style="padding:8px 12px; text-align:left; color:#475569;">Điểm trình</th>' +
            '<th style="padding:8px 12px; text-align:left; color:#475569;">Tỉnh</th>' +
            '</tr></thead><tbody>';

        reg.players.forEach(function(p) {
            playersHtml += '<tr style="border-top:1px solid #f1f5f9;">' +
                '<td style="padding:8px 12px;">' + escapeHtml(p.name) + '</td>' +
                '<td style="padding:8px 12px;">' + escapeHtml(p.phone) + '</td>' +
                '<td style="padding:8px 12px;">' + (p.gender === 'male' ? 'Nam' : 'Nữ') + '</td>' +
                '<td style="padding:8px 12px;">' + escapeHtml(p.skill_level || '-') + '</td>' +
                '<td style="padding:8px 12px;">' + escapeHtml(p.province || '-') + '</td>' +
                '</tr>';
        });
        playersHtml += '</tbody></table>';
        body.innerHTML += playersHtml;

        // Admin note
        if (reg.admin_note) {
            body.innerHTML += '<div style="background:#fef3c7; padding:10px; border-radius:8px; margin-bottom:15px; font-size:0.85rem;"><strong>Ghi chú:</strong> ' + escapeHtml(reg.admin_note) + '</div>';
        }

        // Action buttons (only for pending)
        if (reg.status === 'pending') {
            body.innerHTML += '<div style="display:flex; gap:10px;">' +
                '<button onclick="openRegAction(\'approve\', ' + reg.id + ')" style="padding:8px 18px; background:#22c55e; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:0.85rem;"><i class="fas fa-check"></i> Duyệt</button>' +
                '<button onclick="openRegAction(\'reject\', ' + reg.id + ')" style="padding:8px 18px; background:#ef4444; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:0.85rem;"><i class="fas fa-times"></i> Từ chối</button>' +
                '</div>';
        }

        card.appendChild(header);
        card.appendChild(body);
        container.appendChild(card);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function openRegAction(type, regId) {
    regActionType = type;
    regActionId = regId;
    var modal = document.getElementById('regActionModal');
    var title = document.getElementById('regActionTitle');
    var noteLabel = document.getElementById('regNoteLabel');
    var btn = document.getElementById('regActionConfirmBtn');
    document.getElementById('regActionNote').value = '';

    if (type === 'approve') {
        title.textContent = 'Duyệt đăng ký #' + regId;
        noteLabel.textContent = 'Ghi chú (không bắt buộc)';
        btn.style.background = '#22c55e';
        btn.textContent = 'Duyệt';
    } else {
        title.textContent = 'Từ chối đăng ký #' + regId;
        noteLabel.textContent = 'Lý do từ chối *';
        btn.style.background = '#ef4444';
        btn.textContent = 'Từ chối';
    }
    modal.style.display = 'flex';
}

function closeRegActionModal() {
    document.getElementById('regActionModal').style.display = 'none';
}

function confirmRegAction() {
    var note = document.getElementById('regActionNote').value.trim();

    if (regActionType === 'reject' && !note) {
        toastr.error('Vui lòng nhập lý do từ chối.');
        return;
    }

    var url = '/homeyard/leagues/' + regLeagueSlug + '/registrations/' + regActionId + '/' + regActionType;

    fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': regCsrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ admin_note: note || null })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            toastr.success(data.message);
            closeRegActionModal();
            fetchRegistrations(regCurrentFilter);
        } else {
            toastr.error(data.message || 'Có lỗi xảy ra.');
        }
    })
    .catch(function() { toastr.error('Lỗi kết nối.'); });
}

function copyRegLink() {
    var input = document.getElementById('regLinkInput');
    input.select();
    document.execCommand('copy');
    toastr.success('Đã sao chép link đăng ký!');
}

// Backdrop click to close modal
document.getElementById('regActionModal').addEventListener('click', function(e) {
    if (e.target === this) closeRegActionModal();
});

// Auto-load on first tab view
var regLoaded = false;
function loadRegistrationsIfNeeded() {
    if (!regLoaded) {
        regLoaded = true;
        fetchRegistrations('all');
    }
}
</script>
