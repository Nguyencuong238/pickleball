{{-- Homeyard Rankings Overview --}}
@extends('layouts.homeyard')

@section('content')
<div class="main-content" style="padding:24px;flex:1;">
    <h2 style="font-size:1.3rem;font-weight:700;color:#1e293b;margin-bottom:20px;">Xếp hạng</h2>

    {{-- Stats cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#1e293b;" id="stat-athletes">--</div>
            <div style="font-size:0.8rem;color:#64748b;">Tổng VĐV</div>
        </div>
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#3b82f6;" id="stat-matches">--</div>
            <div style="font-size:0.8rem;color:#64748b;">Tổng trận</div>
        </div>
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#10b981;" id="stat-winrate">--</div>
            <div style="font-size:0.8rem;color:#64748b;">Tỉ lệ thắng TB</div>
        </div>
        <div style="text-align:center;padding:16px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:1.5rem;font-weight:700;color:#f59e0b;" id="stat-wins">--</div>
            <div style="font-size:0.8rem;color:#64748b;">Tổng thắng</div>
        </div>
    </div>

    <div style="background:#fff;border-radius:10px;border:1px solid #e2e8f0;padding:20px;">
        {{-- Toolbar --}}
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
            <select id="tournament-filter" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.85rem;color:#334155;background:#fff;min-width:180px;">
                <option value="">Tất cả giải đấu</option>
                @foreach($tournaments as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
            <select id="category-filter" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.85rem;color:#334155;background:#fff;min-width:160px;">
                <option value="">Tất cả nội dung</option>
                <option value="single_men">Đơn nam</option>
                <option value="single_women">Đơn nữ</option>
                <option value="double_men">Đôi nam</option>
                <option value="double_women">Đôi nữ</option>
                <option value="double_mixed">Đôi nam nữ</option>
            </select>
        </div>

        {{-- Loading --}}
        <div id="loading" style="display:none;text-align:center;padding:32px;color:#94a3b8;">
            Đang tải...
        </div>

        {{-- Desktop table --}}
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.85rem;" id="rankings-table">
                <thead>
                    <tr style="border-bottom:2px solid #e2e8f0;">
                        <th style="padding:10px 8px;text-align:center;color:#64748b;font-weight:600;width:50px;">#</th>
                        <th style="padding:10px 8px;text-align:left;color:#64748b;font-weight:600;">VĐV</th>
                        <th style="padding:10px 8px;text-align:center;color:#64748b;font-weight:600;">Điểm</th>
                        <th style="padding:10px 8px;text-align:center;color:#64748b;font-weight:600;">Trận</th>
                        <th style="padding:10px 8px;text-align:center;color:#64748b;font-weight:600;">Thắng</th>
                        <th style="padding:10px 8px;text-align:center;color:#64748b;font-weight:600;">Thua</th>
                        <th style="padding:10px 8px;text-align:center;color:#64748b;font-weight:600;">Tỉ lệ</th>
                        <th style="padding:10px 8px;text-align:center;color:#64748b;font-weight:600;">Số giải</th>
                    </tr>
                </thead>
                <tbody id="rankings-tbody">
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;">Đang tải...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div id="rankings-mobile" style="display:none;"></div>

        {{-- Pagination --}}
        <div id="pagination" style="display:flex;justify-content:center;gap:6px;margin-top:16px;"></div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        #rankings-table { display: none; }
        #rankings-mobile { display: block !important; }
    }
</style>
@endsection

@section('js')
<script>
(function() {
    var currentPage = 1;
    var statsUrl = '{{ route("homeyard.tournaments.stats") }}';
    var rankingsUrl = '{{ route("homeyard.tournaments.rankings.all") }}';

    function getFilters() {
        return {
            tournament_id: document.getElementById('tournament-filter').value,
            category_type: document.getElementById('category-filter').value
        };
    }

    function fetchStats() {
        var filters = getFilters();
        var params = new URLSearchParams();
        if (filters.tournament_id) params.set('tournament_id', filters.tournament_id);

        fetch(statsUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('stat-athletes').textContent = data.total_athletes || 0;
            document.getElementById('stat-matches').textContent = data.total_matches || 0;
            document.getElementById('stat-winrate').textContent = (data.avg_win_rate || 0) + '%';
            document.getElementById('stat-wins').textContent = data.total_wins || 0;
        })
        .catch(function() {});
    }

    function fetchRankings() {
        var filters = getFilters();
        var params = new URLSearchParams();
        if (filters.category_type) params.set('category_type', filters.category_type);
        params.set('page', currentPage);
        params.set('per_page', 15);

        document.getElementById('loading').style.display = 'block';

        fetch(rankingsUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                renderTable(res.standings || [], (currentPage - 1) * 15);
                renderMobile(res.standings || [], (currentPage - 1) * 15);
                renderPagination({ current_page: res.current_page, last_page: res.last_page, total: res.total });
            }
            document.getElementById('loading').style.display = 'none';
        })
        .catch(function() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('rankings-tbody').innerHTML =
                '<tr><td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;">Lỗi tải dữ liệu.</td></tr>';
        });
    }

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function rankBadge(rank) {
        if (rank === 1) return '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fef3c7;color:#d97706;font-weight:700;font-size:0.85rem;">1</span>';
        if (rank === 2) return '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#e2e8f0;color:#475569;font-weight:700;font-size:0.85rem;">2</span>';
        if (rank === 3) return '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fed7aa;color:#c2410c;font-weight:700;font-size:0.85rem;">3</span>';
        return '<span style="color:#64748b;">' + rank + '</span>';
    }

    function renderTable(data, offset) {
        var tbody = document.getElementById('rankings-tbody');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;">Chưa có dữ liệu xếp hạng.</td></tr>';
            return;
        }
        var html = '';
        data.forEach(function(item, i) {
            var rank = offset + i + 1;
            var name = item.athlete ? item.athlete.athlete_name : 'N/A';
            var catName = item.athlete ? (item.athlete.category_name || '') : '';
            html += '<tr style="border-bottom:1px solid #f1f5f9;">' +
                '<td style="padding:10px 8px;text-align:center;">' + rankBadge(rank) + '</td>' +
                '<td style="padding:10px 8px;"><div style="font-weight:600;color:#1e293b;">' + esc(name) + '</div>' +
                (catName ? '<div style="font-size:0.75rem;color:#94a3b8;">' + esc(catName) + '</div>' : '') + '</td>' +
                '<td style="padding:10px 8px;text-align:center;font-weight:700;color:#1e293b;">' + (item.points || 0) + '</td>' +
                '<td style="padding:10px 8px;text-align:center;color:#334155;">' + (item.matches_played || 0) + '</td>' +
                '<td style="padding:10px 8px;text-align:center;color:#10b981;font-weight:600;">' + (item.matches_won || 0) + '</td>' +
                '<td style="padding:10px 8px;text-align:center;color:#ef4444;">' + (item.matches_lost || 0) + '</td>' +
                '<td style="padding:10px 8px;text-align:center;color:#334155;">' + (item.win_rate || 0) + '%</td>' +
                '<td style="padding:10px 8px;text-align:center;color:#334155;">' + (item.tournaments_count || 0) + '</td>' +
                '</tr>';
        });
        tbody.innerHTML = html;
    }

    function renderMobile(data, offset) {
        var container = document.getElementById('rankings-mobile');
        if (!data.length) {
            container.innerHTML = '<div style="padding:32px;text-align:center;color:#94a3b8;">Chưa có dữ liệu xếp hạng.</div>';
            return;
        }
        var html = '';
        data.forEach(function(item, i) {
            var rank = offset + i + 1;
            var name = item.athlete ? item.athlete.athlete_name : 'N/A';
            var catName = item.athlete ? (item.athlete.category_name || '') : '';
            html += '<div style="padding:12px 0;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px;">' +
                '<div style="flex-shrink:0;">' + rankBadge(rank) + '</div>' +
                '<div style="flex:1;min-width:0;">' +
                '<div style="font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(name) + '</div>' +
                (catName ? '<div style="font-size:0.75rem;color:#94a3b8;">' + esc(catName) + '</div>' : '') +
                '<div style="font-size:0.8rem;color:#64748b;margin-top:2px;">' +
                '<span style="color:#10b981;font-weight:600;">' + (item.matches_won || 0) + 'W</span> - ' +
                '<span style="color:#ef4444;">' + (item.matches_lost || 0) + 'L</span>' +
                ' (' + (item.win_rate || 0) + '%)' +
                '</div></div>' +
                '<div style="text-align:right;flex-shrink:0;">' +
                '<div style="font-weight:700;color:#1e293b;font-size:1.1rem;">' + (item.points || 0) + '</div>' +
                '<div style="font-size:0.7rem;color:#94a3b8;">điểm</div>' +
                '</div></div>';
        });
        container.innerHTML = html;
    }

    function renderPagination(pg) {
        var el = document.getElementById('pagination');
        if (!pg || pg.last_page <= 1) { el.innerHTML = ''; return; }
        var html = '';
        for (var i = 1; i <= pg.last_page; i++) {
            var active = i === pg.current_page
                ? 'background:#1e293b;color:#fff;'
                : 'background:#f1f5f9;color:#334155;';
            html += '<button onclick="window._rankingsGoPage(' + i + ')" style="padding:6px 12px;border-radius:6px;border:none;cursor:pointer;font-size:0.85rem;' + active + '">' + i + '</button>';
        }
        html += '<span style="font-size:0.8rem;color:#94a3b8;align-self:center;margin-left:8px;">' + pg.total + ' kết quả</span>';
        el.innerHTML = html;
    }

    window._rankingsGoPage = function(page) {
        currentPage = page;
        fetchRankings();
    };

    document.getElementById('tournament-filter').addEventListener('change', function() {
        currentPage = 1;
        fetchStats();
        fetchRankings();
    });

    document.getElementById('category-filter').addEventListener('change', function() {
        currentPage = 1;
        fetchRankings();
    });

    // Initial load
    fetchStats();
    fetchRankings();
})();
</script>
@endsection
