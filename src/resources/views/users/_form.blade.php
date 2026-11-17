@php $editing = isset($user); @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" maxlength="255" value="{{ old('name', $user->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" maxlength="255" value="{{ old('email', $user->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
        <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
            <option value="">Select role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? '') == $role->id)>{{ $role->role_name }}</option>
            @endforeach
        </select>
        @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if ($editing)
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                       @checked(old('is_active', $user->is_active))>
                <label class="form-check-label" for="is_active">Account active (inactive users cannot log in)</label>
            </div>
        </div>
    @endif

    <div class="col-md-6">
        <label for="password" class="form-label">
            {{ $editing ? 'New password' : 'Password' }} @unless ($editing)<span class="text-danger">*</span>@endunless
        </label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" @required(! $editing)>
        <div class="form-text">{{ $editing ? 'Leave blank to keep the current password. ' : '' }}Minimum 8 characters.</div>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
               class="form-control" @required(! $editing)>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Create user' }}</button>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
