@if($league->standings->count() > 0)
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569; width: 50px;">#</th>
                    <th style="padding: 12px 15px; text-align: left; font-weight: 600; color: #475569;">Đội</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569;">Trận</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569;">Thắng</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569;">Thua</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569;">Hòa</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569;">GW</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569;">GL</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #475569;">GD</th>
                    <th style="padding: 12px 15px; text-align: center; font-weight: 700; color: #1e293b;">Điểm</th>
                </tr>
            </thead>
            <tbody>
                @foreach($league->standings as $standing)
                    @php
                        $isTopTwo = $standing->rank <= 2;
                        $rowBg = $isTopTwo ? '#f0fdf4' : 'white';
                    @endphp
                    <tr style="border-bottom: 1px solid #e2e8f0; background: {{ $rowBg }};">
                        <td style="padding: 12px 15px; text-align: center; font-weight: 700; color: {{ $isTopTwo ? '#15803d' : '#6b7280' }};">
                            {{ $standing->rank }}
                        </td>
                        <td style="padding: 12px 15px; font-weight: 500; color: #1e293b;">
                            {{ $standing->team->name ?? 'N/A' }}
                        </td>
                        <td style="padding: 12px 15px; text-align: center; color: #6b7280;">{{ $standing->played }}</td>
                        <td style="padding: 12px 15px; text-align: center; color: #15803d; font-weight: 600;">{{ $standing->wins }}</td>
                        <td style="padding: 12px 15px; text-align: center; color: #991b1b;">{{ $standing->losses }}</td>
                        <td style="padding: 12px 15px; text-align: center; color: #6b7280;">{{ $standing->draws }}</td>
                        <td style="padding: 12px 15px; text-align: center; color: #6b7280;">{{ $standing->games_won }}</td>
                        <td style="padding: 12px 15px; text-align: center; color: #6b7280;">{{ $standing->games_lost }}</td>
                        <td style="padding: 12px 15px; text-align: center; color: #6b7280;">{{ $standing->games_won - $standing->games_lost }}</td>
                        <td style="padding: 12px 15px; text-align: center; font-weight: 700; font-size: 1.1rem; color: #1e293b;">{{ $standing->points }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div style="text-align: center; padding: 40px 20px;">
        <i class="fas fa-list-ol" style="font-size: 2.5rem; color: #d1d5db; margin-bottom: 15px;"></i>
        <h4 style="color: #9ca3af; margin: 10px 0;">Chưa có bảng xếp hạng</h4>
        <p style="color: #9ca3af;">Bảng xếp hạng sẽ được tạo tự động khi tạo lịch thi đấu</p>
    </div>
@endif
