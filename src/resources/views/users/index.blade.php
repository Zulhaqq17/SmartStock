@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">User accounts</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">Add user</a>
</div>

<form method="GET" action="{{ route('users.index') }}" class="row g-2 mb-3">
    <div class="col-12 col-md-5">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name or email">
    </div>
    <div class="col-6 col-md-3">
        <select name="role_id" class="form-select">
            <option value="">All roles</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>{{ $role->role_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select name="status" class="form-select">
            <option value="">All status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
    </div>
    <div class="col-12 col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle bg-white">
        <thead class="table-light">
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th style="width:1%">Actions</th></tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }} @if ($user->is(auth()->user())) <span class="badge text-bg-info">You</span> @endif</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role?->role_name }}</td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at?->format('d M Y') }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        @unless ($user->is(auth()->user()))
                            <form method="POST" action="{{ route('users.toggle', $user) }}" class="d-inline"
                                  onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} {{ addslashes($user->name) }}?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $users->links('pagination::bootstrap-5') }}
@endsection
