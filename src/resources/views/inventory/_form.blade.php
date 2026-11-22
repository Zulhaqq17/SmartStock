@php $editing = isset($product); @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="product_name" class="form-label">Product name <span class="text-danger">*</span></label>
        <input type="text" id="product_name" name="product_name" maxlength="150"
               value="{{ old('product_name', $product->product_name ?? '') }}"
               class="form-control @error('product_name') is-invalid @enderror" required>
        @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="sku_code" class="form-label">SKU / product code <span class="text-danger">*</span></label>
        <input type="text" id="sku_code" name="sku_code" maxlength="50" autocomplete="off"
            style="text-transform: uppercase"
            oninput="const s=this.selectionStart,e=this.selectionEnd;this.value=this.value.toUpperCase();this.setSelectionRange(s,e)"
            value="{{ old('sku_code', $product->sku_code ?? '') }}"
            class="form-control @error('sku_code') is-invalid @enderror" required>
        @error('sku_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="unit_of_measure" class="form-label">Unit of measure <span class="text-danger">*</span></label>
        <input type="text" id="unit_of_measure" name="unit_of_measure" maxlength="30" list="uom-list"
               value="{{ old('unit_of_measure', $product->unit_of_measure ?? '') }}"
               class="form-control @error('unit_of_measure') is-invalid @enderror" required>
        <datalist id="uom-list">
            @foreach (['kg', 'g', 'litre', 'ml', 'pcs', 'box', 'pack', 'bag'] as $unit)
                <option value="{{ $unit }}">
            @endforeach
        </datalist>
        @error('unit_of_measure') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if (auth()->user()->hasRole(\App\Models\Role::MANAGER))
        <div class="col-md-3">
            <label for="min_stock_level" class="form-label">Minimum stock level <span class="text-danger">*</span></label>
            <input type="number" id="min_stock_level" name="min_stock_level" step="0.01" min="1"
                    value="{{ old('min_stock_level', $product->min_stock_level ?? 1) }}"
                    class="form-control @error('min_stock_level') is-invalid @enderror" required>
            @error('min_stock_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @endif

    @if ($editing)
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                       @checked(old('is_active', $product->is_active))>
                <label class="form-check-label" for="is_active">Active (inactive products are hidden from normal use)</label>
            </div>
        </div>
    @endif
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add product' }}</button>
    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
