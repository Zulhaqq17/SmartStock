@extends('layouts.app')

@section('title', 'Alerts')

@section('content')
@php
    $badge = [
        'expired' => 'text-bg-danger',
        'near_expiry' => 'text-bg-warning',
        'low_stock' => 'text-bg-info',
        'predicted_spoilage' => 'text-bg-secondary',
        'reorder_suggested' => 'text-bg-primary',
    ];
    $label = [
        'expired' => 'Expired',
        'near_expiry' => 'Near expiry',
        'low_stock' => 'Low stock',
        'predicted_spoilage' => 'Predicted spoilage',
        'reorder_suggested' => 'Reorder suggested',
    ];
    $aiTypes = ['predicted_spoilage', 'reorder_suggested'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Alerts</h1>
    <form method="POST" action="{{ route('alerts.run') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Run check</button>
    </form>
</div>

<div class="table-responsive mb-4">
    <table class="table table-bordered bg-white align-middle">
        <thead class="table-light">
            <tr><th>Type</th><th>Product</th><th>Batch</th><th>Message</th><th>Raised</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($alerts as $alert)
                <tr>
                    <td>
                        <span class="badge {{ $badge[$alert->alert_type] ?? 'text-bg-light' }}">{{ $label[$alert->alert_type] ?? $alert->alert_type }}</span>
                        @if (in_array($alert->alert_type, $aiTypes))
                            <span class="badge text-bg-dark">AI</span>
                        @endif
                    </td>
                    <td>{{ $alert->product->product_name }}</td>
                    <td>{{ $alert->batch->batch_number ?? '-' }}</td>
                    <td>{{ $alert->message }}</td>
                    <td>{{ $alert->created_at->diffForHumans() }}</td>
                    <td>
                        <form method="POST" action="{{ route('alerts.resolve', $alert) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark resolved</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No open alerts. Press "Run check".</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="h5">AI demand forecast <span class="badge text-bg-dark">AI</span></h2>
<p class="text-muted small">
    Next 7 days, per product. Model: exponential smoothing with a day-of-week pattern, tested on the last 7 days of usage.
    Confidence is the accuracy of that test.
</p>

<div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>Product</th><th>Expected demand</th><th>Predicted spoilage</th>
                <th>Suggested reorder</th><th>Confidence</th><th>Generated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($forecasts as $f)
                <tr>
                    <td>{{ $f->product->product_name }}</td>
                    <td>{{ $f->predicted_demand_qty }} {{ $f->product->unit_of_measure }}</td>
                    <td>{{ $f->predicted_spoilage_qty }} {{ $f->product->unit_of_measure }}</td>
                    <td>{{ $f->recommended_reorder_qty }} {{ $f->product->unit_of_measure }}</td>
                    <td>{{ $f->confidence_score !== null ? $f->confidence_score.'%' : '-' }}</td>
                    <td>{{ $f->generated_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">
                    No forecasts yet. Make sure the forecast service is running and usage history exists, then press "Run check".
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
