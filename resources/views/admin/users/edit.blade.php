@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h2>Edit {{ $user->name }} Permissions</h2>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Select Roles</label>
                    <div class="form-check">
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="roles[]" 
                                    value="{{ $role->name }}"
                                    id="role_{{ $role->id }}"
                                    @if(in_array($role->name, $userRoles)) checked @endif
                                >
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    {{ ucfirst($role->name) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Loại Vận Động Viên</label>
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            name="athlete_types[]" 
                            value="athlete_international"
                            id="athlete_international"
                            @if($user->athlete_types && in_array('athlete_international', $user->athlete_types)) checked @endif
                        >
                        <label class="form-check-label" for="athlete_international">
                            Vận động viên quốc tế
                        </label>
                    </div>
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            name="athlete_types[]" 
                            value="athlete_vietnam"
                            id="athlete_vietnam"
                            @if($user->athlete_types && in_array('athlete_vietnam', $user->athlete_types)) checked @endif
                        >
                        <label class="form-check-label" for="athlete_vietnam">
                            Vận động viên Việt Nam
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Update Permissions</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
