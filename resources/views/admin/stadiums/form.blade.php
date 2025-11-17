@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validation Errors:</strong>
        <ul style="margin-bottom: 0; margin-top: 10px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ isset($stadium) ? route('admin.stadiums.update', $stadium) : route('admin.stadiums.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($stadium))
            @method('PUT')
        @endif

        <!-- Row 1: Name and Status -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Name -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Stadium Name *</label>
                <input type="text" name="name" class="form-control" value="{{ $stadium->name ?? old('name') }}" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <!-- Status -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Status *</label>
                <select name="status" class="form-control" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
                    <option value="active" {{ (isset($stadium) && $stadium->status === 'active') || old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (isset($stadium) && $stadium->status === 'inactive') || old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Description</label>
            <textarea name="description" class="form-control" rows="4"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; font-family: inherit;">{{ $stadium->description ?? old('description') }}</textarea>
        </div>

        <!-- Row 2: Address -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Address *</label>
            <input type="text" name="address" class="form-control" value="{{ $stadium->address ?? old('address') }}" required
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Row 3: Contact Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Phone</label>
                <input type="tel" name="phone" class="form-control" value="{{ $stadium->phone ?? old('phone') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $stadium->email ?? old('email') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Website</label>
                <input type="url" name="website" class="form-control" value="{{ $stadium->website ?? old('website') }}"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Row 4: Courts Count, Hours -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Number of Courts *</label>
                <input type="number" name="courts_count" class="form-control" value="{{ $stadium->courts_count ?? old('courts_count', 1) }}" min="1" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Opening Hours</label>
                <input type="text" name="opening_hours" class="form-control" value="{{ $stadium->opening_hours ?? old('opening_hours') }}" placeholder="e.g. 06:00 - 22:00"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
            </div>
        </div>

        <!-- Image -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Stadium Image</label>
            @if(isset($stadium) && $stadium->image)
                <div style="margin-bottom: 10px;">
                    <img src="{{ asset('storage/' . $stadium->image) }}" alt="{{ $stadium->name }}" style="max-width: 200px; max-height: 150px; border-radius: 6px;">
                </div>
            @endif
            <input type="file" name="image" class="form-control" accept="image/*"
                style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem;">
        </div>

        <!-- Buttons -->
        <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('admin.stadiums.index') }}" class="btn" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Cancel</a>
            <button type="submit" class="btn" style="background-color: #00D9B5; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                {{ isset($stadium) ? 'Update Stadium' : 'Create Stadium' }}
            </button>
        </div>
    </form>
</div>
