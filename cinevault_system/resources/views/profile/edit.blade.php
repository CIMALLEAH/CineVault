@extends('layouts.app')

@section('page-title', 'My Profile')

@section('content')
<div style="max-width:760px; margin: 0 auto; padding: 20px;">
    <div class="card" style="padding:24px;">
        <h1 style="margin-bottom:16px;">My Profile</h1>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:16px;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom:16px;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required style="width:100%; padding:10px; margin-top:6px;" />
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required style="width:100%; padding:10px; margin-top:6px;" />
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" style="width:100%; padding:10px; margin-top:6px;" />
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="current_password">Current Password</label>
                <input id="current_password" name="current_password" type="password" style="width:100%; padding:10px; margin-top:6px;" />
                <small style="color:var(--text3);">Required only when updating your password.</small>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="password">New Password</label>
                <input id="password" name="password" type="password" style="width:100%; padding:10px; margin-top:6px;" />
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" style="width:100%; padding:10px; margin-top:6px;" />
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:24px;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ url()->previous() ?? route('dashboard') }}" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
