@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<h1 class="h3 mb-3">Add product</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('inventory.store') }}">
            @csrf
            @include('inventory._form')
        </form>
    </div>
</div>
@endsection
