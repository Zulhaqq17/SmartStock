@extends('layouts.app')

@section('title', 'Stock Entry')

@section('content')
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

<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>Batch No.</th>
            <th>Product</th>
            <th>Initial Qty</th>
            <th>Current Qty</th>
            <th>Manufacture Date</th>
            <th>Expiry Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($batches as $batch)
            <tr>
                <td>{{ $batch->batch_number }}</td>
                <td>{{ $batch->product->product_name ?? $batch->product_id }}</td>
                <td>{{ $batch->initial_quantity }}</td>
                <td>{{ $batch->current_quantity }}</td>
                <td>{{ $batch->manufacture_date ?? '-' }}</td>
                <td>{{ $batch->expiry_date ?? '-' }}</td>
                <td><span class="badge text-bg-secondary">{{ $batch->status }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">No batches yet.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection