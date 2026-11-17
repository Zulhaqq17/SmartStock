@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<h1 class="h3 mb-3">Edit product</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('inventory.update', $product) }}">
            @csrf
            @method('PUT')
            @include('inventory._form')
        </form>
    </div>
</div>
@endsection
