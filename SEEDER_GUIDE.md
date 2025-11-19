# Database Seeder Guide

## Overview
The comprehensive database seeder creates a complete, realistic tournament scenario with Vietnamese names and data to help you understand how all database tables work together.

---

## 🚀 How to Run

### Fresh Install (Recommended)
```bash
# Drop all tables, run migrations, and seed
php artisan migrate:fresh --seed
```

### Re-seed Existing Database
```bash
# Clear existing data and re-seed
php artisan db:seed --force
```

⚠️ **Warning**: This will delete all existing data!

---

## 📊 What Gets Created

### 🎾 Tournament Scenario
**"Giải Pickleball Mở Rộng TP.HCM 2025"**
- Code: `PB-HCM-2025`
- Dates: January 20-22, 2025
- Location: Sân Pickleball Thảo Điền
- Prize Pool: 50,000,000 VND
- Status: In Progress (Quarterfinals stage)

### 👥 Users (15 total)
1. **Admin**: admin@pickleball.vn / password
2. **Organizer**: organizer@pickleball.vn / password (owns tournament)
3. **Stadium Owner**: stadium@pickleball.vn / password (owns venue)
4. **Athletes** (12): athlete1-12@pickleball.vn / password

### 🏟️ Venue
**Sân Pickleball Thảo Điền**
- 8 courts (4 indoor, 4 outdoor)
- Acrylic surface
- 4.8/5 rating
- Full amenities

### 🏆 Tournament Structure

#### Categories (3)
1. **Nam đơn 18+** (Men's Singles 18+)
   - 32 athletes
   - Prize: 20,000,000 VND

2. **Nữ đơn 35+** (Women's Singles 35+)
   - 16 athletes
   - Prize: 15,000,000 VND

3. **Đôi nam nữ** (Mixed Doubles)
   - 16 teams
   - Prize: 15,000,000 VND

#### Rounds
- **Vòng bảng** (Group Stage) - Completed
- **Tứ kết** (Quarterfinals) - In Progress (6/8 matches done)
- **Bán kết** (Semifinals) - Pending

#### Groups (4 per category)
- Bảng A, B, C, D
- 8 athletes per group
- Top 2 advance to knockout stage

### 👤 Athletes (32 for Men's Singles)
Vietnamese names with realistic data:
- Nguyễn Văn An (⭐ #1 seed, Bảng A)
- Trần Văn Bình (#2 seed, Bảng A)
- Lê Văn Cường (#3 seed, Bảng A)
- ... and 29 more

**Payment Status Distribution:**
- ✅ Most athletes: Paid
- ⏳ Some: Pending
- ❌ Few: Unpaid

**Confirmation Status:**
- ✅ Most: Approved
- ⏳ Some: Awaiting confirmation

### 🎾 Matches (100+ matches)

#### Group Stage (Completed)
- Round-robin format
- All athletes play each other
- Realistic scores: 11-7, 11-9, 11-5
- Sets stored as JSON
- All matches assigned to courts

#### Quarterfinals (In Progress)
- Top 2 from each group
- 6 matches completed
- 2 matches in progress

### 📊 Rankings
**Group Standings** calculated with:
- Rank position (1-8 in each group)
- Matches: Played, Won, Lost
- Win rate percentage
- Points (3 per win)
- Sets won/lost with differential
- Games won/lost with differential

Top 2 in each group marked as **advanced to quarterfinals** ✅

### 💰 Payments
- Unique payment references (PAY-XXXXXXXXXX)
- Multiple payment methods:
  - Bank transfer
  - MoMo
  - ZaloPay
  - VnPay
- Payment statuses: completed, pending, unpaid
- Amount: 500,000 VND per registration

---

## 🔍 Understanding the Data

### Tournament Flow
```
1. Registration
   ├─ Athletes register (tournament_athletes)
   └─ Payments processed (payments)

2. Draw/Seeding
   ├─ Athletes assigned seeds (seed_number)
   ├─ Athletes placed in groups (group_id)
   └─ Groups created (groups)

3. Group Stage
   ├─ Matches created (matches)
   ├─ Matches played on courts (court_id)
   ├─ Scores recorded (set_scores JSON)
   └─ Standings calculated (group_standings)

4. Knockout Stage
   ├─ Top athletes advance (is_advanced = true)
   ├─ Quarterfinal matches created
   └─ Winners progress to semifinals
```

### Key Relationships to Explore

#### 1. Tournament → Categories → Athletes
```sql
-- Get all categories in a tournament
SELECT * FROM tournament_categories WHERE tournament_id = 1;

-- Get athletes in a category
SELECT * FROM tournament_athletes WHERE category_id = 1;
```

#### 2. Groups → Standings → Athletes
```sql
-- Get group standings for Bảng A
SELECT
    gs.rank_position,
    ta.athlete_name,
    gs.matches_played,
    gs.matches_won,
    gs.win_rate,
    gs.points
FROM group_standings gs
JOIN tournament_athletes ta ON gs.athlete_id = ta.id
WHERE gs.group_id = 1
ORDER BY gs.rank_position;
```

#### 3. Matches with Scores
```sql
-- Get completed matches with scores
SELECT
    m.match_number,
    m.athlete1_name,
    m.athlete2_name,
    m.final_score,
    m.set_scores,
    c.court_name
FROM matches m
JOIN courts c ON m.court_id = c.id
WHERE m.status = 'completed'
LIMIT 10;
```

#### 4. Athletes with Payment Status
```sql
-- Get athletes and their payment status
SELECT
    ta.athlete_name,
    ta.payment_status,
    ta.status as confirmation_status,
    p.payment_method,
    p.paid_at
FROM tournament_athletes ta
LEFT JOIN payments p ON p.tournament_athlete_id = ta.id
WHERE ta.tournament_id = 1;
```

---

## 📝 Example Queries to Explore

### 1. Get Tournament Overview
```php
$tournament = Tournament::with([
    'categories',
    'rounds',
    'athletes',
])->first();
```

### 2. Get Group Standings
```php
$standings = GroupStanding::with('athlete')
    ->where('group_id', 1)
    ->orderBy('rank_position')
    ->get();
```

### 3. Get Match Results
```php
$matches = Match::with(['athlete1', 'athlete2', 'winner', 'court'])
    ->where('status', 'completed')
    ->get();
```

### 4. Get Payment Summary
```php
$summary = Payment::selectRaw('
    status,
    COUNT(*) as count,
    SUM(amount) as total
')
->groupBy('status')
->get();
```

---

## 🎨 Visual Data Structure

```
Tournament: "Giải Pickleball Mở Rộng TP.HCM 2025"
│
├─ Categories
│  ├─ Nam đơn 18+ (32 athletes)
│  │  ├─ Rounds
│  │  │  ├─ Vòng bảng (completed)
│  │  │  ├─ Tứ kết (in progress)
│  │  │  └─ Bán kết (pending)
│  │  │
│  │  ├─ Groups
│  │  │  ├─ Bảng A (8 athletes)
│  │  │  │  ├─ Standings (ranked 1-8)
│  │  │  │  └─ Matches (28 completed)
│  │  │  ├─ Bảng B (8 athletes)
│  │  │  ├─ Bảng C (8 athletes)
│  │  │  └─ Bảng D (8 athletes)
│  │  │
│  │  └─ Athletes
│  │     ├─ Nguyễn Văn An (#1, paid, Bảng A)
│  │     ├─ Trần Văn Bình (#2, paid, Bảng A)
│  │     └─ ... 30 more
│  │
│  ├─ Nữ đơn 35+ (16 athletes)
│  └─ Đôi nam nữ (16 teams)
│
├─ Courts (8 courts at Thảo Điền)
│  ├─ Sân số 1 (indoor)
│  ├─ Sân số 2 (indoor)
│  └─ ... 6 more
│
├─ Matches (100+ matches)
│  ├─ Group stage (completed)
│  │  └─ Scores: 11-7, 11-5 (JSON format)
│  └─ Quarterfinals (in progress)
│
└─ Payments (32 payments)
   ├─ Completed (most)
   ├─ Pending (some)
   └─ Unpaid (few)
```

---

## 🧪 Testing Scenarios

### 1. View Tournament Dashboard
Access the tournament as organizer and see:
- All categories populated
- Athletes assigned to groups
- Matches scheduled on courts
- Live scores in progress

### 2. Check Group Standings
- See rankings calculated correctly
- Top 2 athletes marked as advanced
- Points, sets, and games tallied

### 3. View Match Results
- See completed matches with scores
- Check set-by-set scoring (JSON format)
- View court assignments

### 4. Payment Management
- See payment status badges
- Filter by paid/unpaid athletes
- Check payment methods distribution

---

## 🔧 Customization

To modify the seeded data, edit `database/seeders/DatabaseSeeder.php`:

### Add More Athletes
```php
// Line 333-342: Expand the $maleNames array
$maleNames = [
    'Your Name Here',
    // ... add more names
];
```

### Change Tournament Dates
```php
// Line 154-156
'start_date' => '2025-01-20',
'end_date' => '2025-01-22',
```

### Adjust Prize Money
```php
// Line 170
'prizes' => 50000000,  // Change total prize

// Line 186
'prize_money' => 20000000,  // Change category prize
```

---

## 📖 Learning Path

1. **Start here**: Run the seeder and explore user accounts
2. **Understand structure**: Check tournament → categories → groups
3. **Follow the flow**: Registration → Draw → Matches → Rankings
4. **Explore relationships**: See how foreign keys connect tables
5. **Test queries**: Try the example queries above
6. **Modify data**: Edit seeder and re-run to see changes

---

## 🎯 Key Takeaways

✅ Complete tournament lifecycle from registration to finals
✅ Realistic Vietnamese names and data
✅ All table relationships demonstrated
✅ Payment tracking with multiple statuses
✅ Group stage with calculated standings
✅ Knockout progression showing bracket advancement
✅ JSON storage for set scores (no separate table needed)
✅ Court scheduling and assignments
✅ Seeding system (#1, #2, #3...)

---

## 📧 Test Credentials

All passwords are: `password`

| Role | Email | Purpose |
|------|-------|---------|
| Admin | admin@pickleball.vn | Full system access |
| Organizer | organizer@pickleball.vn | Create/manage tournaments |
| Stadium Owner | stadium@pickleball.vn | Manage venues |
| Athlete | athlete1@pickleball.vn | Register and compete |

---

**Happy exploring! 🎾**

For questions about the database design, see:
- `DATABASE_SCHEMA.md` - Complete schema reference
- `SCHEMA_REVIEW_CHANGES.md` - Why we simplified
