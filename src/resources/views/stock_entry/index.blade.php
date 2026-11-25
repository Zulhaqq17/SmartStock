@extends('layouts.app')

@section('title', 'Stock Entry')

@section('content')
@php
    $status = ($filters['status'] ?? '') ?: 'current';
    $sort   = ($filters['sort'] ?? '') ?: 'expiry';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Stock Entry</h1>
    <div>
        <a href="{{ route('stock_entry.create') }}" class="btn btn-primary">Add batch</a>
        <a href="{{ route('stock_entry.import') }}" class="btn btn-outline-secondary">Import CSV</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('stock_entry.index') }}" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="text" name="q" class="form-control" placeholder="Search batch number"
               value="{{ $filters['q'] ?? '' }}">
    </div>
    <div class="col-md-3">
        <select name="product_id" class="form-select">
            <option value="">All products</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>
                    {{ $product->product_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="current"     @selected($status === 'current')>Current stock</option>
            <option value="all"         @selected($status === 'all')>All statuses</option>
            <option value="active"      @selected($status === 'active')>Active</option>
            <option value="near_expiry" @selected($status === 'near_expiry')>Near expiry</option>
            <option value="expired"     @selected($status === 'expired')>Expired</option>
            <option value="depleted"    @selected($status === 'depleted')>Depleted</option>
            <option value="deactivated" @selected($status === 'deactivated')>Deactivated</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="sort" class="form-select">
            <option value="expiry" @selected($sort === 'expiry')>Earliest expiry</option>
            <option value="newest" @selected($sort === 'newest')>Newest first</option>
            <option value="qty"    @selected($sort === 'qty')>Highest quantity</option>
            <option value="batch"  @selected($sort === 'batch')>Batch number</option>
        </select>
    </div>
    <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('stock_entry.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<p class="text-muted small mb-2">
    Showing {{ $batches->firstItem() ?? 0 }}–{{ $batches->lastItem() ?? 0 }} of {{ $batches->total() }} batches
</p>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Batch No.</th>
            <th>Product</th>
            <th class="text-end">Initial Qty</th>
            <th class="text-end">Current Qty</th>
            <th>Manufactured</th>
            <th>Expiry</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($batches as $batch)
            @php
                $badge = match($batch->status) {
                    'active'      => 'success',
                    'near_expiry' => 'warning',
                    'expired'     => 'danger',
                    'deactivated' => 'dark',
                    default       => 'secondary',
                };
                $pastExpiry = $batch->expiry_date && $batch->expiry_date->isPast() && $batch->current_quantity > 0;
            @endphp
            <tr @class(['table-secondary text-muted' => $batch->status === 'deactivated'])>
                <td class="fw-semibold">{{ $batch->batch_number }}</td>
                <td>{{ $batch->product->product_name ?? $batch->product_id }}</td>
                <td class="text-end">{{ $batch->initial_quantity }}</td>
                <td class="text-end">{{ $batch->current_quantity }}</td>
                <td>{{ $batch->manufacture_date?->format('d M Y') ?? '-' }}</td>
                <td @class(['text-danger fw-semibold' => $pastExpiry])>
                    {{ $batch->expiry_date?->format('d M Y') ?? '-' }}
                    @if($pastExpiry)<small>(past expiry)</small>@endif
                </td>
                <td><span class="badge text-bg-{{ $badge }}">{{ str_replace('_', ' ', $batch->status) }}</span></td>
                <td class="text-nowrap">
                    @if($batch->status === 'deactivated')
                        <form method="POST" action="{{ route('stock_entry.reactivate', $batch) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-success">Reactivate</button>
                        </form>
                    @else
                        <a href="{{ route('stock_entry.edit', $batch) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="{{ route('stock_entry.deactivate', $batch) }}" class="d-inline"
                              onsubmit="return confirm('Deactivate this batch?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-warning">Deactivate</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center py-4">No batches match your filters.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

{{ $batches->links('pagination::bootstrap-5') }}
@endsection