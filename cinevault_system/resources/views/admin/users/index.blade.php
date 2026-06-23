@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')
 
@section('topbar-actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add User
    </a>
@endsection
 
@section('content')
{{-- Filters --}}
<form method="GET" style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
    <div class="search-box" style="flex:1; min-width:200px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" placeholder="Search name or email..." value="{{ request('search') }}">
    </div>
    <select name="role" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <option value="admin" {{ request('role')==='admin' ? 'selected':'' }}>Admin</option>
        <option value="staff" {{ request('role')==='staff' ? 'selected':'' }}>Staff</option>
        <option value="user"  {{ request('role')==='user'  ? 'selected':'' }}>User</option>
    </select>
    <button class="btn btn-secondary btn-sm" type="submit">Search</button>
</form>
 
<div class="card" style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Joined</th>
                <th>Rentals</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,var(--gold),var(--gold2)); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px; color:#000; flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <span style="font-weight:500;">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td style="color:var(--text2);">{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role==='admin' ? 'badge-gold' : ($user->role==='staff' ? 'badge-blue' : 'badge-purple') }}"
                              style="text-transform:capitalize;">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td style="color:var(--text3);">{{ $user->phone ?? '—' }}</td>
                    <td style="color:var(--text3);">{{ $user->created_at->format('M d, Y') }}</td>
                    <td style="font-weight:600; color:var(--gold);">{{ $user->rentals_count }}</td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm">Edit</a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--text3);">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination">{{ $users->links() }}</div>
@endsection
