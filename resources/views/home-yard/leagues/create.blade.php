@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-forms.css') }}?v=1.1">
@endsection

@section('content')
<div class="main-content">
    <div class="top-header">
        <a href="{{ route('homeyard.leagues.index') }}" class="td-btn td-btn-ghost">
            &larr; Quay lại
        </a>
        <h2 class="page-title">Tạo League Mới</h2>
    </div>

    @if($errors->any())
        <div class="td-alert td-alert-error">
            <strong>Lỗi xác thực:</strong>
            <ul style="margin: 8px 0 0 16px; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="max-width: 900px;">
        @include('home-yard.leagues._form')
    </div>
</div>
@endsection
