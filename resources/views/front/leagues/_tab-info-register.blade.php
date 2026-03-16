{{-- Tab 1: Thông tin & Đăng ký --}}
<div class="reg-card" style="padding: 0; overflow: hidden;">
    <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 14px 20px;">
        <h3 style="margin: 0; color: #fff; font-size: 1rem; font-weight: 600;">
            Thông tin giải đấu
        </h3>
    </div>
    <div style="padding: 16px 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500;">Hạn đăng ký</div>
                    <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $league->registration_deadline ? $league->registration_deadline->format('d/m/Y H:i') : 'Chưa cập nhật' }}
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500;">Ngày bắt đầu</div>
                    <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                        {{ $league->start_date ? $league->start_date->format('d/m/Y') : 'Chưa cập nhật' }}
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500;">Ngày kết thúc</div>
                    <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                        {{ $league->end_date ? $league->end_date->format('d/m/Y') : 'Chưa cập nhật' }}
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500;">Sân thi đấu</div>
                    <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $league->club->name ?? 'Chưa cập nhật' }}
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500;">Số đội tối đa</div>
                    <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                        {{ $league->config['max_teams'] ?? 16 }}
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500;">Số VĐV mỗi đội</div>
                    <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                        {{ $league->config['max_players_per_team'] ?? 'Chưa cập nhật' }}
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px; grid-column: span 2;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500;">Hình thức thi đấu</div>
                    <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                        {{ $league->competition_format === 'mlp' ? 'MLP' : 'Truyền thống' }}
                    </div>
                </div>
            </div>
        </div>
        @if($league->description)
            <div style="margin-top: 12px; background: #f8fafc; border-radius: 8px; padding: 12px 14px; border-left: 3px solid var(--primary-color, #14b8a6);">
                <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 500; margin-bottom: 4px;">Mô tả</div>
                <p style="color: #475569; margin: 0; line-height: 1.6; font-size: 0.85rem;">{{ $league->description }}</p>
            </div>
        @endif
    </div>
</div>
@if($closed)
    <div style="background: white; border-radius: 12px; padding: 40px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
        <i class="fas fa-lock" style="font-size: 3rem; color: #d1d5db; margin-bottom: 15px;"></i>
        <h3 style="color: #6b7280; margin: 0 0 10px;">Đăng ký đã đóng</h3>
        <p style="color: #9ca3af; margin: 0;">Giải đấu hiện không nhận đăng ký mới.</p>
    </div>
@else


    <form method="POST" action="{{ route('leagues.register.store', $league) }}" enctype="multipart/form-data" id="registrationForm">
        @csrf

        <div class="{{ $league->required_players_per_registration > 1 ? 'players-grid' : '' }}">
            @for($i = 0; $i < $league->required_players_per_registration; $i++)
                <div class="reg-card">
                    <div style="display: flex; align-items: center; margin-bottom: 18px;">
                        <span class="player-number">VĐV {{ $i + 1 }}</span>
                        @if($i === 0)
                            <span class="captain-badge">Đội trưởng</span>
                        @endif
                    </div>

                    <div class="reg-row">
                        <div class="reg-field">
                            <label class="reg-label">Số điện thoại *</label>
                            <input type="tel" name="players[{{ $i }}][phone]" class="reg-input" required placeholder="0901234567" value="{{ old("players.{$i}.phone") }}">
                        </div>
                        <div class="reg-field">
                            <label class="reg-label">Họ và tên *</label>
                            <input type="text" name="players[{{ $i }}][name]" class="reg-input" required placeholder="Nguyễn Văn A" value="{{ old("players.{$i}.name") }}">
                        </div>
                    </div>

                    <div class="reg-row">
                        <div class="reg-field">
                            <label class="reg-label">Giới tính *</label>
                            <select name="players[{{ $i }}][gender]" class="reg-input" required>
                                <option value="male" {{ old("players.{$i}.gender") === 'female' ? '' : 'selected' }}>Nam</option>
                                <option value="female" {{ old("players.{$i}.gender") === 'female' ? 'selected' : '' }}>Nữ</option>
                            </select>
                        </div>
                        <div class="reg-field">
                            <label class="reg-label">Điểm trình</label>
                            <input type="text" name="players[{{ $i }}][skill_level]" class="reg-input" placeholder="VD: 3.5, 4.0" value="{{ old("players.{$i}.skill_level") }}">
                        </div>
                    </div>

                    <div class="reg-row">
                        <div class="reg-field">
                            <label class="reg-label">Tỉnh/Thành</label>
                            <input type="text" name="players[{{ $i }}][province]" class="reg-input" placeholder="VD: TP.HCM, Hà Nội" value="{{ old("players.{$i}.province") }}">
                        </div>
                        <div class="reg-field">
                            <label class="reg-label">Ngày sinh</label>
                            <input type="date" name="players[{{ $i }}][birthday]" class="reg-input" value="{{ old("players.{$i}.birthday") }}">
                        </div>
                    </div>

                    <div class="reg-field">
                        <label class="reg-label">Ảnh VĐV</label>
                        <input type="file" name="players[{{ $i }}][photo]" class="reg-input" accept="image/*">
                    </div>

                    <div class="reg-field" style="margin-bottom: 0;">
                        <label class="reg-label">Lời nhắn</label>
                        <textarea name="players[{{ $i }}][message]" class="reg-input" rows="2" maxlength="500" placeholder="Lời nhắn cho ban tổ chức...">{{ old("players.{$i}.message") }}</textarea>
                    </div>
                </div>
            @endfor
        </div>

        <!-- Payment Proof -->
        <div class="reg-card">
            @if($league->qr_code_image)
                <div style="margin-bottom: 20px; text-align: center; background: #f8fafc; padding: 15px; border-radius: 8px;">
                    <p style="margin: 0 0 10px; font-weight: 600; color: #1e293b;">Quét mã QR để thanh toán:</p>
                    <img src="{{ Storage::url($league->qr_code_image) }}" alt="QR Code" style="max-height: 250px; border-radius: 8px; border: 1px solid #e2e8f0; max-width: 100%;margin: 0 auto;">
                    @if($league->registration_fee)
                        @php $totalFee = $league->registration_fee * $league->required_players_per_registration; @endphp
                        <p style="margin: 10px 0 0; font-weight: 600;">Số tiền: <span style="color: red;">{{ number_format($totalFee) }}đ</span>
                            @if($league->required_players_per_registration > 1)
                                <span style="font-size: 0.8rem; color: #6b7280; font-weight: 400;">({{ number_format($league->registration_fee) }}đ x {{ $league->required_players_per_registration }} VĐV)</span>
                            @endif
                        </p>
                    @endif
                </div>
            @endif
            <h3 style="margin: 0 0 15px; color: #1e293b; font-size: 1rem;">
                <i class="fas fa-receipt"></i> Ảnh chuyển khoản
            </h3>
            <input type="file" name="payment_proof" class="reg-input" accept="image/*" id="paymentProofInput">
            <img id="paymentPreview" style="display:none; max-width:100%; margin-top:15px; border-radius:8px; border:1px solid #e2e8f0;">
            <p style="color: #9ca3af; font-size: 0.8rem; margin: 10px 0 0;">Chấp nhận ảnh JPG, PNG. Tối đa 5MB.</p>
        </div>

        <button type="submit" id="submitBtn" style="width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 10px; font-size: 1.05rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s;">
            Gửi đăng ký
        </button>
    </form>
@endif
