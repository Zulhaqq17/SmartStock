@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Inventory</h1>

    @if (auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE))
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">Add product</a>
    @endif
</div>

<div class="alert alert-warning">
    <strong>REFERENCE PAGE (working).</strong> Module 2 owner: add search, a category filter and Edit buttons.
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>SKU</th><th>Product</th><th>Category</th><th>Total stock</th><th>Min level</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku_code }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->category->category_name }}</td>
                    <td>{{ $product->total_stock ?? 0 }} {{ $product->unit_of_measure }}</td>
                    <td>{{ $product->min_stock_level }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No products yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
