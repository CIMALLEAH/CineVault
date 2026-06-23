@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add New User')
 
@section('content')
<div style="max-width:560px;">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                @include('admin.users._form')
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input class="form-input" type="password" name="password" required placeholder="Min 8 characters">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input class="form-input" type="password" name="password_confirmation" required placeholder="Repeat password">
                </div>
                <div style="display:flex; gap:10px; margin-top:6px;">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection