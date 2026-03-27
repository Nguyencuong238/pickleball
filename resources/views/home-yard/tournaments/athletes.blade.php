{{-- Homeyard Athletes Overview - server-side filtered --}}
@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-athletes.css') }}?v=1.1">
@endsection

@section('content')
<div class="main-content" style="padding:24px;flex:1;">
    <h2 style="font-size:1.3rem;font-weight:700;color:#1e293b;margin-bottom:20px;">Vận động viên</h2>

    {{-- Stats cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#1e293b;" id="stat-total">{{ $stats['total'] }}</div>
            <div style="font-size:0.8rem;color:#64748b;">Tổng cộng</div>
        </div>
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#10b981;" id="stat-approved">{{ $stats['approved'] }}</div>
            <div style="font-size:0.8rem;color:#64748b;">Đã duyệt</div>
        </div>
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#f59e0b;" id="stat-pending">{{ $stats['pending'] }}</div>
            <div style="font-size:0.8rem;color:#64748b;">Chờ duyệt</div>
        </div>
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#ef4444;" id="stat-rejected">{{ $stats['rejected'] }}</div>
            <div style="font-size:0.8rem;color:#64748b;">Từ chối</div>
        </div>
    </div>

    <div style="background:#fff;border-radius:10px;border:1px solid #e2e8f0;padding:20px;" id="athletes-container">
        {{-- Toolbar --}}
        <div class="at-toolbar">
            <div class="at-search-wrap">
                <span class="at-search-icon">&#128269;</span>
                <input type="text" class="at-search-input"
                       placeholder="Tìm theo tên, email..."
                       id="search-input">
            </div>
            <select class="at-category-select" id="tournament-filter">
                <option value="">Tất cả giải đấu</option>
                @foreach($tournaments as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filter tabs --}}
        <div class="at-filter-bar" id="filter-tabs">
            <button class="at-filter-tab active" data-status="">
                Tất cả (<span id="tab-total">{{ $stats['total'] }}</span>)
            </button>
            <button class="at-filter-tab tab-approved" data-status="approved">
                Đã duyệt (<span id="tab-approved">{{ $stats['approved'] }}</span>)
            </button>
            <button class="at-filter-tab tab-pending" data-status="pending">
                Chờ duyệt (<span id="tab-pending">{{ $stats['pending'] }}</span>)
            </button>
            <button class="at-filter-tab tab-rejected" data-status="rejected">
                Từ chối (<span id="tab-rejected">{{ $stats['rejected'] }}</span>)
            </button>
        </div>

        {{-- Loading --}}
        <div id="loading" style="display:none;text-align:center;padding:32px;color:#94a3b8;">
            Đang tải...
        </div>

        {{-- Desktop table --}}
        <div class="at-table-wrap">
            <table class="at-table">
                <thead>
                    <tr>
                        <th>Vận động viên</th>
                        <th>Giải đấu</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Thành tích</th>
                    </tr>
                </thead>
                <tbody id="athletes-tbody">
                    <tr>
                        <td colspan="6" class="at-empty">
                            <div class="at-empty-icon">&#128100;</div>
                            <div class="at-empty-text">Đang tải...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="at-cards-mobile" id="athletes-mobile"></div>

        {{-- Pagination --}}
        <div id="pagination" style="display:flex;justify-content:center;gap:6px;margin-top:16px;"></div>
    </div>
</div>
@endsection

@section('js')
<script>
(function() {
    var currentPage = 1;
    var searchTimer = null;
    var statusLabels = { pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' };
    var paymentLabels = { paid: 'Đã thanh toán', unpaid: 'Chưa thanh toán' };

    function getFilters() {
        return {
            search: document.getElementById('search-input').value.trim(),
            tournament_id: document.getElementById('tournament-filter').value,
            status: document.querySelector('#filter-tabs .at-filter-tab.active').dataset.status || '',
            page: currentPage
        };
    }

    function fetchAthletes() {
        var filters = getFilters();
        var params = new URLSearchParams();
        if (filters.search) params.set('search', filters.search);
        if (filters.tournament_id) params.set('tournament_id', filters.tournament_id);
        if (filters.status) params.set('status', filters.status);
        params.set('page', filters.page);

        document.getElementById('loading').style.display = 'block';

        fetch('{{ route("homeyard.athletes") }}?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            renderTable(res.data);
            renderMobile(res.data);
            renderPagination(res.pagination);
            document.getElementById('loading').style.display = 'none';
        })
        .catch(function() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('athletes-tbody').innerHTML =
                '<tr><td colspan="6" class="at-empty"><div class="at-empty-text">Lỗi tải dữ liệu.</div></td></tr>';
        });
    }

    function badgeClass(status) {
        return 'at-badge at-badge-' + status;
    }

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function renderTable(data) {
        var tbody = document.getElementById('athletes-tbody');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="at-empty">' +
                '<div class="at-empty-icon">&#128100;</div>' +
                '<div class="at-empty-text">Không có vận động viên nào.</div></td></tr>';
            return;
        }
        var html = '';
        data.forEach(function(a) {
            var perf = (a.matches_played > 0)
                ? '<div class="at-sub">' + a.matches_won + 'W - ' + a.matches_lost + 'L (' + a.win_rate + '%)</div>'
                : '<span style="color:#94a3b8;">--</span>';
            var payClass = a.payment_status === 'paid' ? 'at-badge at-badge-paid' : 'at-badge at-badge-unpaid';
            var payText = a.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
            html += '<tr>' +
                '<td><div class="at-name">' + esc(a.athlete_name) + '</div><div class="at-sub">' + esc(a.email) + '</div></td>' +
                '<td>' + esc(a.tournament_name) + '</td>' +
                '<td>' + esc(a.category_name) + '</td>' +
                '<td><span class="' + badgeClass(a.status) + '">' + (statusLabels[a.status] || a.status) + '</span></td>' +
                '<td><span class="' + payClass + '">' + payText + '</span></td>' +
                '<td>' + perf + '</td>' +
                '</tr>';
        });
        tbody.innerHTML = html;
    }

    function renderMobile(data) {
        var container = document.getElementById('athletes-mobile');
        if (!data.length) {
            container.innerHTML = '<div class="at-empty" style="padding:32px;text-align:center;color:#94a3b8;">Không có vận động viên nào.</div>';
            return;
        }
        var html = '';
        data.forEach(function(a) {
            var perf = (a.matches_played > 0) ? '<span>' + a.matches_won + 'W - ' + a.matches_lost + 'L</span>' : '';
            html += '<div class="at-card-mobile">' +
                '<div class="at-card-mobile-header"><div>' +
                '<div class="at-card-mobile-name">' + esc(a.athlete_name) + '</div>' +
                '<div class="at-card-mobile-cat">' + esc(a.tournament_name) + ' - ' + esc(a.category_name) + '</div>' +
                '</div><span class="' + badgeClass(a.status) + '">' + (statusLabels[a.status] || a.status) + '</span></div>' +
                '<div class="at-card-mobile-meta"><span>' + esc(a.email) + '</span>' + perf + '</div></div>';
        });
        container.innerHTML = html;
    }

    function renderPagination(pg) {
        var el = document.getElementById('pagination');
        if (pg.last_page <= 1) { el.innerHTML = ''; return; }
        var html = '';
        for (var i = 1; i <= pg.last_page; i++) {
            var active = i === pg.current_page
                ? 'background:#1e293b;color:#fff;'
                : 'background:#f1f5f9;color:#334155;';
            html += '<button onclick="window._athletesGoPage(' + i + ')" style="padding:6px 12px;border-radius:6px;border:none;cursor:pointer;font-size:0.85rem;' + active + '">' + i + '</button>';
        }
        html += '<span style="font-size:0.8rem;color:#94a3b8;align-self:center;margin-left:8px;">' + pg.total + ' kết quả</span>';
        el.innerHTML = html;
    }

    window._athletesGoPage = function(page) {
        currentPage = page;
        fetchAthletes();
    };

    // Event listeners
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() { currentPage = 1; fetchAthletes(); }, 400);
    });

    document.getElementById('tournament-filter').addEventListener('change', function() {
        currentPage = 1;
        fetchAthletes();
    });

    document.querySelectorAll('#filter-tabs .at-filter-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#filter-tabs .at-filter-tab').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            currentPage = 1;
            fetchAthletes();
        });
    });

    // Initial load
    fetchAthletes();
})();
</script>
@endsection
