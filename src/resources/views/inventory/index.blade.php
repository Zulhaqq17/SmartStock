@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
@php $canManage = auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE); @endphp
@php $isManager = auth()->user()->hasRole(\App\Models\Role::MANAGER); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Inventory</h1>
    @if ($canManage)
        <div class="d-flex gap-2">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Manage categories</a>
            <a href="{{ route('inventory.create') }}" class="btn btn-primary">Add product</a>
        </div>
    @endif
</div>

<form method="GET" action="{{ route('inventory.index') }}" class="row g-2 mb-3">
    <div class="col-12 col-md-5">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by product name or SKU">
    </div>
    <div class="col-6 col-md-3">
        <select name="category_id" class="form-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->category_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select name="status" class="form-select">
            <option value="all" @selected($status === 'all')>All status</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
        </select>
    </div>
    <div class="col-12 col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle bg-white">
        <thead class="table-light">
            <tr>
                <th>SKU</th><th>Product</th><th>Category</th><th>Total stock</th>@if ($isManager)<th>Min level</th>@endif<th>Status</th><th style="width:1%">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                @php $stock = (float) ($product->total_stock ?? 0); @endphp
                <tr>
                    <td>{{ $product->sku_code }}</td>
                    <td><a href="{{ route('inventory.show', $product) }}">{{ $product->product_name }}</a></td>
                    <td>{{ $product->category->category_name }}</td>
                    <td>
                        {{ rtrim(rtrim(number_format($stock, 2), '0'), '.') }} {{ $product->unit_of_measure }}
                        @if ($stock < (float) $product->min_stock_level)
                            <span class="badge text-bg-warning">Low</span>
                        @endif
                    </td>
                    @if ($isManager)<td>{{ rtrim(rtrim(number_format($product->min_stock_level, 2), '0'), '.') }}</td>@endif
                    <td>
                        <span class="badge {{ $product->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('inventory.show', $product) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @if ($canManage)
                            <a href="{{ route('inventory.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('inventory.destroy', $product) }}" class="d-inline"
                                  onsubmit="return confirm('Delete {{ addslashes($product->product_name) }}? If it already has batches it will be deactivated instead.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $isManager ? 7 : 6 }}" class="text-center text-muted">No products found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $products->links('pagination::bootstrap-5') }}
@endsection
