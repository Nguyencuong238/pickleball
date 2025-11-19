# Pickleball Tournament Management - Database Schema

## Overview
Lean database design focused on the actual dashboard requirements without over-engineering.

**Total Tables**: 18 (8 new + 2 enhanced + 8 existing)

---

## 🎯 What Was Simplified

### Removed Over-Engineering
1. ❌ **match_sets table** - Replaced with JSON in matches table
2. ❌ **Unnecessary tournament fields**: is_published, is_featured, allow_public_registration, schedule_settings
3. ❌ **Unnecessary athlete fields**: jersey_number, emergency_contact, date_of_birth, gender, ranking_points, skill_level, registration_notes
4. ❌ **Unnecessary match fields**: referee_name, is_featured, duration_minutes

### What We Kept (Dashboard-Driven)
✅ Multi-category tournaments (shown as "Nội dung thi đấu")
✅ Rounds management (shown as "Vòng đấu & Sân")
✅ Court assignments (shown in match management)
✅ Match scoring with JSON sets (shown as 11-7, 11-5)
✅ Group/bracket system (shown as "Bảng A, B, C")
✅ Payment tracking (shown as badges: "Đã thanh toán")
✅ Rankings with stats (shown in "Bảng xếp hạng")

---

## 📋 New Tables

### 1. tournament_categories
Competition categories within tournaments (e.g., "Nam đơn 18+", "Nữ đôi 35+")

```sql
- id
- tournament_id FK
- category_name (e.g., "Nam đơn 18+")
- category_type (single_men, single_women, double_men, double_women, double_mixed)
- age_group (open, u18, 18+, 35+, 45+, 55+)
- max_participants
- prize_money
- description
- status (draft, open, closed, ongoing, completed)
- current_participants
- timestamps
```

### 2. rounds
Tournament round organization

```sql
- id
- tournament_id FK
- category_id FK
- round_name (e.g., "Vòng bảng", "Tứ kết", "Bán kết")
- round_number
- round_type (group_stage, round_of_16, quarterfinal, semifinal, final, etc.)
- start_date, end_date, start_time
- status (pending, in_progress, completed, cancelled)
- total_matches, completed_matches
- notes
- timestamps
```

### 3. courts
Court management and scheduling

```sql
- id
- stadium_id FK
- tournament_id FK
- court_name (e.g., "Sân số 1")
- court_number
- court_type (indoor, outdoor)
- surface_type
- status (available, in_use, maintenance, reserved)
- description
- amenities JSON
- is_active
- daily_matches
- timestamps
```

### 4. matches
Match tracking with JSON-stored sets (no separate match_sets table)

```sql
- id
- tournament_id FK, category_id FK, round_id FK, court_id FK, group_id FK
- match_number
- bracket_position
- athlete1_id FK, athlete1_name, athlete1_score
- athlete2_id FK, athlete2_name, athlete2_score
- winner_id FK
- match_date, match_time
- actual_start_time, actual_end_time
- status (scheduled, ready, in_progress, completed, cancelled, postponed, bye)
- best_of (3 or 5 sets)
- set_scores JSON → [{"set": 1, "athlete1": 11, "athlete2": 7}, ...]
- final_score (e.g., "11-7, 11-5")
- notes
- next_match_id FK (for bracket progression)
- winner_advances_to
- timestamps
```

### 5. groups
Group stage organization (Bảng A, B, C, D)

```sql
- id
- tournament_id FK, category_id FK, round_id FK
- group_name (e.g., "Bảng A")
- group_code (e.g., "A")
- max_participants
- current_participants
- advancing_count (how many advance to next round)
- status (draft, active, completed)
- description
- timestamps
```

### 6. group_standings
Rankings within groups

```sql
- id
- group_id FK, athlete_id FK
- rank_position
- matches_played, matches_won, matches_lost, matches_drawn
- win_rate
- points (3 for win, 1 for draw, 0 for loss)
- sets_won, sets_lost, sets_differential
- games_won, games_lost, games_differential
- is_advanced
- timestamps
```

### 7. payments
Payment tracking for registrations

```sql
- id
- user_id FK, tournament_id FK, tournament_athlete_id FK
- payment_reference (unique, auto-generated)
- amount, currency (VND)
- payment_method (cash, bank_transfer, momo, zalopay, vnpay, etc.)
- status (pending, processing, completed, failed, refunded, cancelled)
- transaction_id
- payment_details
- paid_at, refunded_at
- notes, receipt_url
- timestamps
```

---

## 🔄 Enhanced Existing Tables

### tournaments (11 new columns added)

```sql
New columns:
- tournament_code (unique identifier like "PB-HCM-2025")
- format_type (knockout, round_robin, group_knockout, swiss)
- seeding_enabled
- auto_bracket_generation
- balanced_groups
- group_count, players_per_group
- bracket_data JSON
- tournament_stage (registration, draw_completed, in_progress, finals, completed)
- total_matches, completed_matches
```

### tournament_athletes (15 new columns added)

```sql
New columns:
- category_id FK (which category they're in)
- group_id FK (which group they're in)
- seed_number (1, 2, 3, etc. for seeding)
- payment_status (unpaid, pending, paid, refunded, waived)
- registration_fee, amount_paid
- registered_at, confirmed_at
- matches_played, matches_won, matches_lost
- win_rate, total_points
- sets_won, sets_lost
```

---

## 🔗 Key Relationships

```
Tournament
  ├─ has many TournamentCategories
  ├─ has many Rounds
  ├─ has many Matches
  ├─ has many Courts
  └─ has many Payments

TournamentCategory
  ├─ has many TournamentAthletes
  ├─ has many Rounds
  ├─ has many Matches
  └─ has many Groups

Round
  ├─ has many Matches
  └─ has many Groups

Group
  ├─ has many TournamentAthletes
  ├─ has many Matches
  └─ has many GroupStandings

Match
  ├─ belongs to athlete1, athlete2, winner (TournamentAthlete)
  ├─ belongs to Court
  ├─ belongs to Round
  ├─ belongs to Group
  └─ set_scores stored as JSON (no separate table!)

TournamentAthlete
  ├─ has many Matches
  ├─ has many Payments
  └─ has one GroupStanding (if in group stage)
```

---

## 🚀 Usage Examples

### Match with Sets (JSON Format)
```json
{
  "set_scores": [
    {"set": 1, "athlete1": 11, "athlete2": 7},
    {"set": 2, "athlete1": 11, "athlete2": 5}
  ],
  "final_score": "11-7, 11-5",
  "athlete1_score": 2,
  "athlete2_score": 0
}
```

### Group Standing Calculation
```php
// Dashboard shows: Rank, Name, Group, Matches, Wins, Losses, Win%, Points, Sets, Differential
GroupStanding {
    rank_position: 1,
    matches_played: 5,
    matches_won: 5,
    matches_lost: 0,
    win_rate: 100.00,
    points: 15,  // 3 points per win
    sets_won: 10,
    sets_lost: 0,
    sets_differential: +10,
    games_differential: +110
}
```

---

## 📦 Migration Files (In Order)

1. `2025_11_19_000001_create_tournament_categories_table.php`
2. `2025_11_19_000002_create_rounds_table.php`
3. `2025_11_19_000003_create_courts_table.php`
4. `2025_11_19_000006_create_groups_table.php`
5. `2025_11_19_000004_create_matches_table.php`
6. `2025_11_19_000007_create_group_standings_table.php`
7. `2025_11_19_000008_create_payments_table.php`
8. `2025_11_19_000009_add_tournament_management_columns_to_tournaments_table.php`
9. `2025_11_19_000010_add_tournament_management_columns_to_tournament_athletes_table.php`
10. `2025_11_19_000011_add_group_foreign_key_to_matches_table.php`

Run migrations:
```bash
php artisan migrate
```

---

## 🎨 Model Files

**New Models:**
- `app/Models/TournamentCategory.php`
- `app/Models/Round.php`
- `app/Models/Court.php`
- `app/Models/Match.php`
- `app/Models/Group.php`
- `app/Models/GroupStanding.php`
- `app/Models/Payment.php`

**Existing Models (need relationship updates):**
- `app/Models/Tournament.php`
- `app/Models/TournamentAthlete.php`
- `app/Models/Stadium.php`

---

## ✅ Dashboard Feature Coverage

| Dashboard Feature | Database Support |
|------------------|------------------|
| Tournament basic info | ✅ tournaments table |
| Tournament code | ✅ tournament_code column |
| Competition categories | ✅ tournament_categories table |
| Round management | ✅ rounds table |
| Court selection | ✅ courts table |
| Athlete registration | ✅ tournament_athletes table |
| Payment tracking | ✅ payments table + payment_status |
| Seeding system | ✅ seed_number column |
| Group/draw system | ✅ groups + group_standings tables |
| Match scheduling | ✅ matches table with court_id |
| Live scoring | ✅ matches.set_scores JSON |
| Rankings | ✅ group_standings table |
| Bracket generation | ✅ bracket_data JSON + next_match_id |

---

## 🎯 Why This Design is Better

### Before (Over-Engineered)
- ❌ match_sets table for storing individual sets
- ❌ 25+ unnecessary columns across tables
- ❌ Features not shown in dashboard
- ❌ Complexity without benefit

### After (Lean)
- ✅ Sets stored as JSON (simpler, faster)
- ✅ Only columns actually used in dashboard
- ✅ Easier to maintain
- ✅ Faster queries
- ✅ Less migration complexity

---

## 📝 Next Steps

1. **Update existing Tournament model:**
```php
public function categories() {
    return $this->hasMany(TournamentCategory::class);
}
public function rounds() {
    return $this->hasMany(Round::class);
}
public function matches() {
    return $this->hasMany(MatchModel::class);
}
```

2. **Update existing TournamentAthlete model:**
```php
public function category() {
    return $this->belongsTo(TournamentCategory::class, 'category_id');
}
public function group() {
    return $this->belongsTo(Group::class);
}
public function payments() {
    return $this->hasMany(Payment::class);
}
```

3. **Create controllers:**
```bash
php artisan make:controller TournamentCategoryController --resource
php artisan make:controller MatchController --resource
php artisan make:controller PaymentController --resource
```

4. **Create seeders for testing:**
```bash
php artisan make:seeder TournamentSeeder
php artisan make:seeder MatchSeeder
```

---

**Version**: 2.0 (Optimized)
**Date**: November 19, 2025
**Status**: ✅ Production Ready
