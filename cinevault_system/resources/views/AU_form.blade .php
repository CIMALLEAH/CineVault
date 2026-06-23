{{-- Shared form partial for user create/edit --}}
<div class="form-group">
    <label class="form-label">Full Name *</label>
    <input class="form-input" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required placeholder="Full name">
</div>
<div class="form-group">
    <label class="form-label">Email *</label>
    <input class="form-input" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required placeholder="email@example.com">
</div>
<div class="form-group">
    <label class="form-label">Role *</label>
    <select class="form-select" name="role" required>
        <option value="user"  {{ old('role', $user->role ?? 'user') === 'user'  ? 'selected':'' }}>Regular User</option>
        <option value="staff" {{ old('role', $user->role ?? '')     === 'staff' ? 'selected':'' }}>Staff</option>
        <option value="admin" {{ old('role', $user->role ?? '')     === 'admin' ? 'selected':'' }}>Admin</option>
    </select>
</div>
<div class="form-group">
    <label class="form-label">Phone</label>
    <input class="form-input" type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="09XXXXXXXXX">
</div>