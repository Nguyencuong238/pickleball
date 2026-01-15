@extends('admin.layouts.app')

@section('title', 'Edit Special Challenge')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Special Challenge</h1>
        <a href="{{ route('admin.special-challenges.index') }}" class="btn btn-outline-secondary">
            &larr; Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.special-challenges.update', $specialChallenge) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $specialChallenge->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $specialChallenge->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Points *</label>
                        <input type="number" name="points" class="form-control @error('points') is-invalid @enderror" value="{{ old('points', $specialChallenge->points) }}" min="1" max="1000" required>
                        @error('points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $specialChallenge->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $specialChallenge->end_date->format('Y-m-d')) }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Max Participants</label>
                        <input type="number" name="max_participants" class="form-control @error('max_participants') is-invalid @enderror" value="{{ old('max_participants', $specialChallenge->max_participants) }}" min="1">
                        @error('max_participants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $specialChallenge->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Participants:</strong> {{ $specialChallenge->getParticipantCount() }}
                    @if($specialChallenge->max_participants)
                        / {{ $specialChallenge->max_participants }}
                    @endif
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Challenge</button>
                    <a href="{{ route('admin.special-challenges.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
