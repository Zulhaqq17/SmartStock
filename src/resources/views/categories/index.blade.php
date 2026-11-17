@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Product categories</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Back to inventory</a>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">Add category</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle bg-white">
        <thead class="table-light">
            <tr><th>Category</th><th>Description</th><th>Near-expiry alert (days)</th><th>Products</th><th style="width:1%">Actions</th></tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->category_name }}</td>
                    <td>{{ $category->description ?: '-' }}</td>
                    <td>{{ $category->expiry_alert_days }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" class="d-inline"
                              onsubmit="return confirm('Delete category {{ addslashes($category->category_name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No categories yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
