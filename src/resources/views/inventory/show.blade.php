@extends('layouts.app')

@section('title', $product->product_name)

@section('content')
@php
    $canManage = auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE);
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $product->product_name }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Back</a>
        @if ($canManage)
            <a href="{{ route('inventory.edit', $product) }}" class="btn btn-primary">Edit</a>
        @endif
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">SKU / code</dt><dd class="col-sm-9">{{ $product->sku_code }}</dd>
            <dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $product->category->category_name }}</dd>
            <dt class="col-sm-3">Unit of measure</dt><dd class="col-sm-9">{{ $product->unit_of_measure }}</dd>
            <dt class="col-sm-3">Minimum stock level</dt><dd class="col-sm-9">{{ $fmt($product->min_stock_level) }} {{ $product->unit_of_measure }}</dd>
            <dt class="col-sm-3">Total stock</dt><dd class="col-sm-9">{{ $fmt($product->total_stock ?? 0) }} {{ $product->unit_of_measure }}</dd>
            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                <span class="badge {{ $product->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
            </dd>
            <dt class="col-sm-3">Created</dt><dd class="col-sm-9">{{ $product->created_at?->format('d M Y, H:i') }}</dd>
            <dt class="col-sm-3">Last updated</dt><dd class="col-sm-9 mb-0">{{ $product->updated_at?->format('d M Y, H:i') }}</dd>
        </dl>
    </div>
</div>

<h2 class="h5">Batches (read-only, managed in Stock Entry)</h2>
<div class="table-responsive">
    <table class="table table-bordered bg-white align-middle">
        <thead class="table-light">
            <tr><th>Batch no.</th><th>Quantity left</th><th>Manufactured</th><th>Expiry</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($product->batches as $batch)
                <tr>
                    <td>{{ $batch->batch_number }}</td>
                    <td>{{ $fmt($batch->current_quantity) }} {{ $product->unit_of_measure }}</td>
                    <td>{{ $batch->manufacture_date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $batch->expiry_date?->format('d M Y') ?? 'No expiry' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No batches recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
