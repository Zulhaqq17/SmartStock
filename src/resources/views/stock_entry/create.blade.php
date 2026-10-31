@extends('layouts.app')

@section('title', 'Add Batch')

@section('content')
<h1 class="h3 mb-3">Add Batch</h1>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('stock_entry.store') }}" class="row g-3">
    @csrf

    <div class="col-md-6">
        <label for="product_id" class="form-label">Product</label>
        <select id="product_id" name="product_id" class="form-select" required>
            <option value="">Select a product</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                    {{ $product->product_name }} ({{ $product->sku_code }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="batch_number" class="form-label">Batch number</label>
        <input type="text" id="batch_number" name="batch_number" class="form-control"
               value="{{ old('batch_number') }}" maxlength="100" required>
    </div>

    <div class="col-md-4">
        <label for="initial_quantity" class="form-label">Quantity</label>
        <input type="number" id="initial_quantity" name="initial_quantity" class="form-control"
               value="{{ old('initial_quantity') }}" step="0.01" min="0.01" required>
    </div>

    <div class="col-md-4">
        <label for="manufacture_date" class="form-label">Manufacture date</label>
        <input type="date" id="manufacture_date" name="manufacture_date" class="form-control"
               value="{{ old('manufacture_date') }}">
    </div>

    <div class="col-md-4">
        <label for="expiry_date" class="form-label">Expiry date</label>
        <input type="date" id="expiry_date" name="expiry_date" class="form-control"
               value="{{ old('expiry_date') }}">
        <div class="form-text">Leave empty for non-perishable items.</div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Save batch</button>
        <a href="{{ route('stock_entry.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection