{{-- Tab 2: Lịch thi đấu (public read-only) --}}
@php
    $isMlp = $league->competition_format === 'mlp';
@endphp

@if($league->rounds->count() > 0)
    <div style="display: flex; flex-direction: column; gap: 20px;">
        @foreach($league->rounds->sortBy('round_number') as $round)
            <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                <div style="padding: 12px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <span style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-flag"></i> Vòng {{ $round->round_number }}
                    </span>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 4px; font-size: 0.85rem; color: #6b7280;">
                        @if($round->scheduled_date)
                            <span><i class="fas fa-calendar"></i> {{ $round->scheduled_date->format('d/m/Y') }}</span>
                        @endif
                        @if($round->scheduled_time)
                            <span><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($round->scheduled_time)->format('H:i') }}</span>
                        @endif
                        @if($round->venue)
                            <span><i class="fas fa-map-marker-alt"></i> {{ $round->venue }}</span>
                        @endif
                    </div>
                </div>

                @forelse($round->matches as $match)
                    <div style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <!-- Đội nhà -->
                            <div style="flex: 1; min-width: 120px; text-align: right; font-weight: 500; color: #1e293b;">
                                {{ $match->homeTeam->name ?? 'TBD' }}
                            </div>

                            <!-- Tỉ số -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                @if($match->status === 'completed')
                                    <span style="background: #15803d; color: white; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 0.95rem;">
                                        {{ $match->home_score }} - {{ $match->away_score }}
                                    </span>
                                @elseif($match->status === 'in_progress')
                                    <span style="background: #f59e0b; color: white; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 0.95rem;">
                                        {{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}
                                    </span>
                                @else
                                    <span style="background: #e2e8f0; color: #6b7280; padding: 4px 12px; border-radius: 6px; font-weight: 600; font-size: 0.95rem;">
                                        vs
                                    </span>
                                @endif
                            </div>

                            <!-- Đội khách -->
                            <div style="flex: 1; min-width: 120px; text-align: left; font-weight: 500; color: #1e293b;">
                                {{ $match->awayTeam->name ?? 'TBD' }}
                            </div>

                            <!-- Trạng thái -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                @switch($match->status)
                                    @case('scheduled')
                                        <span style="background-color: #f3f4f6; color: #4b5563; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Chưa đấu</span>
                                        @break
                                    @case('in_progress')
                                        <span style="background-color: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Đang đấu</span>
                                        @break
                                    @case('completed')
                                        <span style="background-color: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Hoàn thành</span>
                                        @break
                                    @case('cancelled')
                                        <span style="background-color: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Đã hủy</span>
                                        @break
                                @endswitch

                                @if($isMlp && $match->games->count() > 0)
                                    <button onclick="toggleMlpPublicDetails({{ $match->id }})" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; color: #475569;">
                                        <span id="mlp-pub-icon-{{ $match->id }}"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="2,4 6,8 10,4"/></svg></span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- MLP: Chi tiết sub-game (read-only) --}}
                        @if($isMlp && $match->games->count() > 0)
                            <div id="mlp-pub-details-{{ $match->id }}" style="display: none; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                                <div style="font-size: 0.85rem; color: #6b7280;">
                                    @foreach($match->games->sortBy('game_number') as $game)
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f8fafc; flex-wrap: wrap; gap: 4px;">
                                            <span style="color: #9ca3af; min-width: 60px;">Game {{ $game->game_number }}</span>
                                            <span style="flex: 1; text-align: right; color: #1e293b; font-weight: 500;">
                                                {{ optional(optional($game->homePlayer1)->user)->name ?? '?' }} + {{ optional(optional($game->homePlayer2)->user)->name ?? '?' }}
                                            </span>
                                            <span style="padding: 2px 8px; border-radius: 4px; font-weight: 700; min-width: 50px; text-align: center;
                                                {{ $game->status === 'completed' ? 'background: #f0fdf4; color: #15803d;' : 'background: #f3f4f6; color: #9ca3af;' }}">
                                                {{ $game->status === 'completed' ? $game->home_score . ' - ' . $game->away_score : '- - -' }}
                                            </span>
                                            <span style="flex: 1; text-align: left; color: #1e293b; font-weight: 500;">
                                                {{ optional(optional($game->awayPlayer1)->user)->name ?? '?' }} + {{ optional(optional($game->awayPlayer2)->user)->name ?? '?' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p style="padding: 15px 20px; color: #9ca3af; margin: 0;">Không có trận đấu trong vòng này</p>
                @endforelse
            </div>
        @endforeach
    </div>
@else
    <div style="text-align: center; padding: 40px 20px;">
        <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: #d1d5db; margin-bottom: 15px;"></i>
        <h4 style="color: #9ca3af; margin: 10px 0;">Chưa có lịch thi đấu</h4>
        <p style="color: #9ca3af;">Lịch thi đấu sẽ được cập nhật sớm.</p>
    </div>
@endif
