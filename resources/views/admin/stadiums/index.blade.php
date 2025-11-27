@extends('layouts.app')

@section('title', 'Stadiums Management')

@section('content')
<div class="container-fluid">
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Table -->
    <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden;">
        @if($stadiums->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">ID</th>
                            <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Name</th>
                            <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Address</th>
                            <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Status</th>
                            <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #475569;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stadiums as $stadium)
                            <tr style="border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s;">
                                <td style="padding: 15px 20px;">{{ $stadium->id }}</td>
                                <td style="padding: 15px 20px; font-weight: 500;">{{ $stadium->name }}</td>
                                <td style="padding: 15px 20px; color: #6b7280;">{{ Str::limit($stadium->address, 30) }}</td>
                                <td style="padding: 15px 20px;">
                                    @if($stadium->status === 'active')
                                        <span style="background-color: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Active</span>
                                    @else
                                        <span style="background-color: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Inactive</span>
                                    @endif
                                </td>
                                <td style="padding: 15px 20px; text-align: center;">
                                    <a href="{{ route('admin.stadiums.edit', $stadium) }}" class="btn btn-sm" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.stadiums.destroy', $stadium) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer;" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="padding: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: center;">
                {{ $stadiums->links() }}
            </div>
        @else
            <div style="padding: 60px 20px; text-align: center;">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #d1d5db; margin-bottom: 20px;"></i>
                <h4 style="color: #9ca3af; margin: 20px 0;">No stadiums found</h4>
                <p style="color: #9ca3af;">Start by <a href="{{ route('admin.stadiums.create') }}" style="color: #00D9B5; text-decoration: none; font-weight: 600;">adding a new stadium</a></p>
            </div>
        @endif
    </div>
</div>

<style>
    table tbody tr:hover {
        background-color: #f8fafc;
    }

    .btn-primary {
        background-color: #00D9B5;
        border-color: #00D9B5;
        color: #1e293b;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary:hover {
        background-color: #00b8a0;
        border-color: #00b8a0;
    }
</style>
@endsection
