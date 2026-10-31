@extends('layouts.app')

@section('title', 'Import CSV')

@section('content')
<h1 class="h3 mb-3">Import CSV</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('import_errors'))
    <div class="alert alert-warning">
        <strong>These rows were not imported:</strong>
        <ul class="mb-0 mt-2">
            @foreach(session('import_errors') as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6">CSV format</h2>
        <p class="mb-2">The first row must be this header. One batch per row, dates as <code>YYYY-MM-DD</code>.</p>
        <table class="table table-sm mb-0">
            <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
            <tbody>
                <tr><td><code>sku_code</code></td><td>Yes</td><td>Must match an existing product SKU</td></tr>
                <tr><td><code>batch_number</code></td><td>Yes</td><td>Unique per product</td></tr>
                <tr><td><code>quantity</code></td><td>Yes</td><td>Number greater than 0</td></tr>
                <tr><td><code>manufacture_date</code></td><td>No</td><td>Leave empty if unknown</td></tr>
                <tr><td><code>expiry_date</code></td><td>No</td><td>Empty for non-perishable items</td></tr>
            </tbody>
        </table>
    </div>
</div>

<form method="POST" action="{{ route('stock_entry.import.store') }}" enctype="multipart/form-data" class="row g-3">
    @csrf
    <div class="col-md-6">
        <label for="csv_file" class="form-label">CSV file</label>
        <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv,.txt" required>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Import</button>
        <a href="{{ route('stock_entry.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</form>
@endsection