@extends('layouts.app')

@section('title', 'Edit Batch')

@section('content')
<h1 class="h3 mb-3">Edit Batch</h1>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('stock_entry.update', $batch) }}" class="row g-3">
    @csrf
    @method('PUT')

    <div class="col-md-6">
        <label class="form-label">Product</label>
        <input type="text" class="form-control" value="{{ $batch->product->product_name ?? $batch->product_id }}" disabled>
    </div>

    <div class="col-md-6">
        <label for="batch_number" class="form-label">Batch number</label>
        <input type="text" id="batch_number" name="batch_number" class="form-control"
               value="{{ old('batch_number', $batch->batch_number) }}" maxlength="100" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Initial quantity</label>
        <input type="text" class="form-control" value="{{ $batch->initial_quantity }}" disabled>
    </div>

    <div class="col-md-4">
        <label for="current_quantity" class="form-label">Current quantity</label>
        <input type="number" id="current_quantity" name="current_quantity" class="form-control"
               value="{{ old('current_quantity', $batch->current_quantity) }}" step="0.01" min="0" required>
        <div class="form-text">Changing this records an adjustment transaction.</div>
    </div>

    <div class="col-md-4">
        <label for="remarks" class="form-label">Reason for adjustment</label>
        <input type="text" id="remarks" name="remarks" class="form-control"
               value="{{ old('remarks') }}" maxlength="500">
    </div>

    <div class="col-md-6">
        <label for="manufacture_date" class="form-label">Manufacture date</label>
        <input type="date" id="manufacture_date" name="manufacture_date" class="form-control"
               value="{{ old('manufacture_date', $batch->manufacture_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-md-6">
        <label for="expiry_date" class="form-label">Expiry date</label>
        <input type="date" id="expiry_date" name="expiry_date" class="form-control"
               value="{{ old('expiry_date', $batch->expiry_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="{{ route('stock_entry.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection