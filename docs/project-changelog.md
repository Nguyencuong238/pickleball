# Project Changelog

All notable changes to the Pickleball Platform project are documented in this file.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.14.0] - 2026-04-10

### Added — Gems Transfer + Escrow Refund Window
- **Mô hình chuyển Gems mới**: thay cho cơ chế đốt trực tiếp, Gems giờ được chuyển từ payer → owner (chủ sân/CLB) khi thanh toán dịch vụ.
- **Cửa sổ hoàn tiền 1 ngày (escrow)**: Gems bên nhận bị khóa trong `locked_balance` cho đến khi hết cửa sổ refund; sau đó được giải phóng tự động.
- **Clawback khi hoàn tiền**: hủy trong cửa sổ → payer được hoàn, owner bị thu hồi từ khóa. Hủy ngoài cửa sổ → HARD BLOCK với thông báo Tiếng Việt.
- **Console command `gems:release-locked`**: chạy mỗi 5 phút, quét và giải phóng các receipt đã đáo hạn. An toàn với race condition và idempotent.
- **Abstraction `App\Contracts\Payable`**: cho phép thêm dịch vụ mới (League, Tournament, ...) chỉ với ~10 LOC.
- **GemPaymentProcessor**: chỗ duy nhất điều phối pay/refund cho mọi Payable. Áp dụng cho `Booking` và `ClubActivity`.
- **UI ví Gems**: hiển thị 3 giá trị (Tổng / Có thể sử dụng / Đang khóa) + badge "Đang chờ giải phóng" cho receipt chưa mở khóa, định dạng tiếng Việt có dấu.
- **Feature flag `GEMS_TRANSFER_ENABLED`**: mặc định OFF. Có thể rollback tức thời bằng env var.

### Technical Details
- **Migrations**:
  - `gem_wallets.locked_balance` (unsigned int, default 0)
  - `gem_transactions` thêm: `counterparty_user_id`, `counterparty_transaction_id`, `platform_fee`, `available_at`, `released_at`
  - ENUM `type`/`status` → VARCHAR (mở rộng cho `receipt`, `refund_clawback`, `refunded`, future `withdraw_*`)
  - Unique index `ux_gem_tx_ref_type_user` chống trùng giao dịch
  - Index `idx_release_scan (type, available_at, released_at)` cho cron job
- **Service**: `GemWalletService::transfer/refund/releaseReceipt` + legacy fallback cho flag OFF
- **Exception**: `App\Exceptions\GemTransferException` với thông báo Tiếng Việt có dấu
- **Tests**: 18 feature tests mới trong `tests/Feature/Gems/*` (228 assertions)
- **Ghi chú rollout**: phí nền tảng (nếu bật) hiện đang bị đốt — không được ghi vào ví nền tảng. `SUM(platform_fee)` là doanh thu đã thu nhưng chưa materialize.
- **Ghi chú multi-server**: schedule dùng `withoutOverlapping` với `CACHE_DRIVER=file` — chỉ an toàn single-server. Khi scale sang nhiều server, đổi `CACHE_DRIVER=database` và bật `->onOneServer()`.
- **Mixed-mode data**: không backfill các giao dịch burn-model cũ. Hai mô hình coexist cho đến khi xóa nhánh legacy (follow-up).
- **Branch**: main

---

## [1.13.0] - 2026-04-08

### Added
- **Gems Payment for Club Activities** - Complete payment system for club social activities using in-app gems
  - `fee_gems` field on ClubActivity model (nullable, optional)
  - Gem deduction on RSVP when participant status confirmed
  - Gem refund on activity cancellation (within activity window)
  - Promote loop with intelligent skip for users with insufficient gems
  - Fee lock mechanism (prevent fee changes after >= 1 confirmed participant)
  - Deletion guard (prevent activity deletion with >= 1 confirmed participant)
  - Check-in gem deduction for open_play activities
  - Vietnamese diacritics (Tiếng Việt) in all UI text and service messages
  - GemPaymentService for orchestrating deductions and refunds
  - Updated ClubActivityService with gem handling logic
  - RSVP and cancel endpoints with gem transaction support

### Technical Details
- **Database**: Added `fee_gems` column to club_activities table
- **Services**: GemPaymentService, GemWalletService integration
- **Controllers**: ClubActivityController, ClubActivityRsvpController
- **Models**: ClubActivity, GemTransaction
- **Branch**: feat/gems-wallet

---

## [1.12.1] - 2026-04-03

### Added
- **Gems Manual Top-up with Admin Approval Workflow**
  - VietQR as default QR code generator (SePay as fallback)
  - Admin dashboard at `/admin/gem-topups` for top-up approval/rejection
  - Environment variables: `GEMS_BANK_ACCOUNT`, `GEMS_BANK_NAME`, `GEMS_BANK_HOLDER`, `GEMS_BANK_BRANCH`
  - GemTopupController for user and admin operations

### Changed
- GemWalletService methods: `cancelTopUp()`, `confirmTopUp()` for workflow management
- Vietnamese text on gem wallet pages

---

## [1.12.0] - 2025-04-06

### Added
- **Gems Wallet System** - In-app currency for premium features
  - User gem wallet with balance tracking
  - Gem transaction history with detailed records
  - Top-up functionality (integration-ready for payment gateways)
  - Gem deduction with atomic locking for race condition prevention
  - GemWalletService with comprehensive operations
  - GemTransaction model for audit trail
  - API endpoints for wallet operations

---

## [1.11.1] - 2026-03-22

### Added
- **Bracket Match Editor Enhancements**
  - Athlete reassignment with cascade warning alerts
  - Bracket slot swap functionality (supports null athletes and bye matches)
  - Match date field for scheduling flexibility
  - Cumulative game scores in rankings display
  - Rankings +/- column for win/loss differential tracking
  - User search by email or phone in athlete management

### Changed
- LIVE status now limited to 2-hour window after match start
- First bracket round allows all category athletes for wildcard flexibility
- Tournament form: `rules` renamed to `competition_rules`
- Added `event_timeline` field to tournament form
- Group standings uses `is_advanced` instead of `tournament_athletes`

---

## [1.11.0] - 2026-03-13

### Added
- **Single Elimination Knockout Bracket System**
  - Bracket generation with seeding algorithms
  - Winner advancement logic with BracketAdvancementTrait
  - Optional third-place match support (`enable_third_place` flag)
  - Bracket visualization with tree layout
  - Bracket match score entry and automatic progression
  - Bracket placement swap functionality
  - Services: KnockoutBracketService, BracketSeedingHelper, KnockoutMatchBuilder, KnockoutBracketQuery
  - JavaScript modules: bracket-manager, bracket-data-fetcher, bracket-score-entry, bracket-swap-editor
  - Bracket tree CSS styling
  - 4 API routes for bracket operations

### Technical Details
- **Database**: Added `enable_third_place` to tournaments table
- **Architecture**: Service layer with helper classes for separation of concerns

---

## [1.10.0] - 2026-03-13

### Added
- **Tournament Management Rewrite - Phase 1**
  - Modular controller architecture (7 controllers + 6 traits)
  - Comprehensive service layer (6 services + 3 helpers)
  - Alpine.js dashboard with responsive UI
  - Dashboard sidebar with navigation
  - Athletes management (CRUD, status, approval)
  - Draw/seeding (auto-assign, manual mode, SortableJS integration)
  - Group setup and management
  - Match management and scoring
  - Rankings and statistics display
  - 20+ Blade partials for component modularity
  - 8 JavaScript modules with mixin composition
  - 11 CSS stylesheets for responsive design

---

## [1.9.0] - 2026-03-09

### Added
- **League Registration System**
  - Payment proof upload with admin approval workflow
  - Phone number normalization in registration flow
  - Auto team generation with skill-ranked snake-draft and random pairing modes
  - LeagueAutoTeamService for race-condition safe operations
  - LeagueRegistrationService for complete registration workflow
  - Auto-create club post when activity is created

### Changed
- Added `join_request_status` to club show endpoint
- Activity detail page redesigned with modern tab-based UI
- Round editing capability in league management

### Security
- Pessimistic locking (lockForUpdate) for team generation consistency

---

## [1.8.0] - 2026-03-03

### Added
- **Club Activity Casual Matches System**
  - 3 match generation algorithms: singles round-robin, rotating doubles, fixed doubles
  - Per-player standings with win/loss tracking
  - 7 AJAX endpoints for match operations
  - 3 new database tables for match and standing management

---

## [1.7.0] - 2026-02-25

### Added
- **League Management System**
  - CRUD operations for leagues
  - Tab-based UI for league details
  - Team and player roster management
  - Auto-schedule generation
  - AJAX scoring with real-time updates
  - Standings calculation with proper sorting
  - MLP (Mixed Pickleball League) format support
  - 7 new database tables

---

## [1.6.0] - 2026-01-14

### Added
- **Point Earning System**
  - 16 task types for user engagement
  - Point wallet with balance tracking
  - Task submissions with image/link/QR code support
  - Social verification workflow
  - Special challenges and event-based earning
  - QR check-in for events
  - 8 new models for point tracking

---

## [1.5.1] - 2026-01-15

### Added
- **Gender-Aware Skill Level Mapping**
  - Female players receive +0.5 skill level boost
  - 8 total skill levels with Vietnamese/English names
  - Aligned with Vietnam pickleball standards

---

## [1.5.0] - 2026-01-03

### Added
- **Skill Assessment Quiz System**
  - 36 questions across 6 skill domains
  - Auto ELO calculation (range: 800-1400)
  - Fraud detection mechanisms
  - Cooldown policy enforcement
  - Admin flagging capability for suspicious attempts

---

## [1.4.0] - 2025-12-18

### Added
- **Doubles Pair Selection**
  - Partner selection interface
  - Pair linking mechanism
  - Draw order management
  - Category detection for doubles
  - Registration and match creation UIs

---

## [1.3.0] - 2025-12-09

### Added
- **Referee System**
  - Referee role with Spatie Permission
  - Referee profile fields (bio, status, rating, matches count)
  - Tournament referee assignment by Home Yard
  - Referee dashboard with statistics
  - Match assignment and filtering
  - Match score entry (set-by-set)
  - Auto-calculate winner from sets
  - Match status transitions (scheduled → in_progress → completed)
  - Public referee directory and profiles
  - 5 database changes

---

## [1.2.0] - 2025-12-07

### Added
- **User Profile Management**
  - Avatar upload and management
  - Location field for user profiles
  - Email and password update functionality
  - ProfileService for centralized profile operations
  - 5 API endpoints for profile management

---

## [1.1.0] - 2025-12-05

### Added
- **OPRS Multi-Component Rating System**
  - Three-component scoring: Elo (70%) + Challenge (20%) + Community (10%)
  - Seven OPR Levels: 1.0 to 5.0+ (Beginner to Elite)
  - Challenge System with 5 challenge types and point-based scoring
  - Community Activities tracking (check-ins, events, referrals, bonuses)
  - Real-time OPRS calculation
  - Change history audit log
  - Level-based leaderboards with filtering
  - Matchmaking with OPRS-similar opponents
  - Score breakdown visualization
  - Admin adjustment and management tools
  - 22 API routes
  - 3 new models: ChallengeResult, CommunityActivity, OprsHistory

---

## [1.0.0] - 2025-12-02

### Added
- **OCR (OnePickleball Championship Ranking) System**
  - Elo rating system (range: 100-3000+)
  - Seven rank tiers: Bronze to Grandmaster
  - Match challenge system (singles/doubles)
  - Match workflow: challenge → accept → play → submit → confirm
  - Global leaderboard with filtering
  - Achievement badge system (7 badge types)
  - Elo history tracking
  - Admin dispute resolution
  - Evidence upload for matches
  - K-factor adjustment based on player experience
  - Team Elo calculation for doubles
  - EloService for rating calculations

---

## [0.9.0] - 2025-11-15

### Added
- **Core Platform Features**
  - User authentication (email/password, OAuth)
  - Role-based access control (admin, home_yard, user)
  - Stadium and court management
  - Court booking system with dynamic pricing
  - Tournament system with categories and matches
  - Instructor platform with profiles and booking
  - Social activities and events
  - Video content library
  - News and CMS
  - Media library integration (Spatie)
  - Responsive frontend interface

---

## Notes

- **Vietnam Standards**: All user-facing text uses Vietnamese diacritics (Tiếng Việt)
- **Development Status**: Project actively maintained and extended with new features
- **Tech Stack**: Laravel 10, PHP 8.1+, MySQL 8.0+, Blade templates, Vite 5, Alpine.js

---

**Maintained By:** Development Team
**Last Updated:** 2026-04-08
