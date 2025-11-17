# Tournament Authorization & Ownership System

## Overview
Implemented role-based tournament ownership and authorization system where:
- **Admin users** can view and manage ALL tournaments
- **Home Yard users** can only view and manage their OWN tournaments

## What Was Done

### 1. Created Tournament Policy
**File**: `app/Policies/TournamentPolicy.php`
- Defines authorization rules for tournament actions
- `view()`: Admins see all, home yard users see only their own
- `create()`: Only admins and home yard users can create
- `update()`: Admins can update all, home yard users only their own
- `delete()`: Admins can delete all, home yard users only their own

### 2. Registered Policy in AuthServiceProvider
**File**: `app/Providers/AuthServiceProvider.php`
- Registered `Tournament::class => TournamentPolicy::class`
- Enables `$this->authorize()` checks in controllers

### 3. Updated HomeYardTournamentController
**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`
- `index()`: Filters tournaments by `user_id` (only their own)
- `show()`: Uses `$this->authorize('view', $tournament)`
- `edit()`: Uses `$this->authorize('update', $tournament)`
- `update()`: Uses `$this->authorize('update', $tournament)`
- `destroy()`: Uses `$this->authorize('delete', $tournament)`
- `addAthlete()`: Uses `$this->authorize('update', $tournament)`
- `removeAthlete()`: Uses `$this->authorize('update', $tournament)`

### 4. Updated Admin TournamentController
**File**: `app/Http/Controllers/Admin/TournamentController.php`
- `index()`: Shows all tournaments for admins, only own for home yard users
- `show()`: Uses `$this->authorize('view', $tournament)`
- `edit()`: Uses `$this->authorize('update', $tournament)`
- `update()`: Uses `$this->authorize('update', $tournament)`
- `destroy()`: Uses `$this->authorize('delete', $tournament)`
- `addAthlete()`: Uses `$this->authorize('update', $tournament)`
- `removeAthlete()`: Uses `$this->authorize('update', $tournament)`

### 5. Updated HomeYard Dashboard
**File**: `resources/views/home-yard/dashboard.blade.php`
- Links now route to homeyard tournament pages
- Homeyard users only see their own tournaments

## Access Control

### Admin Panel (`/admin/tournaments`)
- ✅ Admins: See ALL tournaments, full CRUD access
- ✅ Home Yard: See only their tournaments in admin panel, full CRUD on their own
- ❌ Regular Users: No access

### HomeYard Panel (`/homeyard/tournaments`)
- ✅ Home Yard: See and manage their own tournaments
- ✅ Admins: Also have access but typically use admin panel
- ❌ Regular Users: No access

## Database Structure

The `tournaments` table already has:
- `user_id` - Foreign key to the user who owns the tournament
- Used for filtering and authorization

## How It Works

### Example: Home Yard User Accessing Tournament
1. Home yard user goes to `/homeyard/tournaments`
2. Controller queries: `Tournament::where('user_id', auth()->id())`
3. Only tournaments they created are shown
4. When editing/viewing tournament, policy checks: `$tournament->user_id === $user->id`
5. If not owner, get "This action is unauthorized" error

### Example: Admin Accessing Tournament
1. Admin goes to `/admin/tournaments`
2. Controller checks: `if ($user->hasRole('admin')) { return all tournaments }`
3. Admin can see and edit ALL tournaments
4. Policy allows any action for admins: `if ($user->hasRole('admin')) { return true; }`

## Error Messages

If unauthorized user tries to access:
- Direct URL: `/admin/tournaments/123` (not their tournament)
- Result: "This action is unauthorized" error with 403 status

To prevent this, always use the index pages which filter by user_id.

## Future Improvements

1. Add custom error page for 403 unauthorized errors
2. Add audit logging of who modified which tournaments
3. Add tournament sharing/collaboration features
4. Add request to modify tournament (approval system)
