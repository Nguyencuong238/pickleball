@extends('layouts.app')

@section('title', 'Edit Stadium - ' . $stadium->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div style="margin-bottom: 30px;">
        <a href="{{ route('admin.stadiums.index') }}" style="color: #00D9B5; text-decoration: none; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to Stadiums
        </a>
        <h3 style="margin: 15px 0;">Edit Stadium: {{ $stadium->name }}</h3>
    </div>

    @include('admin.stadiums.form')
</div>
@endsection
