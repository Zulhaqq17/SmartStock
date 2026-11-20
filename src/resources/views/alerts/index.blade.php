@extends('layouts.app')

@section('title', 'Alerts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Alerts</h1>
    <form method="POST" action="{{ route('alerts.run') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Run check</button>
    </form>
</div>

<div class="alert alert-warning">
    TODO (Module 5): show each open alert in the table below (type, product, batch, message, date) with a
    "Mark resolved" button (POST to route('alerts.resolve', $alert)). Colour the type with Bootstrap
    badges: <code>text-bg-danger</code> expired, <code>text-bg-warning</code> near expiry, <code>text-bg-info</code> low stock.
</div>

<div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr><th>Type</th><th>Product</th><th>Batch</th><th>Message</th><th>Raised</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($alerts as $alert)
                <tr>
                    <td>{{ $alert->alert_type }}</td>
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
                <tr><td colspan="6" class="text-center text-muted">No open alerts.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
