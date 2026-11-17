@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<h1 class="h3 mb-3">Add user</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('users._form')
        </form>
    </div>
</div>
@endsection
