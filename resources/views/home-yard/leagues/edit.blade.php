@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-forms.css') }}?v=1.1">
@endsection

@section('content')
<div class="main-content">
    <div class="top-header">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('homeyard.leagues.show', $league) }}" class="td-btn td-btn-ghost">
                &larr; Quay lại
            </a>
            <h2 class="page-title" style="margin: 0;">Chỉnh Sửa League</h2>
            @switch($league->status)
                @case('draft')
                    <span style="background-color: #f3f4f6; color: #4b5563; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Nháp</span>
                    @break
                @case('registration')
                    <span style="background-color: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đăng Ký</span>
                    @break
                @case('active')
                    <span style="background-color: #dcfce7; color: #15803d; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đang Diễn Ra</span>
                    @break
                @case('completed')
                    <span style="background-color: #f3e8ff; color: #7c3aed; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Hoàn Thành</span>
                    @break
                @case('cancelled')
                    <span style="background-color: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đã Hủy</span>
                    @break
                @default
                    <span style="background-color: #f3f4f6; color: #4b5563; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">{{ ucfirst($league->status) }}</span>
            @endswitch
        </div>
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
