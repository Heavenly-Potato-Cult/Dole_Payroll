@extends('layouts.app')
 
@section('title', 'Edit User')
@section('page-title', 'Edit User')
 
@section('content')
 
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit User</h1>
        <p>{{ $user->email }}</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline">← Back</a>
</div>
 
<div style="max-width:520px;">
    <div class="card">
        <div class="card-header"><h3>Account Details</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
 
                <div class="form-group">
                    <label for="name">Full Name <span style="color:var(--red)">*</span></label>
                    <input type="text" id="name" name="name" class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
 
                <div class="form-group">
                    <label for="email">Email Address <span style="color:var(--red)">*</span></label>
                    <input type="email" id="email" name="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
 
                <div class="form-group">
                    <label for="role">Role <span style="color:var(--red)">*</span></label>
                    <select id="role" name="role" class="{{ $errors->has('role') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select a role —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ (old('role', $user->roles->first()?->name) === $role->name) ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
 
                <hr style="border:none;border-top:1px solid var(--border);margin:20px 0 4px;">
                <p style="font-size:0.82rem;color:var(--text-light);margin-bottom:16px;">
                    Leave password fields blank to keep the current password.
                </p>
 
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Leave blank to keep current">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
 
                <div class="form-group" style="margin-bottom:0;">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation">
                </div>
 
                <div style="display:flex;gap:10px;margin-top:24px;">
                    <button type="submit" class="btn btn-primary">✓ Save Changes</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
 
@endsection