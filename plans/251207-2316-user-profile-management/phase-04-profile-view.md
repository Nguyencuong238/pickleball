# Phase 04: Profile Edit View

## Context Links

- Parent: [plan.md](./plan.md)
- Previous: [phase-03-profile-controller.md](./phase-03-profile-controller.md)
- Docs: [code-standards.md](../../docs/code-standards.md)

## Overview

**Date**: 2025-12-07
**Description**: Create profile edit view with forms for profile info, avatar, email, and password
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Key Insights

- Follow existing dashboard view styling (gradient teal theme)
- Responsive design required (mobile-first)
- Separate forms for each section (allows individual submissions)
- Avatar preview with upload and remove options (using Laravel Storage URL)
- Province selector dropdown

## Requirements

### Functional
- Profile info form (name, location, province)
- Avatar section with preview, upload, remove
- Email change form (with password confirmation)
- Password change form (current + new + confirm)
- Success/error flash messages
- Form validation error display

### Non-Functional
- Match existing dashboard UI styling
- Vietnamese labels and messages
- Responsive layout
- No emoji icons (use text/svg icons)

## Architecture

```
resources/views/user/profile/
└── edit.blade.php

Sections:
1. Header - Profile title
2. Avatar Card - Preview, upload, remove
3. Basic Info Card - Name, location, province
4. Email Card - Email, current password
5. Password Card - Current, new, confirm
```

## Related Code Files

### Files to Create
| File | Action | Description |
|------|--------|-------------|
| `resources/views/user/profile/edit.blade.php` | Create | Profile edit form view |

### Reference Files (for styling)
| File | Purpose |
|------|---------|
| `resources/views/user/dashboard.blade.php` | UI styling reference |
| `resources/views/layouts/front.blade.php` | Layout extends |

## Implementation Steps

### Step 1: Create directory
```bash
mkdir -p resources/views/user/profile
```

### Step 2: Create edit.blade.php

```blade
@extends('layouts.front')

@section('content')
<style>
    .profile-container {
        padding: clamp(20px, 3vw, 40px);
        max-width: 900px;
        margin: 0 auto;
    }

    .profile-header {
        margin-bottom: clamp(30px, 5vw, 50px);
    }

    .profile-header h2 {
        font-size: clamp(1.8rem, 5vw, 2.5rem);
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .profile-header p {
        color: #6b7280;
        font-size: clamp(0.9rem, 2vw, 1rem);
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        padding: clamp(20px, 3vw, 30px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .profile-card h4 {
        font-size: 1.2rem;
        color: #1f2937;
        margin-bottom: 20px;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .avatar-section {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        overflow: hidden;
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #00D9B5;
        box-shadow: 0 0 0 3px rgba(0, 217, 181, 0.1);
    }

    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 20px;
        padding-right: 40px;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        font-size: 0.95rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-danger:hover {
        background: #fecaca;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .text-danger {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .file-input {
        display: none;
    }

    .file-label {
        display: inline-block;
        padding: 10px 20px;
        background: #f3f4f6;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.3s;
    }

    .file-label:hover {
        background: #e5e7eb;
    }

    @media (max-width: 768px) {
        .avatar-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-header">
        <h2>Chinh Sua Ho So</h2>
        <p>Cap nhat thong tin ca nhan cua ban</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- Avatar Section --}}
    <div class="profile-card">
        <h4>[USER] Anh Dai Dien</h4>
        <form action="{{ route('user.profile.avatar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="avatar-section">
                <div class="avatar-preview">
                    @if($user->getAvatarUrl())
                        <img src="{{ $user->getAvatarUrl() }}" alt="Avatar">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="avatar-actions">
                    <input type="file" name="avatar" id="avatar-input" class="file-input" accept="image/jpeg,image/png,image/webp">
                    <label for="avatar-input" class="file-label">Chon Anh Moi</label>
                    @if($user->getAvatarUrl())
                        <button type="submit" name="remove_avatar" value="1" class="btn btn-danger">Xoa Anh</button>
                    @endif
                    <button type="submit" class="btn btn-primary">Luu Anh</button>
                </div>
            </div>
            @error('avatar')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </form>
    </div>

    {{-- Basic Info Section --}}
    <div class="profile-card">
        <h4>[USER] Thong Tin Co Ban</h4>
        <form action="{{ route('user.profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Ho va Ten</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Dia Chi</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $user->location) }}" placeholder="Quan/Huyen, Phuong/Xa...">
                @error('location')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tinh/Thanh Pho</label>
                <select name="province_id" class="form-control form-select">
                    <option value="">-- Chon tinh/thanh pho --</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" {{ old('province_id', $user->province_id) == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
                @error('province_id')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Luu Thong Tin</button>
        </form>
    </div>

    {{-- Email Section --}}
    <div class="profile-card">
        <h4>[MAIL] Dia Chi Email</h4>
        <form action="{{ route('user.profile.email') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Mat Khau Hien Tai (de xac nhan)</label>
                <input type="password" name="current_password_email" class="form-control" placeholder="Nhap mat khau hien tai">
                @error('current_password_email')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Cap Nhat Email</button>
        </form>
    </div>

    {{-- Password Section --}}
    <div class="profile-card">
        <h4>[LOCK] Doi Mat Khau</h4>
        <form action="{{ route('user.profile.password') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Mat Khau Hien Tai</label>
                <input type="password" name="current_password" class="form-control" placeholder="Nhap mat khau hien tai">
                @error('current_password')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Mat Khau Moi</label>
                    <input type="password" name="password" class="form-control" placeholder="Toi thieu 6 ky tu">
                    @error('password')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Xac Nhan Mat Khau Moi</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Nhap lai mat khau moi">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Doi Mat Khau</button>
        </form>
    </div>

    {{-- Back Link --}}
    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('user.dashboard') }}" class="btn btn-secondary">Quay Lai Dashboard</a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Avatar preview
    document.getElementById('avatar-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.avatar-preview');
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
```

## Todo List

- [ ] Create user/profile directory
- [ ] Create edit.blade.php view
- [ ] Add avatar upload preview JS
- [ ] Add location text input field
- [ ] Style consistent with dashboard
- [ ] Vietnamese labels
- [ ] Flash message display
- [ ] Form validation error display

## Success Criteria

- [ ] View renders without errors
- [ ] Avatar preview shows current avatar or initial
- [ ] Avatar upload shows preview before save
- [ ] Location text input works
- [ ] Province dropdown populated
- [ ] All forms submit correctly
- [ ] Validation errors display properly
- [ ] Flash messages show success/error
- [ ] Responsive on mobile

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| Avatar preview not working | Low | Low | JS fallback to page reload |
| Province dropdown empty | Low | Medium | Check Province model exists |

## Security Considerations

- CSRF token on all forms
- File input restricted to image types
- Password fields use type="password"

## Next Steps

- Proceed to Phase 05: Routes & Validation
