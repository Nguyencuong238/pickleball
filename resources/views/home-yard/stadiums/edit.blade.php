@extends('layouts.homeyard')

@section('content')
<main class="main-content" id="mainContent">
    <div class="container">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <h1>Chỉnh Sửa Sân</h1>
                <div class="breadcrumb">
                    <span class="breadcrumb-item">
                        <a href="{{ route('homeyard.dashboard') }}" class="breadcrumb-link">🏠 Dashboard</a>
                    </span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item">
                        <a href="{{ route('homeyard.stadiums.index') }}" class="breadcrumb-link">Sân</a>
                    </span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item">{{ $stadium->name }}</span>
                </div>
            </div>
        </header>

        @include('home-yard.stadiums.form')
    </div>
</main>
@endsection
