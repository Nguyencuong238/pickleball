@extends('layouts.app')

@section('title', 'Chỉnh Sửa Giải Đấu')

@section('content')
<div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div style="margin-bottom: 30px;">
        <a href="{{ route('admin.tournaments.index') }}" style="color: #8b5cf6; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
    </div>
    @include('admin.tournaments.form')
</div>
@endsection
