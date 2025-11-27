@extends('layouts.app')

@section('title', 'Chỉnh sửa - ' . $stadium->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div style="margin-bottom: 30px;">
        <a href="{{ route('admin.stadiums.index') }}" style="color: #00D9B5; text-decoration: none; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @include('admin.stadiums.form')
</div>
@endsection
