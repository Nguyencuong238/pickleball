# Phase 1: Database & Models

## Context Links
- [Brainstorm](../reports/brainstorm-260309-1103-league-registration-flow.md)
- [Existing league migration](../../database/migrations/2026_02_25_001_create_leagues_table.php)
- [Existing league_team_players migration](../../database/migrations/2026_02_25_003_create_league_team_players_table.php)

## Overview
- **Priority**: P1 (foundation for all other phases)
- **Status**: pending
- **Effort**: 2h

## Key Insights
- `leagues` table already has `registration_deadline` column -- no need to add it again
- Need 2 new tables + 2 new columns on `leagues` + 2 new models
- Follow existing pattern: `$fillable` arrays, enum columns, FK constraints

## Requirements

### Functional
- Store registration groups with payment proof image
- Store individual player info per registration
- Link players to existing users by phone match
- Track approval status per registration group

### Non-functional
- FK constraints with cascadeOnDelete
- Index on frequently queried columns (league_id, status, phone)

## Architecture

### New Tables

**`league_registrations`**
| Column | Type | Note |
|--------|------|------|
| id | bigint PK | auto-increment |
| league_id | FK leagues | cascadeOnDelete |
| payment_proof | varchar(255) | storage path to uploaded image |
| status | enum(pending,approved,rejected) | default: pending |
| admin_note | text nullable | note when approving/rejecting |
| created_at, updated_at | timestamps | |

**`league_registration_players`**
| Column | Type | Note |
|--------|------|------|
| id | bigint PK | auto-increment |
| league_registration_id | FK | cascadeOnDelete |
| user_id | FK users nullable | linked after phone match/create |
| phone | varchar(20) | primary match key |
| name | varchar(255) | VDV display name |
| skill_level | varchar(50) nullable | diem trinh |
| province | varchar(100) nullable | tinh thanh |
| gender | enum(male,female) | |
| birthday | date nullable | |
| photo | varchar(255) nullable | VDV photo path |
| message | text nullable | loi nhan |
| created_at | timestamp | |

**New columns on `leagues`**
| Column | Type | Note |
|--------|------|------|
| required_players_per_registration | tinyint unsigned, default 1 | 1, 2, or 4 |
| registration_fee | decimal(12,0) nullable | VND amount |

> Note: `registration_deadline` already exists in leagues table.

## Related Code Files

### Files to CREATE
- `database/migrations/2026_03_09_create_league_registrations_table.php`
- `database/migrations/2026_03_09_create_league_registration_players_table.php`
- `database/migrations/2026_03_09_add_registration_fields_to_leagues_table.php`
- `app/Models/LeagueRegistration.php`
- `app/Models/LeagueRegistrationPlayer.php`

### Files to MODIFY
- `app/Models/League.php` -- add `registrations` relationship, add new columns to $fillable

## Implementation Steps

1. **Create migration: `league_registrations`**
   - FK to leagues with cascadeOnDelete
   - enum status with default 'pending'
   - Index on `league_id` and `status`

2. **Create migration: `league_registration_players`**
   - FK to league_registrations with cascadeOnDelete
   - FK to users nullable (nullOnDelete)
   - Index on `phone` for matching
   - Composite index `[league_registration_id, phone]`

3. **Create migration: add columns to `leagues`**
   - `required_players_per_registration` tinyint unsigned default 1 after `registration_deadline`
   - `registration_fee` decimal(12,0) nullable after `required_players_per_registration`

4. **Create `LeagueRegistration` model**
   ```php
   $fillable = ['league_id', 'payment_proof', 'status', 'admin_note'];
   $casts = ['status' => 'string'];
   // Relations: league(), players()
   ```

5. **Create `LeagueRegistrationPlayer` model**
   ```php
   $fillable = ['league_registration_id', 'user_id', 'phone', 'name', 'skill_level', 'province', 'gender', 'birthday', 'photo', 'message'];
   $casts = ['birthday' => 'date', 'gender' => 'string'];
   // Relations: registration(), user()
   ```

6. **Update `League` model**
   - Add `required_players_per_registration`, `registration_fee` to `$fillable`
   - Add `registrations()` HasMany relationship
   - Add `registration_fee` to `$casts` as `decimal:0`

## Todo List
- [ ] Create league_registrations migration
- [ ] Create league_registration_players migration
- [ ] Create add-registration-fields-to-leagues migration
- [ ] Create LeagueRegistration model
- [ ] Create LeagueRegistrationPlayer model
- [ ] Update League model ($fillable, relationship)
- [ ] Run `php artisan migrate` to verify

## Success Criteria
- All 3 migrations run without error
- Models have correct relationships and fillable
- `League::find(1)->registrations` returns collection
- `LeagueRegistration::find(1)->players` returns collection

## Risk Assessment
- **Risk**: `registration_deadline` already exists -- do NOT add again
- **Mitigation**: Migration only adds `required_players_per_registration` and `registration_fee`

## Security
- payment_proof stored as path only, not raw file content
- user_id nullable to handle cases where user not yet created
