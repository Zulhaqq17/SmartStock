@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<h1 class="h3 mb-3">Edit user</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('users._form')
        </form>
    </div>
</div>
@endsection
