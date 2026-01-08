# Laravel Implementation Patterns for Referee Module

**Date**: 2025-12-09
**Scope**: Spatie Permission multi-role setups, pivot patterns, dashboard UI, activity logging

---

## 1. Multi-Role Setup (home_yard + referee)

### Pattern: Multiple Roles Per User

Users can hold multiple roles simultaneously. Spatie handles this natively:

```php
// Assign multiple roles
$user->assignRole(['home_yard', 'referee']);

// Check roles
if ($user->hasRole(['home_yard', 'referee'])) {
    // User has at least one of these roles
}

// Check all roles
if ($user->hasAllRoles(['home_yard', 'referee'])) {
    // User has ALL of these roles
}
```

### Role Hierarchy Setup

```php
// In UserSeeder or RolePermissionSeeder
$homeYard = Role::firstOrCreate(['name' => 'home_yard', 'guard_name' => 'web']);
$referee = Role::firstOrCreate(['name' => 'referee', 'guard_name' => 'web']);

// Assign permissions to roles
$homeYard->givePermissionTo(['manage-stadium', 'manage-pricing']);
$referee->givePermissionTo(['manage-matches', 'submit-scores']);

// Assign user both roles
$user->assignRole(['home_yard', 'referee']);
```

**Key Point**: Use `assignRole()` with array for atomic multi-role assignment. Avoids partial state issues.

---

## 2. Pivot Table with Additional Fields (assigned_at, assigned_by)

### Schema: Enhanced Role Assignment

Spatie's default `model_has_roles` pivot table lacks metadata. Extend with custom fields:

```php
// Migration: extend role assignment pivot
Schema::create('model_has_roles', function (Blueprint $table) {
    $table->unsignedBigInteger('role_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');

    // NEW: Audit fields
    $table->timestamp('assigned_at')->useCurrent();
    $table->unsignedBigInteger('assigned_by')->nullable();

    $table->primary(['role_id', 'model_id', 'model_type']);
    $table->foreign('assigned_by')->references('id')->on('users');
});
```

### Custom Pivot Model

```php
// app/Models/RoleAssignment.php
use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleAssignment extends Pivot
{
    protected $table = 'model_has_roles';
    public $timestamps = false;
    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
```

### Modified User Model

```php
// Override role relationship to use custom pivot
public function roles()
{
    return $this->morphToMany(
        Role::class,
        'model',
        'model_has_roles',
        'model_id',
        'role_id',
        'id',
        'id'
    )->using(RoleAssignment::class)
     ->withPivot(['assigned_at', 'assigned_by'])
     ->withTimestamps();
}
```

### Assign with Audit Trail

```php
// Create role with pivot fields
$user->roles()->attach($roleId, [
    'assigned_at' => now(),
    'assigned_by' => auth()->id(),
]);
```

---

## 3. Dashboard Menu Conditional Display

### Blade Template Pattern

```blade
{{-- resources/views/layouts/navigation.blade.php --}}

<nav>
    <a href="/">Home</a>

    @can('view-admin-panel')
        <a href="/admin">Admin</a>
    @endcan

    @if(auth()->user()?->hasRole('home_yard'))
        <a href="/homeyard/dashboard">Stadium</a>
    @endif

    @if(auth()->user()?->hasRole('referee'))
        <a href="/referee/dashboard">Referee</a>
    @endif

    @if(auth()->user()?->hasAllRoles(['home_yard', 'referee']))
        <a href="/referee-assignments">Manage Referees</a>
    @endif
</nav>
```

### Using Spatie Menu Package

```php
// App/Providers/MenuServiceProvider.php
use Spatie\Menu\Menu;

Menu::macro('main', function () {
    return Menu::new()
        ->link('/', 'Home')
        ->linkIfCan('view-admin-panel', '/admin', 'Admin')
        ->linkIf(
            auth()->user()?->hasRole('home_yard'),
            '/homeyard/dashboard',
            'Stadium'
        )
        ->linkIf(
            auth()->user()?->hasRole('referee'),
            '/referee/dashboard',
            'Referee'
        );
});

// In view: {{ Menu::main()->render() }}
```

---

## 4. Activity Logging Best Practices

### Setup: Pivot Model Activity Logging

```php
// app/Models/RoleAssignment.php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RoleAssignment extends Pivot
{
    use LogsActivity;

    protected $table = 'model_has_roles';
    public $timestamps = false;
    protected $casts = ['assigned_at' => 'datetime'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['assigned_at', 'assigned_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

### Activity Log Query Examples

```php
// Get all role assignments for a user
$logs = activity()
    ->forSubject($user)
    ->where('log_name', 'default')
    ->latest()
    ->get();

// Get who assigned roles to user
$logs = activity()
    ->where('event', 'created')
    ->where('subject_type', RoleAssignment::class)
    ->where('causer_id', $userId)
    ->get();

// Full audit trail display
foreach ($logs as $log) {
    echo $log->causer->name . ' ' . $log->event . ' at ' . $log->created_at;
}
```

### Best Practices Summary

- **Immutable Logs**: Activity logs must never be edited/deleted (legal/compliance)
- **User Attribution**: Always track who made changes via `->causer()`
- **Pivot-Specific Logging**: Log assignment/revocation events separately
- **Retention**: Keep logs indefinitely or per compliance requirements
- **Performance**: Index `causer_id` and `subject_id` for query speed

---

## Implementation Checklist

- [ ] Extend `model_has_roles` migration with `assigned_at`, `assigned_by`
- [ ] Create custom `RoleAssignment` pivot model
- [ ] Update User model relationship to use custom pivot
- [ ] Add permission definitions (manage-stadium, manage-matches, etc.)
- [ ] Update dashboard navigation with role-based conditionals
- [ ] Setup activity logging on pivot model
- [ ] Create audit trail view/API endpoint
- [ ] Test multi-role scenarios (home_yard + referee)

---

## Key Sources

- [Spatie Permission: Basic Usage](https://spatie.be/docs/laravel-permission/v6/basic-usage/basic-usage)
- [Spatie Activitylog: Pivot Model Support](https://spatie.be/docs/laravel-activitylog/v4/advanced-usage/logging-model-events)
- [Spatie Menu: Conditional Items](https://spatie.be/docs/menu/v3/menus-in-your-laravel-app/conditional-items-based-on-permissions)
- [Laravel Auditing Package](https://laravel-auditing.com/)
