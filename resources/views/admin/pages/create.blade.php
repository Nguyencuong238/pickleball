@extends('layouts.app')

@section('title', 'Create Page')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div style="margin-bottom: 30px;">
        <a href="{{ route('admin.pages.index') }}" style="color: #00D9B5; text-decoration: none; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to Pages
        </a>
        <h3 style="margin: 15px 0;">Create New Page</h3>
    </div>

    @include('admin.pages.form')
</div>
@endsection
