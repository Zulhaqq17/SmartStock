@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class = "container-fluid px-4">
    <h1 class="h3 mb-3">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class= "breadcrumb-item active">SmartStock Inventory Overview</li>
</ol>

<!-- Summary Cards -->
<div class="row">
    <div class = "col-xl-3 col-md-6">
        <div class = "card bg-primary text-white mb-4">
            <div class = "card-body">Total Products</div>
            <div class = "card-footer d-flex align-items-center justify-content-between">
                <span class = "fs-4 fw-bold">{{ $summary['total_products']}}</span>
</div>
</div>
</div>
<div class = "col-xl-3 col-md-6">
    <div class = "card bg-warning text-darl mb-4">
        <div class = "card-body">Low Stock Items</div>
        <div class = "card-footer d-flex align-items-center justify-content-between">
            <span class = "fs-4 fw-bold">{{ $summary['low_stock_count']}}</span>
</div>
</div>
</div>
<div class = "col-xl-3 col-md-6">
    <div class = "card bg-danger text-white mb-4">
        <div class = "card-body">Expiring Batches (30 Days) </div>
        <div = class "card-footer d-flex align-items-center justify-content-between">
            <span class = "fs-4 fw-bold">{{ $summary['expiring_batches']}}</span>
</div>
</div>
</div>
<div class = "col-xl-3 col-md-6">
    <div class = "card bg-success text-white mb-4">
        <div class = "card-body">Total Batches Tracked</div>
        <div class = "card-footer d-flex align-items-center justify-content-between">
            <span class = "fs-4 fw-bold">{{ $summary['total_batches'] }}</span>
</div>
</div>
</div>
</div>

<!-- data tables row -->
<div class="row">
    <!-- expiring batches -->
    <div class = "col-xl-6">
        <div class = "card-mb-4">
            <div class = "card-header">
                <i class = "fas fa-exclamation-triangle me-1"></i>
                Expiring Batches (Next 30 Days)
        </div>
            <div class = "card-body">
                <div class = "table-responsive">
                    <table class = "table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Batch #</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringBatches as $batch)
                            <tr>
                                <td>{{ $batch->batch_number }}</td>
                                <td>{{ $batch->product->product_name ?? 'N/A' }}</td>
                                <td>{{ $batch->current_quantity }}</td>
                                <td><span class = "text-danger fw-bold">{{ $batch->expiry_date }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No expiring batches found.</td>
                            </tr>
                            @endforelse
</tbody>
</table>
</div>
</div>
</div>
</div>

<!-- recent stock movements -->
<div class = "col-xl-6">
    <div class = "card mb-4">
        <div class = "card-header">
            <i class = "fas fa-history me-1"></i>
            Recent Stock transactions
        </div>
        <div class = "card-body">
            <div class = "table-responsive">
                <table class = "table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Product</th>
                            <th>Quantity Changed</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMovements as $tx)
                        <tr>
                            <td>
                                <span class = "badge bg-{{ $tx->transaction_type == 'IN' ? 'success' : 'secondary' }}">
                                    {{ $tx->transaction_type }}
                                </span>
                            </td>
                            <td>{{ $tx->batch->product->product_name ?? 'N/A' }}</td>
                            <td>{{ $tx->quantity_changed }}</td>
                            <td>{{ $tx->transaction_date }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No recent movements recorded.</td>
                        </tr>
                        @endforelse
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
@endsection
