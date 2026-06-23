@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit: ' . $user->name)
 
@section('content')
<div style="max-width:560px;">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PUT')
                @include('admin.users._form', ['user' => $user])
                <div class="form-group">
                    <label class="form-label">New Password <span style="color:var(--text3); font-weight:400;">(leave blank to keep current)</span></label>
                    <input class="form-input" type="password" name="password" placeholder="Min 8 characters">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input class="form-input" type="password" name="password_confirmation">
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               style="width:16px; height:16px; accent-color:var(--gold);">
                        <span class="form-label" style="margin:0;">Active account</span>
                    </label>
                </div>
                <div style="display:flex; gap:10px; margin-top:6px;">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection