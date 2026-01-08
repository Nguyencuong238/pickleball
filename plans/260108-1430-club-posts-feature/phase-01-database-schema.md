# Phase 01: Database Schema

**Priority:** Critical
**Status:** Pending

---

## Context

- Existing `club_members` table has roles: `creator`, `admin`, `member`
- Need to add `moderator` role per spec
- Create 4 new tables for posts system

---

## Related Files

**Create:**
- `database/migrations/2026_01_08_000001_add_moderator_role_to_club_members.php`
- `database/migrations/2026_01_08_000002_create_club_posts_table.php`
- `database/migrations/2026_01_08_000003_create_club_post_media_table.php`
- `database/migrations/2026_01_08_000004_create_club_post_reactions_table.php`
- `database/migrations/2026_01_08_000005_create_club_post_comments_table.php`

---

## Implementation Steps

### Step 1: Add moderator role migration

```php
// database/migrations/2026_01_08_000001_add_moderator_role_to_club_members.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: Modify ENUM to include moderator
        DB::statement("ALTER TABLE club_members MODIFY COLUMN role ENUM('creator', 'admin', 'moderator', 'member') DEFAULT 'member'");
    }

    public function down(): void
    {
        // Revert: first update any moderators to members
        DB::table('club_members')->where('role', 'moderator')->update(['role' => 'member']);
        DB::statement("ALTER TABLE club_members MODIFY COLUMN role ENUM('creator', 'admin', 'member') DEFAULT 'member'");
    }
};
```

### Step 2: Create club_posts table

```php
// database/migrations/2026_01_08_000002_create_club_posts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->enum('visibility', ['public', 'members_only'])->default('public');
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->foreignId('pinned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Composite index for feed query
            $table->index(['club_id', 'is_pinned', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_posts');
    }
};
```

### Step 3: Create club_post_media table

```php
// database/migrations/2026_01_08_000003_create_club_post_media_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_post_id')->constrained('club_posts')->cascadeOnDelete();
            $table->enum('type', ['image', 'video', 'youtube']);
            $table->string('path')->nullable(); // for uploads
            $table->string('disk')->default('public'); // local, s3
            $table->string('youtube_url')->nullable();
            $table->unsignedInteger('size')->nullable(); // bytes
            $table->tinyInteger('order')->default(0);
            $table->timestamps();

            $table->index(['club_post_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_post_media');
    }
};
```

### Step 4: Create club_post_reactions table

```php
// database/migrations/2026_01_08_000004_create_club_post_reactions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_post_id')->constrained('club_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['like', 'love', 'fire']);
            $table->timestamps();

            $table->unique(['club_post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_post_reactions');
    }
};
```

### Step 5: Create club_post_comments table

```php
// database/migrations/2026_01_08_000005_create_club_post_comments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_post_id')->constrained('club_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('club_post_comments')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['club_post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_post_comments');
    }
};
```

---

## Todo List

- [ ] Create migration 1: add_moderator_role_to_club_members
- [ ] Create migration 2: create_club_posts_table
- [ ] Create migration 3: create_club_post_media_table
- [ ] Create migration 4: create_club_post_reactions_table
- [ ] Create migration 5: create_club_post_comments_table
- [ ] Run migrations: `php artisan migrate`
- [ ] Verify tables created with correct structure

---

## Success Criteria

- [ ] All 5 migrations run without error
- [ ] `club_members.role` accepts 'moderator' value
- [ ] Foreign keys and cascades work correctly
- [ ] Indexes created for performance

---

## Next Steps

Proceed to Phase 02: Models & Relationships
