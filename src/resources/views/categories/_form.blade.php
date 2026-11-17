@php $editing = isset($category); @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="category_name" class="form-label">Category name <span class="text-danger">*</span></label>
        <input type="text" id="category_name" name="category_name" maxlength="100"
               value="{{ old('category_name', $category->category_name ?? '') }}"
               class="form-control @error('category_name') is-invalid @enderror" required>
        @error('category_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="expiry_alert_days" class="form-label">Near-expiry alert (days before expiry) <span class="text-danger">*</span></label>
        <input type="number" id="expiry_alert_days" name="expiry_alert_days" min="0" max="3650"
               value="{{ old('expiry_alert_days', $category->expiry_alert_days ?? 7) }}"
               class="form-control @error('expiry_alert_days') is-invalid @enderror" required>
        <div class="form-text">Batches in this category are flagged as near expiry this many days before their expiry date.</div>
        @error('expiry_alert_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="1000"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add category' }}</button>
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
